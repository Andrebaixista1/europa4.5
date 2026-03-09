<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MASTER_LOGIN = 'andrefelipe';
    private const DEFAULT_PERMISSIONS_JSON = '{"dashboard":true,"settings.users":false,"settings.permissions":false}';
    private const MASTER_PERMISSIONS_JSON = '{"dashboard":true,"settings.users":true,"settings.permissions":true}';

    private function authDebug(string $event, array $context = []): void
    {
        $payload = array_merge(['event' => $event], $context);
        error_log('AUTH_DEBUG '.json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $normalizedLogin = Str::lower(trim((string) $this->input('login')));
        $password = (string) $this->input('password');

        if ($this->attemptBridgeLogin($normalizedLogin, $password)) {
            RateLimiter::clear($this->throttleKey());

            return;
        }

        if ($this->attemptRealDatabaseLogin($normalizedLogin, $password)) {
            RateLimiter::clear($this->throttleKey());

            return;
        }

        if ($this->attemptEnvDemoLogin($normalizedLogin, $password)) {
            RateLimiter::clear($this->throttleKey());

            return;
        }

        if ($normalizedLogin !== self::MASTER_LOGIN) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        if (app()->environment('testing')) {
            if (! Auth::attempt(['name' => self::MASTER_LOGIN, 'password' => (string) $this->input('password')], $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'login' => trans('auth.failed'),
                ]);
            }

            RateLimiter::clear($this->throttleKey());
            return;
        }

        $passwordSha256 = hash('sha256', $password);
        try {
            $remoteUser = DB::connection('lumia_sqlsrv')
                ->table('lumia_auth_users')
                ->select(['login', 'password_sha256', 'role', 'permissions_config_json'])
                ->whereRaw('LOWER(login) = ?', [self::MASTER_LOGIN])
                ->first();
        } catch (\Throwable $e) {
            report($e);
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        if (! $remoteUser || ! hash_equals((string) $remoteUser->password_sha256, $passwordSha256)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        $this->ensurePermissionsConfigForUser(self::MASTER_LOGIN, (string) ($remoteUser->permissions_config_json ?? ''));

        $sessionUser = User::updateOrCreate(
            ['email' => self::MASTER_LOGIN.'@lumia.local'],
            [
                'name' => self::MASTER_LOGIN,
                // Local password is not used for authentication; auth is validated against SQL Server.
                'password' => Hash::make(Str::random(40)),
            ]
        );

        Auth::login($sessionUser, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    private function attemptBridgeLogin(string $normalizedLogin, string $password): bool
    {
        $providerDriver = Str::lower(trim((string) config('auth.providers.users.driver', '')));
        if ($providerDriver !== 'bridge') {
            return false;
        }

        if ($normalizedLogin === '' || $password === '') {
            return false;
        }

        $ok = Auth::attempt([
            'login' => $normalizedLogin,
            'password' => $password,
        ], $this->boolean('remember'));

        if (! $ok) {
            $this->authDebug('bridge_login_failed', [
                'login' => $normalizedLogin,
            ]);
            return false;
        }

        $bridgeUser = Auth::user();
        $this->authDebug('bridge_login_success', [
            'login' => $normalizedLogin,
            'user_id' => $bridgeUser?->getAuthIdentifier(),
        ]);

        return true;
    }

    private function attemptRealDatabaseLogin(string $normalizedLogin, string $password): bool
    {
        try {
            /** @var User|null $user */
            $user = User::query()
                ->whereRaw('LOWER(login) = ?', [$normalizedLogin])
                ->first();
        } catch (\Throwable $e) {
            report($e);
            Log::error('Auth login DB query failed', [
                'login' => $normalizedLogin,
                'connection' => config('database.default'),
                'database' => config('database.connections.'.config('database.default').'.database'),
                'host' => config('database.connections.'.config('database.default').'.host'),
            ]);
            $this->authDebug('db_query_failed', [
                'login' => $normalizedLogin,
                'connection' => config('database.default'),
                'database' => config('database.connections.'.config('database.default').'.database'),
                'host' => config('database.connections.'.config('database.default').'.host'),
                'error' => mb_substr($e->getMessage(), 0, 220),
            ]);
            return false;
        }

        if (! $user) {
            Log::warning('Auth login user not found', [
                'login' => $normalizedLogin,
                'connection' => config('database.default'),
                'database' => config('database.connections.'.config('database.default').'.database'),
            ]);
            $this->authDebug('user_not_found', [
                'login' => $normalizedLogin,
                'connection' => config('database.default'),
                'database' => config('database.connections.'.config('database.default').'.database'),
            ]);
            return false;
        }

        if (isset($user->ativo) && (int) $user->ativo !== 1) {
            Log::warning('Auth login user inactive', [
                'login' => $normalizedLogin,
                'user_id' => $user->id,
            ]);
            $this->authDebug('user_inactive', [
                'login' => $normalizedLogin,
                'user_id' => $user->id,
            ]);
            return false;
        }

        $storedHash = (string) ($user->password ?? '');
        if ($storedHash === '' || ! Hash::check($password, $storedHash)) {
            Log::warning('Auth login password mismatch', [
                'login' => $normalizedLogin,
                'user_id' => $user->id,
            ]);
            $this->authDebug('password_mismatch', [
                'login' => $normalizedLogin,
                'user_id' => $user->id,
                'hash_prefix' => substr($storedHash, 0, 4),
            ]);
            return false;
        }

        try {
            $user->last_login_at = now();
            $user->save();
        } catch (\Throwable) {
            // Last-login update must not block authentication.
        }

        Auth::login($user, $this->boolean('remember'));

        Log::info('Auth login success', [
            'login' => $normalizedLogin,
            'user_id' => $user->id,
        ]);
        $this->authDebug('login_success', [
            'login' => $normalizedLogin,
            'user_id' => $user->id,
        ]);

        return true;
    }

    private function attemptEnvDemoLogin(string $normalizedLogin, string $password): bool
    {
        $credentialPairs = [
            [env('DEMO_LOGIN', ''), env('DEMO_PASSWORD', '')],
            [env('DEMO_LOGIN_2', ''), env('DEMO_PASSWORD_2', '')],
            [env('DEMO_LOGIN_3', ''), env('DEMO_PASSWORD_3', '')],
        ];

        foreach ($credentialPairs as [$envLoginRaw, $envPasswordRaw]) {
            $envLogin = Str::lower(trim((string) $envLoginRaw));
            $envPassword = (string) $envPasswordRaw;

            if ($envLogin === '' || $envPassword === '') {
                continue;
            }

            if (! hash_equals($envLogin, $normalizedLogin)) {
                continue;
            }

            $sessionUser = User::query()
                ->whereRaw('LOWER(login) = ?', [$envLogin])
                ->orWhere('email', $envLogin.'@demo.local')
                ->first() ?? new User();
            $matchesLocalHash = $sessionUser->exists
                && is_string($sessionUser->password ?? null)
                && $sessionUser->password !== ''
                && Hash::check($password, (string) $sessionUser->password);
            $matchesEnv = hash_equals($envPassword, $password);

            if (! $matchesLocalHash && ! $matchesEnv) {
                continue;
            }

            $sessionUser->name = $envLogin;
            $sessionUser->login = $sessionUser->login ?: $envLogin;
            $sessionUser->email = $sessionUser->email ?: ($envLogin.'@demo.local');
            $sessionUser->equipe_id = $sessionUser->equipe_id ?: 1;
            $sessionUser->role_id = $sessionUser->role_id ?: (Str::lower($envLogin) === self::MASTER_LOGIN ? 1 : 3);
            $sessionUser->ativo = $sessionUser->ativo ?? 1;

            // Mantem a senha local caso ela ja tenha sido alterada pelo usuario.
            if (! $sessionUser->exists || empty($sessionUser->password)) {
                $sessionUser->password = $password;
            }

            $sessionUser->save();

            Auth::login($sessionUser, $this->boolean('remember'));

            return true;
        }

        return false;
    }

    private function ensurePermissionsConfigForUser(string $login, string $existingJson): void
    {
        if (trim($existingJson) !== '') {
            return;
        }

        $json = Str::lower($login) === self::MASTER_LOGIN
            ? self::MASTER_PERMISSIONS_JSON
            : self::DEFAULT_PERMISSIONS_JSON;

        try {
            DB::connection('lumia_sqlsrv')
                ->table('lumia_auth_users')
                ->whereRaw('LOWER(login) = ?', [Str::lower($login)])
                ->update([
                    'permissions_config_json' => $json,
                ]);
        } catch (\Throwable) {
            // Permission bootstrap failure must not block login.
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}

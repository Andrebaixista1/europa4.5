<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MASTER_LOGIN = 'andrefelipe';
    private const DEFAULT_PERMISSIONS_JSON = '{"dashboard":true,"settings.users":false,"settings.permissions":false}';
    private const MASTER_PERMISSIONS_JSON = '{"dashboard":true,"settings.users":true,"settings.permissions":true}';

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
        } catch (\Throwable) {
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

    private function attemptEnvDemoLogin(string $normalizedLogin, string $password): bool
    {
        $envLogin = Str::lower(trim((string) env('DEMO_LOGIN', '')));
        $envPassword = (string) env('DEMO_PASSWORD', '');

        if ($envLogin === '' || $envPassword === '') {
            return false;
        }

        if (! hash_equals($envLogin, $normalizedLogin) || ! hash_equals($envPassword, $password)) {
            return false;
        }

        $sessionUser = User::updateOrCreate(
            ['email' => $envLogin.'@demo.local'],
            [
                'name' => $envLogin,
                // Autenticacao validada por credencial em .env para ambiente de teste.
                'password' => Hash::make(Str::random(40)),
            ]
        );

        Auth::login($sessionUser, $this->boolean('remember'));

        return true;
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

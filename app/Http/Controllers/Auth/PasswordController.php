<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Informe a senha atual.',
            'password.required' => 'Informe a nova senha.',
            'password.min' => 'A nova senha deve ter no minimo 6 caracteres.',
            'password.confirmed' => 'A confirmacao da senha nao confere.',
        ]);

        $login = Str::lower(trim((string) $request->user()?->name));
        if ($login === '') {
            $exception = ValidationException::withMessages([
                'current_password' => 'Falha de autenticacao do usuario atual.',
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        if ($this->canUpdatePasswordOnCurrentUser($request, $validated)) {
            $this->updatePasswordForDemoUser($request, $validated);

            return back()->with('status', 'password-updated');
        }

        try {
            $remoteUser = DB::connection('lumia_sqlsrv')
                ->table('lumia_auth_users')
                ->select(['id', 'login', 'password_sha256'])
                ->whereRaw('LOWER(login) = ?', [$login])
                ->first();
        } catch (\Throwable) {
            $exception = ValidationException::withMessages([
                'current_password' => 'Nao foi possivel validar a senha atual no servidor.',
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        $currentPasswordSha256 = hash('sha256', (string) $validated['current_password']);
        if (! $remoteUser || ! hash_equals((string) $remoteUser->password_sha256, $currentPasswordSha256)) {
            $exception = ValidationException::withMessages([
                'current_password' => 'A senha atual esta incorreta.',
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        $newPasswordSha256 = hash('sha256', (string) $validated['password']);
        $updatedRows = 0;

        try {
            $updatedRows = DB::connection('lumia_sqlsrv')
                ->table('lumia_auth_users')
                ->where('id', (int) $remoteUser->id)
                ->update([
                    'password_sha256' => $newPasswordSha256,
                    'updated_at' => now(),
                ]);
        } catch (\Throwable) {
            try {
                $updatedRows = DB::connection('lumia_sqlsrv')
                    ->table('lumia_auth_users')
                    ->where('id', (int) $remoteUser->id)
                    ->update([
                        'password_sha256' => $newPasswordSha256,
                    ]);
            } catch (\Throwable) {
                $exception = ValidationException::withMessages([
                    'password' => 'Nao foi possivel atualizar a senha no servidor.',
                ]);
                $exception->errorBag = 'updatePassword';
                throw $exception;
            }
        }

        if ($updatedRows === 0 && ! hash_equals((string) $remoteUser->password_sha256, $newPasswordSha256)) {
            $exception = ValidationException::withMessages([
                'password' => 'A senha nao foi atualizada. Tente novamente.',
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        return back()->with('status', 'password-updated');
    }

    private function canUpdatePasswordOnCurrentUser(Request $request, array $validated): bool
    {
        $user = $request->user();
        if (! $user) {
            return false;
        }

        $currentPassword = (string) ($validated['current_password'] ?? '');
        $userHash = (string) ($user->password ?? '');
        if ($userHash !== '' && Hash::check($currentPassword, $userHash)) {
            return true;
        }

        $email = Str::lower(trim((string) ($user->email ?? '')));
        if ($email === '' || ! Str::endsWith($email, '@demo.local')) {
            return false;
        }

        $login = Str::lower(trim((string) ($user->name ?? '')));
        $envPassword = $this->demoPasswordForLogin($login);

        return $envPassword !== null && hash_equals($envPassword, $currentPassword);
    }

    private function updatePasswordForDemoUser(Request $request, array $validated): void
    {
        $user = $request->user();
        $login = Str::lower(trim((string) ($user?->name ?? '')));

        if (! $user || $login === '') {
            $exception = ValidationException::withMessages([
                'current_password' => 'Falha de autenticacao do usuario atual.',
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        $currentPassword = (string) $validated['current_password'];
        $envPassword = $this->demoPasswordForLogin($login);

        $matchesLocalHash = is_string($user->password ?? null)
            && $user->password !== ''
            && Hash::check($currentPassword, (string) $user->password);

        $matchesEnv = $envPassword !== null && hash_equals($envPassword, $currentPassword);

        if (! $matchesLocalHash && ! $matchesEnv) {
            $exception = ValidationException::withMessages([
                'current_password' => 'A senha atual esta incorreta.',
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        $user->password = (string) $validated['password'];
        $user->save();
    }

    private function demoPasswordForLogin(string $login): ?string
    {
        $pairs = [
            [env('DEMO_LOGIN', ''), env('DEMO_PASSWORD', '')],
            [env('DEMO_LOGIN_2', ''), env('DEMO_PASSWORD_2', '')],
            [env('DEMO_LOGIN_3', ''), env('DEMO_PASSWORD_3', '')],
        ];

        foreach ($pairs as [$envLoginRaw, $envPasswordRaw]) {
            $envLogin = Str::lower(trim((string) $envLoginRaw));
            $envPassword = (string) $envPasswordRaw;

            if ($envLogin === '' || $envPassword === '') {
                continue;
            }

            if (hash_equals($envLogin, $login)) {
                return $envPassword;
            }
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
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
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $login = Str::lower(trim((string) $request->user()?->name));
        if ($login === '') {
            $exception = ValidationException::withMessages([
                'current_password' => __('auth.failed'),
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        try {
            $remoteUser = DB::connection('lumia_sqlsrv')
                ->table('lumia_auth_users')
                ->select(['login', 'password_sha256'])
                ->whereRaw('LOWER(login) = ?', [$login])
                ->first();
        } catch (\Throwable) {
            $exception = ValidationException::withMessages([
                'current_password' => __('auth.failed'),
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        $currentPasswordSha256 = hash('sha256', (string) $validated['current_password']);
        if (! $remoteUser || ! hash_equals((string) $remoteUser->password_sha256, $currentPasswordSha256)) {
            $exception = ValidationException::withMessages([
                'current_password' => __('auth.password'),
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        try {
            DB::connection('lumia_sqlsrv')
                ->table('lumia_auth_users')
                ->whereRaw('LOWER(login) = ?', [$login])
                ->update([
                    'password_sha256' => hash('sha256', (string) $validated['password']),
                ]);
        } catch (\Throwable) {
            $exception = ValidationException::withMessages([
                'password' => __('auth.failed'),
            ]);
            $exception->errorBag = 'updatePassword';
            throw $exception;
        }

        return back()->with('status', 'password-updated');
    }
}

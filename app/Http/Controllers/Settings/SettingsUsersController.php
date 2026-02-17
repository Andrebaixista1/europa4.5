<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingsUsersController extends Controller
{
    private const SESSION_UNLOCK_KEY = 'settings_users_password_unlocked_until';

    public function index(Request $request): View
    {
        $login = Str::lower(trim((string) $request->user()?->name));
        $queryError = null;
        $remoteUser = null;

        try {
            $remoteUser = DB::connection('lumia_sqlsrv')
                ->table('lumia_auth_users')
                ->select(['id', 'login', 'password_sha256', 'created_at', 'updated_at'])
                ->whereRaw('LOWER(login) = ?', [$login])
                ->first();
        } catch (\Throwable) {
            $queryError = 'Nao foi possivel carregar os dados de usuarios no momento.';
        }

        $unlockUntil = (int) $request->session()->get(self::SESSION_UNLOCK_KEY, 0);
        $unlockRemainingSeconds = max(0, $unlockUntil - now()->timestamp);
        $passwordUnlocked = $unlockRemainingSeconds > 0;

        return view('settings.users', [
            'queryError' => $queryError,
            'remoteUser' => $remoteUser,
            'passwordUnlocked' => $passwordUnlocked,
            'unlockRemainingSeconds' => $unlockRemainingSeconds,
            'createdAtLabel' => $this->formatDateTimeLabel($remoteUser?->created_at),
            'updatedAtLabel' => $this->formatDateTimeLabel($remoteUser?->updated_at),
        ]);
    }

    public function unlockPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'master_password' => ['required', 'string'],
        ], [
            'master_password.required' => 'Informe a senha atual do master.',
        ]);

        try {
            $masterUser = DB::connection('lumia_sqlsrv')
                ->table('lumia_auth_users')
                ->select(['id', 'password_sha256'])
                ->where('id', 1)
                ->first();
        } catch (\Throwable) {
            return back()->withErrors([
                'master_password' => 'Falha ao validar a senha master.',
            ]);
        }

        $candidateSha256 = hash('sha256', (string) $validated['master_password']);
        if (! $masterUser || ! hash_equals((string) $masterUser->password_sha256, $candidateSha256)) {
            return back()->withErrors([
                'master_password' => 'Senha master invalida.',
            ]);
        }

        $request->session()->put(self::SESSION_UNLOCK_KEY, now()->addMinutes(2)->timestamp);

        return back()->with('status', 'users-password-unlocked');
    }

    private function formatDateTimeLabel(mixed $value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            return Carbon::parse((string) $value)
                ->timezone('America/Sao_Paulo')
                ->format('d/m/Y H:i:s');
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}

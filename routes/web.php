<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\SettingsPermissionsController;
use App\Http\Controllers\Settings\SettingsUsersController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::redirect('/', '/login');

Route::get('/api/front/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
    ]);
})->name('api.front.csrf');

Route::post('/api/front/login', function (Request $request) {
    $validated = $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required', 'string'],
        'remember' => ['nullable', 'boolean'],
    ]);

    $remember = (bool) ($validated['remember'] ?? false);
    $normalizedLogin = Str::lower(trim((string) $validated['login']));
    $password = (string) $validated['password'];

    $authenticated = Auth::attempt([
        'login' => trim((string) $validated['login']),
        'password' => $password,
    ], $remember);

    if (! $authenticated) {
        $demoAdminLogin = Str::lower(trim((string) env('DEMO_ADMIN_LOGIN', 'admin.demo')));
        $demoAdminName = trim((string) env('DEMO_ADMIN_NAME', 'Admin Demo'));
        $credentialPairs = [
            [env('DEMO_LOGIN', ''), env('DEMO_PASSWORD', '')],
            [env('DEMO_LOGIN_2', ''), env('DEMO_PASSWORD_2', '')],
            [env('DEMO_LOGIN_3', ''), env('DEMO_PASSWORD_3', '')],
            [env('DEMO_ADMIN_LOGIN', ''), env('DEMO_ADMIN_PASSWORD', '')],
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

            $isDemoAdmin = $demoAdminLogin !== '' && hash_equals($demoAdminLogin, $envLogin);
            $masterRoleId = null;

            if ($isDemoAdmin) {
                try {
                    $masterRoleId = DB::table('roles')
                        ->whereRaw('LOWER(slug) = ?', ['master'])
                        ->value('id');
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            $sessionUser->name = $isDemoAdmin ? ($demoAdminName !== '' ? $demoAdminName : $envLogin) : $envLogin;
            $sessionUser->login = $sessionUser->login ?: $envLogin;
            $sessionUser->email = $sessionUser->email ?: ($envLogin.'@demo.local');
            $sessionUser->equipe_id = $sessionUser->equipe_id ?: 1;

            if ($isDemoAdmin) {
                $sessionUser->role_id = $masterRoleId !== null ? (int) $masterRoleId : ($sessionUser->role_id ?: 1);
            } else {
                $sessionUser->role_id = $sessionUser->role_id ?: 3;
            }

            $sessionUser->ativo = $sessionUser->ativo ?? 1;

            if (! $sessionUser->exists || empty($sessionUser->password)) {
                $sessionUser->password = $password;
            }

            $sessionUser->save();

            Auth::login($sessionUser, $remember);
            $request->session()->put('is_demo_admin', $isDemoAdmin);
            $authenticated = true;

            break;
        }
    } else {
        $request->session()->forget('is_demo_admin');
    }

    if (! $authenticated) {
        return response()->json([
            'message' => 'Credenciais invalidas.',
        ], 422);
    }

    $request->session()->regenerate();

    $user = $request->user();

    return response()->json([
        'message' => 'Login realizado com sucesso.',
        'user' => [
            'id' => (int) ($user?->id ?? 0),
            'name' => (string) ($user?->name ?? ''),
            'email' => (string) ($user?->email ?? ''),
            'login' => (string) ($user?->login ?? ''),
        ],
    ]);
})->middleware('guest')->name('api.front.login');

Route::middleware('auth')->group(function () {
    Route::get('/api/front/me', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => (int) ($user?->id ?? 0),
                'name' => (string) ($user?->name ?? ''),
                'email' => (string) ($user?->email ?? ''),
                'login' => (string) ($user?->login ?? ''),
                'equipe_id' => $user?->equipe_id !== null ? (int) $user->equipe_id : null,
                'role_id' => $user?->role_id !== null ? (int) $user->role_id : null,
            ],
        ]);
    })->name('api.front.me');

    Route::post('/api/front/logout', function (Request $request) {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    })->name('api.front.logout');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    $usingBridgeProvider = static function (): bool {
        return strtolower(trim((string) config('auth.providers.users.driver', ''))) === 'bridge';
    };

    $callSettingsBridge = static function (string $action, array $payload = []) use ($usingBridgeProvider): ?array {
        if (! $usingBridgeProvider()) {
            return null;
        }

        $endpoint = trim((string) config('services.auth_bridge.url', ''));
        $token = trim((string) config('services.auth_bridge.token', ''));
        $timeout = max(1, (int) config('services.auth_bridge.timeout', 8));

        if ($endpoint === '' || $token === '') {
            return null;
        }

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'X-Bridge-Token' => $token,
                ])
                ->post($endpoint, array_merge(['action' => $action], $payload));
        } catch (\Throwable $e) {
            report($e);
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $json = $response->json();

        return is_array($json) ? $json : null;
    };

    $resolveSettingsScope = static function (Request $request) use ($usingBridgeProvider): array {
        $authUser = $request->user();
        $authUserId = (int) ($authUser?->id ?? 0);
        $authTeamId = $authUser?->equipe_id !== null ? (int) $authUser->equipe_id : null;
        $authRoleId = $authUser?->role_id !== null ? (int) $authUser->role_id : null;

        $authRoleSlug = '';
        if ($usingBridgeProvider()) {
            $authRoleSlug = (string) ($authUser?->role_slug ?? '');
        } elseif ($authRoleId !== null) {
            try {
                $authRoleSlug = (string) (DB::table('roles')
                    ->where('id', $authRoleId)
                    ->value('slug') ?? '');
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $authRoleSlug = strtolower(trim($authRoleSlug));
        $mode = match ($authRoleSlug) {
            'master' => 'all',
            'administrador', 'administrator', 'admin', 'supervisor' => 'team',
            default => 'self',
        };

        return [
            'mode' => $mode,
            'user_id' => $authUserId > 0 ? $authUserId : null,
            'team_id' => $authTeamId,
            'role_id' => $authRoleId,
            'role_slug' => $authRoleSlug,
        ];
    };

    $resolveDemoAdminLogin = static function (): string {
        return Str::lower(trim((string) env('DEMO_ADMIN_LOGIN', 'admin.demo')));
    };

    $isDemoAdminRequest = static function (Request $request) use ($resolveDemoAdminLogin): bool {
        $demoAdminLogin = $resolveDemoAdminLogin();
        if ($demoAdminLogin === '') {
            return false;
        }

        if ((bool) $request->session()->get('is_demo_admin', false)) {
            return true;
        }

        $authUser = $request->user();
        $login = Str::lower(trim((string) ($authUser?->login ?? $authUser?->name ?? '')));

        return $login !== '' && hash_equals($demoAdminLogin, $login);
    };

    $demoNoopResponse = static function (string $message = 'Modo demo: acao simulada. Nenhuma alteracao foi salva.') {
        return response()->json([
            'message' => $message,
            'demo' => true,
        ]);
    };

    $canManageUserByScope = static function (array $scope, ?int $targetUserId, ?int $targetTeamId): bool {
        if ($targetUserId === null || $targetUserId <= 0) {
            return false;
        }

        return match ((string) ($scope['mode'] ?? 'self')) {
            'all' => true,
            'team' => (string) ($scope['team_id'] ?? '') === (string) ($targetTeamId ?? ''),
            default => (int) ($scope['user_id'] ?? 0) === $targetUserId,
        };
    };

    $authHasPermission = static function (Request $request, string $permissionSlug) use ($resolveSettingsScope, $callSettingsBridge, $usingBridgeProvider, $isDemoAdminRequest): bool {
        if ($isDemoAdminRequest($request)) {
            return true;
        }

        $scope = $resolveSettingsScope($request);
        if ((string) ($scope['role_slug'] ?? '') === 'master') {
            return true;
        }

        $roleId = $scope['role_id'] !== null ? (int) $scope['role_id'] : 0;
        if ($roleId <= 0 || trim($permissionSlug) === '') {
            return false;
        }

        if ($usingBridgeProvider()) {
            $payload = $callSettingsBridge('has_permission', [
                'role_id' => $roleId,
                'role_slug' => (string) ($scope['role_slug'] ?? ''),
                'permission_slug' => trim($permissionSlug),
            ]);

            return (bool) ($payload['ok'] ?? false) && (bool) ($payload['allowed'] ?? false);
        }

        try {
            return DB::table('role_permissions as rp')
                ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
                ->where('rp.role_id', $roleId)
                ->where('rp.allowed', 1)
                ->where('p.slug', $permissionSlug)
                ->exists();
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    };

    $ensureSettingsPermissionsCatalog = static function () use ($callSettingsBridge, $usingBridgeProvider): void {
        if ($usingBridgeProvider()) {
            $callSettingsBridge('ensure_permissions_catalog');
            return;
        }

        $requiredPermissions = [
            [
                'slug' => 'consulta_cliente.view',
                'nome' => 'Ver',
                'modulo' => 'consulta_cliente',
            ],
        ];

        foreach ($requiredPermissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                [
                    'nome' => $permission['nome'],
                    'modulo' => $permission['modulo'],
                ]
            );
        }
    };

    $consultaMacicaByCpf = static function (string $cpfDigits): array {
        $cpfOnlyDigits = preg_replace('/\D+/', '', $cpfDigits) ?? '';
        if ($cpfOnlyDigits === '') {
            return [];
        }

        $cpfConsulta = str_pad(substr($cpfOnlyDigits, 0, 11), 11, '0', STR_PAD_LEFT);

        $rows = DB::connection('ct_top_sqlsrv')->select(
            <<<'SQL'
SELECT TOP (1000)
      [nb]
    , [nome_segurado]
    , [dt_nascimento]
    , [nu_cpf]
    , [esp]
    , [dib]
    , [ddb]
    , [vl_beneficio]
    , [id_banco_pagto]
    , [id_agencia_banco]
    , [id_orgao_pagador]
    , [nu_conta_corrente]
    , [aps_benef]
    , [cs_meio_pagto]
    , [id_banco_empres]
    , [id_contrato_empres]
    , [vl_empres]
    , [comp_ini_desconto]
    , [comp_fim_desconto]
    , [quant_parcelas]
    , [vl_parcela]
    , [tipo_empres]
    , [endereco]
    , [bairro]
    , [municipio]
    , [uf]
    , [cep]
    , [situacao_empres]
    , [dt_averbacao_consig]
    , [idade]
    , [pagas]
    , [restantes]
    , [nb_tratado]
    , [dt_nascimento_tratado]
    , [nu_cpf_tratado]
    , [vl_beneficio_tratado]
    , [comp_ini_desconto_tratado]
    , [comp_fim_desconto_tratado]
    , [quant_parcelas_tratado]
    , [vl_parcela_tratado]
    , [vl_empres_tratado]
    , [data_update]
    , [nu_cpf_ix]
    , [nb_ix]
FROM [MacicaCompleta].[dbo].[consignados_unificados_TEXT]
WHERE [nu_cpf_ix] = ?
SQL,
            [$cpfConsulta]
        );

        return array_values(array_map(static fn ($row): array => (array) $row, $rows));
    };

    $consultaEntrantesByCpf = static function (string $cpfDigits): array {
        $cpfOnlyDigits = preg_replace('/\D+/', '', $cpfDigits) ?? '';
        if ($cpfOnlyDigits === '') {
            return [];
        }

        $cpfConsulta = str_pad(substr($cpfOnlyDigits, 0, 11), 11, '0', STR_PAD_LEFT);

        $rows = DB::connection('ct_top_sqlsrv')->select(
            <<<'SQL'
SELECT TOP (1000)
      [NOME]
    , [CPF]
    , [IDADE]
    , [Data_Nascimento]
    , [Beneficio]
    , [CODIGO_ESPECIE]
    , [DDB]
    , [Municipio]
    , [UF]
    , [VALOR_BENEFICIO]
    , [MARGEM_RMC]
    , [MARGEM_DISPONIVEL]
    , [Margem_RCC]
    , [Banco]
    , [Agencia]
    , [Conta]
    , [Meio_Pagamento]
    , [CELULAR1]
    , [CELULAR2]
    , [CELULAR3]
    , [CPF_LIMPO]
    , [BENEFICIO_LIMPO]
    , [CELULAR4]
    , [Data_Lemit]
    , [valor_liberador_RCC]
    , [valor_liberador_RMC]
    , [Total_Valor_Liberado(0.02801)]
    , [total_cartao]
    , [Total_Valor_Liberado]
FROM [Mailing].[dbo].[MAILING_UNIFICADO]
WHERE [CPF_LIMPO] = ?
SQL,
            [$cpfConsulta]
        );

        return array_values(array_map(static fn ($row): array => (array) $row, $rows));
    };

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/consultas/cliente', function (Request $request) use ($authHasPermission, $consultaMacicaByCpf, $consultaEntrantesByCpf) {
        if (! $authHasPermission($request, 'consulta_cliente.view')) {
            abort(403);
        }

        $cpfInput = trim((string) $request->query('cpf', ''));
        $cpfDigits = preg_replace('/\D+/', '', $cpfInput) ?? '';
        $consultaModulesInput = trim((string) $request->query('consultas', ''));
        $allowedModuleKeys = ['macica', 'entrantes', 'consulta_in100', 'presenca', 'hand_mais', 'prata', 'v8'];
        $moduleLabelMap = [
            'macica' => 'Maciça',
            'entrantes' => 'Entrantes',
            'consulta_in100' => 'IN100 Qualibanking',
            'presenca' => 'Presença',
            'hand_mais' => 'Hand+',
            'prata' => 'Prata',
            'v8' => 'V8',
        ];
        $consultaSelectedModules = collect(explode(',', $consultaModulesInput))
            ->map(static fn ($item) => strtolower(trim((string) $item)))
            ->filter(static fn ($item) => $item !== '' && in_array($item, $allowedModuleKeys, true))
            ->unique()
            ->values()
            ->all();
        $cpfConsulta = '';
        $consultaError = '';
        $consultaRows = [];
        $consultaRaw = null;

        if ($cpfDigits !== '') {
            if (strlen($cpfDigits) > 11) {
                $consultaError = 'Informe um CPF valido com ate 11 digitos.';
            } else {
                $cpfConsulta = str_pad($cpfDigits, 11, '0', STR_PAD_LEFT);
            }
        }

        if ($cpfConsulta !== '' && $consultaError === '' && $consultaRows === []) {
            if ($consultaSelectedModules === []) {
                $consultaError = 'Selecione pelo menos uma consulta antes de consultar.';
            }
        }

        if ($cpfConsulta !== '' && $consultaError === '' && $consultaRows === []) {
            try {
                $rowsByModule = [];
                $notImplementedModules = [];

                foreach ($consultaSelectedModules as $moduleKey) {
                    $moduleLabel = $moduleLabelMap[$moduleKey] ?? $moduleKey;
                    $moduleRows = [];

                    if ($moduleKey === 'macica') {
                        $moduleRows = $consultaMacicaByCpf($cpfConsulta);
                    } elseif ($moduleKey === 'entrantes') {
                        $moduleRows = $consultaEntrantesByCpf($cpfConsulta);
                    } else {
                        $notImplementedModules[] = $moduleKey;
                    }

                    if ($moduleRows !== []) {
                        $rowsByModule[$moduleKey] = array_values(array_map(static function ($row) use ($moduleLabel): array {
                            $arrayRow = (array) $row;
                            $arrayRow['_modulo_consulta'] = $moduleLabel;

                            return $arrayRow;
                        }, $moduleRows));
                    } else {
                        $rowsByModule[$moduleKey] = [];
                    }
                }

                $consultaRows = collect($rowsByModule)
                    ->flatten(1)
                    ->values()
                    ->all();

                $consultaRaw = [
                    'source' => 'ct_top_sqlsrv.multi',
                    'cpf' => $cpfConsulta,
                    'selected_modules' => $consultaSelectedModules,
                    'not_implemented_modules' => $notImplementedModules,
                    'rows_by_module' => $rowsByModule,
                    'total' => count($consultaRows),
                    'rows' => $consultaRows,
                ];
            } catch (\Throwable $e) {
                report($e);
                $consultaError = 'Nao foi possivel concluir a consulta agora.';
            }
        }

        return view('consultas.cliente', [
            'cpfInput' => $cpfInput,
            'cpfConsulta' => $cpfConsulta,
            'consultaRows' => $consultaRows,
            'consultaError' => $consultaError,
            'consultaRaw' => $consultaRaw,
            'consultaSelectedModules' => $consultaSelectedModules,
        ]);
    })->name('consultas.cliente');

    Route::get('/api/consultas/macica', function (Request $request) use ($authHasPermission, $consultaMacicaByCpf) {
        if (! $authHasPermission($request, 'consulta_cliente.view')) {
            return response()->json([
                'message' => 'Voce nao tem permissao para consultar Macica.',
            ], 403);
        }

        $cpfInput = trim((string) $request->query('cpf', ''));
        $cpfDigits = preg_replace('/\D+/', '', $cpfInput) ?? '';

        if ($cpfDigits === '' || strlen($cpfDigits) > 11) {
            return response()->json([
                'message' => 'Informe um CPF valido com ate 11 digitos.',
            ], 422);
        }

        $cpfConsulta = str_pad($cpfDigits, 11, '0', STR_PAD_LEFT);

        try {
            $rows = $consultaMacicaByCpf($cpfConsulta);

            return response()->json([
                'source' => 'ct_top_sqlsrv.macica',
                'cpf' => $cpfConsulta,
                'total' => count($rows),
                'rows' => $rows,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Nao foi possivel consultar Macica agora.',
            ], 500);
        }
    })->name('api.consultas.macica');

    Route::get('/api/consultas/entrantes', function (Request $request) use ($authHasPermission, $consultaEntrantesByCpf) {
        if (! $authHasPermission($request, 'consulta_cliente.view')) {
            return response()->json([
                'message' => 'Voce nao tem permissao para consultar Entrantes.',
            ], 403);
        }

        $cpfInput = trim((string) $request->query('cpf', ''));
        $cpfDigits = preg_replace('/\D+/', '', $cpfInput) ?? '';

        if ($cpfDigits === '' || strlen($cpfDigits) > 11) {
            return response()->json([
                'message' => 'Informe um CPF valido com ate 11 digitos.',
            ], 422);
        }

        $cpfConsulta = str_pad($cpfDigits, 11, '0', STR_PAD_LEFT);

        try {
            $rows = $consultaEntrantesByCpf($cpfConsulta);

            return response()->json([
                'source' => 'ct_top_sqlsrv.entrantes',
                'cpf' => $cpfConsulta,
                'total' => count($rows),
                'rows' => $rows,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Nao foi possivel consultar Entrantes agora.',
            ], 500);
        }
    })->name('api.consultas.entrantes');

    Route::post('/configuracoes/permissoes/salvar', function (Request $request) use ($ensureSettingsPermissionsCatalog, $isDemoAdminRequest, $demoNoopResponse) {
        if ($isDemoAdminRequest($request)) {
            return $demoNoopResponse('Modo demo: permissoes simuladas com sucesso.');
        }

        $ensureSettingsPermissionsCatalog();

        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['boolean'],
        ]);

        $roleId = (int) $validated['role_id'];
        $incomingPermissions = (array) $validated['permissions'];

        $role = DB::table('roles')
            ->select(['id', 'slug'])
            ->where('id', $roleId)
            ->first();

        if (! $role) {
            return response()->json([
                'message' => 'Perfil nao encontrado.',
            ], 404);
        }

        $roleSlug = strtolower(trim((string) ($role->slug ?? '')));
        $isMasterRole = $roleSlug === 'master';

        $permissionIdsBySlug = DB::table('permissions')
            ->pluck('id', 'slug')
            ->all();

        $existingScopesByPermission = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->pluck('scope', 'permission_id')
            ->all();

        $defaultScope = match ($roleSlug) {
            'master' => 'all',
            'supervisor' => 'team',
            'operador' => 'self',
            default => 'all',
        };

        $now = now();

        DB::transaction(function () use (
            $permissionIdsBySlug,
            $incomingPermissions,
            $existingScopesByPermission,
            $roleId,
            $defaultScope,
            $isMasterRole,
            $now
        ): void {
            foreach ($permissionIdsBySlug as $permissionSlug => $permissionId) {
                $allowed = $isMasterRole
                    ? true
                    : (bool) ($incomingPermissions[$permissionSlug] ?? false);

                $updatedRows = DB::table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', (int) $permissionId)
                    ->update([
                        'allowed' => $allowed,
                        'updated_at' => $now,
                    ]);

                if ($updatedRows > 0) {
                    continue;
                }

                DB::table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => (int) $permissionId,
                    'allowed' => $allowed,
                    'scope' => (string) ($existingScopesByPermission[(int) $permissionId] ?? $defaultScope),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        return response()->json([
            'message' => 'Permissoes salvas no banco com sucesso.',
        ]);
    })->name('settings.permissions.role.save');

    Route::post('/configuracoes/equipes/renomear', function (Request $request) use ($authHasPermission, $isDemoAdminRequest, $demoNoopResponse) {
        if ($isDemoAdminRequest($request)) {
            return $demoNoopResponse('Modo demo: renomeacao de equipe simulada.');
        }

        if (! $authHasPermission($request, 'equipes.edit')) {
            return response()->json([
                'message' => 'Voce nao tem permissao para editar equipes.',
            ], 403);
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:equipes,id'],
            'nome' => ['required', 'string', 'max:120'],
        ]);

        $teamId = (int) $validated['team_id'];
        $teamName = trim((string) $validated['nome']);

        if ($teamName === '') {
            return response()->json([
                'message' => 'Informe um nome valido para a equipe.',
            ], 422);
        }

        DB::table('equipes')
            ->where('id', $teamId)
            ->update([
                'nome' => $teamName,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Equipe atualizada com sucesso.',
        ]);
    })->name('settings.teams.rename');

    Route::post('/configuracoes/equipes/excluir', function (Request $request) use ($resolveSettingsScope, $isDemoAdminRequest, $demoNoopResponse) {
        if ($isDemoAdminRequest($request)) {
            return $demoNoopResponse('Modo demo: exclusao de equipe simulada.');
        }

        $scope = $resolveSettingsScope($request);
        if ((string) ($scope['mode'] ?? '') !== 'all') {
            return response()->json([
                'message' => 'Apenas Master pode excluir equipes.',
            ], 403);
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:equipes,id'],
        ]);

        $teamId = (int) $validated['team_id'];
        $authUserTeamId = $request->user()?->equipe_id !== null
            ? (int) $request->user()->equipe_id
            : null;

        if ($authUserTeamId !== null && $authUserTeamId === $teamId) {
            return response()->json([
                'message' => 'Nao e permitido excluir a propria equipe.',
            ], 422);
        }

        DB::transaction(function () use ($teamId): void {
            DB::table('users')
                ->where('equipe_id', $teamId)
                ->update([
                    'equipe_id' => null,
                    'updated_at' => now(),
                ]);

            DB::table('equipes')
                ->where('id', $teamId)
                ->delete();
        });

        return response()->json([
            'message' => 'Equipe excluida com sucesso.',
        ]);
    })->name('settings.teams.delete');

    Route::post('/configuracoes/equipes/criar', function (Request $request) use ($resolveSettingsScope, $isDemoAdminRequest, $demoNoopResponse) {
        if ($isDemoAdminRequest($request)) {
            return $demoNoopResponse('Modo demo: criacao de equipe simulada.');
        }

        $scope = $resolveSettingsScope($request);
        if ((string) ($scope['mode'] ?? '') !== 'all') {
            return response()->json([
                'message' => 'Apenas Master pode criar equipes.',
            ], 403);
        }

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'supervisor_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $teamName = trim((string) ($validated['nome'] ?? ''));
        if ($teamName === '') {
            return response()->json([
                'message' => 'Informe um nome valido para a equipe.',
            ], 422);
        }

        $teamName = Str::ucfirst($teamName);
        $supervisorUserId = $validated['supervisor_user_id'] !== null ? (int) $validated['supervisor_user_id'] : null;
        $userIds = collect((array) ($validated['user_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($teamName, $supervisorUserId, $userIds): void {
            $teamId = DB::table('equipes')->insertGetId([
                'nome' => $teamName,
                'supervisor_user_id' => $supervisorUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! empty($userIds)) {
                DB::table('users')
                    ->whereIn('id', $userIds)
                    ->update([
                        'equipe_id' => (int) $teamId,
                        'updated_at' => now(),
                    ]);
            }
        });

        return response()->json([
            'message' => 'Equipe criada com sucesso.',
        ]);
    })->name('settings.teams.create');

    Route::post('/configuracoes/usuarios/criar', function (Request $request) use ($resolveSettingsScope, $authHasPermission, $isDemoAdminRequest, $demoNoopResponse) {
        if ($isDemoAdminRequest($request)) {
            return $demoNoopResponse('Modo demo: criacao de usuario simulada.');
        }

        $scope = $resolveSettingsScope($request);
        if (! $authHasPermission($request, 'users.create')) {
            return response()->json([
                'message' => 'Voce nao tem permissao para criar usuarios.',
            ], 403);
        }

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'senha' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'equipe_id' => ['nullable', 'integer', 'exists:equipes,id'],
            'create_team' => ['nullable', 'boolean'],
            'nova_equipe' => ['nullable', 'array'],
            'nova_equipe.nome' => ['required_if:create_team,true', 'string', 'max:120'],
            'nova_equipe.supervisor_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'nova_equipe.user_ids' => ['nullable', 'array'],
            'nova_equipe.user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $userName = trim((string) ($validated['nome'] ?? ''));
        if ($userName === '') {
            return response()->json([
                'message' => 'Informe um nome valido para o usuario.',
            ], 422);
        }

        $password = (string) ($validated['senha'] ?? '');
        if (mb_strlen($password) < 6) {
            return response()->json([
                'message' => 'A senha deve ter pelo menos 6 caracteres.',
            ], 422);
        }

        $createTeam = (bool) ($validated['create_team'] ?? false);
        $teamId = $validated['equipe_id'] !== null ? (int) $validated['equipe_id'] : null;
        $newTeamName = null;

        if ((string) ($scope['mode'] ?? '') === 'self') {
            return response()->json([
                'message' => 'Operador nao pode criar usuarios.',
            ], 403);
        }

        if ((string) ($scope['mode'] ?? '') === 'team') {
            if ($createTeam) {
                return response()->json([
                    'message' => 'Administrador/Supervisor nao podem criar novas equipes.',
                ], 403);
            }

            if ((string) ($scope['team_id'] ?? '') !== (string) ($teamId ?? '')) {
                return response()->json([
                    'message' => 'Administrador/Supervisor podem criar apenas usuarios da propria equipe.',
                ], 403);
            }
        }

        if ($createTeam) {
            $newTeamName = trim((string) (($validated['nova_equipe']['nome'] ?? '')));
            if ($newTeamName === '') {
                return response()->json([
                    'message' => 'Informe um nome valido para a nova equipe.',
                ], 422);
            }
        }

        DB::transaction(function () use ($validated, $userName, $password, $createTeam, &$teamId, $newTeamName): void {
            if ($createTeam) {
                $teamName = Str::ucfirst((string) $newTeamName);
                $supervisorUserId = $validated['nova_equipe']['supervisor_user_id'] !== null
                    ? (int) $validated['nova_equipe']['supervisor_user_id']
                    : null;

                $teamId = DB::table('equipes')->insertGetId([
                    'nome' => $teamName,
                    'supervisor_user_id' => $supervisorUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $existingUserIds = collect((array) ($validated['nova_equipe']['user_ids'] ?? []))
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                if (! empty($existingUserIds)) {
                    DB::table('users')
                        ->whereIn('id', $existingUserIds)
                        ->update([
                            'equipe_id' => (int) $teamId,
                            'updated_at' => now(),
                        ]);
                }
            }

            $baseLogin = Str::of($userName)
                ->ascii()
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '')
                ->value();

            if ($baseLogin === '') {
                $baseLogin = 'usuario';
            }

            $login = $baseLogin;
            $counter = 2;
            while (DB::table('users')->whereRaw('LOWER(login) = ?', [$login])->exists()) {
                $login = $baseLogin.$counter;
                $counter++;
            }

            $email = $login.'@europa.local';
            $mailCounter = 2;
            while (DB::table('users')->whereRaw('LOWER(email) = ?', [Str::lower($email)])->exists()) {
                $email = $login.$mailCounter.'@europa.local';
                $mailCounter++;
            }

            $newUserId = DB::table('users')->insertGetId([
                'nome' => $userName,
                'login' => $login,
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => (int) $validated['role_id'],
                'equipe_id' => $teamId,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($teamId !== null) {
                DB::table('users')
                    ->where('id', (int) $newUserId)
                    ->update([
                        'equipe_id' => (int) $teamId,
                        'updated_at' => now(),
                    ]);
            }
        });

        return response()->json([
            'message' => 'Usuario criado com sucesso.',
        ]);
    })->name('settings.users.create');

    Route::post('/configuracoes/usuarios/salvar-equipe', function (Request $request) use ($resolveSettingsScope, $canManageUserByScope, $authHasPermission, $isDemoAdminRequest, $demoNoopResponse) {
        if ($isDemoAdminRequest($request)) {
            return $demoNoopResponse('Modo demo: atualizacao de usuario simulada.');
        }

        if (! $authHasPermission($request, 'users.edit')) {
            return response()->json([
                'message' => 'Voce nao tem permissao para editar usuarios.',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'equipe_id' => ['nullable', 'integer', 'exists:equipes,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'nome' => ['required', 'string', 'max:120'],
        ]);

        $userId = (int) $validated['user_id'];
        $targetUser = DB::table('users')
            ->select(['id', 'equipe_id'])
            ->where('id', $userId)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'message' => 'Usuario nao encontrado para atualizar.',
            ], 404);
        }

        $scope = $resolveSettingsScope($request);
        $targetTeamId = $targetUser->equipe_id !== null ? (int) $targetUser->equipe_id : null;
        if (! $canManageUserByScope($scope, $userId, $targetTeamId)) {
            return response()->json([
                'message' => 'Voce nao tem permissao para alterar este usuario.',
            ], 403);
        }

        $incomingTeamId = $validated['equipe_id'] !== null ? (int) $validated['equipe_id'] : null;
        if ((string) ($scope['mode'] ?? '') === 'team' && (string) ($scope['team_id'] ?? '') !== (string) ($incomingTeamId ?? '')) {
            return response()->json([
                'message' => 'Administrador/Supervisor podem vincular apenas usuarios da propria equipe.',
            ], 403);
        }

        $userName = trim((string) ($validated['nome'] ?? ''));
        if ($userName === '') {
            return response()->json([
                'message' => 'Informe um nome valido para o usuario.',
            ], 422);
        }

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'equipe_id' => $incomingTeamId,
                'role_id' => $validated['role_id'] !== null ? (int) $validated['role_id'] : null,
                'nome' => $userName,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Dados do usuario salvos com sucesso.',
        ]);
    })->name('settings.users.team.save');

    Route::post('/configuracoes/usuarios/alterar-status', function (Request $request) use ($resolveSettingsScope, $canManageUserByScope, $authHasPermission, $isDemoAdminRequest, $demoNoopResponse) {
        if ($isDemoAdminRequest($request)) {
            return $demoNoopResponse('Modo demo: alteracao de status simulada.');
        }

        if (! $authHasPermission($request, 'users.edit')) {
            return response()->json([
                'message' => 'Voce nao tem permissao para editar usuarios.',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'ativo' => ['required', 'boolean'],
        ]);

        $userId = (int) $validated['user_id'];
        $ativo = (bool) $validated['ativo'];
        $targetUser = DB::table('users')
            ->select(['id', 'equipe_id'])
            ->where('id', $userId)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'message' => 'Usuario nao encontrado para atualizar status.',
            ], 404);
        }

        $scope = $resolveSettingsScope($request);
        $targetTeamId = $targetUser->equipe_id !== null ? (int) $targetUser->equipe_id : null;
        if (! $canManageUserByScope($scope, $userId, $targetTeamId)) {
            return response()->json([
                'message' => 'Voce nao tem permissao para alterar este usuario.',
            ], 403);
        }

        if ((int) ($request->user()?->id ?? 0) === $userId && $ativo === false) {
            return response()->json([
                'message' => 'Nao e permitido inativar o usuario logado.',
            ], 422);
        }

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'ativo' => $ativo ? 1 : 0,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => $ativo ? 'Usuario ativado com sucesso.' : 'Usuario inativado com sucesso.',
        ]);
    })->name('settings.users.status.save');

    Route::post('/configuracoes/usuarios/resetar-senha', function (Request $request) use ($resolveSettingsScope, $canManageUserByScope, $authHasPermission, $isDemoAdminRequest, $demoNoopResponse) {
        if ($isDemoAdminRequest($request)) {
            return $demoNoopResponse('Modo demo: reset de senha simulado.');
        }

        if (! $authHasPermission($request, 'users.edit')) {
            return response()->json([
                'message' => 'Voce nao tem permissao para editar usuarios.',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'nova_senha' => ['required', 'string', 'min:6'],
        ]);

        $userId = (int) $validated['user_id'];
        $newPassword = (string) $validated['nova_senha'];
        $targetUser = DB::table('users')
            ->select(['id', 'equipe_id'])
            ->where('id', $userId)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'message' => 'Usuario nao encontrado para redefinir senha.',
            ], 404);
        }

        $scope = $resolveSettingsScope($request);
        $targetTeamId = $targetUser->equipe_id !== null ? (int) $targetUser->equipe_id : null;
        if (! $canManageUserByScope($scope, $userId, $targetTeamId)) {
            return response()->json([
                'message' => 'Voce nao tem permissao para alterar este usuario.',
            ], 403);
        }

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'password' => Hash::make($newPassword),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Senha redefinida com sucesso.',
        ]);
    })->name('settings.users.password.reset');

    Route::post('/configuracoes/usuarios/excluir', function (Request $request) use ($resolveSettingsScope, $canManageUserByScope, $authHasPermission, $isDemoAdminRequest, $demoNoopResponse) {
        if ($isDemoAdminRequest($request)) {
            return $demoNoopResponse('Modo demo: exclusao de usuario simulada.');
        }

        if (! $authHasPermission($request, 'users.delete')) {
            return response()->json([
                'message' => 'Voce nao tem permissao para excluir usuarios.',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $userId = (int) $validated['user_id'];
        $targetUser = DB::table('users')
            ->select(['id', 'equipe_id'])
            ->where('id', $userId)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'message' => 'Usuario nao encontrado para excluir.',
            ], 404);
        }

        $scope = $resolveSettingsScope($request);
        $targetTeamId = $targetUser->equipe_id !== null ? (int) $targetUser->equipe_id : null;
        if (! $canManageUserByScope($scope, $userId, $targetTeamId)) {
            return response()->json([
                'message' => 'Voce nao tem permissao para excluir este usuario.',
            ], 403);
        }

        if ((int) ($request->user()?->id ?? 0) === $userId) {
            return response()->json([
                'message' => 'Nao e permitido excluir o usuario logado.',
            ], 422);
        }

        DB::transaction(function () use ($userId): void {
            DB::table('equipes')
                ->where('supervisor_user_id', $userId)
                ->update([
                    'supervisor_user_id' => null,
                    'updated_at' => now(),
                ]);

            DB::table('user_permissions')
                ->where('user_id', $userId)
                ->delete();

            DB::table('users')
                ->where('id', $userId)
                ->delete();
        });

        return response()->json([
            'message' => 'Usuario excluido com sucesso.',
        ]);
    })->name('settings.users.delete');

    Route::get('/configuracoes', function (Request $request) use ($resolveSettingsScope, $ensureSettingsPermissionsCatalog, $callSettingsBridge, $usingBridgeProvider, $isDemoAdminRequest) {
        if ($isDemoAdminRequest($request)) {
            $demoPermissionRoles = [
                ['key' => 'role-1', 'label' => 'Master', 'role_id' => 1, 'slug' => 'master', 'nivel' => 100],
                ['key' => 'role-2', 'label' => 'Administrador', 'role_id' => 2, 'slug' => 'administrador', 'nivel' => 80],
                ['key' => 'role-3', 'label' => 'Supervisor', 'role_id' => 3, 'slug' => 'supervisor', 'nivel' => 50],
                ['key' => 'role-4', 'label' => 'Operador', 'role_id' => 4, 'slug' => 'operador', 'nivel' => 10],
            ];

            $demoTeams = [
                ['key' => 'team-11', 'label' => 'Equipe Comercial', 'team_id' => 11],
                ['key' => 'team-12', 'label' => 'Equipe Operacional', 'team_id' => 12],
                ['key' => 'team-13', 'label' => 'Equipe Qualidade', 'team_id' => 13],
            ];

            $demoUsers = [
                [
                    'key' => 'user-1001',
                    'label' => 'Admin Demo (admindemo)',
                    'user_id' => 1001,
                    'name' => 'Admin Demo',
                    'login' => 'admindemo',
                    'team_id' => 11,
                    'team_key' => 'team-11',
                    'team_label' => 'Equipe Comercial',
                    'role_id' => 1,
                    'role_label' => 'Master',
                    'role_nivel' => 100,
                    'is_active' => true,
                    'created_at_iso' => '2026-01-15',
                    'created_at_label' => '15/01/2026',
                ],
                [
                    'key' => 'user-1002',
                    'label' => 'Bruna Araujo (brunaaraujo)',
                    'user_id' => 1002,
                    'name' => 'Bruna Araujo',
                    'login' => 'brunaaraujo',
                    'team_id' => 11,
                    'team_key' => 'team-11',
                    'team_label' => 'Equipe Comercial',
                    'role_id' => 2,
                    'role_label' => 'Administrador',
                    'role_nivel' => 80,
                    'is_active' => true,
                    'created_at_iso' => '2026-01-20',
                    'created_at_label' => '20/01/2026',
                ],
                [
                    'key' => 'user-1003',
                    'label' => 'Carlos Sousa (carlossousa)',
                    'user_id' => 1003,
                    'name' => 'Carlos Sousa',
                    'login' => 'carlossousa',
                    'team_id' => 12,
                    'team_key' => 'team-12',
                    'team_label' => 'Equipe Operacional',
                    'role_id' => 3,
                    'role_label' => 'Supervisor',
                    'role_nivel' => 50,
                    'is_active' => true,
                    'created_at_iso' => '2026-02-03',
                    'created_at_label' => '03/02/2026',
                ],
                [
                    'key' => 'user-1004',
                    'label' => 'Daniel Lima (daniellima)',
                    'user_id' => 1004,
                    'name' => 'Daniel Lima',
                    'login' => 'daniellima',
                    'team_id' => 12,
                    'team_key' => 'team-12',
                    'team_label' => 'Equipe Operacional',
                    'role_id' => 4,
                    'role_label' => 'Operador',
                    'role_nivel' => 10,
                    'is_active' => true,
                    'created_at_iso' => '2026-02-10',
                    'created_at_label' => '10/02/2026',
                ],
                [
                    'key' => 'user-1005',
                    'label' => 'Elisa Martins (elisamartins)',
                    'user_id' => 1005,
                    'name' => 'Elisa Martins',
                    'login' => 'elisamartins',
                    'team_id' => 13,
                    'team_key' => 'team-13',
                    'team_label' => 'Equipe Qualidade',
                    'role_id' => 2,
                    'role_label' => 'Administrador',
                    'role_nivel' => 80,
                    'is_active' => false,
                    'created_at_iso' => '2026-02-18',
                    'created_at_label' => '18/02/2026',
                ],
            ];

            $demoTeamMembersByTeam = [
                'team-11' => [
                    ['key' => 'member-1001', 'userKey' => 'user-1001', 'label' => 'Admin Demo (admindemo)', 'permissionLevel' => 'Master (Nivel 100)'],
                    ['key' => 'member-1002', 'userKey' => 'user-1002', 'label' => 'Bruna Araujo (brunaaraujo)', 'permissionLevel' => 'Administrador (Nivel 80)'],
                ],
                'team-12' => [
                    ['key' => 'member-1003', 'userKey' => 'user-1003', 'label' => 'Carlos Sousa (carlossousa)', 'permissionLevel' => 'Supervisor (Nivel 50)'],
                    ['key' => 'member-1004', 'userKey' => 'user-1004', 'label' => 'Daniel Lima (daniellima)', 'permissionLevel' => 'Operador (Nivel 10)'],
                ],
                'team-13' => [
                    ['key' => 'member-1005', 'userKey' => 'user-1005', 'label' => 'Elisa Martins (elisamartins)', 'permissionLevel' => 'Administrador (Nivel 80)'],
                ],
            ];

            $demoPermissionsTree = [
                [
                    'key' => 'module-dashboard',
                    'label' => 'Painel',
                    'children' => [
                        ['key' => 'perm-100', 'label' => 'Ver', 'permission_slug' => 'dashboard.view'],
                    ],
                ],
                [
                    'key' => 'module-consultas',
                    'label' => 'Consultas',
                    'children' => [
                        [
                            'key' => 'module-consulta_cliente',
                            'label' => 'Consulta Cliente',
                            'children' => [
                                ['key' => 'perm-101', 'label' => 'Ver', 'permission_slug' => 'consulta_cliente.view'],
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'module-configuracoes',
                    'label' => 'Configuracoes',
                    'children' => [
                        [
                            'key' => 'module-config',
                            'label' => 'Permissoes',
                            'children' => [
                                ['key' => 'perm-102', 'label' => 'Ver', 'permission_slug' => 'config.view'],
                                ['key' => 'perm-103', 'label' => 'Editar', 'permission_slug' => 'config.edit'],
                            ],
                        ],
                        [
                            'key' => 'module-users',
                            'label' => 'Usuarios',
                            'children' => [
                                ['key' => 'perm-104', 'label' => 'Ver', 'permission_slug' => 'users.view'],
                                ['key' => 'perm-105', 'label' => 'Criar', 'permission_slug' => 'users.create'],
                                ['key' => 'perm-106', 'label' => 'Editar', 'permission_slug' => 'users.edit'],
                                ['key' => 'perm-107', 'label' => 'Excluir', 'permission_slug' => 'users.delete'],
                            ],
                        ],
                        [
                            'key' => 'module-equipes',
                            'label' => 'Equipes',
                            'children' => [
                                ['key' => 'perm-108', 'label' => 'Ver', 'permission_slug' => 'equipes.view'],
                                ['key' => 'perm-109', 'label' => 'Criar', 'permission_slug' => 'equipes.create'],
                                ['key' => 'perm-110', 'label' => 'Editar', 'permission_slug' => 'equipes.edit'],
                                ['key' => 'perm-111', 'label' => 'Excluir', 'permission_slug' => 'equipes.delete'],
                            ],
                        ],
                        [
                            'key' => 'module-cadastro-api',
                            'label' => 'Cadastro API',
                            'children' => [
                                [
                                    'key' => 'module-consulta_v8',
                                    'label' => 'Consulta V8',
                                    'children' => [
                                        ['key' => 'perm-112', 'label' => 'Ver', 'permission_slug' => 'consulta_v8.view'],
                                    ],
                                ],
                                [
                                    'key' => 'module-consulta_presenca',
                                    'label' => 'Consulta Presenca',
                                    'children' => [
                                        ['key' => 'perm-113', 'label' => 'Ver', 'permission_slug' => 'consulta_presenca.view'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $allDemoPermissionKeys = [];
            $collectPermissionKeys = static function (array $nodes) use (&$collectPermissionKeys, &$allDemoPermissionKeys): void {
                foreach ($nodes as $node) {
                    $nodeKey = (string) ($node['key'] ?? '');
                    if (str_starts_with($nodeKey, 'perm-')) {
                        $allDemoPermissionKeys[] = $nodeKey;
                    }

                    $children = $node['children'] ?? [];
                    if (is_array($children) && $children !== []) {
                        $collectPermissionKeys($children);
                    }
                }
            };
            $collectPermissionKeys($demoPermissionsTree);
            $allDemoPermissionKeys = array_values(array_unique($allDemoPermissionKeys));

            $demoPermissionsStateByRole = [];
            foreach ($demoPermissionRoles as $demoRole) {
                $selectionKey = 'permissions:'.(string) ($demoRole['key'] ?? '');
                $isMaster = strtolower((string) ($demoRole['slug'] ?? '')) === 'master';
                $isAdmin = strtolower((string) ($demoRole['slug'] ?? '')) === 'administrador';
                $isSupervisor = strtolower((string) ($demoRole['slug'] ?? '')) === 'supervisor';

                $demoPermissionsStateByRole[$selectionKey] = [];
                foreach ($allDemoPermissionKeys as $permissionKey) {
                    if ($isMaster || $isAdmin) {
                        $demoPermissionsStateByRole[$selectionKey][$permissionKey] = true;
                        continue;
                    }

                    if ($isSupervisor) {
                        $demoPermissionsStateByRole[$selectionKey][$permissionKey] = in_array($permissionKey, ['perm-100', 'perm-101', 'perm-104', 'perm-106', 'perm-108', 'perm-110'], true);
                        continue;
                    }

                    $demoPermissionsStateByRole[$selectionKey][$permissionKey] = in_array($permissionKey, ['perm-100', 'perm-101'], true);
                }
            }

            $demoAllowedPermissionSlugs = [
                'dashboard.view',
                'consulta_cliente.view',
                'config.view',
                'config.edit',
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
                'equipes.view',
                'equipes.create',
                'equipes.edit',
                'equipes.delete',
                'consulta_v8.view',
                'consulta_presenca.view',
            ];

            return view('settings.index', [
                'dbUsers' => $demoUsers,
                'dbTeams' => $demoTeams,
                'teamMembersByTeam' => $demoTeamMembersByTeam,
                'permissionRoles' => $demoPermissionRoles,
                'permissionsTree' => $demoPermissionsTree,
                'permissionsStateByRole' => $demoPermissionsStateByRole,
                'permissionsSaveUrl' => route('settings.permissions.role.save'),
                'teamsRenameUrl' => route('settings.teams.rename'),
                'teamsDeleteUrl' => route('settings.teams.delete'),
                'teamsCreateUrl' => route('settings.teams.create'),
                'usersCreateUrl' => route('settings.users.create'),
                'usersSaveTeamUrl' => route('settings.users.team.save'),
                'usersStatusSaveUrl' => route('settings.users.status.save'),
                'usersResetPasswordUrl' => route('settings.users.password.reset'),
                'usersDeleteUrl' => route('settings.users.delete'),
                'authUserId' => (int) ($request->user()?->id ?? 0),
                'authUserTeamId' => 11,
                'authRoleSlug' => 'master',
                'authScopeMode' => 'all',
                'authAllowedPermissionSlugs' => $demoAllowedPermissionSlugs,
            ]);
        }

        if ($usingBridgeProvider()) {
            $bridgePayload = $callSettingsBridge('settings_index', [
                'auth_user' => [
                    'id' => (int) ($request->user()?->id ?? 0),
                    'team_id' => $request->user()?->equipe_id !== null ? (int) $request->user()->equipe_id : null,
                    'role_id' => $request->user()?->role_id !== null ? (int) $request->user()->role_id : null,
                    'role_slug' => (string) ($request->user()?->role_slug ?? ''),
                ],
            ]);

            if ((bool) ($bridgePayload['ok'] ?? false) && isset($bridgePayload['payload']) && is_array($bridgePayload['payload'])) {
                $payload = $bridgePayload['payload'];

                return view('settings.index', [
                    'dbUsers' => (array) ($payload['dbUsers'] ?? []),
                    'dbTeams' => (array) ($payload['dbTeams'] ?? []),
                    'teamMembersByTeam' => (array) ($payload['teamMembersByTeam'] ?? []),
                    'permissionRoles' => (array) ($payload['permissionRoles'] ?? []),
                    'permissionsTree' => (array) ($payload['permissionsTree'] ?? []),
                    'permissionsStateByRole' => (array) ($payload['permissionsStateByRole'] ?? []),
                    'permissionsSaveUrl' => route('settings.permissions.role.save'),
                    'teamsRenameUrl' => route('settings.teams.rename'),
                    'teamsDeleteUrl' => route('settings.teams.delete'),
                    'teamsCreateUrl' => route('settings.teams.create'),
                    'usersCreateUrl' => route('settings.users.create'),
                    'usersSaveTeamUrl' => route('settings.users.team.save'),
                    'usersStatusSaveUrl' => route('settings.users.status.save'),
                    'usersResetPasswordUrl' => route('settings.users.password.reset'),
                    'usersDeleteUrl' => route('settings.users.delete'),
                    'authUserId' => (int) ($payload['authUserId'] ?? 0),
                    'authUserTeamId' => array_key_exists('authUserTeamId', $payload) ? $payload['authUserTeamId'] : null,
                    'authRoleSlug' => (string) ($payload['authRoleSlug'] ?? ''),
                    'authScopeMode' => (string) ($payload['authScopeMode'] ?? 'self'),
                    'authAllowedPermissionSlugs' => (array) ($payload['authAllowedPermissionSlugs'] ?? []),
                ]);
            }

            abort(500, 'Falha ao carregar configuracoes pela bridge.');
        }

        $ensureSettingsPermissionsCatalog();

        $users = collect();
        $dbUsers = [];
        $dbTeams = [];
        $teamMembersByTeam = [];
        $rolesById = [];
        $permissionRoles = [];
        $permissionsTree = [];
        $permissionsStateByRole = [];
        $scope = $resolveSettingsScope($request);
        $scopeMode = (string) ($scope['mode'] ?? 'self');
        $scopeUserId = $scope['user_id'] !== null ? (int) $scope['user_id'] : 0;
        $scopeTeamId = $scope['team_id'] !== null ? (int) $scope['team_id'] : null;
        $scopeRoleId = $scope['role_id'] !== null ? (int) $scope['role_id'] : 0;
        $authAllowedPermissionSlugs = [];

        $buildUserLabel = static function (User $user, int $index): string {
            $name = trim((string) ($user->nome ?? ''));
            $login = trim((string) ($user->login ?? ''));

            if ($name !== '' && $login !== '' && strcasecmp($name, $login) !== 0) {
                return $name.' ('.$login.')';
            }

            if ($name !== '') {
                return $name;
            }

            if ($login !== '') {
                return $login;
            }

            return 'Usuario #'.($user->id ?? ($index + 1));
        };

        if ($scopeRoleId > 0) {
            try {
                $authAllowedPermissionSlugs = DB::table('role_permissions as rp')
                    ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
                    ->where('rp.role_id', $scopeRoleId)
                    ->where('rp.allowed', 1)
                    ->pluck('p.slug')
                    ->map(fn ($slug) => strtolower(trim((string) $slug)))
                    ->filter(fn ($slug) => $slug !== '')
                    ->unique()
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            $usersSelectColumns = ['id', 'nome', 'login', 'equipe_id', 'role_id', 'ativo'];
            if (Schema::hasColumn('users', 'created_at')) {
                $usersSelectColumns[] = 'created_at';
            }
            if (Schema::hasColumn('users', 'data_criacao')) {
                $usersSelectColumns[] = 'data_criacao';
            }

            $usersQuery = User::query()
                ->select($usersSelectColumns)
                ->orderBy('nome')
                ->orderBy('login');

            if ($scopeMode === 'team') {
                if ($scopeTeamId === null) {
                    $usersQuery->whereNull('equipe_id');
                } else {
                    $usersQuery->where('equipe_id', $scopeTeamId);
                }
            } elseif ($scopeMode === 'self') {
                if ($scopeUserId > 0) {
                    $usersQuery->where('id', $scopeUserId);
                } else {
                    $usersQuery->whereRaw('1 = 0');
                }
            }

            $users = $usersQuery->get();
        } catch (\Throwable $e) {
            report($e);
        }

        $buildUserKey = static function (User $user, int $index): string {
            $login = trim((string) ($user->login ?? ''));
            $keySource = $user->id !== null ? (string) $user->id : ($login !== '' ? $login : 'idx-'.$index);

            return 'user-'.preg_replace('/[^a-zA-Z0-9_\-]/', '-', $keySource);
        };

        try {
            $rolesById = DB::table('roles')
                ->select(['id', 'nome', 'nivel'])
                ->get()
                ->keyBy('id')
                ->map(function ($role): array {
                    return [
                        'nome' => trim((string) ($role->nome ?? '')),
                        'nivel' => $role->nivel !== null ? (int) $role->nivel : null,
                    ];
                })
                ->all();
        } catch (\Throwable $e) {
            report($e);
        }

        $teams = collect();

        try {
            $teams = DB::table('equipes')
                ->select(['id', 'nome'])
                ->orderBy('nome')
                ->get();
        } catch (\Throwable $e) {
            report($e);
        }

        if ($scopeMode !== 'all') {
            $visibleTeamIds = $users
                ->pluck('equipe_id')
                ->filter(fn ($id) => $id !== null)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $teams = $teams
                ->filter(fn ($team) => in_array((int) ($team->id ?? 0), $visibleTeamIds, true))
                ->values();
        }

        if ($teams->isEmpty()) {
            $teams = $users
                ->pluck('equipe_id')
                ->filter(fn ($id) => $id !== null)
                ->unique()
                ->sort()
                ->values()
                ->map(function ($id) {
                    return (object) [
                        'id' => $id,
                        'nome' => 'Equipe #'.$id,
                    ];
                });
        }

        $dbTeams = $teams
            ->values()
            ->map(function ($team): array {
                $teamId = (string) ($team->id ?? '');
                $teamName = trim((string) ($team->nome ?? ''));
                $label = $teamName !== '' ? $teamName : 'Equipe #'.$teamId;

                return [
                    'key' => 'team-'.preg_replace('/[^a-zA-Z0-9_\-]/', '-', $teamId),
                    'label' => $label,
                    'team_id' => $team->id,
                ];
            })
            ->all();

        $buildMemberPayload = static function (Collection $memberUsers) use ($buildUserLabel, $buildUserKey, $rolesById): array {
            return $memberUsers
                ->values()
                ->map(function (User $user, int $index) use ($buildUserLabel, $buildUserKey, $rolesById): array {
                    $roleData = $user->role_id !== null ? ($rolesById[$user->role_id] ?? null) : null;
                    $roleName = trim((string) ($roleData['nome'] ?? ''));
                    $roleLevel = $roleData['nivel'] ?? null;

                    if ($roleName !== '' && $roleLevel !== null) {
                        $permissionLevel = $roleName.' (Nivel '.$roleLevel.')';
                    } elseif ($roleName !== '') {
                        $permissionLevel = $roleName;
                    } elseif ($user->role_id !== null) {
                        $permissionLevel = 'Role #'.$user->role_id;
                    } else {
                        $permissionLevel = 'Sem nivel';
                    }

                    return [
                        'key' => 'member-'.($user->id ?? ('idx-'.$index)),
                        'userKey' => $buildUserKey($user, $index),
                        'label' => $buildUserLabel($user, $index),
                        'permissionLevel' => $permissionLevel,
                    ];
                })
                ->all();
        };

        foreach ($dbTeams as $team) {
            $teamKey = (string) $team['key'];
            $teamId = $team['team_id'];

            $members = $users->filter(
                fn (User $user) => (string) ($user->equipe_id ?? '') === (string) ($teamId ?? '')
            );

            $teamMembersByTeam[$teamKey] = $buildMemberPayload($members);
        }

        $usersWithoutTeam = $users->filter(fn (User $user) => $user->equipe_id === null);

        if ($usersWithoutTeam->isNotEmpty()) {
            $noTeamKey = 'team-sem-equipe';
            $dbTeams[] = [
                'key' => $noTeamKey,
                'label' => 'Sem equipe',
                'team_id' => null,
            ];
            $teamMembersByTeam[$noTeamKey] = $buildMemberPayload($usersWithoutTeam);
        }

        try {
            $roles = DB::table('roles')
                ->select(['id', 'nome', 'slug', 'nivel'])
                ->orderByDesc('nivel')
                ->orderBy('nome')
                ->get();

            $permissions = DB::table('permissions')
                ->select(['id', 'nome', 'slug', 'modulo'])
                ->orderBy('modulo')
                ->orderBy('id')
                ->get();

            $allowedPermissionsByRole = [];
            $rolePermissions = DB::table('role_permissions')
                ->select(['role_id', 'permission_id', 'allowed'])
                ->get();

            $permissionsById = $permissions->keyBy('id');

            foreach ($rolePermissions as $row) {
                if ((int) ($row->allowed ?? 0) !== 1) {
                    continue;
                }

                $permission = $permissionsById->get((int) ($row->permission_id ?? 0));
                if (! $permission || trim((string) ($permission->slug ?? '')) === '') {
                    continue;
                }

                $allowedPermissionsByRole[(int) ($row->role_id ?? 0)][(string) $permission->slug] = true;
            }

            $permissionRoles = $roles
                ->map(function ($role): array {
                    return [
                        'key' => 'role-'.(int) ($role->id ?? 0),
                        'label' => trim((string) ($role->nome ?? '')),
                        'role_id' => (int) ($role->id ?? 0),
                        'slug' => trim((string) ($role->slug ?? '')),
                        'nivel' => (int) ($role->nivel ?? 0),
                    ];
                })
                ->all();

            $permissionsByModule = $permissions
                ->groupBy(fn ($permission) => trim((string) ($permission->modulo ?? '')))
                ->all();

            $formatActionLabel = static function (string $permissionSlug, string $fallbackLabel): string {
                $normalized = strtolower(trim($permissionSlug));

                return match (true) {
                    str_ends_with($normalized, '.view') => 'Ver',
                    str_ends_with($normalized, '.create') => 'Criar',
                    str_ends_with($normalized, '.edit') => 'Editar',
                    str_ends_with($normalized, '.delete') => 'Excluir',
                    str_ends_with($normalized, '.batch.send') => 'Enviar lote',
                    str_ends_with($normalized, '.batch.delete') => 'Excluir lote',
                    default => $fallbackLabel !== '' ? $fallbackLabel : $permissionSlug,
                };
            };

            $buildPermissionNodes = static function ($modulePermissions) use ($formatActionLabel): array {
                return collect($modulePermissions)
                    ->sortBy('id')
                    ->map(function ($permission) use ($formatActionLabel): array {
                        $permissionId = (int) ($permission->id ?? 0);
                        $permissionSlug = trim((string) ($permission->slug ?? ''));
                        $permissionName = trim((string) ($permission->nome ?? ''));

                        return [
                            'key' => 'perm-'.$permissionId,
                            'label' => $formatActionLabel($permissionSlug, $permissionName),
                            'permission_slug' => $permissionSlug,
                        ];
                    })
                    ->values()
                    ->all();
            };

            $permissionsTree = [];

            if (isset($permissionsByModule['dashboard'])) {
                $dashboardChildren = $buildPermissionNodes($permissionsByModule['dashboard']);
                if (! empty($dashboardChildren)) {
                    $permissionsTree[] = [
                        'key' => 'module-dashboard',
                        'label' => 'Painel',
                        'children' => $dashboardChildren,
                    ];
                }
            }

            $consultasChildren = [];
            foreach ([
                'consulta_cliente' => 'Consulta Cliente',
            ] as $moduleKey => $moduleLabel) {
                if (! isset($permissionsByModule[$moduleKey])) {
                    continue;
                }

                $consultasChildren[] = [
                    'key' => 'module-'.$moduleKey,
                    'label' => $moduleLabel,
                    'children' => $buildPermissionNodes($permissionsByModule[$moduleKey]),
                ];
            }

            if (! empty($consultasChildren)) {
                $permissionsTree[] = [
                    'key' => 'module-consultas',
                    'label' => 'Consultas',
                    'children' => $consultasChildren,
                ];
            }

            $settingsChildren = [];
            foreach ([
                'config' => 'Permissoes',
                'users' => 'Usuarios',
                'equipes' => 'Equipes',
            ] as $moduleKey => $moduleLabel) {
                if (! isset($permissionsByModule[$moduleKey])) {
                    continue;
                }

                $settingsChildren[] = [
                    'key' => 'module-'.$moduleKey,
                    'label' => $moduleLabel,
                    'children' => $buildPermissionNodes($permissionsByModule[$moduleKey]),
                ];
            }

            $apiChildren = [];
            foreach ([
                'consulta_v8' => 'Consulta V8',
                'consulta_presenca' => 'Consulta Presenca',
            ] as $moduleKey => $moduleLabel) {
                if (! isset($permissionsByModule[$moduleKey])) {
                    continue;
                }

                $apiChildren[] = [
                    'key' => 'module-'.$moduleKey,
                    'label' => $moduleLabel,
                    'children' => $buildPermissionNodes($permissionsByModule[$moduleKey]),
                ];
            }

            if (! empty($apiChildren)) {
                $settingsChildren[] = [
                    'key' => 'module-cadastro-api',
                    'label' => 'Cadastro API',
                    'children' => $apiChildren,
                ];
            }

            if (! empty($settingsChildren)) {
                $permissionsTree[] = [
                    'key' => 'module-configuracoes',
                    'label' => 'Configuracoes',
                    'children' => $settingsChildren,
                ];
            }

            foreach (array_keys($permissionsByModule) as $moduleKey) {
                if (in_array($moduleKey, ['dashboard', 'config', 'users', 'equipes', 'consulta_v8', 'consulta_presenca', 'consulta_cliente'], true)) {
                    continue;
                }

                $modulePermissions = $buildPermissionNodes($permissionsByModule[$moduleKey]);
                if (empty($modulePermissions)) {
                    continue;
                }

                $permissionsTree[] = [
                    'key' => 'module-'.preg_replace('/[^a-zA-Z0-9_\-]/', '-', (string) $moduleKey),
                    'label' => ucwords(str_replace(['_', '.'], ' ', (string) $moduleKey)),
                    'children' => $modulePermissions,
                ];
            }

            $permissionKeyToSlug = collect($permissions)
                ->mapWithKeys(function ($permission): array {
                    return [
                        'perm-'.(int) ($permission->id ?? 0) => trim((string) ($permission->slug ?? '')),
                    ];
                })
                ->all();

            foreach ($permissionRoles as $role) {
                $selectionKey = 'permissions:'.(string) ($role['key'] ?? '');
                $allowedBySlug = $allowedPermissionsByRole[(int) ($role['role_id'] ?? 0)] ?? [];

                $permissionsStateByRole[$selectionKey] = [];
                foreach ($permissionKeyToSlug as $permissionKey => $permissionSlug) {
                    $permissionsStateByRole[$selectionKey][$permissionKey] = (bool) ($allowedBySlug[$permissionSlug] ?? false);
                }

                if (strtolower((string) ($role['slug'] ?? '')) === 'master') {
                    foreach (array_keys($permissionsStateByRole[$selectionKey]) as $permissionKey) {
                        $permissionsStateByRole[$selectionKey][$permissionKey] = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $teamKeyById = collect($dbTeams)
            ->mapWithKeys(function (array $team): array {
                return [
                    (string) ($team['team_id'] ?? '') => (string) ($team['key'] ?? ''),
                ];
            })
            ->all();

        $teamLabelById = collect($dbTeams)
            ->mapWithKeys(function (array $team): array {
                return [
                    (string) ($team['team_id'] ?? '') => (string) ($team['label'] ?? ''),
                ];
            })
            ->all();

        $dbUsers = $users
            ->values()
            ->map(function (User $user, int $index) use ($buildUserLabel, $buildUserKey, $teamKeyById, $teamLabelById, $rolesById): array {
                $login = trim((string) ($user->login ?? ''));
                $teamKey = $teamKeyById[(string) ($user->equipe_id ?? '')] ?? '';
                $teamLabel = trim((string) ($teamLabelById[(string) ($user->equipe_id ?? '')] ?? ''));
                $roleData = $user->role_id !== null ? ($rolesById[$user->role_id] ?? null) : null;
                $createdAtValue = $user->data_criacao ?? $user->created_at ?? null;
                $createdAtIso = null;
                $createdAtLabel = '';

                if ($createdAtValue instanceof \DateTimeInterface) {
                    $createdAtIso = $createdAtValue->format('Y-m-d');
                    $createdAtLabel = $createdAtValue->format('d/m/Y');
                } elseif ($createdAtValue !== null && trim((string) $createdAtValue) !== '') {
                    try {
                        $createdAtParsed = \Illuminate\Support\Carbon::parse((string) $createdAtValue);
                        $createdAtIso = $createdAtParsed->format('Y-m-d');
                        $createdAtLabel = $createdAtParsed->format('d/m/Y');
                    } catch (\Throwable $e) {
                        $createdAtLabel = trim((string) $createdAtValue);
                    }
                }

                return [
                    'key' => $buildUserKey($user, $index),
                    'label' => $buildUserLabel($user, $index),
                    'user_id' => $user->id !== null ? (int) $user->id : null,
                    'name' => trim((string) ($user->nome ?? '')),
                    'login' => $login,
                    'team_id' => $user->equipe_id !== null ? (int) $user->equipe_id : null,
                    'team_key' => $teamKey,
                    'team_label' => $teamLabel,
                    'role_id' => $user->role_id !== null ? (int) $user->role_id : null,
                    'role_label' => trim((string) ($roleData['nome'] ?? '')),
                    'role_nivel' => $roleData['nivel'] ?? null,
                    'is_active' => (int) ($user->ativo ?? 1) === 1,
                    'created_at_iso' => $createdAtIso,
                    'created_at_label' => $createdAtLabel,
                ];
            })
            ->all();

        return view('settings.index', [
            'dbUsers' => $dbUsers,
            'dbTeams' => $dbTeams,
            'teamMembersByTeam' => $teamMembersByTeam,
            'permissionRoles' => $permissionRoles,
            'permissionsTree' => $permissionsTree,
            'permissionsStateByRole' => $permissionsStateByRole,
            'permissionsSaveUrl' => route('settings.permissions.role.save'),
            'teamsRenameUrl' => route('settings.teams.rename'),
            'teamsDeleteUrl' => route('settings.teams.delete'),
            'teamsCreateUrl' => route('settings.teams.create'),
            'usersCreateUrl' => route('settings.users.create'),
            'usersSaveTeamUrl' => route('settings.users.team.save'),
            'usersStatusSaveUrl' => route('settings.users.status.save'),
            'usersResetPasswordUrl' => route('settings.users.password.reset'),
            'usersDeleteUrl' => route('settings.users.delete'),
            'authUserId' => (int) ($request->user()?->id ?? 0),
            'authUserTeamId' => $request->user()?->equipe_id !== null ? (int) $request->user()->equipe_id : null,
            'authRoleSlug' => (string) ($scope['role_slug'] ?? ''),
            'authScopeMode' => $scopeMode,
            'authAllowedPermissionSlugs' => $authAllowedPermissionSlugs,
        ]);
    })->name('settings.index');

    Route::get('/configuracoes/usuarios', [SettingsUsersController::class, 'index'])->name('settings.users');
    Route::post('/configuracoes/usuarios/liberar-senha', [SettingsUsersController::class, 'unlockPassword'])->name('settings.users.unlock-password');
    Route::get('/configuracoes/permissoes', [SettingsPermissionsController::class, 'index'])->name('settings.permissions');
    Route::post('/configuracoes/permissoes', [SettingsPermissionsController::class, 'update'])->name('settings.permissions.update');
});

require __DIR__.'/auth.php';

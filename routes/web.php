<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\SettingsPermissionsController;
use App\Http\Controllers\Settings\SettingsUsersController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    $resolveSettingsScope = static function (Request $request): array {
        $authUser = $request->user();
        $authUserId = (int) ($authUser?->id ?? 0);
        $authTeamId = $authUser?->equipe_id !== null ? (int) $authUser->equipe_id : null;
        $authRoleId = $authUser?->role_id !== null ? (int) $authUser->role_id : null;

        $authRoleSlug = '';
        if ($authRoleId !== null) {
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

    $authHasPermission = static function (Request $request, string $permissionSlug) use ($resolveSettingsScope): bool {
        $scope = $resolveSettingsScope($request);
        if ((string) ($scope['role_slug'] ?? '') === 'master') {
            return true;
        }

        $roleId = $scope['role_id'] !== null ? (int) $scope['role_id'] : 0;
        if ($roleId <= 0 || trim($permissionSlug) === '') {
            return false;
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/configuracoes/permissoes/salvar', function (Request $request) {
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

    Route::post('/configuracoes/equipes/renomear', function (Request $request) use ($authHasPermission) {
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

    Route::post('/configuracoes/equipes/excluir', function (Request $request) use ($resolveSettingsScope) {
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

    Route::post('/configuracoes/equipes/criar', function (Request $request) use ($resolveSettingsScope) {
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

    Route::post('/configuracoes/usuarios/criar', function (Request $request) use ($resolveSettingsScope, $authHasPermission) {
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

    Route::post('/configuracoes/usuarios/salvar-equipe', function (Request $request) use ($resolveSettingsScope, $canManageUserByScope, $authHasPermission) {
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

    Route::post('/configuracoes/usuarios/alterar-status', function (Request $request) use ($resolveSettingsScope, $canManageUserByScope, $authHasPermission) {
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

    Route::post('/configuracoes/usuarios/resetar-senha', function (Request $request) use ($resolveSettingsScope, $canManageUserByScope, $authHasPermission) {
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

    Route::post('/configuracoes/usuarios/excluir', function (Request $request) use ($resolveSettingsScope, $canManageUserByScope, $authHasPermission) {
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

    Route::get('/configuracoes', function (Request $request) use ($resolveSettingsScope) {
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
            $usersQuery = User::query()
                ->select(['id', 'nome', 'login', 'equipe_id', 'role_id', 'ativo'])
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
                if (in_array($moduleKey, ['dashboard', 'config', 'users', 'equipes', 'consulta_v8', 'consulta_presenca'], true)) {
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

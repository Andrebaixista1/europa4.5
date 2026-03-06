<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\SettingsPermissionsController;
use App\Http\Controllers\Settings\SettingsUsersController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
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

    Route::post('/configuracoes/equipes/renomear', function (Request $request) {
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

    Route::post('/configuracoes/usuarios/salvar-equipe', function (Request $request) {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'equipe_id' => ['nullable', 'integer', 'exists:equipes,id'],
            'nome' => ['required', 'string', 'max:120'],
        ]);

        $userId = (int) $validated['user_id'];
        $exists = DB::table('users')->where('id', $userId)->exists();
        if (! $exists) {
            return response()->json([
                'message' => 'Usuario nao encontrado para atualizar.',
            ], 404);
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
                'equipe_id' => $validated['equipe_id'] !== null ? (int) $validated['equipe_id'] : null,
                'nome' => $userName,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Dados do usuario salvos com sucesso.',
        ]);
    })->name('settings.users.team.save');

    Route::post('/configuracoes/usuarios/alterar-status', function (Request $request) {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'ativo' => ['required', 'boolean'],
        ]);

        $userId = (int) $validated['user_id'];
        $ativo = (bool) $validated['ativo'];

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

    Route::post('/configuracoes/usuarios/resetar-senha', function (Request $request) {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'nova_senha' => ['required', 'string', 'min:6'],
        ]);

        $userId = (int) $validated['user_id'];
        $newPassword = (string) $validated['nova_senha'];

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

    Route::post('/configuracoes/usuarios/excluir', function (Request $request) {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $userId = (int) $validated['user_id'];

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

    Route::get('/configuracoes', function () {
        $users = collect();
        $dbUsers = [];
        $dbTeams = [];
        $teamMembersByTeam = [];
        $rolesById = [];
        $permissionRoles = [];
        $permissionsTree = [];
        $permissionsStateByRole = [];

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

        try {
            $users = User::query()
                ->select(['id', 'nome', 'login', 'equipe_id', 'role_id', 'ativo'])
                ->orderBy('nome')
                ->orderBy('login')
                ->get();
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

        $generalTeamKey = collect($dbTeams)
            ->first(function (array $team): bool {
                $label = mb_strtolower(trim((string) ($team['label'] ?? '')));
                return $label === 'equipe geral';
            })['key'] ?? '';

        $dbUsers = $users
            ->values()
            ->map(function (User $user, int $index) use ($buildUserLabel, $buildUserKey, $teamKeyById, $generalTeamKey): array {
                $login = trim((string) ($user->login ?? ''));
                $teamKey = $teamKeyById[(string) ($user->equipe_id ?? '')] ?? '';

                if ($teamKey === '' && strcasecmp($login, 'andrefelipe') === 0 && $generalTeamKey !== '') {
                    $teamKey = $generalTeamKey;
                }

                return [
                    'key' => $buildUserKey($user, $index),
                    'label' => $buildUserLabel($user, $index),
                    'user_id' => $user->id !== null ? (int) $user->id : null,
                    'name' => trim((string) ($user->nome ?? '')),
                    'login' => $login,
                    'team_key' => $teamKey,
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
            'usersSaveTeamUrl' => route('settings.users.team.save'),
            'usersStatusSaveUrl' => route('settings.users.status.save'),
            'usersResetPasswordUrl' => route('settings.users.password.reset'),
            'usersDeleteUrl' => route('settings.users.delete'),
        ]);
    })->name('settings.index');

    Route::get('/configuracoes/usuarios', [SettingsUsersController::class, 'index'])->name('settings.users');
    Route::post('/configuracoes/usuarios/liberar-senha', [SettingsUsersController::class, 'unlockPassword'])->name('settings.users.unlock-password');
    Route::get('/configuracoes/permissoes', [SettingsPermissionsController::class, 'index'])->name('settings.permissions');
    Route::post('/configuracoes/permissoes', [SettingsPermissionsController::class, 'update'])->name('settings.permissions.update');
});

require __DIR__.'/auth.php';

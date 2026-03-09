<?php

namespace App\Auth;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BridgeUserProvider implements UserProvider
{
    public function __construct(
        private readonly string $endpoint,
        private readonly string $token,
        private readonly int $timeoutSeconds = 8
    ) {
    }

    public function retrieveById($identifier): ?UserContract
    {
        $payload = $this->requestBridge([
            'action' => 'user_by_id',
            'id' => (string) $identifier,
        ]);

        if (! is_array($payload) || ! ($payload['ok'] ?? false)) {
            return null;
        }

        return $this->mapUser($payload['user'] ?? []);
    }

    public function retrieveByToken($identifier, $token): ?UserContract
    {
        $user = $this->retrieveById($identifier);

        if (! $user) {
            return null;
        }

        $rememberToken = method_exists($user, 'getRememberToken')
            ? (string) ($user->getRememberToken() ?? '')
            : '';

        return $rememberToken !== '' && hash_equals($rememberToken, (string) $token)
            ? $user
            : null;
    }

    public function updateRememberToken(UserContract $user, $token): void
    {
        // Remember tokens are managed in SQL Server and should be updated there
        // only when needed by explicit API endpoints.
    }

    public function retrieveByCredentials(array $credentials): ?UserContract
    {
        $loginRaw = (string) ($credentials['login'] ?? $credentials['email'] ?? '');
        $login = Str::lower(trim($loginRaw));

        if ($login === '') {
            return null;
        }

        $payload = $this->requestBridge([
            'action' => 'user_by_login',
            'login' => $login,
        ]);

        if (! is_array($payload) || ! ($payload['ok'] ?? false)) {
            return null;
        }

        return $this->mapUser($payload['user'] ?? []);
    }

    public function validateCredentials(UserContract $user, array $credentials): bool
    {
        $password = (string) ($credentials['password'] ?? '');
        $login = Str::lower(trim((string) ($user->login ?? $user->email ?? '')));

        if ($password === '' || $login === '') {
            return false;
        }

        $payload = $this->requestBridge([
            'action' => 'login',
            'login' => $login,
            'password' => $password,
        ]);

        return is_array($payload) && (bool) ($payload['ok'] ?? false);
    }

    public function rehashPasswordIfRequired(UserContract $user, array $credentials, bool $force = false): void
    {
        // Password hash lifecycle is handled by SQL Server / bridge.
    }

    private function requestBridge(array $payload): ?array
    {
        if ($this->endpoint === '' || $this->token === '') {
            return null;
        }

        if (! Str::startsWith($this->endpoint, ['http://', 'https://'])) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->acceptJson()
                ->withHeaders([
                    'X-Bridge-Token' => $this->token,
                ])
                ->post($this->endpoint, $payload);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $json = $response->json();

        return is_array($json) ? $json : null;
    }

    private function mapUser(mixed $rawUser): ?UserContract
    {
        if (! is_array($rawUser)) {
            return null;
        }

        $id = $rawUser['id'] ?? null;
        $login = $rawUser['login'] ?? null;

        if ($id === null || ! is_scalar($id) || ! is_scalar($login)) {
            return null;
        }

        return new GenericUser([
            'id' => (string) $id,
            'login' => (string) $login,
            'name' => (string) ($rawUser['name'] ?? $rawUser['nome'] ?? $login),
            'email' => (string) ($rawUser['email'] ?? ($login.'@europa.local')),
            'equipe_id' => $rawUser['equipe_id'] ?? null,
            'role_id' => $rawUser['role_id'] ?? null,
            'role_slug' => (string) ($rawUser['role_slug'] ?? ''),
            'role_nome' => (string) ($rawUser['role_nome'] ?? ''),
            'role_nivel' => $rawUser['role_nivel'] ?? null,
            'team_name' => (string) ($rawUser['team_name'] ?? ''),
            'ativo' => (int) ($rawUser['ativo'] ?? 1),
            'remember_token' => $rawUser['remember_token'] ?? null,
        ]);
    }
}

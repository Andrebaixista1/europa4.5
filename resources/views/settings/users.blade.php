<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Usuarios
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" x-data="{ secondsLeft: {{ (int) $unlockRemainingSeconds }} }" x-init="
                    if (secondsLeft > 0) {
                        const timer = setInterval(() => {
                            if (secondsLeft <= 0) {
                                clearInterval(timer);
                                window.location.reload();
                                return;
                            }
                            secondsLeft -= 1;
                        }, 1000);
                    }
                ">
                    @if (session('status') === 'users-password-unlocked')
                        <div class="mb-4 rounded-md border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            Senha liberada por 2 minutos.
                        </div>
                    @endif

                    @if ($queryError)
                        <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                            {{ $queryError }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="text-sm text-gray-600">
                            Clique em <strong>Login</strong> para copiar. Para visualizar/copiar a senha (hash), use o icone de olho e confirme a senha master (ID 1).
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Login</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Senha</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Criado em</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Atualizado em</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @if ($remoteUser)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <button
                                                type="button"
                                                class="rounded border border-gray-300 px-2 py-1 text-sm font-medium text-gray-800 hover:bg-gray-50"
                                                data-copy-value="{{ $remoteUser->login }}"
                                            >
                                                {{ $remoteUser->login }}
                                            </button>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($passwordUnlocked)
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        type="button"
                                                        class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-800 hover:bg-gray-50 break-all text-left"
                                                        data-copy-value="{{ $remoteUser->password_sha256 }}"
                                                    >
                                                        {{ $remoteUser->password_sha256 }}
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center justify-center rounded border border-gray-300 p-1 text-gray-700 hover:bg-gray-50"
                                                        title="Senha visivel"
                                                        aria-label="Senha visivel"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Visivel por <span x-text="secondsLeft"></span>s.
                                                </div>
                                            @else
                                                <div class="flex items-center gap-2">
                                                    <span class="rounded border border-gray-300 px-2 py-1 text-sm text-gray-700">
                                                        ****************
                                                    </span>
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center justify-center rounded border border-gray-300 p-1 text-gray-700 hover:bg-gray-50"
                                                        title="Mostrar senha"
                                                        aria-label="Mostrar senha"
                                                        x-on:click="$dispatch('open-modal', 'unlock-master-password')"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 1l22 22"></path>
                                                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.86 21.86 0 0 1 5.06-6.94"></path>
                                                            <path d="M9.53 9.53A3.5 3.5 0 0 0 12 15.5a3.5 3.5 0 0 0 2.47-.97"></path>
                                                            <path d="M14.47 14.47L9.53 9.53"></path>
                                                            <path d="M21.94 12s-1.4-2.8-4.56-5.06A10.94 10.94 0 0 0 12 4c-.84 0-1.65.09-2.44.26"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $createdAtLabel }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $updatedAtLabel }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-600" colspan="4">
                                            Nenhum usuario correspondente ao login atual foi encontrado em <code>lumia_auth_users</code>.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <script>
                        document.addEventListener('click', async (event) => {
                            const button = event.target.closest('[data-copy-value]');
                            if (!button) return;
                            const value = button.getAttribute('data-copy-value') || '';
                            try {
                                await navigator.clipboard.writeText(value);
                                const original = button.textContent;
                                button.textContent = 'Copiado!';
                                setTimeout(() => {
                                    button.textContent = original;
                                }, 1000);
                            } catch (_) {
                                // Clipboard may be blocked by browser policy.
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="unlock-master-password" :show="$errors->has('master_password')" maxWidth="md" focusable>
        <form method="post" action="{{ route('settings.users.unlock-password') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">
                Liberar visualizacao da senha
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                Digite a senha atual do usuario master (ID 1). A senha ficara visivel por 2 minutos.
            </p>

            <div class="mt-4">
                <x-input-label for="master_password" value="Senha master atual" />
                <x-text-input id="master_password" name="master_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('master_password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>
                <x-primary-button>
                    Confirmar
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>

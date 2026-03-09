<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Perfil
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <aside class="rounded-lg border border-gray-200 p-4 sm:p-6 bg-white/50">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-semibold text-gray-900">Aviso importante</h3>
                            <span class="inline-flex items-center rounded-md border border-amber-300 bg-amber-100/80 px-2 py-1 text-xs font-semibold text-amber-900">
                                Em desenvolvimento
                            </span>
                        </div>

                        <p class="mt-3 text-sm leading-6 text-gray-700">
                            O e-mail cadastrado neste perfil sera usado para recuperacao e troca de senha em caso de perda ou esquecimento.
                        </p>

                        <p class="mt-2 text-xs text-gray-500">
                            Em breve este processo sera integrado ao fluxo completo de recuperacao de acesso.
                        </p>
                    </aside>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

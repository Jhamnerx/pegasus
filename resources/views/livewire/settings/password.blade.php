<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

    <!-- Page header -->
    <div class="mb-8">
        <!-- Title -->
        <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Configuraciones</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl mb-8">
        <div class="flex flex-col md:flex-row md:-mr-px">

            <!-- Sidebar -->
            <x-settings.settings-sidebar />

            <!-- Panel -->
            <x-settings.account-panel>
                <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Seguridad</h2>

                <!-- Password -->
                <section>
                    <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1">Cambiar
                        Contraseña</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-5">Asegúrate de que tu cuenta use una
                        contraseña larga y aleatoria para mantenerla segura.</div>

                    <form method="POST" wire:submit="updatePassword" class="space-y-4">
                        <div class="space-y-4">
                            <flux:input wire:model="current_password" label="Contraseña Actual" type="password" required
                                autocomplete="current-password" />
                            <flux:input wire:model="password" label="Nueva Contraseña" type="password" required
                                autocomplete="new-password" />
                            <flux:input wire:model="password_confirmation" label="Confirmar Nueva Contraseña"
                                type="password" required autocomplete="new-password" />
                        </div>

                        <div class="flex items-center gap-4 pt-4">
                            <flux:button variant="primary" type="submit">Actualizar Contraseña</flux:button>
                            <x-action-message class="me-3" on="password-updated">
                                Guardado.
                            </x-action-message>
                        </div>
                    </form>
                </section>
            </x-settings.account-panel>

        </div>
    </div>

</div>

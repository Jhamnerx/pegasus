<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

    <!-- Page header -->
    <div class="mb-8">

        <!-- Title -->
        <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Account Settings</h1>

    </div>

    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl mb-8">
        <div class="flex flex-col md:flex-row md:-mr-px">

            <!-- Sidebar -->
            <x-settings.settings-sidebar />

            <!-- Panel -->
            <x-settings.account-panel>
                <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Perfil de Usuario</h2>

                <!-- Profile Information -->
                <section>
                    <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1">Información
                        Personal</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-5">Actualiza tu nombre y dirección de
                        correo electrónico.</div>

                    <form wire:submit="updateProfileInformation" class="space-y-4">
                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                            <div class="sm:w-1/2">
                                <flux:input wire:model="name" label="Nombre" type="text" required autofocus
                                    autocomplete="name" />
                            </div>
                            <div class="sm:w-1/2">
                                <flux:input wire:model="email" label="Correo Electrónico" type="email" required
                                    autocomplete="email" />
                            </div>
                        </div>

                        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                            <div
                                class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <flux:text class="text-yellow-800 dark:text-yellow-200">
                                    Tu dirección de correo electrónico no está verificada.
                                    <flux:link class="text-sm cursor-pointer underline ml-2"
                                        wire:click.prevent="resendVerificationNotification">
                                        Haz clic aquí para reenviar el correo de verificación.
                                    </flux:link>
                                </flux:text>

                                @if (session('status') === 'verification-link-sent')
                                    <flux:text class="mt-2 font-medium text-green-600 dark:text-green-400">
                                        Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                                    </flux:text>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center gap-4 pt-4">
                            <flux:button variant="primary" type="submit">Guardar Cambios</flux:button>
                            <x-action-message class="me-3" on="profile-updated">
                                Guardado.
                            </x-action-message>
                        </div>
                    </form>
                </section>

                <!-- Delete Account -->
                <section class="mt-10">
                    <livewire:settings.delete-user-form />
                </section>
            </x-settings.account-panel>

        </div>
    </div>

</div>

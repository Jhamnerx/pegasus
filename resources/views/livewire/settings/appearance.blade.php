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
                <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Apariencia</h2>

                <!-- Appearance Settings -->
                <section>
                    <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1">Tema</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-5">Actualiza la configuración de apariencia
                        para tu cuenta.</div>

                    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                        <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                        <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                        <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                    </flux:radio.group>
                </section>
            </x-settings.account-panel>

        </div>
    </div>

</div>

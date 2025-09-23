<div
    class="flex flex-nowrap overflow-x-scroll no-scrollbar md:block md:overflow-auto px-3 py-6 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700/60 min-w-60 md:space-y-6">

    <!-- BUSINESS SETTINGS -->
    @if (auth()->user()->hasRole('Administrador'))
        <div>
            <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3">Business settings</div>
            <ul class="flex flex-nowrap md:block mr-3 md:mr-0">

                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if (Route::is('settings.empresa')) {{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif"
                        href="{{ route('settings.empresa') }}">
                        <svg class="shrink-0 fill-current mr-2 @if (Route::is('settings.empresa')) {{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif"
                            width="16" height="16" viewBox="0 0 16 16">
                            <path
                                d="M8 9a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm-5.143 7.91a1 1 0 1 1-1.714-1.033A7.996 7.996 0 0 1 8 10a7.996 7.996 0 0 1 6.857 3.877 1 1 0 1 1-1.714 1.032A5.996 5.996 0 0 0 8 12a5.996 5.996 0 0 0-5.143 2.91Z" />
                        </svg>
                        <span
                            class="text-sm font-medium @if (Route::is('settings.empresa')) {{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }} @endif">
                            Empresa</span>
                    </a>
                </li>
                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if (Route::is('settings.plantillas-mensajes')) {{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif"
                        href="{{ route('settings.plantillas-mensajes') }}">
                        <svg class="shrink-0 fill-current mr-2 @if (Route::is('settings.plantillas-mensajes')) {{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif"
                            width="16" height="16" viewBox="0 0 16 16">
                            <path
                                d="M2 3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3zm2 1v2h8V4H4zm0 4v2h5V8H4zm0 4v1h8v-1H4z" />
                        </svg>
                        <span
                            class="text-sm font-medium @if (Route::is('settings.plantillas-mensajes')) {{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }} @endif">
                            Plantillas de Mensajes</span>
                    </a>
                </li>
                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if (Route::is('settings.profile')) {{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif"
                        href="{{ route('settings.profile') }}">
                        <svg class="shrink-0 fill-current mr-2 @if (Route::is('settings.profile')) {{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif"
                            width="16" height="16" viewBox="0 0 16 16">
                            <path
                                d="M8 9a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm-5.143 7.91a1 1 0 1 1-1.714-1.033A7.996 7.996 0 0 1 8 10a7.996 7.996 0 0 1 6.857 3.877 1 1 0 1 1-1.714 1.032A5.996 5.996 0 0 0 8 12a5.996 5.996 0 0 0-5.143 2.91Z" />
                        </svg>
                        <span
                            class="text-sm font-medium @if (Route::is('settings.profile')) {{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }} @endif">Mi
                            Perfil</span>
                    </a>
                </li>
            </ul>
        </div>
    @endif
</div>

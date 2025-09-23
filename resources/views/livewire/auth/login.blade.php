<div class="space-y-6">
    <!-- Title -->
    <div class="text-center mb-6">
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Iniciar Sesión</h3>
        <p class="text-sm text-gray-600">
            Ingresa tus credenciales para acceder al sistema
        </p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="rounded-lg bg-green-50 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Login Success Message -->
    @if (session('login_success'))
        <div class="rounded-lg bg-green-50 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('login_success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Login Form -->
    <form wire:submit="login" class="space-y-5">

        <!-- Email -->
        <div>
            <x-input label="Correo Electrónico" id="email" wire:model="email" name="email" required autofocus
                placeholder="Ingresa tu correo electrónico" wire:loading.attr="disabled" autocomplete="email" />
        </div>

        <!-- Password -->
        <div>
            <x-password label="Contraseña" id="password" wire:model="password" name="password" required
                placeholder="Ingresa tu contraseña" wire:loading.attr="disabled" autocomplete="current-password" />
        </div>

        <!-- Remember Me and Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <x-checkbox id="remember" wire:model="remember" label="Recordarme" />
            </div>

            @if (Route::has('password.request'))
                <div>
                    <a href="{{ route('password.request') }}" wire:navigate
                        class="text-sm text-blue-600 hover:text-blue-500 transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            @endif
        </div>

        <!-- Error message -->
        @if ($error)
            <div class="rounded-lg bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Submit Button -->
        <div class="pt-2">
            <x-button wire:loading.attr="disabled" primary full size="lg" wire:click.prevent="login"
                spinner="login" wire:target="login">
                Ingresar
            </x-button>
        </div>
    </form>

    {{-- <!-- Register Link -->
    @if (Route::has('register'))
        <div class="text-center pt-4 border-t border-gray-200">
            <p class="text-sm text-gray-600">
                ¿No tienes una cuenta?
                <a href="{{ route('register') }}" wire:navigate
                    class="text-blue-600 hover:text-blue-500 font-medium transition-colors">
                    Registrarse
                </a>
            </p>
        </div>
    @endif --}}

    <!-- Additional Info -->
    <div class="text-center pt-4 {{ Route::has('register') ? '' : 'border-t border-gray-200' }}">
        <div class="text-xs text-gray-500 space-y-1">
            <p>Sistema de Gestión de Cobranzas</p>
        </div>
    </div>
</div>

<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    #[Validate('required|string')]
    public string $username = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
    public bool $isLoading = false;
    public string $error = '';

    public function mount(): void
    {
        $this->clearError();
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->isLoading = true;
        $this->clearError();

        $this->validate();

        try {
            $this->ensureIsNotRateLimited();

            // Intentar login con email o username
            $credentials = $this->getCredentials();

            if (! Auth::attempt($credentials, $this->remember)) {
                RateLimiter::hit($this->throttleKey());

                $this->error = 'Credenciales incorrectas. Por favor verifica tu usuario y contraseña.';
                $this->isLoading = false;
                return;
            }

            RateLimiter::clear($this->throttleKey());
            Session::regenerate();

            // Mensaje de bienvenida
            session()->flash('login_success', '¡Bienvenido! Sesión iniciada como ' . Auth::user()->name);

            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } catch (ValidationException $e) {
            $this->error = $e->getMessage();
        } catch (\Exception $e) {
            $this->error = 'Error de conexión. No se pudo conectar con el servidor.';
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Get credentials for authentication
     */
    private function getCredentials(): array
    {
        // Determinar si es email o username
        $field = filter_var($this->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        return [
            $field => $this->username,
            'password' => $this->password
        ];
    }

    /**
     * Clear error message
     */
    public function clearError(): void
    {
        $this->error = '';
    }

    /**
     * Get app information
     */
    public function getAppNameProperty(): string
    {
        return config('app.name', 'PEGASUS');
    }

    public function getAppVersionProperty(): string
    {
        return '1.0.0';
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => "Demasiados intentos de inicio de sesión. Por favor intenta de nuevo en {$seconds} segundos.",
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username) . '|' . request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}

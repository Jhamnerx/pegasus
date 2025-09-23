<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $empresaConfig->razon_social ?? config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->

    @livewireStyles
    <wireui:scripts />

    <!-- WireUI Styles -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <style>
        /* Custom gradient background - PEGASUS theme */
        .bg-gradient-pegasus {
            background: linear-gradient(135deg, #5b6df7 0%, #7c3aed 50%, #a855f7 100%);
        }

        /* Text shadow effect */
        .text-shadow-lg {
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        /* Animations */
        .auth-content {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form styles */
        .form-input {
            @apply w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200;
        }

        .btn-primary {
            @apply w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed;
        }

        .form-label {
            @apply block text-sm font-medium text-gray-700 mb-2;
        }
    </style>
</head>

<body class="font-inter antialiased">
    <div class="min-h-screen bg-gradient-pegasus flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-sm w-full space-y-6">
            <!-- Logo and Title -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 flex items-center justify-center bg-white rounded-full shadow-lg mb-4">
                    @if ($empresaConfig && $empresaConfig->logo)
                        <img src="{{ $empresaConfig->logo }}" alt="{{ $empresaConfig->razon_social ?? 'Logo' }}"
                            class="h-10 w-10 object-contain" />
                    @else
                        <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNDY1IDEwMjQiPjxzdHlsZT4uc3QwLC5zdDF7ZmlsbDojNGE2ZGE3fTwvc3R5bGU+PHBhdGggY2xhc3M9InN0MCIgZD0iTTY0MyAyMDhoNTIzdjY0SDY0M3oiLz48cGF0aCBjbGFzcz0ic3QwIiBkPSJNNjQzIDI0MGgzNTJWMzA0SDY0M3pNNjQzIDMwNGgzNTJWMzY4SDY0M3pNOTk1IDQzMmgxNzF2NjRIOTk1ek03MTUgNDMyaDE3MXY2NEg3MTV6TTY0MyA0OTZoMzUyVjU2MEg2NDN6TTY0MyA1NjBoNTIzdjY0SDY0M3pNNzE1IDYyNGgyNzl2NjRINzE1eiIvPjxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0zMTEgNjA2czE1OC0xMzQgMjkwIDE4YzE1MyA0Ni04MSAxMTgtODEgMTE4cy0xNTQgNzYtMjM5LTUyYy0zNi02OCAxOS0xMTEgMzAtODR6Ii8+PHBhdGggY2xhc3M9InN0MCIgZD0iTTQ4MiA1MDYgMzExIDYwNnMxMDAtNDIgMTcxLTEwMHoiLz48cGF0aCBjbGFzcz0ic3QxIiBkPSJNNjg1IDU3OXMxNTgtMTM0IDI5MCAxOGMxNTMgNDYtODEgMTE4LTgxIDExOHMtMTU0IDc2LTIzOS01MmMtMzYtNjggMTktMTExIDMwLTg0eiIvPjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik04NTYgNDc5IDY4NSA1Nzlz MTAwLTQyIDE3MS0xMDB6Ci8+PHBhdGggY2xhc3M9InN0MSIgZD0iTTQ5OCA3OTBzMTU4LTEzNCAyOTAgMThjMTUzIDQ2LTgxIDExOC04MSAxMThzLTE1NCA3Ni0yMzktNTJjLTM2LTY4IDE5LTExMSAzMC04NHoiLz48cGF0aCBjbGFzcz0ic3QwIiBkPSJNNjY5IDY5MCA0OTggNzkwczEwMC00MiAxNzEtMTAweiIvPjwvc3ZnPg=="
                            alt="PEGASUS Logo" class="h-10 w-10" />
                    @endif
                </div>
                <h2 class="text-2xl font-bold text-white text-shadow-lg mb-2">
                    {{ $empresaConfig->razon_social ?? 'Sistema PEGASUS' }}
                </h2>
                <p class="text-sm text-white opacity-90 mb-6">
                    Gestión integral de cobranzas
                </p>
            </div>

            <!-- Auth Content -->
            <div class="bg-white rounded-xl shadow-2xl p-8 auth-content">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-white opacity-80 space-y-1">
                <p>&copy; {{ date('Y') }} {{ $empresaConfig->razon_social ?? 'PEGASUS' }}. Todos los derechos
                    reservados.</p>
                <p>Desarrollado para Synthesis Group</p>
            </div>
        </div>

        <!-- Background Pattern -->
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <svg class="absolute left-[max(50%,25rem)] top-0 h-[64rem] w-[128rem] -translate-x-1/2 stroke-gray-200 opacity-20 [mask-image:radial-gradient(64rem_64rem_at_top,white,transparent)]"
                aria-hidden="true">
                <defs>
                    <pattern id="e813992c-7d03-4cc4-a2bd-151760b470a0" width="200" height="200" x="50%" y="-1"
                        patternUnits="userSpaceOnUse">
                        <path d="M100 200V.5M.5 .5H200" fill="none" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" stroke-width="0"
                    fill="url(#e813992c-7d03-4cc4-a2bd-151760b470a0)" />
            </svg>
        </div>

        <!-- Loading Overlay -->
        <div wire:loading.delay class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            style="display: none;">
            <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-700">Cargando...</span>
            </div>
        </div>
    </div>

    @livewireScripts
</body>

</html>

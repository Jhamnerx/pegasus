<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Pegasus GPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .step-item {
            transition: all 0.3s ease;
        }
        .step-success {
            background-color: #d1fae5;
            border-color: #10b981;
        }
        .step-error {
            background-color: #fee2e2;
            border-color: #ef4444;
        }
        .step-warning {
            background-color: #fef3c7;
            border-color: #f59e0b;
        }
        .step-pending {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-600">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Instalación de Pegasus GPS
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Sistema de gestión GPS y cobranzas
                </p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div id="pre-install" class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Antes de continuar</h3>
                    <p class="text-sm text-gray-600">
                        Asegúrate de haber configurado correctamente el archivo <code class="bg-gray-100 px-1 rounded">.env</code> 
                        con los datos de tu base de datos y demás configuraciones.
                    </p>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Importante
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Este proceso:</p>
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Verificará los requisitos del sistema</li>
                                        <li>Generará la clave de aplicación</li>
                                        <li>Ejecutará las migraciones de base de datos</li>
                                        <li>Optimizará la aplicación para producción</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button id="start-install" onclick="startInstallation()" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Iniciar Instalación
                    </button>
                </div>

                <div id="installation-progress" class="hidden">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Progreso de Instalación</h3>
                    
                    <div id="steps-container" class="space-y-3">
                        <!-- Los pasos se llenarán dinámicamente -->
                    </div>

                    <div id="installation-result" class="mt-6 hidden">
                        <div id="success-message" class="bg-green-50 border border-green-200 rounded-md p-4 hidden">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        ¡Instalación Completada!
                                    </h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>Pegasus GPS se ha instalado correctamente. Ya puedes comenzar a usar el sistema.</p>
                                    </div>
                                    <div class="mt-4">
                                        <a href="/" class="text-sm font-medium text-green-800 hover:text-green-600">
                                            Ir al Sistema →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="error-message" class="bg-red-50 border border-red-200 rounded-md p-4 hidden">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        Error durante la instalación
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p id="error-text">Ha ocurrido un error durante la instalación.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function startInstallation() {
            document.getElementById('pre-install').classList.add('hidden');
            document.getElementById('installation-progress').classList.remove('hidden');

            try {
                const response = await fetch('/install/run', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                
                displaySteps(data.steps);
                
                document.getElementById('installation-result').classList.remove('hidden');
                
                if (data.success) {
                    document.getElementById('success-message').classList.remove('hidden');
                } else {
                    document.getElementById('error-message').classList.remove('hidden');
                    document.getElementById('error-text').textContent = data.error || 'Error desconocido';
                }
                
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('installation-result').classList.remove('hidden');
                document.getElementById('error-message').classList.remove('hidden');
                document.getElementById('error-text').textContent = 'Error de conexión: ' + error.message;
            }
        }

        function displaySteps(steps) {
            const container = document.getElementById('steps-container');
            container.innerHTML = '';

            steps.forEach((step, index) => {
                const stepElement = createStepElement(step, index + 1);
                container.appendChild(stepElement);
            });
        }

        function createStepElement(step, number) {
            const div = document.createElement('div');
            div.className = `step-item p-3 border-l-4 rounded ${getStepClass(step.status)}`;
            
            div.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        ${getStepIcon(step.status)}
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            ${number}. ${step.name}
                        </p>
                        <p class="text-sm text-gray-600">${step.message}</p>
                        ${step.details ? `<pre class="text-xs text-gray-500 mt-1 overflow-auto max-h-20">${typeof step.details === 'object' ? JSON.stringify(step.details, null, 2) : step.details}</pre>` : ''}
                    </div>
                </div>
            `;
            
            return div;
        }

        function getStepClass(status) {
            switch (status) {
                case 'success': return 'step-success';
                case 'error': return 'step-error';
                case 'warning': return 'step-warning';
                default: return 'step-pending';
            }
        }

        function getStepIcon(status) {
            switch (status) {
                case 'success':
                    return '<svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
                case 'error':
                    return '<svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
                case 'warning':
                    return '<svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
                default:
                    return '<div class="spinner"></div>';
            }
        }
    </script>
</body>
</html>
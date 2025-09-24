<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;
    private string $apiKey;
    private string $sender;

    public function __construct()
    {
        $this->apiUrl = config('whatsapp.api_url');
        $this->apiKey = config('whatsapp.api_key');
        $this->sender = config('whatsapp.sender');
    }

    /**
     * Enviar mensaje de texto simple
     */
    public function sendMessage(string $number, string $message): bool
    {
        try {
            $data = [
                'api_key' => $this->apiKey,
                'sender' => $this->sender,
                'number' => $this->limpiarTelefono($number),
                'message' => $message
            ];

            Log::info("Enviando mensaje WhatsApp con datos:", $data);

            $response = Http::asForm()->post($this->apiUrl, $data);

            if ($response->successful()) {
                Log::info("Mensaje WhatsApp enviado exitosamente a: {$number}");
                Log::info("Respuesta de la API: " . $response->body());
                return true;
            } else {
                Log::error("Error enviando mensaje WhatsApp:", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'sent_data' => $data
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Excepción enviando mensaje WhatsApp: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar mensaje con archivo adjunto (PDF, imagen, etc.)
     */
    public function sendMedia(string $number, string $message, string $mediaUrl, string $mediaType = 'pdf'): bool
    {
        try {
            // Usar el endpoint de archivos
            $mediaEndpoint = str_replace('/send-message', '/send-media', $this->apiUrl);

            $data = [
                'api_key' => $this->apiKey,
                'sender' => $this->sender,
                'number' => $this->limpiarTelefono($number),
                'media_type' => $mediaType,
                'caption' => $message,
                'url' => $mediaUrl
            ];

            Log::info("Enviando archivo WhatsApp con datos:", $data);

            $response = Http::asForm()->post($mediaEndpoint, $data);

            Log::info("Respuesta del servidor:", [
                'status' => $response->status(),
                'body' => $response->body(),
                'endpoint' => $mediaEndpoint
            ]);

            if ($response->successful()) {
                Log::info("Mensaje con archivo enviado exitosamente a: {$number}");
                return true;
            } else {
                Log::error("Error enviando archivo WhatsApp:", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'endpoint' => $mediaEndpoint,
                    'sent_data' => $data
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Excepción enviando archivo WhatsApp: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpiar número de teléfono para WhatsApp
     */
    private function limpiarTelefono(string $telefono): string
    {
        // Remover espacios, guiones y otros caracteres
        $telefono = preg_replace('/[^0-9]/', '', $telefono);

        // Si no empieza con 51, agregarlo (código de Perú)
        if (!str_starts_with($telefono, '51') && strlen($telefono) == 9) {
            $telefono = '51' . $telefono;
        }

        return $telefono;
    }

    /**
     * Verificar si las credenciales están configuradas
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiUrl) && !empty($this->apiKey) && !empty($this->sender);
    }

    /**
     * Generar URL pública del PDF para el recibo usando UUID (para visualizar)
     */
    public function generatePdfUrl(string $reciboUuid): string
    {
        return config('app.url') . "/recibo/{$reciboUuid}";
    }

    /**
     * Generar URL de descarga directa del PDF para APIs/WhatsApp
     */
    public function generatePdfDownloadUrl(string $reciboUuid): string
    {
        return config('app.url') . "/recibo/download/{$reciboUuid}";
    }
}

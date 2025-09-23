<?php

return [
    'api_url' => env('WHATSAPP_API_URL', 'http://messages.synthesisgroup.pe/send-message'),
    'api_key' => env('WHATSAPP_API_KEY'),
    'sender' => env('WHATSAPP_SENDER'),
];

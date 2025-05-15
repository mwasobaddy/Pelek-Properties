<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $accessToken;
    protected $phoneNumberId;

    public function __construct()
    {
        $this->apiUrl = 'https://graph.facebook.com/v17.0/';
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    public function sendMessage(string $to, string $message): bool
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl . $this->phoneNumberId . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $this->formatPhoneNumber($to),
                    'type' => 'text',
                    'text' => [
                        'body' => $message
                    ]
                ]);

            if (!$response->successful()) {
                Log::error('WhatsApp API Error', [
                    'response' => $response->json(),
                    'to' => $to
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error', [
                'message' => $e->getMessage(),
                'to' => $to
            ]);
            return false;
        }
    }

    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If the number starts with 0, replace it with 254 (Kenya)
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }
        
        return $phone;
    }
}

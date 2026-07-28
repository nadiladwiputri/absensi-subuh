<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send message using Telegram Bot API.
     *
     * @param string $chatId Recipient's Telegram Chat ID
     * @param string $message Message body
     * @return array Array containing success status and response message
     */
    public function sendMessage(string $chatId, string $message): array
    {
        $token = env('TELEGRAM_BOT_TOKEN');

        if (empty($token)) {
            Log::warning("Telegram bot token (TELEGRAM_BOT_TOKEN) belum dikonfigurasi di file .env");
            return [
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN belum dikonfigurasi di file .env'
            ];
        }

        try {
            $response = Http::timeout(5)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim via Telegram.'
                ];
            }

            $errorData = $response->json();
            return [
                'success' => false,
                'message' => 'Telegram API Error: ' . ($errorData['description'] ?? 'Gagal mengirim')
            ];

        } catch (\Exception $e) {
            Log::error("Gagal mengirim Telegram ke $chatId: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem Telegram: ' . $e->getMessage()
            ];
        }
    }
}

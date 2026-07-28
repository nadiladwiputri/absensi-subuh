<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramPollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start long polling for Telegram Bot to automatically reply with User Chat ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = env('TELEGRAM_BOT_TOKEN');

        if (empty($token)) {
            $this->error("[Telegram] ERROR: TELEGRAM_BOT_TOKEN belum diset di file .env!");
            return 1;
        }

        $this->info("=================================================");
        $this->info(" Starting Telegram Bot Long Polling Handler");
        $this->info(" Token : " . substr($token, 0, 10) . "...");
        $this->info(" Status: Listening for updates... (Press Ctrl+C to stop)");
        $this->info("=================================================");

        $offset = 0;

        while (true) {
            try {
                // Poll for updates (wait up to 5 seconds per call)
                $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 5,
                ]);

                if ($response->successful()) {
                    $updates = $response->json('result') ?? [];

                    foreach ($updates as $update) {
                        $updateId = $update['update_id'];
                        $offset = $updateId + 1; // Update offset to avoid reprocessing

                        if (isset($update['message'])) {
                            $message = $update['message'];
                            $chatId = $message['chat']['id'];
                            $text = $message['text'] ?? '';
                            $firstName = $message['from']['first_name'] ?? 'Wali Santri';

                            $this->info("[Telegram] Received message from {$firstName} (Chat ID: {$chatId}): '{$text}'");

                            if (trim($text) === '/start') {
                                $replyText = "Assalamualaikum Bpk/Ibu <b>{$firstName}</b>,\n\n" .
                                             "Chat ID Telegram Anda adalah: <code>{$chatId}</code>\n\n" .
                                             "Silakan salin angka di atas dan tempelkan (*paste*) ke kolom <b>Telegram Chat ID</b> pada halaman pendaftaran akun Wali di website absensi Anda.\n\n" .
                                             "Setelah akun Anda tersimpan, Anda akan otomatis menerima notifikasi absensi Subuh secara real-time!";

                                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                                    'chat_id' => $chatId,
                                    'text' => $replyText,
                                    'parse_mode' => 'HTML',
                                ]);

                                $this->info("[Telegram] Sent Chat ID response to {$firstName}");
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("[Telegram Error] " . $e->getMessage());
            }

            sleep(1); // Wait 1 second before next poll
        }
    }
}

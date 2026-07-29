<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Santri;
use App\Models\AbsensiSubuh;
use App\Models\FingerprintDeletion;
use App\Models\Wali;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MqttSubscribeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for encrypted MQTT TLS attendance and sensor events from ESP32';

    /**
     * Send automated Telegram notification to parents.
     */
    private function sendTelegramNotification($santri, $status, $time)
    {
        $wali = Wali::where('no_hp', $santri->no_hp_ortu)->first();
        if (!$wali || empty($wali->telegram_chat_id)) {
            $this->warn("[Telegram] Tidak dapat mengirim notifikasi: Akun Wali atau Telegram Chat ID belum terdaftar untuk nomor {$santri->no_hp_ortu}");
            return;
        }

        try {
            $message = "<b>Laporan Absensi Subuh</b>\n\n" .
                       "Nama: <b>{$santri->nama_santri}</b>\n" .
                       "Waktu: <code>{$time}</code>\n" .
                       "Status Kehadiran: <b>{$status}</b>\n\n" .
                       "<i>Semoga ananda senantiasa istiqomah dalam ibadah.</i>";

            $tgService = new \App\Services\TelegramService();
            $result = $tgService->sendMessage($wali->telegram_chat_id, $message);

            if (!$result['success']) {
                $this->warn("[Telegram] Gagal mengirim ke Chat ID {$wali->telegram_chat_id}: " . $result['message']);
            } else {
                $this->info("[Telegram] Sukses mengirim ke Chat ID {$wali->telegram_chat_id}: " . $result['message']);
            }
        } catch (\Exception $e) {
            Log::error("[Telegram] Terjadi Exception saat mengirim ke Chat ID {$wali->telegram_chat_id}: " . $e->getMessage());
        }
    }

    /**
     * Get Fajr prayer time for West Sumatra (Padang).
     */
    private function getFajrTime()
    {
        $today = date('Y-m-d');
        
        return Cache::remember("fajr_schedule_{$today}", 86400, function () {
            try {
                $dateStr = date('d-m-Y');
                $response = Http::timeout(1.5)->get("https://api.aladhan.com/v1/timings/{$dateStr}", [
                    'latitude' => env('PRAYER_LATITUDE', -0.9471),
                    'longitude' => env('PRAYER_LONGITUDE', 100.4172),
                    'method' => 20, // Kemenag RI
                ]);
                
                if ($response->successful()) {
                    $fajr = $response->json('data.timings.Fajr');
                    if ($fajr) {
                        return substr($fajr, 0, 5); // Return "hh:mm"
                    }
                }
            } catch (\Exception $e) {
                // Ignore and fallback
            }
            return '05:01'; // Default fallback
        });
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server   = env('MQTT_HOST', 'test.mosquitto.org');
        $port     = (int) env('MQTT_PORT', 8883);
        $clientId = env('MQTT_CLIENT_ID', 'laravel_subuh_monitor_') . uniqid();
        $useTls   = filter_var(env('MQTT_USE_TLS', true), FILTER_VALIDATE_BOOLEAN);

        $this->info("=================================================");
        $this->info(" Starting Subuh Monitor MQTT TLS Worker");
        $this->info(" Server : {$server}:{$port}");
        $this->info(" Client : {$clientId}");
        $this->info(" Security: TLS/SSL Encrypted (Port 8883)");
        $this->info("=================================================");

        $connectionSettings = (new ConnectionSettings())
            ->setKeepAliveInterval(60)
            ->setConnectTimeout(10)
            ->setReconnectAutomatically(true);

        if ($useTls) {
            $connectionSettings = $connectionSettings
                ->setUseTls(true)
                ->setTlsSelfSignedAllowed(true)
                ->setTlsVerifyPeer(false)
                ->setTlsVerifyPeerName(false);
        }

        while (true) {
            $maxRetries = 10;
            $attempt = 0;
            $connected = false;
            $mqtt = null;

            while ($attempt < $maxRetries && !$connected) {
                try {
                    $attempt++;
                    $clientId = env('MQTT_CLIENT_ID', 'laravel_subuh_monitor_') . uniqid();
                    $mqtt = new MqttClient($server, $port, $clientId);
                    $mqtt->connect($connectionSettings);
                    $connected = true;
                    $this->info("[MQTT TLS] Connected securely to {$server}:{$port}");
                } catch (\Exception $e) {
                    $this->warn("[MQTT TLS] Connection attempt {$attempt} failed: " . $e->getMessage() . ". Retrying in 2s...");
                    sleep(2);
                }
            }

            // 1. Subscribe to Scan Events (ESP32 -> Server)
            $mqtt->subscribe('subuh_monitor/attendance/scan', function ($topic, $message) use ($mqtt) {
                $this->info("[MQTT TLS] Received Scan Event: {$message}");
                $data = json_decode($message, true);

                if (!isset($data['fingerprint_id'])) {
                    return;
                }

                $fingerprintId = (int) $data['fingerprint_id'];
                
                $santri = Santri::where('fingerprint_id', $fingerprintId)->first();
                if (!$santri) {
                    $santri = Santri::create([
                        'nama_santri' => 'Santri Baru (ID #' . $fingerprintId . ')',
                        'fingerprint_id' => $fingerprintId,
                        'no_hp_ortu' => '081234567890',
                        'status' => 'Aktif',
                    ]);
                }

                if ($santri->status !== 'Aktif') {
                    $responsePayload = json_encode([
                        'status' => 'INACTIVE',
                        'message' => 'Santri tidak aktif',
                        'nama' => $santri->nama_santri,
                        'poin' => 0,
                    ]);
                    $mqtt->publish("subuh_monitor/attendance/response/{$fingerprintId}", $responsePayload, 0);
                    return;
                }

                $now = now();
                $attendanceDate = $now->format('Y-m-d');
                $fajrTime = $this->getFajrTime();
                list($fajrHour, $fajrMinute) = explode(':', $fajrTime);

                $nowSec = $now->hour * 3600 + $now->minute * 60 + $now->second;
                $fajrSec = $fajrHour * 3600 + $fajrMinute * 60;
                
                $windowOpenSec = $fajrSec - (5 * 60);
                $lateThresholdSec = $fajrSec + (40 * 60); // 40 minutes after adzan

                if ($nowSec >= $windowOpenSec && $nowSec <= $fajrSec) {
                    $status = 'Hadir';
                    $poin = 100;
                    $keterangan = 'Tepat waktu';
                    $messageStr = 'Absensi berhasil (Tepat Waktu)';
                } elseif ($nowSec > $fajrSec && $nowSec <= $lateThresholdSec) {
                    $status = 'Terlambat';
                    $minutesLate = ceil(($nowSec - $fajrSec) / 60);
                    $poin = 50;
                    $keterangan = "Terlambat {$minutesLate}m";
                    $messageStr = "Absensi berhasil (Terlambat {$minutesLate}m)";
                } else {
                    $responsePayload = json_encode([
                        'status' => 'ABSEN TUTUP',
                        'message' => 'Absensi ditutup',
                        'nama' => $santri->nama_santri,
                        'poin' => 0,
                    ]);
                    $mqtt->publish("subuh_monitor/attendance/response/{$fingerprintId}", $responsePayload, 0);
                    return;
                }

                // Check duplicate today
                $alreadyLogged = AbsensiSubuh::where('id_santri', $santri->id_santri)
                    ->where('tanggal', $attendanceDate)
                    ->first();

                if ($alreadyLogged) {
                    $responsePayload = json_encode([
                        'status' => 'SDH ABSEN',
                        'message' => 'Sudah absen hari ini',
                        'nama' => $santri->nama_santri,
                        'poin' => $alreadyLogged->poin,
                    ]);
                    $mqtt->publish("subuh_monitor/attendance/response/{$fingerprintId}", $responsePayload, 0);
                    return;
                }

                $absensi = AbsensiSubuh::create([
                    'id_santri' => $santri->id_santri,
                    'waktu_absensi' => $now,
                    'tanggal' => $attendanceDate,
                    'jadwal_subuh' => "{$fajrHour}:{$fajrMinute}:00",
                    'status_kehadiran' => $status,
                    'poin' => $poin,
                    'keterangan' => $keterangan,
                ]);

                // Cache for SSE live dashboard
                Cache::put('latest_absensi_scan', [
                    'id_absensi' => $absensi->id_absensi,
                    'nama_santri' => $santri->nama_santri,
                    'waktu' => $now->format('H:i:s'),
                    'status' => $status,
                    'poin' => $poin,
                    'keterangan' => $keterangan,
                ], 60);

                // Send Telegram Notification to parents
                $time = $now->format('H:i:s');
                $this->sendTelegramNotification($santri, $status, $time);

                // Publish encrypted response back to ESP32 for OLED feedback
                $responsePayload = json_encode([
                    'status' => $status,
                    'message' => $messageStr,
                    'nama' => $santri->nama_santri,
                    'poin' => $poin,
                ]);
                $mqtt->publish("subuh_monitor/attendance/response/{$fingerprintId}", $responsePayload, 0);
                $this->info("[MQTT TLS] Sent response back to ESP32: {$responsePayload}");
            }, 0);

            // 2. Subscribe to Confirm Enroll (ESP32 -> Server)
            $mqtt->subscribe('subuh_monitor/fingerprint/confirm-enroll', function ($topic, $message) {
                $this->info("[MQTT TLS] Received Confirm Enroll: {$message}");
                $data = json_decode($message, true);

                if (!isset($data['fingerprint_id'], $data['status'])) {
                    return;
                }

                $enrollId = (int)$data['fingerprint_id'];
                $status = $data['status'];
                $msg = $data['message'] ?? ($status === 'success' ? 'Sidik jari berhasil direkam!' : 'Proses pemindaian gagal.');

                Cache::forget('pending_enroll_id');

                if ($status === 'success') {
                    $santriId = Cache::get('enroll_santri_id');
                    if ($santriId) {
                        $santri = Santri::find($santriId);
                        if ($santri) {
                            $santri->update(['fingerprint_id' => $enrollId]);
                            $msg = "Sidik jari berhasil dihubungkan ke {$santri->nama_santri}!";
                        }
                        Cache::forget('enroll_santri_id');
                    }
                } else {
                    Cache::forget('enroll_santri_id');
                }

                Cache::put('enroll_status', [
                    'status' => $status,
                    'fingerprint_id' => $enrollId,
                    'message' => $msg
                ], 60);
            }, 0);

            // 3. Subscribe to Confirm Deletion (ESP32 -> Server)
            $mqtt->subscribe('subuh_monitor/fingerprint/confirm-deletion', function ($topic, $message) {
                $this->info("[MQTT TLS] Received Confirm Deletion: {$message}");
                $data = json_decode($message, true);

                if (isset($data['fingerprint_id'])) {
                    $deleteId = (int)$data['fingerprint_id'];
                    FingerprintDeletion::where('fingerprint_id', $deleteId)->update(['status' => 'completed']);
                }
            }, 0);

            try {
                // Loop to process messages continuously
                $mqtt->loop(true);
            } catch (\Exception $e) {
                $this->error("[MQTT TLS Error] " . $e->getMessage());
                Log::error("[MQTT TLS Error] " . $e->getMessage());
                $this->warn("[MQTT TLS] Disconnected from broker. Auto-reconnecting in 2s...");
                sleep(2);
            }
        }
    }
}

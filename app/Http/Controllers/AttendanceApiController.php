<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\AbsensiSubuh;
use Illuminate\Http\Request;
use App\Models\FingerprintDeletion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AttendanceApiController extends Controller
{
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
     * Handle scan data from ESP32.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'fingerprint_id' => 'required|integer',
        ]);

        $santri = Santri::where('fingerprint_id', $request->fingerprint_id)->first();
        if (!$santri) {
            // Automatically create a new santri profile so they don't get 404
            $santri = Santri::create([
                'nama_santri' => 'Santri Baru (ID #' . $request->fingerprint_id . ')',
                'fingerprint_id' => $request->fingerprint_id,
                'no_hp_ortu' => '081234567890',
                'status' => 'Aktif',
            ]);
        }

        if ($santri->status !== 'Aktif') {
            return response()->json([
                'status' => 'INACTIVE',
                'message' => 'Santri tidak aktif'
            ], 403);
        }

        $now = now();
        $attendanceDate = $now->format('Y-m-d');
        
        $fajrTime = $this->getFajrTime();
        list($fajrHour, $fajrMinute) = explode(':', $fajrTime);

        $nowSec = $now->hour * 3600 + $now->minute * 60 + $now->second;
        $fajrSec = $fajrHour * 3600 + $fajrMinute * 60;
        
        $windowOpenSec = $fajrSec; // Pas masuk waktu adzan
        $onTimeLimitSec = $fajrSec + (5 * 60); // 5 menit setelah adzan
        $lateThresholdSec = $fajrSec + (30 * 60); // 30 menit setelah adzan

        if ($request->input('simulate') == true) {
            $status = 'Hadir';
            $poin = 100;
            $keterangan = 'Tepat waktu (Simulasi)';
            $message = 'Absensi berhasil dicatat (Simulasi Tepat Waktu)';
        } elseif ($nowSec >= $windowOpenSec && $nowSec <= $onTimeLimitSec) {
            $status = 'Hadir';
            $poin = 100;
            $keterangan = 'Tepat waktu';
            $message = 'Absensi berhasil dicatat (Tepat Waktu)';
        } elseif ($nowSec > $onTimeLimitSec && $nowSec <= $lateThresholdSec) {
            $status = 'Terlambat';
            $minutesLate = ceil(($nowSec - $fajrSec) / 60);
            $poin = 50; // Terlambat flat 50 poin
            $keterangan = "Terlambat {$minutesLate} menit";
            $message = "Absency berhasil dicatat (Terlambat {$minutesLate} menit)";
        } else {
            // Jika di luar jam Subuh, tidak usah disimpan ke database
            return response()->json([
                'status' => 'ABSEN TUTUP',
                'message' => 'Absensi ditutup (Di luar jam Subuh)',
                'nama' => $santri->nama_santri,
                'poin' => 0,
            ]);
        }

        // Check if already logged on the target date
        $alreadyLogged = AbsensiSubuh::where('id_santri', $santri->id_santri)
            ->where('tanggal', $attendanceDate)
            ->first();

        if ($alreadyLogged) {
            return response()->json([
                'status' => 'SDH ABSEN',
                'message' => 'Anda sudah melakukan absensi hari ini',
                'nama' => $santri->nama_santri,
                'poin' => $alreadyLogged->poin,
            ]);
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

        // Send Telegram Notification to parents after response has been sent
        $time = $now->format('H:i:s');
        dispatch(function () use ($santri, $status, $time) {
            $this->sendTelegramNotification($santri, $status, $time);
        })->afterResponse();

        // Save to Cache to trigger SSE stream
        Cache::put('latest_absensi_scan', [
            'id_absensi' => $absensi->id_absensi,
            'nama_santri' => $santri->nama_santri,
            'waktu' => $now->format('H:i:s'),
            'status' => $status,
            'poin' => $poin,
            'keterangan' => $keterangan,
        ], 60);

        return response()->json([
            'status' => $status === 'Tidak Hadir' ? 'ABSN TUTUP' : $status,
            'message' => $message,
            'nama' => $santri->nama_santri,
            'poin' => $poin,
        ]);
    }

    /**
     * SSE Stream for Real-time Dashboard.
     */
    public function stream()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable buffering for Nginx if any

        $lastSentId = 0;

        // Run for a max of 25 seconds to respect server timeouts, browser will reconnect automatically
        $startTime = time();
        while ((time() - $startTime) < 25) {
            $latest = Cache::get('latest_absensi_scan');
            
            if ($latest && $latest['id_absensi'] !== $lastSentId) {
                $lastSentId = $latest['id_absensi'];
                echo "data: " . json_encode($latest) . "\n\n";
                ob_flush();
                flush();
            }

            sleep(1);
        }
    }

    /**
     * Get list of fingerprint IDs pending deletion.
     */
    public function getPendingDeletions()
    {
        $ids = FingerprintDeletion::where('status', 'pending')
            ->pluck('fingerprint_id')
            ->toArray();
            
        return response()->json($ids);
    }

    /**
     * Confirm completion of a fingerprint deletion on the sensor.
     */
    public function confirmDeletion(Request $request)
    {
        $request->validate([
            'fingerprint_id' => 'required|integer',
        ]);

        FingerprintDeletion::where('fingerprint_id', $request->fingerprint_id)
            ->update(['status' => 'completed']);

        return response()->json([
            'status' => 'success',
            'message' => 'Penghapusan sidik jari berhasil dikonfirmasi.'
        ]);
    }

    /**
     * Request a new fingerprint registration session from the web browser.
     */
    public function requestEnroll(Request $request)
    {
        $usedIds = Santri::whereNotNull('fingerprint_id')->pluck('fingerprint_id')->toArray();
        $nextId = 1;
        for ($i = 1; $i <= 127; $i++) {
            if (!in_array($i, $usedIds)) {
                $nextId = $i;
                break;
            }
        }

        Cache::put('pending_enroll_id', $nextId, 60);
        
        if ($request->has('id_santri')) {
            Cache::put('enroll_santri_id', $request->id_santri, 60);
        } else {
            Cache::forget('enroll_santri_id');
        }

        Cache::put('enroll_status', [
            'status' => 'pending',
            'fingerprint_id' => $nextId,
            'message' => 'Silakan tempelkan jari Anda ke sensor...'
        ], 60);

        // Publish to MQTT TLS for instant ESP32 enroll trigger
        try {
            $server   = env('MQTT_HOST', 'broker.emqx.io');
            $port     = (int) env('MQTT_PORT', 8883);
            $clientId = 'laravel_pub_' . rand(100, 999);
            $settings = (new \PhpMqtt\Client\ConnectionSettings())
                ->setUseTls(true)
                ->setTlsSelfSignedAllowed(true)
                ->setTlsVerifyPeer(false);

            $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
            $mqtt->connect($settings, true);
            $mqtt->publish('subuh_monitor/fingerprint/enroll', json_encode([
                'status' => 'enroll',
                'fingerprint_id' => $nextId
            ]), 0);
            $mqtt->disconnect();
        } catch (\Exception $e) {
            // Log if MQTT publish fails, HTTP polling serves as fallback
            \Log::warning("[MQTT TLS Publish Error] " . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'fingerprint_id' => $nextId
        ]);
    }

    /**
     * Get pending enrollment requests for the ESP32 sensor.
     */
    public function getPendingEnroll()
    {
        $id = Cache::get('pending_enroll_id');
        if ($id) {
            return response()->json([
                'status' => 'enroll',
                'fingerprint_id' => (int)$id
            ]);
        }
        return response()->json([
            'status' => 'idle'
        ]);
    }

    /**
     * Confirm completion of a fingerprint enrollment from the ESP32.
     */
    public function confirmEnroll(Request $request)
    {
        $request->validate([
            'fingerprint_id' => 'required|integer',
            'status' => 'required|string', // success, failed
            'message' => 'nullable|string'
        ]);

        Cache::forget('pending_enroll_id');

        $status = $request->status;
        $msg = $request->message ?? ($status === 'success' ? 'Sidik jari berhasil direkam!' : 'Proses pemindaian gagal.');

        if ($status === 'success') {
            $santriId = Cache::get('enroll_santri_id');
            if ($santriId) {
                // Automatically assign fingerprint to existing santri
                $santri = Santri::find($santriId);
                if ($santri) {
                    $santri->update(['fingerprint_id' => $request->fingerprint_id]);
                    $msg = "Sidik jari berhasil dihubungkan ke {$santri->nama_santri}!";
                }
                Cache::forget('enroll_santri_id');
            }
        } else {
            Cache::forget('enroll_santri_id');
        }

        Cache::put('enroll_status', [
            'status' => $status,
            'fingerprint_id' => $request->fingerprint_id,
            'message' => $msg
        ], 60);

        return response()->json(['status' => 'success']);
    }

    /**
     * Cancel an active enrollment session.
     */
    public function cancelEnroll()
    {
        Cache::forget('pending_enroll_id');
        Cache::forget('enroll_santri_id');
        Cache::forget('enroll_status');
        return response()->json(['status' => 'success']);
    }

    /**
     * Check current enrollment status from the web browser.
     */
    public function checkEnrollStatus()
    {
        $status = Cache::get('enroll_status');
        if ($status) {
            return response()->json($status);
        }
        return response()->json([
            'status' => 'idle',
            'message' => 'Tidak ada proses pemindaian aktif.'
        ]);
    }

    /**
     * Send automated Telegram notification to parents.
     */
    private function sendTelegramNotification($santri, $status, $time)
    {
        $wali = \App\Models\Wali::where('no_hp', $santri->no_hp_ortu)->first();
        if (!$wali || empty($wali->telegram_chat_id)) {
            \Log::warning("[Telegram] Tidak dapat mengirim notifikasi: Akun Wali atau Telegram Chat ID belum terdaftar untuk nomor {$santri->no_hp_ortu}");
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
                \Log::warning("[Telegram] Gagal mengirim ke Chat ID {$wali->telegram_chat_id}: " . $result['message']);
            } else {
                \Log::info("[Telegram] Sukses mengirim ke Chat ID {$wali->telegram_chat_id}: " . $result['message']);
            }
        } catch (\Exception $e) {
            \Log::error("[Telegram] Terjadi Exception saat mengirim ke Chat ID {$wali->telegram_chat_id}: " . $e->getMessage());
        }
    }
}

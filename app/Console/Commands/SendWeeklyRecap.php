<?php

namespace App\Console\Commands;

use App\Models\Santri;
use App\Models\AbsensiSubuh;
use App\Models\Wali;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendWeeklyRecap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:send-weekly-recap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim rekapitulasi absensi subuh 7 hari terakhir ke Telegram orang tua santri';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $tgService)
    {
        $this->info("Memulai pengiriman rekapitulasi absensi 7 hari terakhir...");

        $activeStudents = Santri::where('status', 'Aktif')->get();
        if ($activeStudents->isEmpty()) {
            $this->warn("Tidak ada santri aktif ditemukan.");
            return 0;
        }

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $successCount = 0;
        $failCount = 0;

        foreach ($activeStudents as $student) {
            if (empty($student->no_hp_ortu)) {
                $this->warn("Santri [{$student->nama_santri}] tidak memiliki nomor HP orang tua. Dilewati.");
                continue;
            }

            $wali = Wali::where('no_hp', $student->no_hp_ortu)->first();
            if (!$wali || empty($wali->telegram_chat_id)) {
                $this->warn("Santri [{$student->nama_santri}] orang tuanya belum memiliki akun Wali atau Telegram Chat ID. Dilewati.");
                continue;
            }

            // Hitung statistik 7 hari terakhir
            $records = AbsensiSubuh::where('id_santri', $student->id_santri)
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get()
                ->keyBy('tanggal');

            $hadir = 0;
            $terlambat = 0;
            $tidakHadir = 0;
            $activeDays = 0;

            for ($i = 0; $i < 7; $i++) {
                $dateObj = Carbon::now()->subDays(6 - $i);
                $dateStr = $dateObj->format('Y-m-d');

                // Lewati jika sebelum tanggal pendaftaran santri
                $regDate = $student->created_at ? Carbon::parse($student->created_at)->startOfDay() : null;
                if ($regDate && $dateObj->startOfDay()->lt($regDate)) {
                    continue;
                }

                $activeDays++;

                if ($records->has($dateStr)) {
                    $status = $records->get($dateStr)->status_kehadiran;
                    if ($status === 'Hadir') {
                        $hadir++;
                    } elseif ($status === 'Terlambat') {
                        $terlambat++;
                    } else {
                        $tidakHadir++;
                    }
                } else {
                    $tidakHadir++;
                }
            }

            // Jika santri baru didaftarkan hari ini setelah subuh, activeDays bisa 0
            if ($activeDays === 0) {
                $activeDays = 1;
            }

            // Format tanggal Indonesia
            $startFormatted = $startDate->translatedFormat('d M Y');
            $endFormatted = $endDate->translatedFormat('d M Y');

            // Format Pesan Telegram
            $message = "<b>Laporan Rekapitulasi Absensi Salat Subuh</b>\n\n"
                     . "Berikut adalah laporan untuk Ananda <b>{$student->nama_santri}</b> untuk 7 hari terakhir ({$startFormatted} s/d {$endFormatted}):\n\n"
                     . "• Hadir: <b>{$hadir}</b> kali\n"
                     . "• Terlambat: <b>{$terlambat}</b> kali\n"
                     . "• Tidak Hadir: <b>{$tidakHadir}</b> kali\n\n"
                     . "<i>Mari bersama-sama kita motivasi ananda agar senantiasa istiqomah memakmurkan masjid di waktu Subuh.</i>\n\n"
                     . "<b>Subuh Monitor</b>";

            $this->info("Mengirim rekap ke orang tua {$student->nama_santri} via Telegram...");
            
            $res = $tgService->sendMessage($wali->telegram_chat_id, $message);

            if ($res['success']) {
                $successCount++;
                $this->info("Berhasil mengirim untuk {$student->nama_santri}");
            } else {
                $failCount++;
                $this->error("Gagal mengirim untuk {$student->nama_santri}: " . $res['message']);
            }
        }

        $this->info("Selesai. Berhasil: {$successCount}, Gagal: {$failCount}.");
        return 0;
    }
}

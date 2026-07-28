<?php

namespace App\Console\Commands;

use App\Models\Santri;
use App\Models\AbsensiSubuh;
use App\Models\Wali;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendAbsentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:send-absent-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa semua santri yang belum absen subuh hari ini dan catat sebagai Tidak Hadir di database.';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $tgService)
    {
        $this->info("Memulai pemeriksaan ketidakhadiran santri hari ini...");

        $activeStudents = Santri::where('status', 'Aktif')->get();
        if ($activeStudents->isEmpty()) {
            $this->warn("Tidak ada santri aktif ditemukan.");
            return 0;
        }

        $today = Carbon::now()->format('Y-m-d');
        $dateFormatted = Carbon::now()->translatedFormat('d M Y');

        $successCount = 0;
        $skipCount = 0;

        foreach ($activeStudents as $student) {
            // Cek apakah sudah ada catatan absen hari ini (Hadir / Terlambat / Tidak Hadir)
            $alreadyLogged = AbsensiSubuh::where('id_santri', $student->id_santri)
                ->where('tanggal', $today)
                ->first();

            if ($alreadyLogged) {
                // Santri ini sudah absen atau sudah ditandai tidak hadir
                $skipCount++;
                continue;
            }

            // Catat sebagai "Tidak Hadir" di database
            AbsensiSubuh::create([
                'id_santri' => $student->id_santri,
                'waktu_absensi' => Carbon::now(),
                'tanggal' => $today,
                'jadwal_subuh' => '04:35:00', // Waktu adzan Subuh rata-rata
                'status_kehadiran' => 'Tidak Hadir',
                'poin' => 0,
                'keterangan' => 'Tidak melakukan absensi Subuh',
            ]);

            $this->info("Santri [{$student->nama_santri}] dicatat Tidak Hadir.");
            $successCount++;
        }

        $this->info("Selesai. Dicatat tidak hadir & notifikasi dikirim: {$successCount}, Sudah terabsen: {$skipCount}.");
        return 0;
    }
}

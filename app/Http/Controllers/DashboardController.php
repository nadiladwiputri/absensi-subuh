<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\AbsensiSubuh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = date('Y-m-d');

        // Statistik summary
        $totalSantri = Santri::where('status', 'Aktif')->count();
        $hadirHariIni = AbsensiSubuh::where('tanggal', $today)
            ->whereIn('status_kehadiran', ['Hadir', 'Terlambat'])
            ->count();
        
        $persentaseHadir = $totalSantri > 0 ? round(($hadirHariIni / $totalSantri) * 100) : 0;

        $terlambatHariIni = AbsensiSubuh::where('tanggal', $today)
            ->where('status_kehadiran', 'Terlambat')
            ->count();
            
        $tanpaKeteranganHariIni = max(0, $totalSantri - $hadirHariIni);

        // Perankingan Kedisiplinan Santri (Top 10 berdasarkan poin 30 hari terakhir)
        // Logika: Hadir Tepat Waktu = 100, Terlambat = 50, Tidak Hadir (Alpa) = -15
        $now = Carbon::now();
        $thirtyDaysAgo = Carbon::now()->subDays(29)->startOfDay(); // 30 hari termasuk hari ini

        // Dapatkan tanggal absen pertama kali untuk pembatasan hari aktif sistem
        $firstScan = AbsensiSubuh::min('tanggal');
        $firstScanDate = $firstScan ? Carbon::parse($firstScan)->startOfDay() : null;

        $ranking = Santri::where('status', 'Aktif')->get()->map(function ($s) use ($now, $thirtyDaysAgo, $firstScanDate) {
            // Tentukan tanggal pendaftaran santri
            $regDate = $s->created_at ? Carbon::parse($s->created_at)->startOfDay() : $thirtyDaysAgo->copy();
            
            // Start date adalah yang mana yang lebih baru antara 30 hari lalu atau tanggal daftar
            $start = $regDate->greaterThan($thirtyDaysAgo) ? $regDate : $thirtyDaysAgo;
            
            // Batasi agar tidak menghitung hari sebelum sistem dipasang / mulai digunakan
            if ($firstScanDate && $start->lessThan($firstScanDate)) {
                $start = $firstScanDate->copy();
            }

            $end = $now->copy()->startOfDay();
            
            // Jumlah hari aktif untuk santri ini dalam 30 hari terakhir
            $studentActiveDays = $start->greaterThan($end) ? 0 : $start->diffInDays($end) + 1;

            // Hitung absensi dalam rentang 30 hari terakhir
            $absensi = AbsensiSubuh::where('id_santri', $s->id_santri)
                ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->get();

            $hadir = $absensi->where('status_kehadiran', 'Hadir')->count();
            $terlambat = $absensi->where('status_kehadiran', 'Terlambat')->count();
            
            $totalHadirCount = $hadir + $terlambat;
            $alpa = $studentActiveDays > 0 ? max(0, $studentActiveDays - $totalHadirCount) : 0;

            // Hitung total poin: Hadir = 100, Terlambat = 50, Alpa = 0 (tanpa pengurangan)
            $totalPoin = ($hadir * 100) + ($terlambat * 50);

            $s->total_poin = $totalPoin;
            
            // Dapatkan waktu absensi terakhir
            $lastAbsen = AbsensiSubuh::where('id_santri', $s->id_santri)
                ->orderBy('waktu_absensi', 'desc')
                ->first();
            $s->terakhir_absen = $lastAbsen ? $lastAbsen->waktu_absensi : null;

            return $s;
        });

        // Urutkan berdasarkan total_poin desc, lalu nama_santri asc
        $ranking = $ranking->sort(function ($a, $b) {
            if ($a->total_poin === $b->total_poin) {
                return strcasecmp($a->nama_santri, $b->nama_santri);
            }
            return $b->total_poin <=> $a->total_poin;
        })->take(10)->values();

        // Absensi terbaru hari ini untuk live feed
        $absensiTerbaru = AbsensiSubuh::with('santri')
            ->where('tanggal', $today)
            ->orderBy('waktu_absensi', 'desc')
            ->get();

        return view('dashboard.index', compact(
            'totalSantri',
            'hadirHariIni',
            'persentaseHadir',
            'terlambatHariIni',
            'tanpaKeteranganHariIni',
            'ranking',
            'absensiTerbaru'
        ));
    }
}

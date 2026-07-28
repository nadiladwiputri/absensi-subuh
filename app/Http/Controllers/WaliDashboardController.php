<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\AbsensiSubuh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WaliDashboardController extends Controller
{
    public function index()
    {
        $wali = Auth::guard('wali')->user();
        
        // Ambil data santri (anak) yang nomor HP orang tuanya cocok dengan no_hp wali ini
        $anakList = Santri::where('no_hp_ortu', $wali->no_hp)->get();

        $now = Carbon::now();
        $thirtyDaysAgo = Carbon::now()->subDays(29)->startOfDay();

        // Dapatkan tanggal absen pertama kali di database untuk acuan hari aktif
        $firstScan = AbsensiSubuh::min('tanggal');
        $firstScanDate = $firstScan ? Carbon::parse($firstScan)->startOfDay() : null;

        $anakList->map(function ($s) use ($now, $thirtyDaysAgo, $firstScanDate) {
            // Hitung hari aktif untuk santri ini
            $regDate = $s->created_at ? Carbon::parse($s->created_at)->startOfDay() : $thirtyDaysAgo->copy();
            $start = $regDate->greaterThan($thirtyDaysAgo) ? $regDate : $thirtyDaysAgo;
            
            // Batasi agar tidak menghitung hari sebelum alat dipasang
            if ($firstScanDate && $start->lessThan($firstScanDate)) {
                $start = $firstScanDate->copy();
            }

            $end = $now->copy()->startOfDay();

            $studentActiveDays = $start->greaterThan($end) ? 0 : $start->diffInDays($end) + 1;

            // Hitung statistik kehadiran dalam 30 hari terakhir
            $absensi = AbsensiSubuh::where('id_santri', $s->id_santri)
                ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->get();

            $hadir = $absensi->where('status_kehadiran', 'Hadir')->count();
            $terlambat = $absensi->where('status_kehadiran', 'Terlambat')->count();
            
            $totalHadirCount = $hadir + $terlambat;
            $alpa = $studentActiveDays > 0 ? max(0, $studentActiveDays - $totalHadirCount) : 0;

            // Hitung poin kedisiplinan (+100 on time, +50 late)
            $totalPoin = ($hadir * 100) + ($terlambat * 50);
            
            // Hitung persentase kehadiran (bobot: hadir = 1.0, terlambat = 0.5)
            $nilaiKehadiran = ($hadir * 1.0) + ($terlambat * 0.5);
            $persentase = $studentActiveDays > 0 ? round(($nilaiKehadiran / $studentActiveDays) * 100) : 0;

            $s->total_poin = $totalPoin;
            $s->total_hadir = $hadir;
            $s->total_terlambat = $terlambat;
            $s->total_alpa = $alpa;
            $s->persentase_kehadiran = $persentase;
            $s->hari_aktif = $studentActiveDays;

            // Absen terakhir
            $lastAbsen = AbsensiSubuh::where('id_santri', $s->id_santri)
                ->orderBy('waktu_absensi', 'desc')
                ->first();
            $s->terakhir_absen = $lastAbsen;

            return $s;
        });

        return view('wali.dashboard', compact('wali', 'anakList'));
    }

    public function showSantri(Santri $santri)
    {
        $wali = Auth::guard('wali')->user();

        // Validasi kepemilikan anak demi keamanan
        if ($santri->no_hp_ortu !== $wali->no_hp) {
            abort(403, 'Akses ditolak. Santri ini bukan anak Anda.');
        }

        $now = Carbon::now();
        $thirtyDaysAgo = Carbon::now()->subDays(29)->startOfDay();

        // Dapatkan tanggal absen pertama kali di database untuk acuan hari aktif
        $firstScan = AbsensiSubuh::min('tanggal');
        $firstScanDate = $firstScan ? Carbon::parse($firstScan)->startOfDay() : null;

        // Hitung hari aktif untuk santri ini
        $regDate = $santri->created_at ? Carbon::parse($santri->created_at)->startOfDay() : $thirtyDaysAgo->copy();
        $start = $regDate->greaterThan($thirtyDaysAgo) ? $regDate : $thirtyDaysAgo;
        
        // Batasi agar tidak menghitung hari sebelum alat dipasang
        if ($firstScanDate && $start->lessThan($firstScanDate)) {
            $start = $firstScanDate->copy();
        }

        $end = $now->copy()->startOfDay();

        $studentActiveDays = $start->greaterThan($end) ? 0 : $start->diffInDays($end) + 1;

        // Ambil riwayat absensi 30 hari terakhir
        $absensiList = AbsensiSubuh::where('id_santri', $santri->id_santri)
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('tanggal', 'desc')
            ->get();

        $hadir = $absensiList->where('status_kehadiran', 'Hadir')->count();
        $terlambat = $absensiList->where('status_kehadiran', 'Terlambat')->count();
        $totalHadirCount = $hadir + $terlambat;
        $alpa = $studentActiveDays > 0 ? max(0, $studentActiveDays - $totalHadirCount) : 0;

        // Poin kedisiplinan dan persentase
        $totalPoin = ($hadir * 100) + ($terlambat * 50);
        $nilaiKehadiran = ($hadir * 1.0) + ($terlambat * 0.5);
        $persentase = $studentActiveDays > 0 ? round(($nilaiKehadiran / $studentActiveDays) * 100) : 0;

        $stats = (object) [
            'total_poin' => $totalPoin,
            'total_hadir' => $hadir,
            'total_terlambat' => $terlambat,
            'total_alpa' => $alpa,
            'persentase_kehadiran' => $persentase,
            'hari_aktif' => $studentActiveDays
        ];

        // Buat daftar calendar harian untuk tampilan detail (termasuk hari-hari alpa)
        // Kita akan melakukan looping dari end date sampai start date untuk membuat entri kalender
        $calendar = [];
        $currentDate = $end->copy();

        while ($currentDate->greaterThanOrEqualTo($start)) {
            $dateString = $currentDate->format('Y-m-d');
            
            // Cari apakah ada data absen di tanggal ini
            $absenHariIni = $absensiList->firstWhere('tanggal', $dateString);

            if ($absenHariIni) {
                $calendar[] = (object) [
                    'tanggal' => $currentDate->copy(),
                    'status' => $absenHariIni->status_kehadiran,
                    'waktu' => Carbon::parse($absenHariIni->waktu_absensi)->format('H:i:s'),
                    'poin' => $absenHariIni->poin,
                    'keterangan' => $absenHariIni->keterangan
                ];
            } else {
                // Catat sebagai tidak hadir
                $calendar[] = (object) [
                    'tanggal' => $currentDate->copy(),
                    'status' => 'Tidak Hadir',
                    'waktu' => '-',
                    'poin' => 0,
                    'keterangan' => 'Tanpa keterangan absensi Subuh'
                ];
            }

            $currentDate->subDay();
        }

        return view('wali.santri.show', compact('wali', 'santri', 'stats', 'calendar'));
    }
}

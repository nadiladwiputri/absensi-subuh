<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\AbsensiSubuh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RekapitulasiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        // Dapatkan nama bulan
        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        // Ambil tanggal absen pertama kali di database untuk acuan hari aktif sistem
        $firstScan = AbsensiSubuh::min('tanggal');
        $firstScanDate = $firstScan ? Carbon::parse($firstScan)->startOfDay() : null;

        // Tentukan startDay untuk bulan dan tahun terpilih
        $startDay = 1;
        if ($firstScanDate) {
            $fsYear = (int)$firstScanDate->format('Y');
            $fsMonth = (int)$firstScanDate->format('m');
            $fsDay = (int)$firstScanDate->format('d');

            if ($tahun < $fsYear || ($tahun == $fsYear && (int)$bulan < $fsMonth)) {
                // Bulan sebelum alat mulai digunakan
                $startDay = 0;
            } elseif ($tahun == $fsYear && (int)$bulan == $fsMonth) {
                // Bulan saat alat mulai digunakan
                $startDay = $fsDay;
            }
        }

        // Tentukan endDay untuk bulan terpilih
        $hariDalamBulan = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        if ($bulan == date('m') && $tahun == date('Y')) {
            $endDay = (int)date('d');
        } else {
            $endDay = $hariDalamBulan;
        }

        // Query Santri dengan agregasi absensi
        $query = Santri::where('status', 'Aktif');

        $rekap = $query->get()->map(function ($s) use ($bulan, $tahun, $startDay, $endDay) {
            // Tentukan tanggal pendaftaran santri
            $regDate = $s->created_at ? Carbon::parse($s->created_at)->startOfDay() : null;
            
            // Hitung start day spesifik untuk santri ini
            $studentStartDay = $startDay;
            if ($regDate) {
                $regYear = (int)$regDate->format('Y');
                $regMonth = (int)$regDate->format('m');
                $regDay = (int)$regDate->format('d');
                
                if ($regYear == $tahun && $regMonth == $bulan) {
                    $studentStartDay = max($studentStartDay, $regDay);
                }
            }

            // Hitung hari aktif untuk santri ini
            if ($studentStartDay == 0 || $studentStartDay > $endDay) {
                $studentActiveDays = 0;
            } else {
                $studentActiveDays = $endDay - $studentStartDay + 1;
            }

            // Count Hadir
            $hadir = AbsensiSubuh::where('id_santri', $s->id_santri)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status_kehadiran', 'Hadir')
                ->count();

            // Count Terlambat
            $terlambat = AbsensiSubuh::where('id_santri', $s->id_santri)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status_kehadiran', 'Terlambat')
                ->count();

            $totalHadir = $hadir + $terlambat;
            $alpa = $studentActiveDays > 0 ? max(0, $studentActiveDays - $totalHadir) : 0;
            $persentase = $studentActiveDays > 0 ? round(($totalHadir / $studentActiveDays) * 100, 1) : 0;

            return (object) [
                'id_santri' => $s->id_santri,
                'nama_santri' => $s->nama_santri,
                'total_hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin_sakit' => 0, // Default 0
                'alpa' => $alpa,
                'persentase' => $persentase
            ];
        });

        

        // Cari rata-rata persentase, total terlambat dsb untuk card di rekapitulasi
        $totalKehadiranPersen = $rekap->count() > 0 ? round($rekap->avg('persentase'), 1) : 0;
        $totalTerlambatMenit = AbsensiSubuh::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status_kehadiran', 'Terlambat')
            ->count(); // total kali terlambat
            
        // Santri Terdisiplin (Persentase kehadiran tertinggi, terlambat tersedikit, alpa tersedikit)
        $terdisiplin = $rekap->sort(function ($a, $b) {
            if ($a->persentase != $b->persentase) {
                return $b->persentase <=> $a->persentase;
            }
            if ($a->terlambat != $b->terlambat) {
                return $a->terlambat <=> $b->terlambat;
            }
            return $a->alpa <=> $b->alpa;
        })->first();

        $hasData = $terdisiplin && ($terdisiplin->total_hadir + $terdisiplin->terlambat) > 0;
        $santriTerdisiplin = $hasData ? $terdisiplin->nama_santri : 'Belum ada data';
        $santriTerdisiplinSub = $hasData 
            ? "Tepat Waktu: " . $terdisiplin->total_hadir . "x | Terlambat: " . $terdisiplin->terlambat . "x | Tidak Hadir: " . $terdisiplin->alpa . "x"
            : "Belum ada data";

        // Santri Perlu Perhatian (Sering alpa/tidak hadir, sering terlambat, hadir tersedikit)
        $perluPerhatian = $rekap->sort(function ($a, $b) {
            if ($a->alpa != $b->alpa) {
                return $b->alpa <=> $a->alpa;
            }
            if ($a->terlambat != $b->terlambat) {
                return $b->terlambat <=> $a->terlambat;
            }
            return $a->total_hadir <=> $b->total_hadir;
        })->first();

        $hasDataPerhatian = $perluPerhatian && ($perluPerhatian->alpa > 0 || $perluPerhatian->terlambat > 0 || $perluPerhatian->total_hadir > 0);
        $absenTerbanyakName = $hasDataPerhatian ? $perluPerhatian->nama_santri : 'Belum ada data';
        
        $perhatianPersen = $perluPerhatian ? $perluPerhatian->persentase : 0;
        
        $absenTerbanyakSub = $hasDataPerhatian
            ? "Tepat Waktu: " . $perluPerhatian->total_hadir . "x | Terlambat: " . $perluPerhatian->terlambat . "x | Tidak Hadir: " . $perluPerhatian->alpa . "x"
            : "Belum ada data";

        return view('rekapitulasi.index', compact(
            'rekap',
            'namaBulan',
            'bulan',
            'tahun',
            'totalKehadiranPersen',
            'totalTerlambatMenit',
            'santriTerdisiplin',
            'santriTerdisiplinSub',
            'absenTerbanyakName',
            'perhatianPersen',
            'absenTerbanyakSub'
        ));
    }
}

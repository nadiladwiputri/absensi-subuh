@extends('layouts.wali')

@section('title', 'Detail Perkembangan Santri')

@section('styles')
    <style>
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-medium);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }

        .btn-back:hover {
            color: var(--primary-dark);
        }

        .profile-header-card {
            background-color: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .profile-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-avatar {
            width: 70px;
            height: 70px;
            background-color: var(--primary-dark);
            color: #ffffff;
            font-size: 1.8rem;
            font-weight: 700;
            border-radius: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .profile-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* Stats Dashboard Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01);
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .stat-card-title {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-card-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        /* History Table Styling */
        .history-card {
            background-color: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            padding: 24px 30px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .history-table th {
            background-color: rgba(240, 253, 244, 0.5);
            padding: 16px 30px;
            font-weight: 600;
            color: var(--primary-dark);
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
        }

        .history-table td {
            padding: 16px 30px;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.95rem;
        }

        .history-table tr:last-child td {
            border-bottom: none;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 99px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge-status-ontime {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .badge-status-late {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .badge-status-absent {
            background-color: #FEE2E2;
            color: #9B1C1C;
        }

        .poin-text {
            font-weight: 700;
        }

        .poin-plus {
            color: #10B981;
        }

        .poin-minus {
            color: #EF4444;
        }

        @media (max-width: 768px) {
            .profile-header-card {
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
            }
            .history-table th, .history-table td {
                padding: 12px 20px;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Link Kembali -->
    <a href="{{ route('wali.dashboard') }}" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Halaman Utama
    </a>

    <!-- Profil Header Card -->
    <div class="profile-header-card">
        <div class="profile-left">
            <div class="profile-avatar">
                {{ strtoupper(substr($santri->nama_santri, 0, 2)) }}
            </div>
            <div>
                <h1 class="profile-title">{{ $santri->nama_santri }}</h1>
                <span class="profile-subtitle">
                    <i class="fa-solid fa-fingerprint"></i> ID Sensor: #{{ $santri->fingerprint_id ?? '-' }}
                </span>
            </div>
        </div>
        <div>
            <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500; display: block; text-align: right;">
                Terdaftar sejak: {{ \Carbon\Carbon::parse($santri->created_at)->format('d M Y') }}
            </span>
        </div>
    </div>

    <!-- Grid Statistik Detail -->
    <div class="stats-grid">
        <!-- Poin Disiplin -->
        <div class="stat-card">
            <span class="stat-card-title"><i class="fa-solid fa-star"></i> Poin Disiplin</span>
            <span class="stat-card-value" style="color: {{ $stats->total_poin >= 0 ? 'var(--primary-medium)' : '#EF4444' }};">
                {{ $stats->total_poin }}
            </span>
        </div>



        <!-- Tepat Waktu -->
        <div class="stat-card">
            <span class="stat-card-title" style="color: #065F46;"><i class="fa-solid fa-circle-check"></i> Tepat Waktu</span>
            <span class="stat-card-value" style="color: #065F46;">{{ $stats->total_hadir }}x</span>
        </div>

        <!-- Terlambat -->
        <div class="stat-card">
            <span class="stat-card-title" style="color: #92400E;"><i class="fa-solid fa-circle-exclamation"></i> Terlambat</span>
            <span class="stat-card-value" style="color: #92400E;">{{ $stats->total_terlambat }}x</span>
        </div>

        <!-- Alpa -->
        <div class="stat-card">
            <span class="stat-card-title" style="color: #9B1C1C;"><i class="fa-solid fa-circle-xmark"></i> Alpa (Tidak Hadir)</span>
            <span class="stat-card-value" style="color: #9B1C1C;">{{ $stats->total_alpa }}x</span>
        </div>
    </div>

    <!-- Tabel Riwayat Kehadiran -->
    <div class="history-card">
        <div class="card-header">
            <h2 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Absensi Subuh Harian</h2>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Urutan berdasarkan tanggal terbaru</span>
        </div>
        <div class="table-responsive">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Hari / Tanggal</th>
                        <th>Waktu Absen</th>
                        <th>Status Kehadiran</th>
                        <th>Poin</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($calendar as $c)
                        <tr>
                            <!-- Hari & Tanggal -->
                            <td style="font-weight: 500;">
                                {{ $c->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}
                            </td>
                            <!-- Waktu Absen -->
                            <td>
                                <i class="fa-regular fa-clock" style="color: var(--text-muted); margin-right: 4px;"></i> 
                                {{ $c->waktu }}
                            </td>
                            <!-- Status Kehadiran -->
                            <td>
                                @if($c->status === 'Hadir')
                                    <span class="badge-status badge-status-ontime">
                                        <i class="fa-solid fa-circle-check"></i> Tepat Waktu
                                    </span>
                                @elseif($c->status === 'Terlambat')
                                    <span class="badge-status badge-status-late">
                                        <i class="fa-solid fa-circle-exclamation"></i> Terlambat
                                    </span>
                                @else
                                    <span class="badge-status badge-status-absent">
                                        <i class="fa-solid fa-circle-xmark"></i> Tidak Hadir
                                    </span>
                                @endif
                            </td>
                            <!-- Poin -->
                            <td>
                                @if($c->poin > 0)
                                    <span class="poin-text poin-plus">+{{ $c->poin }}</span>
                                @else
                                    <span class="poin-text poin-minus">{{ $c->poin }}</span>
                                @endif
                            </td>
                            <!-- Keterangan -->
                            <td style="color: var(--text-muted); font-size: 0.85rem;">
                                {{ $c->keterangan }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-regular fa-calendar-xmark" style="font-size: 2.5rem; margin-bottom: 10px; display: block; color: var(--text-muted);"></i>
                                Belum ada data absensi untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

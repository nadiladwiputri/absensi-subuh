@extends('layouts.wali')

@section('title', 'Dashboard Wali Santri')

@section('styles')
    <style>
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
            color: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(11, 74, 58, 0.15);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            right: 20px;
            bottom: -30px;
            width: 150px;
            height: 150px;
            background: url('https://illustrations.popsy.co/white/achievement.svg') no-repeat center;
            background-size: contain;
            opacity: 0.1;
        }

        .welcome-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .welcome-desc {
            font-size: 0.95rem;
            opacity: 0.85;
            max-width: 600px;
            line-height: 1.5;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
        }

        /* Anak Grid */
        .anak-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .anak-card {
            background-color: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
        }

        .anak-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }

        .anak-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 16px;
            background-color: rgba(240, 253, 244, 0.5);
        }

        .anak-avatar {
            width: 50px;
            height: 50px;
            background-color: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid rgba(52, 211, 153, 0.2);
        }

        .anak-info {
            flex-grow: 1;
        }

        .anak-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 4px;
        }

        .anak-status {
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        .status-active {
            color: #10B981;
            background-color: #D1FAE5;
            padding: 2px 8px;
            border-radius: 99px;
        }

        .status-absent {
            color: #EF4444;
            background-color: #FEE2E2;
            padding: 2px 8px;
            border-radius: 99px;
        }

        .point-badge {
            font-size: 1.2rem;
            font-weight: 700;
            padding: 8px 14px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .point-positive {
            background-color: var(--primary-soft);
            color: var(--primary-medium);
            border: 1px solid rgba(52, 211, 153, 0.2);
        }

        .point-negative {
            background-color: #FEE2E2;
            color: #EF4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* Anak Body / Stats */
        .anak-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex-grow: 1;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-value {
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            height: 8px;
            background-color: #E2E8F0;
            border-radius: 99px;
            overflow: hidden;
            margin-top: 6px;
        }

        .progress-bar {
            height: 100%;
            background-color: var(--primary-light);
            border-radius: 99px;
        }

        .stats-badge-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 5px;
        }

        .stat-badge {
            padding: 8px;
            border-radius: 10px;
            text-align: center;
            font-size: 0.8rem;
            display: flex;
            flex-direction: column;
            gap: 4px;
            border: 1px solid var(--border-color);
        }

        .badge-ontime {
            background-color: #ECFDF5;
            color: #065F46;
            border-color: #A7F3D0;
        }

        .badge-late {
            background-color: #FFFBEB;
            color: #92400E;
            border-color: #FDE68A;
        }

        .badge-absent {
            background-color: #FDF2F2;
            color: #9B1C1C;
            border-color: #FECDCA;
        }

        .anak-footer {
            padding: 20px 24px;
            border-top: 1px solid var(--border-color);
            background-color: #FAF5F5;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .last-activity {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-view-detail {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            background-color: var(--primary-medium);
            color: #ffffff;
            text-decoration: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background-color 0.2s ease;
        }

        .btn-view-detail:hover {
            background-color: var(--primary-dark);
        }

        /* Empty state */
        .empty-card {
            background-color: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 40px;
            text-align: center;
            max-width: 600px;
            margin: 40px auto;
        }

        .empty-icon {
            font-size: 3rem;
            color: #F59E0B;
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .empty-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }
    </style>
@endsection

@section('content')
    <!-- Kartu Sambutan -->
    <div class="welcome-card">
        <h1 class="welcome-title">Assalamu'alaikum, Bpk/Ibu {{ $wali->nama_wali }}!</h1>
        <p class="welcome-desc">
            Selamat datang di Portal Orang Tua **Subuh Monitor**. Di sini Anda dapat memantau kedisiplinan shalat Subuh berjamaah dan perolehan poin disiplin anak-anak Anda secara berkala.
        </p>
    </div>

    <!-- Header Section -->
    <div class="section-header">
        <h2 class="section-title"><i class="fa-solid fa-graduation-cap"></i> Profil Anak Anda</h2>
        <span style="font-size: 0.85rem; color: var(--text-muted);">Menampilkan riwayat 30 hari terakhir</span>
    </div>

    @if($anakList->isEmpty())
        <!-- Keadaan Kosong (Tidak ada anak terkait nomor HP) -->
        <div class="empty-card">
            <i class="fa-solid fa-triangle-exclamation empty-icon"></i>
            <h3 class="empty-title">Data Santri Tidak Ditemukan</h3>
            <p class="empty-desc">
                Tidak ada data santri yang terhubung dengan nomor HP Anda (**{{ $wali->no_hp }}**). 
                Silakan hubungi administrator untuk mencocokkan nomor HP orang tua di data santri agar tersinkronisasi otomatis.
            </p>
        </div>
    @else
        <!-- Grid Kartu Anak -->
        <div class="anak-grid">
            @foreach($anakList as $a)
                <div class="anak-card">
                    <!-- Bagian Header Anak -->
                    <div class="anak-header">
                        <div class="anak-avatar">
                            {{ strtoupper(substr($a->nama_santri, 0, 2)) }}
                        </div>
                        <div class="anak-info">
                            <h3 class="anak-name">{{ $a->nama_santri }}</h3>
                            @if($a->terakhir_absen && \Carbon\Carbon::parse($a->terakhir_absen->tanggal)->isToday())
                                <span class="anak-status status-active">
                                    <i class="fa-solid fa-circle-check"></i> Sudah Absen Hari Ini
                                </span>
                            @else
                                <span class="anak-status status-absent">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Belum Absen Hari Ini
                                </span>
                            @endif
                        </div>
                        <!-- Poin Badge -->
                        <div class="point-badge {{ $a->total_poin >= 0 ? 'point-positive' : 'point-negative' }}">
                            {{ $a->total_poin }} Poin
                        </div>
                    </div>

                    <!-- Bagian Body Anak / Stats -->
                    <div class="anak-body">


                        <!-- Grid Statistik Kehadiran -->
                        <div>
                            <div class="stat-label" style="margin-bottom: 8px;">
                                <i class="fa-solid fa-list-check"></i> Rincian Kehadiran ({{ $a->hari_aktif }} Hari Aktif)
                            </div>
                            <div class="stats-badge-grid">
                                <div class="stat-badge badge-ontime">
                                    <strong>{{ $a->total_hadir }}x</strong>
                                    <span>Tepat Waktu</span>
                                </div>
                                <div class="stat-badge badge-late">
                                    <strong>{{ $a->total_terlambat }}x</strong>
                                    <span>Terlambat</span>
                                </div>
                                <div class="stat-badge badge-absent">
                                    <strong>{{ $a->total_alpa }}x</strong>
                                    <span>Alpa</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Footer Anak -->
                    <div class="anak-footer">
                        <div class="last-activity">
                            <i class="fa-regular fa-clock"></i> 
                            @if($a->terakhir_absen)
                                Absensi terakhir: {{ \Carbon\Carbon::parse($a->terakhir_absen->tanggal)->format('d M Y') }} ({{ \Carbon\Carbon::parse($a->terakhir_absen->waktu_absensi)->format('H:i') }})
                            @else
                                Belum ada riwayat absensi subuh
                            @endif
                        </div>
                        <a href="{{ route('wali.santri.show', $a->id_santri) }}" class="btn-view-detail">
                            <i class="fa-regular fa-calendar-days"></i> Lihat Kalender Riwayat Detail
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

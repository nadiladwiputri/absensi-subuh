@extends('layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Monitoring')
@section('header_subtitle', 'Pemantauan Kehadiran Salat Subuh Santri secara Real-Time')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    .stat-card {
        background-color: #ffffff;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01);
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .stat-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--primary-dark);
    }

    .stat-sub {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background-color: var(--primary-soft);
        color: var(--primary-medium);
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.6rem;
    }

    .progress-bar-container {
        width: 100%;
        height: 8px;
        background-color: #E2E8F0;
        border-radius: 10px;
        margin-top: 12px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background-color: var(--primary-medium);
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    /* Layout structure: Left Rank, Right Live Feed */
    .dashboard-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .section-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-success {
        background-color: #D1FAE5;
        color: #065F46;
    }

    .badge-warning {
        background-color: #FEF3C7;
        color: #92400E;
    }

    .badge-info {
        background-color: #DBEAFE;
        color: #1E40AF;
    }

    .badge-danger {
        background-color: #FEE2E2;
        color: #991B1B;
    }

    /* Tables */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        text-align: left;
        padding: 14px 16px;
        font-size: 0.85rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 600;
        border-bottom: 1.5px solid var(--border-color);
    }

    .data-table td {
        padding: 16px;
        font-size: 0.95rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    .rank-cell {
        font-weight: 700;
        width: 40px;
        text-align: center;
    }

    .rank-1 { color: #D97706; }
    .rank-2 { color: #4B5563; }
    .rank-3 { color: #B45309; }

    .santri-name-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .santri-initial {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: var(--primary-soft);
        color: var(--primary-dark);
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .point-badge {
        background-color: #ECFDF5;
        color: var(--primary-medium);
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #D1FAE5;
        font-size: 0.85rem;
    }

    /* Live Feed Feed list */
    .feed-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-height: 480px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .feed-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px;
        border-radius: 14px;
        background-color: var(--bg-light);
        border: 1px solid rgba(16, 185, 129, 0.1);
        transition: all 0.3s ease;
        animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .feed-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(11, 74, 58, 0.05);
    }

    .feed-status-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.1rem;
    }

    .feed-status-hadir {
        background-color: #D1FAE5;
        color: #059669;
    }

    .feed-status-terlambat {
        background-color: #FEF3C7;
        color: #D97706;
    }

    .feed-body {
        flex-grow: 1;
    }

    .feed-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .feed-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #CBD5E1;
    }

    @media (max-width: 1100px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .dashboard-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@section('content')

    <!-- Stats summary grid -->
    <div class="stats-grid">
        <!-- Card 1 -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Jumlah Santri Aktif</span>
                <span class="stat-value">{{ number_format($totalSantri) }}</span>
                <span class="stat-sub">Terdaftar dalam sistem</span>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="stat-card" style="justify-content: flex-start; gap: 24px;">
            <div class="stat-icon" style="background-color: #E0F2FE; color: #0284C7;">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Kehadiran Hari Ini</span>
                <span class="stat-value">{{ $hadirHariIni }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">Santri</span></span>
                <span class="stat-sub">Dari total {{ $totalSantri }} santri aktif</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="stat-card" style="justify-content: flex-start; gap: 24px;">
            <div class="stat-icon" style="background-color: #FEF3C7; color: #D97706;">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Laporan Singkat Hari Ini</span>
                <div style="display: flex; gap: 20px; margin-top: 5px;">
                    <div>
                        <span style="font-weight: 700; color: #D97706; font-size: 1.3rem;">{{ $terlambatHariIni }}</span>
                        <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Terlambat</span>
                    </div>
                    <div style="border-left: 1.5px solid var(--border-color); height: 35px;"></div>
                    <div>
                        <span style="font-weight: 700; color: #EF4444; font-size: 1.3rem;">{{ $tanpaKeteranganHariIni }}</span>
                        <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Tidak Hadir</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main split layout -->
    <div class="dashboard-layout">
        <!-- Left: Perankingan Kehadiran -->
        <div class="card">
            <div class="section-title">
                <span>Perankingan Kehadiran Santri</span>
                <a href="{{ route('rekapitulasi') }}" style="font-size: 0.85rem; color: var(--primary-medium); text-decoration: none; font-weight: 600;">Lihat Semua <i class="fa-solid fa-angle-right"></i></a>
            </div>

            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">Rank</th>
                            <th>Nama Santri</th>
                            <th>Poin Kehadiran</th>
                            <th>Status Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ranking as $index => $r)
                            <tr>
                                <td class="rank-cell rank-{{ $index + 1 }}">{{ $index + 1 }}</td>
                                <td>
                                    <div class="santri-name-wrapper">
                                        <div class="santri-initial">
                                            {{ strtoupper(substr($r->nama_santri, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;">{{ $r->nama_santri }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="point-badge">{{ $r->total_poin }} Poin</span>
                                </td>
                                <td>
                                    @if($r->terakhir_absen)
                                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Aktif</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Belum Absen</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fa-solid fa-box-open"></i>
                                    <p>Belum ada data kehadiran santri.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // Real-time updates using SSE (Server-Sent Events)
    if (!!window.EventSource) {
        const source = new EventSource('/api/attendance/stream');

        source.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                
                // 1. Show toast notification
                showToastNotification(data.nama_santri, data.status, data.waktu);

                // 2. Fetch updated dashboard content and replace stats & table
                fetch(window.location.href)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Replace stats cards
                        const oldGrid = document.querySelector('.stats-grid');
                        const newGrid = doc.querySelector('.stats-grid');
                        if (oldGrid && newGrid) {
                            oldGrid.innerHTML = newGrid.innerHTML;
                        }

                        // Replace ranking table
                        const oldTable = document.querySelector('.data-table tbody');
                        const newTable = doc.querySelector('.data-table tbody');
                        if (oldTable && newTable) {
                            oldTable.innerHTML = newTable.innerHTML;
                        }
                    })
                    .catch(err => console.error('Error updating dashboard:', err));
            } catch (e) {
                console.error('Error parsing SSE event:', e);
            }
        };
    }

    function showToastNotification(nama, status, waktu) {
        // Create container if it doesn't exist
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            container.style.position = 'fixed';
            container.style.bottom = '30px';
            container.style.right = '30px';
            container.style.zIndex = '9999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '15px';
            document.body.appendChild(container);
        }

        // Determine colors & icons based on status
        let borderLeftColor = '#10B981'; // Green for Hadir
        let iconClass = 'fa-circle-check';
        let iconColor = '#10B981';
        let statusText = 'Hadir';

        if (status === 'Terlambat') {
            borderLeftColor = '#F59E0B'; // Orange
            iconClass = 'fa-clock';
            iconColor = '#F59E0B';
            statusText = 'Terlambat';
        } else if (status === 'Tidak Hadir' || status === 'Tdk Hadir' || status === 'ABSN TUTUP') {
            borderLeftColor = '#EF4444'; // Red
            iconClass = 'fa-triangle-exclamation';
            iconColor = '#EF4444';
            statusText = 'Absen Tutup';
        }

        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.position = 'relative'; // Override fixed position for stack behavior
        toast.style.bottom = 'auto';
        toast.style.right = 'auto';
        toast.style.borderLeftColor = borderLeftColor;

        toast.innerHTML = `
            <div class="toast-icon" style="color: ${iconColor};">
                <i class="fa-solid ${iconClass}"></i>
            </div>
            <div class="toast-body">
                <div class="toast-title">${nama}</div>
                <div class="toast-text">Absensi Subuh: ${statusText} • ${waktu}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        `;

        container.appendChild(toast);

        // Slide in
        setTimeout(() => toast.classList.add('show'), 50);

        // Slide out and remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 6000);
    }
</script>
@endsection



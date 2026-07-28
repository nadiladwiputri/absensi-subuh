@extends('layouts.app')

@section('title', 'Rekapitulasi Kehadiran')
@section('header_title', 'Rekapitulasi Kehadiran Santri')
@section('header_subtitle', 'Laporan kedisiplinan ibadah Subuh bulanan')

@section('styles')
<style>
    .rekap-stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .rekap-card {
        background-color: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01);
        border: 1px solid rgba(226, 232, 240, 0.8);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .rekap-card-title {
        font-size: 0.85rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .rekap-card-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--primary-dark);
    }

    .rekap-card-sub {
        font-size: 0.8rem;
        color: #10B981;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .rekap-filter-row {
        background-color: #ffffff;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 15px;
    }

    .filter-inputs-group {
        display: flex;
        gap: 15px;
        flex-grow: 1;
        flex-wrap: wrap;
    }

    .btn-export {
        background-color: #ffffff;
        color: var(--primary-medium);
        border: 1.5px solid var(--primary-medium);
        transition: all 0.3s ease;
    }

    .btn-export:hover {
        background-color: var(--primary-soft);
    }

    .persen-badge {
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    .persen-perfect {
        background-color: #D1FAE5;
        color: #065F46;
    }

    .persen-good {
        background-color: #DBEAFE;
        color: #1E40AF;
    }

    .persen-warning {
        background-color: #FEF3C7;
        color: #92400E;
    }

    .persen-danger {
        background-color: #FEE2E2;
        color: #991B1B;
    }

    /* Print styling */
    @media print {
        body {
            background-color: #ffffff !important;
        }
        .sidebar, .rekap-filter-row, .btn-export, .top-header, .header-actions, .no-print {
            display: none !important;
        }
        .main-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }
        .rekap-stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
</style>
@endsection

@section('content')

    <!-- Filter & Action Row -->
    <div class="rekap-filter-row">
        <form action="{{ route('rekapitulasi') }}" method="GET" class="filter-inputs-group">
            <div style="display: flex; flex-direction: column; gap: 6px; min-width: 150px;">
                <label style="font-size: 0.85rem; font-weight: 600;" for="bulan">Bulan</label>
                <select id="bulan" name="bulan" class="filter-input">
                    @foreach($namaBulan as $num => $name)
                        <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px; min-width: 120px;">
                <label style="font-size: 0.85rem; font-weight: 600;" for="tahun">Tahun</label>
                <select id="tahun" name="tahun" class="filter-input">
                    @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            

            <div style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn-action">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
            </div>
        </form>

        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-action btn-export">
                <i class="fa-solid fa-file-pdf"></i> Cetak Laporan PDF
            </button>
            <button onclick="exportToCSV()" class="btn-action btn-export">
                <i class="fa-solid fa-file-excel"></i> Ekspor CSV
            </button>
        </div>
    </div>

    <!-- Stats summary grid -->
    <div class="rekap-stats-grid">
        <div class="rekap-card">
            <span class="rekap-card-title">Kehadiran Tertinggi</span>
            <span class="rekap-card-value" style="font-size: 1.15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $santriTerdisiplin }}">
                {{ $santriTerdisiplin }}
            </span>
            <span class="rekap-card-sub"><i class="fa-solid fa-award"></i> {{ $santriTerdisiplinSub }}</span>
        </div>

        <div class="rekap-card">
            <span class="rekap-card-title">Kehadiran Terendah</span>
            <span class="rekap-card-value" style="font-size: 1.15rem; color: #EF4444; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $absenTerbanyakName }}">
                {{ $absenTerbanyakName }}
            </span>
            <span class="rekap-card-sub" style="color: #EF4444;"><i class="fa-solid fa-triangle-exclamation"></i> {{ $absenTerbanyakSub }}</span>
        </div>
    </div>

    <!-- Table Details -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 24px; border-bottom: 1.5px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--primary-dark);">
                Detail Laporan Kehadiran Santri - Periode {{ $namaBulan[$bulan] }} {{ $tahun }}
            </h3>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table" id="rekapTable">
                <thead>
                    <tr>
                        <th style="padding-left: 24px; width: 80px;">No</th>
                        <th>Nama Santri</th>
                        <th style="text-align: center; width: 160px;">Tepat Waktu</th>
                        <th style="text-align: center; width: 130px;">Terlambat</th>
                        <th style="text-align: center; width: 140px;">Tidak Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekap as $index => $r)
                        <tr>
                            <td style="padding-left: 24px;">{{ $index + 1 }}</td>
                            <td style="font-weight: 600; color: var(--text-dark);">{{ $r->nama_santri }}</td>
                            <td style="text-align: center; color: #059669; font-weight: 600;">{{ $r->total_hadir }}</td>
                            <td style="text-align: center; color: #D97706; font-weight: 600;">{{ $r->terlambat }}</td>
                            <td style="text-align: center; color: #EF4444; font-weight: 600;">{{ $r->alpa }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="fa-solid fa-file-circle-xmark"></i>
                                <p>Tidak ada data absensi untuk periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    function exportToCSV() {
        let csv = [];
        let rows = document.querySelectorAll("#rekapTable tr");
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            
            for (let j = 0; j < cols.length; j++) {
                let cellText = cols[j].innerText.trim().replace(/(\r\n|\n|\r)/gm, " ");
                // Escape double quotes
                cellText = cellText.replace(/"/g, '""');
                row.push('"' + cellText + '"');
            }
            csv.push(row.join(","));
        }
        
        // Download CSV file
        let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
        let downloadLink = document.createElement("a");
        downloadLink.download = "Laporan_Kehadiran_Subuh_{{ $bulan }}_{{ $tahun }}.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    // Toast helper for Rekapitulasi page
    function showToast(title, text, isSuccess = true) {
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

        const borderLeftColor = isSuccess ? '#10B981' : '#EF4444';
        const iconClass = isSuccess ? 'fa-circle-check' : 'fa-triangle-exclamation';
        const iconColor = isSuccess ? '#10B981' : '#EF4444';

        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.position = 'relative';
        toast.style.bottom = 'auto';
        toast.style.right = 'auto';
        toast.style.borderLeftColor = borderLeftColor;

        toast.innerHTML = `
            <div class="toast-icon" style="color: ${iconColor};">
                <i class="fa-solid ${iconClass}"></i>
            </div>
            <div class="toast-body">
                <div class="toast-title">${title}</div>
                <div class="toast-text">${text}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        `;

        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 50);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 6000);
    }
</script>
@endsection

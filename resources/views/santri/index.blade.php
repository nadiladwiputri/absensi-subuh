@extends('layouts.app')

@section('title', 'Data Santri')
@section('header_title', 'Kelola Data Santri')
@section('header_subtitle', 'Manajemen data diri dan status pendaftaran fingerprint santri')

@section('styles')
<style>
    .filter-card {
        background-color: #ffffff;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        display: flex;
        gap: 15px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex-grow: 1;
        min-width: 180px;
    }

    .filter-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .filter-input {
        padding: 10px 14px;
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        font-size: 0.9rem;
        width: 100%;
        background-color: #ffffff;
        transition: all 0.3s ease;
    }

    .filter-input:focus {
        outline: none;
        border-color: var(--primary-medium);
    }

    .btn-action {
        padding: 10px 20px;
        background-color: var(--primary-medium);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-action:hover {
        background-color: var(--primary-dark);
    }

    .btn-reset {
        background-color: #F1F5F9;
        color: var(--text-dark);
        border: 1.5px solid var(--border-color);
    }

    .btn-reset:hover {
        background-color: #E2E8F0;
    }

    .btn-add {
        background-color: var(--primary-dark);
    }

    .btn-add:hover {
        background-color: #064E3B;
    }

    /* Modal dialog */
    .modal-backdrop {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(15, 23, 42, 0.4);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .modal-backdrop.show {
        opacity: 1;
        pointer-events: auto;
    }

    .modal-content {
        background-color: #ffffff;
        border-radius: 20px;
        width: 500px;
        max-width: 90%;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transform: scale(0.95);
        transition: transform 0.3s ease;
    }

    .modal-backdrop.show .modal-content {
        transform: scale(1);
    }

    .modal-header {
        background-color: var(--primary-dark);
        color: #ffffff;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.15rem;
        font-weight: 700;
    }

    .modal-close {
        background: none;
        border: none;
        color: #ffffff;
        font-size: 1.25rem;
        cursor: pointer;
        opacity: 0.8;
    }

    .modal-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        padding: 16px 24px;
        background-color: #F8FAFC;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    /* Row actions */
    .row-actions {
        display: flex;
        gap: 8px;
    }

    .btn-icon-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        font-size: 0.9rem;
    }

    .btn-edit {
        background-color: #FEF3C7;
        color: #D97706;
    }

    .btn-edit:hover {
        background-color: #FDE68A;
    }

    .btn-delete {
        background-color: #FEE2E2;
        color: #EF4444;
    }

    .btn-delete:hover {
        background-color: #FCA5A5;
    }

    /* Pagination design */
    .pagination-wrapper {
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pagination-info {
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    .pagination-nav {
        display: flex;
        gap: 5px;
    }

    .pagination-link {
        padding: 8px 14px;
        border: 1px solid var(--border-color);
        background-color: #ffffff;
        color: var(--text-dark);
        text-decoration: none;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .pagination-link.active {
        background-color: var(--primary-medium);
        color: #ffffff;
        border-color: var(--primary-medium);
    }

    .pagination-link:hover:not(.active) {
        background-color: #F1F5F9;
    }
</style>
@endsection

@section('content')

    <!-- Notification Banner -->
    @if(session('success'))
        <div style="background-color: #D1FAE5; border-left: 4px solid #10B981; color: #065F46; padding: 16px; border-radius: 12px; font-weight: 500;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Filtering & Search Row -->
    <div class="filter-card">
        <form action="{{ route('santri.index') }}" method="GET" style="display: flex; gap: 15px; flex-grow: 1; flex-wrap: wrap;">
            <div class="filter-group">
                <label class="filter-label" for="search">Cari Nama Santri</label>
                <input type="text" id="search" name="search" class="filter-input" placeholder="Masukkan nama santri..." value="{{ request('search') }}">
            </div>
            
            

            <div class="filter-group">
                <label class="filter-label" for="status">Status</label>
                <select id="status" name="status" class="filter-input">
                    <option value="">Semua Status</option>
                    <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Nonaktif" {{ request('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-action">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                <a href="{{ route('santri.index') }}" class="btn-action btn-reset">
                    <i class="fa-solid fa-arrows-rotate"></i> Reset
                </a>
            </div>
        </form>
        
        <div>
            <button class="btn-action btn-add" onclick="openAddModal()">
                <i class="fa-solid fa-user-plus"></i> Tambah Santri
            </button>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 24px; width: 5%;">No</th>
                        <th style="width: 45%;">Nama Santri</th>
                        <th style="width: 17%;">Fingerprint ID</th>
                        <th style="width: 18%;">No. HP Orang Tua</th>
                        <th style="width: 10%;">Status</th>
                        <th style="padding-right: 24px; width: 15%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($santri as $index => $s)
                        <tr>
                            <td style="padding-left: 24px;">{{ $santri->firstItem() + $index }}</td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-dark);">{{ $s->nama_santri }}</div>
                            </td>
                            <td>
                                @if($s->fingerprint_id)
                                    <span style="background-color: var(--primary-soft); color: var(--primary-dark); font-weight: 600; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem;">
                                        ID #{{ $s->fingerprint_id }}
                                    </span>
                                @else
                                    <span style="background-color: #F1F5F9; color: #94A3B8; font-weight: 500; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-style: italic;">
                                        Belum Terdaftar
                                    </span>
                                @endif
                            </td>
                            
                            <td>
                                <a href="tel:{{ preg_replace('/[^0-9]/', '', $s->no_hp_ortu) }}" style="color: var(--primary-medium); text-decoration: none; font-weight: 500;">
                                    <i class="fa-solid fa-phone" style="color: #3b82f6; font-size: 0.9rem;"></i> {{ $s->no_hp_ortu }}
                                </a>
                            </td>
                            <td>
                                <span class="badge {{ $s->status == 'Aktif' ? 'badge-success' : 'badge-danger' }}">
                                    <i class="fa-solid {{ $s->status == 'Aktif' ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i> {{ $s->status }}
                                </span>
                            </td>
                            <td style="padding-right: 24px;">
                                <div class="row-actions" style="justify-content: center;">
                                    @if(!$s->fingerprint_id)
                                        <button class="btn-icon-action" style="background-color: #F8FAFC; color: var(--primary-medium); border: 1px solid var(--primary-medium);" title="Hubungkan Sidik Jari" onclick="startScanEnrollFromTable({{ $s->id_santri }}, '{{ addslashes($s->nama_santri) }}')">
                                            <i class="fa-solid fa-fingerprint"></i>
                                        </button>
                                    @endif
                                    <button class="btn-icon-action btn-edit" title="Edit Santri" 
                                            onclick="openEditModal({{ json_encode($s) }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('santri.destroy', $s->id_santri) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data santri ini? Data sidik jari pada alat juga akan terhapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-action btn-delete" title="Hapus Santri">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fa-solid fa-user-slash"></i>
                                <p>Tidak ada data santri ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Custom Pagination -->
        @if($santri->hasPages())
            <div class="pagination-wrapper" style="padding: 20px 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div class="pagination-info" style="font-size: 0.9rem; color: var(--text-muted);">
                    Menampilkan {{ $santri->firstItem() }} sampai {{ $santri->lastItem() }} dari {{ $santri->total() }} santri
                </div>
                <div class="pagination-nav" style="display: flex; gap: 5px;">
                    {{-- Previous Page Link --}}
                    @if($santri->onFirstPage())
                        <span class="pagination-link" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">Sebelumnya</span>
                    @else
                        <a href="{{ $santri->previousPageUrl() }}" class="pagination-link">Sebelumnya</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @for($i = 1; $i <= $santri->lastPage(); $i++)
                        @if($i == $santri->currentPage())
                            <span class="pagination-link active">{{ $i }}</span>
                        @else
                            <a href="{{ $santri->url($i) }}" class="pagination-link">{{ $i }}</a>
                        @endif
                    @endfor

                    {{-- Next Page Link --}}
                    @if($santri->hasMorePages())
                        <a href="{{ $santri->nextPageUrl() }}" class="pagination-link">Berikutnya</a>
                    @else
                        <span class="pagination-link" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">Berikutnya</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Add Santri Modal -->
    <div class="modal-backdrop" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Data Santri Baru</h3>
                <button class="modal-close" onclick="closeAddModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('santri.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="add_nama_santri">Nama Lengkap Santri</label>
                        <input type="text" id="add_nama_santri" name="nama_santri" class="filter-input" placeholder="Masukkan nama santri..." required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add_fingerprint_id">Fingerprint ID (pada Sensor)</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="number" id="add_fingerprint_id" name="fingerprint_id" class="filter-input" placeholder="Opsional (Bisa didaftarkan nanti)" min="1" max="127" readonly style="background-color: #F8FAFC; cursor: not-allowed;">
                            <button type="button" id="btn_scan_add" class="btn-action" style="background-color: var(--primary-medium);" onclick="startScanEnroll('add')">
                                <i class="fa-solid fa-fingerprint"></i> Scan Jari
                            </button>
                        </div>
                        <small id="scan_status_add" style="color: #64748B; font-weight: 500; margin-top: 5px; display: block;"></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add_no_hp_ortu">No. HP / Handphone Orang Tua</label>
                        <input type="text" id="add_no_hp_ortu" name="no_hp_ortu" class="filter-input" placeholder="Contoh: 08123456789" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add_status">Status Santri</label>
                        <select id="add_status" name="status" class="filter-input" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action btn-reset" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn-action">Simpan Santri</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Santri Modal -->
    <div class="modal-backdrop" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Data Santri</h3>
                <button class="modal-close" onclick="closeEditModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="edit_nama_santri">Nama Lengkap Santri</label>
                        <input type="text" id="edit_nama_santri" name="nama_santri" class="filter-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_fingerprint_id">Fingerprint ID (pada Sensor)</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="number" id="edit_fingerprint_id" name="fingerprint_id" class="filter-input" placeholder="Opsional (Bisa didaftarkan nanti)" min="1" max="127" readonly style="background-color: #F8FAFC; cursor: not-allowed;">
                            <button type="button" id="btn_scan_edit" class="btn-action" style="background-color: var(--primary-medium);" onclick="startScanEnroll('edit')">
                                <i class="fa-solid fa-fingerprint"></i> Scan Jari
                            </button>
                        </div>
                        <small id="scan_status_edit" style="color: #64748B; font-weight: 500; margin-top: 5px; display: block;"></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_no_hp_ortu">No. HP / Handphone Orang Tua</label>
                        <input type="text" id="edit_no_hp_ortu" name="no_hp_ortu" class="filter-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_status">Status Santri</label>
                        <select id="edit_status" name="status" class="filter-input" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action btn-reset" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn-action">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Direct Scan Modal -->
    <div id="directScanModal" class="modal-backdrop">
        <div class="modal-content" style="max-width: 450px; text-align: center; padding: 30px;">
            <div style="font-size: 3.5rem; color: var(--primary-medium); margin-bottom: 20px;">
                <i class="fa-solid fa-fingerprint" id="directScanIcon"></i>
            </div>
            <h3 style="font-size: 1.3rem; margin-bottom: 10px; color: var(--text-dark);" id="directScanTitle">Hubungkan Sidik Jari</h3>
            <p style="color: var(--text-muted); font-size: 1rem; margin-bottom: 25px;" id="directScanDesc">
                Meminta sesi pendaftaran ke sensor...
            </p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" class="btn-action btn-reset" onclick="closeDirectScanModal()">Tutup / Batal</button>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // Modal controls
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');

    function openAddModal() {
        addModal.classList.add('show');
    }

    function closeAddModal() {
        if (scanInterval) {
            clearInterval(scanInterval);
            scanInterval = null;
        }
        const form = addModal.querySelector('form');
        if (form) {
            form.reset();
        }
        const statusEl = document.getElementById('scan_status_add');
        if (statusEl) {
            statusEl.innerHTML = '';
            statusEl.style.color = '#64748B';
        }
        const btnEl = document.getElementById('btn_scan_add');
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.style.opacity = '1';
        }
        fetch('/api/fingerprint/cancel-enroll').catch(err => console.error(err));
        addModal.classList.remove('show');
    }

    function openEditModal(santri) {
        document.getElementById('edit_nama_santri').value = santri.nama_santri;
        document.getElementById('edit_fingerprint_id').value = santri.fingerprint_id || '';
        document.getElementById('edit_no_hp_ortu').value = santri.no_hp_ortu;
        document.getElementById('edit_status').value = santri.status;
        
        // Dynamically set form action route
        editForm.action = `/santri/${santri.id_santri}`;
        
        editModal.classList.add('show');
    }

    function closeEditModal() {
        if (scanInterval) {
            clearInterval(scanInterval);
            scanInterval = null;
        }
        const statusEl = document.getElementById('scan_status_edit');
        if (statusEl) {
            statusEl.innerHTML = '';
            statusEl.style.color = '#64748B';
        }
        const btnEl = document.getElementById('btn_scan_edit');
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.style.opacity = '1';
        }
        fetch('/api/fingerprint/cancel-enroll').catch(err => console.error(err));
        editModal.classList.remove('show');
    }

    // Close modals on clicking backdrop
    window.addEventListener('click', function(e) {
        if (e.target === addModal) closeAddModal();
        if (e.target === editModal) closeEditModal();
        if (e.target === directScanModal) closeDirectScanModal();
    });

    let scanInterval = null;

    function startScanEnroll(type) {
        const inputId = type === 'add' ? 'add_fingerprint_id' : 'edit_fingerprint_id';
        const statusId = type === 'add' ? 'scan_status_add' : 'scan_status_edit';
        const btnId = type === 'add' ? 'btn_scan_add' : 'btn_scan_edit';
        
        const statusEl = document.getElementById(statusId);
        const inputEl = document.getElementById(inputId);
        const btnEl = document.getElementById(btnId);

        statusEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghubungkan ke alat sensor...';
        statusEl.style.color = '#3B82F6';
        btnEl.disabled = true;
        btnEl.style.opacity = '0.7';

        // 1. Request enrollment session
        fetch('/api/fingerprint/request-enroll')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const expectedId = data.fingerprint_id;
                    statusEl.innerHTML = '<i class="fa-solid fa-fingerprint fa-bounce"></i> Menunggu jari Anda ditempelkan ke alat... (ID #' + expectedId + ')';
                    
                    // 2. Poll for enrollment completion
                    if (scanInterval) clearInterval(scanInterval);
                    
                    scanInterval = setInterval(() => {
                        fetch('/api/fingerprint/check-enroll-status')
                            .then(res => res.json())
                            .then(statusData => {
                                if (statusData.status === 'success') {
                                    clearInterval(scanInterval);
                                    inputEl.value = statusData.fingerprint_id;
                                    statusEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + statusData.message;
                                    statusEl.style.color = '#10B981';
                                    btnEl.disabled = false;
                                    btnEl.style.opacity = '1';
                                } else if (statusData.status === 'failed') {
                                    clearInterval(scanInterval);
                                    statusEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + statusData.message;
                                    statusEl.style.color = '#EF4444';
                                    btnEl.disabled = false;
                                    btnEl.style.opacity = '1';
                                }
                            });
                    }, 1500);
                } else {
                    statusEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Gagal membuat sesi pemindaian.';
                    statusEl.style.color = '#EF4444';
                    btnEl.disabled = false;
                    btnEl.style.opacity = '1';
                }
            })
            .catch(err => {
                statusEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Koneksi server bermasalah.';
                statusEl.style.color = '#EF4444';
                btnEl.disabled = false;
                btnEl.style.opacity = '1';
            });
    }

    // Direct Enroll from Table
    const directScanModal = document.getElementById('directScanModal');
    function openDirectScanModal() { directScanModal.classList.add('show'); }
    function closeDirectScanModal() { 
        directScanModal.classList.remove('show'); 
        if (scanInterval) clearInterval(scanInterval);
    }

    function startScanEnrollFromTable(id_santri, nama_santri) {
        document.getElementById('directScanTitle').innerText = `Hubungkan Jari: ${nama_santri}`;
        document.getElementById('directScanDesc').innerText = 'Meminta sesi pendaftaran ke sensor...';
        document.getElementById('directScanIcon').className = 'fa-solid fa-spinner fa-spin';
        document.getElementById('directScanIcon').style.color = '#3B82F6';
        openDirectScanModal();

        fetch(`/api/fingerprint/request-enroll?id_santri=${id_santri}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('directScanDesc').innerText = `Silakan tempelkan jari ${nama_santri} ke sensor alat... (ID #${data.fingerprint_id})`;
                    document.getElementById('directScanIcon').className = 'fa-solid fa-fingerprint fa-bounce';
                    document.getElementById('directScanIcon').style.color = 'var(--primary-medium)';
                    
                    if (scanInterval) clearInterval(scanInterval);
                    scanInterval = setInterval(() => {
                        fetch('/api/fingerprint/check-enroll-status')
                            .then(res => res.json())
                            .then(statusData => {
                                if (statusData.status === 'success') {
                                    clearInterval(scanInterval);
                                    document.getElementById('directScanDesc').innerHTML = `<strong>Berhasil!</strong> ${statusData.message}`;
                                    document.getElementById('directScanIcon').className = 'fa-solid fa-circle-check';
                                    document.getElementById('directScanIcon').style.color = '#10B981';
                                    setTimeout(() => window.location.reload(), 2000);
                                } else if (statusData.status === 'failed') {
                                    clearInterval(scanInterval);
                                    document.getElementById('directScanDesc').innerHTML = `<strong>Gagal:</strong> ${statusData.message}`;
                                    document.getElementById('directScanIcon').className = 'fa-solid fa-circle-xmark';
                                    document.getElementById('directScanIcon').style.color = '#EF4444';
                                }
                            });
                    }, 1500);
                } else {
                    document.getElementById('directScanDesc').innerText = 'Gagal membuat sesi pemindaian.';
                    document.getElementById('directScanIcon').className = 'fa-solid fa-circle-xmark';
                    document.getElementById('directScanIcon').style.color = '#EF4444';
                }
            })
            .catch(err => {
                document.getElementById('directScanDesc').innerText = 'Koneksi server bermasalah.';
                document.getElementById('directScanIcon').className = 'fa-solid fa-circle-xmark';
                document.getElementById('directScanIcon').style.color = '#EF4444';
            });
    }
</script>
@endsection

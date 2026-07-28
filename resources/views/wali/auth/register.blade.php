@extends('layouts.auth')

@section('title', 'Daftar Akun Wali Santri')

@section('content')
    <div class="form-header">
        <h2 class="form-title">Daftar Akun Orang Tua 📝</h2>
        <p class="form-subtitle">Lengkapi data untuk membuat akses pemantauan anak</p>
    </div>

    @if ($errors->any())
        <div class="error-container">
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li><i class="fa-solid fa-triangle-exclamation"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('wali.register') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="nama_wali" class="form-label">Nama Lengkap Anda (Orang Tua / Wali)</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-user input-icon"></i>
                <input type="text" id="nama_wali" name="nama_wali" class="form-input" placeholder="Masukkan nama lengkap Anda" value="{{ old('nama_wali') }}" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="no_hp" class="form-label">Nomor HP / Handphone Anda</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-mobile-button input-icon" style="color: #3b82f6; font-size: 1.2rem;"></i>
                <input type="text" id="no_hp" name="no_hp" class="form-input" placeholder="Contoh: 081275245541" value="{{ old('no_hp') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
            </div>
            <span style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; display: block;">
                <i class="fa-solid fa-circle-info"></i> Harus sama dengan nomor HP yang terdaftar di biodata santri.
            </span>
        </div>

        <div class="form-group">
            <label for="telegram_chat_id" class="form-label">Telegram Chat ID (Opsional - Untuk Notifikasi)</label>
            <div class="input-wrapper">
                <i class="fa-brands fa-telegram input-icon" style="color: #0088cc; font-size: 1.2rem;"></i>
                <input type="text" id="telegram_chat_id" name="telegram_chat_id" class="form-input" placeholder="Contoh: 987654321 (Dapat diisi nanti)" value="{{ old('telegram_chat_id') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            <span style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px; display: block; line-height: 1.3;">
                <i class="fa-solid fa-circle-info"></i> <b>Cara mendapatkan Chat ID:</b> Buka Telegram, cari bot absensi Anda, lalu klik <b>Start</b>. Bot akan langsung mengirimkan Chat ID unik Anda.
            </span>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Buat Password Baru</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required>
                <i class="fa-regular fa-eye-slash password-toggle" id="togglePassword"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Ulangi password baru" required>
                <i class="fa-regular fa-eye-slash password-toggle" id="toggleConfirmPassword"></i>
            </div>
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 10px;">
            <i class="fa-solid fa-user-check"></i> Daftar Akun Saya
        </button>
    </form>

    <div class="divider">sudah punya akun?</div>

    <a href="{{ route('wali.login') }}" class="btn-secondary">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk / Login Portal Wali
    </a>
@endsection

@section('scripts')
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const passwordConfirmation = document.querySelector('#password_confirmation');
        toggleConfirmPassword.addEventListener('click', function (e) {
            const type = passwordConfirmation.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmation.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
@endsection

@extends('layouts.auth')

@section('title', 'Login Wali Santri')

@section('content')
    <div class="form-header">
        <h2 class="form-title">Portal Orang Tua 🕌</h2>
        <p class="form-subtitle">Masuk untuk memantau perkembangan & absensi anak Anda</p>
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

    <form action="{{ route('wali.login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="no_hp" class="form-label">Nomor HP / Handphone Orang Tua</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-mobile-button input-icon" style="color: #3b82f6; font-size: 1.2rem;"></i>
                <input type="text" id="no_hp" name="no_hp" class="form-input" placeholder="Contoh: 081275245541" value="{{ old('no_hp') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required autofocus>
            </div>
            <span style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; display: block;">
                Gunakan nomor HP / Handphone yang terdaftar di data santri.
            </span>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password Anda" required>
                <i class="fa-regular fa-eye-slash password-toggle" id="togglePassword"></i>
            </div>
        </div>

        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                Ingat saya
            </label>
            <a href="#" class="forgot-password" onclick="alert('Silakan hubungi ustadz/pengelola pondok untuk mereset password akun wali.'); return false;">Lupa password?</a>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal
        </button>
    </form>
    <div class="divider">atau belum punya akun?</div>

    <a href="{{ route('wali.register') }}" class="btn-secondary">
        <i class="fa-solid fa-user-plus"></i> Daftar Akun Orang Tua
    </a>
    <div style="text-align: center; margin-top: 20px;">
        <a href="{{ route('login') }}" style="color: var(--primary-medium); font-weight: 500; font-size: 0.9rem; text-decoration: none;">
            <i class="fa-solid fa-user-shield"></i> Login sebagai Pengelola / Ustadz
        </a>
    </div>
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
    </script>
@endsection

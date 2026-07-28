@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="form-header">
        <h2 class="form-title">Selamat Datang Kembali 👋</h2>
        <p class="form-subtitle">Masuk untuk mengakses sistem absensi subuh</p>
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

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="login" class="form-label">Username / Email</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-user input-icon"></i>
                <input type="text" id="login" name="login" class="form-input" placeholder="Masukkan username atau email" value="{{ old('login') }}" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password" required>
                <i class="fa-regular fa-eye-slash password-toggle" id="togglePassword"></i>
            </div>
        </div>

        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                Ingat saya
            </label>
            <a href="#" class="forgot-password" onclick="alert('Silakan hubungi administrator utama untuk reset password.'); return false;">Lupa password?</a>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-right-to-bracket"></i> Login
        </button>
    </form>

    <div class="divider">atau</div>

    <a href="{{ route('register') }}" class="btn-secondary">
        <i class="fa-solid fa-user-plus"></i> Daftar Akun Admin Baru
    </a>

    <div style="text-align: center; margin-top: 20px; border-top: 1.5px solid var(--border-color); padding-top: 20px;">
        <a href="{{ route('wali.login') }}" style="color: var(--primary-medium); font-weight: 600; font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: color 0.2s;">
            <i class="fa-solid fa-users" style="font-size: 1.2rem; color: #3b82f6;"></i> Masuk ke Portal Orang Tua / Wali Santri
        </a>
    </div>
@endsection

@section('scripts')
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye slash icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
@endsection

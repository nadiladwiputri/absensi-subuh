@extends('layouts.auth')

@section('title', 'Registrasi Admin')

@section('content')
    <div class="form-header">
        <h2 class="form-title">Daftar Akun Baru 📝</h2>
        <p class="form-subtitle">Buat akun untuk mengakses sistem absensi subuh</p>
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

    <form action="{{ route('register') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-id-card input-icon"></i>
                <input type="text" id="nama" name="nama" class="form-input" placeholder="Masukkan nama lengkap" value="{{ old('nama') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-user input-icon"></i>
                <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan username" value="{{ old('username') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-envelope input-icon"></i>
                <input type="email" id="email" name="email" class="form-input" placeholder="Masukkan email" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="role" class="form-label">Hak Akses (Role)</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-user-shield input-icon"></i>
                <select id="role" name="role" class="form-input" style="padding-left: 46px; appearance: none; -webkit-appearance: none; -moz-appearance: none; background: url('data:image/svg+xml;utf8,<svg fill=%22%2364748B%22 height=%2224%22 viewBox=%220 0 24 24%22 width=%2224%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M7 10l5 5 5-5z%22/><path d=%22M0 0h24v24H0z%22 fill=%22none%22/></svg>') no-repeat right 16px center #fff;" required>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password (min. 6 karakter)" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Masukkan ulang password" required>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-user-plus"></i> Register
        </button>
    </form>

    <div class="divider">sudah memiliki akun?</div>

    <a href="{{ route('login') }}" class="btn-secondary">
        <i class="fa-solid fa-right-to-bracket"></i> Login ke Akun
    </a>
@endsection

<?php

namespace App\Http\Controllers;

use App\Models\Wali;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class WaliAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('wali')->check()) {
            return redirect()->route('wali.dashboard');
        }
        return view('wali.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'no_hp' => 'required|string',
            'password' => 'required|string',
        ]);

        // Clean input number to ensure uniform format
        $noHp = trim($request->no_hp);
        
        $authData = [
            'no_hp' => $noHp,
            'password' => $request->password,
        ];

        if (Auth::guard('wali')->attempt($authData, $request->has('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('wali.dashboard'));
        }

        return back()->withErrors([
            'no_hp' => 'Nomor HP atau password salah.',
        ])->withInput($request->only('no_hp', 'remember'));
    }

    public function showRegister()
    {
        if (Auth::guard('wali')->check()) {
            return redirect()->route('wali.dashboard');
        }
        return view('wali.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama_wali' => 'required|string|max:100',
            'no_hp' => 'required|string|max:20',
            'telegram_chat_id' => 'nullable|string|max:50',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $noHp = trim($request->no_hp);

        // 1. Validasi apakah nomor HP terdaftar di data santri
        $existsInSantri = Santri::where('no_hp_ortu', $noHp)->exists();
        if (!$existsInSantri) {
            return back()->withErrors([
                'no_hp' => 'Nomor HP ini tidak terdaftar di data santri kami. Silakan hubungi ustadz/pengelola untuk mendaftarkan nomor Anda terlebih dahulu.',
            ])->withInput();
        }

        // 2. Validasi apakah sudah pernah dibuat akun wali sebelumnya
        $existsInWali = Wali::where('no_hp', $noHp)->exists();
        if ($existsInWali) {
            return back()->withErrors([
                'no_hp' => 'Akun untuk nomor HP ini sudah aktif. Silakan langsung login.',
            ])->withInput();
        }

        // 3. Buat akun wali baru
        $wali = Wali::create([
            'nama_wali' => $request->nama_wali,
            'no_hp' => $noHp,
            'telegram_chat_id' => trim($request->telegram_chat_id),
            'password' => Hash::make($request->password),
        ]);

        // 4. Loginkan otomatis
        Auth::guard('wali')->login($wali);

        return redirect()->route('wali.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('wali')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('wali.login');
    }
}

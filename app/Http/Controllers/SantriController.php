<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\FingerprintDeletion;
use Illuminate\Http\Request;

class SantriController extends Controller
{
    public function index(Request $request)
    {
        $query = Santri::query();

        // Search filter (name only)
        if ($request->filled('search')) {
            $query->where('nama_santri', 'like', "%{$request->search}%");
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $santri = $query->orderBy('nama_santri', 'asc')->paginate(10)->withQueryString();
        
        return view('santri.index', compact('santri'));
    }

    public function store(Request $request)
    {
        // Check if there is an automatically created stub record for this fingerprint_id
        if ($request->filled('fingerprint_id')) {
            $existing = Santri::where('fingerprint_id', $request->fingerprint_id)->first();
            if ($existing && str_starts_with($existing->nama_santri, 'Santri Baru (ID #')) {
                 $request->validate([
                    'nama_santri' => 'required|string|max:100',
                    'no_hp_ortu' => 'required|string|max:20',
                    'status' => 'required|string|in:Aktif,Nonaktif',
                ]);

                $existing->update($request->all());

                return redirect()->route('santri.index')->with('success', 'Data santri berhasil ditambahkan.');
            }
        }

        $request->validate([
            'nama_santri' => 'required|string|max:100',
            'fingerprint_id' => 'nullable|integer|unique:santri,fingerprint_id',
            'no_hp_ortu' => 'required|string|max:20',
            'status' => 'required|string|in:Aktif,Nonaktif',
        ]);

        Santri::create($request->all());

        return redirect()->route('santri.index')->with('success', 'Data santri berhasil ditambahkan.');
    }

    public function update(Request $request, Santri $santri)
    {
        $request->validate([
            'nama_santri' => 'required|string|max:100',
            'fingerprint_id' => 'nullable|integer|unique:santri,fingerprint_id,' . $santri->id_santri . ',id_santri',
            'no_hp_ortu' => 'required|string|max:20',
            'status' => 'required|string|in:Aktif,Nonaktif',
        ]);

        $santri->update($request->all());

        return redirect()->route('santri.index')->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy(Santri $santri)
    {
        if ($santri->fingerprint_id) {
            FingerprintDeletion::create([
                'fingerprint_id' => $santri->fingerprint_id,
                'status' => 'pending'
            ]);
        }

        $santri->delete();
        return redirect()->route('santri.index')->with('success', 'Data santri berhasil dihapus.');
    }

    public function clearFingerprint(Santri $santri)
    {
        if ($santri->fingerprint_id) {
            FingerprintDeletion::create([
                'fingerprint_id' => $santri->fingerprint_id,
                'status' => 'pending'
            ]);

            $santri->update(['fingerprint_id' => null]);
            return redirect()->route('santri.index')->with('success', 'Sidik jari berhasil dihapus. Permintaan penghapusan telah dikirim ke alat.');
        }

        return redirect()->route('santri.index')->with('error', 'Santri tidak memiliki sidik jari terdaftar.');
    }
}

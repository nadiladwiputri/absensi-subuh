<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiSubuh extends Model
{
    protected $table = 'absensi_subuh';
    protected $primaryKey = 'id_absensi';

    protected $fillable = [
        'id_santri',
        'waktu_absensi',
        'tanggal',
        'jadwal_subuh',
        'status_kehadiran',
        'poin',
        'keterangan',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'id_santri', 'id_santri');
    }
}

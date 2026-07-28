<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Santri extends Model
{
    protected $table = 'santri';
    protected $primaryKey = 'id_santri';

    protected $fillable = [
        'nama_santri',
        'fingerprint_id',
        'no_hp_ortu',
        'status',
    ];

    public function absensi()
    {
        return $this->hasMany(AbsensiSubuh::class, 'id_santri', 'id_santri');
    }

    public function wali()
    {
        return $this->belongsTo(Wali::class, 'no_hp_ortu', 'no_hp');
    }
}

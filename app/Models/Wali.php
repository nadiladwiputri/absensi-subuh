<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Wali extends Authenticatable
{
    use Notifiable;

    protected $table = 'wali';
    protected $primaryKey = 'id_wali';

    protected $fillable = [
        'nama_wali',
        'no_hp',
        'telegram_chat_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function santri()
    {
        return $this->hasMany(Santri::class, 'no_hp_ortu', 'no_hp');
    }
}

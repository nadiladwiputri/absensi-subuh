<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FingerprintDeletion extends Model
{
    protected $table = 'fingerprint_deletions';

    protected $fillable = [
        'fingerprint_id',
        'status',
    ];
}

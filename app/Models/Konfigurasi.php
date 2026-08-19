<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konfigurasi extends Model
{
    protected $table = 'konfigurasi';
    protected $fillable = [
        'judul',
        'profil',
        'instagram',
        'facebook',
        'tiktok',
        'telepon',
        'email',
    ];
}

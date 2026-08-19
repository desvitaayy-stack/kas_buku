<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman'; // sesuaikan jika beda

    protected $fillable = [
        'judul',
        'isi',
        'tanggal_hapus',
    ];

    protected $casts = [
        'tanggal_hapus' => 'date',
    ];

    // untuk dashboard user (opsional)
    public function scopeAktif($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('tanggal_hapus')
            ->orWhere('tanggal_hapus', '>=', now()->toDateString());
        });
    }

}

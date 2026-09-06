<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_kecamatan',
        'nama_kecamatan',
    ];

    public function kelurahans()
    {
        return $this->hasMany(Kelurahan::class, 'kecamatan_id');
    }

    public function lokasis()
    {
        return $this->hasMany(LokasiPju::class, 'kecamatan_id');
    }
}

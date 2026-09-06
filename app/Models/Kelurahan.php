<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kecamatan_id',
        'kode_kelurahan',
        'nama_kelurahan',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function lokasis()
    {
        return $this->hasMany(LokasiPju::class, 'kelurahan_id');
    }
}

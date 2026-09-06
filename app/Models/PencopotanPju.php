<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PencopotanPju extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'pencopotan_pjus';

    protected $fillable = [
        'no_pencopotan',
        'pju_asset_id',
        'lokasi_id',
        'tanggal_copot',
        'teknisi_id',
        'alasan',
        'kondisi_barang',
        'catatan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logUnguarded()->logOnlyDirty();
    }

    public function asset()
    {
        return $this->belongsTo(PjuAsset::class, 'pju_asset_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiPju::class, 'lokasi_id');
    }

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PemasanganPju extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'pemasangan_pjus';

    protected $fillable = [
        'no_pemasangan',
        'pju_asset_id',
        'lokasi_id',
        'teknisi_id',
        'maintenance_id',
        'tanggal_pasang',
        'kondisi_pasang',
        'foto_pemasangan',
        'catatan',
        'status',
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

    public function maintenance()
    {
        return $this->belongsTo(MaintenancePju::class, 'maintenance_id');
    }
}

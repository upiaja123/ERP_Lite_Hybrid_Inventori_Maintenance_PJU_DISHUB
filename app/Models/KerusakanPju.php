<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class KerusakanPju extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'kerusakan_pjus';

    protected $fillable = [
        'no_laporan',
        'pju_asset_id',
        'lokasi_id',
        'tanggal_laporan',
        'pelapor_id',
        'nama_pelapor',
        'jenis_kerusakan',
        'deskripsi_kerusakan',
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

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    public function maintenances()
    {
        return $this->hasMany(MaintenancePju::class, 'kerusakan_id');
    }
}

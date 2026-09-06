<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MaintenancePju extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'maintenance_pjus';

    protected $fillable = [
        'no_work_order',
        'kerusakan_id',
        'pju_asset_id',
        'lokasi_id',
        'teknisi_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_maintenance',
        'tindakan_perbaikan',
        'spare_part_digunakan',
        'hasil',
        'status',
    ];

    protected $casts = [
        'spare_part_digunakan' => 'array',
        'tanggal_mulai'        => 'date',
        'tanggal_selesai'      => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logUnguarded()->logOnlyDirty();
    }

    public function kerusakan()
    {
        return $this->belongsTo(KerusakanPju::class, 'kerusakan_id');
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

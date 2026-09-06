<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GaransiPju extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'garansi_pjus';

    protected $fillable = [
        'pju_asset_id',
        'supplier_id',
        'no_kontrak_garansi',
        'tanggal_mulai',
        'tanggal_berakhir',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_berakhir' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logUnguarded()->logOnlyDirty();
    }

    public function asset()
    {
        return $this->belongsTo(PjuAsset::class, 'pju_asset_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}

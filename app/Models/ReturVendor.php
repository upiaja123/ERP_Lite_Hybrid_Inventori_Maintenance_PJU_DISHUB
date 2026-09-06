<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ReturVendor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'retur_vendors';

    protected $fillable = [
        'no_retur',
        'pju_asset_id',
        'supplier_id',
        'alasan_retur',
        'tanggal_retur',
        'tanggal_kembali',
        'kondisi_kembali',
        'status',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_retur'   => 'date',
        'tanggal_kembali' => 'date',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Watt extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nilai_watt',
        'satuan_daya',
        'keterangan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logUnguarded()->logOnlyDirty();
    }

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'watt_id');
    }
}

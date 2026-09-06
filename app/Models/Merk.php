<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Merk extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nama_merk',
        'keterangan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logUnguarded()->logOnlyDirty();
    }

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'merk_id');
    }
}

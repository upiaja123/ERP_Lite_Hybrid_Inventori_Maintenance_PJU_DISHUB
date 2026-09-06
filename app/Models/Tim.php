<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tim extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'kode_tim',
        'nama_tim',
        'penanggung_jawab',
        'kontak',
        'wilayah_tugas',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logUnguarded()->logOnlyDirty();
    }
}

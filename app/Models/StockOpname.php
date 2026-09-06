<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StockOpname extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'kode_opname',
        'tanggal_opname',
        'barang_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'status_opname',
        'alasan_selisih',
        'petugas_id',
        'approved_by',
        'approved_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logUnguarded()->logOnlyDirty();
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

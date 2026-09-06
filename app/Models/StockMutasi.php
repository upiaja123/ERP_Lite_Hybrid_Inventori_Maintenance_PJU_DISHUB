<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StockMutasi extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'stock_mutasis';

    protected $fillable = [
        'kode_mutasi',
        'tanggal_mutasi',
        'barang_id',
        'jumlah',
        'tipe_mutasi',
        'asal_lokasi',
        'tujuan_lokasi_id',
        'tujuan_lokasi_nama',
        'status',
        'catatan',
        'created_by',
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

    public function tujuanLokasi()
    {
        return $this->belongsTo(LokasiPju::class, 'tujuan_lokasi_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PjuAsset extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'pju_assets';

    protected $fillable = [
        'kode_pju',
        'nama_pju',
        'barang_id',
        'lokasi_id',
        'jenis_lampu',
        'daya_watt',
        'merk',
        'tipe_spesifikasi',
        'no_seri',
        'tahun_pengadaan',
        'no_kontrak',
        'supplier_id',
        'status_aset',
        'garansi_mulai',
        'garansi_berakhir',
        'catatan',
        'created_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty();
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiPju::class, 'lokasi_id');
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

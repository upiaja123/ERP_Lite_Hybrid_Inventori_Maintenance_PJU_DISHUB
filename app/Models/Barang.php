<?php

namespace App\Models;

use App\Models\User;
use App\Models\Jenis;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Models\Merk;
use App\Models\Watt;
use App\Models\BarangMasuk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Barang extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'deskripsi',
        'gambar',
        'stok_minimum',
        'jenis_id',
        'merk_id',
        'watt_id',
        'supplier_id',
        'barcode_type',
        'barcode_value',
        'tanggal_pengadaan',
        'masa_garansi_bulan',
        'stok',
        'satuan_id',
        'user_id',
        'status',
    ];

    protected $guarded = ['id'];
    protected $ignoreChangedAttributes = ['updated_at'];

    protected $casts = [
        'gambar' => 'array',
        'tanggal_pengadaan' => 'date',
    ];

    protected $attributes = [
        'stok' => 0,
        'status' => 'AKTIF',
        'barcode_type' => 'BATCH',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jenis()
    {
        return $this->belongsTo(Jenis::class, 'jenis_id');
    }

    public function merk()
    {
        return $this->belongsTo(Merk::class, 'merk_id');
    }

    public function watt()
    {
        return $this->belongsTo(Watt::class, 'watt_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function barangMasuks()
    {
        return $this->hasMany(BarangMasuk::class, 'barang_id');
    }

    public function lastBarangMasuk()
    {
        return $this->hasOne(BarangMasuk::class, 'barang_id')->latestOfMany();
    }

    public function pjuAssets()
    {
        return $this->hasMany(PjuAsset::class, 'barang_id');
    }
}

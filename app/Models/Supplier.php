<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Supplier extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'supplier',
        'nama_supplier',
        'kode_supplier',
        'alamat',
        'telepon',
        'email',
        'kontak_person',
        'deskripsi',
        'user_id'
    ];
    protected $guarded = ['id'];
    protected $ignoreChangedAttributes = ['updated_at'];

    public function setSupplierAttribute($value)
    {
        $this->attributes['supplier'] = $value;
        if (empty($this->attributes['nama_supplier'])) {
            $this->attributes['nama_supplier'] = $value;
        }
    }

    public function setNamaSupplierAttribute($value)
    {
        $this->attributes['nama_supplier'] = $value;
        if (empty($this->attributes['supplier'])) {
            $this->attributes['supplier'] = $value;
        }
    }

    public function getSupplierAttribute($value)
    {
        return $value ?? $this->attributes['nama_supplier'] ?? null;
    }

    public function getNamaSupplierAttribute($value)
    {
        return $value ?? $this->attributes['supplier'] ?? null;
    }

    public function getActivitylogAttributes(): array
    {
        return array_diff($this->fillable, $this->ignoreChangedAttributes);
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty();
    }

    // 1 Supplier memiliki banyak barangMasuk
    public function barangMasuks()
    {
        return $this->hasMany(BarangMasuk::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Jenis extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'nama_jenis',
        'jenis_barang',
        'user_id'
    ];
    protected $guarded = ['id'];
    protected $ignoreChangedAttributes = ['updated_at'];

    public function setJenisBarangAttribute($value)
    {
        $this->attributes['jenis_barang'] = $value;
        if (empty($this->attributes['nama_jenis'])) {
            $this->attributes['nama_jenis'] = $value;
        }
    }

    public function setNamaJenisAttribute($value)
    {
        $this->attributes['nama_jenis'] = $value;
        if (empty($this->attributes['jenis_barang'])) {
            $this->attributes['jenis_barang'] = $value;
        }
    }

    public function getJenisBarangAttribute($value)
    {
        return $value ?? $this->attributes['nama_jenis'] ?? null;
    }

    public function getNamaJenisAttribute($value)
    {
        return $value ?? $this->attributes['jenis_barang'] ?? null;
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

    // 1 Jenis, dimiliki oleh banyak barang
    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }
}

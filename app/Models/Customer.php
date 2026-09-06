<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'customer',
        'nama_customer',
        'kode_customer',
        'alamat',
        'telepon',
        'deskripsi',
        'user_id'
    ];

    protected $guarded = ['id'];
    protected $ignoreChangedAttributes = ['updated_at'];

    public function setCustomerAttribute($value)
    {
        $this->attributes['customer'] = $value;
        if (empty($this->attributes['nama_customer'])) {
            $this->attributes['nama_customer'] = $value;
        }
    }

    public function setNamaCustomerAttribute($value)
    {
        $this->attributes['nama_customer'] = $value;
        if (empty($this->attributes['customer'])) {
            $this->attributes['customer'] = $value;
        }
    }

    public function getCustomerAttribute($value)
    {
        return $value ?? $this->attributes['nama_customer'] ?? null;
    }

    public function getNamaCustomerAttribute($value)
    {
        return $value ?? $this->attributes['customer'] ?? null;
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

    // 1 customer memiliki banyak barangKeluar
    public function barangKeluars()
    {
        return $this->hasMany(BarangKeluar::class);
    }
}

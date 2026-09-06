<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LokasiPju extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'lokasi_pjus';

    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'alamat_jalan',
        'kecamatan_id',
        'kelurahan_id',
        'pole_number',
        'latitude',
        'longitude',
        'google_maps_url',
        'tim_operasional',
        'status',
        'created_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty();
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assets()
    {
        return $this->hasMany(PjuAsset::class, 'lokasi_id');
    }

    /**
     * Accessor untuk Google Maps Link (Fallback ke address search jika lat/lng NULL)
     */
    public function getMapsUrlAttribute(): string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps/search/?api=1&query={$this->latitude},{$this->longitude}";
        }

        $addressQuery = urlencode("{$this->alamat_jalan}, " . ($this->kelurahan->nama_kelurahan ?? '') . ", " . ($this->kecamatan->nama_kecamatan ?? ''));
        return "https://www.google.com/maps/search/?api=1&query={$addressQuery}";
    }
}

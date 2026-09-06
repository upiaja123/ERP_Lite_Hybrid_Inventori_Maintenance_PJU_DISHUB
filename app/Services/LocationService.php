<?php

namespace App\Services;

use App\Models\LokasiPju;
use App\Services\ItemCodeGeneratorService;
use Illuminate\Support\Str;

class LocationService
{
    protected ItemCodeGeneratorService $codeGenerator;

    public function __construct(ItemCodeGeneratorService $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Cari lokasi yang sudah ada (berdasarkan normalisasi alamat) atau buat lokasi baru secara dinamis.
     */
    public function findOrCreateLocation(array $data, ?int $userId = null): LokasiPju
    {
        $normalizedAddress = $this->normalizeAddress($data['alamat_jalan'] ?? '');
        $kelurahanId = $data['kelurahan_id'] ?? null;
        $kecamatanId = $data['kecamatan_id'] ?? null;
        $poleNumber = !empty($data['pole_number']) ? trim($data['pole_number']) : null;

        // 1. Jika ada pole_number dan diset unik (atau dicari via pole_number)
        if ($poleNumber) {
            $existingByPole = LokasiPju::where('pole_number', $poleNumber)
                ->when($kelurahanId, fn($q) => $q->where('kelurahan_id', $kelurahanId))
                ->first();

            if ($existingByPole) {
                // Update GPS jika sebelumnya NULL tapi sekarang diberikan
                $this->updateCoordinatesIfAvailable($existingByPole, $data);
                return $existingByPole;
            }
        }

        // 2. Cari berdasarkan normalisasi Alamat + Kelurahan + Kecamatan
        $existingByAddress = LokasiPju::whereRaw('LOWER(TRIM(alamat_jalan)) = ?', [$normalizedAddress])
            ->when($kelurahanId, fn($q) => $q->where('kelurahan_id', $kelurahanId))
            ->when($kecamatanId, fn($q) => $q->where('kecamatan_id', $kecamatanId))
            ->first();

        if ($existingByAddress) {
            $this->updateCoordinatesIfAvailable($existingByAddress, $data);
            return $existingByAddress;
        }

        // 3. Jika tidak ditemukan, buat Lokasi Baru secara DYNAMIC
        $kodeLokasi = $data['kode_lokasi'] ?? $this->codeGenerator->generate('pju_asset', 'lokasi_pjus', 'kode_lokasi');

        $lat = !empty($data['latitude']) ? (float) $data['latitude'] : null;
        $lng = !empty($data['longitude']) ? (float) $data['longitude'] : null;

        $mapsUrl = null;
        if ($lat && $lng) {
            $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
        }

        return LokasiPju::create([
            'kode_lokasi'     => $kodeLokasi,
            'nama_lokasi'     => $data['nama_lokasi'] ?? ($data['alamat_jalan'] ?? 'Lokasi PJU'),
            'alamat_jalan'    => trim($data['alamat_jalan'] ?? ''),
            'kecamatan_id'    => $kecamatanId,
            'kelurahan_id'    => $kelurahanId,
            'pole_number'     => $poleNumber,
            'latitude'        => $lat,
            'longitude'       => $lng,
            'google_maps_url' => $mapsUrl,
            'tim_operasional' => $data['tim_operasional'] ?? null,
            'status'          => 'AKTIF',
            'created_by'      => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Normalisasi string alamat (trim, single space, lowercase).
     */
    public function normalizeAddress(string $address): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($address)));
    }

    /**
     * Helper untuk memperbarui koordinat GPS jika sebelumnya kosong.
     */
    protected function updateCoordinatesIfAvailable(LokasiPju $location, array $data): void
    {
        $lat = !empty($data['latitude']) ? (float) $data['latitude'] : null;
        $lng = !empty($data['longitude']) ? (float) $data['longitude'] : null;

        if (($lat && $lng) && (!$location->latitude || !$location->longitude)) {
            $location->latitude = $lat;
            $location->longitude = $lng;
            $location->google_maps_url = "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
            $location->save();
        }
    }
}

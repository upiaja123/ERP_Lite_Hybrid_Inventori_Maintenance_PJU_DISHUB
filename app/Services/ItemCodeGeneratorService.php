<?php

namespace App\Services;

use App\Exceptions\ItemCodeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItemCodeGeneratorService
{
    /**
     * Generate code unik untuk entity tertentu.
     * 
     * @param string $entityType (e.g., 'barang', 'barang_masuk', 'barang_keluar', 'pju_asset')
     * @param string|null $table Nama tabel untuk validasi keunikan
     * @param string $column Nama kolom kode di tabel
     * @return string
     * @throws ItemCodeException
     */
    public function generate(string $entityType = 'barang', ?string $table = 'barangs', string $column = 'kode_barang'): string
    {
        $config = config('erp.item_code');
        
        $prefix = $config['entities'][$entityType] ?? $config['prefix'];
        $separator = $config['separator'];
        $padding = $config['padding'];
        $format = $config['format'];

        $maxAttempts = 10;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;

            // Hitung sequence berikutnya secara lebih aman
            $sequence = $this->getNextSequence($table, $column, $prefix, $separator);
            $paddedSeq = str_pad((string)$sequence, $padding, '0', STR_PAD_LEFT);

            // Susun kode berdasarkan format
            if ($format === 'PREFIX-YEAR-SEQ') {
                $year = date('Y');
                $code = "{$prefix}{$separator}{$year}{$separator}{$paddedSeq}";
            } else {
                $code = "{$prefix}{$separator}{$paddedSeq}";
            }

            // Jika tabel diset, pastikan benar-benar unik di DB
            if ($table && DB::table($table)->where($column, $code)->exists()) {
                // Jika sudah ada (race condition), coba lagi dengan sequence bertambah
                continue;
            }

            return $code;
        }

        // Fallback jika terjadi tabrakan berulang (misal random UUID suffix)
        return "{$prefix}{$separator}" . strtoupper(Str::random(6));
    }

    /**
     * Ambil sequence berikutnya dengan aman.
     */
    protected function getNextSequence(?string $table, string $column, string $prefix, string $separator): int
    {
        if (!$table) {
            return rand(1, 99999);
        }

        try {
            // Ambil id / count terbesar sebagai acuan dasar
            $maxId = DB::table($table)->max('id') ?? 0;
            return $maxId + 1;
        } catch (\Exception $e) {
            return rand(1000, 9999);
        }
    }
}

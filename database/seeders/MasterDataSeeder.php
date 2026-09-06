<?php

namespace Database\Seeders;

use App\Models\Watt;
use App\Models\Merk;
use App\Models\Tim;
use App\Models\Satuan;
use App\Models\Jenis;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Watt Master (Minimal 20, 30, 40, 90, 120 Watt)
        $watts = [20, 30, 40, 90, 120];
        foreach ($watts as $w) {
            Watt::firstOrCreate(['nilai_watt' => $w], [
                'satuan_daya' => 'Watt',
                'keterangan'  => "Lampu PJU {$w} Watt"
            ]);
        }

        // 2. Merk Master
        $merks = ['Philips', 'Osram', 'Panasonic', 'Krisbow', 'Indal', 'Solarlite'];
        foreach ($merks as $m) {
            Merk::firstOrCreate(['nama_merk' => $m], [
                'keterangan' => "Merk Lampu/Aset {$m}"
            ]);
        }

        // 3. Tim Master
        $tims = [
            ['kode_tim' => 'TIM-01', 'nama_tim' => 'Tim Maintenance Utara', 'penanggung_jawab' => 'Budi Santoso', 'wilayah_tugas' => 'Kecamatan Utara'],
            ['kode_tim' => 'TIM-02', 'nama_tim' => 'Tim Maintenance Selatan', 'penanggung_jawab' => 'Rahmat Hidayat', 'wilayah_tugas' => 'Kecamatan Selatan'],
            ['kode_tim' => 'TIM-SUBKON', 'nama_tim' => 'Tim Subkontraktor PJU', 'penanggung_jawab' => 'PT Karya PJU', 'wilayah_tugas' => 'Lintas Wilayah'],
        ];
        foreach ($tims as $t) {
            Tim::firstOrCreate(['kode_tim' => $t['kode_tim']], $t);
        }

        // 4. Default Satuan & Jenis
        $satuans = ['Unit', 'Set', 'Buah', 'Batang', 'Meter', 'Roll'];
        foreach ($satuans as $s) {
            Satuan::firstOrCreate(['satuan' => $s]);
        }

        $jenis = ['Lampu LED PJU', 'Tiang PJU', 'Kabel Power', 'Box Panel PJU', 'MCB / Switch', 'Timer / Photocell'];
        foreach ($jenis as $j) {
            Jenis::firstOrCreate(['nama_jenis' => $j]);
        }
    }
}

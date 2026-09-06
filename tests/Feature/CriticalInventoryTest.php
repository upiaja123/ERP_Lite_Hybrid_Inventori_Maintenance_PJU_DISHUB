<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\StockLedger;
use App\Models\StockOpname;
use App\Models\User;
use App\Models\Role;
use App\Services\InventoryService;
use App\Services\ApprovalWorkflowService;
use App\Services\ItemCodeGeneratorService;
use Tests\TestCase;

class CriticalInventoryTest extends TestCase
{
    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $codeGenerator = new ItemCodeGeneratorService();
        $approvalService = new ApprovalWorkflowService();
        $this->inventoryService = new InventoryService($approvalService, $codeGenerator);
    }

    /**
     * CRITICAL TEST SCENARIO:
     * Inbound: 100 units -> Stock = 100
     * Outbound: 30 units -> Stock = 70
     * Stock Opname: Physical = 68 -> Difference = -2
     * Adjustment: -2 units -> Final Stock = 68
     * Verify complete audit trail & history traceability.
     */
    public function test_critical_inventory_lifecycle_scenario(): void
    {
        // 0. Setup Base Master Data
        $role = Role::firstOrCreate(['role' => 'admin gudang']);
        $user = User::firstOrCreate(
            ['email' => 'operator_test@example.com'],
            ['name' => 'Operator Test', 'password' => bcrypt('password'), 'role_id' => $role->id, 'status' => 'ACTIVE']
        );
        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu LED']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);
        $supplier = Supplier::firstOrCreate(['nama_supplier' => 'PT Supplier Utama'], ['alamat' => 'Jl. Merdeka 1']);
        $customer = Customer::firstOrCreate(['nama_customer' => 'Dishub Lapangan'], ['alamat' => 'Jl. Dishub 2']);

        $barang = Barang::create([
            'kode_barang'   => 'PRPTY-TEST-001',
            'nama_barang'   => 'Lampu LED 120W Critical Test',
            'deskripsi'     => 'Testing item',
            'stok_minimum'  => 10,
            'jenis_id'      => $jenis->id,
            'satuan_id'     => $satuan->id,
            'user_id'       => $user->id,
            'stok'          => 0,
        ]);

        $this->assertEquals(0, $barang->stok, 'Initial stock must be 0');

        // STEP 1: Masukkan 100 unit (Inbound)
        $inboundData = [
            'tanggal_masuk'  => date('Y-m-d'),
            'barang_id'      => $barang->id,
            'jumlah_masuk'   => 100,
            'supplier_id'    => $supplier->id,
            'kode_transaksi' => 'BM-TEST-100',
            'status'         => 'POSTED',
        ];

        $barangMasuk = $this->inventoryService->recordInbound($inboundData, $user->id);
        $barang->refresh();

        $this->assertEquals(100, $barang->stok, 'Stock after inbound 100 must be 100');
        $this->assertDatabaseHas('stock_ledgers', [
            'transaction_number' => 'BM-TEST-100',
            'transaction_type'   => 'IN',
            'quantity'           => 100,
            'quantity_before'    => 0,
            'quantity_after'     => 100,
        ]);

        // STEP 2: Barang keluar 30 unit (Outbound)
        $outboundData = [
            'tanggal_keluar' => date('Y-m-d'),
            'barang_id'      => $barang->id,
            'jumlah_keluar'  => 30,
            'customer_id'    => $customer->id,
            'kode_transaksi' => 'BK-TEST-030',
            'status'         => 'POSTED',
        ];

        $barangKeluar = $this->inventoryService->recordOutbound($outboundData, $user->id);
        $barang->refresh();

        $this->assertEquals(70, $barang->stok, 'Stock after outbound 30 must be 70');
        $this->assertDatabaseHas('stock_ledgers', [
            'transaction_number' => 'BK-TEST-030',
            'transaction_type'   => 'OUT',
            'quantity'           => 30,
            'quantity_before'    => 100,
            'quantity_after'     => 70,
        ]);

        // STEP 3: Stock opname (Stok fisik = 68, Selisih = -2)
        $opnameData = [
            'barang_id'      => $barang->id,
            'stok_fisik'     => 68,
            'tanggal_opname' => date('Y-m-d'),
            'kode_opname'    => 'SO-TEST-068',
            'alasan_selisih' => 'Kerusakan fisik 2 unit di rak C',
        ];

        $opname = $this->inventoryService->recordStockOpname($opnameData, $user->id);

        $this->assertEquals(70, $opname->stok_sistem);
        $this->assertEquals(68, $opname->stok_fisik);
        $this->assertEquals(-2, $opname->selisih);

        // STEP 4: Adjustment via Approval Stock Opname
        $approvedOpname = $this->inventoryService->approveStockOpname($opname, $user->id);
        $barang->refresh();

        $this->assertEquals(68, $barang->stok, 'Final stock after adjustment must be 68');
        $this->assertDatabaseHas('stock_ledgers', [
            'transaction_number' => 'SO-TEST-068-ADJ',
            'transaction_type'   => 'ADJUSTMENT',
            'quantity'           => -2,
            'quantity_before'    => 70,
            'quantity_after'     => 68,
        ]);

        // STEP 5: Formula Calculation Verification
        $calculatedStock = $this->inventoryService->calculateStockFromLedger($barang->id);
        $this->assertEquals(68, $calculatedStock, 'Ledger formula (0 + 100 - 30 + (-2)) must equal 68');

        // STEP 6: Traceability Verification
        $historyLedgers = StockLedger::where('barang_id', $barang->id)
            ->orderBy('id', 'asc')
            ->get();

        $this->assertCount(3, $historyLedgers, 'Must have exactly 3 chronological ledger records');
        $this->assertEquals('IN', $historyLedgers[0]->transaction_type);
        $this->assertEquals('OUT', $historyLedgers[1]->transaction_type);
        $this->assertEquals('ADJUSTMENT', $historyLedgers[2]->transaction_type);
    }
}

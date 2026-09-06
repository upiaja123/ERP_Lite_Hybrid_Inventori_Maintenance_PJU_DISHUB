<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Satuan;
use App\Models\SyncLog;
use App\Models\User;
use App\Models\Role;
use App\Services\GoogleSheetsService;
use App\Services\InventoryService;
use App\Services\ApprovalWorkflowService;
use App\Services\ItemCodeGeneratorService;
use Tests\TestCase;

class SyncAndIntegrationTest extends TestCase
{
    protected GoogleSheetsService $sheetsService;
    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sheetsService = new GoogleSheetsService();
        $codeGenerator = new ItemCodeGeneratorService();
        $approvalService = new ApprovalWorkflowService();
        $this->inventoryService = new InventoryService($approvalService, $codeGenerator);
    }

    /**
     * Test Whitelisted Dataset Safety (No sensitive secrets exposed)
     */
    public function test_whitelisted_dataset_contains_no_sensitive_secrets(): void
    {
        $dataset = $this->sheetsService->buildWhitelistedDataset('stok_ringkasan');
        $this->assertIsArray($dataset);

        foreach ($dataset as $row) {
            $this->assertArrayHasKey('kode_barang', $row);
            $this->assertArrayHasKey('nama_barang', $row);
            $this->assertArrayHasKey('stok', $row);
            $this->assertArrayNotHasKey('password', $row);
            $this->assertArrayNotHasKey('remember_token', $row);
        }
    }

    /**
     * Test Primary ERP Transaction succeeds even if Google API is Down
     */
    public function test_primary_transaction_succeeds_when_google_api_is_down(): void
    {
        $role = Role::firstOrCreate(['role' => 'admin gudang']);
        $user = User::firstOrCreate(
            ['email' => 'operator_sync@example.com'],
            ['name' => 'Operator Sync', 'password' => bcrypt('password'), 'role_id' => $role->id, 'status' => 'ACTIVE']
        );
        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu LED']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);

        $barang = Barang::create([
            'kode_barang'  => 'PRPTY-SYNC-001',
            'nama_barang'  => 'Lampu Sync Test',
            'deskripsi'    => 'Test item',
            'stok_minimum' => 5,
            'jenis_id'     => $jenis->id,
            'satuan_id'    => $satuan->id,
            'user_id'      => $user->id,
            'stok'         => 0,
        ]);

        // 1. Primary ERP Transaction succeeds
        $inbound = $this->inventoryService->recordInbound([
            'tanggal_masuk'  => date('Y-m-d'),
            'barang_id'      => $barang->id,
            'jumlah_masuk'   => 50,
            'supplier_id'    => 1,
            'kode_transaksi' => 'BM-SYNC-001',
            'status'         => 'POSTED',
        ], $user->id);

        $this->assertEquals(50, $barang->fresh()->stok, 'Primary ERP Stock must be updated');

        // 2. Google API Sync fails due to Service Unavailable
        $syncLog = $this->sheetsService->syncDataset('stok_ringkasan', $user->id, true, 'GOOGLE_API_DOWN');

        $this->assertEquals('FAILED', $syncLog->status);
        $this->assertStringContainsString('503 Service Unavailable', $syncLog->error_message);
        $this->assertEquals(50, $barang->fresh()->stok, 'Primary ERP Stock remains 50 unaffected by API failure!');
    }

    /**
     * Test Idempotency (Skips duplicate sync traffic)
     */
    public function test_idempotency_skips_duplicate_sync(): void
    {
        $firstSync = $this->sheetsService->syncDataset('pju_status', null, false);
        $secondSync = $this->sheetsService->syncDataset('pju_status', null, false);

        $this->assertEquals('SUCCESS', $firstSync->status);
        $this->assertEquals('SUCCESS', $secondSync->status);
        $this->assertEquals('SKIP_DUPLICATE', $secondSync->payload_reference['idempotency'] ?? null);
    }

    /**
     * Test Conflict Detection (Saves conflict details and DOES NOT overwrite)
     */
    public function test_conflict_detection_does_not_overwrite(): void
    {
        $syncLog = $this->sheetsService->syncDataset('stok_ringkasan', null, true, 'CONFLICT');

        $this->assertEquals('CONFLICT', $syncLog->status);
        $this->assertNotNull($syncLog->conflict_details);
        $this->assertEquals('RESOLVE_MANUALLY_DO_NOT_OVERWRITE', $syncLog->conflict_details['status']);
    }
}

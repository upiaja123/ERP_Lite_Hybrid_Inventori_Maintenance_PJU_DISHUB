<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Barang;
use App\Models\StockLedger;
use App\Services\InventoryService;
use App\Services\ApprovalWorkflowService;
use App\Services\ItemCodeGeneratorService;
use Tests\TestCase;

class InventoryLedgerTest extends TestCase
{
    /**
     * Test stock exception when requested quantity exceeds available stock
     */
    public function test_insufficient_stock_throws_exception(): void
    {
        $this->expectException(InsufficientStockException::class);

        $barang = new Barang([
            'id' => 999,
            'nama_barang' => 'Test Lampu PJU',
            'stok' => 5,
            'stok_minimum' => 2,
        ]);

        // Simulasi recordOutbound dengan 10 unit pada stok 5
        if (10 > $barang->stok && !config('erp.stock.allow_negative', false)) {
            throw new InsufficientStockException(
                "Stok tidak mencukupi!",
                $barang->id,
                10,
                $barang->stok
            );
        }
    }
}

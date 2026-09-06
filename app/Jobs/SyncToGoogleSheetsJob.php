<?php

namespace App\Jobs;

use App\Services\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncToGoogleSheetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    protected string $datasetName;
    protected ?int $userId;

    public function __construct(string $datasetName = 'stok_ringkasan', ?int $userId = null)
    {
        $this->datasetName = $datasetName;
        $this->userId = $userId;
    }

    public function handle(GoogleSheetsService $sheetsService): void
    {
        $sheetsService->syncDataset($this->datasetName, $this->userId);
    }
}

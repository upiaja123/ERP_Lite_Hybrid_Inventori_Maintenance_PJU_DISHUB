<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Satuan;
use App\Services\ReportingService;
use Tests\TestCase;

class ReportingAndAuditTest extends TestCase
{
    /**
     * Test Role-based Dashboard Metrics Protection
     */
    public function test_role_based_dashboard_isolation(): void
    {
        $roleTeknisi = Role::firstOrCreate(['role' => 'teknisi']);
        $teknisiUser = User::firstOrCreate(
            ['email' => 'teknisi_dash@example.com'],
            ['name' => 'Teknisi Dash', 'password' => bcrypt('password'), 'role_id' => $roleTeknisi->id, 'status' => 'ACTIVE']
        );

        $this->actingAs($teknisiUser);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('role', 'teknisi');

        // Verify Pimpinan data is empty for Teknisi role
        $viewData = $response->viewData('pimpinanData');
        $this->assertEmpty($viewData, 'Pimpinan metrics must not be accessible to Teknisi role');
    }

    /**
     * Test ReportingService generates stock and inbound reports correctly
     */
    public function test_reporting_service_filters(): void
    {
        $reportingService = new ReportingService();

        $stockReport = $reportingService->getStockReport();
        $this->assertNotNull($stockReport);

        $inboundReport = $reportingService->getInboundReport(['start_date' => '2026-01-01']);
        $this->assertNotNull($inboundReport);
    }

    /**
     * Test Security: Audit Log deletion attempt is denied (Append-Only)
     */
    public function test_audit_log_deletion_is_denied(): void
    {
        $roleAdmin = Role::firstOrCreate(['role' => 'admin gudang']);
        $adminUser = User::firstOrCreate(
            ['email' => 'admin_audit@example.com'],
            ['name' => 'Admin Audit', 'password' => bcrypt('password'), 'role_id' => $roleAdmin->id, 'status' => 'ACTIVE']
        );

        $this->actingAs($adminUser);

        $response = $this->delete('/aktivitas-user/1');
        $response->assertStatus(403);
    }
}

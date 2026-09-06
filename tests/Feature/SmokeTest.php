<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    /**
     * Smoke test — Login page is accessible.
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Smoke test — Root application route redirects to login or dashboard.
     */
    public function test_root_route_is_accessible(): void
    {
        $response = $this->get('/');

        // Either redirects unauthenticated user to login or serves dashboard
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }

    /**
     * Smoke test — All web routes render successfully (HTTP 200) for Super Admin.
     */
    public function test_all_web_module_views_render_successfully(): void
    {
        $superadminRole = \App\Models\Role::firstOrCreate(['role' => 'superadmin']);
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'smoke_superadmin@dishub.test'],
            ['name' => 'Super Admin Smoke', 'password' => bcrypt('password'), 'role_id' => $superadminRole->id, 'status' => 'ACTIVE']
        );

        $routes = [
            '/dashboard',
            '/barang',
            '/jenis-barang',
            '/satuan-barang',
            '/merk',
            '/watt',
            '/tim',
            '/lokasi-pju',
            '/kecamatan',
            '/kelurahan',
            '/supplier',
            '/customer',
            '/barang-masuk',
            '/barang-keluar',
            '/stock-opname',
            '/stock-mutasi',
            '/pju-asset',
            '/pemasangan-pju',
            '/pencopotan-pju',
            '/maintenance-pju',
            '/garansi-pju',
            '/retur-vendor',
            '/laporan-stok',
            '/laporan-barang-masuk',
            '/laporan-barang-keluar',
            '/data-pengguna',
            '/hak-akses',
            '/aktivitas-user',
            '/import',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $this->assertEquals(
                200,
                $response->getStatusCode(),
                "Route [{$route}] failed to render HTTP 200. Got: " . $response->getStatusCode()
            );
        }
    }
}

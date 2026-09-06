<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Satuan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndApiTest extends TestCase
{
    /**
     * Test Unauthorized Route Access Rejection
     */
    public function test_unauthenticated_user_redirected_from_protected_routes(): void
    {
        $response = $this->get('/barang');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test Privilege Escalation Rejection (Ordinary User cannot access User Management)
     */
    public function test_ordinary_user_cannot_access_user_management(): void
    {
        $roleViewer = Role::firstOrCreate(['role' => 'viewer']);
        $viewer = User::firstOrCreate(
            ['email' => 'viewer_sec@example.com'],
            ['name' => 'Viewer Sec', 'password' => bcrypt('password'), 'role_id' => $roleViewer->id, 'status' => 'ACTIVE']
        );

        $this->actingAs($viewer);

        $response = $this->get('/data-pengguna');
        $response->assertStatus(403);
    }

    /**
     * Test Malicious File Upload Rejection (Rejecting executable/script files)
     */
    public function test_malicious_file_upload_rejected(): void
    {
        $roleAdmin = Role::firstOrCreate(['role' => 'admin gudang']);
        $admin = User::firstOrCreate(
            ['email' => 'admin_upload@example.com'],
            ['name' => 'Admin Upload', 'password' => bcrypt('password'), 'role_id' => $roleAdmin->id, 'status' => 'ACTIVE']
        );

        $this->actingAs($admin);

        $fakeScript = UploadedFile::fake()->create('exploit.php', 100, 'application/x-php');

        $response = $this->postJson('/barang', [
            'nama_barang'  => 'Test Upload Security',
            'deskripsi'    => 'Test',
            'gambar'       => [$fakeScript],
            'stok_minimum' => 5,
            'jenis_id'     => 1,
            'satuan_id'    => 1,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test REST API v1 Response Security (No Secret Exposure)
     */
    public function test_api_v1_exposes_no_sensitive_passwords_or_secrets(): void
    {
        $response = $this->getJson('/api/v1/barang');

        $response->assertStatus(200);
        $response->assertHeader('X-RateLimit-Limit', 60);

        $content = $response->getContent();
        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('remember_token', $content);
    }
}

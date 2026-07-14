<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Inventori;
use App\Models\Tuntutan;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FFGroceryTrackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cipta peranan (roles) asas
        Role::create(['name' => 'Superadmin']);
        Role::create(['name' => 'Stocker']);
        Role::create(['name' => 'Tracker']);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Log Masuk');
    }

    public function test_superadmin_can_access_dashboard_and_logs(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('Superadmin');

        $response = $this->actingAs($superadmin)->get('/inventori');
        $response->assertStatus(200);

        $responseLogs = $this->actingAs($superadmin)->get('/log-aktiviti');
        $responseLogs->assertStatus(200);
    }

    public function test_stocker_cannot_access_logs_but_can_access_tuntutan(): void
    {
        $stocker = User::factory()->create();
        $stocker->assignRole('Stocker');

        $responseLogs = $this->actingAs($stocker)->get('/log-aktiviti');
        $responseLogs->assertStatus(403);

        $responseClaims = $this->actingAs($stocker)->get('/tuntutan');
        $responseClaims->assertStatus(200);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/inventori');
        $response->assertRedirect('/login');
    }

    public function test_superadmin_can_create_another_superadmin(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('Superadmin');

        $response = $this->actingAs($superadmin)->post('/pengguna', [
            'name' => 'New Superadmin',
            'email' => 'newadmin@email.com',
            'role' => 'Superadmin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/pengguna');
        $this->assertDatabaseHas('users', ['email' => 'newadmin@email.com']);
        $newAdmin = User::where('email', 'newadmin@email.com')->first();
        $this->assertTrue($newAdmin->hasRole('Superadmin'));
    }

    public function test_tracker_can_create_and_delete_items(): void
    {
        $tracker = User::factory()->create();
        $tracker->assignRole('Tracker');

        // Test create item
        $responseCreate = $this->actingAs($tracker)->post('/inventori', [
            'nama_item' => 'Tracker Milk',
            'kategori' => 'Tenusu',
            'jumlah_keseluruhan' => 5,
            'jumlah_belum_dibuka' => 5,
            'peratus_baki' => 100,
            'had_ambang' => 2,
        ]);
        $responseCreate->assertRedirect('/inventori');
        $this->assertDatabaseHas('inventori', ['nama_item' => 'Tracker Milk']);

        $item = Inventori::where('nama_item', 'Tracker Milk')->first();

        // Test delete item
        $responseDelete = $this->actingAs($tracker)->delete('/inventori/' . $item->id);
        $responseDelete->assertRedirect('/inventori');
        $this->assertDatabaseMissing('inventori', ['id' => $item->id]);
    }

    public function test_tracker_cannot_access_tuntutan(): void
    {
        $tracker = User::factory()->create();
        $tracker->assignRole('Tracker');

        $response = $this->actingAs($tracker)->get('/tuntutan');
        $response->assertStatus(403);
    }
}

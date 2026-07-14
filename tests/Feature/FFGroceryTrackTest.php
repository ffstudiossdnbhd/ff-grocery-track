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
}

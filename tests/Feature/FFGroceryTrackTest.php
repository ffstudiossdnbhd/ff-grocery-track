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

    public function test_stocker_can_submit_weekly_lunch_claim(): void
    {
        $stocker = User::factory()->create();
        $stocker->assignRole('Stocker');

        $response = $this->actingAs($stocker)->post('/tuntutan', [
            'tag' => 'Lunch',
            'week' => '2026-W29',
            'lunch_dates' => [
                '2026-07-13',
                '2026-07-14',
                '2026-07-15',
                '2026-07-16',
                '2026-07-17',
                '2026-07-18',
                '2026-07-19',
            ],
            'lunch_butirans' => [
                'Lunch Mon',
                'Lunch Tue',
                'Lunch Claim',
                'Lunch Claim',
                'Lunch Claim',
                'Lunch Claim',
                'Lunch Claim',
            ],
            'lunch_pax' => [
                10,
                12,
                0,
                0,
                0,
                0,
                0,
            ],
            'lunch_hargas' => [
                12.50,
                10.00, // Different price on Tuesday
                0,
                0,
                0,
                0,
                0,
            ],
        ]);

        $response->assertRedirect('/tuntutan');

        // Check if database contains the claims for Mon and Tue, but not other days
        $this->assertDatabaseHas('tuntutan', [
            'user_id' => $stocker->id,
            'tag' => 'Lunch',
            'nilai_tuntutan' => 125.00,
            'tarikh_beli' => '2026-07-13 00:00:00',
            'minggu_tuntutan' => '2026-W29',
            'nama_item' => 'Lunch Mon (10 pax @ RM 12.50/pax)',
        ]);

        $this->assertDatabaseHas('tuntutan', [
            'user_id' => $stocker->id,
            'tag' => 'Lunch',
            'nilai_tuntutan' => 120.00, // 12 * 10.00
            'tarikh_beli' => '2026-07-14 00:00:00',
            'minggu_tuntutan' => '2026-W29',
            'nama_item' => 'Lunch Tue (12 pax @ RM 10.00/pax)',
        ]);

        // There should be exactly 2 claims created
        $this->assertEquals(2, Tuntutan::where('user_id', $stocker->id)->count());
    }

    public function test_stocker_cannot_submit_invalid_lunch_claims(): void
    {
        $stocker = User::factory()->create();
        $stocker->assignRole('Stocker');

        // 1. Submit with zero total pax
        $response = $this->actingAs($stocker)->post('/tuntutan', [
            'tag' => 'Lunch',
            'week' => '2026-W29',
            'lunch_dates' => [
                '2026-07-13', '2026-07-14', '2026-07-15', '2026-07-16', '2026-07-17', '2026-07-18', '2026-07-19'
            ],
            'lunch_butirans' => [
                'Lunch Mon', 'Lunch Tue', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim'
            ],
            'lunch_pax' => [
                0, 0, 0, 0, 0, 0, 0
            ],
            'lunch_hargas' => [
                12.50, 12.50, 12.50, 12.50, 12.50, 12.50, 12.50
            ],
        ]);
        $response->assertSessionHasErrors(['lunch_pax']);

        // 2. Submit with future date
        $futureDate = \Carbon\Carbon::now()->addYear()->format('Y-m-d');
        $response2 = $this->actingAs($stocker)->post('/tuntutan', [
            'tag' => 'Lunch',
            'week' => '2026-W29',
            'lunch_dates' => [
                $futureDate, '2026-07-14', '2026-07-15', '2026-07-16', '2026-07-17', '2026-07-18', '2026-07-19'
            ],
            'lunch_butirans' => [
                'Lunch Mon', 'Lunch Tue', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim'
            ],
            'lunch_pax' => [
                5, 0, 0, 0, 0, 0, 0
            ],
            'lunch_hargas' => [
                12.50, 12.50, 12.50, 12.50, 12.50, 12.50, 12.50
            ],
        ]);
        $response2->assertSessionHasErrors(['lunch_dates']);

        // 3. Submit with missing butiran for claimed day
        $response3 = $this->actingAs($stocker)->post('/tuntutan', [
            'tag' => 'Lunch',
            'week' => '2026-W29',
            'lunch_dates' => [
                '2026-07-13', '2026-07-14', '2026-07-15', '2026-07-16', '2026-07-17', '2026-07-18', '2026-07-19'
            ],
            'lunch_butirans' => [
                '', 'Lunch Tue', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim'
            ],
            'lunch_pax' => [
                5, 0, 0, 0, 0, 0, 0
            ],
            'lunch_hargas' => [
                12.50, 12.50, 12.50, 12.50, 12.50, 12.50, 12.50
            ],
        ]);
        $response3->assertSessionHasErrors(['lunch_butirans']);

        // 4. Submit with missing/zero price for claimed day
        $response4 = $this->actingAs($stocker)->post('/tuntutan', [
            'tag' => 'Lunch',
            'week' => '2026-W29',
            'lunch_dates' => [
                '2026-07-13', '2026-07-14', '2026-07-15', '2026-07-16', '2026-07-17', '2026-07-18', '2026-07-19'
            ],
            'lunch_butirans' => [
                'Lunch Mon', 'Lunch Tue', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim', 'Lunch Claim'
            ],
            'lunch_pax' => [
                5, 0, 0, 0, 0, 0, 0
            ],
            'lunch_hargas' => [
                0, 0, 0, 0, 0, 0, 0
            ],
        ]);
        $response4->assertSessionHasErrors(['lunch_hargas']);
    }
}

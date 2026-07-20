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

    public function test_stocker_can_submit_general_and_food_claim_with_attachment(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $stocker = User::factory()->create();
        $stocker->assignRole('Stocker');

        $file = \Illuminate\Http\UploadedFile::fake()->create('receipt.pdf', 100); // 100kb PDF

        // 1. Test General claim
        $responseGeneral = $this->actingAs($stocker)->post('/tuntutan', [
            'tag' => 'General',
            'nama_item' => 'Barang Pejabat A4 Paper',
            'nilai_tuntutan' => 45.90,
            'tarikh_beli' => '2026-07-20',
            'attachment' => $file,
        ]);

        $responseGeneral->assertRedirect('/tuntutan');
        $this->assertDatabaseHas('tuntutan', [
            'user_id' => $stocker->id,
            'tag' => 'General',
            'nilai_tuntutan' => 45.90,
            'nama_item' => 'Barang Pejabat A4 Paper',
        ]);

        $claimGeneral = Tuntutan::where('tag', 'General')->first();
        $this->assertNotNull($claimGeneral->attachment);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($claimGeneral->attachment);

        // 2. Test Food claim
        $file2 = \Illuminate\Http\UploadedFile::fake()->create('food_receipt.png', 200); // 200kb Image
        $responseFood = $this->actingAs($stocker)->post('/tuntutan', [
            'tag' => 'Food',
            'nama_item' => 'Katering Makan Malam',
            'nilai_tuntutan' => 150.00,
            'tarikh_beli' => '2026-07-20',
            'attachment' => $file2,
        ]);

        $responseFood->assertRedirect('/tuntutan');
        $this->assertDatabaseHas('tuntutan', [
            'user_id' => $stocker->id,
            'tag' => 'Food',
            'nilai_tuntutan' => 150.00,
            'nama_item' => 'Katering Makan Malam',
        ]);

        $claimFood = Tuntutan::where('tag', 'Food')->first();
        $this->assertNotNull($claimFood->attachment);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($claimFood->attachment);
    }

    public function test_inventori_crud_with_brand_type_capacity(): void
    {
        $tracker = User::factory()->create();
        $tracker->assignRole('Tracker');

        // Test create
        $responseCreate = $this->actingAs($tracker)->post('/inventori', [
            'nama_item' => 'Susu Tepung',
            'kategori' => 'Tenusu',
            'jenama' => 'Fernleaf',
            'jenis' => 'Serbuk',
            'capacity' => '1.8kg',
            'jumlah_keseluruhan' => 10,
            'jumlah_belum_dibuka' => 8,
            'peratus_baki' => 80,
            'had_ambang' => 3,
        ]);

        $responseCreate->assertRedirect('/inventori');
        $this->assertDatabaseHas('inventori', [
            'nama_item' => 'Susu Tepung',
            'jenama' => 'Fernleaf',
            'jenis' => 'Serbuk',
            'capacity' => '1.8kg',
        ]);

        $item = Inventori::first();

        // Test update
        $responseUpdate = $this->actingAs($tracker)->put('/inventori/' . $item->id, [
            'nama_item' => 'Susu Tepung Pek Baru',
            'kategori' => 'Tenusu',
            'jenama' => 'Fernleaf Gold',
            'jenis' => 'Serbuk',
            'capacity' => '2kg',
            'jumlah_keseluruhan' => 12,
            'jumlah_belum_dibuka' => 9,
            'peratus_baki' => 75,
            'had_ambang' => 4,
        ]);

        $responseUpdate->assertRedirect('/inventori');
        $this->assertDatabaseHas('inventori', [
            'id' => $item->id,
            'nama_item' => 'Susu Tepung Pek Baru',
            'jenama' => 'Fernleaf Gold',
            'jenis' => 'Serbuk',
            'capacity' => '2kg',
        ]);
    }
}

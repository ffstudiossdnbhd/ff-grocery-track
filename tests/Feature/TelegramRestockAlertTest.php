<?php

namespace Tests\Feature;

use App\Models\Inventori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TelegramRestockAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup default configuration
        Config::set('services.telegram.bot_token', 'test_bot_token');
        Config::set('services.telegram.chat_id', 'test_chat_id');
    }

    public function test_command_sends_telegram_message_when_items_are_below_threshold(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true], 200),
        ]);

        // Item 1: jumlah_keseluruhan <= had_ambang (1 <= 2)
        Inventori::create([
            'nama_item' => 'Apple Milk',
            'kategori' => 'Tenusu',
            'jumlah_keseluruhan' => 1,
            'jumlah_belum_dibuka' => 1,
            'peratus_baki' => 50,
            'had_ambang' => 2,
        ]);

        // Item 2: jumlah_belum_dibuka <= had_ambang (1 <= 3)
        Inventori::create([
            'nama_item' => 'Gardenia Bread',
            'kategori' => 'Roti',
            'jumlah_keseluruhan' => 5,
            'jumlah_belum_dibuka' => 1,
            'peratus_baki' => 80,
            'had_ambang' => 3,
        ]);

        // Item 3: Above threshold (5 > 2 and 5 > 2)
        Inventori::create([
            'nama_item' => 'Coca Cola',
            'kategori' => 'Minuman',
            'jumlah_keseluruhan' => 5,
            'jumlah_belum_dibuka' => 5,
            'peratus_baki' => 100,
            'had_ambang' => 2,
        ]);

        $this->artisan('telegram:send-restock-alert')
            ->assertExitCode(0);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'sendMessage') &&
                $request['chat_id'] === 'test_chat_id' &&
                str_contains($request['text'], 'FFGrocery Restock List ⚠️') &&
                str_contains($request['text'], '• Apple Milk') &&
                str_contains($request['text'], '• Gardenia Bread') &&
                !str_contains($request['text'], '• Coca Cola');
        });
    }

    public function test_command_does_not_send_telegram_message_when_no_items_below_threshold(): void
    {
        Http::fake();

        // Item: Above threshold (5 > 2 and 5 > 2)
        Inventori::create([
            'nama_item' => 'Coca Cola',
            'kategori' => 'Minuman',
            'jumlah_keseluruhan' => 5,
            'jumlah_belum_dibuka' => 5,
            'peratus_baki' => 100,
            'had_ambang' => 2,
        ]);

        $this->artisan('telegram:send-restock-alert')
            ->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_command_fails_when_telegram_config_is_missing(): void
    {
        Config::set('services.telegram.bot_token', '');
        Config::set('services.telegram.chat_id', '');

        Http::fake();

        $this->artisan('telegram:send-restock-alert')
            ->assertExitCode(1);

        Http::assertNothingSent();
    }
}

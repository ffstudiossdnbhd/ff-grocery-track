<?php

namespace App\Console\Commands;

use App\Models\Inventori;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTelegramRestockAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send-restock-alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a list of all items reaching their restock threshold to Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (empty($token) || empty($chatId)) {
            $this->warn('Telegram bot token or chat ID is not configured.');
            Log::warning('Telegram bot token or chat ID is not configured.');
            return self::FAILURE;
        }

        // Fetch items reaching restock threshold
        $items = Inventori::where(function ($query) {
            $query->whereRaw('jumlah_keseluruhan <= had_ambang')
                  ->orWhereRaw('jumlah_belum_dibuka <= had_ambang');
        })->get();

        if ($items->isEmpty()) {
            $this->info('No items have reached the restock threshold.');
            return self::SUCCESS;
        }

        $itemList = $items->map(fn($item) => "• {$item->nama_item}")->implode("\n");

        $message = "FFGrocery Restock List ⚠️\n\n"
                 . "Sila beli stok baharu untuk item berikut:\n"
                 . "{$itemList}\n\n"
                 . "Sila semak sistem untuk maklumat lanjut.";

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->failed()) {
                $this->error('Telegram API error: ' . $response->body());
                Log::error('Telegram API error response: ' . $response->body());
                return self::FAILURE;
            }

            $this->info('Telegram restock alert sent successfully.');
            Log::info("Telegram restock alert list sent successfully for " . $items->count() . " items.");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to send Telegram notification: ' . $e->getMessage());
            Log::error('Failed to send Telegram notification: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

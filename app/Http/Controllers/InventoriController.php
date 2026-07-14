<?php

namespace App\Http\Controllers;

use App\Models\Inventori;
use App\Models\LogAktiviti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoriController extends Controller
{
    /**
     * Paparkan senarai inventori.
     */
    public function index(Request $request)
    {
        $query = Inventori::query();

        // Carian nama item
        if ($request->filled('carian')) {
            $query->where('nama_item', 'like', '%' . $request->carian . '%');
        }

        // Penapisan kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $items = $query->orderBy('nama_item', 'asc')->paginate(15);
        $kategoriSenarai = Inventori::distinct()->pluck('kategori');

        return view('inventori.index', compact('items', 'kategoriSenarai'));
    }

    /**
     * Tunjukkan senarai barang perlu direstok (habis stok atau bawah ambang).
     */
    public function restockList()
    {
        // Barang yang habis stok sepenuhnya
        $habisStok = Inventori::where('jumlah_keseluruhan', 0)->get();

        // Barang yang di bawah had ambang
        $bawahAmbang = Inventori::where('jumlah_keseluruhan', '>', 0)
            ->where(function($query) {
                $query->whereRaw('jumlah_keseluruhan <= had_ambang')
                      ->orWhereRaw('jumlah_belum_dibuka <= had_ambang');
            })
            ->get();

        return view('inventori.restok', compact('habisStok', 'bawahAmbang'));
    }

    /**
     * Tunjukkan borang tambah barang baharu.
     */
    public function create()
    {
        // Hanya Superadmin, Stocker dan Tracker boleh tambah item baharu
        if (!Auth::user()->hasAnyRole(['Superadmin', 'Stocker', 'Tracker'])) {
            abort(403, 'Anda tidak mempunyai kebenaran untuk menambah item.');
        }
        return view('inventori.create');
    }

    /**
     * Simpan barang baharu.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['Superadmin', 'Stocker', 'Tracker'])) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_item' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'jumlah_keseluruhan' => 'required|integer|min:0',
            'jumlah_belum_dibuka' => 'required|integer|min:0|lte:jumlah_keseluruhan',
            'peratus_baki' => 'required|integer|between:0,100',
            'tarikh_luput' => 'nullable|date',
            'jejak_luput' => 'nullable|boolean',
            'had_ambang' => 'required|integer|min:0',
        ], [
            'nama_item.required' => 'Sila masukkan nama item.',
            'kategori.required' => 'Sila masukkan kategori.',
            'jumlah_keseluruhan.required' => 'Sila masukkan jumlah keseluruhan.',
            'jumlah_belum_dibuka.required' => 'Sila masukkan jumlah belum dibuka.',
            'jumlah_belum_dibuka.lte' => 'Jumlah belum dibuka tidak boleh melebihi jumlah keseluruhan.',
            'peratus_baki.between' => 'Peratus baki mestilah di antara 0 hingga 100.',
            'had_ambang.required' => 'Sila tetapkan had ambang restok.',
        ]);

        // Tetapkan nilai laluan untuk jejak_luput (jika tiada dalam input)
        $validated['jejak_luput'] = $request->has('jejak_luput');
        $validated['dicipta_oleh'] = Auth::id();
        $validated['dikemaskini_oleh'] = Auth::id();

        $item = Inventori::create($validated);

        // Log Aktiviti
        LogAktiviti::create([
            'user_id' => Auth::id(),
            'aktiviti' => "Menambah item baharu: {$item->nama_item}.",
            'item_id' => $item->id,
            'data_baru' => $item->toArray(),
        ]);

        return redirect()->route('inventori.index')->with('success', 'Barang berjaya ditambahkan.');
    }

    /**
     * Paparkan butiran barang.
     */
    public function show(Inventori $inventori)
    {
        return view('inventori.show', compact('inventori'));
    }

    /**
     * Tunjukkan borang kemaskini.
     */
    public function edit(Inventori $inventori)
    {
        return view('inventori.edit', compact('inventori'));
    }

    /**
     * Kemaskini maklumat barang.
     */
    public function update(Request $request, Inventori $inventori)
    {
        $validated = $request->validate([
            'nama_item' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'jumlah_keseluruhan' => 'required|integer|min:0',
            'jumlah_belum_dibuka' => 'required|integer|min:0|lte:jumlah_keseluruhan',
            'peratus_baki' => 'required|integer|between:0,100',
            'tarikh_luput' => 'nullable|date',
            'jejak_luput' => 'nullable|boolean',
            'had_ambang' => 'required|integer|min:0',
        ], [
            'nama_item.required' => 'Sila masukkan nama item.',
            'kategori.required' => 'Sila masukkan kategori.',
            'jumlah_keseluruhan.required' => 'Sila masukkan jumlah keseluruhan.',
            'jumlah_belum_dibuka.required' => 'Sila masukkan jumlah belum dibuka.',
            'jumlah_belum_dibuka.lte' => 'Jumlah belum dibuka tidak boleh melebihi jumlah keseluruhan.',
            'peratus_baki.between' => 'Peratus baki mestilah di antara 0 hingga 100.',
            'had_ambang.required' => 'Sila tetapkan had ambang restok.',
        ]);

        $validated['jejak_luput'] = $request->has('jejak_luput');
        $validated['dikemaskini_oleh'] = Auth::id();

        $oldData = $inventori->toArray();
        $oldBelumDibuka = $inventori->jumlah_belum_dibuka;

        $inventori->update($validated);

        // Semak trigger Telegram
        // Jika baki belum dibuka mencapai 0 dari nilai > 0, manakala baki keseluruhan masih > 0
        if ($inventori->jumlah_belum_dibuka === 0 && $oldBelumDibuka > 0 && $inventori->jumlah_keseluruhan > 0) {
            $this->notifyTelegram($inventori);

            LogAktiviti::create([
                'user_id' => Auth::id(),
                'aktiviti' => "Membuka unit terakhir belum dibuka untuk item: {$inventori->nama_item}. Amaran Telegram telah dihantar.",
                'item_id' => $inventori->id,
                'data_lama' => $oldData,
                'data_baru' => $inventori->toArray(),
            ]);
        } else {
            // Log kemaskini stok biasa
            LogAktiviti::create([
                'user_id' => Auth::id(),
                'aktiviti' => "Mengemaskini maklumat stok bagi item: {$inventori->nama_item}.",
                'item_id' => $inventori->id,
                'data_lama' => $oldData,
                'data_baru' => $inventori->toArray(),
            ]);
        }

        return redirect()->route('inventori.index')->with('success', 'Barang berjaya dikemaskini.');
    }

    /**
     * Kemaskini pantas tahap stok (digunakan pada dashboard / index).
     */
    public function adjustStock(Request $request, Inventori $inventori)
    {
        $request->validate([
            'jumlah_keseluruhan' => 'required|integer|min:0',
            'jumlah_belum_dibuka' => 'required|integer|min:0|lte:jumlah_keseluruhan',
            'peratus_baki' => 'required|integer|between:0,100',
        ]);

        $oldData = $inventori->toArray();
        $oldBelumDibuka = $inventori->jumlah_belum_dibuka;

        $inventori->update([
            'jumlah_keseluruhan' => $request->jumlah_keseluruhan,
            'jumlah_belum_dibuka' => $request->jumlah_belum_dibuka,
            'peratus_baki' => $request->peratus_baki,
            'dikemaskini_oleh' => Auth::id(),
        ]);

        // Semak trigger Telegram
        if ($inventori->jumlah_belum_dibuka === 0 && $oldBelumDibuka > 0 && $inventori->jumlah_keseluruhan > 0) {
            $this->notifyTelegram($inventori);

            LogAktiviti::create([
                'user_id' => Auth::id(),
                'aktiviti' => "Membuka unit terakhir belum dibuka untuk item: {$inventori->nama_item}. Amaran Telegram telah dihantar.",
                'item_id' => $inventori->id,
                'data_lama' => $oldData,
                'data_baru' => $inventori->toArray(),
            ]);
        } else {
            LogAktiviti::create([
                'user_id' => Auth::id(),
                'aktiviti' => "Melaraskan kuantiti/peratus baki bagi item: {$inventori->nama_item}.",
                'item_id' => $inventori->id,
                'data_lama' => $oldData,
                'data_baru' => $inventori->toArray(),
            ]);
        }

        return back()->with('success', 'Tahap stok berjaya diselaraskan.');
    }

    /**
     * Padam barang.
     */
    public function destroy(Inventori $inventori)
    {
        // Hanya Superadmin, Stocker dan Tracker boleh padam item
        if (!Auth::user()->hasAnyRole(['Superadmin', 'Stocker', 'Tracker'])) {
            abort(403, 'Anda tidak mempunyai kebenaran untuk memadam item.');
        }

        $oldData = $inventori->toArray();
        $itemName = $inventori->nama_item;

        $inventori->delete();

        // Log Aktiviti
        LogAktiviti::create([
            'user_id' => Auth::id(),
            'aktiviti' => "Memadam item inventori: {$itemName}.",
            'item_id' => null,
            'data_lama' => $oldData,
        ]);

        return redirect()->route('inventori.index')->with('success', 'Barang berjaya dipadam.');
    }

    /**
     * Hantar notifikasi Telegram ke group chat.
     */
    private function notifyTelegram(Inventori $item)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (empty($token) || empty($chatId)) {
            Log::warning('Token bot Telegram atau ID Sembang tidak dikonfigurasikan dalam fail .env');
            return;
        }

        try {
            $message = "🚨 *AMARAN RESTOK FFGroceryTrack* 🚨\n\n"
                     . "Unit belum dibuka terakhir bagi item *{$item->nama_item}* telah dibuka!\n"
                     . "Sila beli stok baharu untuk item ini secepat mungkin.\n\n"
                     . "• *Kategori:* {$item->kategori}\n"
                     . "• *Jumlah Baki Unit Keseluruhan:* {$item->jumlah_keseluruhan}\n"
                     . "• *Had Ambang Restok:* {$item->had_ambang}\n\n"
                     . "Sila lawati sistem untuk maklumat lanjut.";

            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->failed()) {
                Log::error('Telegram API error response: ' . $response->body());
            } else {
                Log::info("Telegram alert sent for item: {$item->nama_item}");
            }
        } catch (\Exception $e) {
            Log::error('Gagal menghantar notifikasi Telegram: ' . $e->getMessage());
        }
    }
}

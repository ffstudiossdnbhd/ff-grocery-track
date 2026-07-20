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
            'jenama' => 'nullable|string|max:255',
            'jenis' => 'nullable|string|max:255',
            'capacity' => 'nullable|string|max:255',
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
            'jenama' => 'nullable|string|max:255',
            'jenis' => 'nullable|string|max:255',
            'capacity' => 'nullable|string|max:255',
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

        if ($inventori->jumlah_belum_dibuka === 0 && $oldBelumDibuka > 0 && $inventori->jumlah_keseluruhan > 0) {
            LogAktiviti::create([
                'user_id' => Auth::id(),
                'aktiviti' => "Membuka unit terakhir belum dibuka untuk item: {$inventori->nama_item}.",
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

        if ($inventori->jumlah_belum_dibuka === 0 && $oldBelumDibuka > 0 && $inventori->jumlah_keseluruhan > 0) {
            LogAktiviti::create([
                'user_id' => Auth::id(),
                'aktiviti' => "Membuka unit terakhir belum dibuka untuk item: {$inventori->nama_item}.",
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

}

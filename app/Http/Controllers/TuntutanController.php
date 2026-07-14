<?php

namespace App\Http\Controllers;

use App\Models\Tuntutan;
use App\Models\LogAktiviti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TuntutanController extends Controller
{
    /**
     * Paparkan senarai tuntutan berkumpulan mengikut minggu.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Tuntutan::query();

        // Stocker hanya boleh melihat tuntutan sendiri.
        // Superadmin & Tracker boleh melihat semua tuntutan.
        if ($user->hasRole('Stocker')) {
            $query->where('user_id', $user->id);
        }

        // Dapatkan semua tuntutan dan urutkan mengikut tarikh beli terkini
        $claims = $query->orderBy('tarikh_beli', 'desc')->get();

        // Kumpulkan mengikut minggu_tuntutan
        $claimsGrouped = $claims->groupBy('minggu_tuntutan');

        return view('tuntutan.index', compact('claimsGrouped'));
    }

    /**
     * Tunjukkan borang tambah tuntutan.
     */
    public function create()
    {
        // Hanya Stocker sahaja dibenarkan membuat tuntutan
        if (!Auth::user()->hasRole('Stocker')) {
            abort(403, 'Hanya Stocker sahaja dibenarkan membuat tuntutan.');
        }

        return view('tuntutan.create');
    }

    /**
     * Simpan tuntutan baharu.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('Stocker')) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_item' => 'required|string|max:255',
            'nilai_tuntutan' => 'required|numeric|min:0.01',
            'tarikh_beli' => 'required|date|before_or_equal:today',
        ], [
            'nama_item.required' => 'Sila masukkan nama item yang dibeli.',
            'nilai_tuntutan.required' => 'Sila masukkan nilai tuntutan.',
            'nilai_tuntutan.numeric' => 'Nilai tuntutan mestilah dalam bentuk nombor.',
            'nilai_tuntutan.min' => 'Nilai tuntutan mestilah lebih daripada RM0.00.',
            'tarikh_beli.required' => 'Sila masukkan tarikh pembelian.',
            'tarikh_beli.before_or_equal' => 'Tarikh pembelian tidak boleh pada masa hadapan.',
        ]);

        // Hitung minggu tuntutan (Format: YYYY-Www)
        $date = Carbon::parse($request->tarikh_beli);
        // Dapatkan tahun ISO-8601 dan nombor minggu
        $year = $date->format('o'); 
        $week = sprintf('%02d', $date->weekOfYear);
        $validated['minggu_tuntutan'] = "{$year}-W{$week}";
        $validated['user_id'] = Auth::id();
        $validated['status'] = 'Dalam Proses'; // Default status

        $claim = Tuntutan::create($validated);

        // Log Aktiviti
        LogAktiviti::create([
            'user_id' => Auth::id(),
            'aktiviti' => "Membuat tuntutan baharu bagi pembelian: {$claim->nama_item} bernilai RM{$claim->nilai_tuntutan}.",
            'item_id' => null,
            'data_baru' => $claim->toArray(),
        ]);

        return redirect()->route('tuntutan.index')->with('success', 'Tuntutan berjaya dihantar.');
    }

    /**
     * Kemaskini status tuntutan (Hanya untuk Superadmin).
     */
    public function updateStatus(Request $request, Tuntutan $tuntutan)
    {
        if (!Auth::user()->hasRole('Superadmin')) {
            abort(403, 'Hanya Superadmin sahaja dibenarkan menguruskan status tuntutan.');
        }

        $request->validate([
            'status' => 'required|in:Selesai,Ditolak,Dalam Proses',
        ]);

        $oldData = $tuntutan->toArray();
        $tuntutan->update([
            'status' => $request->status,
        ]);

        // Log Aktiviti
        LogAktiviti::create([
            'user_id' => Auth::id(),
            'aktiviti' => "Mengemaskini status tuntutan ID {$tuntutan->id} ({$tuntutan->nama_item}) kepada '{$tuntutan->status}'.",
            'item_id' => null,
            'data_lama' => $oldData,
            'data_baru' => $tuntutan->toArray(),
        ]);

        return back()->with('success', 'Status tuntutan berjaya dikemaskini.');
    }
}

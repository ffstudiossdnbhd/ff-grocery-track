<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LogAktiviti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PenggunaController extends Controller
{


    /**
     * Paparkan senarai akaun Stocker dan Tracker.
     */
    public function index()
    {
        $users = User::whereDoesntHave('roles', function($q) {
            $q->where('name', 'Superadmin');
        })->orderBy('name', 'asc')->paginate(15);

        return view('pengguna.index', compact('users'));
    }

    /**
     * Tunjukkan borang tambah akaun baharu.
     */
    public function create()
    {
        $roles = Role::where('name', '!=', 'Superadmin')->get();
        return view('pengguna.create', compact('roles'));
    }

    /**
     * Simpan akaun baharu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ], [
            'name.required' => 'Sila masukkan nama penuh.',
            'email.required' => 'Sila masukkan alamat e-mel.',
            'email.unique' => 'E-mel ini telah digunakan.',
            'password.required' => 'Sila tetapkan kata laluan.',
            'password.min' => 'Kata laluan mestilah sekurang-kurangnya 6 aksara.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
            'role.required' => 'Sila pilih peranan pengguna.',
        ]);

        // Halang penciptaan akaun Superadmin daripada form ini
        if ($validated['role'] === 'Superadmin') {
            abort(403, 'Anda tidak dibenarkan mencipta akaun Superadmin baharu.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        // Log Aktiviti
        LogAktiviti::create([
            'user_id' => Auth::id(),
            'aktiviti' => "Mencipta akaun pengguna baharu: {$user->name} dengan peranan '{$validated['role']}'.",
            'item_id' => null,
            'data_baru' => $user->only(['id', 'name', 'email']),
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Akaun pengguna berjaya dicipta.');
    }

    /**
     * Tunjukkan borang kemaskini pengguna.
     */
    public function edit(User $pengguna)
    {
        // Halang mengedit akaun Superadmin lain melalui sini
        if ($pengguna->hasRole('Superadmin')) {
            abort(403);
        }

        $roles = Role::where('name', '!=', 'Superadmin')->get();
        return view('pengguna.edit', compact('pengguna', 'roles'));
    }

    /**
     * Kemaskini akaun pengguna.
     */
    public function update(Request $request, User $pengguna)
    {
        if ($pengguna->hasRole('Superadmin')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $pengguna->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ], [
            'name.required' => 'Sila masukkan nama penuh.',
            'email.required' => 'Sila masukkan alamat e-mel.',
            'email.unique' => 'E-mel ini telah digunakan.',
            'password.min' => 'Kata laluan mestilah sekurang-kurangnya 6 aksara.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
            'role.required' => 'Sila pilih peranan pengguna.',
        ]);

        if ($validated['role'] === 'Superadmin') {
            abort(403);
        }

        $oldData = $pengguna->only(['id', 'name', 'email']);
        $oldRole = $pengguna->roles->first()?->name;

        $pengguna->name = $validated['name'];
        $pengguna->email = $validated['email'];

        if ($request->filled('password')) {
            $pengguna->password = Hash::make($validated['password']);
        }

        $pengguna->save();

        // Kemaskini peranan
        $pengguna->syncRoles([$validated['role']]);

        // Log Aktiviti
        LogAktiviti::create([
            'user_id' => Auth::id(),
            'aktiviti' => "Mengemaskini akaun pengguna: {$pengguna->name} (Peranan asal: {$oldRole}, Peranan baharu: {$validated['role']}).",
            'item_id' => null,
            'data_lama' => $oldData,
            'data_baru' => $pengguna->only(['id', 'name', 'email']),
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Akaun pengguna berjaya dikemaskini.');
    }

    /**
     * Padam akaun pengguna.
     */
    public function destroy(User $pengguna)
    {
        if ($pengguna->hasRole('Superadmin')) {
            abort(403);
        }

        $oldData = $pengguna->only(['id', 'name', 'email']);
        $userName = $pengguna->name;

        $pengguna->delete();

        // Log Aktiviti
        LogAktiviti::create([
            'user_id' => Auth::id(),
            'aktiviti' => "Memadam akaun pengguna: {$userName}.",
            'item_id' => null,
            'data_lama' => $oldData,
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Akaun pengguna berjaya dipadam.');
    }
}

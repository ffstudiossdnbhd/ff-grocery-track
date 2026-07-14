@extends('layouts.app')

@section('title', 'Kemaskini Pengguna')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Kemaskini Akaun Pengguna</h1>
        <p>Kemaskini profil atau peranan bagi {{ $pengguna->name }}</p>
    </div>
    <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('pengguna.update', $pengguna->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">Nama Penuh</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $pengguna->name) }}" required>
            @error('name')
                <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email" class="form-label">Alamat E-mel</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $pengguna->email) }}" required>
                @error('email')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Peranan (Role)</label>
                <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                    @php
                        $userRole = $pengguna->roles->first()?->name;
                    @endphp
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role', $userRole) == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="background-color: rgba(255, 255, 255, 0.02); padding: 1.5rem; border-radius: var(--radius-sm); border: 1px dashed var(--border-color); margin: 2rem 0 1rem 0;">
            <h4 style="font-size: 1rem; color: #fff; margin-bottom: 8px;">Tukar Kata Laluan (Opsional)</h4>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">Biarkan kosong jika anda tidak mahu menukar kata laluan pengguna.</p>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">Kata Laluan Baharu</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 aksara">
                    @error('password')
                        <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Sahkan Kata Laluan Baharu</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Sahkan kata laluan">
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Kemaskini</button>
        </div>
    </form>
</div>
@endsection

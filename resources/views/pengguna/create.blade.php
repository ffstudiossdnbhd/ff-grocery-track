@extends('layouts.app')

@section('title', 'Daftar Pengguna')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Daftar Akaun Pengguna</h1>
        <p>Cipta akaun baharu untuk kakitangan Stocker atau Tracker</p>
    </div>
    <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('pengguna.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">Nama Penuh</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Masukkan nama penuh" required>
            @error('name')
                <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email" class="form-label">Alamat E-mel</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@domain.com" required>
                @error('email')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Peranan (Role)</label>
                <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                    <option value="">-- Pilih Peranan --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row" style="margin-top: 1rem;">
            <div class="form-group">
                <label for="password" class="form-label">Kata Laluan</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 aksara" required>
                @error('password')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Sahkan Kata Laluan</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Sahkan kata laluan" required>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Daftar Pengguna</button>
        </div>
    </form>
</div>
@endsection

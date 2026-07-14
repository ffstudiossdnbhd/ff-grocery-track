@extends('layouts.app')

@section('title', 'Hantar Tuntutan')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Borang Tuntutan Pembelian</h1>
        <p>Isi maklumat barangan yang dibeli untuk menuntut pembayaran semula</p>
    </div>
    <a href="{{ route('tuntutan.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('tuntutan.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="nama_item" class="form-label">Nama Barang / Deskripsi Pembelian</label>
            <input type="text" name="nama_item" id="nama_item" class="form-control @error('nama_item') is-invalid @enderror" placeholder="Contoh: Susu 4 Tin & Bawang 1kg" value="{{ old('nama_item') }}" required>
            @error('nama_item')
                <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nilai_tuntutan" class="form-label">Nilai Pembelian (RM)</label>
                <input type="number" step="0.01" name="nilai_tuntutan" id="nilai_tuntutan" class="form-control @error('nilai_tuntutan') is-invalid @enderror" placeholder="0.00" value="{{ old('nilai_tuntutan') }}" required>
                @error('nilai_tuntutan')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="tarikh_beli" class="form-label">Tarikh Pembelian</label>
                <input type="date" name="tarikh_beli" id="tarikh_beli" class="form-control @error('tarikh_beli') is-invalid @enderror" value="{{ old('tarikh_beli', date('Y-m-d')) }}" required>
                @error('tarikh_beli')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <a href="{{ route('tuntutan.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Hantar Tuntutan</button>
        </div>
    </form>
</div>
@endsection

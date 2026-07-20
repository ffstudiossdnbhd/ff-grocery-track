@extends('layouts.app')

@section('title', 'Kemaskini Barang')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Kemaskini Maklumat Stok</h1>
        <p>Kemaskini maklumat stok bagi {{ $inventori->nama_item }}</p>
    </div>
    <a href="{{ route('inventori.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card" style="max-width: 680px;">
    <form action="{{ route('inventori.update', $inventori->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label for="nama_item" class="form-label">Nama Barang</label>
                <input type="text" name="nama_item" id="nama_item" class="form-control @error('nama_item') is-invalid @enderror" value="{{ old('nama_item', $inventori->nama_item) }}" required>
                @error('nama_item')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="kategori" class="form-label">Kategori</label>
                <input type="text" name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('kategori', $inventori->kategori) }}" required>
                @error('kategori')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row-3col" style="margin-top: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label for="jenama" class="form-label">Jenama</label>
                <input type="text" name="jenama" id="jenama" class="form-control @error('jenama') is-invalid @enderror" placeholder="Contoh: Nestlé, Gardenia" value="{{ old('jenama', $inventori->jenama) }}">
                @error('jenama')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="jenis" class="form-label">Jenis/Varian</label>
                <input type="text" name="jenis" id="jenis" class="form-control @error('jenis') is-invalid @enderror" placeholder="Contoh: Oreo biasa, Oreo Strawberry" value="{{ old('jenis', $inventori->jenis) }}">
                @error('jenis')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="capacity" class="form-label">Capacity (ml/g/kg)</label>
                <input type="text" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror" placeholder="Contoh: 1L, 500g, 2kg" value="{{ old('capacity', $inventori->capacity) }}">
                @error('capacity')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="jumlah_keseluruhan" class="form-label">Jumlah Keseluruhan (Unit)</label>
                <input type="number" name="jumlah_keseluruhan" id="jumlah_keseluruhan" class="form-control @error('jumlah_keseluruhan') is-invalid @enderror" min="0" value="{{ old('jumlah_keseluruhan', $inventori->jumlah_keseluruhan) }}" required onchange="hadkanBelumDibukaInput()">
                @error('jumlah_keseluruhan')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="jumlah_belum_dibuka" class="form-label">Jumlah Belum Dibuka (Unit)</label>
                <input type="number" name="jumlah_belum_dibuka" id="jumlah_belum_dibuka" class="form-control @error('jumlah_belum_dibuka') is-invalid @enderror" min="0" value="{{ old('jumlah_belum_dibuka', $inventori->jumlah_belum_dibuka) }}" required>
                @error('jumlah_belum_dibuka')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="peratus_baki" class="form-label">Peratus Baki Item Dibuka (%)</label>
                <input type="number" name="peratus_baki" id="peratus_baki" class="form-control @error('peratus_baki') is-invalid @enderror" min="0" max="100" value="{{ old('peratus_baki', $inventori->peratus_baki) }}" required>
                @error('peratus_baki')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="had_ambang" class="form-label">Had Ambang Restok (Kuantiti Minimum)</label>
                <input type="number" name="had_ambang" id="had_ambang" class="form-control @error('had_ambang') is-invalid @enderror" min="0" value="{{ old('had_ambang', $inventori->had_ambang) }}" required>
                @error('had_ambang')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row" style="margin-top: 1rem;">
            <div class="form-group">
                <label for="tarikh_luput" class="form-label">Tarikh Luput</label>
                <input type="date" name="tarikh_luput" id="tarikh_luput" class="form-control @error('tarikh_luput') is-invalid @enderror" value="{{ old('tarikh_luput', $inventori->tarikh_luput ? $inventori->tarikh_luput->format('Y-m-d') : '') }}">
                @error('tarikh_luput')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="display: flex; align-items: center; padding-top: 2rem;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="jejak_luput" value="1" {{ old('jejak_luput', $inventori->jejak_luput) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--color-primary);">
                    <span style="font-weight: 500;">Jejak tarikh luput untuk barang ini</span>
                </label>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <a href="{{ route('inventori.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Kemaskini</button>
        </div>
    </form>
</div>

<script>
    function hadkanBelumDibukaInput() {
        const keseluruhan = parseInt(document.getElementById('jumlah_keseluruhan').value) || 0;
        const belumDibuka = document.getElementById('jumlah_belum_dibuka');
        belumDibuka.max = keseluruhan;
        if (parseInt(belumDibuka.value) > keseluruhan) {
            belumDibuka.value = keseluruhan;
        }
    }
</script>
@endsection

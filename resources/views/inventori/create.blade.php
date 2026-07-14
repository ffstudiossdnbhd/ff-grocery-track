@extends('layouts.app')

@section('title', 'Tambah Barang')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Tambah Barangan Baharu</h1>
        <p>Masukkan maklumat barangan dapur ke dalam inventori</p>
    </div>
    <a href="{{ route('inventori.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card" style="max-width: 680px;">
    <form action="{{ route('inventori.store') }}" method="POST">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="nama_item" class="form-label">Nama Barang</label>
                <input type="text" name="nama_item" id="nama_item" class="form-control @error('nama_item') is-invalid @enderror" placeholder="Contoh: Susu Segar, Bawang Besar" value="{{ old('nama_item') }}" required>
                @error('nama_item')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="kategori" class="form-label">Kategori</label>
                <input type="text" name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" placeholder="Contoh: Tenusu, Sayuran, Rencah" value="{{ old('kategori') }}" required>
                @error('kategori')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="jumlah_keseluruhan" class="form-label">Jumlah Keseluruhan (Unit)</label>
                <input type="number" name="jumlah_keseluruhan" id="jumlah_keseluruhan" class="form-control @error('jumlah_keseluruhan') is-invalid @enderror" min="0" value="{{ old('jumlah_keseluruhan', 0) }}" required onchange="hadkanBelumDibukaInput()">
                @error('jumlah_keseluruhan')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="jumlah_belum_dibuka" class="form-label">Jumlah Belum Dibuka (Unit)</label>
                <input type="number" name="jumlah_belum_dibuka" id="jumlah_belum_dibuka" class="form-control @error('jumlah_belum_dibuka') is-invalid @enderror" min="0" value="{{ old('jumlah_belum_dibuka', 0) }}" required>
                @error('jumlah_belum_dibuka')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="peratus_baki" class="form-label">Peratus Baki Item Dibuka (%)</label>
                <input type="number" name="peratus_baki" id="peratus_baki" class="form-control @error('peratus_baki') is-invalid @enderror" min="0" max="100" value="{{ old('peratus_baki', 100) }}" required>
                @error('peratus_baki')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="had_ambang" class="form-label">Had Ambang Restok (Kuantiti Minimum)</label>
                <input type="number" name="had_ambang" id="had_ambang" class="form-control @error('had_ambang') is-invalid @enderror" min="0" value="{{ old('had_ambang', 1) }}" required>
                <small style="color: var(--text-dark); display: block; margin-top: 4px;">Sistem akan memberi amaran apabila baki stok jatuh ke tahap ini.</small>
                @error('had_ambang')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-row" style="margin-top: 1rem;">
            <div class="form-group">
                <label for="tarikh_luput" class="form-label">Tarikh Luput</label>
                <input type="date" name="tarikh_luput" id="tarikh_luput" class="form-control @error('tarikh_luput') is-invalid @enderror" value="{{ old('tarikh_luput') }}">
                @error('tarikh_luput')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="display: flex; align-items: center; padding-top: 2rem;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="jejak_luput" value="1" {{ old('jejak_luput', '1') == '1' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--color-primary);">
                    <span style="font-weight: 500;">Jejak tarikh luput untuk barang ini</span>
                </label>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <a href="{{ route('inventori.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Barang</button>
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

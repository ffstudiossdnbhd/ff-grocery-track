@extends('layouts.app')

@section('title', 'Barangan Perlu Restok')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Senarai Perlu Restok</h1>
        <p>Senarai barangan yang telah habis atau di bawah had ambang restok</p>
    </div>
</div>

<!-- Bahagian 1: Habis Stok -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header-flex">
        <h2 style="color: var(--color-danger); font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>Habis Stok (Kuantiti: 0)</span>
        </h2>
        <span class="badge badge-danger">{{ $habisStok->count() }} item</span>
    </div>
    
    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Had Ambang</th>
                    <th style="text-align: right;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($habisStok as $item)
                <tr>
                    <td><strong>{{ $item->nama_item }}</strong></td>
                    <td><span class="badge badge-primary">{{ $item->kategori }}</span></td>
                    <td>{{ $item->had_ambang }} unit</td>
                    <td style="text-align: right;">
                        <a href="{{ route('inventori.edit', $item->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-plus"></i> Tambah Stok
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        Tiada barangan yang kehabisan stok sepenuhnya. Bagus!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Bahagian 2: Bawah Ambang -->
<div class="card">
    <div class="card-header-flex">
        <h2 style="color: var(--color-warning); font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Kuantiti Bawah Ambang</span>
        </h2>
        <span class="badge badge-warning">{{ $bawahAmbang->count() }} item</span>
    </div>
    
    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Baki Keseluruhan</th>
                    <th>Baki Belum Dibuka</th>
                    <th>Had Ambang</th>
                    <th style="text-align: right;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bawahAmbang as $item)
                <tr>
                    <td><strong>{{ $item->nama_item }}</strong></td>
                    <td><span class="badge badge-primary">{{ $item->kategori }}</span></td>
                    <td><span style="color: var(--color-warning); font-weight: 600;">{{ $item->jumlah_keseluruhan }}</span> unit</td>
                    <td>{{ $item->jumlah_belum_dibuka }} unit</td>
                    <td>{{ $item->had_ambang }} unit</td>
                    <td style="text-align: right;">
                        <a href="{{ route('inventori.edit', $item->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-pen"></i> Kemaskini
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        Tiada barangan di bawah had ambang restok.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

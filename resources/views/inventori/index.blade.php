@extends('layouts.app')

@section('title', 'Senarai Inventori')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Inventori Barang Runcit</h1>
        <p>Uruskan baki unit dan status barangan dapur</p>
    </div>
    @hasanyrole('Superadmin|Stocker|Tracker')
    <a href="{{ route('inventori.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Barang</span>
    </a>
    @endhasanyrole
</div>

<!-- Penapis dan Carian -->
<div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
    <form action="{{ route('inventori.index') }}" method="GET" class="inventori-filter-form">
        <div class="inventori-search-row">
            <div class="inventori-search-input">
                <input type="text" name="carian" class="form-control" placeholder="Cari nama barang..." value="{{ request('carian') }}">
            </div>
            <button type="submit" class="btn btn-secondary inventori-submit-btn">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="tapis-label">Tapis</span>
            </button>
        </div>
        <div class="inventori-filter-row">
            <select name="kategori" class="form-control">
                <option value="">Semua Kategori</option>
                @foreach($kategoriSenarai as $kat)
                    <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
            @if(request('carian') || request('kategori'))
                <a href="{{ route('inventori.index') }}" class="btn btn-secondary" style="background: transparent; border: none; white-space: nowrap;">Set Semula</a>
            @endif
        </div>
    </form>
</div>

<!-- Senarai Barang -->
<div class="card inventori-list-card" style="padding: 0;">
    <div class="table-wrapper desktop-only-view">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Kategori</th>
                    <th>Jumlah Keseluruhan</th>
                    <th>Belum Dibuka</th>
                    <th>Tahap Penggunaan</th>
                    <th>Status Luput</th>
                    <th style="text-align: right;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td data-label="Nama Item">
                        <div class="table-item-info">
                            <div style="font-weight: 600; font-size: 1rem; color: #fff;">{{ $item->nama_item }}</div>
                            @if($item->jenama || $item->jenis || $item->capacity)
                                <div style="font-size: 0.78rem; color: var(--text-dark); margin-top: 2px; margin-bottom: 2px;">
                                    @if($item->jenama)<span>Jenama: <strong>{{ $item->jenama }}</strong></span>@endif
                                    @if($item->jenis)<span> @if($item->jenama)|@endif Jenis/Varian: <strong>{{ $item->jenis }}</strong></span>@endif
                                    @if($item->capacity)<span> @if($item->jenama || $item->jenis)|@endif Kapasiti: <strong>{{ $item->capacity }}</strong></span>@endif
                                </div>
                            @endif
                            <span style="font-size: 0.75rem; color: var(--text-dark);">Dicipta: {{ $item->created_at->format('d/m/Y') }}</span>
                        </div>
                    </td>
                    <td data-label="Kategori">
                        <span class="badge badge-primary">{{ $item->kategori }}</span>
                    </td>
                    <td data-label="Jumlah Unit">
                        <strong style="font-size: 1.1rem; color: #fff;">{{ $item->jumlah_keseluruhan }}</strong> unit
                    </td>
                    <td data-label="Belum Dibuka">
                        @if($item->jumlah_belum_dibuka == 0 && $item->jumlah_keseluruhan > 0)
                            <span class="badge badge-danger">0 Unit (Semua Dibuka)</span>
                        @else
                            <strong style="color: #fff;">{{ $item->jumlah_belum_dibuka }}</strong> unit
                        @endif
                    </td>
                    <td data-label="Baki Kuantiti" style="min-width: 150px;">
                        <div class="table-progress-wrapper">
                            <div class="table-progress-info">
                                <span>Baki dibuka</span>
                                <span>{{ $item->peratus_baki }}%</span>
                            </div>
                            <div class="progress-bar-container" style="margin-top: 0;">
                                <div class="progress-bar-fill" style="width: {{ $item->peratus_baki }}%; background: {{ $item->peratus_baki <= 20 ? 'var(--color-danger)' : ($item->peratus_baki <= 50 ? 'var(--color-warning)' : 'var(--color-success)') }}"></div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Tarikh Luput">
                        @if($item->jejak_luput && $item->tarikh_luput)
                            @php
                                $daysToExpiry = now()->startOfDay()->diffInDays($item->tarikh_luput->startOfDay(), false);
                            @endphp
                            @if($daysToExpiry < 0)
                                <span class="badge badge-danger" title="{{ $item->tarikh_luput->format('d/m/Y') }}">Telah Luput ({{ abs($daysToExpiry) }} hari)</span>
                            @elseif($daysToExpiry <= 3)
                                <span class="badge badge-warning" title="{{ $item->tarikh_luput->format('d/m/Y') }}">Hampir Luput ({{ $daysToExpiry }} hari)</span>
                            @else
                                <span style="font-size: 0.9rem; color: var(--text-muted);">{{ $item->tarikh_luput->format('d/m/Y') }}</span>
                            @endif
                        @else
                            <span style="font-size: 0.85rem; color: var(--text-dark);">Tidak dijejak</span>
                        @endif
                    </td>
                    <td data-label="Tindakan" style="text-align: right;">
                        <div style="display: inline-flex; gap: 8px;">
                            <button onclick="bukaModalPelarasan({{ json_encode($item) }})" class="btn btn-secondary btn-sm" title="Selaraskan Stok">
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                            <a href="{{ route('inventori.edit', $item->id) }}" class="btn btn-secondary btn-sm" title="Edit Barangan">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            @hasanyrole('Superadmin|Stocker|Tracker')
                            <form action="{{ route('inventori.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Adakah anda pasti mahu memadam item ini?')" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Padam Barangan" style="background-color: transparent; color: var(--color-danger); border: 1px solid rgba(239, 68, 68, 0.2);">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endhasanyrole
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        <i class="fa-solid fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--text-dark);"></i>
                        Tiada rekod inventori dijumpai.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- View Mudah Alih / Mobile View -->
    <div class="mobile-only-view">
        @forelse($items as $item)
        <div class="mobile-item-card">
            <div class="mobile-card-header">
                <div class="item-name-group">
                    <span class="item-name">{{ $item->nama_item }}</span>
                    <span class="badge badge-primary">{{ $item->kategori }}</span>
                </div>
                <div class="item-expiry">
                    @if($item->jejak_luput && $item->tarikh_luput)
                        @php
                            $daysToExpiry = now()->startOfDay()->diffInDays($item->tarikh_luput->startOfDay(), false);
                        @endphp
                        @if($daysToExpiry < 0)
                            <span class="badge badge-danger">Telah Luput</span>
                        @elseif($daysToExpiry <= 3)
                            <span class="badge badge-warning">Hampir Luput ({{ $daysToExpiry }}h)</span>
                        @else
                            <span class="expiry-date-text" style="font-size: 0.85rem; color: var(--text-muted);">EXP: {{ $item->tarikh_luput->format('d/m/Y') }}</span>
                        @endif
                    @else
                        <span class="expiry-no-track" style="font-size: 0.8rem; color: var(--text-dark);">Tidak dijejak</span>
                    @endif
                </div>
            </div>
            
            <div class="mobile-card-stats">
                <div class="stat-box">
                    <span class="stat-label">Jumlah</span>
                    <span class="stat-val"><strong>{{ $item->jumlah_keseluruhan }}</strong> unit</span>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Belum Dibuka</span>
                    <span class="stat-val">
                        @if($item->jumlah_belum_dibuka == 0 && $item->jumlah_keseluruhan > 0)
                            <span class="badge badge-danger" style="padding: 2px 6px; font-size: 0.7rem; font-weight: 500;">0 Unit</span>
                        @else
                            <strong>{{ $item->jumlah_belum_dibuka }}</strong> unit
                        @endif
                    </span>
                </div>
                <div class="stat-box baki-box">
                    <span class="stat-label">Baki Dibuka</span>
                    <div class="baki-progress-group">
                        <span class="stat-val"><strong>{{ $item->peratus_baki }}%</strong></span>
                        <div class="progress-bar-container mini">
                            <div class="progress-bar-fill" style="width: {{ $item->peratus_baki }}%; background: {{ $item->peratus_baki <= 20 ? 'var(--color-danger)' : ($item->peratus_baki <= 50 ? 'var(--color-warning)' : 'var(--color-success)') }}"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mobile-card-actions">
                <span class="created-at-text">Dicipta: {{ $item->created_at->format('d/m/Y') }}</span>
                <div class="action-buttons">
                    <button onclick="bukaModalPelarasan({{ json_encode($item) }})" class="btn btn-secondary btn-sm" title="Selaraskan Stok">
                        <i class="fa-solid fa-sliders"></i>
                    </button>
                    <a href="{{ route('inventori.edit', $item->id) }}" class="btn btn-secondary btn-sm" title="Edit Barangan">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    @hasanyrole('Superadmin|Stocker|Tracker')
                    <form action="{{ route('inventori.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Adakah anda pasti mahu memadam item ini?')" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Padam Barangan" style="background-color: transparent; color: var(--color-danger); border: 1px solid rgba(239, 68, 68, 0.2); padding: 6px 10px;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    @endhasanyrole
                </div>
            </div>
        </div>
        @empty
        <div class="mobile-empty-state">
            <i class="fa-solid fa-box-open" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: var(--text-dark);"></i>
            Tiada rekod inventori dijumpai.
        </div>
        @endforelse
    </div>
    
    <div style="padding: 1.5rem;">
        {{ $items->links() }}
    </div>
</div>

<!-- Modal Pelarasan Tahap Stok -->
<div id="modalPelarasan" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="card" style="width: 100%; max-width: 460px; margin: 1rem; box-shadow: var(--shadow-lg);">
        <div class="card-header-flex">
            <h3 id="modalTitle" style="color: #fff; font-size: 1.25rem;">Selaraskan Stok</h3>
            <button onclick="tutupModalPelarasan()" style="cursor: pointer; font-size: 1.25rem;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <form id="formPelarasan" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">Jumlah Keseluruhan (Unit)</label>
                <input type="number" name="jumlah_keseluruhan" id="adj_keseluruhan" class="form-control" min="0" required onchange="hadkanBelumDibuka()">
            </div>
            
            <div class="form-group">
                <label class="form-label">Jumlah Belum Dibuka (Unit)</label>
                <input type="number" name="jumlah_belum_dibuka" id="adj_belum_dibuka" class="form-control" min="0" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Peratus Baki Item Dibuka (%)</label>
                <input type="range" name="peratus_baki" id="adj_peratus" min="0" max="100" style="width: 100%; height: 6px; background: var(--border-color); outline: none; border-radius: 99px; cursor: pointer;" oninput="updateRangeText(this.value)">
                <div style="text-align: right; font-weight: 600; margin-top: 4px; color: var(--color-primary);"><span id="rangeVal">100</span>%</div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 2rem;">
                <button type="button" onclick="tutupModalPelarasan()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success">Simpan Pelarasan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalPelarasan(item) {
        document.getElementById('modalTitle').innerText = 'Selaraskan: ' + item.nama_item;
        document.getElementById('formPelarasan').action = '/inventori/' + item.id + '/adjust';
        document.getElementById('adj_keseluruhan').value = item.jumlah_keseluruhan;
        document.getElementById('adj_belum_dibuka').value = item.jumlah_belum_dibuka;
        document.getElementById('adj_peratus').value = item.peratus_baki;
        document.getElementById('rangeVal').innerText = item.peratus_baki;
        
        // set limit input
        document.getElementById('adj_belum_dibuka').max = item.jumlah_keseluruhan;
        
        document.getElementById('modalPelarasan').style.display = 'flex';
    }
    
    function tutupModalPelarasan() {
        document.getElementById('modalPelarasan').style.display = 'none';
    }
    
    function updateRangeText(val) {
        document.getElementById('rangeVal').innerText = val;
    }
    
    function hadkanBelumDibuka() {
        const keseluruhan = parseInt(document.getElementById('adj_keseluruhan').value) || 0;
        const belumDibukaInput = document.getElementById('adj_belum_dibuka');
        belumDibukaInput.max = keseluruhan;
        if (parseInt(belumDibukaInput.value) > keseluruhan) {
            belumDibukaInput.value = keseluruhan;
        }
    }
</script>
@endsection

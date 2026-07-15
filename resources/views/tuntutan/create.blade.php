@extends('layouts.app')

@section('title', 'Hantar Tuntutan')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Borang Tuntutan Pembelian</h1>
        <p>Tambah barangan yang dibeli satu persatu untuk menuntut pembayaran semula</p>
    </div>
    <a href="{{ route('tuntutan.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card" style="max-width: 720px;">
    <form action="{{ route('tuntutan.store') }}" method="POST" id="tuntutanForm">
        @csrf

        {{-- Hidden field to store serialised item string --}}
        <input type="hidden" name="nama_item" id="nama_item_hidden">

        {{-- Item Table --}}
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <label class="form-label" style="margin-bottom: 0; font-weight: 600; font-size: 0.95rem;">
                    <i class="fa-solid fa-list" style="color: var(--color-primary); margin-right: 6px;"></i>
                    Senarai Barangan
                </label>
                <span style="font-size: 0.8rem; color: var(--text-dark);">
                    <kbd style="background: var(--bg-surface-hover); border: 1px solid var(--border-color); border-radius: 4px; padding: 2px 6px; font-family: inherit; font-size: 0.78rem;">Enter</kbd>
                    untuk ke ruangan seterusnya
                </span>
            </div>

            <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden;">
                {{-- Table Header --}}
                <div style="display: grid; grid-template-columns: 1fr 150px 44px; background: var(--bg-surface-hover); border-bottom: 1px solid var(--border-color); padding: 0.6rem 0.75rem; gap: 8px;">
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Item</span>
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Unit / Kuantiti</span>
                    <span></span>
                </div>

                {{-- Item Rows Container --}}
                <div id="itemRows"></div>

                {{-- Add Row Button --}}
                <div style="border-top: 1px solid var(--border-color); padding: 0.5rem 0.75rem;">
                    <button type="button" id="addRowBtn" onclick="addRow()"
                        style="background: none; border: none; color: var(--color-primary); font-size: 0.85rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; padding: 4px 0; transition: var(--transition-fast);"
                        onmouseenter="this.style.color='var(--color-primary-hover)'"
                        onmouseleave="this.style.color='var(--color-primary)'">
                        <i class="fa-solid fa-plus"></i> Tambah baris
                    </button>
                </div>
            </div>

            <div id="itemError" style="color: var(--color-danger); font-size: 0.8rem; margin-top: 6px; display: none;">
                Sila masukkan sekurang-kurangnya satu item.
            </div>
        </div>

        {{-- Bottom Row: Nilai Pembelian & Tarikh --}}
        <div class="form-row">
            <div class="form-group">
                <label for="nilai_tuntutan" class="form-label">Nilai Pembelian (RM) — Jumlah</label>
                <input type="number" step="0.01" name="nilai_tuntutan" id="nilai_tuntutan"
                    class="form-control @error('nilai_tuntutan') is-invalid @enderror"
                    placeholder="0.00" value="{{ old('nilai_tuntutan') }}" required>
                @error('nilai_tuntutan')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="tarikh_beli" class="form-label">Tarikh Pembelian</label>
                <input type="date" name="tarikh_beli" id="tarikh_beli"
                    class="form-control @error('tarikh_beli') is-invalid @enderror"
                    value="{{ old('tarikh_beli', date('Y-m-d')) }}" required>
                @error('tarikh_beli')
                    <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <a href="{{ route('tuntutan.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fa-solid fa-paper-plane"></i> Hantar Tuntutan
            </button>
        </div>
    </form>
</div>

<style>
    .item-row {
        display: grid;
        grid-template-columns: 1fr 150px 44px;
        gap: 8px;
        padding: 0.4rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
        transition: background var(--transition-fast);
    }
    .item-row:last-child {
        border-bottom: none;
    }
    .item-row:hover {
        background: rgba(99, 102, 241, 0.04);
    }
    .item-row input {
        width: 100%;
        background: var(--bg-surface-hover);
        border: 1px solid transparent;
        border-radius: var(--radius-sm);
        padding: 0.45rem 0.65rem;
        font-size: 0.9rem;
        color: var(--text-main);
        transition: border-color var(--transition-fast), background var(--transition-fast);
    }
    .item-row input:focus {
        border-color: var(--color-primary);
        background: rgba(99, 102, 241, 0.08);
        outline: none;
    }
    .item-row input.is-empty-error {
        border-color: var(--color-danger) !important;
    }
    .remove-row-btn {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        cursor: pointer;
        color: var(--text-dark);
        transition: color var(--transition-fast), background var(--transition-fast);
        font-size: 0.8rem;
        background: none;
        border: none;
        flex-shrink: 0;
    }
    .remove-row-btn:hover {
        color: var(--color-danger);
        background: rgba(239, 68, 68, 0.1);
    }
    .remove-row-btn:disabled {
        opacity: 0.2;
        cursor: not-allowed;
        pointer-events: none;
    }
</style>

<script>
    let rowCount = 0;

    function addRow(focusItem = true) {
        rowCount++;
        const idx = rowCount;
        const container = document.getElementById('itemRows');

        const row = document.createElement('div');
        row.className = 'item-row';
        row.dataset.rowId = idx;
        row.innerHTML = `
            <input
                type="text"
                class="item-name-input"
                placeholder="Nama barang…"
                data-row="${idx}"
                autocomplete="off"
            >
            <input
                type="text"
                class="item-unit-input"
                placeholder="cth: 2 tin, 1 kg"
                data-row="${idx}"
                autocomplete="off"
            >
            <button type="button" class="remove-row-btn" onclick="removeRow(${idx})" title="Padam baris">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        container.appendChild(row);
        updateRemoveButtons();

        if (focusItem) {
            row.querySelector('.item-name-input').focus();
        }

        // Enter on item-name → focus unit
        row.querySelector('.item-name-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                row.querySelector('.item-unit-input').focus();
            }
        });

        // Enter on unit → add new row and focus its item field
        row.querySelector('.item-unit-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addRow(true);
            }
        });
    }

    function removeRow(idx) {
        const container = document.getElementById('itemRows');
        const row = container.querySelector(`[data-row-id="${idx}"]`);
        if (row) row.remove();
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-row-btn');
            btn.disabled = rows.length <= 1;
        });
    }

    function serializeItems() {
        const rows = document.querySelectorAll('.item-row');
        const parts = [];
        let hasError = false;

        rows.forEach(row => {
            const nameInput = row.querySelector('.item-name-input');
            const unitInput = row.querySelector('.item-unit-input');
            const name = nameInput.value.trim();
            const unit = unitInput.value.trim();

            nameInput.classList.remove('is-empty-error');

            // Skip fully empty rows silently
            if (name === '' && unit === '') return;

            if (name === '') {
                nameInput.classList.add('is-empty-error');
                hasError = true;
                return;
            }

            parts.push(unit ? `${name} (${unit})` : name);
        });

        return { hasError, value: parts.join(', ') };
    }

    document.getElementById('tuntutanForm').addEventListener('submit', function(e) {
        const { hasError, value } = serializeItems();
        const itemError = document.getElementById('itemError');

        if (hasError || value === '') {
            e.preventDefault();
            itemError.style.display = 'block';
            return;
        }

        itemError.style.display = 'none';
        document.getElementById('nama_item_hidden').value = value;
    });

    // Initialise with one empty row, focus item field
    addRow(false);
    document.querySelector('.item-name-input').focus();
</script>
@endsection

@extends('layouts.app')

@section('title', 'Hantar Tuntutan')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Borang Tuntutan Pembelian</h1>
        <p>Tambah barangan yang dibeli untuk menuntut pembayaran semula</p>
    </div>
    <a href="{{ route('tuntutan.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card" style="max-width: 720px;">
    <form action="{{ route('tuntutan.store') }}" method="POST" id="tuntutanForm">
        @csrf

        {{-- Hidden fields submitted to backend --}}
        <input type="hidden" name="nama_item"      id="nama_item_hidden">
        <input type="hidden" name="nilai_tuntutan" id="nilai_tuntutan_hidden">

        {{-- ══════════════════════════════════════
             TAG SELECTOR
        ══════════════════════════════════════ --}}
        <div style="margin-bottom: 1.75rem;">
            <label class="form-label" style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.6rem; display: block;">
                <i class="fa-solid fa-tag" style="color: var(--color-primary); margin-right: 6px;"></i>
                Jenis Tuntutan
            </label>
            <div style="display: flex; gap: 10px;">
                <label class="tag-pill" id="pill-stok">
                    <input type="radio" name="tag" value="Stok" {{ old('tag', '') === 'Stok' ? 'checked' : '' }} required>
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Stok</span>
                </label>
                <label class="tag-pill" id="pill-lunch">
                    <input type="radio" name="tag" value="Lunch" {{ old('tag', '') === 'Lunch' ? 'checked' : '' }}>
                    <i class="fa-solid fa-utensils"></i>
                    <span>Lunch</span>
                </label>
            </div>
            @error('tag')
                <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 6px;">{{ $message }}</div>
            @enderror
        </div>

        {{-- ══════════════════════════════════════
             STOK SECTION
        ══════════════════════════════════════ --}}
        <div id="section-stok" class="mode-section" style="display: none;">

            {{-- Item table --}}
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
                    <div style="display: grid; grid-template-columns: 1fr 150px 44px; background: var(--bg-surface-hover); border-bottom: 1px solid var(--border-color); padding: 0.6rem 0.75rem; gap: 8px;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Item</span>
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Unit / Kuantiti</span>
                        <span></span>
                    </div>
                    <div id="itemRows"></div>
                    <div style="border-top: 1px solid var(--border-color); padding: 0.5rem 0.75rem;">
                        <button type="button" onclick="addRow()"
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

            {{-- Stok: Nilai Pembelian + Tarikh Pembelian --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nilai Pembelian (RM) — Jumlah</label>
                    <input type="number" step="0.01" id="stok_nilai"
                        class="form-control"
                        placeholder="0.00" value="{{ old('nilai_tuntutan') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tarikh Pembelian</label>
                    <input type="date" id="stok_tarikh"
                        class="form-control"
                        value="{{ old('tarikh_beli', date('Y-m-d')) }}">
                </div>
            </div>
            <div id="stokBottomError" style="color: var(--color-danger); font-size: 0.8rem; margin-top: -0.5rem; margin-bottom: 0.75rem; display: none;">
                Sila lengkapkan nilai pembelian dan tarikh.
            </div>
        </div>

        {{-- ══════════════════════════════════════
             LUNCH SECTION
        ══════════════════════════════════════ --}}
        <div id="section-lunch" class="mode-section" style="display: none;">

            {{-- Week Selector Row --}}
            <div class="form-row" style="margin-bottom: 1.25rem;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.6rem; display: block;">
                        <i class="fa-solid fa-calendar-week" style="color: var(--color-primary); margin-right: 6px;"></i>
                        Pilih Minggu
                    </label>
                    <input type="week" id="lunch_week" name="week" class="form-control"
                        value="{{ old('week', \Carbon\Carbon::now()->format('o-\WW')) }}" required>
                </div>
            </div>

            {{-- Table with auto-filled dates --}}
            <div style="margin-bottom: 1.5rem;">
                <label class="form-label" style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem; display: block;">
                    <i class="fa-solid fa-calendar-day" style="color: var(--color-primary); margin-right: 6px;"></i>
                    Butiran Lunch Mengikut Hari
                </label>

                <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden;">
                    {{-- Header --}}
                    <div style="display: grid; grid-template-columns: 140px 1fr 90px 110px; background: var(--bg-surface-hover); border-bottom: 1px solid var(--border-color); padding: 0.6rem 0.75rem; gap: 12px;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Tarikh</span>
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Butiran Lunch</span>
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Pax</span>
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Harga/Pax</span>
                    </div>

                    {{-- Rows container --}}
                    <div id="lunchDaysRows"></div>
                </div>
                <div id="lunchInputError" style="color: var(--color-danger); font-size: 0.8rem; margin-top: 6px; display: none;">
                    Sila masukkan Bil. Pax dan Harga/Pax untuk hari yang ingin dituntut.
                </div>
            </div>

            {{-- Lunch: Jumlah (auto) --}}
            <div class="form-row" style="align-items: flex-end; margin-bottom: 1rem;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" style="display: flex; align-items: center; gap: 6px; font-weight: 600;">
                        Jumlah Tuntutan Minggu Ini (RM)
                        <span style="font-size: 0.75rem; color: var(--text-dark); font-weight: 400;">— dikira secara automatik</span>
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-dark); font-size: 0.85rem; pointer-events: none; z-index: 1;">RM</span>
                        <input type="text" id="lunch_total_display" class="form-control"
                            readonly value="0.00"
                            style="background: var(--bg-surface-hover); color: var(--color-success); font-weight: 700; font-size: 1.05rem; cursor: default; border-color: rgba(16,185,129,0.3); padding-left: 2.5rem;">
                    </div>
                </div>
            </div>
            <div id="lunchBottomError" style="color: var(--color-danger); font-size: 0.8rem; margin-top: -0.5rem; margin-bottom: 0.75rem; display: none;">
                Sila isi minggu yang sah.
            </div>
        </div>

        {{-- ══════════════════════════════════════
             PROMPT — no tag selected yet
        ══════════════════════════════════════ --}}
        <div id="section-prompt" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-dark);">
            <i class="fa-solid fa-hand-pointer" style="font-size: 2rem; margin-bottom: 0.75rem; display: block; opacity: 0.4;"></i>
            <span style="font-size: 0.9rem;">Pilih jenis tuntutan di atas untuk mula mengisi borang.</span>
        </div>

        {{-- Action buttons --}}
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <a href="{{ route('tuntutan.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fa-solid fa-paper-plane"></i> Hantar Tuntutan
            </button>
        </div>
    </form>
</div>

<style>
    /* Tag pill toggle */
    .tag-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.5rem 1.1rem;
        border-radius: 999px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-surface-hover);
        color: var(--text-muted);
        font-size: 0.88rem;
        font-weight: 500;
        cursor: pointer;
        transition: border-color var(--transition-fast), background var(--transition-fast),
                    color var(--transition-fast), box-shadow var(--transition-fast);
        user-select: none;
    }
    .tag-pill input[type="radio"] { display: none; }
    .tag-pill:has(input:checked) {
        border-color: var(--color-primary);
        background: rgba(99, 102, 241, 0.12);
        color: #a5b4fc;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .tag-pill:hover {
        border-color: rgba(99, 102, 241, 0.5);
        color: var(--text-main);
    }

    /* Stok item rows */
    .item-row {
        display: grid;
        grid-template-columns: 1fr 150px 44px;
        gap: 8px;
        padding: 0.4rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
        transition: background var(--transition-fast);
    }
    .item-row:last-child { border-bottom: none; }
    .item-row:hover { background: rgba(99, 102, 241, 0.04); }
    .item-row input,
    .lunch-input {
        width: 100%;
        background: var(--bg-surface-hover);
        border: 1px solid transparent;
        border-radius: var(--radius-sm);
        padding: 0.45rem 0.65rem;
        font-size: 0.9rem;
        color: var(--text-main);
        transition: border-color var(--transition-fast), background var(--transition-fast);
    }
    .item-row input:focus,
    .lunch-input:focus {
        border-color: var(--color-primary);
        background: rgba(99, 102, 241, 0.08);
        outline: none;
    }
    .item-row input.is-empty-error { border-color: var(--color-danger) !important; }
    .remove-row-btn {
        width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
        border-radius: var(--radius-sm);
        cursor: pointer;
        color: var(--text-dark);
        transition: color var(--transition-fast), background var(--transition-fast);
        font-size: 0.8rem;
        background: none; border: none;
        flex-shrink: 0;
    }
    .remove-row-btn:hover { color: var(--color-danger); background: rgba(239,68,68,0.1); }
    .remove-row-btn:disabled { opacity: 0.2; cursor: not-allowed; pointer-events: none; }

    /* Lunch inputs in the table row */
    #section-lunch .lunch-input { display: block; }
    #lunch_jumlah_wrapper { margin-top: 0; }
</style>

<script>
    /* ─── Tag switching ─── */
    const radios = document.querySelectorAll('input[name="tag"]');
    radios.forEach(r => r.addEventListener('change', onTagChange));

    function onTagChange() {
        const val = document.querySelector('input[name="tag"]:checked')?.value;
        document.getElementById('section-prompt').style.display = val ? 'none' : 'block';
        document.getElementById('section-stok').style.display  = val === 'Stok'  ? 'block' : 'none';
        document.getElementById('section-lunch').style.display = val === 'Lunch' ? 'block' : 'none';
    }

    // Restore state on page load (e.g. after validation error)
    onTagChange();

    /* ─── STOK: dynamic item rows ─── */
    let rowCount = 0;

    function addRow(focusItem = true) {
        rowCount++;
        const idx = rowCount;
        const container = document.getElementById('itemRows');

        const row = document.createElement('div');
        row.className = 'item-row';
        row.dataset.rowId = idx;
        row.innerHTML = `
            <input type="text" class="item-name-input" placeholder="Nama barang…"
                data-row="${idx}" autocomplete="off">
            <input type="text" class="item-unit-input" placeholder="cth: 2 tin, 1 kg"
                data-row="${idx}" autocomplete="off">
            <button type="button" class="remove-row-btn" onclick="removeRow(${idx})" title="Padam baris">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        container.appendChild(row);
        updateRemoveButtons();

        if (focusItem) row.querySelector('.item-name-input').focus();

        row.querySelector('.item-name-input').addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); row.querySelector('.item-unit-input').focus(); }
        });
        row.querySelector('.item-unit-input').addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); addRow(true); }
        });
    }

    function removeRow(idx) {
        const row = document.querySelector(`#itemRows [data-row-id="${idx}"]`);
        if (row) row.remove();
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            row.querySelector('.remove-row-btn').disabled = rows.length <= 1;
        });
    }

    function serializeStokItems() {
        const rows = document.querySelectorAll('.item-row');
        const parts = [];
        let hasError = false;
        rows.forEach(row => {
            const nameInput = row.querySelector('.item-name-input');
            const unitInput = row.querySelector('.item-unit-input');
            const name = nameInput.value.trim();
            const unit = unitInput.value.trim();
            nameInput.classList.remove('is-empty-error');
            if (name === '' && unit === '') return;
            if (name === '') { nameInput.classList.add('is-empty-error'); hasError = true; return; }
            parts.push(unit ? `${name} (${unit})` : name);
        });
        return { hasError, value: parts.join(', ') };
    }

    // Initialise first row
    addRow(false);

    /* ─── LUNCH: weekly claim logic ─── */
    const oldLunchData = {
        dates: @json(old('lunch_dates', [])),
        butirans: @json(old('lunch_butirans', [])),
        pax: @json(old('lunch_pax', [])),
        hargas: @json(old('lunch_hargas', []))
    };

    const weekInput  = document.getElementById('lunch_week');
    const totalDisp  = document.getElementById('lunch_total_display');

    function getDatesFromISOWeek(weekStr) {
        const parts = weekStr.split('-W');
        if (parts.length !== 2) return [];
        const year = parseInt(parts[0], 10);
        const week = parseInt(parts[1], 10);
        
        // Start with Jan 4 (always in ISO week 1)
        const d = new Date(year, 0, 4);
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);
        const mondayOfWeek1 = new Date(d.setDate(diff));
        
        const mondayOfTargetWeek = new Date(mondayOfWeek1.setDate(mondayOfWeek1.getDate() + (week - 1) * 7));
        
        const dates = [];
        for (let i = 0; i < 7; i++) {
            const temp = new Date(mondayOfTargetWeek);
            temp.setDate(mondayOfTargetWeek.getDate() + i);
            const yyyy = temp.getFullYear();
            const mm = String(temp.getMonth() + 1).padStart(2, '0');
            const dd = String(temp.getDate()).padStart(2, '0');
            dates.push(`${yyyy}-${mm}-${dd}`);
        }
        return dates;
    }

    function formatDateDisplay(dateStr) {
        const parts = dateStr.split('-');
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function calcLunchTotal() {
        let total = 0;
        
        document.querySelectorAll('.lunch-pax-input').forEach((paxInput) => {
            const pax = parseInt(paxInput.value, 10) || 0;
            const row = paxInput.closest('div').parentElement;
            const hargaInput = row.querySelector('.lunch-harga-input');
            const harga = parseFloat(hargaInput.value) || 0;
            total += pax * harga;
        });
        
        totalDisp.value = total > 0 ? total.toFixed(2) : '0.00';
    }

    function renderLunchRows() {
        const weekVal = weekInput.value;
        const container = document.getElementById('lunchDaysRows');
        container.innerHTML = '';
        
        if (!weekVal) {
            container.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--text-muted);">Sila pilih minggu di atas.</div>';
            return;
        }
        
        const dates = getDatesFromISOWeek(weekVal);
        const dayNames = ['Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu', 'Ahad'];
        
        dates.forEach((dateStr, index) => {
            let paxVal = '';
            let butiranVal = 'Lunch Claim';
            let hargaVal = '12.50'; // Default daily price
            
            // Check if we have old validation data for this index
            if (oldLunchData.dates && oldLunchData.dates[index] === dateStr) {
                paxVal = oldLunchData.pax[index] !== null ? oldLunchData.pax[index] : '';
                butiranVal = oldLunchData.butirans[index] || 'Lunch Claim';
                hargaVal = oldLunchData.hargas[index] !== null ? oldLunchData.hargas[index] : '12.50';
            }
            
            const row = document.createElement('div');
            row.style.display = 'grid';
            row.style.gridTemplateColumns = '140px 1fr 90px 110px';
            row.style.gap = '12px';
            row.style.padding = '0.4rem 0.75rem';
            row.style.alignItems = 'center';
            row.style.borderBottom = index < 6 ? '1px solid var(--border-color)' : 'none';
            
            row.innerHTML = `
                <div>
                    <span style="font-size: 0.85rem; font-weight: 500; display: block; color: var(--text-main);">${dayNames[index]}</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">${formatDateDisplay(dateStr)}</span>
                    <input type="hidden" name="lunch_dates[]" value="${dateStr}">
                </div>
                <div>
                    <input type="text" name="lunch_butirans[]" class="lunch-input" 
                        value="${escapeHtml(butiranVal)}" placeholder="Butiran lunch..." autocomplete="off">
                </div>
                <div>
                    <input type="number" name="lunch_pax[]" class="lunch-input lunch-pax-input" 
                        value="${paxVal}" min="0" placeholder="0" autocomplete="off" style="text-align: right;">
                </div>
                <div>
                    <input type="number" name="lunch_hargas[]" class="lunch-input lunch-harga-input" 
                        value="${hargaVal}" step="0.01" min="0" placeholder="0.00" autocomplete="off" style="text-align: right;">
                </div>
            `;
            container.appendChild(row);
        });
        
        document.querySelectorAll('.lunch-pax-input').forEach(input => {
            input.addEventListener('input', calcLunchTotal);
        });
        document.querySelectorAll('.lunch-harga-input').forEach(input => {
            input.addEventListener('input', calcLunchTotal);
        });
        
        calcLunchTotal();
    }

    weekInput.addEventListener('change', renderLunchRows);

    // Initial render
    renderLunchRows();

    /* ─── Form submit ─── */
    document.getElementById('tuntutanForm').addEventListener('submit', function(e) {
        const tag = document.querySelector('input[name="tag"]:checked')?.value;

        if (!tag) { e.preventDefault(); return; }

        if (tag === 'Stok') {
            const { hasError, value } = serializeStokItems();
            const stokNilai  = document.getElementById('stok_nilai').value.trim();
            const stokTarikh = document.getElementById('stok_tarikh').value.trim();
            const itemErr    = document.getElementById('itemError');
            const botErr     = document.getElementById('stokBottomError');

            let stop = false;
            if (hasError || value === '') { e.preventDefault(); itemErr.style.display = 'block'; stop = true; }
            else itemErr.style.display = 'none';

            if (!stokNilai || !stokTarikh) { e.preventDefault(); botErr.style.display = 'block'; stop = true; }
            else botErr.style.display = 'none';

            if (stop) return;

            document.getElementById('nama_item_hidden').value      = value;
            document.getElementById('nilai_tuntutan_hidden').value = stokNilai;
            // inject tarikh into a named input for submission
            let t = document.getElementById('_stok_tarikh_submit');
            if (!t) { t = document.createElement('input'); t.type='hidden'; t.name='tarikh_beli'; t.id='_stok_tarikh_submit'; this.appendChild(t); }
            t.value = stokTarikh;
        }

        if (tag === 'Lunch') {
            const week = weekInput.value;
            const paxInputs = document.querySelectorAll('.lunch-pax-input');
            const inpErr = document.getElementById('lunchInputError');
            const botErr = document.getElementById('lunchBottomError');
            
            let totalPax = 0;
            let missingButiran = false;
            let missingHarga = false;
            let totalClaimsAmount = 0;
            
            paxInputs.forEach((input) => {
                const p = parseInt(input.value, 10) || 0;
                totalPax += p;
                
                const row = input.closest('div').parentElement;
                const butiranInput = row.querySelector('input[name="lunch_butirans[]"]');
                const hargaInput = row.querySelector('input[name="lunch_hargas[]"]');
                const price = parseFloat(hargaInput.value) || 0;
                
                butiranInput.style.borderColor = '';
                hargaInput.style.borderColor = '';
                
                if (p > 0) {
                    const butiran = butiranInput.value.trim();
                    totalClaimsAmount += p * price;
                    
                    if (!butiran) {
                        missingButiran = true;
                        butiranInput.style.borderColor = 'var(--color-danger)';
                    }
                    if (price <= 0) {
                        missingHarga = true;
                        hargaInput.style.borderColor = 'var(--color-danger)';
                    }
                }
            });
            
            let stop = false;
            if (totalPax <= 0) {
                e.preventDefault();
                inpErr.innerText = 'Sila masukkan sekurang-kurangnya satu Bil. Pax untuk hari yang ingin dituntut.';
                inpErr.style.display = 'block';
                stop = true;
            } else {
                inpErr.style.display = 'none';
            }
            
            if (!week) {
                e.preventDefault();
                botErr.innerText = 'Sila pilih minggu.';
                botErr.style.display = 'block';
                stop = true;
            } else {
                botErr.style.display = 'none';
            }
            
            if (missingButiran) {
                e.preventDefault();
                inpErr.innerText = 'Sila isi butiran lunch untuk hari yang mempunyai pax.';
                inpErr.style.display = 'block';
                stop = true;
            }
            
            if (missingHarga) {
                e.preventDefault();
                inpErr.innerText = 'Sila isi harga per pax yang sah untuk hari yang mempunyai pax.';
                inpErr.style.display = 'block';
                stop = true;
            }
            
            if (stop) return;
            
            // Set values to satisfy dummy checks
            document.getElementById('nama_item_hidden').value = `Lunch Claim - Minggu ${week}`;
            document.getElementById('nilai_tuntutan_hidden').value = totalClaimsAmount.toFixed(2);
            
            let firstDate = '';
            document.querySelectorAll('.lunch-pax-input').forEach((input) => {
                const p = parseInt(input.value, 10) || 0;
                if (p > 0 && !firstDate) {
                    const row = input.closest('div').parentElement;
                    firstDate = row.querySelector('input[name="lunch_dates[]"]').value;
                }
            });
            
            let t = document.getElementById('_lunch_tarikh_submit');
            if (!t) {
                t = document.createElement('input');
                t.type = 'hidden';
                t.name = 'tarikh_beli';
                t.id = '_lunch_tarikh_submit';
                this.appendChild(t);
            }
            t.value = firstDate || new Date().toISOString().split('T')[0];
        }
    });
</script>
@endsection

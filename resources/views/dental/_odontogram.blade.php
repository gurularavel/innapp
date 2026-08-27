{{--
    Interactive FDI odontogram.

    $mode      'edit' (clickable, posts inputs) | 'view' (read-only summary)
    $selected  [['tooth_number' => 16, 'status' => 'caries', 'note' => '...'], ...]
    $history   [16 => [['date' => '12.05.2026', 'status' => 'filling', 'note' => '...'], ...], ...]
    $inputName name of the posted array (edit mode only)
--}}
@php
    /** @var class-string<\App\Models\PatientVisitTooth> $T */
    $T = \App\Models\PatientVisitTooth::class;

    $mode      = $mode      ?? 'edit';
    $selected  = collect($selected ?? [])->keyBy('tooth_number');
    $history   = $history   ?? [];
    $inputName = $inputName ?? 'teeth';
    $chartId   = 'odo-' . uniqid();

    $shapeNames = [
        'incisor'  => 'kəsici diş',
        'canine'   => 'köpək dişi',
        'premolar' => 'kiçik azı',
        'molar'    => 'böyük azı',
    ];
@endphp

@once
@push('styles')
<style>
/* ── Odontogram ─────────────────────────────────────────────────────────── */
.odo-wrap{border:1px solid #e6e9ee;border-radius:.85rem;background:linear-gradient(180deg,#fbfcfe 0%,#f4f7fa 100%);}
.odo-head{padding:.75rem .9rem;border-bottom:1px solid #e9edf2;}
.odo-body{padding:.9rem;}

/* permanent / primary switch */
.odo-set{display:inline-flex;align-items:center;padding-inline:.8rem;white-space:nowrap;}
.odo-set .badge{font-size:.65rem;}
.odo-set.active .badge{background:#fff!important;color:#6c757d!important;}

/* status palette */
.odo-palette{display:flex;flex-wrap:wrap;gap:.35rem;}
.odo-pill{display:inline-flex;align-items:center;gap:.35rem;border:1px solid #dde3ea;background:#fff;
    border-radius:999px;padding:.25rem .65rem;font-size:.78rem;line-height:1.2;cursor:pointer;
    transition:box-shadow .15s,border-color .15s,transform .1s;}
.odo-pill:hover{border-color:var(--c);transform:translateY(-1px);}
.odo-pill .dot{width:.6rem;height:.6rem;border-radius:50%;background:var(--c);flex-shrink:0;}
.odo-pill.active{border-color:var(--c);background:var(--c);color:#fff;box-shadow:0 2px 8px -2px var(--c);}
.odo-pill.active .dot{background:#fff;}
.odo-pill-count{display:inline-flex;align-items:center;justify-content:center;min-width:1.15rem;height:1.15rem;
    padding:0 .25rem;border-radius:999px;background:var(--c);color:#fff;font-size:.66rem;font-weight:700;}
.odo-pill.active .odo-pill-count{background:#fff;color:var(--c);}
.odo-pill-funnel{display:none;font-size:.7rem;}
.odo-pill.odo-filtering .odo-pill-funnel{display:inline-block;}

/* chart */
/* `safe center` keeps the chart centred but never scrolls its left edge out of reach */
.odo-scroll{overflow-x:auto;overflow-y:hidden;padding:.25rem 0 .1rem;display:flex;justify-content:safe center;}
.odo-chart{width:max-content;flex:none;user-select:none;-webkit-user-select:none;}
.odo-sides{display:flex;justify-content:space-between;font-size:.72rem;font-weight:600;
    letter-spacing:.04em;text-transform:uppercase;color:#8b97a5;padding:0 .25rem;}
.odo-jaw{text-align:center;font-size:.75rem;font-weight:600;color:#5a6673;}
.odo-row{display:flex;align-items:flex-end;justify-content:center;gap:.15rem;}
/* the arch offset moves the outer teeth outside the row box — reserve room for it */
.odo-row.upper{padding-bottom:14px;}
.odo-row.lower{align-items:flex-start;padding-top:14px;}
.odo-mid{width:0;border-left:1px dashed #c8d1db;align-self:stretch;margin:0 .5rem;}
.odo-occlusal{border-top:1px dashed #d5dde6;margin:.35rem 0;}

/* one tooth */
.odo-tooth{display:flex;flex-direction:column;align-items:center;gap:.1rem;
    background:none;border:0;padding:.1rem .05rem;cursor:pointer;--c:#adb5bd;
    transition:opacity .15s;}
/* a status filter is on and this tooth does not carry it */
.odo-tooth.odo-dim{opacity:.15;}
/* still reachable while filtered, so it can be marked */
.odo-tooth.odo-dim:not([disabled]):hover{opacity:.6;}
/* …and the ones that do carry it get a heavier outline */
.odo-chart.odo-filtering .odo-tooth:not(.odo-dim) .odo-path{stroke-width:3.2;}
.odo-tooth.lower{flex-direction:column-reverse;}
.odo-tooth[disabled]{cursor:default;}
.odo-num{font-size:.68rem;font-weight:600;color:#8b97a5;font-variant-numeric:tabular-nums;}
.odo-svg{width:30px;height:44px;display:block;overflow:visible;}
.odo-tooth.upper .odo-svg{transform:scaleY(-1);}
.odo-path{fill:#fff;stroke:#c9d2dc;stroke-width:2.2;stroke-linejoin:round;transition:fill .15s,stroke .15s;}
.odo-tooth:not([disabled]):hover .odo-path{stroke:#0d6efd;fill:#eef5ff;}
.odo-tooth:not([disabled]):hover .odo-num{color:#0d6efd;}
.odo-tooth:focus-visible{outline:2px solid #0d6efd;outline-offset:2px;border-radius:.35rem;}
.odo-tooth.on .odo-path{fill:var(--c);fill-opacity:.32;stroke:var(--c);}
.odo-tooth.on .odo-num{color:var(--c);}
.odo-x{display:none;stroke:var(--c);stroke-width:3;stroke-linecap:round;}
.odo-tooth.on[data-status="extraction"] .odo-x{display:block;}
.odo-tooth.on[data-status="extraction"] .odo-path{fill-opacity:.12;}
/* marks left by earlier visits */
.odo-hist{display:flex;gap:2px;height:5px;align-items:center;justify-content:center;}
.odo-hist i{width:5px;height:5px;border-radius:50%;display:block;}

/* selected list */
.odo-chip{display:grid;grid-template-columns:auto 1fr auto;gap:.5rem;align-items:center;
    background:#fff;border:1px solid #e6e9ee;border-left:3px solid var(--c);
    border-radius:.6rem;padding:.5rem .6rem;}
.odo-chip-no{display:inline-flex;align-items:center;justify-content:center;min-width:2.1rem;height:2.1rem;
    border-radius:.5rem;background:var(--c);color:#fff;font-weight:700;font-size:.85rem;}
.odo-chip-meta{font-size:.72rem;color:#8b97a5;line-height:1.25;}
.odo-chip-fields{grid-column:1/-1;display:grid;gap:.4rem;
    grid-template-columns:repeat(auto-fit,minmax(150px,1fr));}
.odo-empty{border:1px dashed #d5dde6;border-radius:.6rem;padding:.9rem;text-align:center;
    color:#8b97a5;font-size:.82rem;background:#fff;}
</style>
@endpush

{{-- tooth silhouettes, crown up / root down (the upper arch flips with CSS) --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs>
        <path id="odo-shape-incisor"  d="M12 4H28c1 0 2 1 2 2v15c0 5-2 8-4.5 9L21.5 52c-.5 4-2.5 4-3 0L14.5 30C12 29 10 26 10 21V6c0-1 1-2 2-2Z"/>
        <path id="odo-shape-canine"   d="M13 12c0-6 3-10 7-10s7 4 7 10v9c0 5-2 8-4 9l-2 23c-.3 4-1.7 4-2 0l-2-23c-2-1-4-4-4-9Z"/>
        <path id="odo-shape-premolar" d="M10 6c0-3 2-4 5-4h10c3 0 5 1 5 4v15c0 5-2 8-4.5 9L21.5 52c-.5 4-2.5 4-3 0L14.5 30C12 29 10 26 10 21Z"/>
        <path id="odo-shape-molar"    d="M7 5c0-2 2-3 5-3h16c3 0 5 1 5 3v17c0 5-2 8-5 9l-2 21c-.4 4-2.6 4-3 0l-1.5-18h-3L17 52c-.4 4-2.6 4-3 0l-2-21c-3-1-5-4-5-9Z"/>
    </defs>
</svg>
@endonce

<div class="odo-wrap" id="{{ $chartId }}-wrap">
    <div class="odo-head d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold" style="font-size:.92rem;">
                <i class="bi bi-grid-3x3-gap me-1 text-primary"></i>Diş sxemi
            </span>
            <span class="badge rounded-pill text-bg-primary" data-odo-count="{{ $chartId }}">
                {{ $selected->count() }}
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="d-flex gap-2" role="group" aria-label="Diş dəsti">
                <button type="button" class="btn btn-sm btn-outline-secondary odo-set active" data-odo-set="permanent">
                    Daimi<span class="badge rounded-pill text-bg-primary ms-2" data-odo-set-count hidden>0</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary odo-set" data-odo-set="primary">
                    Süd dişləri<span class="badge rounded-pill text-bg-primary ms-2" data-odo-set-count hidden>0</span>
                </button>
            </div>
            @if($mode === 'edit')
            <button type="button" class="btn btn-sm btn-outline-danger" data-odo-clear>
                <i class="bi bi-eraser me-1"></i>Təmizlə
            </button>
            @endif
        </div>
    </div>

    <div class="odo-body">
        {{-- Status pills: the marking colour in edit mode, and in both modes a
             filter that leaves only the teeth carrying that status lit up. --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="text-muted" style="font-size:.78rem;">
                {{ $mode === 'edit' ? 'Vəziyyət:' : 'Filtr:' }}
            </span>
            <div class="odo-palette" data-odo-palette>
                @if($mode !== 'edit')
                <button type="button" class="odo-pill active" style="--c:#6c757d" data-odo-status="">
                    Hamısı
                </button>
                @endif
                @foreach($T::STATUSES as $key => $s)
                <button type="button"
                        class="odo-pill {{ $mode === 'edit' && $key === $T::DEFAULT_STATUS ? 'active' : '' }}"
                        style="--c:{{ $s['color'] }}"
                        data-odo-status="{{ $key }}">
                    <span class="dot"></span>{{ $s['label'] }}
                    <span class="odo-pill-count" data-odo-pill-count hidden>0</span>
                    <i class="bi bi-funnel-fill odo-pill-funnel"></i>
                </button>
                @endforeach
            </div>
        </div>
        <div class="text-muted mb-2" style="font-size:.75rem;">
            <i class="bi bi-info-circle me-1"></i>
            @if($mode === 'edit')
                Vəziyyəti seçin — həmin vəziyyətdəki dişlər sxemdə önə çıxır, qalanları solğunlaşır.
                Sonra dişə klikləyin ki, o vəziyyət tətbiq olunsun; bir neçə dişi birdən seçmək üçün
                siçanı basılı saxlayaraq sürüşdürün. Hamısını yenidən görmək üçün eyni düyməyə
                ikinci dəfə klikləyin.
            @else
                Vəziyyətə klikləyin — yalnız həmin vəziyyətdəki dişlər qalır.
                «Hamısı» filtri sıfırlayır.
            @endif
        </div>

        {{-- chart --}}
        <div class="odo-scroll">
            <div class="odo-chart" id="{{ $chartId }}" data-odo-chart data-set="permanent" data-mode="{{ $mode }}">
                <div class="odo-sides mb-1"><span>Sağ</span><span>Sol</span></div>

                @foreach(['upper', 'lower'] as $jaw)
                    @if($jaw === 'lower')
                        <div class="odo-occlusal"></div>
                    @else
                        <div class="odo-jaw mb-1">Yuxarı çənə</div>
                    @endif

                    @foreach(['permanent', 'primary'] as $set)
                    <div class="odo-row {{ $jaw }}" data-odo-row="{{ $set }}" @if($set === 'primary') hidden @endif>
                        @foreach($T::LAYOUT[$set][$jaw] as $halfIndex => $half)
                            @if($halfIndex === 1)<div class="odo-mid"></div>@endif
                            @php
                                $count  = count($half);
                                $center = ($count * 2 - 1) / 2;   // both halves make up one arch
                            @endphp
                            @foreach($half as $i => $number)
                                @php
                                    $mark   = $selected->get($number);
                                    $status = $mark['status'] ?? null;
                                    $shape  = $T::shape($number);
                                    // gentle arch: the further from the midline, the lower the tooth sits
                                    $pos    = $halfIndex === 0 ? $i : ($count * 2 - 1 - $i);
                                    $t      = ($pos - $center) / $center;
                                    $arc    = round(12 * $t * $t, 1);
                                    $past   = $history[$number] ?? [];
                                    $title  = $number . ' — ' . $T::quadrantLabel($number)
                                              . ' · ' . ($shapeNames[$shape] ?? '');
                                    if ($mark) { $title .= "\n" . $T::statusLabel($status); }
                                    foreach ($past as $h) {
                                        $title .= "\n" . $h['date'] . ': ' . $T::statusLabel($h['status'] ?? null);
                                    }
                                @endphp
                                <button type="button"
                                        class="odo-tooth {{ $jaw }} {{ $mark ? 'on' : '' }}"
                                        style="transform:translateY({{ $jaw === 'upper' ? $arc : -$arc }}px);--c:{{ $mark ? $T::statusColor($status) : '#adb5bd' }}"
                                        data-odo-tooth="{{ $number }}"
                                        data-jaw="{{ $jaw }}"
                                        data-quadrant="{{ $T::quadrantLabel($number) }}"
                                        data-shape-name="{{ $shapeNames[$shape] ?? '' }}"
                                        @if($status) data-status="{{ $status }}" @endif
                                        @if($mode !== 'edit') disabled @endif
                                        title="{{ $title }}"
                                        aria-label="{{ $number }} {{ $T::quadrantLabel($number) }}">
                                    <span class="odo-num">{{ $number }}</span>
                                    <svg class="odo-svg" viewBox="0 0 40 58">
                                        <use class="odo-path" href="#odo-shape-{{ $shape }}"></use>
                                        <g class="odo-x">
                                            <line x1="8" y1="10" x2="32" y2="48"></line>
                                            <line x1="32" y1="10" x2="8" y2="48"></line>
                                        </g>
                                    </svg>
                                    <span class="odo-hist">
                                        @foreach(array_slice($past, 0, 4) as $h)
                                            <i style="background:{{ $T::statusColor($h['status'] ?? null) }}"></i>
                                        @endforeach
                                    </span>
                                </button>
                            @endforeach
                        @endforeach
                    </div>
                    @endforeach

                    @if($jaw === 'lower')
                        <div class="odo-jaw mt-1">Aşağı çənə</div>
                    @endif
                @endforeach
            </div>
        </div>

        @if($mode === 'edit')
        {{-- quick add by number --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
            <div class="input-group input-group-sm" style="max-width:240px;">
                <span class="input-group-text">Diş №</span>
                <input type="text" inputmode="numeric" class="form-control" placeholder="16, 24, 36…"
                       data-odo-quick-input maxlength="20">
                <button type="button" class="btn btn-primary" data-odo-quick-add title="Əlavə et">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
            <span class="text-muted" style="font-size:.72rem;">
                Nömrəni yazıb <kbd>+</kbd> düyməsinə basın — vergüllə bir neçəsini də yazmaq olar.
            </span>
        </div>

        {{-- selected teeth --}}
        <div class="mt-3">
            <div class="fw-semibold mb-2" style="font-size:.82rem;">Seçilmiş dişlər</div>
            <div class="row g-2" data-odo-list></div>
            <div class="odo-empty mt-1" data-odo-empty @if($selected->isNotEmpty()) hidden @endif>
                Hələ diş seçilməyib — yuxarıdakı sxemdən dişə klikləyin.
            </div>
        </div>
        @endif
    </div>
</div>

@if($mode === 'edit')
<template data-odo-template>
    <div class="col-md-6">
        <div class="odo-chip">
            <span class="odo-chip-no">__NO__</span>
            <div>
                <div class="odo-chip-title fw-semibold" style="font-size:.82rem;">__QUADRANT__</div>
                <div class="odo-chip-meta">__SHAPE__</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger border-0" data-odo-remove
                    title="Seçimdən çıxar"><i class="bi bi-x-lg"></i></button>
            <div class="odo-chip-fields">
                <select class="form-select form-select-sm" data-odo-status-select
                        name="{{ $inputName }}[__NO__][status]">
                    @foreach($T::STATUSES as $key => $s)
                    <option value="{{ $key }}">{{ $s['label'] }}</option>
                    @endforeach
                </select>
                <input type="text" class="form-control form-control-sm" maxlength="255"
                       data-odo-note placeholder="Qeyd (ixtiyari) — məs. dərin karies"
                       name="{{ $inputName }}[__NO__][note]">
            </div>
        </div>
    </div>
</template>

<script type="application/json" data-odo-initial="{{ $chartId }}">@json($selected->values())</script>
@endif

@once
@push('scripts')
<script>
(function () {
    const STATUSES = @json(\App\Models\PatientVisitTooth::STATUSES);
    const DEFAULT_STATUS = @json(\App\Models\PatientVisitTooth::DEFAULT_STATUS);

    document.querySelectorAll('[data-odo-chart]').forEach(initChart);

    function initChart(chart) {
        const wrap    = chart.closest('.odo-wrap');
        const editing = chart.dataset.mode === 'edit';
        const counter = document.querySelector('[data-odo-count="' + chart.id + '"]');

        // ── permanent / primary switch — available in both modes ───────────
        // FDI puts the primary set in the 5x-8x range.
        function setOf(el) { return Number(el.dataset.odoTooth) >= 51 ? 'primary' : 'permanent'; }
        function otherSet(set) { return set === 'primary' ? 'permanent' : 'primary'; }

        function showSet(set) {
            wrap.querySelectorAll('[data-odo-set]').forEach(function (b) {
                b.classList.toggle('active', b.dataset.odoSet === set);
            });
            chart.dataset.set = set;
            chart.querySelectorAll('[data-odo-row]').forEach(function (row) {
                row.hidden = row.dataset.odoRow !== set;
            });
        }

        /** Marked teeth in one set, optionally only those carrying `status`. */
        function markedIn(set, status) {
            let n = 0;
            allTeeth().forEach(function (el) {
                if (setOf(el) !== set || !el.dataset.status) return;
                if (status && el.dataset.status !== status) return;
                n++;
            });
            return n;
        }

        /** Open on the set that actually holds marks, so nothing hides off-screen. */
        function autoSelectSet(status) {
            const current = chart.dataset.set;
            const other   = otherSet(current);
            if (!markedIn(current, status) && markedIn(other, status)) {
                showSet(other);
            }
        }

        wrap.querySelectorAll('[data-odo-set]').forEach(function (btn) {
            btn.addEventListener('click', function () { showSet(btn.dataset.odoSet); });
        });

        // ── status pills — filter in both modes, marking colour when editing ─
        const palette = wrap.querySelector('[data-odo-palette]');
        const pills   = palette ? palette.querySelectorAll('[data-odo-status]') : [];
        let brush = DEFAULT_STATUS;
        let filter = null;

        function allTeeth() {
            return chart.querySelectorAll('[data-odo-tooth]');
        }

        /** Leave only the teeth carrying the filtered status lit up. */
        function applyFilter() {
            chart.classList.toggle('odo-filtering', !!filter);
            allTeeth().forEach(function (el) {
                el.classList.toggle('odo-dim', !!filter && el.dataset.status !== filter);
            });
        }

        /** How many teeth carry each status — shown on the pills and set buttons. */
        function updateCounts() {
            const tally = {};
            allTeeth().forEach(function (el) {
                if (el.dataset.status) {
                    tally[el.dataset.status] = (tally[el.dataset.status] || 0) + 1;
                }
            });
            pills.forEach(function (pill) {
                const badge = pill.querySelector('[data-odo-pill-count]');
                if (!badge) return;
                const n = tally[pill.dataset.odoStatus] || 0;
                badge.textContent = n;
                badge.hidden = n === 0;
            });
            wrap.querySelectorAll('[data-odo-set]').forEach(function (btn) {
                const badge = btn.querySelector('[data-odo-set-count]');
                if (!badge) return;
                const n = markedIn(btn.dataset.odoSet);
                badge.textContent = n;
                badge.hidden = n === 0;
            });
        }

        function paintPills() {
            pills.forEach(function (pill) {
                const status = pill.dataset.odoStatus;
                pill.classList.toggle('odo-filtering', !!filter && status === filter);
                // editing: the highlighted pill is the colour about to be applied.
                // read-only: it is simply the active filter ('' = Hamısı).
                pill.classList.toggle('active', editing ? status === brush : status === (filter || ''));
            });
        }

        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                const status = pill.dataset.odoStatus;

                if (status && editing) brush = status;
                // clicking the pill that is already filtering clears the filter
                filter = (!status || filter === status) ? null : status;

                paintPills();
                applyFilter();
                // the matching teeth may all sit in the set that is not on screen
                if (filter) autoSelectSet(filter);
            });
        });

        updateCounts();
        paintPills();
        autoSelectSet();

        if (!editing) return;

        const list  = wrap.querySelector('[data-odo-list]');
        const empty = wrap.querySelector('[data-odo-empty]');
        const tpl   = document.querySelector('[data-odo-template]');

        const marks = new Map();   // toothNumber -> { status, note }

        function toothEl(no) {
            return chart.querySelector('[data-odo-tooth="' + no + '"]');
        }

        function chipFor(no) {
            return list.querySelector('[data-odo-chip="' + no + '"]');
        }

        function paint(no) {
            const el = toothEl(no);
            if (!el) return;
            const mark = marks.get(no);
            el.classList.toggle('on', !!mark);
            el.style.setProperty('--c', mark ? (STATUSES[mark.status] || {}).color || '#6c757d' : '#adb5bd');
            if (mark) { el.dataset.status = mark.status; } else { delete el.dataset.status; }
        }

        function tintChip(node, status) {
            const color = (STATUSES[status] || {}).color || '#6c757d';
            node.querySelector('.odo-chip').style.setProperty('--c', color);
            node.querySelector('.odo-chip-no').style.background = color;
        }

        function addChip(no, mark) {
            const node = tpl.content.firstElementChild.cloneNode(true);
            node.innerHTML = node.innerHTML.split('__NO__').join(no);
            node.dataset.odoChip = no;

            const el = toothEl(no);
            node.querySelector('.odo-chip-no').textContent = no;
            node.querySelector('.odo-chip-title').textContent = (el && el.dataset.quadrant) || '';
            node.querySelector('.odo-chip-meta').textContent = (el && el.dataset.shapeName) || '';

            const select = node.querySelector('[data-odo-status-select]');
            select.value = mark.status;
            select.addEventListener('change', function () {
                marks.get(no).status = select.value;
                tintChip(node, select.value);
                paint(no);
                refresh();
            });

            const note = node.querySelector('[data-odo-note]');
            note.value = mark.note || '';
            note.addEventListener('input', function () { marks.get(no).note = note.value; });

            node.querySelector('[data-odo-remove]').addEventListener('click', function () { unset(no); });

            tintChip(node, mark.status);

            // keep the list in the same order the chart reads
            const after = Array.prototype.find.call(list.children, function (c) {
                return Number(c.dataset.odoChip) > no;
            });
            list.insertBefore(node, after || null);
        }

        function refresh() {
            if (counter) counter.textContent = marks.size;
            if (empty) empty.hidden = marks.size > 0;
            updateCounts();
            applyFilter();
        }

        function set(no, status) {
            if (marks.has(no)) {
                marks.get(no).status = status;
                const chip = chipFor(no);
                if (chip) {
                    const sel = chip.querySelector('[data-odo-status-select]');
                    sel.value = status;
                    tintChip(chip, status);
                }
            } else {
                marks.set(no, { status: status, note: '' });
                addChip(no, marks.get(no));
            }
            paint(no);
            refresh();
        }

        function unset(no) {
            marks.delete(no);
            const chip = chipFor(no);
            if (chip) chip.remove();
            paint(no);
            refresh();
        }

        function toggle(no) {
            const mark = marks.get(no);
            if (mark && mark.status === brush) { unset(no); return 'off'; }
            set(no, brush);
            return 'on';
        }

        // ── click to toggle, mouse-drag to paint a run of teeth ────────────
        // Touch is left to the browser so the chart can still be swiped
        // sideways; a tap arrives as a plain click.
        let dragMode = null;
        let handledByMouse = false;

        chart.addEventListener('pointerdown', function (e) {
            const el = e.target.closest('[data-odo-tooth]');
            if (!el || e.pointerType !== 'mouse') return;
            e.preventDefault();
            handledByMouse = true;
            dragMode = toggle(Number(el.dataset.odoTooth));
        });

        // covers taps and, since these are buttons, Enter / Space as well
        chart.addEventListener('click', function (e) {
            const el = e.target.closest('[data-odo-tooth]');
            if (!el) return;
            if (handledByMouse) { handledByMouse = false; return; }
            toggle(Number(el.dataset.odoTooth));
        });

        chart.addEventListener('pointermove', function (e) {
            if (!dragMode) return;
            const under = document.elementFromPoint(e.clientX, e.clientY);
            const el = under && under.closest('[data-odo-tooth]');
            if (!el || el.closest('[data-odo-row]').hidden) return;
            const no = Number(el.dataset.odoTooth);
            const mark = marks.get(no);
            if (dragMode === 'on') {
                if (!mark || mark.status !== brush) set(no, brush);
            } else if (mark) {
                unset(no);
            }
        });

        ['pointerup', 'pointercancel'].forEach(function (ev) {
            document.addEventListener(ev, function () { dragMode = null; });
        });

        // ── quick add by number ────────────────────────────────────────────
        const quickInput = wrap.querySelector('[data-odo-quick-input]');
        function quickAdd() {
            const numbers = (quickInput.value.match(/\d{2}/g) || []).map(Number);
            const known = numbers.filter(function (n) { return !!toothEl(n); });
            known.forEach(function (n) { set(n, brush); });
            quickInput.classList.toggle('is-invalid', known.length !== numbers.length || !numbers.length);
            if (known.length === numbers.length && numbers.length) quickInput.value = '';
        }
        const quickBtn = wrap.querySelector('[data-odo-quick-add]');
        if (quickBtn) quickBtn.addEventListener('click', quickAdd);
        if (quickInput) {
            quickInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); quickAdd(); }
            });
        }

        // ── clear ──────────────────────────────────────────────────────────
        const clearBtn = wrap.querySelector('[data-odo-clear]');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (marks.size && !confirm('Bütün seçilmiş dişlər silinsin?')) return;
                Array.from(marks.keys()).forEach(unset);
            });
        }

        // ── hydrate from the server ────────────────────────────────────────
        const seed = document.querySelector('[data-odo-initial="' + chart.id + '"]');
        if (seed) {
            JSON.parse(seed.textContent || '[]').forEach(function (row) {
                const no = Number(row.tooth_number);
                if (!toothEl(no)) return;
                marks.set(no, { status: row.status || DEFAULT_STATUS, note: row.note || '' });
                addChip(no, marks.get(no));
                paint(no);
            });
            refresh();
            // marks only on primary teeth mean the doctor was working on that set
            autoSelectSet();
        }
    }
})();
</script>
@endpush
@endonce

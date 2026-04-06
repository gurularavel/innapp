@extends('layouts.doctor')

@section('title', 'Müştəri Profili')
@section('page-title', 'Müştəri Profili')

@section('content')
<div class="d-flex justify-content-end align-items-center mb-4 gap-2 flex-wrap">
    <a href="{{ route('panel.appointments.create') }}?patient_id={{ $patient->id }}"
       class="btn btn-success btn-sm">
        <i class="bi bi-calendar-plus me-1"></i><span class="d-none d-sm-inline">Randevu Əlavə Et</span><span class="d-sm-none">Randevu</span>
    </a>
    <a href="{{ route('panel.patients.visits.create', $patient) }}" class="btn btn-outline-info btn-sm">
        <i class="bi bi-clock-history me-1"></i><span class="d-none d-sm-inline">Ziyarət Əlavə Et</span><span class="d-sm-none">Ziyarət</span>
    </a>
    <a href="{{ route('panel.patients.edit', $patient) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil me-1"></i>Düzəlt
    </a>
    <a href="{{ route('panel.patients.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Geri
    </a>
</div>

<div class="row g-4">
    {{-- Patient Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                @if($patient->photo_url)
                    <img src="{{ $patient->photo_url }}" alt="{{ $patient->full_name }}"
                         class="rounded-circle mx-auto d-block mb-3"
                         style="width:80px;height:80px;object-fit:cover;border:3px solid #e9ecef;">
                @else
                    <div class="rounded-circle bg-success bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width:80px;height:80px;">
                        <i class="bi bi-person fs-2 text-success"></i>
                    </div>
                @endif
                <h5 class="fw-bold mb-1">{{ $patient->full_name }}</h5>
                @php $genderLabels = ['male' => 'Kişi', 'female' => 'Qadın', 'other' => 'Digər']; @endphp
                <div class="text-muted">{{ $genderLabels[$patient->gender] ?? '—' }}</div>
            </div>
            <div class="card-body border-top pt-3">
                <div class="mb-3">
                    <div class="text-muted small">Telefon</div>
                    <div class="fw-medium">{{ $patient->phone ?? '—' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Doğum Tarixi</div>
                    <div class="fw-medium">
                        {{ $patient->birth_date ? $patient->birth_date->format('d.m.Y') : '—' }}
                        @if($patient->birth_date)
                            <span class="text-muted small">({{ $patient->birth_date->age }} yaş)</span>
                        @endif
                    </div>
                </div>
                @if($patient->weight)
                <div class="mb-3">
                    <div class="text-muted small">Çəki</div>
                    <div class="fw-medium">{{ $patient->weight }} kg</div>
                </div>
                @endif
                @if($patient->blood_type)
                <div class="mb-3">
                    <div class="text-muted small">Qan Qrupu</div>
                    <div class="fw-medium">{{ $patient->blood_type }}</div>
                </div>
                @endif
                @if($patient->marital_status)
                <div class="mb-3">
                    <div class="text-muted small">Ailə Vəziyyəti</div>
                    <div class="fw-medium">{{ $patient->marital_status_label }}</div>
                </div>
                @endif
                <div class="mb-3">
                    <div class="text-muted small">Qeydiyyat Tarixi</div>
                    <div class="fw-medium">{{ $patient->created_at->format('d.m.Y') }}</div>
                </div>
                @if($patient->notes)
                <div>
                    <div class="text-muted small">Qeydlər</div>
                    <div class="fw-medium small mt-1 p-2 bg-light rounded">{{ $patient->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Column: Tabs --}}
    <div class="col-lg-8">

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-0" id="patientTabs" style="border-bottom:none;">
            <li class="nav-item">
                <button class="nav-link active fw-medium" id="tab-history" data-bs-toggle="tab" data-bs-target="#pane-history" type="button">
                    <i class="bi bi-clock-history me-1"></i>Tibb Tarixi
                    <span class="badge bg-info ms-1">{{ $patient->visits->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-medium" id="tab-appointments" data-bs-toggle="tab" data-bs-target="#pane-appointments" type="button">
                    <i class="bi bi-calendar3 me-1"></i>Randevular
                    <span class="badge bg-secondary ms-1">{{ $patient->appointments->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- ===== VISIT HISTORY ===== --}}
            <div class="tab-pane fade show active" id="pane-history">
                <div class="card border-0 shadow-sm" style="border-top-left-radius:0;">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Tibb Tarixi</span>
                        <a href="{{ route('panel.patients.visits.create', $patient) }}" class="btn btn-sm btn-info text-white">
                            <i class="bi bi-plus-lg me-1"></i>Yeni Ziyarət
                        </a>
                    </div>

                    @if($patient->visits->isEmpty())
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-25"></i>
                        Tibb tarixi tapılmadı
                        <div class="mt-2">
                            <a href="{{ route('panel.patients.visits.create', $patient) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-plus-lg me-1"></i>İlk ziyarəti əlavə et
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="accordion accordion-flush" id="visitsAccordion">
                        @foreach($patient->visits as $visit)
                        <div class="accordion-item border-bottom">
                            <div class="accordion-header d-flex align-items-center pe-2">
                                {{-- Collapsible trigger --}}
                                <button class="accordion-button collapsed py-3 flex-grow-1"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#visit-body-{{ $visit->id }}"
                                        aria-expanded="false">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="text-muted small" style="min-width:110px;">
                                            <i class="bi bi-calendar3 me-1 text-info"></i>{{ $visit->visited_at->format('d.m.Y') }}
                                            <span class="text-muted">{{ $visit->visited_at->format('H:i') }}</span>
                                        </span>
                                        <span class="fw-semibold text-dark">
                                            {{ $visit->title ?: '—' }}
                                        </span>
                                        @if($visit->files->isNotEmpty())
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border" style="font-size:.7rem;">
                                            <i class="bi bi-paperclip me-1"></i>{{ $visit->files->count() }}
                                        </span>
                                        @endif
                                    </div>
                                </button>
                                {{-- Action buttons always visible --}}
                                <div class="d-flex gap-1 ms-2 flex-shrink-0">
                                    <a href="{{ route('panel.patients.visits.edit', [$patient, $visit]) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('panel.patients.visits.destroy', [$patient, $visit]) }}"
                                          onsubmit="return confirm('Bu ziyarəti silmək istəyirsiniz?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div id="visit-body-{{ $visit->id }}" class="accordion-collapse collapse"
                                 data-bs-parent="">
                                <div class="accordion-body pt-2 pb-3">
                                    {{-- Notes --}}
                                    @if($visit->notes)
                                    <div class="small text-muted bg-light rounded p-2 mb-3">{{ $visit->notes }}</div>
                                    @endif

                                    {{-- Files --}}
                                    @if($visit->files->isNotEmpty())
                                    @php $imgIndex = 0; @endphp
                                    <div class="row g-2">
                                        @foreach($visit->files as $file)
                                        <div class="col-6 col-sm-4 col-md-3">
                                            @if($file->is_image)
                                            @php $imgIndex++ @endphp
                                            <img src="{{ $file->url }}"
                                                 alt="{{ $file->original_name }}"
                                                 class="img-fluid rounded border visit-img"
                                                 style="width:100%;height:90px;object-fit:cover;cursor:zoom-in;"
                                                 data-src="{{ $file->url }}"
                                                 data-name="{{ $file->original_name }}"
                                                 data-gallery="visit-{{ $visit->id }}"
                                                 data-index="{{ $imgIndex - 1 }}">
                                            @else
                                            <a href="{{ $file->url }}" target="_blank"
                                               class="d-flex align-items-center gap-2 p-2 border rounded text-decoration-none text-dark bg-white h-100">
                                                <i class="bi bi-file-earmark-pdf text-danger fs-4 flex-shrink-0"></i>
                                                <span class="small text-truncate">{{ $file->original_name }}</span>
                                            </a>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @elseif(!$visit->notes)
                                    <div class="text-muted small">Məlumat yoxdur.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Gallery Lightbox --}}
            <div id="glBox" aria-hidden="true">
                <div id="glBackdrop"></div>
                <div id="glWrap">
                    {{-- Toolbar --}}
                    <div id="glToolbar">
                        <span id="glCounter" class="text-white small opacity-75"></span>
                        <span id="glName" class="text-white small opacity-75 text-truncate" style="max-width:200px;"></span>
                        <div class="d-flex gap-2 ms-auto">
                            <button id="glZoomOut" class="gl-btn" title="Kiçilt"><i class="bi bi-zoom-out"></i></button>
                            <button id="glZoomReset" class="gl-btn" title="Orijinal ölçü"><i class="bi bi-aspect-ratio"></i></button>
                            <button id="glZoomIn"  class="gl-btn" title="Böyüt"><i class="bi bi-zoom-in"></i></button>
                            <button id="glClose"   class="gl-btn" title="Bağla"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </div>
                    {{-- Prev / Next --}}
                    <button id="glPrev" class="gl-nav gl-nav-prev"><i class="bi bi-chevron-left"></i></button>
                    <button id="glNext" class="gl-nav gl-nav-next"><i class="bi bi-chevron-right"></i></button>
                    {{-- Image stage --}}
                    <div id="glStage">
                        <img id="glImg" src="" alt="" draggable="false">
                    </div>
                    {{-- Thumbnails --}}
                    <div id="glThumbBar">
                        <div id="glThumbs"></div>
                    </div>
                </div>
            </div>

            {{-- ===== APPOINTMENTS ===== --}}
            <div class="tab-pane fade" id="pane-appointments">
                <div class="card border-0 shadow-sm" style="border-top-left-radius:0;">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Randevu Tarixçəsi</span>
                        <span class="badge bg-secondary">{{ $patient->appointments->count() }}</span>
                    </div>
                    @php
                        $apts   = $patient->appointments()->with('treatmentType')->latest('scheduled_at')->get();
                        $badges = ['pending'=>'warning','confirmed'=>'info','completed'=>'success','cancelled'=>'danger'];
                        $labels = ['pending'=>'Gözləyir','confirmed'=>'Təsdiqləndi','completed'=>'Tamamlandı','cancelled'=>'Ləğv edildi'];
                    @endphp

                    {{-- Mobile --}}
                    <div class="d-md-none">
                        @forelse($apts as $apt)
                        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-medium">{{ $apt->scheduled_at->format('d.m.Y H:i') }}</div>
                                <div class="text-muted" style="font-size:.78rem;">{{ $apt->treatmentType?->name ?? '—' }} · {{ $apt->duration_minutes }} dəq</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $badges[$apt->status] ?? 'secondary' }}">{{ $labels[$apt->status] ?? $apt->status }}</span>
                                <a href="{{ route('panel.appointments.show', $apt) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">Randevu tapılmadı</div>
                        @endforelse
                    </div>

                    {{-- Desktop --}}
                    <div class="card-body p-0 d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Tarix</th><th>Müalicə</th><th>Müddət</th><th>Status</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse($apts as $apt)
                                    <tr>
                                        <td class="text-muted small">{{ $apt->scheduled_at->format('d.m.Y H:i') }}</td>
                                        <td>{{ $apt->treatmentType?->name ?? '—' }}</td>
                                        <td class="text-muted small">{{ $apt->duration_minutes }} dəq</td>
                                        <td>
                                            <span class="badge bg-{{ $badges[$apt->status] ?? 'secondary' }}">
                                                {{ $labels[$apt->status] ?? $apt->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('panel.appointments.show', $apt) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">Randevu tapılmadı</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- end tab-content --}}
    </div>
</div>
@endsection

@push('styles')
<style>
/* Accordion */
.accordion-button:not(.collapsed) { background:#f0f8ff; color:inherit; box-shadow:none; }
.accordion-button::after { flex-shrink:0; }
.visit-img:hover { opacity:.85; transition:opacity .15s; }

/* ===== Gallery Lightbox ===== */
#glBox {
    display:none;
    position:fixed;inset:0;z-index:9999;
}
#glBox.gl-open { display:flex; }
#glBackdrop {
    position:absolute;inset:0;
    background:rgba(0,0,0,.92);
}
#glWrap {
    position:relative;
    display:flex;flex-direction:column;
    width:100%;height:100%;
}
/* Toolbar */
#glToolbar {
    display:flex;align-items:center;gap:10px;
    padding:8px 12px;
    background:rgba(0,0,0,.4);
    z-index:2;flex-shrink:0;
}
.gl-btn {
    background:rgba(255,255,255,.12);
    border:none;color:#fff;
    width:34px;height:34px;border-radius:6px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:1rem;transition:background .15s;
}
.gl-btn:hover { background:rgba(255,255,255,.28); }
/* Nav arrows */
.gl-nav {
    position:absolute;top:50%;transform:translateY(-50%);
    background:rgba(255,255,255,.13);
    border:none;color:#fff;
    width:44px;height:60px;border-radius:8px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:1.4rem;z-index:3;
    transition:background .15s;
}
.gl-nav:hover { background:rgba(255,255,255,.3); }
.gl-nav-prev { left:10px; }
.gl-nav-next { right:10px; }
.gl-nav.gl-hidden { display:none; }
/* Stage */
#glStage {
    flex:1;overflow:hidden;
    display:flex;align-items:center;justify-content:center;
    position:relative;
}
#glImg {
    max-width:100%;max-height:100%;
    object-fit:contain;
    transform-origin:center center;
    transition:transform .12s ease;
    will-change:transform;
    user-select:none;
}
#glImg.gl-grab  { cursor:grab; }
#glImg.gl-grabbing { cursor:grabbing; }
/* Thumbnails */
#glThumbBar {
    flex-shrink:0;
    background:rgba(0,0,0,.5);
    padding:6px 0;
    overflow-x:auto;
    white-space:nowrap;
    text-align:center;
    scrollbar-width:thin;
    scrollbar-color:rgba(255,255,255,.2) transparent;
}
#glThumbs img {
    display:inline-block;
    width:56px;height:42px;
    object-fit:cover;
    border-radius:4px;
    margin:0 3px;
    opacity:.5;
    cursor:pointer;
    border:2px solid transparent;
    transition:opacity .15s,border-color .15s;
    flex-shrink:0;
}
#glThumbs img.gl-active {
    opacity:1;
    border-color:#0d6efd;
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    /* ── collect galleries ── */
    const galleries = {};   // { 'visit-123': [{src,name}, ...] }

    document.querySelectorAll('.visit-img').forEach(img => {
        const key = img.dataset.gallery;
        if (!galleries[key]) galleries[key] = [];
        galleries[key].push({ src: img.dataset.src, name: img.dataset.name });
    });

    /* ── elements ── */
    const box      = document.getElementById('glBox');
    const glImg    = document.getElementById('glImg');
    const glName   = document.getElementById('glName');
    const glCounter= document.getElementById('glCounter');
    const glThumbs = document.getElementById('glThumbs');
    const btnPrev  = document.getElementById('glPrev');
    const btnNext  = document.getElementById('glNext');
    const btnClose = document.getElementById('glClose');
    const btnZoomIn  = document.getElementById('glZoomIn');
    const btnZoomOut = document.getElementById('glZoomOut');
    const btnReset   = document.getElementById('glZoomReset');
    const backdrop   = document.getElementById('glBackdrop');

    /* ── state ── */
    let items  = [];
    let index  = 0;
    let scale  = 1;
    let tx = 0, ty = 0;     // pan offset
    let dragging = false, dragStartX, dragStartY, dragTx, dragTy;

    /* ── open / close ── */
    function open(galleryKey, startIndex) {
        items = galleries[galleryKey] || [];
        if (!items.length) return;
        index = startIndex;
        scale = 1; tx = 0; ty = 0;
        buildThumbs();
        go(index);
        box.classList.add('gl-open');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        box.classList.remove('gl-open');
        document.body.style.overflow = '';
        glImg.src = '';
    }

    function go(i) {
        index = i;
        const item = items[index];
        scale = 1; tx = 0; ty = 0;
        applyTransform();
        glImg.src   = item.src;
        glName.textContent = item.name;
        glCounter.textContent = (index + 1) + ' / ' + items.length;
        btnPrev.classList.toggle('gl-hidden', items.length <= 1);
        btnNext.classList.toggle('gl-hidden', items.length <= 1);
        // thumbs highlight
        glThumbs.querySelectorAll('img').forEach((t, idx) => {
            t.classList.toggle('gl-active', idx === index);
        });
        // scroll active thumb into view
        const active = glThumbs.querySelector('img.gl-active');
        if (active) active.scrollIntoView({ inline: 'center', behavior: 'smooth' });
    }

    function buildThumbs() {
        glThumbs.innerHTML = '';
        items.forEach((item, i) => {
            const t = document.createElement('img');
            t.src = item.src;
            t.alt = item.name;
            t.title = item.name;
            t.addEventListener('click', () => go(i));
            glThumbs.appendChild(t);
        });
    }

    /* ── zoom ── */
    const ZOOM_STEP = 0.25, ZOOM_MIN = 1, ZOOM_MAX = 5;

    function zoom(delta, cx, cy) {
        const newScale = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, scale + delta));
        if (newScale === scale) return;

        // zoom toward cursor/center
        const rect = glImg.getBoundingClientRect();
        const ox = (cx ?? rect.left + rect.width / 2)  - rect.left - rect.width  / 2;
        const oy = (cy ?? rect.top  + rect.height / 2) - rect.top  - rect.height / 2;
        tx += ox - ox * (newScale / scale);
        ty += oy - oy * (newScale / scale);

        scale = newScale;
        clampPan();
        applyTransform();
        updateCursor();
    }

    function clampPan() {
        if (scale <= 1) { tx = 0; ty = 0; return; }
        const rect = glImg.getBoundingClientRect();
        const maxX = (rect.width  * (scale - 1)) / 2;
        const maxY = (rect.height * (scale - 1)) / 2;
        tx = Math.max(-maxX, Math.min(maxX, tx));
        ty = Math.max(-maxY, Math.min(maxY, ty));
    }

    function applyTransform() {
        glImg.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`;
    }

    function updateCursor() {
        glImg.classList.toggle('gl-grab', scale > 1);
    }

    /* ── pan (drag) ── */
    glImg.addEventListener('mousedown', e => {
        if (scale <= 1) return;
        dragging = true;
        dragStartX = e.clientX; dragStartY = e.clientY;
        dragTx = tx; dragTy = ty;
        glImg.classList.add('gl-grabbing');
        e.preventDefault();
    });
    document.addEventListener('mousemove', e => {
        if (!dragging) return;
        tx = dragTx + (e.clientX - dragStartX);
        ty = dragTy + (e.clientY - dragStartY);
        clampPan();
        applyTransform();
    });
    document.addEventListener('mouseup', () => {
        if (!dragging) return;
        dragging = false;
        glImg.classList.remove('gl-grabbing');
    });

    /* ── touch pan/pinch ── */
    let lastPinchDist = null;
    glImg.addEventListener('touchstart', e => {
        if (e.touches.length === 2) {
            lastPinchDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
        } else if (e.touches.length === 1 && scale > 1) {
            dragging = true;
            dragStartX = e.touches[0].clientX;
            dragStartY = e.touches[0].clientY;
            dragTx = tx; dragTy = ty;
        }
    }, { passive: true });
    glImg.addEventListener('touchmove', e => {
        if (e.touches.length === 2 && lastPinchDist !== null) {
            const dist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            const cx = (e.touches[0].clientX + e.touches[1].clientX) / 2;
            const cy = (e.touches[0].clientY + e.touches[1].clientY) / 2;
            zoom((dist - lastPinchDist) * 0.02, cx, cy);
            lastPinchDist = dist;
            e.preventDefault();
        } else if (e.touches.length === 1 && dragging) {
            tx = dragTx + (e.touches[0].clientX - dragStartX);
            ty = dragTy + (e.touches[0].clientY - dragStartY);
            clampPan();
            applyTransform();
            e.preventDefault();
        }
    }, { passive: false });
    glImg.addEventListener('touchend', () => {
        lastPinchDist = null;
        dragging = false;
    });

    /* ── wheel zoom ── */
    document.getElementById('glStage').addEventListener('wheel', e => {
        e.preventDefault();
        zoom(e.deltaY < 0 ? ZOOM_STEP : -ZOOM_STEP, e.clientX, e.clientY);
    }, { passive: false });

    /* ── double-click toggle zoom ── */
    glImg.addEventListener('dblclick', e => {
        if (scale > 1) { scale = 1; tx = 0; ty = 0; applyTransform(); updateCursor(); }
        else zoom(1, e.clientX, e.clientY);
    });

    /* ── buttons ── */
    btnZoomIn.addEventListener('click',  () => zoom(ZOOM_STEP));
    btnZoomOut.addEventListener('click', () => zoom(-ZOOM_STEP));
    btnReset.addEventListener('click',   () => { scale = 1; tx = 0; ty = 0; applyTransform(); updateCursor(); });
    btnClose.addEventListener('click', close);
    backdrop.addEventListener('click', e => { if (e.target === backdrop) close(); });
    btnPrev.addEventListener('click', () => go((index - 1 + items.length) % items.length));
    btnNext.addEventListener('click', () => go((index + 1) % items.length));

    /* ── keyboard ── */
    document.addEventListener('keydown', e => {
        if (!box.classList.contains('gl-open')) return;
        if (e.key === 'Escape')      close();
        if (e.key === 'ArrowLeft')   go((index - 1 + items.length) % items.length);
        if (e.key === 'ArrowRight')  go((index + 1) % items.length);
        if (e.key === '+')           zoom(ZOOM_STEP);
        if (e.key === '-')           zoom(-ZOOM_STEP);
        if (e.key === '0')           { scale=1;tx=0;ty=0;applyTransform();updateCursor(); }
    });

    /* ── trigger on thumbnail click ── */
    document.querySelectorAll('.visit-img').forEach(img => {
        img.addEventListener('click', function () {
            open(this.dataset.gallery, parseInt(this.dataset.index));
        });
    });
})();
</script>
@endpush

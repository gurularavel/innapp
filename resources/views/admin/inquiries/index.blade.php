@extends('layouts.admin')

@section('title', 'Müraciətlər')
@section('page-title', 'Müraciətlər')

@section('content')
{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
                <div class="stat-label">Cəmi</div>
                <div class="stat-value">{{ $counts['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
                <div class="stat-label">Oxunmamış</div>
                <div class="stat-value text-danger">{{ $counts['unread'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
                <div class="stat-label">Statusu "Yeni"</div>
                <div class="stat-value text-primary">{{ $counts['new'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.inquiries.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="q" class="form-label fw-medium small">Axtarış</label>
                <input type="text" class="form-control form-control-sm" id="q" name="q" value="{{ request('q') }}" placeholder="Ad, e-poçt, telefon və ya mesaj">
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label fw-medium small">Növ</label>
                <select class="form-select form-select-sm" id="type" name="type">
                    <option value="">— Hamısı —</option>
                    @foreach(\App\Models\Inquiry::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label fw-medium small">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">— Hamısı —</option>
                    @foreach(\App\Models\Inquiry::STATUSES as $key => $meta)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search me-1"></i>Filtrele
                </button>
                <a href="{{ route('admin.inquiries.index') }}" class="btn btn-outline-secondary btn-sm" title="Sıfırla">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Saytdan gələn müraciətlər</h6>
        <span class="badge bg-secondary">{{ $inquiries->total() }} nəticə</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Göndərən</th>
                        <th>Əlaqə</th>
                        <th>Mesaj</th>
                        <th>Növ</th>
                        <th>Status</th>
                        <th>Tarix</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inquiry)
                    <tr class="{{ $inquiry->read_at ? '' : 'fw-semibold' }}">
                        <td class="text-muted small">{{ $inquiries->firstItem() + $loop->index }}</td>
                        <td>
                            @unless($inquiry->read_at)
                                <span class="badge rounded-pill bg-danger me-1" title="Oxunmayıb">&nbsp;</span>
                            @endunless
                            <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="text-decoration-none">
                                {{ $inquiry->display_name }}
                            </a>
                        </td>
                        <td class="small">
                            @if($inquiry->email)<div><i class="bi bi-envelope me-1 text-muted"></i>{{ $inquiry->email }}</div>@endif
                            @if($inquiry->phone)<div><i class="bi bi-telephone me-1 text-muted"></i>{{ $inquiry->phone }}</div>@endif
                        </td>
                        <td>
                            @if($inquiry->message)
                                <span title="{{ $inquiry->message }}" style="cursor:help;">{{ Str::limit($inquiry->message, 60) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $inquiry->type === \App\Models\Inquiry::TYPE_DEMO ? 'info text-dark' : 'secondary' }}">{{ $inquiry->type_label }}</span></td>
                        <td><span class="badge bg-{{ $inquiry->status_badge }}">{{ $inquiry->status_label }}</span></td>
                        <td class="text-muted small">{{ $inquiry->created_at->format('d.m.Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="btn btn-sm btn-outline-primary" title="Bax">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Müraciət tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($inquiries->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $inquiries->firstItem() }}–{{ $inquiries->lastItem() }} / {{ $inquiries->total() }} nəticə
        </div>
        {{ $inquiries->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

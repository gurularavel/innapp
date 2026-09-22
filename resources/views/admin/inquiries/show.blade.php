@extends('layouts.admin')

@section('title', 'Müraciət #' . $inquiry->id)
@section('page-title', 'Müraciət #' . $inquiry->id)

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.inquiries.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Siyahıya qayıt
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">{{ $inquiry->type_label }}</h6>
                <span class="badge bg-{{ $inquiry->status_badge }}">{{ $inquiry->status_label }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-medium">Ad və soyad</dt>
                    <dd class="col-sm-8">{{ $inquiry->name ?: '—' }}</dd>

                    <dt class="col-sm-4 text-muted fw-medium">E-poçt</dt>
                    <dd class="col-sm-8">
                        @if($inquiry->email)
                            <a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted fw-medium">Telefon</dt>
                    <dd class="col-sm-8">
                        @if($inquiry->phone)
                            <a href="tel:{{ $inquiry->phone }}">{{ $inquiry->phone }}</a>
                            <a href="{{ $inquiry->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success ms-2">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted fw-medium">Mesaj</dt>
                    <dd class="col-sm-8">
                        @if($inquiry->message)
                            <div class="bg-light rounded p-3" style="white-space: pre-wrap;">{{ $inquiry->message }}</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted fw-medium">Göndərilib</dt>
                    <dd class="col-sm-8">{{ $inquiry->created_at->format('d.m.Y H:i') }}</dd>

                    <dt class="col-sm-4 text-muted fw-medium">Oxunub</dt>
                    <dd class="col-sm-8">{{ $inquiry->read_at?->format('d.m.Y H:i') ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted fw-medium">IP</dt>
                    <dd class="col-sm-8 text-muted small">{{ $inquiry->ip ?: '—' }}</dd>

                    <dt class="col-sm-4 text-muted fw-medium">Brauzer</dt>
                    <dd class="col-sm-8 text-muted small text-break">{{ $inquiry->user_agent ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Status və qeyd</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="status" class="form-label fw-medium small">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                            @foreach(\App\Models\Inquiry::STATUSES as $key => $meta)
                                <option value="{{ $key }}" {{ old('status', $inquiry->status) === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="admin_note" class="form-label fw-medium small">Daxili qeyd</label>
                        <textarea class="form-control @error('admin_note') is-invalid @enderror" id="admin_note" name="admin_note" rows="5" placeholder="Zəng edildi, demo təyin olundu…">{{ old('admin_note', $inquiry->admin_note) }}</textarea>
                        @error('admin_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Yadda saxla
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-danger-subtle">
            <div class="card-body d-flex justify-content-between align-items-center">
                <span class="text-muted small">Müraciəti tamamilə sil</span>
                <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Silmək istədiyinizdən əminsiniz?">
                        <i class="bi bi-trash me-1"></i>Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

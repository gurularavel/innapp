@extends('layouts.admin')

@section('title', 'Promo Kodlar')
@section('page-title', 'Promo Kodlar')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Promo Kodlar Siyahısı</h6>
        <a href="{{ route('admin.promo-codes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Yeni Promo Kod
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kod</th>
                        <th>Promotor</th>
                        <th>Endirim</th>
                        <th>Komissiya</th>
                        <th>İstifadə</th>
                        <th>Bitmə</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promoCodes as $code)
                    <tr>
                        <td><span class="badge bg-info">{{ $code->code }}</span></td>
                        <td class="fw-medium">{{ $code->promoter?->full_name ?? '—' }}</td>
                        <td>
                            {{ $code->discount_type === 'percent'
                                ? rtrim(rtrim(number_format($code->discount_value, 2), '0'), '.') . '%'
                                : number_format($code->discount_value, 2) . ' ₼' }}
                        </td>
                        <td>
                            {{ $code->commission_type === 'percent'
                                ? rtrim(rtrim(number_format($code->commission_value, 2), '0'), '.') . '%'
                                : number_format($code->commission_value, 2) . ' ₼' }}
                        </td>
                        <td>{{ $code->used_count }}{{ $code->max_uses ? ' / ' . $code->max_uses : '' }}</td>
                        <td>{{ $code->expires_at ? $code->expires_at->format('d.m.Y') : '∞' }}</td>
                        <td>
                            @if($code->isUsable())
                                <span class="badge bg-success">Aktiv</span>
                            @elseif(!$code->is_active)
                                <span class="badge bg-danger">Deaktiv</span>
                            @else
                                <span class="badge bg-warning">Bitib</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('admin.promo-codes.edit', $code) }}" class="btn btn-sm btn-outline-primary" title="Düzəlt">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.promo-codes.destroy', $code) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Silmək istədiyinizdən əminsiniz?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-ticket-perforated fs-3 d-block mb-2"></i>
                            Promo kod tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($promoCodes->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $promoCodes->firstItem() }}–{{ $promoCodes->lastItem() }} / {{ $promoCodes->total() }} nəticə
        </div>
        {{ $promoCodes->links() }}
    </div>
    @endif
</div>
@endsection

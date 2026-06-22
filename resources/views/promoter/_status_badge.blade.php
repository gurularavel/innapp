@switch($status)
    @case('pending')
        <span class="badge bg-warning"><i class="bi bi-hourglass-split me-1"></i>Gözləyir</span>
        @break
    @case('available')
        <span class="badge bg-success"><i class="bi bi-wallet2 me-1"></i>Çıxarıla bilər</span>
        @break
    @case('paid')
        <span class="badge bg-info"><i class="bi bi-check-circle me-1"></i>Ödənilib</span>
        @break
    @case('cancelled')
        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ləğv</span>
        @break
    @default
        <span class="badge bg-secondary">{{ $status }}</span>
@endswitch

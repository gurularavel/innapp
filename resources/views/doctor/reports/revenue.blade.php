@extends('layouts.doctor')

@section('title', 'Gəlir Hesabatı')
@section('page-title', 'Gəlir Hesabatı')

@push('styles')
<style>
    .stat-card { border: none; border-radius: .75rem; }
    .stat-card .stat-icon { width: 48px; height: 48px; border-radius: .5rem; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    .chart-container { position: relative; height: 320px; }
    .tab-chart { display: none; }
    .tab-chart.active { display: block; }
    .service-row:last-child { border-bottom: none !important; }
    .progress-sm { height: 6px; }
</style>
@endpush

@section('content')

{{-- ── Summary Cards ─────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-calendar-week"></i>
                </div>
                <div>
                    <div class="text-muted small">Bu həftə</div>
                    <div class="fw-bold fs-5">{{ number_format($thisWeekRevenue, 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-calendar-month"></i>
                </div>
                <div>
                    <div class="text-muted small">Bu ay</div>
                    <div class="fw-bold fs-5">{{ number_format($thisMonthRevenue, 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-calendar-year"></i>
                </div>
                <div>
                    <div class="text-muted small">Bu il</div>
                    <div class="fw-bold fs-5">{{ number_format($thisYearRevenue, 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="text-muted small">Ümumi / Ortalama</div>
                    <div class="fw-bold fs-5">{{ number_format($totalRevenue, 2) }} ₼</div>
                    <div class="text-muted" style="font-size:.75rem">{{ number_format($avgRevenue, 2) }} ₼ / randevu ({{ $completedCount }})</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row ────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Main chart with tabs --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Gəlir Dinamikası</h6>
                <div class="d-flex align-items-center gap-2">
                    {{-- Year picker (shown only in monthly tab) --}}
                    <form method="GET" action="{{ route('doctor.reports.revenue') }}" id="year-form" class="d-none">
                        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" style="width:90px">
                            @foreach($availableYears as $yr)
                                <option value="{{ $yr }}" {{ $yr == $monthlyYear ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </form>
                    <ul class="nav nav-pills nav-sm" id="chart-tabs">
                        <li class="nav-item">
                            <button class="nav-link py-1 px-2 small active" data-tab="weekly">Həftəlik</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-2 small" data-tab="monthly">Aylıq</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-2 small" data-tab="annual">İllik</button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-chart active chart-container" id="chart-weekly">
                    <canvas id="canvasWeekly"></canvas>
                </div>
                <div class="tab-chart chart-container" id="chart-monthly">
                    <canvas id="canvasMonthly"></canvas>
                </div>
                <div class="tab-chart chart-container" id="chart-annual">
                    <canvas id="canvasAnnual"></canvas>
                </div>

                {{-- Per-tab appointment count info --}}
                <div class="mt-2 small text-muted text-end" id="tab-count-info"></div>
            </div>
        </div>
    </div>

    {{-- Pie chart: by service --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-pie-chart me-2 text-success"></i>Xidmət Növünə Görə</h6>
            </div>
            <div class="card-body d-flex flex-column">
                @if($byService->count())
                <div style="position:relative;height:220px;" class="mb-3">
                    <canvas id="canvasPie"></canvas>
                </div>
                @php $maxRevenue = $byService->first()->revenue; @endphp
                <div class="flex-grow-1 overflow-auto" style="max-height:200px">
                    @foreach($byService as $svc)
                    <div class="service-row border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-medium" style="color:{{ $svc->color }}">
                                <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:{{ $svc->color }};margin-right:4px;"></span>
                                {{ $svc->name }}
                            </span>
                            <span class="fw-semibold">{{ number_format($svc->revenue, 2) }} ₼</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar" role="progressbar"
                                 style="width:{{ $maxRevenue > 0 ? round($svc->revenue / $maxRevenue * 100) : 0 }}%;background:{{ $svc->color }}">
                            </div>
                        </div>
                        <div class="text-muted" style="font-size:.72rem">{{ $svc->cnt }} tamamlandı</div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-pie-chart fs-3 d-block mb-2"></i>
                    Məlumat yoxdur
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Detailed Tables ────────────────────────────────────────────────── --}}
<div class="row g-3">
    {{-- Weekly table --}}
    <div class="col-12 tab-table active" id="table-weekly">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-table me-2 text-primary"></i>Son 7 Günlük Detal</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Tarix</th><th class="text-end">Randevu</th><th class="text-end">Gəlir</th></tr>
                    </thead>
                    <tbody>
                        @foreach(array_reverse(array_keys($weeklyLabels ?? [])) as $i)
                        @php $idx = 6 - $i; @endphp
                        @endforeach
                        @for($i = 0; $i < 7; $i++)
                        <tr>
                            <td>{{ $weeklyLabels[$i] }}</td>
                            <td class="text-end text-muted">{{ $weeklyCounts[$i] }}</td>
                            <td class="text-end fw-semibold">{{ number_format($weeklyRevenues[$i], 2) }} ₼</td>
                        </tr>
                        @endfor
                        <tr class="table-light fw-bold">
                            <td>Cəmi</td>
                            <td class="text-end">{{ array_sum($weeklyCounts) }}</td>
                            <td class="text-end">{{ number_format(array_sum($weeklyRevenues), 2) }} ₼</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Monthly table --}}
    <div class="col-12 tab-table d-none" id="table-monthly">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-table me-2 text-success"></i>{{ $monthlyYear }} İli Aylıq Detal</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Ay</th><th class="text-end">Randevu</th><th class="text-end">Gəlir</th></tr>
                    </thead>
                    <tbody>
                        @for($m = 0; $m < 12; $m++)
                        <tr>
                            <td>{{ $monthlyLabels[$m] }}</td>
                            <td class="text-end text-muted">{{ $monthlyCounts[$m] }}</td>
                            <td class="text-end fw-semibold">{{ number_format($monthlyRevenues[$m], 2) }} ₼</td>
                        </tr>
                        @endfor
                        <tr class="table-light fw-bold">
                            <td>Cəmi</td>
                            <td class="text-end">{{ array_sum($monthlyCounts) }}</td>
                            <td class="text-end">{{ number_format(array_sum($monthlyRevenues), 2) }} ₼</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Annual table --}}
    <div class="col-12 tab-table d-none" id="table-annual">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-table me-2 text-warning"></i>İllik Detal</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>İl</th><th class="text-end">Randevu</th><th class="text-end">Gəlir</th></tr>
                    </thead>
                    <tbody>
                        @for($y = 0; $y < 5; $y++)
                        <tr>
                            <td>{{ $annualLabels[$y] }}</td>
                            <td class="text-end text-muted">{{ $annualCounts[$y] }}</td>
                            <td class="text-end fw-semibold">{{ number_format($annualRevenues[$y], 2) }} ₼</td>
                        </tr>
                        @endfor
                        <tr class="table-light fw-bold">
                            <td>Cəmi</td>
                            <td class="text-end">{{ array_sum($annualCounts) }}</td>
                            <td class="text-end">{{ number_format(array_sum($annualRevenues), 2) }} ₼</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function () {
    // ── Data from server ────────────────────────────────────────────────
    const weekly  = { labels: @json($weeklyLabels),  revenues: @json($weeklyRevenues),  counts: @json($weeklyCounts) };
    const monthly = { labels: @json($monthlyLabels), revenues: @json($monthlyRevenues), counts: @json($monthlyCounts) };
    const annual  = { labels: @json($annualLabels),  revenues: @json($annualRevenues),  counts: @json($annualCounts) };

    @if($byService->count())
    const pieLabels   = @json($byService->pluck('name'));
    const pieRevenues = @json($byService->pluck('revenue'));
    const pieColors   = @json($byService->pluck('color'));
    @endif

    // ── Chart defaults ──────────────────────────────────────────────────
    Chart.defaults.font.family = 'system-ui, sans-serif';
    Chart.defaults.font.size   = 12;

    const barColor    = 'rgba(33, 150, 243, 0.75)';
    const borderColor = 'rgba(33, 150, 243, 1)';

    function buildBar(canvasId, data, label) {
        return new Chart(document.getElementById(canvasId), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: label,
                    data: data.revenues,
                    backgroundColor: barColor,
                    borderColor: borderColor,
                    borderWidth: 1.5,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.parsed.y.toFixed(2) + ' ₼  (' + data.counts[ctx.dataIndex] + ' randevu)'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => v.toFixed(0) + ' ₼' },
                        grid: { color: 'rgba(0,0,0,0.06)' }
                    }
                }
            }
        });
    }

    const chartWeekly  = buildBar('canvasWeekly',  weekly,  'Həftəlik gəlir');
    const chartMonthly = buildBar('canvasMonthly', monthly, 'Aylıq gəlir');
    const chartAnnual  = buildBar('canvasAnnual',  annual,  'İllik gəlir');

    // ── Pie chart ───────────────────────────────────────────────────────
    @if($byService->count())
    new Chart(document.getElementById('canvasPie'), {
        type: 'doughnut',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieRevenues,
                backgroundColor: pieColors,
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.toFixed(2) + ' ₼'
                    }
                }
            }
        }
    });
    @endif

    // ── Tab switching ───────────────────────────────────────────────────
    const yearForm = document.getElementById('year-form');
    const tabs     = document.querySelectorAll('#chart-tabs .nav-link');

    function switchTab(tab) {
        // charts
        document.querySelectorAll('.tab-chart').forEach(el => el.classList.remove('active'));
        document.getElementById('chart-' + tab).classList.add('active');

        // tables
        document.querySelectorAll('.tab-table').forEach(el => el.classList.add('d-none'));
        document.getElementById('table-' + tab).classList.remove('d-none');

        // nav pills
        tabs.forEach(btn => btn.classList.remove('active'));
        document.querySelector('[data-tab="' + tab + '"]').classList.add('active');

        // year picker only visible for monthly
        yearForm.classList.toggle('d-none', tab !== 'monthly');

        // count info
        const data = tab === 'weekly' ? weekly : (tab === 'monthly' ? monthly : annual);
        const total = data.counts.reduce((a, b) => a + b, 0);
        const rev   = data.revenues.reduce((a, b) => a + b, 0);
        document.getElementById('tab-count-info').textContent =
            total + ' tamamlanan randevu · ' + rev.toFixed(2) + ' ₼';
    }

    tabs.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

    // Init count info
    switchTab('weekly');

    // If page loaded with ?year=..., switch to monthly tab
    @if(request('year'))
    switchTab('monthly');
    @endif
})();
</script>
@endpush

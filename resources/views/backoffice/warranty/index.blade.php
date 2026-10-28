@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filters = request()->only(['q', 'cliente', 'tipo']))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Fora da Garantia') }}</title>
@endsection

@push('styles')
<style>
    .wc-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .75rem; }
    .wc-stat { display: block; border-radius: 14px; padding: .8rem 1rem; background: var(--bg); color: var(--fg); border: 2px solid transparent;
               text-decoration: none !important; transition: transform .15s; }
    .wc-stat:hover { transform: translateY(-2px); color: var(--fg); }
    .wc-stat.is-active { border-color: var(--fg); }
    .wc-stat .num { font-size: 1.8rem; font-weight: 700; line-height: 1; }
    .wc-stat .lbl { font-size: .78rem; font-weight: 600; margin-top: .3rem; line-height: 1.25; }

    .wc-client { display: inline-block; font-size: .7rem; font-weight: 800; letter-spacing: .03em; padding: .15rem .5rem; border-radius: 6px; text-transform: uppercase; }
    .wc-client-lidl { background: #0050aa; color: #fff200; }
    .wc-client-sonae { background: #f39200; color: #fff; }
    .wc-type-badge { display: inline-flex; align-items: center; gap: .25rem; font-size: .66rem; font-weight: 800; letter-spacing: .03em;
                     padding: .1rem .4rem; border-radius: 6px; text-transform: uppercase;
                     background: var(--bg); color: var(--c); border: 1px solid var(--c); }
    .wc-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .76rem; font-weight: 600; padding: .2rem .55rem;
                border-radius: 999px; background: var(--bg); color: var(--fg); border: 1px solid var(--fg); white-space: nowrap; }

    /* Um cartão por processo: insígnia, loja, estado e os anexos */
    .wc-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: .75rem; }
    @media (max-width: 575px) { .wc-cards { grid-template-columns: 1fr; } }
    .wc-card { cursor: pointer; padding: .75rem .9rem; background: #fff;
               border: 1px solid #e2e8f0; border-left: 5px solid var(--c); border-radius: 12px;
               transition: box-shadow .15s, transform .15s; }
    .wc-card:hover { box-shadow: 0 6px 18px rgba(15, 23, 42, .09); transform: translateY(-2px); }
    .wc-card-head { display: flex; align-items: center; flex-wrap: wrap; gap: .45rem; }
    .wc-card-store { font-weight: 700; color: #0f172a; font-size: .95rem; }
    .wc-card-store a { color: inherit; }
    .wc-card-store a:hover { color: var(--c); }

    .wc-state { margin-left: auto; display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .6rem; border-radius: 999px;
                font-size: .74rem; font-weight: 700; white-space: nowrap; background: var(--bg); color: var(--fg); }
    .wc-late { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .55rem; border-radius: 999px;
               font-size: .74rem; font-weight: 700; background: #fef2f2; color: #b91c1c; white-space: nowrap; }
    .wc-late .fa { animation: wc-pulse 1.4s ease-in-out infinite; }
    @keyframes wc-pulse { 50% { opacity: .3; } }
    @media (prefers-reduced-motion: reduce) { .wc-late .fa { animation: none; } }

    /* Os três anexos, sempre nos mesmos lugares: a cinzento os que ainda não existem */
    .wc-files { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .55rem; }
    .wc-file { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .5rem; border-radius: 8px;
               font-size: .75rem; font-weight: 600; background: #f1f5f9; color: #94a3b8; border: 1px solid transparent; }
    .wc-file.has { background: #fff; color: #334155; border-color: #cbd5e1; }
    .wc-file.pdf { color: #b91c1c; border-color: #fecaca; background: #fff5f5; }
    .wc-file.pdf:hover { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: .75rem">
            <div>
                <h4 class="mb-1"><i class="fas fa-file-invoice-dollar text-danger"></i> {{ __('Fora da Garantia') }}</h4>
                <p class="text-muted small mb-0">{{ __('Avarias por culpa do cliente. Com peças: cotação da Sielaff e encomenda à Alemanha. Sem peças: só a deslocação e a mão de obra.') }}</p>
            </div>
            <div class="d-flex flex-wrap" style="gap: .5rem">
                <form method="GET" class="form-inline" style="gap: .5rem">
                    @if(request('fase'))<input type="hidden" name="fase" value="{{ request('fase') }}">@endif
                    <select name="tipo" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">{{ __('Com e sem peças') }} ({{ $typeCounts->sum() }})</option>
                        @foreach($types as $value => $type)
                            <option value="{{ $value }}" @selected(request('tipo') === $value)>{{ __($type['label']) }} ({{ $typeCounts[$value] ?? 0 }})</option>
                        @endforeach
                    </select>
                    <select name="cliente" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">{{ __('Todos os clientes') }}</option>
                        @foreach($clients as $value => $label)
                            <option value="{{ $value }}" @selected(request('cliente') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ __('Loja, nº série, nº cotação…') }}" style="min-width: 220px">
                </form>
                <a href="{{ route('backoffice.warranty.export', $filters + request()->only('fase')) }}" class="btn btn-outline-success btn-sm"><i class="fa fa-file-excel"></i> {{ __('Exportar Excel') }}</a>
                <a href="{{ route('backoffice.warranty.create') }}" class="btn btn-danger btn-sm"><i class="fa fa-plus"></i> {{ __('Novo processo') }}</a>
            </div>
        </div>

        <div class="wc-summary">
            <a href="{{ route('backoffice.warranty.index', $filters) }}" class="wc-stat {{ request('fase') ? '' : 'is-active' }}" style="--bg: #f1f3f6; --fg: #263238">
                <div class="num">{{ $counts->sum() }}</div><div class="lbl">{{ __('Todos') }}</div>
            </a>
            <a href="{{ route('backoffice.warranty.index', ['fase' => 'abertos'] + $filters) }}" class="wc-stat {{ request('fase') === 'abertos' ? 'is-active' : '' }}" style="--bg: #fff1f2; --fg: #be123c">
                <div class="num">{{ $openTotal }}</div><div class="lbl">{{ __('Em aberto') }}</div>
            </a>
            @foreach($types as $key => $type)
                <a href="{{ route('backoffice.warranty.index', ['tipo' => $key] + request()->only(['q', 'cliente', 'fase'])) }}" class="wc-stat {{ request('tipo') === $key ? 'is-active' : '' }}" style="--bg: {{ $type['bg'] }}; --fg: {{ $type['color'] }}">
                    <div class="num">{{ $typeCounts[$key] ?? 0 }}</div>
                    <div class="lbl"><i class="fas {{ $type['icon'] }}"></i> {{ __($type['label']) }}</div>
                </a>
            @endforeach
            @foreach($phases as $key => $phase)
                <a href="{{ route('backoffice.warranty.index', ['fase' => $key] + $filters) }}" class="wc-stat {{ request('fase') === $key ? 'is-active' : '' }}" style="--bg: {{ $phase['bg'] }}; --fg: {{ $phase['color'] }}">
                    <div class="num">{{ $counts[$key] ?? 0 }}</div>
                    <div class="lbl"><i class="fas {{ $phase['icon'] }}"></i> {{ __($phase['label']) }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($cases->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                <p class="mb-3">{{ $counts->sum() ? __('Nenhum processo corresponde aos filtros.') : __('Ainda não há processos fora da garantia.') }}</p>
                <a href="{{ route('backoffice.warranty.create') }}" class="btn btn-danger btn-sm"><i class="fa fa-plus"></i> {{ __('Novo processo') }}</a>
            </div>
        @else
            <div class="wc-cards">
                @foreach($cases as $case)
                    @php($phase = $case->phase())
                    @php($accent = $case->isOpen() ? $case->waitingOnConfig()['color'] : $phase['color'])
                    <div class="wc-card" style="--c: {{ $accent }}"
                         onclick="if (!event.target.closest('a')) location.href = this.dataset.href"
                         data-href="{{ route('backoffice.warranty.show', $case->id) }}">

                        <div class="wc-card-head">
                            <span class="wc-client wc-client-{{ $case->cliente }}">{{ $case->clientLabel() }}</span>
                            <span class="wc-card-store">
                                <a href="{{ route('backoffice.warranty.show', $case->id) }}">{{ $case->store->codigo_loja ?? '—' }} · {{ $case->store->nome_loja ?? '—' }}</a>
                            </span>
                            @if($case->isOpen())
                                <span class="wc-state" style="--bg: #f1f5f9; --fg: #475569"><i class="fa fa-circle-notch"></i> {{ __('Em curso') }}</span>
                            @else
                                <span class="wc-state" style="--bg: {{ $phase['bg'] }}; --fg: {{ $phase['color'] }}">
                                    <i class="fas {{ $phase['icon'] }}"></i> {{ $case->fase === 'concluido' ? __('Concluído') : __('Recusado') }}
                                </span>
                            @endif
                        </div>

                        {{-- Os três anexos sempre nos mesmos lugares: vê-se de relance o que já existe --}}
                        <div class="wc-files">
                            @foreach(['os' => [__('OS'), __('Ordem de serviço')], 'cotacao' => [__('Cotação'), __('Cotação')], 'oc' => [__('OC'), __('Ordem de compra')]] as $tipo => [$curto, $completo])
                                @php($numero = $case->{$tipo . '_numero'})
                                @if($case->{$tipo . '_path'})
                                    <a href="{{ route('backoffice.warranty.document', [$case->id, $tipo]) }}" target="_blank" class="wc-file pdf" title="{{ $completo }} {{ $numero }}">
                                        <i class="fa fa-file-pdf"></i> {{ $curto }}@if($numero) · {{ $numero }}@endif
                                    </a>
                                @elseif($numero)
                                    <span class="wc-file has" title="{{ $completo }} {{ $numero }} · {{ __('sem PDF') }}">
                                        <i class="fa fa-file"></i> {{ $curto }} · {{ $numero }}
                                    </span>
                                @else
                                    <span class="wc-file" title="{{ $completo }} · {{ __('ainda não') }}"><i class="fa fa-minus"></i> {{ $curto }}</span>
                                @endif
                            @endforeach
                            @if($case->isOpen() && $case->waitingAlert() === 'late')
                                <span class="wc-late" title="{{ $case->todoLabel() }}">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    {{ trans_choice('{0} hoje|{1} há :count dia|[2,*] há :count dias', $case->daysInPhase(), ['count' => $case->daysInPhase()]) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if($cases->hasPages())
                <div class="mt-3 d-flex justify-content-center">{{ $cases->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection

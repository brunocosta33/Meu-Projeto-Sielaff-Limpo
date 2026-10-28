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

    /* Uma linha por processo, com a barra de cinco passos */
    .wc-list { display: flex; flex-direction: column; }
    .wc-item { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1.25fr) minmax(0, .95fr);
               gap: 1rem; align-items: center; padding: .85rem .75rem; border-radius: 10px; cursor: pointer; }
    .wc-item + .wc-item { border-top: 1px solid #eef1f5; }
    .wc-item:hover { background: #f8fafc; }
    @media (max-width: 991px) { .wc-item { grid-template-columns: 1fr; gap: .6rem; } }

    .wc-loja { display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; font-weight: 700; color: #0f172a; }
    .wc-loja a { color: inherit; }
    .wc-loja .cod { font-family: ui-monospace, Menlo, monospace; color: #64748b; font-weight: 600; }
    .wc-avaria { color: #64748b; font-size: .88rem; margin: .2rem 0 0;
                 display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    /* Barra de progresso: um segmento por bloco, sempre cinco para as linhas alinharem */
    .wc-trilho { display: grid; grid-template-columns: repeat(5, 1fr); gap: .22rem; min-width: 0; }
    .wc-passo { min-width: 0; }
    .wc-passo .barra { height: .42rem; border-radius: 3px; background: #e2e8f0; }
    .wc-passo.feito .barra { background: var(--c); }
    .wc-passo.agora .barra { background: var(--c); box-shadow: 0 0 0 2px color-mix(in srgb, var(--c) 30%, transparent); }
    .wc-passo .nome { display: block; font-size: .66rem; text-transform: uppercase; letter-spacing: .04em;
                      color: #94a3b8; margin-top: .3rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wc-passo.feito .nome { color: #64748b; }
    .wc-passo.agora .nome { color: var(--c); font-weight: 700; }
    .wc-passo.vazio { opacity: .35; }

    .wc-agora { font-weight: 700; font-size: .9rem; line-height: 1.3; color: #1e293b; }
    .wc-agora .seta { color: var(--c); }
    .wc-meta { display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; margin-top: .35rem; }
    .wc-dias { font-size: .76rem; color: #94a3b8; }
    .wc-dias.warn { color: #c2410c; background: #fff7ed; font-weight: 700; padding: .1rem .4rem; border-radius: 5px; }
    .wc-dias.late { color: #b91c1c; background: #fef2f2; font-weight: 700; padding: .1rem .4rem; border-radius: 5px; }
    .wc-anexo { font-family: ui-monospace, Menlo, monospace; font-size: .72rem; padding: .1rem .35rem; border-radius: 4px;
                border: 1px solid #e2e8f0; color: #64748b; white-space: nowrap; }
    a.wc-anexo { color: #b91c1c; border-color: #fecaca; }
    a.wc-anexo:hover { background: #fef2f2; color: #991b1b; }
    .wc-fechado { font-size: .84rem; font-weight: 700; color: #15803d; }
    .wc-fechado.recusado { color: #b91c1c; }
</style>
@endpush

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: .75rem">
            <div>
                <h4 class="mb-1"><i class="fas fa-file-invoice-dollar text-danger"></i> {{ __('Fora da Garantia') }}</h4>
                <p class="text-muted small mb-0">{{ __('Avarias por culpa do cliente. Com peças: cotação da Sielaff e encomenda à Alemanha. Sem peças: deslocação, mão de obra ou peça trocada na hora.') }}</p>
            </div>
            <div class="d-flex flex-wrap" style="gap: .5rem">
                <form method="GET" class="form-inline" style="gap: .5rem">
                    @if(request('fase'))<input type="hidden" name="fase" value="{{ request('fase') }}">@endif
                    <select name="tipo" class="form-control form-control-sm" onchange="this.form.submit()" style="max-width: 250px">
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
            <div class="wc-list">
                @foreach($cases as $case)
                    @php($phase = $case->phase())
                    @php($next = $case->nextStep())
                    @php($accent = $case->isOpen() ? $case->waitingOnConfig()['color'] : $phase['color'])
                    <div class="wc-item" style="--c: {{ $accent }}"
                         onclick="if (!event.target.closest('a')) location.href = this.dataset.href"
                         data-href="{{ route('backoffice.warranty.show', $case->id) }}">

                        <div>
                            <div class="wc-loja">
                                <span class="wc-client wc-client-{{ $case->cliente }}">{{ $case->clientLabel() }}</span>
                                <span class="cod">{{ $case->store->codigo_loja ?? '—' }}</span>
                                <a href="{{ route('backoffice.warranty.show', $case->id) }}">{{ $case->store->nome_loja ?? '—' }}</a>
                            </div>
                            <p class="wc-avaria">{{ $case->descricao }}</p>
                        </div>

                        {{-- Cinco segmentos sempre: os processos sem peças mostram os que não têm apagados --}}
                        <div class="wc-trilho">
                            @foreach($case->blocks() as $blockKey => $block)
                                @php($estado = $case->blockState($blockKey, $next))
                                <div class="wc-passo {{ $estado }}" style="--c: {{ $block['color'] }}" title="{{ __($block['label']) }}">
                                    <div class="barra"></div><span class="nome">{{ __($block['curto']) }}</span>
                                </div>
                            @endforeach
                            @for($i = count($case->blocks()); $i < 5; $i++)
                                <div class="wc-passo vazio"><div class="barra"></div><span class="nome">—</span></div>
                            @endfor
                        </div>

                        <div>
                            @if($case->isOpen())
                                <div class="wc-agora"><span class="seta">→</span> {{ $case->todoLabel() }}</div>
                            @else
                                <div class="wc-fechado {{ $case->fase === 'recusado' ? 'recusado' : '' }}">
                                    <i class="fas {{ $phase['icon'] }}"></i> {{ __($phase['label']) }} {{ optional($case->fechado_em)->format('d/m/Y') }}
                                </div>
                            @endif
                            <div class="wc-meta">
                                @if($case->isOpen())
                                    <span class="wc-dias {{ $case->waitingAlert() }}">
                                        {{ trans_choice('{0} hoje|{1} há :count dia|[2,*] há :count dias', $case->daysInPhase(), ['count' => $case->daysInPhase()]) }}
                                    </span>
                                @endif
                                @foreach(['os' => __('Ordem de serviço'), 'cotacao' => __('Cotação'), 'oc' => __('Ordem de compra')] as $tipo => $label)
                                    @continue(!$case->{$tipo . '_numero'})
                                    @if($case->{$tipo . '_path'})
                                        <a href="{{ route('backoffice.warranty.document', [$case->id, $tipo]) }}" target="_blank" class="wc-anexo" title="{{ $label }}">{{ $case->{$tipo . '_numero'} }}</a>
                                    @else
                                        <span class="wc-anexo" title="{{ $label }} · {{ __('sem PDF') }}">{{ $case->{$tipo . '_numero'} }}</span>
                                    @endif
                                @endforeach
                                @if($case->photos_count)
                                    <span class="wc-dias"><i class="fa fa-camera"></i> {{ $case->photos_count }}</span>
                                @endif
                            </div>
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

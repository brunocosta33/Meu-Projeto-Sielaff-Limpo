@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filters = request()->only(['q', 'cliente', 'tipo']))
@php($filtrosTodos = request()->only(['q', 'cliente', 'tipo', 'espera']))

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
    .wc-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .76rem; font-weight: 600; padding: .2rem .55rem;
                border-radius: 999px; background: var(--bg); color: var(--fg); border: 1px solid var(--fg); white-space: nowrap; }
    .wc-client { display: inline-block; font-size: .68rem; font-weight: 800; letter-spacing: .03em; padding: .1rem .45rem; border-radius: 6px; text-transform: uppercase; }
    .wc-client-lidl { background: #0050aa; color: #fff200; }
    .wc-client-sonae { background: #f39200; color: #fff; }
    .wc-type-badge { display: inline-flex; align-items: center; gap: .25rem; font-size: .66rem; font-weight: 800; letter-spacing: .03em;
                     padding: .1rem .4rem; border-radius: 6px; text-transform: uppercase;
                     background: var(--bg); color: var(--c); border: 1px solid var(--c); }
    .wc-table td { vertical-align: middle; }
    .wc-row { cursor: pointer; }
    .wc-row:hover { background: #f8fafc; }
    .wc-store { font-weight: 600; }
    .wc-store small { display: block; font-weight: 400; color: #6c757d; }
    .wc-problem { max-width: 340px; font-size: .86rem; color: #334155; }
    .wc-wait { display: inline-flex; align-items: center; gap: .4rem; margin-top: .4rem; padding: .3rem .7rem; border-radius: 8px;
               font-size: .8rem; font-weight: 700; background: var(--bg); color: var(--fg); border-left: 4px solid var(--fg); white-space: nowrap; }
    .wc-wait small { font-weight: 600; opacity: .8; }
    .wc-wait.warn { --bg: #fff7ed !important; --fg: #c2410c !important; }
    .wc-wait.late { --bg: #fef2f2 !important; --fg: #b91c1c !important; }
    .wc-wait.late .fa { animation: wc-pulse 1.4s ease-in-out infinite; }
    .wc-closed { font-size: .76rem; margin-top: .3rem; color: #64748b; }
    .wc-todo { font-weight: 700; color: #1e293b; font-size: .88rem; line-height: 1.25; margin-bottom: .3rem; }
    @keyframes wc-pulse { 50% { opacity: .3; } }
    @media (prefers-reduced-motion: reduce) { .wc-wait.late .fa { animation: none; } }
    .wc-docs { font-size: .78rem; white-space: nowrap; }
    .wc-docs div + div { margin-top: .2rem; }
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
                    @if(request('espera'))<input type="hidden" name="espera" value="{{ request('espera') }}">@endif
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
                <a href="{{ route('backoffice.warranty.export', $filtrosTodos) }}" class="btn btn-outline-success btn-sm"><i class="fa fa-file-excel"></i> {{ __('Exportar Excel') }}</a>
                <a href="{{ route('backoffice.warranty.create') }}" class="btn btn-danger btn-sm"><i class="fa fa-plus"></i> {{ __('Novo processo') }}</a>
            </div>
        </div>

        <div class="wc-summary">
            <a href="{{ route('backoffice.warranty.index', $filters) }}" class="wc-stat {{ request('espera') ? '' : 'is-active' }}" style="--bg: #f1f3f6; --fg: #263238">
                <div class="num">{{ $total }}</div><div class="lbl">{{ __('Todos') }}</div>
            </a>
            @foreach($waitingGroups as $key => $group)
                <a href="{{ route('backoffice.warranty.index', ['espera' => $key] + $filters) }}" class="wc-stat {{ request('espera') === $key ? 'is-active' : '' }}" style="--bg: {{ $group['bg'] }}; --fg: {{ $group['color'] }}">
                    <div class="num">{{ $waitCounts[$key] ?? 0 }}</div>
                    <div class="lbl"><i class="fas {{ $group['icon'] }}"></i> {{ __($group['label']) }}</div>
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
            <div class="table-responsive">
                <table class="table wc-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Loja') }}</th>
                            <th>{{ __('Problema') }}</th>
                            <th>{{ __('O que falta') }}</th>
                            <th>{{ __('Documentos') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cases as $case)
                            @php($phase = $case->phase())
                            @php($alert = $case->waitingAlert())
                            <tr class="wc-row" onclick="if (!event.target.closest('a')) location.href = this.dataset.href" data-href="{{ route('backoffice.warranty.show', $case->id) }}">
                                @php($type = $case->typeConfig())
                                <td class="wc-store">
                                    <span class="wc-client wc-client-{{ $case->cliente }}">{{ $case->clientLabel() }}</span>
                                    <span class="wc-type-badge" style="--c: {{ $type['color'] }}; --bg: {{ $type['bg'] }}" title="{{ __($type['ajuda']) }}"><i class="fas {{ $type['icon'] }}"></i> {{ __($type['curto']) }}</span>
                                    {{ $case->store->codigo_loja ?? '—' }} — {{ $case->store->nome_loja ?? '—' }}
                                    <small>
                                        #{{ $case->id }} · {{ __('Avaria') }} {{ optional($case->data_avaria)->format('d/m/Y') }}
                                        @if($case->machine) · <i class="fa fa-hashtag"></i>{{ $case->machine->serial_number }}@endif
                                    </small>
                                </td>
                                <td class="wc-problem">
                                    {{ \Illuminate\Support\Str::limit($case->descricao, 110) }}
                                    @if($case->photos_count)
                                        <span class="text-muted small text-nowrap"><i class="fa fa-camera"></i> {{ $case->photos_count }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($case->isOpen())
                                        @php($espera = $case->waitingOnConfig())
                                        <div class="wc-todo">{{ $case->todoLabel() }}</div>
                                        <span class="wc-wait {{ $alert }}" style="--bg: {{ $espera['bg'] }}; --fg: {{ $espera['color'] }}">
                                            <i class="fa {{ $alert ? 'fa-exclamation-triangle' : $espera['icon'] }}"></i>
                                            {{ __($espera['curto']) }}
                                            <small>· {{ trans_choice('{0} hoje|{1} há :count dia|[2,*] há :count dias', $case->daysInPhase(), ['count' => $case->daysInPhase()]) }}</small>
                                        </span>
                                    @else
                                        <span class="wc-badge" style="--bg: {{ $phase['bg'] }}; --fg: {{ $phase['color'] }}"><i class="fas {{ $phase['icon'] }}"></i> {{ __($phase['label']) }}</span>
                                        @if($case->fechado_em)<div class="wc-closed">{{ $case->fechado_em->format('d/m/Y') }}</div>@endif
                                    @endif
                                </td>
                                <td class="wc-docs">
                                    @foreach(['os' => [__('OS'), 'fa-clipboard-check'], 'cotacao' => [__('Cotação'), 'fa-file-invoice'], 'oc' => [__('OC'), 'fa-file-signature']] as $tipo => [$docLabel, $docIcon])
                                        @continue(!$case->{$tipo . '_numero'})
                                        <div>
                                            @if($case->{$tipo . '_path'})
                                                <a href="{{ route('backoffice.warranty.document', [$case->id, $tipo]) }}" target="_blank"><i class="fa {{ $docIcon }}"></i> {{ $docLabel }} {{ $case->{$tipo . '_numero'} }}</a>
                                            @else
                                                <span class="text-muted" title="{{ __('sem PDF') }}"><i class="fa {{ $docIcon }}"></i> {{ $docLabel }} {{ $case->{$tipo . '_numero'} }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($case->fase === 'concluido')
                                        <div>
                                            @if($case->servico_path)
                                                <a href="{{ route('backoffice.warranty.document', [$case->id, 'servico']) }}" target="_blank"><i class="fa fa-clipboard-check"></i> {{ __('Ordem de serviço') }}</a>
                                            @else
                                                <span class="text-muted" title="{{ __('sem PDF') }}"><i class="fa fa-clipboard-check"></i> {{ __('Ordem de serviço') }} · {{ __('sem PDF') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if(!$case->os_numero && !$case->cotacao_numero && !$case->oc_numero && $case->fase !== 'concluido')<span class="text-muted">—</span>@endif
                                </td>
                                <td class="text-right"><a href="{{ route('backoffice.warranty.show', $case->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($cases->hasPages())
                <div class="mt-3 d-flex justify-content-center">{{ $cases->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection

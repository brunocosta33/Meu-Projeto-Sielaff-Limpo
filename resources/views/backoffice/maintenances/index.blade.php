@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filters = request()->only(['q', 'estado', 'cliente', 'zona', 'mes']))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Manutenções Anuais') }} {{ $year }}</title>
@endsection

@push('styles')
<style>
    .mt-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .75rem; }
    .mt-stat { display: block; border-radius: 14px; padding: .8rem 1rem; background: var(--bg); color: var(--fg); border: 2px solid transparent;
               text-decoration: none !important; transition: transform .15s; }
    .mt-stat:hover { transform: translateY(-2px); color: var(--fg); }
    .mt-stat.is-active { border-color: var(--fg); }
    .mt-stat .num { font-size: 1.8rem; font-weight: 700; line-height: 1; }
    .mt-stat .lbl { font-size: .8rem; font-weight: 600; margin-top: .25rem; }
    .mt-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .78rem; font-weight: 600; padding: .2rem .55rem;
                border-radius: 999px; background: var(--bg); color: var(--fg); border: 1px solid var(--fg); white-space: nowrap; }
    .mt-table td { vertical-align: middle; }
    /* O cliente salta à vista: cor própria na barra da linha e na etiqueta */
    .mt-table tr { border-left: 5px solid var(--cliente, transparent); }
    .mt-cliente { display: inline-flex; align-items: center; gap: .32rem; font-size: .74rem; font-weight: 800;
                  text-transform: uppercase; letter-spacing: .05em; padding: .26rem .66rem; border-radius: 999px;
                  background: var(--grad, var(--bg)); color: var(--txt, var(--fg));
                  box-shadow: 0 6px 14px var(--sombra, transparent); vertical-align: middle; }
    .mt-clientes { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .75rem; }
    .mt-cliente-btn { display: inline-flex; align-items: center; gap: .35rem; border: 1px solid #dde3ea; background: #fff;
                      border-radius: 999px; padding: .22rem .75rem; font-size: .82rem; font-weight: 700; color: #475569;
                      text-decoration: none !important; }
    .mt-cliente-btn:hover { border-color: var(--fg, #94a3b8); color: var(--fg, #334155); }
    .mt-cliente-btn.on { background: var(--grad, #eef2f7); color: var(--txt, #263238); border-color: transparent;
                         box-shadow: 0 6px 14px var(--sombra, transparent); }
    .mt-cliente-btn .n { font-weight: 800; opacity: .75; }
    .mt-mes { font-weight: 800; font-size: .95rem; text-transform: capitalize; }
    /* Zonas: Norte, Centro, Sul */
    .mt-zona { display: inline-flex; align-items: center; gap: .28rem; font-size: .72rem; font-weight: 800; text-transform: uppercase;
               letter-spacing: .04em; padding: .2rem .55rem; border-radius: 999px; background: var(--bg); color: var(--fg);
               border: 1px solid var(--fg); vertical-align: middle; }
    .mt-zona i { font-size: .62rem; }
    .mt-zonas { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-top: .6rem; }
    .mt-zonas .rotulo, .mt-clientes .rotulo, .mt-meses .rotulo { font-size: .72rem; font-weight: 800; color: #94a3b8;
               text-transform: uppercase; letter-spacing: .06em; min-width: 4.2rem; }
    .mt-zona-btn { display: inline-flex; align-items: center; gap: .35rem; border: 1px solid #dde3ea; background: #fff;
                   border-radius: 999px; padding: .22rem .75rem; font-size: .82rem; font-weight: 700; color: #475569;
                   text-decoration: none !important; }
    .mt-zona-btn:hover { border-color: var(--fg, #94a3b8); color: var(--fg, #334155); }
    .mt-zona-btn.on { background: var(--fg, #263238); border-color: var(--fg, #263238); color: #fff; }
    .mt-zona-btn .n { font-weight: 800; opacity: .75; }
    /* Cabeçalho de cada grupo (em atraso, cada mês, sem data) */
    .mt-table tr.mt-grupo { border-left: 0; }
    .mt-table tr.mt-grupo td { padding: 1.1rem .5rem .45rem; border-top: 0; background: #fff; }
    .mt-grupo .caixa { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem .9rem; padding: .55rem .9rem;
                       border-radius: 12px; background: var(--gbg, #f1f5f9); color: var(--gfg, #334155); }
    .mt-grupo .titulo { font-size: 1.02rem; font-weight: 800; display: inline-flex; align-items: center; gap: .45rem; }
    .mt-grupo .agora { font-size: .68rem; font-weight: 800; text-transform: uppercase; background: var(--gfg); color: #fff;
                       border-radius: 999px; padding: .1rem .5rem; letter-spacing: .05em; }
    .mt-grupo .contas { font-size: .8rem; font-weight: 600; opacity: .85; }
    .mt-grupo .barra { width: 110px; height: 7px; border-radius: 999px; background: rgba(255, 255, 255, .7); overflow: hidden; }
    .mt-grupo .barra span { display: block; height: 100%; background: #2e7d32; border-radius: 999px; }
    .mt-grupo .zonas { margin-left: auto; display: flex; gap: .3rem; flex-wrap: wrap; }
    .mt-grupo-atraso { --gbg: #fde2e1; --gfg: #b91c1c; }
    .mt-grupo-passou { --gbg: #ffedd5; --gfg: #9a3412; }
    .mt-grupo-agora { --gbg: #dbeafe; --gfg: #0f4c81; }
    .mt-grupo-mes { --gbg: #f1f5f9; --gfg: #334155; }
    .mt-grupo-sem { --gbg: #f8fafc; --gfg: #64748b; }
    .mt-limite { display: inline-flex; align-items: center; gap: .3rem; margin-top: .2rem; padding: .05rem .5rem; border-radius: 999px;
        background: #eef2ff; color: #3730a3; font-size: .74rem; font-weight: 700; }
    .mt-passou { color: #b45309; font-weight: 700; }
    /* Barra dos meses */
    .mt-meses { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .75rem; }
    .mt-mes-btn { display: inline-flex; align-items: center; gap: .3rem; border: 1px solid #dde3ea; background: #fff;
                  color: #475569; border-radius: 9px; padding: .22rem .6rem; font-size: .82rem; font-weight: 700;
                  text-decoration: none !important; text-transform: capitalize; }
    .mt-mes-btn:hover { border-color: #0f4c81; color: #0f4c81; }
    .mt-mes-btn.on { background: #0f4c81; border-color: #0f4c81; color: #fff; }
    .mt-mes-btn.zero { color: #b9c3cf; }
    .mt-mes-btn.zero .n { opacity: .5; }
    .mt-mes-btn.agora { border-color: #0f4c81; box-shadow: 0 0 0 2px #dbeafe; }
    .mt-mes-btn.agora::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #0f4c81; }
    .mt-mes-btn.agora.on::before { background: #fff; }
    .mt-mes-btn .n { background: #eef2f7; color: #475569; border-radius: 999px; padding: 0 .4rem; font-size: .72rem; }
    .mt-mes-btn.on .n { background: rgba(255, 255, 255, .25); color: #fff; }
    /* Definir a data/mês na própria linha, sem abrir formulários */
    .mt-definir { display: flex; gap: .3rem; align-items: center; }
    .mt-definir input { max-width: 10.5rem; }
    .mt-store { font-weight: 600; }
    .mt-store small { display: block; font-weight: 400; color: #6c757d; }
    .mt-window { white-space: nowrap; font-size: .85rem; }
    .mt-window .due { font-weight: 700; font-size: .95rem; }
    /* Mesmo verde da etiqueta "Feita" (config maintenance.states.feita) */
    .mt-done-date { display: inline-flex; align-items: center; gap: .3rem; padding: .15rem .55rem; border-radius: 999px; background: #dff0e0; color: #2e7d32;
                    border: 1px solid #2e7d32; font-weight: 700; font-size: .78rem; white-space: nowrap; }
    .mt-doc { display: inline-flex; align-items: center; gap: .3rem; margin-top: .3rem; font-weight: 600; font-size: .78rem; color: #1565c0; background: #e3f0ff; border-radius: 999px; padding: .1rem .55rem; text-decoration: none !important; }
    .mt-doc:hover { background: #cfe3ff; }
    .mt-notes { font-size: .84rem; color: #334155; max-width: 280px; white-space: pre-line; overflow-wrap: anywhere; }
    .mt-doc-missing { display: inline-block; margin-top: .3rem; font-size: .74rem; color: #8a96a8; }
    /* As máquinas da loja, com o mesmo aspeto que têm na página das Lojas */
    .mt-maquinas { display: flex; flex-wrap: wrap; gap: 6px; margin-top: .4rem; }
    .mt-maquina { min-width: 9rem; background: linear-gradient(180deg, #fff 0%, #f8fbff 100%); border: 1px solid #dfe9f5;
                  border-radius: 12px; padding: .4rem .5rem; box-shadow: 0 4px 10px rgba(19, 34, 56, .04); font-weight: 400; }
    .mt-maquina .serie { display: inline-flex; align-items: center; gap: 5px; padding: .2rem .55rem; border-radius: 999px;
                         background: linear-gradient(135deg, #ffe69a 0%, #ffd166 100%); color: #7a4a00;
                         font-size: .76rem; font-weight: 800; }
    .mt-maquina .modelo { color: #6c7a89; font-size: .73rem; line-height: 1.3; margin-top: .3rem; }
    .mt-maquina .modelo i { color: #b6c2d1; margin-right: .1rem; }
    /* Anexos: folhas de serviço e fotografias */
    .mt-anexos { display: flex; flex-wrap: wrap; gap: .25rem; margin-top: .35rem; }
    .mt-anexo { display: inline-flex; align-items: center; gap: .15rem; border: 1px solid currentColor; border-radius: 8px;
                padding: .12rem .25rem .12rem .45rem; font-size: .74rem; font-weight: 700; color: var(--c, #1565c0);
                background: #fff; }
    .mt-anexo a { color: inherit; text-decoration: none !important; }
    .mt-anexo a small { font-size: .62rem; opacity: .7; margin-left: .1rem; }
    .mt-anexo form { display: inline; margin: 0; }
    .mt-anexo button { border: 0; background: none; color: inherit; opacity: .45; line-height: 1; padding: 0 .15rem; font-size: .9rem; }
    .mt-anexo button:hover { opacity: 1; color: #b3261e; }

    /* As fotografias em miniatura, para se ver o que é sem abrir */
    .mt-fotos { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .35rem; }
    .mt-foto { position: relative; }
    .mt-foto > button:first-child { width: 54px; height: 54px; border: 1px solid #dfe9f5; border-radius: 10px; padding: 0;
                                    background-size: cover; background-position: center; background-color: #f1f5f9;
                                    box-shadow: 0 2px 6px rgba(19, 34, 56, .1); transition: transform .12s; }
    .mt-foto > button:first-child:hover { transform: scale(1.06); border-color: #6d28d9; }
    .mt-foto .sem-previa { display: grid; place-items: center; width: 54px; height: 54px; border: 1px dashed #cbd5e1;
                           border-radius: 10px; color: #6d28d9; background: #faf8ff; }
    .mt-foto form { position: absolute; top: -5px; right: -5px; margin: 0; }
    .mt-foto .apagar { width: 18px; height: 18px; border-radius: 50%; border: 0; background: #fff; color: #94a3b8;
                       line-height: 1; font-size: .85rem; box-shadow: 0 1px 4px rgba(19, 34, 56, .25); opacity: 0; }
    .mt-foto:hover .apagar { opacity: 1; }
    .mt-foto .apagar:hover { color: #fff; background: #b3261e; }
</style>
@endpush

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: .75rem">
            <div class="d-flex align-items-center">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.maintenances.index', ['ano' => $year - 1] + $filters) }}"><i class="fa fa-chevron-left"></i></a>
                <h4 class="mb-0 mx-3"><i class="fas fa-clipboard-check text-primary"></i> {{ __('Manutenções Anuais') }} {{ $year }}</h4>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.maintenances.index', ['ano' => $year + 1] + $filters) }}"><i class="fa fa-chevron-right"></i></a>
            </div>
            <div class="d-flex flex-wrap" style="gap: .5rem">
                <form method="GET" class="form-inline">
                    <input type="hidden" name="ano" value="{{ $year }}">
                    @foreach(['estado', 'cliente', 'zona', 'mes'] as $campo)
                        @if(request($campo))<input type="hidden" name="{{ $campo }}" value="{{ request($campo) }}">@endif
                    @endforeach
                    <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ __('Pesquisar loja, código, insígnia…') }}" style="min-width: 240px">
                </form>
                <a class="btn btn-outline-success btn-sm" href="{{ route('backoffice.maintenances.export', ['ano' => $year] + $filters) }}">
                    <i class="fa fa-file-excel"></i> {{ __('Excel') }}
                </a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('backoffice.maintenances.export.zip', ['ano' => $year] + $filters) }}"
                   title="{{ __('Excel mais as folhas de serviço e as fotografias') }}">
                    <i class="fa fa-file-archive"></i> {{ __('Excel + anexos') }}
                </a>
            </div>
        </div>

        <div class="mt-summary">
            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year] + \Illuminate\Support\Arr::except($filters, 'estado')) }}"
               class="mt-stat {{ request('estado') ? '' : 'is-active' }}" style="--bg: #f1f3f6; --fg: #263238">
                <div class="num">{{ $total }}</div><div class="lbl">{{ __('Total de lojas') }}</div>
            </a>
            @foreach(['atrasada', 'janela', 'proxima', 'futura', 'contrato_futuro', 'por_definir', 'feita'] as $key)
                @php($state = $states[$key])
                @php($n = $key === 'feita' ? ($counts['feita'] ?? 0) + ($counts['feita_fora'] ?? 0) : ($counts[$key] ?? 0))
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'estado' => $key] + $filters) }}"
                   class="mt-stat {{ request('estado') === $key ? 'is-active' : '' }}" style="--bg: {{ $state['bg'] }}; --fg: {{ $state['color'] }}">
                    <div class="num">{{ $n }}</div><div class="lbl"><i class="fas {{ $state['icon'] }}"></i> {{ $key === 'feita' ? __('Feitas') : __($state['label']) }}</div>
                </a>
            @endforeach
        </div>
        <div class="mt-clientes">
            <span class="rotulo">{{ __('Cliente') }}</span>
            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year] + \Illuminate\Support\Arr::except($filters, 'cliente')) }}"
               class="mt-cliente-btn {{ request('cliente') ? '' : 'on' }}">
                <i class="fas fa-building"></i> {{ __('Todos os clientes') }} <span class="n">{{ $total }}</span>
            </a>
            @foreach($clientesConfig as $key => $cfg)
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'cliente' => $key] + $filters) }}"
                   class="mt-cliente-btn {{ request('cliente') === $key ? 'on' : '' }}"
                   style="--bg: {{ $cfg['bg'] }}; --fg: {{ $cfg['color'] }}; --grad: {{ $cfg['gradiente'] }}; --txt: {{ $cfg['texto'] }}; --sombra: {{ $cfg['sombra'] }}">
                    <i class="fas {{ $cfg['icon'] }}"></i> {{ $cfg['label'] }} <span class="n">{{ $clientes[$key] ?? 0 }}</span>
                </a>
            @endforeach
            @if(($clientes['outros'] ?? 0) > 0)
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'cliente' => 'outros'] + $filters) }}"
                   class="mt-cliente-btn {{ request('cliente') === 'outros' ? 'on' : '' }}">
                    {{ __('Outros') }} <span class="n">{{ $clientes['outros'] }}</span>
                </a>
            @endif
        </div>

        {{-- Zonas: para planear por onde andam os técnicos --}}
        <div class="mt-zonas">
            <span class="rotulo">{{ __('Zona') }}</span>
            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year] + \Illuminate\Support\Arr::except($filters, 'zona')) }}"
               class="mt-zona-btn {{ request('zona') ? '' : 'on' }}" style="--fg: #263238">
                <i class="fas fa-map"></i> {{ __('Todas') }} <span class="n">{{ $zonas->sum() }}</span>
            </a>
            @foreach($zonasConfig as $key => $cfg)
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'zona' => $key] + $filters) }}"
                   class="mt-zona-btn {{ request('zona') === $key ? 'on' : '' }}" style="--fg: {{ $cfg['color'] }}">
                    <i class="fas {{ $cfg['icon'] }}"></i> {{ __($cfg['label']) }} <span class="n">{{ $zonas[$key] ?? 0 }}</span>
                </a>
            @endforeach
            @if(($zonas['sem'] ?? 0) > 0)
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'zona' => 'sem'] + $filters) }}"
                   class="mt-zona-btn {{ request('zona') === 'sem' ? 'on' : '' }}" title="{{ __('Defina a zona na ficha da loja') }}">
                    {{ __('Sem zona') }} <span class="n">{{ $zonas['sem'] }}</span>
                </a>
            @endif
        </div>

        {{-- Os meses do ano que se está a ver: é por aqui que se planeia --}}
        <div class="mt-meses">
            <span class="rotulo align-self-center">{{ __('Mês') }}</span>
            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year] + \Illuminate\Support\Arr::except($filters, 'mes')) }}"
               class="mt-mes-btn {{ $mesEscolhido ? '' : 'on' }}">{{ __('Ano todo') }}</a>

            @foreach(range(1, 12) as $m)
                @php($n = $meses[$m] ?? 0)
                @php($agora = $m === now()->month && $year === now()->year)
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'mes' => $m] + $filters) }}"
                   class="mt-mes-btn {{ (int) $mesEscolhido === $m ? 'on' : '' }} {{ $n ? '' : 'zero' }} {{ $agora ? 'agora' : '' }}"
                   title="{{ $agora ? __('Este mês') : '' }}">
                    {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::create($year, $m, 1)->isoFormat('MMM')) }}
                    <span class="n">{{ $n }}</span>
                </a>
            @endforeach

            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'mes' => 'sem_data'] + $filters) }}"
               class="mt-mes-btn {{ $mesEscolhido === 'sem_data' ? 'on' : '' }}">
                {{ __('Sem data neste ano') }}
                <span class="n">{{ ($counts['por_definir'] ?? 0) + ($counts['contrato_futuro'] ?? 0) }}</span>
            </a>
        </div>

        @include('backoffice.partials.filtros_ativos', [
            'rota' => 'backoffice.maintenances.index',
            'atuais' => $filters,
            'manter' => ['ano' => $year],
            'ativos' => [
                'estado' => request('estado') ? ['label' => request('estado') === 'feita' ? __('Feitas') : __($states[request('estado')]['label'] ?? request('estado')), 'icon' => $states[request('estado')]['icon'] ?? null] : null,
                'cliente' => request('cliente') ? ['label' => request('cliente') === 'outros' ? __('Outros') : ($clientesConfig[request('cliente')]['label'] ?? request('cliente')), 'icon' => 'fa-building'] : null,
                'zona' => request('zona') ? ['label' => request('zona') === 'sem' ? __('Sem zona') : __($zonasConfig[request('zona')]['label'] ?? request('zona')), 'icon' => 'fa-map-marker-alt'] : null,
                'mes' => $mesEscolhido ? ['label' => $mesEscolhido === 'sem_data' ? __('Sem data neste ano') : \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::create($year, (int) $mesEscolhido, 1)->isoFormat('MMMM')), 'icon' => 'fa-calendar'] : null,
                'q' => filled(request('q')) ? ['label' => '«' . request('q') . '»', 'icon' => 'fa-search'] : null,
            ],
        ])

        <p class="small text-muted mb-0 mt-2">
            <i class="fa fa-info-circle"></i>
            <b>{{ __('Sonae') }}:</b> {{ __('tem de ser feita no mês contratado.') }}
            <b class="ml-2">{{ __('Lidl') }}:</b> {{ __('o contratado é o mês, mas pode ser feita um mês antes ou um mês depois; a data limite é o fim do mês seguinte.') }}
        </p>
    </div>
</div>

{{-- Lista --}}
<div class="card">
    <div class="card-body">
        @if($rows->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                <p class="mb-0">{{ $total ? __('Nenhuma loja corresponde aos filtros.') : __('Ainda não há lojas com manutenção anual registada para este ano.') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table mt-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Loja') }}</th>
                            <th>{{ __('Mês contratado / limite') }} {{ $year }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th>{{ __('Feita em') }}</th>
                            <th>{{ __('Observações') }}</th>
                            <th class="text-right">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @php($state = $states[$row->status])
                            @php($cliente = $row->store->clienteConfig())
                            @if($loop->first || $rows[$loop->index - 1]->grupo !== $row->grupo)
                                @php($g = $grupos[$row->grupo])
                                <tr class="mt-grupo mt-grupo-{{ $g->tipo }}">
                                    <td colspan="6">
                                        <div class="caixa">
                                            <span class="titulo">
                                                <i class="fas {{ ['atraso' => 'fa-exclamation-circle', 'passou' => 'fa-bell', 'sem' => 'fa-calendar-plus'][$g->tipo] ?? 'fa-calendar-alt' }}"></i>
                                                {{ $g->label }}
                                                @if($g->tipo === 'agora')<span class="agora">{{ __('este mês') }}</span>@endif
                                            </span>
                                            <span class="contas">
                                                {{ trans_choice('{1} :count loja|[2,*] :count lojas', $g->total, ['count' => $g->total]) }}
                                                @if($g->tipo !== 'atraso' && $g->tipo !== 'sem')
                                                    · {{ $g->feitas }} {{ __('feitas') }}
                                                @endif
                                            </span>
                                            @if($g->tipo !== 'atraso' && $g->tipo !== 'sem')
                                                <span class="barra" title="{{ $g->feitas }} / {{ $g->total }}"><span style="width: {{ $g->total ? round($g->feitas * 100 / $g->total) : 0 }}%"></span></span>
                                            @endif
                                            <span class="zonas">
                                                @foreach($zonasConfig as $zk => $zc)
                                                    @if($g->zonas[$zk] ?? 0)
                                                        <span class="mt-zona" style="--bg: {{ $zc['bg'] }}; --fg: {{ $zc['color'] }}">{{ __($zc['label']) }} {{ $g->zonas[$zk] }}</span>
                                                    @endif
                                                @endforeach
                                                @if($g->zonas['sem'] ?? 0)
                                                    <span class="mt-zona" style="--bg: #f1f5f9; --fg: #64748b">{{ __('Sem zona') }} {{ $g->zonas['sem'] }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            <tr style="--cliente: {{ $cliente['color'] ?? 'transparent' }}">
                                <td class="mt-store">
                                    @if($cliente)
                                        <span class="mt-cliente"
                                              style="--grad: {{ $cliente['gradiente'] }}; --txt: {{ $cliente['texto'] }}; --sombra: {{ $cliente['sombra'] }}">
                                            <i class="fas {{ $cliente['icon'] }}"></i> {{ $cliente['label'] }}
                                        </span>
                                    @endif
                                    @if($zona = $row->store->zonaConfig())
                                        <span class="mt-zona" style="--bg: {{ $zona['bg'] }}; --fg: {{ $zona['color'] }}">
                                            <i class="fas {{ $zona['icon'] }}"></i> {{ __($zona['label']) }}
                                        </span>
                                    @endif
                                    {{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}
                                    <small>{{ $row->store->cidade }}</small>
                                    @if($row->store->machines->isNotEmpty())
                                        <div class="mt-maquinas">
                                            @foreach($row->store->machines as $maquina)
                                                <div class="mt-maquina">
                                                    <span class="serie"><i class="fa fa-hashtag"></i> {{ $maquina->serial_number }}</span>
                                                    <div class="modelo">
                                                        <i class="fa fa-cogs"></i>
                                                        {{ $maquina->descricao ?: ($maquina->modelo ?: __('Modelo não definido')) }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="mt-window">
                                    @if(!$row->maintenance)
                                        <form method="POST" action="{{ route('backoffice.maintenances.store') }}" class="mt-definir">
                                            @csrf
                                            <input type="hidden" name="store_id" value="{{ $row->store->id }}">
                                            <input type="month" name="data_contratada"
                                                   class="form-control form-control-sm" required>
                                            <button type="submit" class="btn btn-sm btn-primary" title="{{ __('Gravar') }}"><i class="fa fa-check"></i></button>
                                        </form>
                                        <div class="text-muted">{{ __('escolha o mês contratado') }}</div>
                                    @elseif($row->status === 'contrato_futuro')
                                        <span class="mt-mes">{{ $row->maintenance->data_contratada->translatedFormat('F Y') }}</span>
                                        <div class="text-muted">
                                            {{ __('começa em :ano', ['ano' => $row->maintenance->data_contratada->year]) }} ·
                                            <a href="{{ route('backoffice.maintenances.index', ['ano' => $row->maintenance->data_contratada->year] + $filters) }}">{{ __('ver esse ano') }}</a>
                                        </div>
                                    @elseif($row->maintenance->isFlexivel())
                                        <span class="mt-mes">{{ $row->due->translatedFormat('F') }} {{ $year }}</span>
                                        <div class="text-muted">{{ __('pode ser de :de a :a', ['de' => $row->start->isoFormat('MMM'), 'a' => $row->end->isoFormat('MMM')]) }}</div>
                                        <div class="mt-limite"><i class="fa fa-flag-checkered"></i> {{ __('limite') }} {{ $row->end->format('d/m/Y') }}</div>
                                    @else
                                        <span class="mt-mes">{{ $row->due->translatedFormat('F') }} {{ $year }}</span>
                                        <div class="text-muted">{{ __('só neste mês') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="mt-badge" style="--bg: {{ $state['bg'] }}; --fg: {{ $state['color'] }}"><i class="fas {{ $state['icon'] }}"></i> {{ __($state['label']) }}</span>
                                    @if($row->status === 'atrasada')
                                        <div class="small text-danger mt-1">{{ $row->end->diffInDays(now()->startOfDay()) }} {{ __('dias de atraso') }}</div>
                                    @elseif($row->status === 'janela' && $row->maintenance->isFlexivel())
                                        @if($row->due->lt(now()->startOfMonth()))
                                            <div class="small mt-1 mt-passou"><i class="fa fa-bell"></i> {{ __('o mês já passou') }}</div>
                                        @endif
                                        <div class="small mt-1" style="color: {{ $state['color'] }}">{{ trans_choice('{0} hoje é o dia limite|{1} falta :count dia para o limite|[2,*] faltam :count dias para o limite', $n = now()->startOfDay()->diffInDays($row->end), ['count' => $n]) }}</div>
                                    @elseif($row->status === 'janela')
                                        <div class="small mt-1" style="color: {{ $state['color'] }}">{{ trans_choice('{0} hoje é o último dia do mês|{1} falta :count dia para acabar o mês|[2,*] faltam :count dias para acabar o mês', $n = now()->startOfDay()->diffInDays($row->end), ['count' => $n]) }}</div>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($row->record)
                                        <span class="mt-done-date"><i class="fa fa-check-circle"></i> {{ $row->record->data_realizada->format('d/m/Y') }}</span>
                                        @if($row->record->technician)<div>{{ $row->record->technician->name }}</div>@endif
                                        @if($row->record->observacoes)<div class="text-muted">{{ \Illuminate\Support\Str::limit($row->record->observacoes, 60) }}</div>@endif
                                        @php($folhas = $row->record->files->where('tipo', 'folha'))
                                        @php($fotos = $row->record->files->where('tipo', 'foto'))

                                        @if($folhas->isNotEmpty())
                                            <div class="mt-anexos">
                                                @foreach($folhas as $anexo)
                                                    @php($ico = $anexo->iconeConfig())
                                                    <span class="mt-anexo" style="--c: {{ $ico['cor'] }}">
                                                        <a href="{{ route('backoffice.maintenances.file', $anexo->id) }}" target="_blank" rel="noopener"
                                                           title="{{ $anexo->nome }} {{ $anexo->tamanhoLegivel() }}">
                                                            <i class="fas {{ $ico['icon'] }}"></i>
                                                            {{ \Illuminate\Support\Str::limit($anexo->nome, 16) }}
                                                            <small>{{ strtoupper($anexo->extensao()) }}</small>
                                                        </a>
                                                        <form method="POST" action="{{ route('backoffice.maintenances.file.delete', $anexo->id) }}"
                                                              onsubmit="return confirm({{ json_encode(__('Remover este anexo?')) }})">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" title="{{ __('Remover') }}">&times;</button>
                                                        </form>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($fotos->isNotEmpty())
                                            <div class="mt-fotos">
                                                @foreach($fotos as $foto)
                                                    <span class="mt-foto">
                                                        @if($foto->isImagem())
                                                            <button type="button" data-toggle="modal" data-target="#mt-foto"
                                                                    data-src="{{ route('backoffice.maintenances.file', $foto->id) }}"
                                                                    data-nome="{{ $foto->nome }}"
                                                                    title="{{ $foto->nome }} {{ $foto->tamanhoLegivel() }}"
                                                                    style="background-image: url('{{ route('backoffice.maintenances.file.thumb', $foto->id) }}')"></button>
                                                        @else
                                                            {{-- HEIC e companhia: o browser não os mostra, fica o ícone --}}
                                                            <a href="{{ route('backoffice.maintenances.file', $foto->id) }}" target="_blank" rel="noopener"
                                                               class="sem-previa" title="{{ $foto->nome }}"><i class="fas fa-file-image"></i></a>
                                                        @endif
                                                        <form method="POST" action="{{ route('backoffice.maintenances.file.delete', $foto->id) }}"
                                                              onsubmit="return confirm({{ json_encode(__('Remover esta fotografia?')) }})">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="apagar" title="{{ __('Remover') }}">&times;</button>
                                                        </form>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($row->record->files->isEmpty())
                                            <span class="mt-doc-missing"><i class="fa fa-paperclip"></i> {{ __('sem anexos') }}</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="mt-notes">
                                    @if($row->maintenance?->observacoes)
                                        {{ $row->maintenance->observacoes }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right text-nowrap">
                                    @if(!$row->maintenance)
                                        <span class="text-muted">—</span>
                                    @elseif($row->record)
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#mt-attach"
                                                title="{{ __('Anexar folhas de serviço ou fotografias') }}"
                                                data-action="{{ route('backoffice.maintenances.files.store', $row->record->id) }}"
                                                data-store="{{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}">
                                            <i class="fa fa-paperclip"></i>
                                        </button>
                                        <form method="POST" action="{{ route('backoffice.maintenances.undo', $row->record->id) }}" class="d-inline"
                                              onsubmit="return confirm({{ json_encode(__('Anular o registo desta manutenção? Volta a ficar por fazer (a folha de obra anexada também é apagada).')) }})">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-secondary" title="{{ __('Anular registo') }}"><i class="fa fa-undo"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#mt-done"
                                                data-action="{{ route('backoffice.maintenances.done', $row->maintenance->id) }}"
                                                data-store="{{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}"
                                                data-window="{{ $row->maintenance->isFlexivel()
                                                    ? \Illuminate\Support\Str::ucfirst($row->due->translatedFormat('F \d\e Y')) . ' (' . __('pode ser de :de a :a', ['de' => $row->start->format('d/m/Y'), 'a' => $row->end->format('d/m/Y')]) . ')'
                                                    : \Illuminate\Support\Str::ucfirst($row->due->translatedFormat('F \d\e Y')) . ' (' . __('só neste mês') . ')' }}">
                                            <i class="fa fa-check"></i> {{ __('Marcar feita') }}
                                        </button>
                                    @endif
                                    @if($row->maintenance)
                                    <button type="button" class="btn btn-sm btn-link text-secondary px-1" title="{{ __('Editar') }}" data-toggle="modal" data-target="#mt-edit"
                                            data-action="{{ route('backoffice.maintenances.update', $row->maintenance->id) }}"
                                            data-delete="{{ route('backoffice.maintenances.destroy', $row->maintenance->id) }}"
                                            data-store="{{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}"
                                            data-mes="{{ $row->maintenance->data_contratada->format('Y-m') }}"
                                            data-notes="{{ $row->maintenance->observacoes }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Modal: marcar como feita --}}
<div class="modal fade" id="mt-done" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content" id="mt-done-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ano" value="{{ $year }}">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-check-circle text-success"></i> {{ __('Manutenção feita') }} {{ $year }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-1 font-weight-bold" id="mt-done-store"></p>
                <p class="small text-muted">{{ __('Prazo') }}: <span id="mt-done-window"></span></p>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Data em que foi feita') }}</label>
                    <input type="date" name="data_realizada" class="form-control" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                </div>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Técnico') }}</label>
                    <select name="user_id" class="form-control">
                        <option value="">—</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="small mb-1"><i class="fa fa-file-alt"></i> {{ __('Folhas de serviço (opcional)') }}</label>
                    <input type="file" name="folhas[]" multiple class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx">
                    <small class="form-text text-muted">{{ __('PDF, foto ou Word, até 32 MB cada. Podem ser anexadas mais tarde.') }}</small>
                </div>
                <div class="form-group mb-0">
                    <label class="small mb-1"><i class="fa fa-camera"></i> {{ __('Fotografias (opcional)') }}</label>
                    <input type="file" name="fotos[]" multiple class="form-control-file" accept="image/*,.heic,.heif">
                    <small class="form-text text-muted">{{ __('Pode escolher várias de uma vez.') }}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{ __('Marcar como feita') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: anexar folhas de serviço ou fotografias --}}
<div class="modal fade" id="mt-attach" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="mt-attach-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-paperclip text-primary"></i> {{ __('Anexar à manutenção') }} {{ $year }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="font-weight-bold" id="mt-attach-store"></p>
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Tipo de anexo') }}</label>
                        <select name="tipo" class="form-control">
                            @foreach(\App\Models\StoreMaintenanceFile::TIPOS as $chave => $tipo)
                                <option value="{{ $chave }}">{{ __($tipo['label']) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <input type="file" name="ficheiros[]" multiple class="form-control-file"
                               accept=".pdf,.jpg,.jpeg,.png,.heic,.heif,.webp,.doc,.docx" required>
                        <small class="form-text text-muted">{{ __('Pode escolher vários ficheiros de uma vez, até 32 MB cada.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> {{ __('Anexar') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: ver a fotografia em grande --}}
<div class="modal fade" id="mt-foto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mt-foto-nome"></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center" style="background: #0f172a">
                <img id="mt-foto-img" src="" alt="" style="max-width: 100%; max-height: 70vh; border-radius: 8px">
            </div>
            <div class="modal-footer">
                <a id="mt-foto-abrir" href="#" target="_blank" rel="noopener" class="btn btn-outline-primary">
                    <i class="fa fa-external-link-alt"></i> {{ __('Abrir em separado') }}
                </a>
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Fechar') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: editar contrato --}}
<div class="modal fade" id="mt-edit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="mt-edit-form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> {{ __('Editar manutenção anual') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="font-weight-bold" id="mt-edit-store"></p>
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Mês contratado') }}</label>
                        <input type="month" name="data_contratada" id="mt-edit-date" class="form-control" required>
                        <small class="form-text text-muted">{{ __('Repete-se todos os anos neste mês.') }}</small>
                    </div>
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Observações') }}</label>
                        <textarea name="observacoes" id="mt-edit-notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" form="mt-delete-form" class="btn btn-link text-danger" onclick="return confirm({{ json_encode(__('Remover esta loja das manutenções anuais? O histórico também é apagado.')) }})">
                        <i class="fa fa-trash"></i> {{ __('Remover') }}
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                    </div>
                </div>
            </form>
            <form method="POST" id="mt-delete-form">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.jQuery) return;
        $('#mt-done').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-done-form').attr('action', b.data('action'));
            $('#mt-done-store').text(b.data('store'));
            $('#mt-done-window').text(b.data('window'));
        });
        $('#mt-foto').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-foto-img').attr('src', b.data('src'));
            $('#mt-foto-nome').text(b.data('nome'));
            $('#mt-foto-abrir').attr('href', b.data('src'));
        });
        $('#mt-attach').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-attach-form').attr('action', b.data('action'));
            $('#mt-attach-store').text(b.data('store'));
        });
        $('#mt-edit').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-edit-form').attr('action', b.data('action'));
            $('#mt-delete-form').attr('action', b.data('delete'));
            $('#mt-edit-store').text(b.data('store'));
            $('#mt-edit-date').val(b.data('mes') || '');
            $('#mt-edit-notes').val(b.data('notes') || '');
        });

        if ($('.selectpicker').length && $.fn.selectpicker) $('.selectpicker').selectpicker();
    });
</script>
@endpush

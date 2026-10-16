@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Pedidos de Assistência Técnica') }}</title>
@endsection

@section('head-scripts')
<style>
    .trb { min-width: 0; max-width: 100%; --trb-pendente: #f59e0b; --trb-agendado: #0ea5e9; --trb-aguarda_peca: #ef4444; --trb-concluido: #10b981; }

    /* Cabeçalho */
    .trb-hero { position: relative; overflow: hidden; border-radius: 22px; padding: 1.6rem 1.75rem; color: #fff; margin-bottom: 1.25rem;
                background: linear-gradient(120deg, #1e3a8a 0%, #2563eb 45%, #7c3aed 100%); box-shadow: 0 18px 40px -18px rgba(37, 99, 235, .65); }
    .trb-hero::before, .trb-hero::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .08); }
    .trb-hero::before { width: 260px; height: 260px; right: -60px; top: -110px; }
    .trb-hero::after { width: 160px; height: 160px; right: 180px; bottom: -90px; }
    .trb-hero > * { position: relative; z-index: 1; }
    .trb-hello { font-size: .85rem; opacity: .85; letter-spacing: .02em; }
    .trb-title { font-size: 1.65rem; font-weight: 800; margin: .15rem 0 .35rem; }
    .trb-sub { opacity: .85; margin: 0; max-width: 560px; }
    .trb-hero .btn { border-radius: 12px; font-weight: 600; }
    .trb-hero .btn-light { color: #1e3a8a; }
    .trb-hero .btn-ghost { background: rgba(255, 255, 255, .14); color: #fff; border: 1px solid rgba(255, 255, 255, .25); }
    .trb-hero .btn-ghost:hover { background: rgba(255, 255, 255, .24); color: #fff; }

    .trb-today { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: 1.1rem; }
    .trb-pill { display: inline-flex; align-items: center; gap: .5rem; padding: .45rem .85rem; border-radius: 999px;
                background: rgba(255, 255, 255, .15); backdrop-filter: blur(4px); font-size: .88rem; }
    .trb-pill strong { font-size: 1.05rem; }
    .trb-pill .fa { opacity: .9; }

    /* Barra de progresso dos estados */
    .trb-progress { display: flex; height: 10px; border-radius: 999px; overflow: hidden; background: rgba(255, 255, 255, .2); margin-top: 1.1rem; }
    .trb-progress span { display: block; height: 100%; transition: width 1s ease; width: 0; }

    /* Filtros */
    .trb-filters { display: flex; flex-wrap: wrap; gap: .6rem; align-items: center; background: #fff; border-radius: 16px; padding: .75rem;
                   box-shadow: 0 6px 20px -12px rgba(15, 23, 42, .35); margin-bottom: 1.25rem; }
    .trb-filters .form-control { border-radius: 10px; min-width: 150px; flex: 1 1 150px; }
    .trb-filters .trb-search { flex: 2 1 220px; }
    .trb-filters .btn { border-radius: 10px; }
    .trb-filters .bootstrap-select { flex: 1 1 180px; width: auto !important; }

    /* Quadro */
    .trb-board { display: grid; grid-template-columns: repeat(4, minmax(270px, 1fr)); gap: 1rem; align-items: start; }
    .trb-col { background: #f1f5f9; border-radius: 18px; padding: .75rem; min-height: 200px; }
    .trb-col-head { display: flex; align-items: center; justify-content: space-between; padding: .25rem .35rem .75rem; position: sticky; top: 0; }
    .trb-col-title { display: flex; align-items: center; gap: .55rem; font-weight: 700; color: #0f172a; }
    .trb-col-dot { width: 30px; height: 30px; border-radius: 10px; display: grid; place-items: center; color: #fff; font-size: .85rem; }
    .trb-col-count { background: #fff; border-radius: 999px; padding: .1rem .65rem; font-weight: 700; font-size: .85rem; color: #334155; }
    .trb-col-hint { font-size: .72rem; color: #64748b; font-weight: 500; }
    .trb-col-list { display: flex; flex-direction: column; gap: .65rem; max-height: 72vh; overflow-y: auto; padding: 2px; }

    .trb-card { display: block; position: relative; background: #fff; border-radius: 14px; padding: .8rem .85rem .75rem 1rem; color: inherit;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .06), 0 4px 14px -10px rgba(15, 23, 42, .4);
                transition: transform .18s ease, box-shadow .18s ease; animation: trb-in .35s ease both; }
    .trb-card:hover { text-decoration: none; color: inherit; transform: translateY(-3px) rotate(-.4deg);
                      box-shadow: 0 2px 4px rgba(15, 23, 42, .06), 0 14px 28px -14px rgba(15, 23, 42, .45); }
    .trb-card::before { content: ""; position: absolute; left: 0; top: 12px; bottom: 12px; width: 4px; border-radius: 0 4px 4px 0; background: #cbd5e1; }
    .trb-card.prio-alta::before { background: #ef4444; }
    .trb-card.prio-media::before { background: #f59e0b; }
    .trb-card.prio-baixa::before { background: #38bdf8; }
    @keyframes trb-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

    .trb-card-top { display: flex; justify-content: space-between; gap: .5rem; align-items: flex-start; }
    .trb-store { font-weight: 700; font-size: .93rem; color: #0f172a; line-height: 1.25; }
    .trb-store small { display: block; font-weight: 500; color: #64748b; font-size: .76rem; margin-top: .1rem; }
    .trb-prio { flex-shrink: 0; font-size: .7rem; font-weight: 700; padding: .2rem .5rem; border-radius: 8px; white-space: nowrap; }
    .trb-prio.prio-alta { background: #fee2e2; color: #b91c1c; }
    .trb-prio.prio-media { background: #fef3c7; color: #92400e; }
    .trb-prio.prio-baixa { background: #e0f2fe; color: #0369a1; }
    .trb-desc { font-size: .84rem; color: #334155; margin: .5rem 0 .6rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .trb-tags { display: flex; flex-wrap: wrap; gap: .3rem; margin-bottom: .6rem; }
    .trb-tag { font-size: .7rem; padding: .15rem .5rem; border-radius: 6px; background: #f1f5f9; color: #475569; white-space: nowrap; }
    .trb-tag.sched { background: #e0f2fe; color: #0369a1; font-weight: 600; }
    .trb-tag.done { background: #d1fae5; color: #047857; font-weight: 600; }

    .trb-card-foot { display: flex; align-items: center; justify-content: space-between; gap: .5rem; border-top: 1px dashed #e2e8f0; padding-top: .55rem; }
    .trb-person { display: flex; align-items: center; gap: .45rem; min-width: 0; font-size: .78rem; color: #334155; }
    .trb-person span:last-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .trb-avatar { width: 28px; height: 28px; flex-shrink: 0; border-radius: 50%; display: grid; place-items: center; color: #fff; font-size: .7rem; font-weight: 700;
                  box-shadow: 0 0 0 2px #fff, 0 0 0 3px rgba(15, 23, 42, .08); }
    .trb-avatar.none { background: repeating-linear-gradient(45deg, #cbd5e1, #cbd5e1 4px, #e2e8f0 4px, #e2e8f0 8px); color: #475569; }
    .trb-age { flex-shrink: 0; font-size: .7rem; font-weight: 600; padding: .15rem .5rem; border-radius: 999px; }
    .trb-age.fresh { background: #dcfce7; color: #15803d; }
    .trb-age.warm { background: #fef3c7; color: #a16207; }
    .trb-age.hot { background: #fee2e2; color: #b91c1c; }
    .trb-age.hot .fa { animation: trb-pulse 1.4s ease-in-out infinite; }
    @keyframes trb-pulse { 50% { opacity: .35; } }
    .trb-icons { display: flex; gap: .55rem; color: #94a3b8; font-size: .75rem; }

    .trb-empty { text-align: center; color: #64748b; padding: 1.75rem .75rem; border: 2px dashed #cbd5e1; border-radius: 14px; font-size: .85rem; }
    .trb-empty .fa { display: block; font-size: 1.8rem; margin-bottom: .5rem; color: #94a3b8; }
    .trb-empty.happy .fa { color: #10b981; animation: trb-float 3s ease-in-out infinite; }
    @keyframes trb-float { 50% { transform: translateY(-5px); } }

    .trb-note { font-size: .78rem; color: #64748b; text-align: center; margin-top: 1rem; }

    @media (max-width: 1199px) {
        .trb-board { grid-template-columns: none; grid-auto-flow: column; grid-auto-columns: minmax(260px, 82%); overflow-x: auto;
                     scroll-snap-type: x mandatory; padding-bottom: .5rem; max-width: 100%; }
        .trb-filters .form-control, .trb-filters .bootstrap-select { min-width: 0; }
        .trb-col { scroll-snap-align: start; }
        .trb-col-list { max-height: none; }
    }
    @media (max-width: 575px) {
        .trb-hero { padding: 1.25rem; border-radius: 18px; }
        .trb-title { font-size: 1.35rem; }
        .trb-hero-actions .btn { width: 100%; margin: .35rem 0 0 !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        .trb-card, .trb-empty.happy .fa, .trb-age.hot .fa { animation: none; }
        .trb-progress span { transition: none; }
    }
</style>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? __('Bom dia') : ($hour < 20 ? __('Boa tarde') : __('Boa noite'));
    $firstName = \Illuminate\Support\Str::of(auth()->user()->name ?? '')->explode(' ')->first();
    $colMeta = [
        'pendente' => ['icon' => 'fa-clock', 'hint' => null, 'empty' => __('Nada pendente. Bom trabalho!'), 'happy' => true],
        'agendado' => ['icon' => 'fa-calendar-check', 'hint' => null, 'empty' => __('Sem pedidos agendados.'), 'happy' => false],
        'aguarda_peca' => ['icon' => 'fa-tools', 'hint' => null, 'empty' => __('Nenhum pedido à espera de peças.'), 'happy' => true],
        'concluido' => ['icon' => 'fa-check', 'hint' => __('últimos 15'), 'empty' => __('Ainda não há pedidos concluídos.'), 'happy' => false],
    ];
    $palette = ['#2563eb', '#7c3aed', '#db2777', '#ea580c', '#059669', '#0891b2', '#4f46e5', '#b45309', '#0d9488', '#9333ea'];
    $avatarColor = fn ($id) => $palette[$id % count($palette)];
    $initials = function ($name) {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        return mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
    };
    $total = max(1, $stats['pendente'] + $stats['agendado'] + $stats['aguarda_peca'] + $stats['concluido']);
    $keep = request()->only(['estado']);
@endphp

<div class="trb">
    <div class="trb-hero">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
            <div>
                <div class="trb-hello">{{ $greeting }}{{ $firstName ? ', ' . $firstName : '' }} 👋</div>
                <h4 class="trb-title">{{ __('Quadro de Pedidos') }}</h4>
                <p class="trb-sub">{{ __('Cada coluna é um estado. Os pedidos mais urgentes e mais antigos aparecem primeiro.') }}</p>
            </div>
            <div class="mt-3 mt-lg-0 trb-hero-actions">
                @if($canManageAll)
                    <a href="{{ route('backoffice.technical_requests.create') }}" class="btn btn-light mr-2 px-3">
                        <i class="fa fa-plus"></i> {{ __('Novo Pedido') }}
                    </a>
                @endif
                <a href="{{ route('backoffice.technical_requests.index', ['vista' => 'lista']) }}" class="btn btn-ghost px-3">
                    <i class="fa fa-th-list"></i> {{ __('Vista clássica') }}
                </a>
            </div>
        </div>

        <div class="trb-today">
            <span class="trb-pill"><i class="fa fa-inbox"></i> {{ __('Hoje') }}: <strong data-count="{{ $todayStats['novos'] }}">0</strong> {{ __('novos') }}</span>
            <span class="trb-pill"><i class="fa fa-check-circle"></i> <strong data-count="{{ $todayStats['concluidos'] }}">0</strong> {{ __('concluídos hoje') }}</span>
            <span class="trb-pill"><i class="fa fa-fire"></i> <strong data-count="{{ $todayStats['urgentes'] }}">0</strong> {{ __('urgentes em aberto') }}</span>
        </div>

        <div class="trb-progress" title="{{ __('Distribuição dos pedidos por estado') }}">
            @foreach(['concluido', 'agendado', 'aguarda_peca', 'pendente'] as $state)
                <span data-width="{{ round($stats[$state] / $total * 100, 1) }}" style="background: var(--trb-{{ $state }})" title="{{ $statuses[$state] }}: {{ $stats[$state] }}"></span>
            @endforeach
        </div>
    </div>

    <form method="GET" class="trb-filters">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control trb-search" placeholder="{{ __('Loja, origem, descrição...') }}">
        @if($canManageAll)
            <select name="assigned_technician_id" class="form-control selectpicker" data-live-search="true" data-none-results-text="{{ __('Sem resultados para :term', ['term' => '{0}']) }}" title="{{ __('Técnico') }}">
                <option value="">{{ __('Todos os técnicos') }}</option>
                <option value="unassigned" {{ request('assigned_technician_id') === 'unassigned' ? 'selected' : '' }}>{{ __('Por atribuir') }}</option>
                @foreach($technicians as $technician)
                    <option value="{{ $technician->id }}" {{ (string) request('assigned_technician_id') === (string) $technician->id ? 'selected' : '' }}>{{ $technician->name ?: $technician->email }}</option>
                @endforeach
            </select>
        @endif
        <select name="prioridade" class="form-control">
            <option value="">{{ __('Prioridade') }}: {{ __('Todas') }}</option>
            @foreach($priorities as $value => $label)
                <option value="{{ $value }}" {{ request('prioridade') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="zona" class="form-control">
            <option value="">{{ __('Zona') }}: {{ __('Todas') }}</option>
            @foreach($zones as $value => $label)
                <option value="{{ $value }}" {{ request('zona') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @foreach((array) request('estado', []) as $state)
            <input type="hidden" name="estado[]" value="{{ $state }}">
        @endforeach
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> {{ __('Filtrar') }}</button>
        @if(request()->hasAny(['q', 'assigned_technician_id', 'prioridade', 'zona', 'estado']))
            <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary"><i class="fa fa-undo"></i> {{ __('Limpar') }}</a>
        @endif
    </form>

    <div class="trb-board">
        @foreach($columns as $state => $items)
            <section class="trb-col">
                <div class="trb-col-head">
                    <div class="trb-col-title">
                        <span class="trb-col-dot" style="background: var(--trb-{{ $state }})"><i class="fa {{ $colMeta[$state]['icon'] }}"></i></span>
                        <span>
                            {{ $statuses[$state] }}
                            @if($colMeta[$state]['hint'])<div class="trb-col-hint">{{ $colMeta[$state]['hint'] }}</div>@endif
                        </span>
                    </div>
                    <span class="trb-col-count">{{ $stats[$state] }}</span>
                </div>

                <div class="trb-col-list">
                    @forelse($items as $i => $req)
                        @php
                            $opened = $req->created_at ?? \Carbon\Carbon::parse($req->data_pedido);
                            $days = $opened->diffInDays(now());
                            $ageClass = $days < 1 ? 'fresh' : ($days <= 3 ? 'warm' : 'hot');
                            $person = $req->assignedTechnician;
                            $personName = $person ? ($person->name ?: $person->email) : __('Por atribuir');
                        @endphp
                        <a href="{{ route('backoffice.technical_requests.show', ['id' => $req->id, 'return_url' => url()->full()]) }}"
                           class="trb-card prio-{{ $req->prioridade }}" style="animation-delay: {{ min($i, 12) * 30 }}ms">
                            <div class="trb-card-top">
                                <div class="trb-store">
                                    {{ $req->store->codigo_loja ?? '—' }} · {{ $req->store->nome_loja ?? __('Sem loja associada') }}
                                    <small>
                                        @if($req->zona)<i class="fa fa-map-marker-alt"></i> {{ $zones[$req->zona] ?? ucfirst($req->zona) }} · @endif
                                        {{ $serviceTypes[$req->tipo_servico] ?? ucfirst((string) $req->tipo_servico) }}
                                    </small>
                                </div>
                                <span class="trb-prio prio-{{ $req->prioridade }}">
                                    @if($req->prioridade === 'alta')<i class="fa fa-fire"></i>@endif
                                    {{ $priorities[$req->prioridade] ?? ucfirst((string) $req->prioridade) }}
                                </span>
                            </div>

                            <div class="trb-desc">{{ $req->descricao_problema ?: __('Sem descrição.') }}</div>

                            @if(($state === 'agendado' && $req->data_agendamento) || ($state === 'concluido' && $req->data_resolucao) || $req->machine)
                                <div class="trb-tags">
                                    @if($state === 'agendado' && $req->data_agendamento)
                                        <span class="trb-tag sched"><i class="fa fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($req->data_agendamento)->translatedFormat('D d/m H:i') }}</span>
                                    @endif
                                    @if($state === 'concluido' && $req->data_resolucao)
                                        <span class="trb-tag done"><i class="fa fa-check"></i> {{ \Carbon\Carbon::parse($req->data_resolucao)->format('d/m H:i') }}</span>
                                    @endif
                                    @if($req->machine)
                                        <span class="trb-tag"><i class="fa fa-hashtag"></i> {{ $req->machine->serial_number }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="trb-card-foot">
                                <div class="trb-person">
                                    @if($person)
                                        <span class="trb-avatar" style="background: {{ $avatarColor($person->id) }}">{{ $initials($personName) }}</span>
                                    @else
                                        <span class="trb-avatar none"><i class="fa fa-question"></i></span>
                                    @endif
                                    <span>{{ $personName }}</span>
                                </div>
                                <div class="d-flex align-items-center" style="gap: .5rem">
                                    @if($req->files->count())
                                        <span class="trb-icons"><span><i class="fa fa-paperclip"></i> {{ $req->files->count() }}</span></span>
                                    @endif
                                    @if($state !== 'concluido')
                                        <span class="trb-age {{ $ageClass }}" title="{{ __('Aberto em') }} {{ $opened->format('d/m/Y H:i') }}">
                                            @if($ageClass === 'hot')<i class="fa fa-hourglass-half"></i>@endif
                                            {{ $opened->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="trb-empty {{ $colMeta[$state]['happy'] ? 'happy' : '' }}">
                            <i class="fa {{ $colMeta[$state]['happy'] ? 'fa-smile-beam' : 'fa-inbox' }}"></i>
                            {{ $colMeta[$state]['empty'] }}
                        </div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    <p class="trb-note"><i class="fa fa-flask"></i> {{ __('Vista experimental. Pode voltar à vista clássica a qualquer momento.') }}</p>
</div>
@endsection

@section('foot-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $('.selectpicker').length) {
        $('.selectpicker').selectpicker();
    }

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Números do cabeçalho a contar
    document.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        if (reduce || target === 0) { el.textContent = target; return; }
        var start = null;
        var step = function (ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / 700, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    });

    // Barra de estados a encher
    setTimeout(function () {
        document.querySelectorAll('.trb-progress span').forEach(function (el) {
            el.style.width = el.getAttribute('data-width') + '%';
        });
    }, 80);
});
</script>
@endsection

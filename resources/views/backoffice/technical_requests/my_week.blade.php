@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('A Minha Semana') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($cores = ['alta' => '#b3261e', 'media' => '#d39d00', 'baixa' => '#208a47'])
@php($zonas = ['norte' => '#0f766e', 'centro' => '#b45309', 'sul' => '#be185d'])

@push('styles')
<style>
    .ms-faixa { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem;
                padding: .8rem 1rem; color: #fff; border-radius: 14px;
                background: linear-gradient(115deg, #0b5ed7, #1b9aaa 55%, #20a464); margin-bottom: .9rem; }
    .ms-faixa .t { font-size: 1.1rem; font-weight: 800; }
    .ms-faixa .sub { display: block; font-size: .76rem; opacity: .85; }
    .ms-nav { display: flex; align-items: center; gap: .3rem; }
    .ms-nav .data { font-weight: 700; font-size: .85rem; min-width: 8.5rem; text-align: center; }
    .ms-seta { border: 1px solid rgba(255, 255, 255, .35); background: rgba(255, 255, 255, .12); color: #fff;
               border-radius: 8px; padding: .1rem .55rem; }
    .ms-seta:hover { background: #fff; color: #0b5ed7; }

    .ms-dia { background: #fff; border: 1px solid #e3e9f1; border-radius: 14px; overflow: hidden; margin-bottom: .7rem;
              box-shadow: 0 1px 3px rgba(19, 34, 56, .06); }
    .ms-dia > header { display: flex; align-items: baseline; gap: .5rem; padding: .55rem .9rem;
                       background: #f4f7fb; border-bottom: 1px solid #e9eff6; }
    .ms-dia > header .nome { font-weight: 800; color: #132238; }
    .ms-dia > header .d { font-size: .8rem; color: #6c7a89; }
    .ms-dia > header .n { margin-left: auto; font-size: .72rem; font-weight: 800; color: #0b5ed7;
                          background: #e7f0ff; border-radius: 999px; padding: .05rem .55rem; }
    .ms-dia.hoje { border-color: #9dc2f5; box-shadow: 0 3px 12px rgba(11, 94, 215, .16); }
    .ms-dia.hoje > header { background: linear-gradient(100deg, #0b5ed7, #1b9aaa); }
    .ms-dia.hoje > header .nome, .ms-dia.hoje > header .d { color: #fff; }
    .ms-dia.hoje > header .n { background: #fff; color: #0b5ed7; }
    .ms-dia.vazio { opacity: .75; }

    .ms-paragem { display: block; padding: .6rem .9rem; border-top: 1px solid #f1f5f9; text-decoration: none !important;
                  border-left: 5px solid var(--p, #cbd5e1); }
    .ms-paragem:first-of-type { border-top: 0; }
    .ms-paragem:hover { background: #f9fbfe; }
    .ms-topo { display: flex; flex-wrap: wrap; align-items: center; gap: .25rem; margin-bottom: .15rem; }
    .ms-topo .n { font-size: .7rem; font-weight: 800; color: #94a3b8; margin-right: auto; }
    .ms-chip { font-size: .62rem; font-weight: 800; text-transform: uppercase; letter-spacing: .03em;
               border-radius: 999px; padding: .03rem .45rem; color: #fff; background: var(--c, #64748b); }
    .ms-chip.clara { background: #fff; color: var(--c); border: 1px solid currentColor; }
    .ms-marca { font-size: .62rem; font-weight: 800; text-transform: uppercase; border-radius: 4px;
                padding: .03rem .35rem; color: var(--c); background: var(--bg); }
    .ms-loja { font-weight: 700; color: #132238; line-height: 1.25; }
    .ms-tipo { font-size: .8rem; color: #6c7a89; }
    .ms-tipo .peca { color: #b26a00; font-weight: 700; }
    .ms-serie { font-family: Consolas, Menlo, monospace; font-weight: 700; color: #0b5ed7; letter-spacing: -.02em; }
    /* A avaria em três linhas: chega para saber o que leva na carrinha */
    .ms-avaria { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
                 font-size: .82rem; color: #334155; line-height: 1.3; margin-top: .2rem;
                 border-left: 3px solid #e3e9f1; padding-left: .5rem; }
    .ms-morada { font-size: .8rem; color: #6c7a89; margin-top: .2rem; }
    .ms-botoes { display: flex; gap: .4rem; margin-top: .5rem; }
    .ms-botao { flex: 1; text-align: center; border-radius: 9px; padding: .4rem .5rem; font-size: .82rem;
                font-weight: 700; text-decoration: none !important; }
    .ms-botao.mapa { background: #e7f0ff; color: #0b5ed7; }
    .ms-botao.mapa:hover { background: #0b5ed7; color: #fff; }
    .ms-botao.ver { background: #f1f5f9; color: #475569; }
    .ms-botao.ver:hover { background: #475569; color: #fff; }
    .ms-nada { padding: .9rem; color: #9aa7b6; font-size: .85rem; }
</style>
@endpush

@section('content')

<div class="ms-faixa">
    <div>
        <div class="t"><i class="fas fa-route"></i> {{ __('A Minha Semana') }}</div>
        <span class="sub">{{ trans_choice('{0} sem serviços marcados|{1} :count serviço marcado|[2,*] :count serviços marcados', $total, ['count' => $total]) }}</span>
    </div>
    <div class="ms-nav">
        <a class="ms-seta" href="{{ route('backoffice.technical_requests.my_week', ['semana' => $inicio->copy()->subWeek()->toDateString()]) }}"><i class="fa fa-chevron-left"></i></a>
        <span class="data">{{ $inicio->format('d/m') }} — {{ $fim->format('d/m') }}</span>
        <a class="ms-seta" href="{{ route('backoffice.technical_requests.my_week', ['semana' => $inicio->copy()->addWeek()->toDateString()]) }}"><i class="fa fa-chevron-right"></i></a>
    </div>
</div>

@foreach($dias as $dia)
    @php($doDia = $pedidos[$dia->toDateString()] ?? collect())
    <section class="ms-dia {{ $dia->isToday() ? 'hoje' : '' }} {{ $doDia->isEmpty() ? 'vazio' : '' }}">
        <header>
            <span class="nome">{{ \Illuminate\Support\Str::ucfirst($dia->isoFormat('dddd')) }}</span>
            <span class="d">{{ $dia->format('d/m') }}</span>
            @if($doDia->isNotEmpty())<span class="n">{{ $doDia->count() }}</span>@endif
        </header>

        @forelse($doDia as $p)
            <div class="ms-paragem" style="--p: {{ $cores[$p->prioridade] ?? '#cbd5e1' }}">
                @php($cliente = $p->store?->clienteConfig())
                @php($morada = collect([$p->store?->morada, $p->store?->codigo_postal, $p->store?->cidade])->filter()->implode(', '))
                <div class="ms-topo">
                    <span class="n">#{{ $p->id }}</span>
                    @if($p->zona)
                        <span class="ms-chip clara" style="--c: {{ $zonas[$p->zona] ?? '#64748b' }}">{{ __(\App\Http\Controllers\TechnicalRequestController::ZONES[$p->zona] ?? $p->zona) }}</span>
                    @endif
                    <span class="ms-chip {{ $p->prioridade === 'alta' ? '' : 'clara' }}" style="--c: {{ $cores[$p->prioridade] ?? '#94a3b8' }}">
                        {{ __(\App\Http\Controllers\TechnicalRequestController::PRIORITIES[$p->prioridade] ?? $p->prioridade) }}
                    </span>
                </div>
                <div class="ms-loja">
                    @if($cliente)<span class="ms-marca" style="--c: {{ $cliente['color'] }}; --bg: {{ $cliente['bg'] }}">{{ $cliente['label'] }}</span>@endif
                    {{ $p->store->codigo_loja ?? '' }} {{ $p->store->nome_loja ?? __('Sem loja') }}
                </div>
                @php($series = collect([$p->machine?->serial_number])
                    ->merge($p->relationLoaded('machines') ? $p->machines->map(fn ($m) => $m->machine?->serial_number) : [])
                    ->filter()->unique()->values())
                <div class="ms-tipo">
                    {{ __(\App\Http\Controllers\TechnicalRequestController::SERVICE_TYPES[$p->tipo_servico] ?? ($p->tipo_servico ?: '—')) }}
                    @if($series->isNotEmpty())
                        · <span class="ms-serie">{{ $series->implode(' · ') }}</span>
                    @endif
                    @if($p->estado === 'aguarda_peca')<span class="peca">· {{ __('Aguarda Peça') }}</span>@endif
                </div>
                @if($p->descricao_problema)
                    <div class="ms-avaria">{{ $p->descricao_problema }}</div>
                @endif
                @if($morada)<div class="ms-morada"><i class="fa fa-map-marker-alt"></i> {{ $morada }}</div>@endif

                <div class="ms-botoes">
                    @if($morada)
                        <a class="ms-botao mapa" target="_blank" rel="noopener"
                           href="https://www.google.com/maps/search/?api=1&query={{ urlencode($morada) }}">
                            <i class="fa fa-location-arrow"></i> {{ __('Abrir no mapa') }}
                        </a>
                    @endif
                    <a class="ms-botao ver" href="{{ route('backoffice.technical_requests.show', $p->id) }}">
                        <i class="fa fa-file-alt"></i> {{ __('Ver pedido') }}
                    </a>
                </div>
            </div>
        @empty
            <div class="ms-nada">{{ __('Sem serviços marcados.') }}</div>
        @endforelse
    </section>
@endforeach

@if($semDia->isNotEmpty())
    <section class="ms-dia">
        <header>
            <span class="nome">{{ __('Ainda sem dia') }}</span>
            <span class="d">{{ __('atribuídos a si, à espera de marcação') }}</span>
            <span class="n">{{ $semDia->count() }}</span>
        </header>
        @foreach($semDia as $p)
            <a class="ms-paragem" href="{{ route('backoffice.technical_requests.show', $p->id) }}"
               style="--p: {{ $cores[$p->prioridade] ?? '#cbd5e1' }}">
                <div class="ms-topo">
                    <span class="n">#{{ $p->id }}</span>
                    <span class="ms-chip {{ $p->prioridade === 'alta' ? '' : 'clara' }}" style="--c: {{ $cores[$p->prioridade] ?? '#94a3b8' }}">
                        {{ __(\App\Http\Controllers\TechnicalRequestController::PRIORITIES[$p->prioridade] ?? $p->prioridade) }}
                    </span>
                </div>
                <div class="ms-loja">{{ $p->store->codigo_loja ?? '' }} {{ $p->store->nome_loja ?? __('Sem loja') }}</div>
                @if($p->machine?->serial_number)<div class="ms-tipo"><span class="ms-serie">{{ $p->machine->serial_number }}</span></div>@endif
                @if($p->descricao_problema)<div class="ms-avaria">{{ $p->descricao_problema }}</div>@endif
            </a>
        @endforeach
    </section>
@endif

@endsection

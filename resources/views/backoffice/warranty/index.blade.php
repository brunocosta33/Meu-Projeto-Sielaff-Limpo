@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filters = request()->only(['q', 'cliente', 'tipo']))
@php($marcas = config('maintenance.clients'))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Fora da Garantia') }}</title>
@endsection

@push('styles')
<style>
    /* ---- Cabeçalho ---- */
    .fg-faixa { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .7rem;
                padding: .9rem 1.15rem; color: #fff; border-radius: 14px 14px 0 0;
                background: linear-gradient(115deg, #7f1d1d, #b91c1c 50%, #ef4444); }
    .fg-faixa .t { font-size: 1.15rem; font-weight: 800; }
    .fg-faixa .sub { display: block; font-size: .78rem; opacity: .88; max-width: 46rem; }
    .fg-faixa .btn-novo { background: #fff; color: #b91c1c; font-weight: 800; border-radius: 10px; padding: .45rem .9rem; }
    .fg-faixa .btn-novo:hover { background: #fee2e2; }

    .fg-filtros { display: flex; flex-wrap: wrap; align-items: center; gap: .45rem; }
    .fg-chip { display: inline-flex; align-items: center; gap: .3rem; border: 1px solid #dde3ea; background: #fff; color: #475569;
               border-radius: 999px; padding: .22rem .75rem; font-size: .82rem; font-weight: 700; text-decoration: none !important; }
    .fg-chip:hover { border-color: #94a3b8; color: #1e293b; }
    .fg-chip.on { background: var(--grad, #1e293b); color: var(--txt, #fff); border-color: transparent;
                  box-shadow: 0 5px 12px var(--sombra, rgba(15, 23, 42, .15)); }
    .fg-chip .n { opacity: .75; }

    /* ---- O quadro: quatro colunas, uma por quem tem a vez ---- */
    .fg-quadro { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .75rem; align-items: start; }
    @media (max-width: 1399px) { .fg-quadro { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 991px) { .fg-quadro { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) { .fg-quadro { grid-template-columns: 1fr; } }

    .fg-coluna { background: #f6f8fb; border: 1px solid #e6ebf2; border-radius: 16px; overflow: hidden; }
    .fg-coluna > header { padding: .75rem .9rem; color: #fff;
                          background: linear-gradient(120deg, var(--c), color-mix(in srgb, var(--c) 70%, #000)); }
    .fg-coluna > header .linha { display: flex; align-items: center; gap: .45rem; }
    .fg-coluna > header .nome { font-weight: 800; font-size: .95rem; }
    .fg-coluna > header .conta { margin-left: auto; background: rgba(255, 255, 255, .95); color: var(--c);
                                 border-radius: 999px; padding: .02rem .6rem; font-weight: 800; font-size: .85rem; }
    .fg-coluna > header .explica { font-size: .74rem; opacity: .9; margin-top: .15rem; }
    .fg-coluna .corpo { padding: .6rem; display: flex; flex-direction: column; gap: .55rem; min-height: 5rem; }
    .fg-vazio { text-align: center; color: #a3b0bf; font-size: .82rem; padding: 1.2rem .5rem; }

    /* ---- O cartão de cada processo ---- */
    .fg-cartao { display: block; background: #fff; border: 1px solid #e6ebf2; border-left: 5px solid var(--c);
                 border-radius: 12px; padding: .65rem .75rem; text-decoration: none !important; color: inherit;
                 box-shadow: 0 1px 3px rgba(15, 23, 42, .05); transition: transform .12s, box-shadow .12s; }
    .fg-cartao:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(15, 23, 42, .1); color: inherit; }

    .fg-topo { display: flex; align-items: center; gap: .3rem; flex-wrap: wrap; }
    .fg-marca { font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; border-radius: 999px;
                padding: .12rem .5rem; color: var(--txt, #fff); background: var(--grad, #64748b); }
    .fg-tipo { margin-left: auto; font-size: .62rem; font-weight: 800; text-transform: uppercase; letter-spacing: .03em;
               border-radius: 6px; padding: .08rem .38rem; color: var(--tc); background: var(--tb); }
    .fg-loja { font-weight: 800; color: #0f172a; margin-top: .3rem; line-height: 1.25; }
    .fg-loja .cod { color: #94a3b8; font-weight: 700; margin-right: .15rem; }
    .fg-serie { display: inline-flex; align-items: center; gap: 4px; margin-top: .3rem; padding: .1rem .45rem;
                border-radius: 999px; background: linear-gradient(135deg, #ffe69a, #ffd166); color: #7a4a00;
                font-size: .7rem; font-weight: 800; }
    .fg-avaria { font-size: .8rem; color: #475569; margin-top: .35rem; line-height: 1.3;
                 display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    /* A barra do processo, em bolinhas: cheia = feito, anel = é agora */
    .fg-passos { display: flex; align-items: center; gap: 3px; margin-top: .5rem; }
    .fg-passos span { flex: 1; height: 6px; border-radius: 99px; background: #e2e8f0; }
    .fg-passos span.feito { background: var(--bc); }
    .fg-passos span.agora { background: var(--bc); opacity: .45; }

    .fg-proximo { margin-top: .5rem; font-size: .8rem; font-weight: 800; color: #b45309;
                  background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: .3rem .5rem; }
    .fg-rodape { display: flex; align-items: center; gap: .5rem; margin-top: .45rem; font-size: .74rem; color: #94a3b8; }
    .fg-dias { font-weight: 700; }
    .fg-rodape .fotos { margin-left: auto; }
    .fg-datas { display: flex; flex-wrap: wrap; gap: .25rem; margin-top: .5rem; }
    .fg-datas span { font-size: .7rem; color: #475569; background: #f1f5f9; border-radius: 6px; padding: .08rem .4rem;
                     border-left: 3px solid var(--bc, #94a3b8); white-space: nowrap; }
    .fg-datas span b { font-weight: 800; color: #1e293b; margin-right: .15rem; }
    .fg-datas .fotos { border-left: 0; background: none; color: #94a3b8; margin-left: auto; }

    /* ---- Fechados ---- */
    .fg-fechados { display: grid; grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr)); gap: .6rem; }
    .fg-fechado-estado { font-size: .78rem; font-weight: 800; margin-top: .4rem; }
    .fg-ver-todos { display: block; text-align: center; font-size: .8rem; font-weight: 700; color: #15803d;
                    padding: .4rem; border-radius: 8px; border: 1px dashed #bbf7d0; background: #f0fdf4; }
    .fg-ver-todos:hover { background: #dcfce7; text-decoration: none; color: #166534; }
</style>
@endpush

@section('content')

<div class="card mb-3" style="border: 0; border-radius: 14px; overflow: hidden; box-shadow: 0 3px 14px rgba(127, 29, 29, .12)">
    <div class="fg-faixa">
        <div>
            <div class="t"><i class="fas fa-file-invoice-dollar"></i> {{ __('Fora da Garantia') }}</div>
            <span class="sub">{{ __('Avarias por culpa do cliente. Cada processo está na coluna de quem tem de dar o próximo passo.') }}</span>
        </div>
        <a href="{{ route('backoffice.warranty.create') }}" class="btn btn-novo"><i class="fa fa-plus"></i> {{ __('Novo processo') }}</a>
    </div>

    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: .6rem">
            <div class="fg-filtros">
                <a href="{{ route('backoffice.warranty.index', \Illuminate\Support\Arr::except($filters, 'cliente')) }}"
                   class="fg-chip {{ request('cliente') ? '' : 'on' }}">
                    {{ __('Todos os clientes') }} <span class="n">{{ $porCliente->sum() }}</span>
                </a>
                @foreach($clients as $chave => $nome)
                    @php($m = $marcas[$chave] ?? [])
                    <a href="{{ route('backoffice.warranty.index', ['cliente' => $chave] + $filters) }}"
                       class="fg-chip {{ request('cliente') === $chave ? 'on' : '' }}"
                       style="--grad: {{ $m['gradiente'] ?? '#1e293b' }}; --txt: {{ $m['texto'] ?? '#fff' }}; --sombra: {{ $m['sombra'] ?? 'transparent' }}">
                        <i class="fas fa-tag"></i> {{ $nome }} <span class="n">{{ $porCliente[$chave] ?? 0 }}</span>
                    </a>
                @endforeach

                <form method="GET" class="form-inline" style="gap: .45rem">
                    @if(request('cliente'))<input type="hidden" name="cliente" value="{{ request('cliente') }}">@endif
                    <select name="tipo" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">{{ __('Com e sem peças') }}</option>
                        @foreach($types as $value => $type)
                            <option value="{{ $value }}" @selected(request('tipo') === $value)>{{ __($type['curto']) }} ({{ $typeCounts[$value] ?? 0 }})</option>
                        @endforeach
                    </select>
                    <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                           placeholder="{{ __('Loja, nº série, nº cotação…') }}" style="min-width: 210px">
                </form>
            </div>

            <div class="fg-filtros">
                <a href="{{ route('backoffice.warranty.index', $filters + ($verFechados ? [] : ['fechados' => 1])) }}"
                   class="fg-chip {{ $verFechados ? 'on' : '' }}" style="--grad: linear-gradient(135deg, #15803d, #22c55e)">
                    <i class="fas fa-check-circle"></i> {{ __('Fechados') }} <span class="n">{{ $totalFechados }}</span>
                </a>
                <a href="{{ route('backoffice.warranty.export', $filters) }}" class="btn btn-outline-success btn-sm">
                    <i class="fa fa-file-excel"></i> {{ __('Excel') }}
                </a>
            </div>
        </div>
    </div>
</div>

@php($explicacao = [
    'nos' => __('Somos nós a dar o próximo passo.'),
    'sielaff' => __('Pedimos a cotação; falta a resposta.'),
    'cliente' => __('Enviámos a cotação; falta a ordem de compra.'),
    'alemanha' => __('Encomendámos a peça; falta chegar.'),
])

<div class="fg-quadro mb-3">
    @foreach($colunas as $quem => $lista)
        @php($cfg = $esperas[$quem])
        <section class="fg-coluna" style="--c: {{ $cfg['color'] }}">
            <header>
                <div class="linha">
                    <i class="fas {{ $cfg['icon'] }}"></i>
                    <span class="nome">{{ __($cfg['label']) }}</span>
                    <span class="conta">{{ $lista->count() }}</span>
                </div>
                <div class="explica">{{ $explicacao[$quem] }}</div>
            </header>
            <div class="corpo">
                @forelse($lista as $case)
                    @include('backoffice.warranty.partials.card', ['case' => $case, 'cor' => $cfg['color'], 'mostrarProximo' => $quem === 'nos'])
                @empty
                    <div class="fg-vazio"><i class="far fa-smile"></i> {{ __('Nada aqui.') }}</div>
                @endforelse
            </div>
        </section>
    @endforeach

    {{-- Concluídos, à direita: o fim do percurso --}}
    @php($cfgFechados = $esperas['fechados'])
    <section class="fg-coluna" style="--c: {{ $cfgFechados['color'] }}">
        <header>
            <div class="linha">
                <i class="fas {{ $cfgFechados['icon'] }}"></i>
                <span class="nome">{{ __('Concluídos') }}</span>
                <span class="conta">{{ $recentes->count() }}</span>
            </div>
            <div class="explica">{{ __('Fechados nos últimos 30 dias.') }}</div>
        </header>
        <div class="corpo">
            @forelse($recentes as $case)
                @include('backoffice.warranty.partials.card', ['case' => $case, 'cor' => $case->fase === 'recusado' ? '#b91c1c' : '#15803d', 'mostrarProximo' => false])
            @empty
                <div class="fg-vazio">{{ __('Nenhum fechado este mês.') }}</div>
            @endforelse
            @if($totalFechados > $recentes->count())
                <a class="fg-ver-todos" href="{{ route('backoffice.warranty.index', $filters + ['fechados' => 1]) }}#fechados">
                    {{ __('Ver todos os fechados (:n)', ['n' => $totalFechados]) }} <i class="fa fa-chevron-down"></i>
                </a>
            @endif
        </div>
    </section>
</div>

@if($verFechados)
    <div class="card" id="fechados">
        <div class="card-body">
            <h6 class="text-uppercase mb-3" style="font-size: .78rem; letter-spacing: .06em; color: #15803d; font-weight: 800">
                <i class="fas fa-check-circle"></i> {{ __('Fechados') }} ({{ $totalFechados }})
            </h6>
            <div class="fg-fechados">
                @forelse($fechados as $case)
                    @include('backoffice.warranty.partials.card', ['case' => $case, 'cor' => $case->fase === 'recusado' ? '#b91c1c' : '#15803d', 'mostrarProximo' => false])
                @empty
                    <div class="fg-vazio">{{ __('Ainda não há processos fechados.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endif

@endsection

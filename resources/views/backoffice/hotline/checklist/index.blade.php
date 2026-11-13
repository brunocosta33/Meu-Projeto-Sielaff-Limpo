@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Checklist da Hotline') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))

@push('styles')
<style>
    /* ---- Cabeçalho: faixa de cor com o dia que se está a ver ---- */
    .ck-cabeca { overflow: hidden; border: 0; border-radius: 14px; box-shadow: 0 3px 14px rgba(15, 76, 129, .13); }
    .ck-cartao { position: relative; overflow: hidden; border: 1px solid #e9eff6; border-radius: 14px;
                 box-shadow: 0 1px 3px rgba(15, 23, 42, .05); }
    .ck-cartao::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 4px;
                         background: linear-gradient(90deg, #0ea5e9, #8b5cf6 45%, #f59e0b); }
    .ck-faixa { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .6rem;
                padding: .8rem 1.15rem; color: #fff; background: linear-gradient(115deg, #0a3f72, #1b86c6 48%, #24c2e0); }
    .ck-faixa .t { font-size: 1.12rem; font-weight: 800; letter-spacing: -.01em; }
    .ck-faixa .t i { opacity: .85; margin-right: .3rem; }
    .ck-faixa .sub { display: block; font-size: .76rem; font-weight: 500; opacity: .8; letter-spacing: 0; }
    .ck-dias-nav { display: flex; align-items: center; gap: .3rem; }
    .ck-dias-nav .data { min-width: 13rem; text-align: center; font-weight: 700; font-size: .92rem; }
    .ck-seta { border: 1px solid rgba(255, 255, 255, .35); background: rgba(255, 255, 255, .12); color: #fff;
               border-radius: 8px; padding: .1rem .55rem; line-height: 1.6; }
    .ck-seta:hover { background: #fff; color: #0f4c81; }
    .ck-hoje { border: 0; background: #fff; color: #0f4c81; font-weight: 800; font-size: .76rem;
               border-radius: 8px; padding: .22rem .7rem; margin-left: .3rem; }

    /* ---- Quem está de serviço: uma roda com o que já validou ---- */
    .ck-pessoas { display: flex; flex-wrap: wrap; gap: .6rem; }
    .ck-pessoa { display: flex; align-items: center; gap: .7rem; flex: 1 1 17rem; max-width: 26rem;
                 border: 1px solid #e8edf3; border-left: 5px solid var(--c, #cbd5e1); border-radius: 13px;
                 padding: .6rem .85rem; background: #fff;
                 /* um toque da cor da pessoa no fundo; onde não houver suporte fica branco */
                 background: linear-gradient(110deg, color-mix(in srgb, var(--c) 22%, #fff), #fff 72%); }
    .ck-pessoa.folga { background: #f8fafc; border-left-color: #e2e8f0; }
    .ck-roda { width: 52px; height: 52px; flex: 0 0 52px; border-radius: 50%; display: grid; place-items: center;
               background: conic-gradient(var(--c) calc(var(--p) * 1%), #e8eef5 0); }
    .ck-roda::after { content: ''; grid-area: 1 / 1; width: 38px; height: 38px; border-radius: 50%; background: #fff; }
    .ck-roda .n { grid-area: 1 / 1; z-index: 1; font-size: .78rem; font-weight: 800; color: var(--c); }
    .ck-roda.folga { background: #eef2f7; }
    .ck-roda.folga .n { color: #94a3b8; font-size: 1rem; }
    .ck-pessoa .nome { font-weight: 800; color: #0f172a; line-height: 1.2; }
    .ck-pessoa .horas { font-size: .78rem; color: #64748b; margin-top: .1rem; }
    .ck-pessoa .horas b { color: var(--c); font-weight: 700; }

    .ck-dica { display: flex; align-items: center; gap: .5rem; margin-top: .85rem; border-radius: 11px;
               padding: .55rem .9rem; font-size: .89rem; font-weight: 700;
               background: linear-gradient(100deg, #dbeafe, #eff6ff 60%); color: #1d4ed8; border: 1px solid #c7ddfb; }
    .ck-dica.ok { background: linear-gradient(100deg, #d1fae5, #f0fdf4 60%); color: #15803d; border-color: #a7f3d0; }
    .ck-dica i { font-size: 1.05rem; }

    /* ---- Uma coluna por pessoa de serviço ---- */
    .ck-colunas { display: grid; grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr)); gap: .9rem; margin-bottom: 1rem; }
    .ck-coluna { max-width: 46rem; background: #fff; border: 1px solid #e9eff6; border-radius: 15px; overflow: hidden;
                 box-shadow: 0 2px 8px rgba(15, 23, 42, .07); }
    .ck-coluna > header { display: flex; align-items: center; gap: .65rem; padding: .75rem .95rem; color: #fff;
                          background: var(--c, #475569);
                          background: linear-gradient(120deg, color-mix(in srgb, var(--c) 88%, #fff), color-mix(in srgb, var(--c) 72%, #000)); }
    .ck-coluna > header .bola { width: 40px; height: 40px; flex: 0 0 40px; border-radius: 50%; display: grid;
                                place-items: center; font-weight: 800; font-size: .8rem;
                                background: rgba(255, 255, 255, .22); border: 1px solid rgba(255, 255, 255, .35); }
    .ck-coluna > header .nome { font-weight: 800; line-height: 1.15; }
    .ck-coluna > header .horas { font-size: .76rem; opacity: .88; }
    .ck-coluna > header .conta { margin-left: auto; text-align: right; }
    .ck-coluna > header .conta b { display: block; font-size: 1.15rem; font-weight: 800; line-height: 1; }
    .ck-coluna > header .conta span { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; opacity: .85; }
    .ck-prog { height: 6px; background: #eef2f7; }
    .ck-prog span { display: block; height: 100%; background: linear-gradient(90deg, #4ade80, #16a34a); transition: width .3s; }

    .ck-item { display: flex; align-items: flex-start; gap: .7rem; padding: .62rem .9rem; border-top: 1px solid #f1f5f9; }
    .ck-item:hover { background: #f9fbfe; }
    .ck-item.feito { background: linear-gradient(90deg, #f2fcf6, #fff 65%); }
    .ck-item.prob { background: linear-gradient(90deg, #fff8ec, #fff 65%); }
    .ck-item.falhou { background: #fffafa; }
    .ck-item .txt { flex: 1; min-width: 0; padding-top: .3rem; }
    .ck-item .num { display: inline-block; min-width: 1.1rem; font-size: .78rem; font-weight: 800; color: #b6c2d1; }
    .ck-item .titulo { font-weight: 600; color: #0f172a; }
    .ck-item.feito .titulo { color: #15803d; }
    .ck-item .nota { font-size: .8rem; color: #64748b; margin-top: .2rem; white-space: pre-wrap; }
    .ck-item .nota i { color: #a9b6c6; margin-right: .15rem; }
    .ck-item .dir { text-align: right; padding-top: .45rem; white-space: nowrap; }

    /* Etiquetas de turno e de dias, na lista da chefia */
    .ck-eti { display: inline-flex; align-items: center; gap: .22rem; font-size: .67rem; font-weight: 800;
              text-transform: uppercase; letter-spacing: .04em; border-radius: 999px; padding: .1rem .5rem;
              color: #fff; background: var(--c, #64748b); white-space: nowrap; vertical-align: middle; }
    .ck-eti.clara { color: var(--c); background: #fff; border: 1px solid currentColor; }

    .ck-vazio-col { padding: 1.6rem .9rem; text-align: center; color: #94a3b8; font-size: .86rem; }
    .ck-nada { text-align: center; color: #94a3b8; padding: 2rem 1rem; background: #fff; border: 1px dashed #dbe3ec;
               border-radius: 14px; margin-bottom: 1rem; }

    /* ---- O estado de cada célula: um círculo para tocar ---- */
    .ck-tique { width: 36px; height: 36px; border-radius: 50%; border: 2px dashed #b9dcc6; background: #f5fdf8;
                color: #9ed0b1; display: inline-flex; align-items: center; justify-content: center; font-size: .95rem;
                padding: 0; transition: all .15s; }
    .ck-tique:hover { border-style: solid; border-color: #16a34a; background: #16a34a; color: #fff;
                      transform: scale(1.09); box-shadow: 0 5px 14px rgba(22, 163, 74, .35); }
    .ck-tique.ok { border: 0; color: #fff; background: linear-gradient(145deg, #34d399, #15803d);
                   box-shadow: 0 4px 11px rgba(22, 163, 74, .38); }
    .ck-tique.ok:hover { transform: scale(1.09); filter: saturate(1.2); }
    .ck-tique.prob { border: 0; color: #fff; background: linear-gradient(145deg, #fbbf24, #d97706);
                     box-shadow: 0 4px 11px rgba(217, 119, 6, .38); }
    .ck-tique.prob:hover { background: linear-gradient(145deg, #fbbf24, #d97706); border-color: transparent;
                           transform: scale(1.09); filter: saturate(1.2); }
    .ck-tique.parado { cursor: default; }
    .ck-tique.parado:hover { transform: none; filter: none; }
    .ck-hora { font-size: .72rem; font-weight: 800; letter-spacing: -.01em; }
    .ck-hora.ok { color: #15803d; }
    .ck-hora.prob { color: #b45309; }
    .ck-prob-link { border: 0; background: none; color: #c7913a; font-size: .7rem; font-weight: 700; padding: 0;
                    text-transform: uppercase; letter-spacing: .03em; opacity: .75; }
    .ck-prob-link:hover { color: #b45309; opacity: 1; text-decoration: underline; }
    .ck-falhou { display: inline-flex; align-items: center; gap: .3rem; font-size: .78rem; font-weight: 700;
                 border-radius: 8px; padding: .18rem .55rem; background: #fee2e2; color: #b91c1c; }
    .ck-vazio { color: #cdd8e5; }
    .ck-linha form { display: inline-block; margin: 0; }

    /* O que correu mal ocupa a largura toda do cartão */
    .ck-notas { grid-column: 1 / -1; margin-top: .55rem; }
    .ck-nota-prob { display: flex; flex-wrap: wrap; gap: .1rem .5rem; align-items: baseline; font-size: .85rem;
                    color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b;
                    border-radius: 9px; padding: .4rem .7rem; }
    .ck-nota-prob + .ck-nota-prob { margin-top: .3rem; }
    .ck-nota-prob i { color: #f59e0b; }
    .ck-nota-prob .quem { font-weight: 800; white-space: nowrap; }
    .ck-nota-prob .texto { white-space: pre-wrap; word-break: break-word; }

    .ck-aviso { display: flex; flex-wrap: wrap; align-items: center; gap: .1rem .5rem; margin-bottom: .9rem;
                border: 1px solid #fca5a5; border-left: 5px solid #ef4444; border-radius: 12px; padding: .5rem .85rem;
                background: linear-gradient(100deg, #fee2e2, #fff7f7 60%); color: #991b1b; font-size: .87rem; }
    .ck-aviso:hover { background: linear-gradient(100deg, #fecaca, #fff5f5 60%); color: #7f1d1d; text-decoration: none; }
    .ck-aviso b { background: #fff; border: 1px solid #fecaca; border-radius: 999px; padding: .02rem .5rem; font-weight: 800; }
    .ck-aviso .ver { margin-left: auto; font-weight: 700; font-size: .8rem; }

    .ck-legenda { display: flex; flex-wrap: wrap; gap: .2rem 1.1rem; padding: .1rem .9rem;
                  font-size: .74rem; color: #94a3b8; }
    .ck-legenda i { margin-right: .15rem; }

    /* ---- A lista toda, só para a chefia ---- */
    .ck-titulo-cartao { display: flex; align-items: center; gap: .45rem; font-size: .78rem; font-weight: 800;
                        text-transform: uppercase; letter-spacing: .06em; color: #0f4c81; margin: .15rem 0 .1rem; }
    .ck-titulo-cartao i { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px;
                          border-radius: 8px; background: linear-gradient(145deg, #1b86c6, #0a3f72); color: #fff; font-size: .8rem; }
    .ck-titulo-cartao .conta { background: #e8f1f8; color: #0f4c81; border-radius: 999px; padding: 0 .5rem; font-size: .72rem; }
    .ck-sub-cartao { font-size: .8rem; color: #94a3b8; margin-bottom: .8rem; }
    .ck-gerir { width: 100%; }
    .ck-gerir td { padding: .4rem .5rem; border-top: 1px solid #f1f5f9; vertical-align: middle; }
    .ck-gerir tr:hover td { background: #f9fbfe; }
    .ck-gerir .t { font-weight: 600; color: #0f172a; }
    .ck-gerir .n { font-size: .8rem; color: #94a3b8; }
    .ck-gerir .etis { white-space: nowrap; text-align: right; }
    .ck-gerir .acoes { text-align: right; white-space: nowrap; width: 4rem; }
    .ck-gerir form { display: inline-block; margin: 0; }
    .ck-ordem { display: inline-flex; flex-direction: column; gap: 1px; }
    .ck-ordem button { border: 1px solid #e2e8f0; background: #fff; color: #94a3b8; line-height: 1; padding: 0 .3rem; font-size: .6rem; }
    .ck-ordem button:hover { color: #0f4c81; border-color: #0f4c81; }
    .ck-acao { border: 0; background: none; color: #94a3b8; padding: 0 .2rem; }
    .ck-acao:hover { color: #0f4c81; }
    .ck-acao.apagar:hover { color: #dc2626; }
    .ck-nova { background: #f7fafd; border: 1px dashed #cfe0ee; border-radius: 12px; padding: .7rem .8rem; margin-bottom: .9rem; }

    /* ---- Histórico ---- */
    .ck-hist td, .ck-hist th { padding: .3rem .5rem; font-size: .82rem; text-align: center; }
    .ck-hist td:first-child, .ck-hist th:first-child { text-align: left; white-space: nowrap; }
    .ck-hist .ok, .ck-hist .mau { display: inline-block; border-radius: 999px; padding: .02rem .5rem; font-weight: 800; font-size: .78rem; }
    .ck-hist .ok { color: #15803d; background: #dcfce7; }
    .ck-hist .mau { color: #b91c1c; background: #fee2e2; }
    .ck-hist .folga { color: #cbd5e1; }
    .ck-hist tr.hoje { background: #f2f8ff; }
    .ck-hist tr.fds { background: #f0fdfa; }
    .ck-hist tr.fds td:first-child { color: #0d9488; font-weight: 800; }
    .ck-hist tr.hoje td { font-weight: 700; }
</style>
@endpush

@section('content')

<div class="card ck-cabeca mb-3">
    <div class="ck-faixa">
        <div>
            <div class="t"><i class="fas fa-clipboard-check"></i> {{ __('Checklist da Hotline') }}</div>
            <span class="sub">{{ __('O que há a fazer todos os dias. Quem está de serviço valida; os dias e os turnos vêm da escala.') }}</span>
        </div>
        <div class="ck-dias-nav">
            <a class="ck-seta" href="{{ route('backoffice.checklist.index', ['dia' => $dia->copy()->subDay()->toDateString()]) }}"
               title="{{ __('Dia anterior') }}"><i class="fa fa-chevron-left"></i></a>
            <span class="data">{{ \Illuminate\Support\Str::ucfirst($dia->isoFormat('dddd, D [de] MMMM')) }}</span>
            <a class="ck-seta" href="{{ route('backoffice.checklist.index', ['dia' => $dia->copy()->addDay()->toDateString()]) }}"
               title="{{ __('Dia seguinte') }}"><i class="fa fa-chevron-right"></i></a>
            @unless($hoje)
                <a class="ck-hoje" href="{{ route('backoffice.checklist.index') }}">{{ __('Hoje') }}</a>
            @endunless
        </div>
    </div>

    @php($forasDeServico = $colunas->filter(fn ($c) => !$c['turno']))
    @if($forasDeServico->isNotEmpty() || ($hoje && $meuTurno && isset($resumo[$eu])))
    <div class="card-body">
        {{-- Só quem não está de serviço: quem está tem a sua coluna, com o mesmo resumo --}}
        <div class="ck-pessoas">
            @foreach($forasDeServico as $uid => $col)
                <div class="ck-pessoa folga">
                    <div class="ck-roda folga"><span class="n">{{ \App\Services\HotlineChecklist::iniciais($col['user']->name) }}</span></div>
                    <div>
                        <div class="nome">{{ $uid === $eu ? __('Eu') : $col['user']->name }}</div>
                        <div class="horas">{{ $col['motivo'] ?? __('Sem turno') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($hoje && $meuTurno && isset($resumo[$eu]))
            @php($meu = $resumo[$eu])
            @php($faltam = $meu['total'] - $meu['feitas'])
            <div class="ck-dica {{ $faltam > 0 ? '' : 'ok' }}">
                @if($meu['total'] === 0)
                    <i class="fas fa-mug-hot"></i> {{ __('Hoje não tem nada na sua lista.') }}
                @elseif($faltam > 0)
                    <i class="fas fa-hand-point-right"></i>
                    {{ trans_choice('Falta :n por validar — vá validando à medida que for fazendo.|Faltam :n por validar — vá validando à medida que for fazendo.', $faltam, ['n' => $faltam]) }}
                @else
                    <i class="fas fa-check-circle"></i> {{ __('Está tudo validado. Bom trabalho.') }}
                @endif
            </div>
        @endif
    </div>
    @endif
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>
@endif

@if($gestor && $falhasDeOntem->isNotEmpty())
    <a class="ck-aviso" href="{{ route('backoffice.checklist.index', ['dia' => today()->subDay()->toDateString()]) }}">
        <i class="fas fa-exclamation-triangle"></i>
        <span>{{ __('Ontem ficou por validar') }}:</span>
        @foreach($falhasDeOntem as $falha)
            <b>{{ $falha['nome'] }} {{ $falha['n'] }}</b>
        @endforeach
        <span class="ver">{{ __('ver o dia') }} <i class="fa fa-chevron-right"></i></span>
    </a>
@endif

@if($listas->isEmpty())
    <div class="ck-nada">
        <i class="fas fa-mug-hot fa-lg d-block mb-2" style="color: #cbd5e1"></i>
        {{ __('Ninguém está de serviço na Hotline neste dia.') }}
    </div>
@else
    <div class="ck-colunas">
        @foreach($listas as $uid => $lista)
            @php($col = $colunas[$uid])
            @php($r = $resumo[$uid] ?? ['total' => 0, 'feitas' => 0])
            @php($cor = \App\Services\HotlineRotation::COLORS[$loop->index % count(\App\Services\HotlineRotation::COLORS)])
            @php($souEu = $hoje && $uid === $eu && $meuTurno)

            <section class="ck-coluna" style="--c: {{ $cor }}">
                <header>
                    <span class="bola">{{ \App\Services\HotlineChecklist::iniciais($col['user']->name) }}</span>
                    <div>
                        <div class="nome">{{ $uid === $eu ? __('Eu') : $col['user']->name }}</div>
                        <div class="horas">
                            {{ $col['horas'] }}@if($col['turno'] !== 'ambos') · {{ __(\App\Models\HotlineChecklistItem::TURNOS[$col['turno']]['curto']) }}@endif
                            @if($col['porConta'])
                                · {{ __('no turno de :nome', ['nome' => $col['porConta']->name]) }}
                            @endif
                        </div>
                    </div>
                    <div class="conta">
                        <b>{{ $r['feitas'] }}/{{ $r['total'] }}</b>
                        <span>{{ __('validadas') }}</span>
                    </div>
                </header>
                <div class="ck-prog">
                    <span style="width: {{ $r['total'] ? round(100 * $r['feitas'] / $r['total']) : 0 }}%"></span>
                </div>

                @forelse($lista as $item)
                    @php($check = $checks[$item->id . '_' . $uid] ?? null)
                    @php($falhou = !$check && $dia->isPast() && !$hoje)
                    <div class="ck-item {{ $check ? ($check->isProblema() ? 'prob' : 'feito') : ($falhou ? 'falhou' : '') }}">
                        @if($souEu)
                            <form method="POST" action="{{ $check
                                ? route('backoffice.checklist.desfazer', $item->id)
                                : route('backoffice.checklist.validar', $item->id) }}">
                                @csrf
                                @unless($check)<input type="hidden" name="estado" value="feito">@endunless
                                <button type="submit"
                                        class="ck-tique {{ $check ? ($check->isProblema() ? 'prob' : 'ok') : '' }}"
                                        title="{{ $check ? __('Clique para desfazer') : __('Clique quando estiver feita') }}">
                                    <i class="fas {{ $check && $check->isProblema() ? 'fa-exclamation' : 'fa-check' }}"></i>
                                </button>
                            </form>
                        @else
                            <span class="ck-tique parado {{ $check ? ($check->isProblema() ? 'prob' : 'ok') : ($falhou ? 'falhou' : '') }}">
                                <i class="fas {{ $check && $check->isProblema() ? 'fa-exclamation' : ($falhou ? 'fa-times' : 'fa-check') }}"></i>
                            </span>
                        @endif

                        <div class="txt">
                            <span class="num">{{ $loop->iteration }}</span>
                            <span class="titulo">{{ $item->titulo }}</span>
                            @if($item->nota)
                                <div class="nota"><i class="fas fa-info-circle"></i> {{ $item->nota }}</div>
                            @endif
                            @if($check && $check->nota)
                                <div class="ck-nota-prob">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span class="texto">{{ $check->nota }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="dir">
                            @if($check)
                                <span class="ck-hora {{ $check->isProblema() ? 'prob' : 'ok' }}">{{ $check->hora() }}</span>
                            @elseif($souEu)
                                <button type="button" class="ck-prob-link" data-toggle="modal" data-target="#ck-problema"
                                        data-id="{{ $item->id }}" data-titulo="{{ $item->titulo }}">
                                    <i class="fa fa-exclamation-triangle"></i> {{ __('problema') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="ck-vazio-col">
                        @if($listaToda->isNotEmpty() || !$gestor)
                            {{ __('Não há nada para este dia.') }}
                        @else
                            {{ __('A checklist ainda está vazia.') }}
                        @endif
                    </div>
                @endforelse
            </section>
        @endforeach
    </div>
@endif

@if($gestor)
    <div class="card ck-cartao mb-3">
        <div class="card-body">
            <div class="ck-titulo-cartao">
                <i class="fa fa-list-check"></i> {{ __('A lista toda') }}
                <span class="conta">{{ $listaToda->count() }}</span>
            </div>
            <p class="ck-sub-cartao">{{ __('Só o Coordenador e o Country Manager mexem aqui. A grelha de cima mostra apenas o que é do dia.') }}</p>

            <div class="ck-nova">
                <form method="POST" action="{{ route('backoffice.checklist.store') }}">
                    @csrf
                    <div class="form-row align-items-center">
                        <div class="col-lg-4 mb-2 mb-lg-0">
                            <input type="text" name="titulo" class="form-control" maxlength="255" required
                                   value="{{ old('titulo') }}" placeholder="{{ __('O que é para fazer?') }}">
                        </div>
                        <div class="col-lg-2 col-6 mb-2 mb-lg-0">
                            <select name="turno" class="form-control">
                                @foreach($turnos as $chave => $t)
                                    <option value="{{ $chave }}" @selected(old('turno') === $chave)>{{ __($t['label']) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-6 mb-2 mb-lg-0">
                            <select name="dias" class="form-control">
                                @foreach($diasPossiveis as $chave => $d)
                                    <option value="{{ $chave }}" @selected(old('dias') === $chave)>{{ __($d['label']) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2 mb-lg-0">
                            <input type="text" name="nota" class="form-control" maxlength="1000"
                                   value="{{ old('nota') }}" placeholder="{{ __('Nota (opcional)') }}">
                        </div>
                        <div class="col-lg-1">
                            <button type="submit" class="btn btn-primary btn-block" title="{{ __('Acrescentar') }}">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <table class="ck-gerir">
                @forelse($listaToda as $item)
                    <tr>
                        <td style="width: 2.5rem">
                            <span class="ck-ordem">
                                <form method="POST" action="{{ route('backoffice.checklist.ordem', $item->id) }}">
                                    @csrf<input type="hidden" name="dir" value="cima">
                                    <button type="submit" title="{{ __('Subir') }}" @disabled($loop->first)><i class="fa fa-chevron-up"></i></button>
                                </form>
                                <form method="POST" action="{{ route('backoffice.checklist.ordem', $item->id) }}">
                                    @csrf<input type="hidden" name="dir" value="baixo">
                                    <button type="submit" title="{{ __('Descer') }}" @disabled($loop->last)><i class="fa fa-chevron-down"></i></button>
                                </form>
                            </span>
                        </td>
                        <td>
                            <div class="t">{{ $item->titulo }}</div>
                            @if($item->nota)<div class="n">{{ $item->nota }}</div>@endif
                        </td>
                        <td class="etis">
                            <span class="ck-eti clara" style="--c: {{ $item->turnoConfig()['cor'] }}">
                                <i class="fas {{ $item->turnoConfig()['icon'] }}"></i> {{ __($item->turnoConfig()['curto']) }}
                            </span>
                            <span class="ck-eti" style="--c: {{ $item->diasConfig()['cor'] }}">
                                <i class="fas {{ $item->diasConfig()['icon'] }}"></i> {{ __($item->diasConfig()['curto']) }}
                            </span>
                        </td>
                        <td class="acoes">
                            <button type="button" class="ck-acao" data-toggle="modal" data-target="#ck-editar"
                                    data-id="{{ $item->id }}" data-titulo="{{ $item->titulo }}"
                                    data-nota="{{ $item->nota }}" data-turno="{{ $item->turno }}" data-dias="{{ $item->dias }}"
                                    title="{{ __('Alterar') }}"><i class="fa fa-pencil-alt"></i></button>
                            <form method="POST" action="{{ route('backoffice.checklist.destroy', $item->id) }}"
                                  onsubmit="return confirm('{{ __('Tirar esta tarefa da checklist?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="ck-acao apagar" title="{{ __('Tirar da lista') }}"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="text-muted small py-3">{{ __('A checklist ainda está vazia.') }}</td></tr>
                @endforelse
            </table>
        </div>
    </div>
@endif

<div class="card ck-cartao">
    <div class="card-body">
        <div class="ck-titulo-cartao"><i class="fa fa-history"></i> {{ __('Últimos :n dias', ['n' => count($historico['dias'])]) }}</div>
        <p class="ck-sub-cartao">{{ __('Clique num dia para o ver em pormenor.') }}</p>
        <div class="table-responsive">
            <table class="table table-sm mb-0 ck-hist">
                <thead>
                    <tr>
                        <th>{{ __('Dia') }}</th>
                        @foreach($historico['pessoas'] as $uid => $pessoa)
                            <th>{{ $uid === $eu ? __('Eu') : $pessoa->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($historico['dias'] as $linha)
                    <tr class="{{ $linha['data']->isToday() ? 'hoje' : '' }} {{ $linha['data']->isWeekend() ? 'fds' : '' }}">
                        <td>
                            <a href="{{ route('backoffice.checklist.index', ['dia' => $linha['data']->toDateString()]) }}">
                                {{ \Illuminate\Support\Str::ucfirst($linha['data']->isoFormat('ddd D/MM')) }}
                            </a>
                        </td>
                        @foreach($historico['pessoas'] as $uid => $pessoa)
                            @php($c = $linha['linhas'][$uid] ?? null)
                            <td>
                                @if(!$c || !$c['turno'])
                                    <span class="folga" title="{{ $c['motivo'] ?? '' }}">{{ $c['curto'] ?? '—' }}</span>
                                @elseif($c['total'] === 0)
                                    <span class="folga">—</span>
                                @else
                                    <span class="{{ $c['feitas'] >= $c['total'] ? 'ok' : 'mau' }}">{{ $c['feitas'] }}/{{ $c['total'] }}</span>
                                    @if($c['problemas'])
                                        <i class="fas fa-exclamation-triangle text-warning" title="{{ trans_choice(':count problema|:count problemas', $c['problemas'], ['count' => $c['problemas']]) }}"></i>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Houve um problema: obriga a dizer qual --}}
<div class="modal fade" id="ck-problema" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="ck-problema-form">
                @csrf
                <input type="hidden" name="estado" value="problema">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning"></i> {{ __('Houve um problema') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Fechar') }}"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2 font-weight-bold" id="ck-problema-titulo"></p>
                    <textarea name="nota" id="ck-problema-nota" class="form-control" rows="3" maxlength="1000" required
                              placeholder="{{ __('O que se passou? Quem foi avisado?') }}"></textarea>
                    <small class="form-text text-muted">{{ __('Fica à vista do Coordenador e do Country Manager.') }}</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link text-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-warning"><i class="fa fa-check"></i> {{ __('Registar') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($gestor)
    <div class="modal fade" id="ck-editar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" id="ck-editar-form">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa fa-pencil-alt text-primary"></i> {{ __('Alterar tarefa') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Fechar') }}"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('Tarefa') }}</label>
                            <input type="text" name="titulo" id="ck-editar-titulo" class="form-control" maxlength="255" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-sm-6">
                                <label>{{ __('Turno') }}</label>
                                <select name="turno" id="ck-editar-turno" class="form-control">
                                    @foreach($turnos as $chave => $t)
                                        <option value="{{ $chave }}">{{ __($t['label']) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-6">
                                <label>{{ __('Dias') }}</label>
                                <select name="dias" id="ck-editar-dias" class="form-control">
                                    @foreach($diasPossiveis as $chave => $d)
                                        <option value="{{ $chave }}">{{ __($d['label']) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>{{ __('Nota (opcional)') }}</label>
                            <textarea name="nota" id="ck-editar-nota" class="form-control" rows="2" maxlength="1000"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link text-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{ __('Guardar') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var base = {{ \Illuminate\Support\Js::from(route('backoffice.checklist.index')) }};

        // Um modal só, preenchido pelo botão da linha
        var liga = function (id, antes) {
            var modal = document.getElementById(id);
            if (modal && window.jQuery) {
                $(modal).on('show.bs.modal', function (ev) { if (ev.relatedTarget) antes(ev.relatedTarget); });
            }
        };

        liga('ck-problema', function (b) {
            document.getElementById('ck-problema-form').action = base + '/' + b.dataset.id + '/validar';
            document.getElementById('ck-problema-titulo').textContent = b.dataset.titulo;
            document.getElementById('ck-problema-nota').value = '';
        });

        liga('ck-editar', function (b) {
            document.getElementById('ck-editar-form').action = base + '/' + b.dataset.id;
            document.getElementById('ck-editar-titulo').value = b.dataset.titulo;
            document.getElementById('ck-editar-nota').value = b.dataset.nota || '';
            document.getElementById('ck-editar-turno').value = b.dataset.turno;
            document.getElementById('ck-editar-dias').value = b.dataset.dias;
        });
    });
</script>
@endpush

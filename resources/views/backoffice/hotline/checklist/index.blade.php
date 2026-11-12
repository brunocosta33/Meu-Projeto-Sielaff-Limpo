@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Checklist da Hotline') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))

@push('styles')
<style>
    /* ---- Cabeçalho: faixa de cor com o dia que se está a ver ---- */
    .ck-cabeca { overflow: hidden; border: 0; box-shadow: 0 2px 10px rgba(15, 23, 42, .07); }
    .ck-faixa { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .6rem;
                padding: .7rem 1.1rem; color: #fff; background: linear-gradient(120deg, #0f4c81, #1f7bb6 65%, #2a94c9); }
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
                 border: 1px solid #e8edf3; border-left: 5px solid var(--c, #cbd5e1); border-radius: 12px;
                 padding: .55rem .8rem; background: linear-gradient(#fff, #fcfdff); }
    .ck-pessoa.folga { background: #f8fafc; border-left-color: #e2e8f0; }
    .ck-roda { width: 52px; height: 52px; flex: 0 0 52px; border-radius: 50%; display: grid; place-items: center;
               background: conic-gradient(var(--c) calc(var(--p) * 1%), #e8eef5 0); }
    .ck-roda::after { content: ''; grid-area: 1 / 1; width: 40px; height: 40px; border-radius: 50%; background: #fff; }
    .ck-roda .n { grid-area: 1 / 1; z-index: 1; font-size: .78rem; font-weight: 800; color: var(--c); }
    .ck-roda.folga { background: #eef2f7; }
    .ck-roda.folga .n { color: #94a3b8; font-size: 1rem; }
    .ck-pessoa .nome { font-weight: 800; color: #0f172a; line-height: 1.2; }
    .ck-pessoa .horas { font-size: .78rem; color: #64748b; margin-top: .1rem; }
    .ck-pessoa .horas b { color: var(--c); font-weight: 700; }

    .ck-dica { display: flex; align-items: center; gap: .45rem; margin-top: .8rem; border-radius: 10px;
               padding: .5rem .85rem; font-size: .88rem; font-weight: 600;
               background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
    .ck-dica.ok { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }

    /* ---- A grelha do dia ---- */
    .ck-tabela { background: #fff; border-collapse: separate; border-spacing: 0; }
    .ck-tabela thead th { border-top: 0; border-bottom: 2px solid #eef2f7; background: #fff; padding: .5rem .6rem;
                          font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }
    .ck-tabela thead th .t { display: block; margin-top: .1rem; font-size: .68rem; font-weight: 600;
                             text-transform: none; letter-spacing: 0; color: #94a3b8; }
    .ck-tabela thead th.minha { color: #0f4c81; }
    .ck-tabela thead th.fora { color: #cbd5e1; }
    .ck-tabela .ck-col { text-align: center; width: 17rem; border-left: 1px solid #f1f5f9; }
    @media (max-width: 991px) { .ck-tabela .ck-col { width: 11rem; } }

    .ck-tabela tbody tr { --fundo: #fff; }
    .ck-tabela tbody tr.feita { --fundo: #f6fdf8; }
    .ck-tabela tbody tr.problema { --fundo: #fffbf2; }
    .ck-tabela tbody tr:hover { --fundo: #f7fbff; }
    .ck-tabela tbody td { background: var(--fundo); border-top: 1px solid #eef2f7; vertical-align: middle;
                          transition: background .12s; }
    .ck-tabela td.minha { --fundo: #f2f8ff; }
    .ck-tabela tr.feita td.minha { --fundo: #edfaf1; }
    .ck-tabela tr.problema td.minha { --fundo: #fff6e7; }
    /* O nome da tarefa fica à vista quando se arrasta a tabela ao lado, no telemóvel */
    .ck-tabela th:first-child, .ck-tabela td:first-child { position: sticky; left: 0; z-index: 2; min-width: 14rem; }
    /* A barra da esquerda tem a cor do turno */
    .ck-tabela td.ck-tarefa { border-left: 5px solid var(--turno, #e2e8f0); }

    .ck-tarefa .titulo { font-weight: 600; color: #0f172a; }
    .ck-tabela tr.feita .ck-tarefa .titulo { color: #166534; }
    /* Tarefa de um turno que hoje ninguém faz: não deve competir com as que interessam */
    .ck-tabela tr.nada .ck-tarefa .titulo { color: #94a3b8; font-weight: 500; }
    .ck-tabela tr.nada td.ck-tarefa { border-left-color: #eef2f7; }
    .ck-tarefa .nota { display: inline-block; max-width: 38rem; font-size: .82rem; color: #475569; background: #f1f5f9;
                       border-left: 3px solid #cbd5e1; border-radius: 0 7px 7px 0; padding: .22rem .55rem;
                       margin-top: .35rem; white-space: pre-wrap; }
    .ck-tarefa .nota i { color: #94a3b8; margin-right: .15rem; }

    /* Etiquetas de turno e de dias: cheias de cor, para se lerem de longe */
    .ck-eti { display: inline-flex; align-items: center; gap: .22rem; font-size: .67rem; font-weight: 800;
              text-transform: uppercase; letter-spacing: .04em; border-radius: 999px; padding: .1rem .5rem;
              color: #fff; background: var(--c, #64748b); white-space: nowrap; vertical-align: middle; }
    .ck-eti.clara { color: var(--c); background: #fff; border: 1px solid currentColor; }

    /* ---- As células ---- */
    .ck-feito, .ck-problema, .ck-falhou { display: inline-flex; align-items: center; gap: .3rem; font-size: .8rem;
                                          font-weight: 700; border-radius: 8px; padding: .18rem .55rem; }
    .ck-feito { background: #dcfce7; color: #15803d; }
    .ck-problema { background: #fef3c7; color: #b45309; }
    .ck-falhou { background: #fee2e2; color: #b91c1c; }
    .ck-vazio { color: #cbd5e1; }
    .ck-btn { border: 1px solid #bbf7d0; background: #f0fdf4; color: #15803d; border-radius: 8px; font-size: .8rem;
              font-weight: 700; padding: .25rem .75rem; box-shadow: 0 1px 2px rgba(15, 23, 42, .07); transition: all .12s; }
    .ck-btn:hover { background: #15803d; border-color: #15803d; color: #fff; }
    .ck-btn.prob { border-color: #fde68a; background: #fffbeb; color: #b45309; padding: .25rem .5rem; margin-left: .2rem; }
    .ck-btn.prob:hover { background: #b45309; border-color: #b45309; color: #fff; }
    .ck-desfazer { border: 0; background: none; color: #cbd5e1; font-size: .7rem; padding: 0 .2rem; }
    .ck-desfazer:hover { color: #dc2626; }
    .ck-tabela form { display: inline-block; margin: 0; }

    /* O que correu mal fica numa faixa por baixo da linha, não espremido na célula */
    .ck-notas td { border-top: 0 !important; padding-top: 0 !important; }
    .ck-nota-prob { display: flex; flex-wrap: wrap; gap: .1rem .5rem; align-items: baseline; font-size: .85rem;
                    color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b;
                    border-radius: 8px; padding: .4rem .7rem; margin-bottom: .35rem; }
    .ck-nota-prob i { color: #f59e0b; }
    .ck-nota-prob .quem { font-weight: 800; white-space: nowrap; }
    .ck-nota-prob .texto { white-space: pre-wrap; word-break: break-word; }

    .ck-legenda { display: flex; flex-wrap: wrap; gap: .2rem 1.1rem; padding: .45rem .9rem;
                  border-top: 1px solid #eef2f7; font-size: .74rem; color: #94a3b8; }
    .ck-legenda i { margin-right: .15rem; }

    /* ---- A lista toda, só para a chefia ---- */
    .ck-titulo-cartao { display: flex; align-items: center; gap: .4rem; font-size: .76rem; font-weight: 800;
                        text-transform: uppercase; letter-spacing: .06em; color: #0f4c81; margin-bottom: .1rem; }
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

    .ck-aviso { border: 1px solid #fecaca; background: #fff5f5; color: #991b1b; border-radius: 10px; padding: .6rem .85rem; }
    .ck-aviso .t { font-weight: 800; }

    /* ---- Histórico ---- */
    .ck-hist td, .ck-hist th { padding: .3rem .5rem; font-size: .82rem; text-align: center; }
    .ck-hist td:first-child, .ck-hist th:first-child { text-align: left; white-space: nowrap; }
    .ck-hist .ok { color: #15803d; font-weight: 700; }
    .ck-hist .mau { color: #b91c1c; font-weight: 700; }
    .ck-hist .folga { color: #cbd5e1; }
    .ck-hist tr.hoje { background: #f2f8ff; }
    .ck-hist tr.fds td:first-child { color: #0d9488; font-weight: 700; }
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

    <div class="card-body">
        <div class="ck-pessoas">
            @foreach($colunas as $uid => $col)
                @php($cor = $col['turno'] ? \App\Services\HotlineRotation::COLORS[$loop->index % count(\App\Services\HotlineRotation::COLORS)] : '#94a3b8')
                @php($r = $resumo[$uid] ?? ['total' => 0, 'feitas' => 0])
                <div class="ck-pessoa {{ $col['turno'] ? '' : 'folga' }}" style="--c: {{ $cor }}">
                    @if($col['turno'])
                        <div class="ck-roda" style="--c: {{ $cor }}; --p: {{ $r['total'] ? round(100 * $r['feitas'] / $r['total']) : 100 }}">
                            <span class="n">{{ $r['feitas'] }}/{{ $r['total'] }}</span>
                        </div>
                    @else
                        <div class="ck-roda folga"><span class="n">{{ \App\Services\HotlineChecklist::iniciais($col['user']->name) }}</span></div>
                    @endif
                    <div>
                        <div class="nome">{{ $uid === $eu ? __('Eu') : $col['user']->name }}</div>
                        <div class="horas">
                            @if($col['turno'])
                                <b>{{ $col['horas'] }}</b>
                                @if($col['turno'] !== 'ambos') · {{ __(\App\Models\HotlineChecklistItem::TURNOS[$col['turno']]['curto']) }}@endif
                                @if($col['porConta'])
                                    <br>{{ __('no turno de :nome', ['nome' => $col['porConta']->name]) }}
                                @endif
                            @else
                                {{ $col['motivo'] ?? __('Sem turno') }}
                            @endif
                        </div>
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
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>
@endif

@if($gestor && $falhasDeOntem->isNotEmpty())
    <div class="ck-aviso mb-3">
        <span class="t"><i class="fas fa-exclamation-triangle"></i> {{ __('Ontem ficou por validar') }}</span>
        @foreach($falhasDeOntem as $falha)
            <div class="small mt-1">{{ $falha['nome'] }} — {{ $falha['tarefas'] }}</div>
        @endforeach
    </div>
@endif

<div class="card mb-3">
    <div class="table-responsive">
        <table class="table table-sm mb-0 ck-tabela">
            <thead>
                <tr>
                    <th>{{ __('Tarefa') }}</th>
                    @foreach($colunas as $uid => $col)
                        <th class="ck-col {{ $uid === $eu && $col['turno'] ? 'minha' : '' }} {{ $col['turno'] ? '' : 'fora' }}">
                            {{ $uid === $eu ? __('Eu') : $col['user']->name }}
                            <span class="t">{{ $col['turno'] ? $col['horas'] : ($col['curto'] ?? __('Sem turno')) }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @forelse($itens as $item)
                <tr class="{{ $estados[$item->id] ?? 'nada' }}">
                    <td class="ck-tarefa" style="--turno: {{ $item->turnoConfig()['cor'] }}">
                        <span class="titulo">{{ $item->titulo }}</span>
                        @if($item->turno !== 'ambos')
                            <span class="ck-eti" style="--c: {{ $item->turnoConfig()['cor'] }}">
                                <i class="fas {{ $item->turnoConfig()['icon'] }}"></i> {{ __($item->turnoConfig()['curto']) }}
                            </span>
                        @endif
                        @if($item->nota)
                            <div class="nota"><i class="fas fa-info-circle"></i> {{ $item->nota }}</div>
                        @endif
                    </td>

                    @foreach($colunas as $uid => $col)
                        @php($quem = $col['turno'] ? $col['user'] : null)
                        @php($check = $quem ? ($checks[$item->id . '_' . $quem->id] ?? null) : null)
                        @php($souEu = $hoje && $quem && $quem->id === $eu && $meuTurno)
                        <td class="ck-col {{ $uid === $eu && $col['turno'] ? 'minha' : '' }}">
                            @if(!$quem)
                                <span class="ck-vazio">—</span>
                            @elseif(!$item->doTurno($col['turno']))
                                <span class="ck-vazio" title="{{ __('Não é do turno dele hoje') }}">·</span>
                            @elseif($check)
                                <span class="{{ $check->isProblema() ? 'ck-problema' : 'ck-feito' }}"
                                      title="{{ $check->isProblema() ? __('Houve um problema') : __('Validada.') }} {{ $check->hora() }}">
                                    <i class="fas {{ $check->isProblema() ? 'fa-exclamation-triangle' : 'fa-check' }}"></i> {{ $check->hora() }}
                                </span>
                                @if($souEu)
                                    <form method="POST" action="{{ route('backoffice.checklist.desfazer', $item->id) }}">
                                        @csrf
                                        <button type="submit" class="ck-desfazer" title="{{ __('Desfazer') }}"><i class="fa fa-times"></i></button>
                                    </form>
                                @endif
                            @elseif($souEu)
                                <form method="POST" action="{{ route('backoffice.checklist.validar', $item->id) }}">
                                    @csrf<input type="hidden" name="estado" value="feito">
                                    <button type="submit" class="ck-btn"><i class="fa fa-check"></i> {{ __('Validar') }}</button>
                                </form>
                                <button type="button" class="ck-btn prob" data-toggle="modal" data-target="#ck-problema"
                                        data-id="{{ $item->id }}" data-titulo="{{ $item->titulo }}"
                                        title="{{ __('Houve um problema') }}"><i class="fa fa-exclamation-triangle"></i></button>
                            @elseif($dia->isPast() && !$dia->isToday())
                                <span class="ck-falhou"><i class="fa fa-times"></i> {{ __('Por validar') }}</span>
                            @else
                                <span class="ck-vazio">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
                @if(isset($notas[$item->id]))
                    <tr class="ck-notas">
                        <td colspan="{{ 1 + $colunas->count() }}">
                            @foreach($notas[$item->id] as $nota)
                                <div class="ck-nota-prob">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span class="quem">{{ $nota->user->name ?? '—' }}, {{ $nota->hora() }}</span>
                                    <span class="texto">{{ $nota->nota }}</span>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="{{ 1 + $colunas->count() }}" class="text-center text-muted py-4">
                        @if($dia->isPast() && !$hoje)
                            {{ __('Nesse dia ainda não havia nada na checklist.') }}
                        @elseif($listaToda->isNotEmpty())
                            {{ __('Não há nada para este dia.') }}
                            @if($dia->isWeekend()) {{ __('As tarefas da lista são para dias de semana.') }} @endif
                        @else
                            {{ __('A checklist ainda está vazia.') }}
                            @if($gestor) {{ __('Acrescente a primeira tarefa aqui em baixo.') }} @endif
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($itens->isNotEmpty())
        <div class="ck-legenda">
            <span><i class="fa fa-check text-success"></i> {{ __('validada') }}</span>
            <span><i class="fa fa-exclamation-triangle text-warning"></i> {{ __('com problema') }}</span>
            <span><strong>·</strong> {{ __('não é do turno dessa pessoa') }}</span>
            <span><strong>—</strong> {{ __('não está de serviço') }}</span>
        </div>
    @endif
</div>

@if($gestor)
    <div class="card mb-3">
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

<div class="card">
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

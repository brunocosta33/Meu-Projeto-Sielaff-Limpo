@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Quadro') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filtros = request()->only(['minhas', 'quem', 'tudo']))

@push('styles')
<style>
    .qd-colunas { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; align-items: start; }
    @media (max-width: 991px) { .qd-colunas { grid-template-columns: 1fr; } }

    /* Cada coluna tem a sua cor: a do estado. A barra de cima dá-lhe a cara. */
    .qd-coluna { background: var(--bg); border: 1px solid var(--borda); border-radius: 16px; padding: 0 .7rem .7rem;
                 min-height: 130px; overflow: hidden; }
    .qd-coluna > header { display: flex; align-items: center; gap: .45rem; margin: 0 -.7rem .75rem;
                          padding: .55rem .9rem; background: var(--c); color: #fff;
                          box-shadow: inset 0 -2px 6px rgba(0, 0, 0, .12); }
    .qd-coluna > header .nome { font-size: .76rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; }
    .qd-coluna > header .conta { margin-left: auto; font-size: .74rem; font-weight: 800; color: var(--c);
                                 background: #fff; border-radius: 999px; padding: .02rem .55rem; min-width: 1.6rem; text-align: center; }

    /* O cartão traz a cor de quem o tem, na barra da esquerda */
    .qd-cartao { position: relative; background: #fff; border: 1px solid #e8edf3; border-left: 4px solid var(--dono, #cbd5e1);
                 border-radius: 10px; padding: .65rem .75rem; margin-bottom: .6rem;
                 box-shadow: 0 1px 3px rgba(15, 23, 42, .06); transition: box-shadow .15s, transform .15s; }
    .qd-cartao:hover { box-shadow: 0 4px 12px rgba(15, 23, 42, .11); transform: translateY(-1px); }
    .qd-cartao.feito { background: linear-gradient(#fff, #f6fdf8); border-color: #d6efdd; opacity: .85; }
    .qd-cartao.atrasada { border-left-color: #dc2626; background: linear-gradient(#fff, #fff6f6); border-color: #fcdcdc; }
    .qd-titulo { font-weight: 600; color: #0f172a; line-height: 1.3; word-break: break-word; }
    .qd-cartao.feito .qd-titulo { text-decoration: line-through; text-decoration-color: #94a3b8; }
    .qd-desc { font-size: .8rem; color: #64748b; margin-top: .25rem; white-space: pre-wrap; word-break: break-word; }

    .qd-rodape { display: flex; align-items: center; gap: .4rem; margin-top: .55rem; }
    .qd-quem { display: inline-flex; align-items: center; gap: .4rem; font-size: .85rem; font-weight: 700;
               color: var(--dono, #475569); letter-spacing: -.01em; min-width: 0; }
    .qd-quem .nome { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .qd-bola { width: 26px; height: 26px; border-radius: 50%; background: var(--c); color: #fff; font-size: .68rem;
               font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 26px;
               box-shadow: 0 1px 3px rgba(15, 23, 42, .2); }
    .qd-prazo { font-size: .72rem; font-weight: 700; padding: .05rem .4rem; border-radius: 5px;
                background: #f1f5f9; color: #64748b; white-space: nowrap; }
    .qd-prazo.atrasado { background: #fee2e2; color: #b91c1c; }
    .qd-prazo.hoje { background: #fef3c7; color: #92400e; }

    .qd-botoes { margin-left: auto; display: flex; gap: .25rem; }
    .qd-botoes form { margin: 0; }
    /* O botão tem a cor da coluna para onde manda o cartão */
    .qd-btn { border: 1px solid #fde3c2; background: #fff8ef; border-radius: 7px; font-size: .72rem; font-weight: 700;
              padding: .12rem .45rem; color: #b45309; white-space: nowrap; transition: all .12s; }
    .qd-btn:hover { background: #b45309; border-color: #b45309; color: #fff; }
    .qd-btn.ok { border-color: #bbf7d0; background: #f0fdf4; color: #15803d; }
    .qd-btn.ok:hover { background: #15803d; border-color: #15803d; color: #fff; }
    .qd-btn.voltar { border-color: #e2e8f0; background: #fff; color: #64748b; }
    .qd-btn.voltar:hover { background: #64748b; border-color: #64748b; color: #fff; }

    .qd-conversa { margin-top: .55rem; border-top: 1px dashed #e2e8f0; padding-top: .5rem; }
    .qd-link { font-size: .74rem; font-weight: 700; color: #0f4c81; background: none; border: 0; padding: 0; }
    .qd-link:hover { text-decoration: underline; }
    .qd-ultimo { font-size: .76rem; color: #475569; background: #f8fafc; border-radius: 7px; padding: .3rem .5rem; margin-top: .35rem; }
    .qd-ultimo b { color: #0f172a; }
    .qd-ultimo .quando { color: #94a3b8; font-size: .7rem; }
    /* O fio cresce com a conversa: rola por dentro para o cartão não ficar infinito */
    .qd-fio { max-height: 260px; overflow-y: auto; margin: .4rem 0 .5rem; padding-right: .2rem; }
    .qd-fio::-webkit-scrollbar { width: 6px; }
    .qd-fio::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

    .qd-msg { display: flex; gap: .4rem; margin-bottom: .45rem; }
    .qd-msg .qd-bola { width: 20px; height: 20px; flex: 0 0 20px; font-size: .6rem; margin-top: .1rem; }
    .qd-balao { background: #f1f5f9; border-radius: 3px 10px 10px 10px; padding: .3rem .55rem; min-width: 0; flex: 1; }
    .qd-msg.meu .qd-balao { background: #e0f2fe; }
    .qd-balao .cab { font-size: .68rem; color: #64748b; font-weight: 700; display: flex; gap: .35rem; }
    .qd-balao .cab .quando { font-weight: 400; color: #94a3b8; margin-left: auto; white-space: nowrap; }
    .qd-balao .texto { font-size: .79rem; color: #1e293b; white-space: pre-wrap; word-break: break-word; line-height: 1.4; }

    .qd-passagem { display: flex; align-items: center; gap: .35rem; font-size: .72rem; color: #64748b;
                   background: #f8fafc; border-radius: 6px; padding: .2rem .45rem; margin-bottom: .45rem; }
    .qd-passagem .quando { margin-left: auto; color: #cbd5e1; white-space: nowrap; }

    .qd-escrever textarea { font-size: .8rem; resize: vertical; min-height: 44px; }
    .qd-escrever .ajuda { font-size: .68rem; color: #94a3b8; }

    /* A equipa em cima: quem é quem e quanto tem em mãos */
    .qd-equipa { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem .45rem; }
    .qd-chip { display: inline-flex; align-items: center; gap: .35rem; border: 1px solid #e2e8f0; background: #fff;
               border-radius: 999px; padding: .15rem .55rem .15rem .15rem; font-size: .78rem; color: #334155;
               text-decoration: none !important; white-space: nowrap; }
    .qd-chip:hover { border-color: #0f4c81; color: #0f4c81; }
    .qd-chip.on { border-color: var(--dono, #0f4c81); background: var(--dono, #0f4c81); color: #fff;
                  box-shadow: 0 2px 6px rgba(15, 23, 42, .22); }
    .qd-chip.on .qd-bola { background: rgba(255, 255, 255, .28) !important; }
    .qd-chip .n { font-size: .7rem; font-weight: 800; background: #f1f5f9; color: #475569; border-radius: 999px; padding: 0 .35rem; }
    .qd-chip.on .n { background: rgba(255, 255, 255, .25); color: #fff; }
    .qd-chip.zero { opacity: .55; }
    .qd-chip-todos { padding-left: .55rem; }

    .qd-quem.trocar { cursor: pointer; border: 0; background: none; padding: 0; }
    .qd-quem.trocar:hover { color: #0f4c81; }
    .qd-quem.trocar .seta { font-size: .6rem; color: #cbd5e1; }
    .qd-quem.trocar:hover .seta { color: #0f4c81; }
    .qd-passar { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 9px; padding: .45rem .55rem; margin-top: .45rem; }
    .qd-passar .rotulo { font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }

    .qd-vazia { text-align: center; color: var(--c); opacity: .35; font-size: .82rem; padding: 1.4rem .5rem; }
    .qd-vazia i { display: block; font-size: 1.6rem; margin-bottom: .3rem; }
    .qd-nova input, .qd-nova select { font-size: .9rem; }
    .qd-prazo-campo { position: relative; }
    .qd-prazo-campo label { position: absolute; top: -.62rem; left: .5rem; z-index: 2; margin: 0; padding: 0 .3rem;
                            background: #fff; font-size: .62rem; font-weight: 800; text-transform: uppercase;
                            letter-spacing: .05em; color: #94a3b8; }

    /* O prazo só vale alguma coisa se o atraso saltar à vista */
    .qd-chip-atraso { border-color: #fecaca; background: #fff1f1; color: #b91c1c; }
    .qd-chip-atraso:hover { border-color: #b91c1c; color: #b91c1c; }
    .qd-chip-atraso .n { background: #fee2e2; color: #b91c1c; }
    .qd-chip-atraso.on { background: #b91c1c; border-color: #b91c1c; color: #fff; }
    .qd-chip-atraso.on .n { background: rgba(255, 255, 255, .25); color: #fff; }
</style>
@endpush

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: .75rem">
            <div>
                <h4 class="mb-1"><i class="fas fa-clipboard-check text-primary"></i> {{ __('Quadro') }}</h4>
                <p class="text-muted small mb-0">{{ __('Dê uma coisa a alguém, veja quem pegou nela e o que respondeu.') }}</p>
            </div>
            <form method="GET" class="form-inline" style="gap: .5rem">
                @if($verTudo)<input type="hidden" name="tudo" value="1">@endif
                @if(request('quem'))<input type="hidden" name="quem" value="{{ request('quem') }}">@endif
                <label class="btn btn-sm {{ $soMinhas ? 'btn-primary' : 'btn-outline-secondary' }} mb-0">
                    <input type="checkbox" name="minhas" value="1" class="d-none" @checked($soMinhas) onchange="this.form.submit()">
                    <i class="fa fa-user"></i> {{ __('Só as minhas') }}
                </label>
                @unless($soMinhas)<input type="hidden" name="minhas" value="0">@endunless
            </form>
        </div>

        {{-- A equipa, arrumada pela função. O número é o que cada um tem em mãos. --}}
        <div class="qd-equipa mt-3">
            <a href="{{ route('backoffice.board.index', collect($filtros)->except('quem')->all()) }}"
               class="qd-chip qd-chip-todos {{ request('quem') ? '' : 'on' }}">
                <i class="fas fa-users"></i> {{ __('Todos') }}
                <span class="n">{{ $totalAbertas }}</span>
            </a>

            @if($numAtrasadas)
                <a href="{{ route('backoffice.board.index', collect($filtros)->merge(['atraso' => 1])->except('quem')->all()) }}"
                   class="qd-chip qd-chip-atraso {{ request('atraso') ? 'on' : '' }}">
                    <i class="fas fa-exclamation-triangle"></i> {{ __('Fora de prazo') }}
                    <span class="n">{{ $numAtrasadas }}</span>
                </a>
            @endif

            {{-- Sem os nomes das funções: a ordem já é essa (escritório primeiro, Hotline depois) --}}
            @foreach($equipa->flatten(1) as $p)
                @php($n = (int) ($porPessoa[$p->id] ?? 0))
                <a href="{{ route('backoffice.board.index', collect($filtros)->merge(['quem' => $p->id, 'minhas' => 0])->all()) }}"
                   class="qd-chip {{ request('quem') == $p->id ? 'on' : '' }} {{ $n ? '' : 'zero' }}"
                   style="--dono: {{ $cores[$p->id] ?? '#94a3b8' }}"
                   title="{{ $p->name ?: $p->email }}">
                    <span class="qd-bola" style="--c: {{ $cores[$p->id] ?? '#94a3b8' }}">
                        {{ \Illuminate\Support\Str::of($p->name ?: '?')->explode(' ')->map(fn ($x) => mb_substr($x, 0, 1))->take(2)->implode('') }}
                    </span>
                    {{ $p->id === $eu ? __('Eu') : ($p->name ?: $p->email) }}
                    <span class="n">{{ $n }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>
@endif

{{-- Dar uma tarefa: uma linha, nada mais --}}
<div class="card mb-3">
    <div class="card-body py-2 qd-nova">
        <form method="POST" action="{{ route('backoffice.board.store') }}">
            @csrf
            <div class="form-row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <input type="text" name="titulo" class="form-control" maxlength="255" required
                           value="{{ old('titulo') }}" placeholder="{{ __('O que é para fazer?') }}">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="assigned_to" class="form-control" required>
                        <option value="">{{ __('Para quem?') }}</option>
                        @foreach($pessoas as $p)
                            <option value="{{ $p->id }}" @selected(old('assigned_to') == $p->id)>{{ $p->name ?: $p->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="qd-prazo-campo">
                        <label for="qd-prazo-novo">{{ __('Para quando') }}</label>
                        <input type="date" id="qd-prazo-novo" name="prazo" class="form-control" value="{{ old('prazo') }}">
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary btn-block" title="{{ __('Dar a tarefa') }}"><i class="fa fa-plus"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="qd-colunas">
    @foreach($estados as $chave => $estado)
        <section class="qd-coluna" style="--c: {{ $estado['color'] }}; --bg: {{ $estado['col'] }}; --borda: {{ $estado['borda'] }}">
            <header>
                <i class="fas {{ $estado['icon'] }}" style="color: {{ $estado['color'] }}"></i>
                <span class="nome">{{ __($estado['label']) }}</span>
                <span class="conta">{{ $colunas[$chave]->count() }}</span>
            </header>

            @forelse($colunas[$chave] as $t)
                @php($meu = $t->assigned_to === $eu)
                <article class="qd-cartao {{ $t->isFeito() ? 'feito' : '' }} {{ $t->atrasada() ? 'atrasada' : '' }}"
                         style="--dono: {{ $cores[$t->assigned_to] ?? '#94a3b8' }}">
                    <div class="qd-titulo">{{ $t->titulo }}</div>
                    @if($t->descricao)<div class="qd-desc">{{ $t->descricao }}</div>@endif

                    <div class="qd-rodape">
                        <button type="button" class="qd-quem trocar" data-toggle="collapse" data-target="#qd-passar-{{ $t->id }}"
                                title="{{ __('Passar a outra pessoa') }}">
                            <span class="qd-bola" style="--c: {{ $cores[$t->assigned_to] ?? '#94a3b8' }}">{{ $t->iniciais() }}</span>
                            <span class="nome">{{ $meu ? __('Eu') : ($t->responsavel->name ?? '—') }}</span>
                            <i class="fa fa-exchange-alt seta"></i>
                        </button>

                        @if($t->prazo && !$t->isFeito())
                            <span class="qd-prazo {{ $t->atrasada() ? 'atrasado' : ($t->paraHoje() ? 'hoje' : '') }}">
                                <i class="fa fa-clock"></i>
                                {{ $t->paraHoje() ? __('hoje') : $t->prazo->format('d/m') }}
                            </span>
                        @endif

                        <span class="qd-botoes">
                            @if($chave !== 'em_curso' && !$t->isFeito())
                                <form method="POST" action="{{ route('backoffice.board.move', $t->id) }}">
                                    @csrf<input type="hidden" name="estado" value="em_curso">
                                    <button class="qd-btn" title="{{ __('Fico eu com isto') }}"><i class="fa fa-play"></i> {{ __('Pegar') }}</button>
                                </form>
                            @endif
                            @unless($t->isFeito())
                                <form method="POST" action="{{ route('backoffice.board.move', $t->id) }}">
                                    @csrf<input type="hidden" name="estado" value="feito">
                                    <button class="qd-btn ok" title="{{ __('Dar por concluída') }}"><i class="fa fa-check"></i></button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('backoffice.board.move', $t->id) }}">
                                    @csrf<input type="hidden" name="estado" value="por_fazer">
                                    <button class="qd-btn voltar" title="{{ __('Afinal não está feita') }}"><i class="fa fa-undo"></i></button>
                                </form>
                            @endunless
                        </span>
                    </div>

                    <div class="collapse qd-passar" id="qd-passar-{{ $t->id }}">
                        <form method="POST" action="{{ route('backoffice.board.reassign', $t->id) }}">
                            @csrf
                            <div class="rotulo mb-1">{{ __('Passar a') }}</div>
                            <div class="d-flex" style="gap: .35rem">
                                <select name="assigned_to" class="form-control form-control-sm" required>
                                    @foreach($equipa as $funcao => $gente)
                                        <optgroup label="{{ __(config('hr.functions.' . $funcao, 'Outros')) }}">
                                            @foreach($gente as $p)
                                                <option value="{{ $p->id }}" @selected($t->assigned_to == $p->id)>{{ $p->name ?: $p->email }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-primary"><i class="fa fa-paper-plane"></i></button>
                            </div>
                        </form>
                    </div>

                    @if($t->isFeito() && $t->concluido_em)
                        <div class="qd-ultimo">
                            <i class="fas fa-check-circle text-success"></i>
                            {{ __('Feita por :quem', ['quem' => $t->concluidor->name ?? '—']) }}
                            <span class="quando">· {{ $t->concluido_em->format('d/m H:i') }}</span>
                        </div>
                    @endif

                    <div class="qd-conversa">
                        <button type="button" class="qd-link" data-toggle="collapse" data-target="#qd-fio-{{ $t->id }}">
                            <i class="fa fa-comment-dots"></i>
                            {{ $t->comments_count
                                ? trans_choice(':count resposta|:count respostas', $t->comments_count, ['count' => $t->comments_count])
                                : __('Responder') }}
                        </button>

                        @php($ultima = $t->comments->last())
                        @if($ultima)
                            <div class="qd-ultimo">
                                <b>{{ \App\Models\BoardTask::nomeCurto($ultima->user->name ?? null) }}:</b>
                                {{ \Illuminate\Support\Str::limit($ultima->texto, 90) }}
                                <span class="quando">· {{ $ultima->created_at->format('d/m H:i') }}</span>
                            </div>
                        @endif

                        <div class="collapse" id="qd-fio-{{ $t->id }}">
                            @if($t->comments->isNotEmpty())
                                <div class="qd-fio" data-fio>
                                    @foreach($t->comments as $c)
                                        @if($c->isPassagem())
                                            <div class="qd-passagem">
                                                <i class="fa fa-exchange-alt"></i>
                                                {{ $c->texto }}
                                                <span class="quando">{{ $c->created_at->format('d/m H:i') }}</span>
                                            </div>
                                            @continue
                                        @endif
                                        <div class="qd-msg {{ $c->user_id === $eu ? 'meu' : '' }}">
                                            <span class="qd-bola" style="--c: {{ $cores[$c->user_id] ?? '#94a3b8' }}">
                                                {{ \Illuminate\Support\Str::of($c->user->name ?? '?')->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
                                            </span>
                                            <div class="qd-balao">
                                                <div class="cab">
                                                    <span>{{ $c->user_id === $eu ? __('Eu') : ($c->user->name ?? '—') }}</span>
                                                    <span class="quando">{{ $c->created_at->format('d/m H:i') }}</span>
                                                </div>
                                                <div class="texto">{{ $c->texto }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <form method="POST" action="{{ route('backoffice.board.comment', $t->id) }}" class="qd-escrever">
                                @csrf
                                <textarea name="texto" class="form-control form-control-sm" rows="2" maxlength="5000" required
                                          placeholder="{{ __('Escrever uma resposta…') }}"></textarea>
                                <div class="d-flex align-items-center mt-1" style="gap: .5rem">
                                    <span class="ajuda">{{ __('Enter muda de linha · Ctrl+Enter envia') }}</span>
                                    <button class="btn btn-sm btn-outline-primary ml-auto">
                                        <i class="fa fa-paper-plane"></i> {{ __('Responder') }}
                                    </button>
                                </div>
                            </form>

                            <div class="d-flex mt-2" style="gap: .4rem">
                                <button type="button" class="qd-link" data-toggle="collapse" data-target="#qd-editar-{{ $t->id }}">
                                    <i class="fa fa-pencil-alt"></i> {{ __('Alterar') }}
                                </button>
                                <form method="POST" action="{{ route('backoffice.board.destroy', $t->id) }}" class="ml-auto"
                                      onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Apagar esta tarefa e as respostas?')) }})">
                                    @csrf @method('DELETE')
                                    <button class="qd-link text-danger"><i class="fa fa-trash"></i> {{ __('Apagar') }}</button>
                                </form>
                            </div>

                            <div class="collapse mt-2" id="qd-editar-{{ $t->id }}">
                                <form method="POST" action="{{ route('backoffice.board.update', $t->id) }}">
                                    @csrf @method('PUT')
                                    <input type="text" name="titulo" class="form-control form-control-sm mb-1" maxlength="255" required value="{{ $t->titulo }}">
                                    <textarea name="descricao" class="form-control form-control-sm mb-1" rows="2"
                                              placeholder="{{ __('Detalhe (opcional)') }}">{{ $t->descricao }}</textarea>
                                    <div class="d-flex" style="gap: .35rem">
                                        <select name="assigned_to" class="form-control form-control-sm" required>
                                            @foreach($pessoas as $p)
                                                <option value="{{ $p->id }}" @selected($t->assigned_to == $p->id)>{{ $p->name ?: $p->email }}</option>
                                            @endforeach
                                        </select>
                                        <input type="date" name="prazo" class="form-control form-control-sm" value="{{ optional($t->prazo)->format('Y-m-d') }}">
                                        <button class="btn btn-sm btn-primary"><i class="fa fa-check"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="qd-vazia"><i class="fas {{ $estado['icon'] }}"></i> {{ __('Nada aqui.') }}</div>
            @endforelse

            @if($chave === 'feito' && $feitasEscondidas)
                <div class="text-center">
                    <a href="{{ route('backoffice.board.index', $filtros + ['tudo' => 1]) }}" class="qd-link">
                        {{ trans_choice('ver mais :count antiga|ver mais :count antigas', $feitasEscondidas, ['count' => $feitasEscondidas]) }}
                    </a>
                </div>
            @endif
        </section>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ao abrir um fio, mostrar o fim: numa conversa comprida o que interessa e a ultima resposta
        document.querySelectorAll('[id^="qd-fio-"]').forEach(function (fio) {
            if (window.jQuery) {
                $(fio).on('shown.bs.collapse', function () {
                    var lista = fio.querySelector('[data-fio]');
                    if (lista) lista.scrollTop = lista.scrollHeight;
                    var caixa = fio.querySelector('textarea[name="texto"]');
                    if (caixa) caixa.focus();
                });
            }
        });

        document.querySelectorAll('.qd-escrever textarea').forEach(function (caixa) {
            // Cresce com o que se escreve, ate um limite
            caixa.addEventListener('input', function () {
                caixa.style.height = 'auto';
                caixa.style.height = Math.min(caixa.scrollHeight, 200) + 'px';
            });

            // Enter muda de linha; Ctrl/Cmd+Enter envia
            caixa.addEventListener('keydown', function (ev) {
                if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
                    ev.preventDefault();
                    if (caixa.form.requestSubmit) { caixa.form.requestSubmit(); } else { caixa.form.submit(); }
                }
            });
        });
    });
</script>
@endpush

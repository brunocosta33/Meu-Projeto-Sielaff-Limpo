@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Checklist da Hotline') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))

@push('styles')
<style>
    .ck-topo { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; }
    .ck-dias { display: flex; align-items: center; gap: .35rem; }
    .ck-dias .data { font-weight: 700; color: #0f172a; min-width: 12.5rem; text-align: center; text-transform: capitalize; }
    .ck-seta { border: 1px solid #e2e8f0; background: #fff; color: #475569; border-radius: 8px; padding: .15rem .5rem; }
    .ck-seta:hover { background: #f1f5f9; color: #0f172a; }

    /* Quem está de serviço nesse dia, e como vai */
    .ck-pessoas { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .9rem; }
    .ck-pessoa { display: flex; align-items: center; gap: .55rem; border: 1px solid #e8edf3; border-left: 4px solid var(--c, #cbd5e1);
                 border-radius: 10px; padding: .4rem .7rem; background: #fff; min-width: 15rem; }
    .ck-pessoa.folga { background: #f8fafc; opacity: .75; }
    .ck-bola { width: 30px; height: 30px; border-radius: 50%; background: var(--c, #94a3b8); color: #fff; font-size: .7rem;
               font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 30px; }
    .ck-pessoa .nome { font-weight: 700; color: #0f172a; line-height: 1.15; }
    .ck-pessoa .horas { font-size: .74rem; color: #64748b; }
    .ck-conta { margin-left: auto; text-align: right; }
    .ck-conta .n { font-weight: 800; color: #0f172a; font-size: .95rem; }
    .ck-barra { height: 5px; width: 4.5rem; border-radius: 999px; background: #e2e8f0; overflow: hidden; margin-top: .2rem; }
    .ck-barra span { display: block; height: 100%; background: #16a34a; }
    .ck-barra.falta span { background: #f59e0b; }

    .ck-tabela { background: #fff; }
    .ck-tabela th { border-top: 0; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }
    .ck-tabela td { vertical-align: middle; }
    .ck-tabela .ck-col { text-align: center; width: 14rem; }
    .ck-tabela tr.arrastada td { background: #f8fafc; }
    .ck-tarefa .titulo { font-weight: 600; color: #0f172a; }
    .ck-tarefa .nota { font-size: .79rem; color: #64748b; white-space: pre-wrap; margin-top: .15rem; }
    .ck-turno { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em;
                border-radius: 5px; padding: .05rem .35rem; background: #f1f5f9; color: var(--c, #475569); border: 1px solid currentColor; }
    .ck-ordem { display: inline-flex; flex-direction: column; gap: 1px; margin-right: .5rem; vertical-align: middle; }
    .ck-ordem button { border: 1px solid #e2e8f0; background: #fff; color: #94a3b8; line-height: 1; padding: 0 .3rem; font-size: .6rem; }
    .ck-ordem button:hover { color: #0f4c81; border-color: #0f4c81; }
    .ck-linha-acoes { display: inline-flex; gap: .3rem; margin-left: .4rem; }
    .ck-linha-acoes button { border: 0; background: none; color: #cbd5e1; padding: 0 .15rem; }
    .ck-linha-acoes button:hover { color: #0f4c81; }
    .ck-linha-acoes .apagar:hover { color: #dc2626; }
    .ck-tabela form { display: inline-block; margin: 0; }

    /* A célula: o estado da tarefa para aquela pessoa, naquele dia */
    .ck-feito, .ck-problema, .ck-falhou { display: inline-flex; align-items: center; gap: .3rem; font-size: .8rem; font-weight: 700;
                                          border-radius: 7px; padding: .15rem .5rem; }
    .ck-feito { background: #dcfce7; color: #15803d; }
    .ck-problema { background: #fef3c7; color: #b45309; }
    .ck-falhou { background: #fee2e2; color: #b91c1c; }
    .ck-vazio { color: #cbd5e1; }
    .ck-nota-prob { font-size: .75rem; color: #92400e; margin-top: .2rem; text-align: left; white-space: pre-wrap; }
    .ck-btn { border: 1px solid #bbf7d0; background: #f0fdf4; color: #15803d; border-radius: 7px; font-size: .78rem;
              font-weight: 700; padding: .18rem .6rem; }
    .ck-btn:hover { background: #15803d; border-color: #15803d; color: #fff; }
    .ck-btn.prob { border-color: #fde68a; background: #fffbeb; color: #b45309; padding: .18rem .45rem; }
    .ck-btn.prob:hover { background: #b45309; border-color: #b45309; color: #fff; }
    .ck-desfazer { border: 0; background: none; color: #cbd5e1; font-size: .7rem; padding: 0 .2rem; }
    .ck-desfazer:hover { color: #dc2626; }

    .ck-aviso { border: 1px solid #fecaca; background: #fff5f5; color: #991b1b; border-radius: 10px; padding: .6rem .85rem; }
    .ck-aviso .t { font-weight: 800; }

    .ck-hist td, .ck-hist th { padding: .3rem .5rem; font-size: .82rem; text-align: center; }
    .ck-hist td:first-child, .ck-hist th:first-child { text-align: left; text-transform: capitalize; white-space: nowrap; }
    .ck-hist .ok { color: #15803d; font-weight: 700; }
    .ck-hist .mau { color: #b91c1c; font-weight: 700; }
    .ck-hist .folga { color: #cbd5e1; }
    .ck-hist tr.hoje { background: #f6f8fb; }
</style>
@endpush

@section('content')

<div class="card mb-3">
    <div class="card-body">
        <div class="ck-topo">
            <div>
                <h4 class="mb-1"><i class="fas fa-clipboard-list text-primary"></i> {{ __('Checklist da Hotline') }}</h4>
                <p class="text-muted small mb-0">
                    {{ __('O que há a fazer todos os dias. Quem está de serviço valida; os dias e os turnos vêm da escala.') }}
                </p>
            </div>
            <div class="ck-dias">
                <a class="ck-seta" href="{{ route('backoffice.checklist.index', ['dia' => $dia->copy()->subDay()->toDateString()]) }}"
                   title="{{ __('Dia anterior') }}"><i class="fa fa-chevron-left"></i></a>
                <span class="data">{{ $dia->isoFormat('dddd, D MMM YYYY') }}</span>
                <a class="ck-seta" href="{{ route('backoffice.checklist.index', ['dia' => $dia->copy()->addDay()->toDateString()]) }}"
                   title="{{ __('Dia seguinte') }}"><i class="fa fa-chevron-right"></i></a>
                @unless($hoje)
                    <a class="btn btn-sm btn-primary ml-1" href="{{ route('backoffice.checklist.index') }}">{{ __('Hoje') }}</a>
                @endunless
            </div>
        </div>

        <div class="ck-pessoas">
            @foreach($colunas as $uid => $col)
                @php($cor = $col['turno'] ? \App\Services\HotlineRotation::COLORS[$loop->index % count(\App\Services\HotlineRotation::COLORS)] : '#cbd5e1')
                <div class="ck-pessoa {{ $col['turno'] ? '' : 'folga' }}" style="--c: {{ $cor }}">
                    <span class="ck-bola" style="--c: {{ $cor }}">{{ \App\Services\HotlineChecklist::iniciais($col['user']->name) }}</span>
                    <div>
                        <div class="nome">{{ $uid === $eu ? __('Eu') : $col['user']->name }}</div>
                        <div class="horas">
                            @if($col['turno'])
                                {{ $col['horas'] }}@if($col['turno'] !== 'ambos') · {{ __(\App\Models\HotlineChecklistItem::TURNOS[$col['turno']]['curto']) }}@endif
                                @if($col['porConta'])
                                    <span class="text-muted">({{ __('no turno de :nome', ['nome' => $col['porConta']->name]) }})</span>
                                @endif
                            @else
                                {{ $col['motivo'] ?? __('Sem turno') }}
                            @endif
                        </div>
                    </div>
                    @if($col['turno'])
                        <div class="ck-conta">
                            <span class="n">{{ $resumo[$uid]['feitas'] }}/{{ $resumo[$uid]['total'] }}</span>
                            <div class="ck-barra {{ $resumo[$uid]['feitas'] < $resumo[$uid]['total'] ? 'falta' : '' }}">
                                <span style="width: {{ $resumo[$uid]['total'] ? round(100 * $resumo[$uid]['feitas'] / $resumo[$uid]['total']) : 0 }}%"></span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
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
                        <th class="ck-col">
                            {{ $uid === $eu ? __('Eu') : $col['user']->name }}
                            @unless($col['turno'])<i class="fa fa-moon text-muted ml-1" title="{{ $col['motivo'] }}"></i>@endunless
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @forelse($itens as $item)
                <tr>
                    <td class="ck-tarefa">
                        @if($gestor)
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
                        @endif
                        <span class="titulo">{{ $item->titulo }}</span>
                        @if($item->turno !== 'ambos')
                            <span class="ck-turno" style="--c: {{ $item->turnoConfig()['cor'] }}">
                                <i class="fas {{ $item->turnoConfig()['icon'] }}"></i> {{ __($item->turnoConfig()['curto']) }}
                            </span>
                        @endif
                        @if($gestor)
                            <span class="ck-linha-acoes">
                                <button type="button" data-toggle="modal" data-target="#ck-editar"
                                        data-id="{{ $item->id }}" data-titulo="{{ $item->titulo }}"
                                        data-nota="{{ $item->nota }}" data-turno="{{ $item->turno }}"
                                        title="{{ __('Alterar') }}"><i class="fa fa-pencil-alt"></i></button>
                                <form method="POST" action="{{ route('backoffice.checklist.destroy', $item->id) }}"
                                      onsubmit="return confirm('{{ __('Tirar esta tarefa da checklist?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="apagar" title="{{ __('Tirar da lista') }}"><i class="fa fa-trash"></i></button>
                                </form>
                            </span>
                        @endif
                        @if($item->nota)<div class="nota">{{ $item->nota }}</div>@endif
                    </td>

                    @foreach($colunas as $col)
                        @php($quem = $col['turno'] ? $col['user'] : null)
                        @php($check = $quem ? ($checks[$item->id . '_' . $quem->id] ?? null) : null)
                        @php($souEu = $hoje && $quem && $quem->id === $eu && $meuTurno)
                        <td class="ck-col">
                            @if(!$quem)
                                <span class="ck-vazio">—</span>
                            @elseif(!$item->doTurno($col['turno']))
                                <span class="ck-vazio" title="{{ __('Não é do turno dele hoje') }}">·</span>
                            @elseif($check)
                                <span class="{{ $check->isProblema() ? 'ck-problema' : 'ck-feito' }}">
                                    <i class="fas {{ $check->isProblema() ? 'fa-exclamation-triangle' : 'fa-check' }}"></i> {{ $check->hora() }}
                                </span>
                                @if($souEu)
                                    <form method="POST" action="{{ route('backoffice.checklist.desfazer', $item->id) }}">
                                        @csrf
                                        <button type="submit" class="ck-desfazer" title="{{ __('Desfazer') }}"><i class="fa fa-times"></i></button>
                                    </form>
                                @endif
                                @if($check->nota)<div class="ck-nota-prob">{{ $check->nota }}</div>@endif
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
            @empty
                <tr>
                    <td colspan="{{ 1 + $colunas->count() }}" class="text-center text-muted py-4">
                        @if($dia->isPast() && !$hoje)
                            {{ __('Nesse dia ainda não havia nada na checklist.') }}
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
</div>

@if($gestor)
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="POST" action="{{ route('backoffice.checklist.store') }}">
                @csrf
                <div class="form-row align-items-center">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <input type="text" name="titulo" class="form-control" maxlength="255" required
                               value="{{ old('titulo') }}" placeholder="{{ __('O que é para fazer todos os dias?') }}">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select name="turno" class="form-control">
                            @foreach($turnos as $chave => $t)
                                <option value="{{ $chave }}" @selected(old('turno') === $chave)>{{ __($t['label']) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <input type="text" name="nota" class="form-control" maxlength="1000"
                               value="{{ old('nota') }}" placeholder="{{ __('Nota (opcional)') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- O histórico: o que interessa é o padrão, não o dia solto --}}
<div class="card">
    <div class="card-body">
        <h6 class="text-uppercase text-muted mb-3" style="font-size: .75rem; letter-spacing: .06em">
            <i class="fa fa-history"></i> {{ __('Últimos :n dias', ['n' => count($historico)]) }}
        </h6>
        <div class="table-responsive">
            <table class="table table-sm mb-0 ck-hist">
                <thead>
                    <tr>
                        <th>{{ __('Dia') }}</th>
                        @foreach($titulares as $titular)
                            <th>{{ $titular->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($historico as $linha)
                    <tr class="{{ $linha['data']->isToday() ? 'hoje' : '' }}">
                        <td>
                            <a href="{{ route('backoffice.checklist.index', ['dia' => $linha['data']->toDateString()]) }}">
                                {{ $linha['data']->isoFormat('ddd D/MM') }}
                            </a>
                        </td>
                        @foreach($titulares as $sid => $titular)
                            @php($c = $linha['linhas'][$sid] ?? null)
                            <td>
                                @if(!$c || !$c['quem'])
                                    <span class="folga">{{ $c['motivo'] ?? '—' }}</span>
                                @elseif($c['total'] === 0)
                                    <span class="folga">—</span>
                                @else
                                    <span class="{{ $c['feitas'] >= $c['total'] ? 'ok' : 'mau' }}">{{ $c['feitas'] }}/{{ $c['total'] }}</span>
                                    @if($c['quem']->id !== $titular->id)
                                        <span class="folga" title="{{ __('Quem fez o turno: :nome', ['nome' => $c['quem']->name]) }}">
                                            {{ \App\Services\HotlineChecklist::iniciais($c['quem']->name) }}
                                        </span>
                                    @endif
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
                        <div class="form-group">
                            <label>{{ __('Turno') }}</label>
                            <select name="turno" id="ck-editar-turno" class="form-control">
                                @foreach($turnos as $chave => $t)
                                    <option value="{{ $chave }}">{{ __($t['label']) }}</option>
                                @endforeach
                            </select>
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
        });
    });
</script>
@endpush

@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Entregas a Técnicos') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filtros = request()->only(['technician_id', 'tipo', 'proalpha', 'de', 'ate', 'q']))

@push('styles')
<style>
    .ent-form label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #475569; margin-bottom: .2rem; }
    .ent-tipo { display: flex; gap: .4rem; }
    .ent-tipo label { flex: 1; cursor: pointer; margin: 0; padding: .45rem .5rem; border: 2px solid #e2e8f0; border-radius: 9px;
                      text-transform: none; font-size: .86rem; letter-spacing: 0; color: #334155; text-align: center; }
    .ent-tipo label.active { border-color: var(--c); background: var(--bg); color: var(--c); font-weight: 700; }
    .ent-tipo input { position: absolute; opacity: 0; }

    .ent-tabela { width: 100%; }
    .ent-tabela th { font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8;
                     font-weight: 700; border-bottom: 2px solid #e2e8f0; padding: .4rem .5rem; white-space: nowrap; }
    .ent-tabela td { padding: .55rem .5rem; border-bottom: 1px solid #f1f5f9; vertical-align: top; font-size: .88rem; color: #334155; }
    .ent-tabela tr:hover td { background: #f8fafc; }
    .ent-quando { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .ent-quando small { display: block; color: #94a3b8; font-size: .74rem; }
    .ent-artigo { font-weight: 600; color: #0f172a; }
    /* O codigo e o que se procura no ProAlpha: fica a ler-se bem, nao em letra miuda */
    .ent-codigo { display: block; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                  font-size: 1rem; font-weight: 700; color: #0f172a; letter-spacing: .02em; }
    .ent-artigo .nome { display: block; font-weight: 400; font-size: .84rem; color: #64748b; margin-top: .1rem; }
    .ent-obs { color: #64748b; font-size: .82rem; }
    .ent-tag { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 700;
               padding: .12rem .5rem; border-radius: 999px; background: var(--bg); color: var(--c); white-space: nowrap; }
    .ent-qtd { font-weight: 800; font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
    .ent-apagar { border: 0; background: none; color: #cbd5e1; padding: 0; }
    .ent-apagar:hover { color: #c62828; }
    .ent-tabela td > form { display: inline-block; margin: 0; vertical-align: middle; }
    .ent-corrigir { border: 0; background: none; color: #cbd5e1; padding: 0 .4rem 0 0; vertical-align: middle; }
    .ent-corrigir:hover { color: #0f4c81; }
    .ent-editado { display: block; font-size: .72rem; color: #b45309; font-style: italic; margin-top: .15rem; }
    .ent-aviso-proalpha { font-size: .8rem; color: #9a3412; background: #fff7ed; border: 1px solid #fed7aa;
                          border-radius: 8px; padding: .45rem .7rem; margin: 0; }

    /* ProAlpha: o que falta passar no programa */
    .ent-aviso { display: flex; align-items: center; gap: .75rem; background: #fff7ed; border: 1px solid #fed7aa;
                 border-left: 5px solid #ea580c; border-radius: 10px; padding: .7rem 1rem; margin-bottom: 1rem;
                 color: #7c2d12; text-decoration: none !important; }
    .ent-aviso:hover { background: #ffedd5; color: #7c2d12; }
    .ent-aviso .n { font-size: 1.6rem; font-weight: 800; line-height: 1; color: #ea580c; }
    .ent-aviso .txt { font-size: .88rem; font-weight: 600; }
    .ent-aviso .txt small { display: block; font-weight: 400; opacity: .8; }
    .ent-aviso.ok { background: #f0fdf4; border-color: #bbf7d0; border-left-color: #16a34a; color: #14532d; }
    .ent-aviso.ok .n { color: #16a34a; }

    .ent-pa-sim { display: inline-flex; align-items: center; gap: .3rem; font-size: .76rem; font-weight: 700; color: #15803d; white-space: nowrap; }
    .ent-pa-sim small { font-weight: 400; color: #94a3b8; }
    .ent-pa-btn { font-size: .74rem; font-weight: 700; padding: .12rem .5rem; border-radius: 999px;
                  border: 1px solid #fdba74; background: #fff7ed; color: #c2410c; white-space: nowrap; }
    .ent-pa-btn:hover { background: #ea580c; border-color: #ea580c; color: #fff; }
    .ent-pa-desfazer { border: 0; background: none; color: #cbd5e1; font-size: .72rem; padding: 0 0 0 .2rem; }
    .ent-pa-desfazer:hover { color: #64748b; }
    tr.por-lancar td { background: #fffbf5; }

    .ent-lote { display: none; align-items: center; gap: .6rem; background: #0f172a; color: #fff; border-radius: 10px;
                padding: .5rem .9rem; margin-bottom: .75rem; font-size: .86rem; }
    .ent-lote.show { display: flex; }
    .ent-lote .btn { font-size: .8rem; }
    .ent-check { width: 1px; }
    .ent-check input { cursor: pointer; }

    /* A caixa do ProAlpha no formulario: tem de dar nas vistas */
    .ent-pa-campo { display: flex; align-items: center; gap: .5rem; height: 100%; padding: .45rem .7rem;
                    border: 2px dashed #cbd5e1; border-radius: 9px; cursor: pointer; margin: 0; }
    .ent-pa-campo:hover { border-color: #ea580c; }
    .ent-pa-campo span { font-size: .8rem; font-weight: 600; color: #475569; text-transform: none; letter-spacing: 0; }
    @media (max-width: 767px) { .ent-tabela, .ent-tabela tbody, .ent-tabela tr, .ent-tabela td { display: block; width: 100%; }
        .ent-tabela thead { display: none; } .ent-tabela tr { border-bottom: 2px solid #e2e8f0; padding: .4rem 0; }
        .ent-tabela td { border: 0; padding: .15rem .5rem; } }
</style>
@endpush

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: .75rem">
            <div>
                <h4 class="mb-1"><i class="fas fa-dolly text-primary"></i> {{ __('Entregas a Técnicos') }}</h4>
                <p class="text-muted small mb-0">{{ __('Quem levou que peça ou ferramenta, quando e porquê.') }}</p>
            </div>
            <a href="{{ route('backoffice.deliveries.export', $filtros) }}" class="btn btn-outline-success btn-sm">
                <i class="fa fa-file-excel"></i> {{ __('Exportar Excel') }}
            </a>
        </div>
    </div>
</div>

{{-- O que falta passar no ProAlpha: é isto que evita o esquecimento --}}
@if($numPorLancar)
    @php($dias = $maisAntigaPorLancar ? (int) \Carbon\Carbon::parse($maisAntigaPorLancar)->startOfDay()->diffInDays(now()->startOfDay()) : 0)
    <a href="{{ route('backoffice.deliveries.index', ['proalpha' => 'por_lancar']) }}" class="ent-aviso">
        <i class="fas fa-exclamation-triangle fa-lg"></i>
        <span class="n">{{ $numPorLancar }}</span>
        <span class="txt">
            {{ trans_choice('registo por lançar no ProAlpha|registos por lançar no ProAlpha', $numPorLancar) }}
            <small>
                @if($dias > 0)
                    {{ trans_choice('O mais antigo é de há :count dia.|O mais antigo é de há :count dias.', $dias, ['count' => $dias]) }}
                @else
                    {{ __('Todos de hoje.') }}
                @endif
                {{ __('Clique para ver só esses.') }}
            </small>
        </span>
    </a>
@else
    <div class="ent-aviso ok">
        <i class="fas fa-check-circle fa-lg"></i>
        <span class="txt">{{ __('Está tudo lançado no ProAlpha.') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>
@endif

{{-- Registar --}}
<div class="card mb-3">
    <div class="card-body ent-form">
        <form method="POST" action="{{ route('backoffice.deliveries.store') }}">
            @csrf
            @php($tipo = old('tipo', 'entrega'))
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label>{{ __('Tipo') }}</label>
                    <div class="ent-tipo">
                        @foreach($tipos as $valor => $t)
                            <label class="{{ $tipo === $valor ? 'active' : '' }}" style="--c: {{ $t['color'] }}; --bg: {{ $t['bg'] }}">
                                <input type="radio" name="tipo" value="{{ $valor }}" @checked($tipo === $valor)>
                                <i class="fa {{ $t['icon'] }}"></i> {{ __($t['label']) }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label>{{ __('Técnico') }}</label>
                    <select name="technician_id" class="form-control" required>
                        <option value="">{{ __('Escolha…') }}</option>
                        @foreach($technicians as $t)
                            <option value="{{ $t->id }}" @selected(old('technician_id') == $t->id)>{{ $t->name ?: $t->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>{{ __('Peça ou ferramenta') }}</label>
                    <select name="item_id" class="form-control selectpicker" data-live-search="true"
                            title="{{ __('Procurar por código ou nome…') }}">
                        <option value="">{{ __('Procurar por código ou nome…') }}</option>
                        @foreach($artigos as $a)
                            <option value="{{ $a->id }}" @selected(old('item_id') == $a->id)>{{ $a->reference }} — {{ $a->name }}</option>
                        @endforeach
                    </select>
                    <a href="#ent-novo" data-toggle="collapse" class="small d-inline-block mt-1">
                        <i class="fa fa-plus"></i> {{ __('Não está na lista? Criar artigo') }}
                    </a>
                    <a href="{{ route('backoffice.stock.items.index') }}" class="small d-inline-block mt-1 ml-2 text-muted">
                        <i class="fa fa-pencil-alt"></i> {{ __('Corrigir ou apagar artigos') }}
                    </a>
                </div>
                <div class="form-group col-md-1">
                    <label>{{ __('Qtd') }}</label>
                    <input type="number" name="quantidade" class="form-control" min="1" value="{{ old('quantidade', 1) }}" required>
                </div>
                <div class="form-group col-md-2">
                    <label>{{ __('Data e hora') }}</label>
                    <input type="datetime-local" name="ocorrido_em" class="form-control"
                           value="{{ old('ocorrido_em', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
            </div>
            <div class="collapse {{ old('novo_nome') || old('novo_codigo') ? 'show' : '' }}" id="ent-novo">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>{{ __('Código do artigo novo') }}</label>
                        <input type="text" name="novo_codigo" class="form-control" value="{{ old('novo_codigo') }}" maxlength="100"
                               placeholder="{{ __('Ex.: 699015800') }}">
                    </div>
                    <div class="form-group col-md-9">
                        <label>{{ __('Nome do artigo novo') }}</label>
                        <input type="text" name="novo_nome" class="form-control" value="{{ old('novo_nome') }}" maxlength="255"
                               placeholder="{{ __('Ex.: chave dinamométrica') }}">
                        <small class="form-text text-muted">{{ __('Fica no catálogo de artigos, com stock zero. Esta página nunca mexe em quantidades.') }}</small>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-end">
                <div class="form-group col-md-7 mb-0">
                    <label>{{ __('Observações') }} <span class="text-muted font-weight-normal" style="text-transform: none">({{ __('opcional') }})</span></label>
                    <input type="text" name="observacoes" class="form-control" value="{{ old('observacoes') }}" maxlength="2000"
                           placeholder="{{ __('Ex.: levou para a reparação da loja 229') }}">
                </div>
                <div class="form-group col-md-3 mb-0">
                    <label>{{ __('ProAlpha') }}</label>
                    <label class="ent-pa-campo">
                        <input type="checkbox" name="proalpha" value="1" @checked(old('proalpha'))>
                        <span>{{ __('Já lancei no ProAlpha') }}</span>
                    </label>
                </div>
                <div class="form-group col-md-2 mb-0">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> {{ __('Registar') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Registo --}}
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: .5rem">
            <h5 class="card-title mb-0"><i class="fa fa-list text-secondary"></i> {{ __('Registo') }}</h5>
            <form method="GET" class="form-inline" style="gap: .5rem">
                <select name="technician_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Todos os técnicos') }}</option>
                    @foreach($technicians as $t)
                        <option value="{{ $t->id }}" @selected(request('technician_id') == $t->id)>{{ $t->name ?: $t->email }}</option>
                    @endforeach
                </select>
                <select name="tipo" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Entregas e devoluções') }}</option>
                    @foreach($tipos as $valor => $t)
                        <option value="{{ $valor }}" @selected(request('tipo') === $valor)>{{ __($t['label']) }}</option>
                    @endforeach
                </select>
                <select name="proalpha" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ __('ProAlpha: todos') }}</option>
                    <option value="por_lancar" @selected(request('proalpha') === 'por_lancar')>{{ __('Por lançar no ProAlpha') }}</option>
                    <option value="lancadas" @selected(request('proalpha') === 'lancadas')>{{ __('Já lançados no ProAlpha') }}</option>
                </select>
                <input type="date" name="de" class="form-control form-control-sm" value="{{ request('de') }}" title="{{ __('De') }}" onchange="this.form.submit()">
                <input type="date" name="ate" class="form-control form-control-sm" value="{{ request('ate') }}" title="{{ __('Até') }}" onchange="this.form.submit()">
                <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                       placeholder="{{ __('Artigo, nota, técnico…') }}" style="min-width: 170px">
            </form>
        </div>

        @if($deliveries->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-dolly fa-3x mb-3"></i>
                <p class="mb-0">{{ __('Ainda não há nada registado.') }}</p>
            </div>
        @else
            {{-- Fora da tabela de proposito: as caixinhas ligam-se a este formulario pelo atributo form= --}}
            <form method="POST" action="{{ route('backoffice.deliveries.proalpha.bulk') }}" id="ent-lote-form">@csrf</form>

            <div class="ent-lote" id="ent-lote">
                <i class="fas fa-check-double"></i>
                <span><strong id="ent-lote-n">0</strong> {{ __('escolhidos') }}</span>
                <button type="submit" form="ent-lote-form" class="btn btn-sm btn-warning ml-auto">
                    <i class="fa fa-check"></i> {{ __('Marcar como lançados no ProAlpha') }}
                </button>
            </div>

            <div class="table-responsive">
                <table class="ent-tabela">
                    <thead>
                        <tr>
                            <th class="ent-check"><input type="checkbox" id="ent-todos" title="{{ __('Escolher todos os que estão por lançar') }}"></th>
                            <th>{{ __('Quando') }}</th>
                            <th>{{ __('Tipo') }}</th>
                            <th>{{ __('Técnico') }}</th>
                            <th>{{ __('Artigo') }}</th>
                            <th class="text-right">{{ __('Qtd') }}</th>
                            <th>{{ __('ProAlpha') }}</th>
                            <th>{{ __('Observações') }}</th>
                            <th>{{ __('Registado por') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveries as $e)
                            @php($t = $e->tipoConfig())
                            <tr class="{{ $e->isProalpha() ? '' : 'por-lancar' }}">
                                <td class="ent-check">
                                    @unless($e->isProalpha())
                                        <input type="checkbox" name="ids[]" value="{{ $e->id }}" form="ent-lote-form" class="ent-um">
                                    @endunless
                                </td>
                                <td class="ent-quando">
                                    {{ optional($e->ocorrido_em)->format('d/m/Y') }}
                                    <small>{{ optional($e->ocorrido_em)->format('H:i') }}</small>
                                </td>
                                <td><span class="ent-tag" style="--c: {{ $t['color'] }}; --bg: {{ $t['bg'] }}"><i class="fa {{ $t['icon'] }}"></i> {{ $e->tipoLabel() }}</span></td>
                                <td>{{ $e->technician->name ?? '—' }}</td>
                                <td class="ent-artigo">
                                    @if($e->codigo)
                                        <span class="ent-codigo">{{ $e->codigo }}</span>
                                        <span class="nome">{{ $e->artigo }}</span>
                                    @else
                                        {{ $e->artigo }}
                                    @endif
                                </td>
                                <td class="ent-qtd">{{ (int) $e->quantidade }}</td>
                                <td>
                                    @if($e->isProalpha())
                                        <span class="ent-pa-sim" title="{{ $e->proalphaUser->name ?? '' }}">
                                            <i class="fas fa-check-circle"></i> {{ __('Lançado') }}
                                            <small>{{ $e->proalpha_em->format('d/m') }}</small>
                                        </span>
                                        <form method="POST" action="{{ route('backoffice.deliveries.proalpha', $e->id) }}" class="d-inline"
                                              onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Afinal ainda não foi lançado no ProAlpha?')) }})">
                                            @csrf
                                            <input type="hidden" name="desmarcar" value="1">
                                            <button class="ent-pa-desfazer" title="{{ __('Afinal não foi lançado') }}"><i class="fa fa-undo"></i></button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('backoffice.deliveries.proalpha', $e->id) }}">
                                            @csrf
                                            <button class="ent-pa-btn"><i class="fa fa-check"></i> {{ __('Lançar') }}</button>
                                        </form>
                                    @endif
                                </td>
                                <td class="ent-obs">{{ $e->observacoes }}</td>
                                <td class="ent-obs">
                                    {{ $e->creator->name ?? '—' }}
                                    @if($e->foiEditado())
                                        <span class="ent-editado">
                                            <i class="fa fa-pencil-alt"></i>
                                            {{ __('corrigido a :data por :quem', ['data' => $e->editado_em->format('d/m H:i'), 'quem' => $e->editor->name ?? '—']) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right text-nowrap">
                                    <button type="button" class="ent-corrigir" title="{{ __('Corrigir') }}"
                                            data-toggle="modal" data-target="#ent-corrigir"
                                            data-id="{{ $e->id }}"
                                            data-tipo="{{ $e->tipo }}"
                                            data-tecnico="{{ $e->technician_id }}"
                                            data-artigo="{{ $e->item_id }}"
                                            data-artigo-nome="{{ $e->artigoCompleto() }}"
                                            data-qtd="{{ (int) $e->quantidade }}"
                                            data-quando="{{ optional($e->ocorrido_em)->format('Y-m-d\TH:i') }}"
                                            data-obs="{{ $e->observacoes }}"
                                            data-proalpha="{{ $e->isProalpha() ? 1 : 0 }}">
                                        <i class="fa fa-pencil-alt"></i>
                                    </button>
                                    <form method="POST" action="{{ route('backoffice.deliveries.destroy', $e->id) }}"
                                          onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Apagar este registo?')) }})">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ent-apagar" title="{{ __('Apagar') }}"><i class="fa fa-times"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($deliveries->hasPages())
                <div class="mt-3 d-flex justify-content-center">{{ $deliveries->links() }}</div>
            @endif
        @endif
    </div>
</div>

{{-- Corrigir uma entrega já gravada --}}
<div class="modal fade" id="ent-corrigir" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="ent-corrigir-form" class="ent-form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-pencil-alt text-primary"></i> {{ __('Corrigir entrega') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Fechar') }}"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="ent-aviso-proalpha d-none" id="ent-corrigir-aviso">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ __('Esta entrega já foi lançada no ProAlpha. Se mudar o técnico, o artigo, a quantidade ou o tipo, volta a ficar por lançar — e tem de a passar lá outra vez.') }}
                    </p>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>{{ __('Tipo') }}</label>
                            <select name="tipo" class="form-control" id="ent-corrigir-tipo" required>
                                @foreach($tipos as $valor => $t)
                                    <option value="{{ $valor }}">{{ __($t['label']) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-5">
                            <label>{{ __('Técnico') }}</label>
                            <select name="technician_id" class="form-control" id="ent-corrigir-tecnico" required>
                                @foreach($technicians as $t)
                                    <option value="{{ $t->id }}">{{ $t->name ?: $t->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>{{ __('Qtd') }}</label>
                            <input type="number" name="quantidade" class="form-control" id="ent-corrigir-qtd" min="1" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>{{ __('Data e hora') }}</label>
                            <input type="datetime-local" name="ocorrido_em" class="form-control" id="ent-corrigir-quando"
                                   max="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Peça ou ferramenta') }}</label>
                        <select name="item_id" class="form-control selectpicker" id="ent-corrigir-artigo" required
                                data-live-search="true" data-container="body" data-size="10"
                                title="{{ __('Procurar por código ou nome…') }}">
                            @foreach($artigos as $a)
                                <option value="{{ $a->id }}">{{ $a->reference }} — {{ $a->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">{{ __('Para criar um artigo novo, use o formulário da página.') }}</small>
                    </div>

                    <div class="form-group mb-0">
                        <label>{{ __('Observações') }}</label>
                        <input type="text" name="observacoes" class="form-control" id="ent-corrigir-obs" maxlength="2000">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link text-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{ __('Guardar correção') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.selectpicker) $('.selectpicker').selectpicker();
        // Corrigir: um modal só, preenchido a partir do botão da linha
        var modal = document.getElementById('ent-corrigir');
        if (modal && window.jQuery) {
            $(modal).on('show.bs.modal', function (ev) {
                var b = ev.relatedTarget;
                if (!b) return;
                var val = function (id, v) { var el = document.getElementById(id); if (el) el.value = v; };

                document.getElementById('ent-corrigir-form').action =
                    {{ \Illuminate\Support\Js::from(route('backoffice.deliveries.index')) }} + '/' + b.dataset.id;

                val('ent-corrigir-tipo', b.dataset.tipo);
                val('ent-corrigir-tecnico', b.dataset.tecnico);
                val('ent-corrigir-qtd', b.dataset.qtd);
                val('ent-corrigir-quando', b.dataset.quando);
                val('ent-corrigir-obs', b.dataset.obs || '');

                // O artigo pode ter saído do catálogo: nesse caso acrescenta-se à lista para não se perder
                var artigo = document.getElementById('ent-corrigir-artigo');
                if (artigo) {
                    if (!artigo.querySelector('option[value="' + b.dataset.artigo + '"]')) {
                        var o = document.createElement('option');
                        o.value = b.dataset.artigo;
                        o.textContent = b.dataset.artigoNome;
                        artigo.appendChild(o);
                    }
                    artigo.value = b.dataset.artigo;
                    if ($.fn.selectpicker) $(artigo).selectpicker('refresh');
                }

                document.getElementById('ent-corrigir-aviso')
                    .classList.toggle('d-none', b.dataset.proalpha !== '1');
            });
        }

        // Marcacao em lote: a barra preta so aparece quando ha alguma escolhida
        var barra = document.getElementById('ent-lote');
        var conta = document.getElementById('ent-lote-n');
        var todos = document.getElementById('ent-todos');
        var uns = Array.prototype.slice.call(document.querySelectorAll('.ent-um'));

        function atualiza() {
            var n = uns.filter(function (c) { return c.checked; }).length;
            if (conta) conta.textContent = n;
            if (barra) barra.classList.toggle('show', n > 0);
            if (todos) todos.checked = n > 0 && n === uns.length;
        }
        uns.forEach(function (c) { c.addEventListener('change', atualiza); });
        if (todos) todos.addEventListener('change', function () {
            uns.forEach(function (c) { c.checked = todos.checked; });
            atualiza();
        });
        atualiza();

        document.querySelectorAll('.ent-tipo input').forEach(function (r) {
            r.addEventListener('change', function () {
                document.querySelectorAll('.ent-tipo label').forEach(function (l) { l.classList.toggle('active', l.contains(r) && r.checked); });
            });
        });
    });
</script>
@endpush

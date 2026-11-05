@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Entregas a Técnicos') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filtros = request()->only(['technician_id', 'tipo', 'de', 'ate', 'q']))

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
    .ent-obs { color: #64748b; font-size: .82rem; }
    .ent-tag { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 700;
               padding: .12rem .5rem; border-radius: 999px; background: var(--bg); color: var(--c); white-space: nowrap; }
    .ent-qtd { font-weight: 800; font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
    .ent-apagar { border: 0; background: none; color: #cbd5e1; padding: 0; }
    .ent-apagar:hover { color: #c62828; }
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
                <div class="form-group col-md-10 mb-0">
                    <label>{{ __('Observações') }} <span class="text-muted font-weight-normal" style="text-transform: none">({{ __('opcional') }})</span></label>
                    <input type="text" name="observacoes" class="form-control" value="{{ old('observacoes') }}" maxlength="2000"
                           placeholder="{{ __('Ex.: levou para a reparação da loja 229') }}">
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
            <div class="table-responsive">
                <table class="ent-tabela">
                    <thead>
                        <tr>
                            <th>{{ __('Quando') }}</th>
                            <th>{{ __('Tipo') }}</th>
                            <th>{{ __('Técnico') }}</th>
                            <th>{{ __('Artigo') }}</th>
                            <th class="text-right">{{ __('Qtd') }}</th>
                            <th>{{ __('Observações') }}</th>
                            <th>{{ __('Registado por') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveries as $e)
                            @php($t = $e->tipoConfig())
                            <tr>
                                <td class="ent-quando">
                                    {{ optional($e->ocorrido_em)->format('d/m/Y') }}
                                    <small>{{ optional($e->ocorrido_em)->format('H:i') }}</small>
                                </td>
                                <td><span class="ent-tag" style="--c: {{ $t['color'] }}; --bg: {{ $t['bg'] }}"><i class="fa {{ $t['icon'] }}"></i> {{ $e->tipoLabel() }}</span></td>
                                <td>{{ $e->technician->name ?? '—' }}</td>
                                <td class="ent-artigo">
                                    {{ $e->artigo }}
                                    @if($e->codigo)<small class="d-block" style="font-weight: 400; color: #94a3b8; font-family: ui-monospace, Menlo, monospace">{{ $e->codigo }}</small>@endif
                                </td>
                                <td class="ent-qtd">{{ (int) $e->quantidade }}</td>
                                <td class="ent-obs">{{ $e->observacoes }}</td>
                                <td class="ent-obs">{{ $e->creator->name ?? '—' }}</td>
                                <td class="text-right">
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.selectpicker) $('.selectpicker').selectpicker();
        document.querySelectorAll('.ent-tipo input').forEach(function (r) {
            r.addEventListener('change', function () {
                document.querySelectorAll('.ent-tipo label').forEach(function (l) { l.classList.toggle('active', l.contains(r) && r.checked); });
            });
        });
    });
</script>
@endpush

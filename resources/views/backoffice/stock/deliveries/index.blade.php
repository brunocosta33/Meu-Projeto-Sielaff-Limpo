@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Entregas a Técnicos') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))

@push('styles')
<style>
    .en-form label { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #475569; margin-bottom: .2rem; }
    .en-dir { display: flex; gap: .5rem; }
    .en-dir label { flex: 1; cursor: pointer; margin: 0; padding: .55rem .7rem; border: 2px solid #e2e8f0; border-radius: 10px;
                    text-transform: none; font-size: .9rem; letter-spacing: 0; color: #334155; text-align: center; }
    .en-dir label.active { border-color: var(--c); background: var(--bg); color: var(--c); font-weight: 700; }
    .en-dir input { position: absolute; opacity: 0; }

    .en-linha { display: grid; grid-template-columns: minmax(0, 150px) minmax(0, 1fr) minmax(0, 1.1fr) auto;
                gap: .75rem; align-items: center; padding: .7rem .75rem; border-radius: 10px; }
    .en-linha + .en-linha { border-top: 1px solid #eef1f5; }
    .en-linha:hover { background: #f8fafc; }
    @media (max-width: 900px) { .en-linha { grid-template-columns: 1fr; gap: .4rem; } }
    .en-quando { font-size: .84rem; color: #475569; font-variant-numeric: tabular-nums; }
    .en-quando small { display: block; color: #94a3b8; font-size: .72rem; }
    .en-artigo { font-weight: 700; color: #0f172a; }
    .en-artigo small { display: block; font-weight: 400; color: #64748b; font-family: ui-monospace, Menlo, monospace; font-size: .74rem; }
    .en-quem { font-size: .88rem; color: #334155; }
    .en-quem small { display: block; color: #94a3b8; font-size: .74rem; }
    .en-nota { grid-column: 1 / -1; font-size: .82rem; color: #475569; background: #f8fafc;
               border-left: 3px solid #cbd5e1; border-radius: 0 6px 6px 0; padding: .3rem .6rem; }
    .en-qtd { display: inline-flex; align-items: center; gap: .35rem; font-weight: 800; font-size: .95rem;
              padding: .25rem .7rem; border-radius: 999px; background: var(--bg); color: var(--c); white-space: nowrap; }

    .en-tec { border: 1px solid #e2e8f0; border-radius: 12px; padding: .7rem .85rem; }
    .en-tec + .en-tec { margin-top: .6rem; }
    .en-tec h6 { margin: 0 0 .4rem; font-size: .92rem; font-weight: 700; color: #0f172a; }
    .en-tec ul { list-style: none; margin: 0; padding: 0; font-size: .85rem; }
    .en-tec li { display: flex; justify-content: space-between; gap: .5rem; padding: .15rem 0; color: #334155; }
    .en-tec li span:last-child { font-weight: 700; font-variant-numeric: tabular-nums; }
    .en-vazio { color: #94a3b8; font-size: .84rem; }
</style>
@endpush

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <h4 class="mb-1"><i class="fas fa-dolly text-primary"></i> {{ __('Entregas a Técnicos') }}</h4>
        <p class="text-muted small mb-0">
            {{ __('Registo do que sai para cada técnico e do que volta. Não controla stock: serve para ficar escrito quem levou o quê, quando e porquê.') }}
        </p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>
@endif

<div class="row">
    <div class="col-lg-5 mb-3">
        <div class="card">
            <div class="card-body en-form">
                <h5 class="card-title"><i class="fa fa-plus-circle text-success"></i> {{ __('Registar') }}</h5>

                <form method="POST" action="{{ route('backoffice.stock.deliveries.store') }}">
                    @csrf
                    @php($direcao = old('direcao', 'entrega'))
                    <div class="form-group">
                        <div class="en-dir">
                            <label class="{{ $direcao === 'entrega' ? 'active' : '' }}" style="--c: #0369a1; --bg: #e0f2fe">
                                <input type="radio" name="direcao" value="entrega" @checked($direcao === 'entrega')>
                                <i class="fa fa-arrow-right"></i> {{ __('Entrega') }}
                            </label>
                            <label class="{{ $direcao === 'devolucao' ? 'active' : '' }}" style="--c: #15803d; --bg: #dcfce7">
                                <input type="radio" name="direcao" value="devolucao" @checked($direcao === 'devolucao')>
                                <i class="fa fa-undo"></i> {{ __('Devolução') }}
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Técnico') }}</label>
                        <select name="technician_id" class="form-control" required>
                            <option value="">{{ __('Escolha…') }}</option>
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}" @selected(old('technician_id') == $t->id)>{{ $t->name ?: $t->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Peça ou ferramenta') }}</label>
                        <select name="item_id" id="en-item" class="form-control selectpicker" data-live-search="true" title="{{ __('Procurar no catálogo…') }}">
                            <option value="">{{ __('Procurar no catálogo…') }}</option>
                            @foreach($items as $i)
                                <option value="{{ $i->id }}" @selected(old('item_id') == $i->id)>{{ $i->name }} — {{ $i->reference }}</option>
                            @endforeach
                        </select>
                        <a href="#en-novo" data-toggle="collapse" class="small d-inline-block mt-1">
                            <i class="fa fa-plus"></i> {{ __('Não está na lista? Criar aqui') }}
                        </a>
                        <div class="collapse {{ old('novo_nome') ? 'show' : '' }}" id="en-novo">
                            <div class="form-row mt-2">
                                <div class="form-group col-7 mb-2">
                                    <label>{{ __('Nome') }}</label>
                                    <input type="text" name="novo_nome" class="form-control" value="{{ old('novo_nome') }}" maxlength="255"
                                           placeholder="{{ __('Ex.: chave dinamométrica') }}">
                                </div>
                                <div class="form-group col-5 mb-2">
                                    <label>{{ __('Referência') }}</label>
                                    <input type="text" name="novo_referencia" class="form-control" value="{{ old('novo_referencia') }}" maxlength="255"
                                           placeholder="{{ __('opcional') }}">
                                </div>
                            </div>
                            <small class="form-text text-muted mb-2">{{ __('Sem referência, fica uma automática. O artigo passa a existir no catálogo.') }}</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-4">
                            <label>{{ __('Quantidade') }}</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="{{ old('quantity', 1) }}" required>
                        </div>
                        <div class="form-group col-8">
                            <label>{{ __('Data e hora') }}</label>
                            <input type="datetime-local" name="moved_at" class="form-control"
                                   value="{{ old('moved_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Observações') }} <span class="text-muted font-weight-normal" style="text-transform: none">({{ __('opcional') }})</span></label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="3000"
                                  placeholder="{{ __('Ex.: levou para a reparação da loja 229') }}">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> {{ __('Registar movimento') }}</button>
                </form>
            </div>
        </div>

        {{-- O que está com cada técnico, somado do registo --}}
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-user-check text-info"></i> {{ __('Com os técnicos') }}</h5>
                <p class="text-muted small">{{ __('Somado deste registo: entregue menos devolvido e consumido.') }}</p>
                @php($algum = false)
                @foreach($technicians as $t)
                    @php($comEle = $holdings[$t->id] ?? collect())
                    @continue($comEle->isEmpty())
                    @php($algum = true)
                    <div class="en-tec">
                        <h6>{{ $t->name ?: $t->email }}</h6>
                        <ul>
                            @foreach($comEle as $linha)
                                <li><span>{{ $linha->item->name ?? '—' }}</span><span>{{ (int) $linha->quantity }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
                @unless($algum)
                    <p class="en-vazio mb-0">{{ __('Ninguém tem nada por devolver.') }}</p>
                @endunless
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap: .5rem">
                    <h5 class="card-title mb-0"><i class="fa fa-list text-secondary"></i> {{ __('Registo') }}</h5>
                    <form method="GET" class="form-inline" style="gap: .5rem">
                        <select name="technician_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">{{ __('Todos os técnicos') }}</option>
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}" @selected(request('technician_id') == $t->id)>{{ $t->name ?: $t->email }}</option>
                            @endforeach
                        </select>
                        <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                               placeholder="{{ __('Artigo, referência, nota…') }}" style="min-width: 180px">
                    </form>
                </div>

                @if($movements->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-dolly fa-3x mb-3"></i>
                        <p class="mb-0">{{ __('Ainda não há entregas registadas.') }}</p>
                    </div>
                @else
                    @foreach($movements as $m)
                        @php($entrega = $m->movement_type === 'to_technician')
                        <div class="en-linha">
                            <div class="en-quando">
                                {{ optional($m->moved_at)->format('d/m/Y') }}
                                <small>{{ optional($m->moved_at)->format('H:i') }}</small>
                            </div>
                            <div class="en-artigo">
                                {{ $m->item->name ?? '—' }}
                                <small>{{ $m->item->reference ?? '' }}</small>
                            </div>
                            <div class="en-quem">
                                <i class="fa {{ $entrega ? 'fa-arrow-right text-primary' : 'fa-undo text-success' }}"></i>
                                {{ $m->technician->name ?? '—' }}
                                <small>{{ __('registado por') }} {{ $m->creator->name ?? '—' }}</small>
                            </div>
                            <div>
                                <span class="en-qtd" style="--c: {{ $entrega ? '#0369a1' : '#15803d' }}; --bg: {{ $entrega ? '#e0f2fe' : '#dcfce7' }}">
                                    {{ $entrega ? '−' : '+' }}{{ (int) $m->quantity }}
                                </span>
                            </div>
                            @if($m->notes)<div class="en-nota">{{ $m->notes }}</div>@endif
                        </div>
                    @endforeach

                    @if($movements->hasPages())
                        <div class="mt-3 d-flex justify-content-center">{{ $movements->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.selectpicker) $('.selectpicker').selectpicker();
        document.querySelectorAll('.en-dir input').forEach(function (r) {
            r.addEventListener('change', function () {
                document.querySelectorAll('.en-dir label').forEach(function (l) { l.classList.toggle('active', l.contains(r) && r.checked); });
            });
        });
    });
</script>
@endpush

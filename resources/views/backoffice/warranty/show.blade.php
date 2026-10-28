@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Fora da Garantia') }} #{{ $case->id }}</title>
@endsection

@php
    \Carbon\Carbon::setLocale(app()->getLocale());
    $phase = $case->phase();
    $alert = $case->waitingAlert();
    $type = $case->typeConfig();
    $blocks = $case->blocks();
    $feitos = $case->doneSteps();
    $porFazer = $case->pendingSteps();
    // O próximo passo fica no cartão grande; os outros por fazer ficam atrás de "não é isto?"
    $outros = array_values(array_filter($porFazer, fn ($s) => !$next || $s['block'] . '.' . $s['half'] !== $next['block'] . '.' . $next['half']));
    $machinesByStore = $stores->mapWithKeys(fn ($s) => [$s->id => $s->machines->map(fn ($m) => ['id' => $m->id, 'label' => trim($m->serial_number . ($m->descricao ? ' — ' . $m->descricao : ''))])->values()]);
@endphp

@push('styles')
<style>
    .wc-client { display: inline-block; font-size: .72rem; font-weight: 800; letter-spacing: .03em; padding: .15rem .5rem; border-radius: 6px; text-transform: uppercase; vertical-align: middle; }
    .wc-client-lidl { background: #0050aa; color: #fff200; }
    .wc-client-sonae { background: #f39200; color: #fff; }
    .wc-type-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .68rem; font-weight: 800; letter-spacing: .03em;
                     padding: .12rem .45rem; border-radius: 6px; text-transform: uppercase; vertical-align: middle;
                     background: var(--bg); color: var(--c); border: 1px solid var(--c); }
    .wc-cod { font-family: ui-monospace, Menlo, monospace; color: #64748b; }

    .wc-dias { display: inline-flex; align-items: center; gap: .4rem; padding: .3rem .7rem; border-radius: 9px;
               font-size: .85rem; font-weight: 700; background: #f1f5f9; color: #475569; white-space: nowrap; }
    .wc-dias.warn { background: #fff7ed; color: #c2410c; }
    .wc-dias.late { background: #fef2f2; color: #b91c1c; }
    .wc-dias.late .fa { animation: wc-pulse 1.4s ease-in-out infinite; }
    @keyframes wc-pulse { 50% { opacity: .3; } }
    @media (prefers-reduced-motion: reduce) { .wc-dias.late .fa { animation: none; } }

    /* A mesma barra de cinco passos da listagem */
    .wc-trilho { display: grid; grid-template-columns: repeat(5, 1fr); gap: .3rem; margin-top: 1rem; }
    .wc-passo .barra { height: .55rem; border-radius: 4px; background: #e2e8f0; }
    .wc-passo.feito .barra { background: var(--c); }
    .wc-passo.agora .barra { background: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 28%, transparent); }
    .wc-passo .nome { display: block; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin-top: .35rem; }
    .wc-passo.feito .nome { color: #64748b; }
    .wc-passo.agora .nome { color: var(--c); font-weight: 700; }
    .wc-passo .quando { display: block; font-size: .7rem; color: #94a3b8; font-weight: 400; }
    .wc-passo.vazio { opacity: .35; }

    /* O que fazer agora */
    .wc-agora { border: 2px solid var(--c); border-radius: 16px; overflow: hidden; }
    .wc-agora > header { background: color-mix(in srgb, var(--c) 10%, #fff); color: var(--c); padding: .6rem .9rem;
                         display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; flex-wrap: wrap; }
    .wc-agora > header strong { font-size: 1rem; }
    .wc-agora > header small { font-weight: 600; opacity: .85; }
    .wc-agora .corpo { padding: .9rem; }
    .wc-fechado { border: 2px solid var(--c); border-radius: 16px; padding: 1.1rem; text-align: center; }

    .wc-form label { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #475569; margin-bottom: .2rem; }
    .wc-outros { display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; margin-top: .75rem; font-size: .82rem; }
    .wc-outros .chip { border: 1px dashed #cbd5e1; border-radius: 999px; padding: .15rem .6rem; color: #475569; background: #fff; }
    .wc-outros .chip:hover { border-color: var(--c); color: var(--c); text-decoration: none; }

    /* Já feito */
    .wc-feito { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
    .wc-feito + .wc-feito { margin-top: .4rem; }
    .wc-feito > summary { display: flex; align-items: center; gap: .55rem; padding: .55rem .7rem; cursor: pointer; list-style: none; }
    .wc-feito > summary::-webkit-details-marker { display: none; }
    .wc-feito[open] > summary { border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .wc-feito .tick { width: 1.3rem; height: 1.3rem; border-radius: 50%; display: grid; place-items: center; flex: none;
                      font-size: .7rem; background: color-mix(in srgb, var(--c) 18%, #fff); color: var(--c); }
    .wc-feito .que { font-weight: 600; font-size: .9rem; color: #1e293b; }
    .wc-feito .quando { margin-left: auto; font-size: .8rem; color: #94a3b8; white-space: nowrap; }
    .wc-feito .corpo { padding: .8rem; }
    .wc-nota { font-size: .82rem; color: #475569; background: #f8fafc; border-left: 3px solid #cbd5e1;
               border-radius: 0 6px 6px 0; padding: .3rem .55rem; margin: .35rem .7rem .55rem 2.5rem; }
    .wc-anexo { font-family: ui-monospace, Menlo, monospace; font-size: .74rem; padding: .1rem .4rem; border-radius: 4px;
                border: 1px solid #e2e8f0; color: #64748b; white-space: nowrap; }
    a.wc-anexo { color: #b91c1c; border-color: #fecaca; }
    a.wc-anexo:hover { background: #fef2f2; color: #991b1b; }

    .wc-desc { white-space: pre-line; color: #1e293b; }
    .wc-obs { background: #fffbeb; border-left: 3px solid #d97706; border-radius: 0 8px 8px 0; padding: .5rem .7rem; font-size: .88rem; }
    .wc-diag { background: #faf9ff; border-left: 3px solid #7c3aed; border-radius: 0 8px 8px 0; padding: .5rem .7rem; font-size: .88rem; }
    .wc-facts { font-size: .88rem; color: #334155; }
    .wc-facts div + div { margin-top: .25rem; }
    .wc-facts dt { display: inline; font-size: .74rem; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; font-weight: 600; }
    .wc-facts dd { display: inline; margin: 0 0 0 .3rem; font-weight: 600; }
    .wc-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(78px, 1fr)); gap: .4rem; }
    .wc-thumb { position: relative; }
    .wc-thumb img { width: 100%; height: 78px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
    .wc-thumb form { position: absolute; top: .15rem; right: .15rem; margin: 0; }
    .wc-thumb button { border: 0; border-radius: 50%; width: 1.3rem; height: 1.3rem; font-size: .65rem; background: rgba(15,23,42,.65); color: #fff; }
    .wc-sibling { display: flex; align-items: center; gap: .5rem; padding: .4rem .55rem; border-radius: 8px; background: #f8fafc; }
    .wc-sibling + .wc-sibling { margin-top: .35rem; }
</style>
@endpush

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between" style="gap: .75rem">
            <div>
                <h4 class="mb-1">
                    <span class="wc-client wc-client-{{ $case->cliente }}">{{ $case->clientLabel() }}</span>
                    <span class="wc-cod">{{ $case->store->codigo_loja ?? '—' }}</span>
                    {{ $case->store->nome_loja ?? '—' }}
                    <span class="wc-type-badge" style="--c: {{ $type['color'] }}; --bg: {{ $type['bg'] }}" title="{{ __($type['ajuda']) }}">
                        <i class="fas {{ $type['icon'] }}"></i> {{ __($type['curto']) }}
                    </span>
                </h4>
                @if($case->isOpen())
                    <span class="wc-dias {{ $alert }}">
                        <i class="fa {{ $alert ? 'fa-exclamation-triangle' : 'fa-clock' }}"></i>
                        {{ trans_choice('{0} hoje|{1} há :count dia|[2,*] há :count dias', $case->daysInPhase(), ['count' => $case->daysInPhase()]) }}
                        <small>· {{ __($case->waitingOnConfig()['label']) }}</small>
                    </span>
                @else
                    <span class="wc-dias" style="background: {{ $phase['bg'] }}; color: {{ $phase['color'] }}">
                        <i class="fas {{ $phase['icon'] }}"></i> {{ __($phase['label']) }} · {{ optional($case->fechado_em)->format('d/m/Y') }}
                    </span>
                @endif
            </div>
            <div class="d-flex flex-wrap" style="gap: .5rem">
                <a href="{{ route('backoffice.warranty.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left"></i> {{ __('Voltar') }}</a>
                <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#wc-edit"><i class="fa fa-edit"></i> {{ __('Editar') }}</button>
                <form method="POST" action="{{ route('backoffice.warranty.destroy', $case->id) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Apagar este processo? As fotografias e os documentos também são apagados.')) }})">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i> {{ __('Apagar') }}</button>
                </form>
            </div>
        </div>

        <div class="wc-trilho">
            @foreach($blocks as $blockKey => $block)
                @php($estado = $case->blockState($blockKey, $next))
                <div class="wc-passo {{ $estado }}" style="--c: {{ $block['color'] }}" title="{{ __($block['label']) }}">
                    <div class="barra"></div>
                    <span class="nome">{{ __($block['curto']) }}
                        <span class="quando">{{ optional($case->blockDate($blockKey))->format('d/m') ?: '—' }}</span>
                    </span>
                </div>
            @endforeach
            @for($i = count($blocks); $i < 5; $i++)
                <div class="wc-passo vazio"><div class="barra"></div><span class="nome">—</span></div>
            @endfor
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-7">
        {{-- Uma coisa a fazer de cada vez --}}
        @if($next)
            @php($blocoDoNext = config('warranty.blocks.' . $next['block']))
            <div class="wc-agora mb-3" style="--c: {{ $blocoDoNext['color'] }}">
                <header>
                    <strong><i class="fas {{ $blocoDoNext['icon'] }}"></i> {{ __('Agora') }}: {{ __($next['todo'] ?? $next['label']) }}</strong>
                    @if($case->daysInPhase() > 0)
                        <small>{{ __($case->waitingOnConfig()['label']) }} {{ __('desde') }} {{ optional($case->fase_desde)->format('d/m') }}</small>
                    @endif
                </header>
                <div class="corpo">
                    @include('backoffice.warranty.partials.step_form', ['step' => $next, 'done' => false])

                    @if($outros)
                        <div class="wc-outros">
                            <span class="text-muted">{{ __('Não é isto?') }}</span>
                            @foreach($outros as $outro)
                                <a href="#wc-p-{{ $outro['block'] }}-{{ $outro['half'] }}" data-toggle="collapse" class="chip"
                                   style="--c: {{ config('warranty.blocks.' . $outro['block'] . '.color') }}">{{ __($outro['label']) }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @foreach($outros as $outro)
                <div class="collapse {{ $errors->any() && old('__step') === $outro['block'] . '.' . $outro['half'] ? 'show' : '' }}"
                     id="wc-p-{{ $outro['block'] }}-{{ $outro['half'] }}">
                    <div class="card mb-3" style="border-left: 4px solid {{ config('warranty.blocks.' . $outro['block'] . '.color') }}">
                        <div class="card-body">
                            <h6 class="font-weight-bold">{{ __($outro['label']) }}</h6>
                            @include('backoffice.warranty.partials.step_form', ['step' => $outro, 'done' => false])
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="wc-fechado mb-3" style="--c: {{ $phase['color'] }}">
                <i class="fas {{ $phase['icon'] }} fa-2x mb-2" style="color: {{ $phase['color'] }}"></i>
                <h5 class="mb-1">{{ __($phase['label']) }}</h5>
                <p class="text-muted small mb-0">{{ __('Processo fechado a :data.', ['data' => optional($case->fechado_em)->format('d/m/Y')]) }}</p>
                @if($case->motivo_recusa)<p class="small mt-2 mb-0"><strong>{{ __('Motivo') }}:</strong> {{ $case->motivo_recusa }}</p>@endif
            </div>
        @endif

        {{-- O que já foi feito, do mais recente para o mais antigo --}}
        <h6 class="text-uppercase text-muted small font-weight-bold mt-4 mb-2">{{ __('Já feito') }}</h6>
        @foreach($feitos as $step)
            @php($bloco = config('warranty.blocks.' . $step['block']))
            @php($evento = $case->events->firstWhere('fase', $step['fase']))
            @php($doc = $step['doc'] ?? null)
            <details class="wc-feito" style="--c: {{ $bloco['color'] }}">
                <summary>
                    <span class="tick"><i class="fa fa-check"></i></span>
                    <span class="que">{{ __($step['label']) }}</span>
                    @if($doc && $case->{$doc . '_path'})
                        <a href="{{ route('backoffice.warranty.document', [$case->id, $doc]) }}" target="_blank" class="wc-anexo">
                            <i class="fa fa-file-pdf"></i> {{ $case->{$doc . '_numero'} ?? __('abrir') }}
                        </a>
                    @elseif($doc && $case->{$doc . '_numero'})
                        <span class="wc-anexo" title="{{ __('sem PDF') }}">{{ $case->{$doc . '_numero'} }}</span>
                    @endif
                    <span class="quando">{{ $case->{$step['date']}->format('d/m/Y') }}</span>
                </summary>
                <div class="corpo">
                    @include('backoffice.warranty.partials.step_form', ['step' => $step, 'done' => true])
                </div>
            </details>
            @if($evento?->nota)<div class="wc-nota">{{ $evento->nota }}</div>@endif
        @endforeach

        @php($registado = $case->events->firstWhere('fase', 'registado'))
        <div class="wc-feito" style="--c: #94a3b8">
            <div class="d-flex align-items-center" style="gap: .55rem; padding: .55rem .7rem">
                <span class="tick"><i class="fa fa-check"></i></span>
                <span class="que">{{ __('Avaria registada') }}</span>
                <span class="quando">{{ optional($case->data_avaria)->format('d/m/Y') }} · {{ $case->creator->name ?? '—' }}</span>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-exclamation-circle text-danger"></i> {{ __('A avaria') }}</h5>
                <p class="wc-desc">{{ $case->descricao }}</p>
                @if($case->observacoes)
                    <div class="wc-obs mb-2"><strong>{{ __('Atenção') }}:</strong> {{ $case->observacoes }}</div>
                @endif
                @if($case->os_texto)
                    <div class="wc-diag mb-2">
                        <strong style="color: {{ config('warranty.blocks.visita.color') }}">
                            {{ __('Diagnóstico do técnico') }}@if($case->os_numero) · {{ $case->os_numero }}@endif
                        </strong><br>{{ $case->os_texto }}
                    </div>
                @endif

                @if($case->photos->isNotEmpty())
                    <div class="wc-gallery mt-3">
                        @foreach($case->photos as $photo)
                            <div class="wc-thumb">
                                <a href="{{ route('backoffice.warranty.photo', $photo->id) }}" target="_blank" title="{{ $photo->nome }}">
                                    <img src="{{ route('backoffice.warranty.photo', $photo->id) }}" alt="{{ $photo->nome }}" loading="lazy">
                                </a>
                                <form method="POST" action="{{ route('backoffice.warranty.photo.delete', $photo->id) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Remover esta fotografia?')) }})">
                                    @csrf
                                    @method('DELETE')
                                    <button title="{{ __('Remover') }}"><i class="fa fa-times"></i></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
                <a href="#wc-fotos" data-toggle="collapse" class="small text-muted d-inline-block mt-2">
                    <i class="fa fa-camera"></i> {{ __('Adicionar fotografias') }}
                </a>
                <div class="collapse" id="wc-fotos">
                    <form method="POST" action="{{ route('backoffice.warranty.photos.store', $case->id) }}" enctype="multipart/form-data" class="mt-2">
                        @csrf
                        @include('backoffice.warranty.partials.photo_input', ['inputId' => 'wc-more-photos'])
                        <button type="submit" class="btn btn-primary btn-sm mt-2"><i class="fa fa-upload"></i> {{ __('Adicionar fotografias') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-link text-info"></i> {{ __('Ligações') }}</h5>
                <dl class="wc-facts mb-0">
                    <div><dt>{{ __('Data da avaria') }}</dt><dd>{{ optional($case->data_avaria)->format('d/m/Y') ?? '—' }}
                        <a href="#" class="ml-1 text-primary" data-toggle="modal" data-target="#wc-edit" data-wc-field="data_avaria" title="{{ __('Alterar data da avaria') }}"><i class="fa fa-pencil-alt"></i></a></dd></div>
                    <div><dt>{{ __('Máquina') }}</dt><dd>{{ $case->machine ? trim($case->machine->serial_number . ' ' . ($case->machine->descricao ? '— ' . $case->machine->descricao : '')) : '—' }}</dd></div>
                    @if($case->technicalRequest)
                        <div><dt>{{ __('Pedido de assistência') }}</dt><dd><a href="{{ route('backoffice.technical_requests.show', $case->technical_request_id) }}">#{{ $case->technical_request_id }} <i class="fa fa-external-link-alt small"></i></a></dd></div>
                    @endif
                    <div><dt>{{ __('Criado por') }}</dt><dd>{{ $case->creator->name ?? '—' }} · {{ $case->created_at->format('d/m/Y') }}</dd></div>
                </dl>

                @if($siblings->isNotEmpty())
                    <hr>
                    <h6 class="text-uppercase text-muted small font-weight-bold">{{ __('Outros processos do mesmo pedido') }}</h6>
                    @foreach($siblings as $sibling)
                        @php($st = $sibling->typeConfig())
                        <div class="wc-sibling">
                            <span class="wc-type-badge" style="--c: {{ $st['color'] }}; --bg: {{ $st['bg'] }}"><i class="fas {{ $st['icon'] }}"></i> {{ __($st['curto']) }}</span>
                            <span class="flex-grow-1 small">#{{ $sibling->id }} · {{ __($sibling->phase()['label']) }}</span>
                            <a href="{{ route('backoffice.warranty.show', $sibling->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-arrow-right"></i></a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Editar dados --}}
<div class="modal fade" id="wc-edit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('backoffice.warranty.update', $case->id) }}" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-edit"></i> {{ __('Editar dados da avaria') }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="small font-weight-bold mb-1">{{ __('Tipo de processo') }}</label>
                    @if($case->canChangeType())
                        <select name="tipo" class="form-control">
                            @foreach($types as $value => $t)
                                <option value="{{ $value }}" @selected($case->tipo === $value)>{{ __($t['label']) }} — {{ __($t['ajuda']) }}</option>
                            @endforeach
                        </select>
                    @else
                        <p class="mb-0"><span class="wc-type-badge" style="--c: {{ $type['color'] }}; --bg: {{ $type['bg'] }}"><i class="fas {{ $type['icon'] }}"></i> {{ __($type['curto']) }}</span>
                            <small class="text-muted ml-2">{{ __('Já não pode ser alterado: o processo passou da visita.') }}</small></p>
                    @endif
                </div>
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label class="small font-weight-bold mb-1">{{ __('Loja') }}</label>
                        <select name="store_id" id="wc-edit-store" class="form-control selectpicker" data-live-search="true" required>
                            @foreach($stores as $s)
                                <option value="{{ $s->id }}" @selected($case->store_id == $s->id)>{{ $s->codigo_loja }} — {{ $s->nome_loja }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold mb-1">{{ __('Cliente') }}</label>
                        <select name="cliente" class="form-control" required>
                            @foreach($clients as $value => $label)
                                <option value="{{ $value }}" @selected($case->cliente === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold mb-1">{{ __('Data da avaria') }}</label>
                        <input type="date" name="data_avaria" class="form-control" value="{{ optional($case->data_avaria)->toDateString() }}" max="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="small font-weight-bold mb-1">{{ __('Máquina') }}</label>
                        <select name="machine_id" id="wc-edit-machine" class="form-control" data-selected="{{ $case->machine_id }}"><option value="">—</option></select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold mb-1">{{ __('Problema') }}</label>
                    <textarea name="descricao" class="form-control" rows="4" required>{{ $case->descricao }}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold mb-1">{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="2">{{ $case->observacoes }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var data = {{ \Illuminate\Support\Js::from($machinesByStore) }};
        var store = document.getElementById('wc-edit-store');
        var machine = document.getElementById('wc-edit-machine');
        var fill = function () {
            var selected = machine.dataset.selected;
            machine.innerHTML = '<option value="">—</option>';
            (data[store.value] || []).forEach(function (m) { machine.add(new Option(m.label, m.id, false, String(m.id) === String(selected))); });
            machine.dataset.selected = '';
        };
        if (window.jQuery) $(store).on('change', fill); else store.addEventListener('change', fill);
        fill();
        if (window.jQuery && $.fn.selectpicker) $('.selectpicker').selectpicker();

        // Email para a Sielaff Alemanha com o nº do artigo e do cliente já escritos
        var mail = document.getElementById('wc-mail');
        if (mail) mail.addEventListener('click', function (e) {
            e.preventDefault();
            var artigo = document.getElementById('wc-artigo').value.trim();
            var cliente = document.getElementById('wc-cliente-num').value.trim();
            var info = {{ \Illuminate\Support\Js::from([
                'email' => $germanyEmail,
                'oc' => $case->oc_numero,
                'loja' => trim(($case->store->codigo_loja ?? '') . ' - ' . ($case->store->nome_loja ?? '') . ' (' . $case->clientLabel() . ')'),
                'serie' => $case->machine->serial_number ?? null,
                'assinatura' => auth()->user()->name,
            ]) }};
            var subject = 'Spare parts order - Customer no. ' + cliente + (info.oc ? ' - PO ' + info.oc : '');
            var body = 'Hello,\n\nPlease process the following spare parts order:\n\n'
                + 'Customer number: ' + cliente + '\n'
                + 'Article number(s): ' + artigo + '\n'
                + (info.oc ? 'Customer purchase order: ' + info.oc + '\n' : '')
                + 'Store: ' + info.loja + '\n'
                + (info.serie ? 'Machine S/N: ' + info.serie + '\n' : '')
                + '\nBest regards,\n' + info.assinatura + '\nSielaff Portugal';
            window.location.href = 'mailto:' + info.email + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
        });

        // Lápis da data da avaria: abre o Editar já com o campo da data selecionado
        if (window.jQuery) $('#wc-edit').on('shown.bs.modal', function (e) {
            var field = $(e.relatedTarget).data('wcField');
            if (field) $(this).find('[name=' + field + ']').trigger('focus');
        });
    });
</script>
@endpush

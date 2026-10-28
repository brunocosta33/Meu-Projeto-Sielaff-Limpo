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
    $todo = $case->todoLabel();
    $machinesByStore = $stores->mapWithKeys(fn ($s) => [$s->id => $s->machines->map(fn ($m) => ['id' => $m->id, 'label' => trim($m->serial_number . ($m->descricao ? ' — ' . $m->descricao : ''))])->values()]);

    // Estado de cada bloco para a tira do topo
    $blockState = function ($blockKey, $block) use ($case, $next) {
        $halves = array_map(fn ($k) => $case->step($blockKey, $k), array_keys($block['halves']));
        $done = array_filter($halves, fn ($h) => $case->stepDone($h));
        if ($blockKey === 'fecho') {
            return count($done) ? 'done' : ($next && $next['block'] === $blockKey ? 'current' : '');
        }
        if (count($done) === count($halves)) {
            return 'done';
        }

        return $next && $next['block'] === $blockKey ? 'current' : (count($done) ? 'partial' : '');
    };
@endphp

@push('styles')
<style>
    .wc-client { display: inline-block; font-size: .72rem; font-weight: 800; letter-spacing: .03em; padding: .15rem .5rem; border-radius: 6px; text-transform: uppercase; vertical-align: middle; }
    .wc-client-lidl { background: #0050aa; color: #fff200; }
    .wc-client-sonae { background: #f39200; color: #fff; }
    .wc-type-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 800; letter-spacing: .03em;
                     padding: .15rem .5rem; border-radius: 6px; text-transform: uppercase; vertical-align: middle;
                     background: var(--bg); color: var(--c); border: 1px solid var(--c); }
    .wc-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .8rem; font-weight: 600; padding: .25rem .65rem;
                border-radius: 999px; background: var(--bg); color: var(--fg); border: 1px solid var(--fg); white-space: nowrap; }
    .wc-wait { display: inline-flex; align-items: center; gap: .45rem; padding: .35rem .8rem; border-radius: 9px; vertical-align: middle;
               font-size: .88rem; font-weight: 700; background: var(--bg); color: var(--fg); border-left: 4px solid var(--fg); }
    .wc-wait small { font-weight: 600; opacity: .8; font-size: .8rem; }
    .wc-wait.warn { --bg: #fff7ed !important; --fg: #c2410c !important; }
    .wc-wait.late { --bg: #fef2f2 !important; --fg: #b91c1c !important; }
    .wc-wait.late .fa { animation: wc-pulse 1.4s ease-in-out infinite; }
    @keyframes wc-pulse { 50% { opacity: .3; } }
    @media (prefers-reduced-motion: reduce) { .wc-wait.late .fa { animation: none; } }

    /* Tira do topo: um ponto por bloco */
    .wc-steps { display: flex; gap: .25rem; overflow-x: auto; padding-bottom: .25rem; }
    .wc-step { flex: 1 1 0; min-width: 120px; text-align: center; position: relative; padding-top: 2.6rem; font-size: .8rem; color: #94a3b8; }
    .wc-step::before { content: ""; position: absolute; top: 1.05rem; left: -50%; right: 50%; height: 3px; background: #e2e8f0; }
    .wc-step:first-child::before { display: none; }
    .wc-step .dot { position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 2.2rem; height: 2.2rem; border-radius: 50%;
                    display: grid; place-items: center; background: #fff; border: 3px solid #e2e8f0; color: #94a3b8; z-index: 1; }
    .wc-step.done, .wc-step.partial { color: #334155; }
    .wc-step.done::before, .wc-step.current::before, .wc-step.partial::before { background: var(--c); }
    .wc-step.done .dot { background: var(--c); border-color: var(--c); color: #fff; }
    .wc-step.partial .dot { border-color: var(--c); color: var(--c); }
    .wc-step.current { color: #0f172a; font-weight: 700; }
    .wc-step.current .dot { border-color: var(--c); color: var(--c); box-shadow: 0 0 0 5px color-mix(in srgb, var(--c) 18%, transparent); }

    /* Blocos */
    .wc-block { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: .75rem; overflow: hidden; background: #fff; }
    .wc-block.is-current { border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 12%, transparent); }
    .wc-block > header { display: flex; align-items: center; gap: .5rem; padding: .55rem .85rem; background: var(--bg); color: var(--c); font-weight: 700; font-size: .92rem; }
    .wc-block > header .num { width: 1.5rem; height: 1.5rem; border-radius: 50%; background: var(--c); color: #fff; display: grid; place-items: center; font-size: .78rem; }
    .wc-half { padding: .6rem .85rem; }
    .wc-half + .wc-half { border-top: 1px dashed #e2e8f0; }
    .wc-half-head { display: flex; align-items: center; gap: .5rem; }
    .wc-half-head form { margin: 0; }
    .wc-tick { width: 1.4rem; height: 1.4rem; border-radius: 50%; display: grid; place-items: center; font-size: .62rem; background: #f1f5f9; color: #cbd5e1; flex: none; }
    .wc-half.done .wc-tick { background: #dcfce7; color: #15803d; }
    .wc-half.next .wc-tick { background: #fef3c7; color: #b45309; }
    .wc-half .wc-when { color: #64748b; font-size: .82rem; margin-left: .4rem; }
    .wc-half-body { padding: .35rem 0 0 1.9rem; font-size: .86rem; color: #334155; }
    .wc-half-form { padding: .6rem 0 .2rem 1.9rem; }
    .wc-half-form label { font-size: .76rem; font-weight: 700; margin-bottom: .15rem; color: #475569; }
    .wc-note { margin-top: .35rem; padding: .35rem .6rem; background: #f8fafc; border-left: 3px solid #cbd5e1; border-radius: 0 6px 6px 0; font-size: .82rem; color: #475569; }

    .wc-desc { white-space: pre-line; color: #1e293b; }
    .wc-diag { background: #faf9ff; border-left: 3px solid #7c3aed; border-radius: 8px; padding: .6rem .8rem; }
    .wc-facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: .6rem; }
    .wc-facts div { background: #f8fafc; border-radius: 10px; padding: .45rem .65rem; }
    .wc-facts dt { font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin: 0; }
    .wc-facts dd { margin: 0; font-weight: 600; color: #334155; font-size: .88rem; }
    .wc-sibling { display: flex; align-items: center; gap: .5rem; padding: .5rem .65rem; border-radius: 10px; background: #f8fafc; }
    .wc-sibling + .wc-sibling { margin-top: .4rem; }
    .wc-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(92px, 1fr)); gap: .5rem; }
    .wc-thumb { position: relative; }
    .wc-thumb img { width: 100%; height: 92px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; }
    .wc-thumb form { position: absolute; top: .2rem; right: .2rem; margin: 0; }
    .wc-thumb button { border: 0; border-radius: 50%; width: 1.4rem; height: 1.4rem; font-size: .7rem; background: rgba(15,23,42,.65); color: #fff; }
    .wc-timeline { list-style: none; margin: 0; padding: 0 0 0 1rem; }
    .wc-timeline li { position: relative; padding: 0 0 .9rem 1rem; border-left: 2px solid #e2e8f0; }
    .wc-timeline li:last-child { border-left-color: transparent; padding-bottom: 0; }
    .wc-timeline .tl-dot { position: absolute; left: -.43rem; top: .15rem; width: .8rem; height: .8rem; border-radius: 50%; background: #fff; border: 3px solid var(--c); }
    .wc-timeline .tl-title { font-weight: 600; font-size: .88rem; color: #1e293b; }
    .wc-timeline .tl-meta { font-size: .76rem; color: #64748b; }
    .wc-timeline .tl-note { font-size: .8rem; color: #475569; background: #f8fafc; border-radius: 6px; padding: .25rem .5rem; margin-top: .2rem; }
</style>
@endpush

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between mb-3" style="gap: .75rem">
            <div>
                <h4 class="mb-2">
                    <span class="wc-client wc-client-{{ $case->cliente }}">{{ $case->clientLabel() }}</span>
                    <span class="wc-type-badge" style="--c: {{ $type['color'] }}; --bg: {{ $type['bg'] }}" title="{{ __($type['ajuda']) }}"><i class="fas {{ $type['icon'] }}"></i> {{ __($type['label']) }}</span>
                    {{ $case->store->codigo_loja ?? '—' }} — {{ $case->store->nome_loja ?? '—' }}
                </h4>
                <span class="wc-badge" style="--bg: {{ $phase['bg'] }}; --fg: {{ $phase['color'] }}"><i class="fas {{ $phase['icon'] }}"></i> {{ __($phase['label']) }}</span>
                @if($todo)
                    <span class="wc-wait ml-2 {{ $alert }}" style="--bg: {{ $phase['bg'] }}; --fg: {{ $phase['color'] }}">
                        <i class="fa {{ $alert ? 'fa-exclamation-triangle' : 'fa-hourglass-half' }}"></i>
                        {{ $todo }}
                        <small>· {{ trans_choice('{0} hoje|{1} há :count dia|[2,*] há :count dias', $case->daysInPhase(), ['count' => $case->daysInPhase()]) }}</small>
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

        <div class="wc-steps">
            @foreach($blocks as $blockKey => $block)
                @php($state = $blockState($blockKey, $block))
                <div class="wc-step {{ $state }}" style="--c: {{ $block['color'] }}">
                    <span class="dot"><i class="fas {{ $state === 'done' ? 'fa-check' : $block['icon'] }}"></i></span>
                    {{ __($block['label']) }}
                </div>
            @endforeach
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
        @php($n = 0)
        @foreach($blocks as $blockKey => $block)
            @php($n++)
            <div class="wc-block {{ $next && $next['block'] === $blockKey ? 'is-current' : '' }}" style="--c: {{ $block['color'] }}; --bg: {{ $block['bg'] }}">
                <header><span class="num">{{ $n }}</span> <i class="fas {{ $block['icon'] }}"></i> {{ __($block['label']) }}</header>
                @foreach($block['halves'] as $halfKey => $half)
                    @php($step = $case->step($blockKey, $halfKey))
                    {{-- As duas metades do fecho são alternativas: a recusa esconde-se depois de o processo fechar --}}
                    @continue($blockKey === 'fecho' && $halfKey === 'recusado' && !$case->stepDone($step) && !$case->isOpen())
                    @include('backoffice.warranty.partials.step', [
                        'blockKey' => $blockKey,
                        'block' => $block,
                        'halfKey' => $halfKey,
                        'step' => $step,
                        'isNext' => $next && $next['block'] === $blockKey && $next['half'] === $halfKey,
                    ])
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="col-lg-5">
        {{-- Problema --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-exclamation-circle text-danger"></i> {{ __('Problema') }}</h5>
                <p class="wc-desc">{{ $case->descricao }}</p>
                @if($case->observacoes)
                    <div class="alert alert-warning py-2 small mb-3"><strong>{{ __('Observações') }}:</strong> {{ $case->observacoes }}</div>
                @endif
                @if($case->os_texto)
                    <div class="wc-diag mb-3">
                        <div class="small font-weight-bold text-uppercase mb-1" style="color: {{ config('warranty.blocks.visita.color') }}">
                            <i class="fa fa-clipboard-check"></i> {{ __('Diagnóstico do técnico') }}
                            @if($case->os_numero)<span class="text-muted">· {{ __('OS') }} {{ $case->os_numero }}</span>@endif
                        </div>
                        <p class="wc-desc mb-0">{{ $case->os_texto }}</p>
                    </div>
                @endif
                <dl class="wc-facts mb-0">
                    <div><dt>{{ __('Data da avaria') }}</dt><dd>{{ optional($case->data_avaria)->format('d/m/Y') ?? '—' }}
                        <a href="#" class="ml-1 text-primary" data-toggle="modal" data-target="#wc-edit" data-wc-field="data_avaria" title="{{ __('Alterar data da avaria') }}"><i class="fa fa-pencil-alt"></i></a></dd></div>
                    <div><dt>{{ __('Máquina') }}</dt><dd>{{ $case->machine ? trim($case->machine->serial_number . ' ' . ($case->machine->descricao ? '— ' . $case->machine->descricao : '')) : '—' }}</dd></div>
                    <div><dt>{{ __('Criado por') }}</dt><dd>{{ $case->creator->name ?? '—' }} · {{ $case->created_at->format('d/m/Y') }}</dd></div>
                    @if($case->technicalRequest)
                        <div><dt>{{ __('Pedido de assistência') }}</dt><dd><a href="{{ route('backoffice.technical_requests.show', $case->technical_request_id) }}">#{{ $case->technical_request_id }} <i class="fa fa-external-link-alt small"></i></a></dd></div>
                    @endif
                </dl>
            </div>
        </div>

        @if($siblings->isNotEmpty())
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-code-branch text-info"></i> {{ __('Outros processos do mesmo pedido') }}</h5>
                    @foreach($siblings as $sibling)
                        @php($st = $sibling->typeConfig())
                        <div class="wc-sibling">
                            <span class="wc-type-badge" style="--c: {{ $st['color'] }}; --bg: {{ $st['bg'] }}"><i class="fas {{ $st['icon'] }}"></i> {{ __($st['label']) }}</span>
                            <span class="flex-grow-1 small">#{{ $sibling->id }} · {{ __($sibling->phase()['label']) }}</span>
                            <a href="{{ route('backoffice.warranty.show', $sibling->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-arrow-right"></i></a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Fotografias --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-camera text-primary"></i> {{ __('Fotografias') }} <span class="text-muted small">({{ $case->photos->count() }})</span></h5>
                @if($case->photos->isNotEmpty())
                    <div class="wc-gallery mb-3">
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
                @else
                    <p class="text-muted small">{{ __('Ainda sem fotografias.') }}</p>
                @endif
                <form method="POST" action="{{ route('backoffice.warranty.photos.store', $case->id) }}" enctype="multipart/form-data">
                    @csrf
                    @include('backoffice.warranty.partials.photo_input', ['inputId' => 'wc-more-photos'])
                    <button type="submit" class="btn btn-primary btn-sm mt-2"><i class="fa fa-upload"></i> {{ __('Adicionar fotografias') }}</button>
                </form>
            </div>
        </div>

        {{-- Histórico --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-history text-secondary"></i> {{ __('Histórico') }}</h5>
                <ul class="wc-timeline">
                    @foreach($case->events as $event)
                        @php($ep = config('warranty.phases.' . $event->fase, []))
                        <li style="--c: {{ $ep['color'] ?? '#94a3b8' }}">
                            <span class="tl-dot"></span>
                            <div class="tl-title">{{ __($ep['label'] ?? $event->fase) }}</div>
                            <div class="tl-meta">{{ $event->data->format('d/m/Y') }} · {{ $event->user->name ?? '—' }}</div>
                            @if($event->nota)<div class="tl-note">{{ $event->nota }}</div>@endif
                        </li>
                    @endforeach
                </ul>
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
                        <p class="mb-0"><span class="wc-type-badge" style="--c: {{ $type['color'] }}; --bg: {{ $type['bg'] }}"><i class="fas {{ $type['icon'] }}"></i> {{ __($type['label']) }}</span>
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

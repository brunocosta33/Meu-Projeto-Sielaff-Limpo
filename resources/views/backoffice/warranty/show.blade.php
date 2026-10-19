@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Fora da Garantia') }} #{{ $case->id }}</title>
@endsection

@php
    \Carbon\Carbon::setLocale(app()->getLocale());
    $phase = $case->phase();
    $alert = $case->waitingAlert();
    $reached = $case->events->keyBy('fase');
    $steps = ['registado', 'cotacao_pedida', 'cotacao_recebida', 'enviada_cliente', 'oc_recebida', 'pedido_alemanha', 'peca_recebida', $case->fase === 'recusado' ? 'recusado' : 'concluido'];
    $currentIndex = array_search($case->fase, $steps, true);
    $nextForms = [
        'cotacao_pedida' => ['title' => __('Pedi a cotação à Sielaff'), 'help' => __('Registe o dia em que pediu a cotação à Sielaff.'), 'button' => __('Registar pedido de cotação')],
        'cotacao_recebida' => ['title' => __('Recebi a cotação da Sielaff'), 'help' => __('Indique o número e anexe o PDF da cotação.'), 'button' => __('Registar cotação')],
        'enviada_cliente' => ['title' => __('Enviei a cotação ao cliente'), 'help' => __('Registe quando enviou a cotação ao :cliente.', ['cliente' => $case->clientLabel()]), 'button' => __('Registar envio')],
        'oc_recebida' => ['title' => __('Recebi a ordem de compra'), 'help' => __('Indique o número e anexe o PDF da ordem de compra do :cliente.', ['cliente' => $case->clientLabel()]), 'button' => __('Registar ordem de compra')],
        'pedido_alemanha' => ['title' => __('Enviei o pedido para a Alemanha'), 'help' => __('Envie um email para :email com o número do artigo e o número do cliente e registe o envio.', ['email' => $germanyEmail]), 'button' => __('Registar envio para a Alemanha')],
        'peca_recebida' => ['title' => __('Recebi a peça'), 'help' => __('Registe o dia em que a peça chegou. Depois é só enviar o técnico fazer a reparação.'), 'button' => __('Registar peça recebida')],
        'concluido' => ['title' => __('Reparação feita'), 'help' => __('Fecha o processo.'), 'button' => __('Fechar processo')],
    ];
    $machinesByStore = $stores->mapWithKeys(fn ($s) => [$s->id => $s->machines->map(fn ($m) => ['id' => $m->id, 'label' => trim($m->serial_number . ($m->descricao ? ' — ' . $m->descricao : ''))])->values()]);
@endphp

@push('styles')
<style>
    .wc-client { display: inline-block; font-size: .72rem; font-weight: 800; letter-spacing: .03em; padding: .15rem .5rem; border-radius: 6px; text-transform: uppercase; vertical-align: middle; }
    .wc-client-lidl { background: #0050aa; color: #fff200; }
    .wc-client-sonae { background: #f39200; color: #fff; }
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
    .wc-steps { display: flex; gap: .25rem; overflow-x: auto; padding-bottom: .25rem; }
    .wc-step { flex: 1 1 0; min-width: 105px; text-align: center; position: relative; padding-top: 2.6rem; font-size: .78rem; color: #94a3b8; }
    .wc-step::before { content: ""; position: absolute; top: 1.05rem; left: -50%; right: 50%; height: 3px; background: #e2e8f0; }
    .wc-step:first-child::before { display: none; }
    .wc-step .dot { position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 2.2rem; height: 2.2rem; border-radius: 50%;
                    display: grid; place-items: center; background: #fff; border: 3px solid #e2e8f0; color: #94a3b8; z-index: 1; }
    .wc-step.done { color: #334155; }
    .wc-step.done::before, .wc-step.current::before { background: var(--c); }
    .wc-step.done .dot { background: var(--c); border-color: var(--c); color: #fff; }
    .wc-step.current { color: #0f172a; font-weight: 700; }
    .wc-step.current .dot { border-color: var(--c); color: var(--c); box-shadow: 0 0 0 5px color-mix(in srgb, var(--c) 18%, transparent); }
    .wc-step .when { display: block; font-weight: 400; color: #64748b; font-size: .72rem; }

    .wc-next { border: 2px solid var(--c); border-radius: 16px; }
    .wc-next .card-header { background: color-mix(in srgb, var(--c) 10%, #fff); border-bottom: 0; border-radius: 14px 14px 0 0; font-weight: 700; color: var(--c); }
    .wc-desc { white-space: pre-line; color: #1e293b; }
    .wc-facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: .6rem; }
    .wc-facts div { background: #f8fafc; border-radius: 10px; padding: .45rem .65rem; }
    .wc-facts dt { font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin: 0; }
    .wc-facts dd { margin: 0; font-weight: 600; color: #334155; font-size: .88rem; }

    .wc-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: .6rem; }
    .wc-thumb { position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; background: #f1f5f9; }
    .wc-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .2s; }
    .wc-thumb:hover img { transform: scale(1.05); }
    .wc-thumb form { position: absolute; top: 4px; right: 4px; }
    .wc-thumb button { width: 26px; height: 26px; border-radius: 50%; border: 0; background: rgba(15, 23, 42, .65); color: #fff; font-size: .75rem; line-height: 1; }

    .wc-doc { display: flex; align-items: center; gap: .75rem; padding: .7rem .8rem; border-radius: 12px; background: #f8fafc; }
    .wc-doc + .wc-doc { margin-top: .5rem; }
    .wc-doc .fa-2x { color: #dc2626; }
    .wc-doc small { display: block; color: #64748b; }

    .wc-timeline { list-style: none; margin: 0; padding: 0; }
    .wc-timeline li { position: relative; padding: 0 0 .9rem 1.6rem; }
    .wc-timeline li::before { content: ""; position: absolute; left: .42rem; top: 1rem; bottom: 0; width: 2px; background: #e2e8f0; }
    .wc-timeline li:last-child::before { display: none; }
    .wc-timeline .tl-dot { position: absolute; left: 0; top: .2rem; width: .95rem; height: .95rem; border-radius: 50%; background: var(--c); }
    .wc-timeline .tl-title { font-weight: 600; font-size: .88rem; }
    .wc-timeline .tl-meta { font-size: .76rem; color: #64748b; }
    .wc-timeline .tl-note { font-size: .82rem; color: #334155; white-space: pre-line; margin-top: .15rem; }
</style>
@endpush

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

{{-- Cabeçalho --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between mb-4" style="gap: .75rem">
            <div>
                <div class="small text-muted mb-1">{{ __('Fora da Garantia') }} · #{{ $case->id }}</div>
                <h4 class="mb-2">
                    <span class="wc-client wc-client-{{ $case->cliente }}">{{ $case->clientLabel() }}</span>
                    {{ $case->store->codigo_loja ?? '—' }} — {{ $case->store->nome_loja ?? '—' }}
                </h4>
                <span class="wc-badge" style="--bg: {{ $phase['bg'] }}; --fg: {{ $phase['color'] }}"><i class="fas {{ $phase['icon'] }}"></i> {{ __($phase['label']) }}</span>
                @if($case->isOpen() && $phase['waiting'])
                    <span class="wc-wait ml-2 {{ $alert }}" style="--bg: {{ $phase['bg'] }}; --fg: {{ $phase['color'] }}">
                        <i class="fa {{ $alert ? 'fa-exclamation-triangle' : 'fa-hourglass-half' }}"></i>
                        {{ __($phase['waiting']) }}
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
            @foreach($steps as $i => $step)
                @php($p = $phases[$step])
                <div class="wc-step {{ $i < $currentIndex || in_array($case->fase, ['concluido', 'recusado'], true) ? 'done' : ($i === $currentIndex ? 'current' : '') }}" style="--c: {{ $p['color'] }}">
                    <span class="dot"><i class="fas {{ $i < $currentIndex || !$case->isOpen() ? ($step === 'recusado' ? 'fa-times' : 'fa-check') : $p['icon'] }}"></i></span>
                    {{ __($p['label']) }}
                    @if(isset($reached[$step]))<span class="when">{{ $reached[$step]->data->format('d/m/Y') }}</span>@endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        {{-- Problema --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-exclamation-circle text-danger"></i> {{ __('Problema') }}</h5>
                <p class="wc-desc">{{ $case->descricao }}</p>
                @if($case->observacoes)
                    <div class="alert alert-warning py-2 small mb-3"><strong>{{ __('Observações') }}:</strong> {{ $case->observacoes }}</div>
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
    </div>

    <div class="col-lg-5">
        {{-- Próximo passo --}}
        @if($next)
            @php($nf = $nextForms[$next])
            <div class="card wc-next mb-3" style="--c: {{ $phases[$next]['color'] }}">
                <div class="card-header"><i class="fas {{ $phases[$next]['icon'] }}"></i> {{ __('Próximo passo') }}: {{ $nf['title'] }}</div>
                <div class="card-body">
                    <p class="small text-muted">{{ $nf['help'] }}</p>
                    <form method="POST" action="{{ route('backoffice.warranty.advance', $case->id) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="fase" value="{{ $next }}">
                        @if($next === 'cotacao_recebida' || $next === 'oc_recebida')
                            @php($numberField = $next === 'cotacao_recebida' ? 'cotacao_numero' : 'oc_numero')
                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">{{ $next === 'cotacao_recebida' ? __('Nº da cotação') : __('Nº da ordem de compra') }}</label>
                                <input type="text" name="{{ $numberField }}" class="form-control" value="{{ old($numberField) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">{{ $next === 'cotacao_recebida' ? __('PDF da cotação') : __('PDF da ordem de compra') }}</label>
                                <input type="file" name="documento" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                        @endif
                        @if($next === 'pedido_alemanha')
                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">{{ __('Nº do artigo') }}</label>
                                <input type="text" name="artigo_numero" id="wc-artigo" class="form-control" value="{{ old('artigo_numero') }}" required placeholder="{{ __('Se forem vários, separe por vírgulas') }}">
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">{{ __('Nº do cliente') }}</label>
                                <input type="text" name="cliente_numero" id="wc-cliente-num" class="form-control" value="{{ old('cliente_numero', $suggestedClientNumber) }}" required>
                                @if($suggestedClientNumber)<small class="form-text text-muted">{{ __('Sugerido: o último usado para o :cliente.', ['cliente' => $case->clientLabel()]) }}</small>@endif
                            </div>
                            <a href="#" class="btn btn-outline-dark btn-block mb-3" id="wc-mail"><i class="fa fa-envelope"></i> {{ __('Abrir email para :email', ['email' => $germanyEmail]) }}</a>
                        @endif
                        @if($next === 'enviada_cliente')
                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">{{ __('Enviada para') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                                <input type="text" name="enviada_para" class="form-control" value="{{ old('enviada_para') }}" placeholder="{{ __('Ex.: nome ou email do contacto no cliente') }}">
                            </div>
                        @endif
                        <div class="form-row">
                            <div class="form-group col-sm-6">
                                <label class="small font-weight-bold mb-1">{{ __('Data') }}</label>
                                <input type="date" name="data" class="form-control" value="{{ old('data', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold mb-1">{{ __('Nota') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                            <textarea name="nota" class="form-control" rows="2">{{ old('nota') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-block text-white" style="background: {{ $phases[$next]['color'] }}"><i class="fa fa-check"></i> {{ $nf['button'] }}</button>
                    </form>

                    @if(in_array($case->fase, ['cotacao_recebida', 'enviada_cliente'], true))
                        <hr>
                        <a href="#wc-refuse" data-toggle="collapse" class="small text-danger"><i class="fa fa-times-circle"></i> {{ __('O cliente recusou a cotação?') }}</a>
                        <div class="collapse {{ $errors->has('motivo_recusa') ? 'show' : '' }}" id="wc-refuse">
                            <form method="POST" action="{{ route('backoffice.warranty.refuse', $case->id) }}" class="mt-2">
                                @csrf
                                <div class="form-group">
                                    <textarea name="motivo_recusa" class="form-control" rows="2" placeholder="{{ __('Motivo da recusa') }}" required>{{ old('motivo_recusa') }}</textarea>
                                </div>
                                <div class="d-flex" style="gap: .5rem">
                                    <input type="date" name="data" class="form-control" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required style="max-width: 170px">
                                    <button class="btn btn-outline-danger"><i class="fa fa-times"></i> {{ __('Fechar como recusado') }}</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card mb-3" style="border: 2px solid {{ $phase['color'] }}; border-radius: 16px">
                <div class="card-body text-center">
                    <i class="fas {{ $phase['icon'] }} fa-2x mb-2" style="color: {{ $phase['color'] }}"></i>
                    <h5 class="mb-1">{{ __($phase['label']) }}</h5>
                    <p class="text-muted small mb-0">{{ __('Processo fechado a :data.', ['data' => optional($case->fechado_em)->format('d/m/Y')]) }}</p>
                    @if($case->motivo_recusa)<p class="small mt-2 mb-0"><strong>{{ __('Motivo') }}:</strong> {{ $case->motivo_recusa }}</p>@endif
                </div>
            </div>
        @endif

        {{-- Documentos --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-folder-open text-warning"></i> {{ __('Documentos') }}</h5>
                @php($docs = ['cotacao' => [__('Cotação Sielaff'), $case->cotacao_numero, $case->cotacao_recebida_em, $case->cotacao_path], 'oc' => [__('Ordem de compra'), $case->oc_numero, $case->oc_recebida_em, $case->oc_path]])
                @php($anyDoc = false)
                @foreach($docs as $tipo => [$label, $numero, $data, $path])
                    @continue(!$path)
                    @php($anyDoc = true)
                    <div class="wc-doc">
                        <i class="fa fa-file-pdf fa-2x"></i>
                        <div class="flex-grow-1">
                            <strong>{{ $label }} {{ $numero }}</strong>
                            <small>{{ __('Recebida a') }} {{ optional($data)->format('d/m/Y') }}</small>
                        </div>
                        <a href="{{ route('backoffice.warranty.document', [$case->id, $tipo]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i> {{ __('Abrir') }}</a>
                        <button type="button" class="btn btn-sm btn-link text-secondary px-1" data-toggle="collapse" data-target="#wc-replace-{{ $tipo }}" title="{{ __('Substituir') }}"><i class="fa fa-sync-alt"></i></button>
                    </div>
                    <div class="collapse" id="wc-replace-{{ $tipo }}">
                        <form method="POST" action="{{ route('backoffice.warranty.document.replace', [$case->id, $tipo]) }}" enctype="multipart/form-data" class="p-2">
                            @csrf
                            <input type="text" name="numero" class="form-control form-control-sm mb-2" value="{{ $numero }}" required>
                            <input type="file" name="documento" class="form-control-file mb-2" accept=".pdf,.jpg,.jpeg,.png" required>
                            <button class="btn btn-sm btn-primary"><i class="fa fa-upload"></i> {{ __('Substituir documento') }}</button>
                        </form>
                    </div>
                @endforeach
                @unless($anyDoc)
                    <p class="text-muted small mb-0">{{ __('A cotação e a ordem de compra aparecem aqui quando forem registadas.') }}</p>
                @endunless
                @if($case->alemanha_enviado_em)
                    <p class="small text-muted mt-2 mb-0"><i class="fa fa-globe-europe"></i> {{ __('Pedido enviado para a Alemanha a') }} {{ $case->alemanha_enviado_em->format('d/m/Y') }} — {{ __('Artigo') }} {{ $case->artigo_numero }} · {{ __('Cliente') }} {{ $case->cliente_numero }}</p>
                @endif
                @if($case->peca_recebida_em)
                    <p class="small text-muted mt-2 mb-0"><i class="fa fa-box-open"></i> {{ __('Peça recebida a') }} {{ $case->peca_recebida_em->format('d/m/Y') }}</p>
                @endif
                @if($case->enviada_cliente_em)
                    <p class="small text-muted mt-2 mb-0"><i class="fa fa-paper-plane"></i> {{ __('Enviada ao cliente a') }} {{ $case->enviada_cliente_em->format('d/m/Y') }}{{ $case->enviada_para ? ' — ' . $case->enviada_para : '' }}</p>
                @endif
            </div>
        </div>

        {{-- Histórico --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-history text-secondary"></i> {{ __('Histórico') }}</h5>
                <ul class="wc-timeline">
                    @foreach($case->events as $event)
                        <li style="--c: {{ $phases[$event->fase]['color'] ?? '#94a3b8' }}">
                            <span class="tl-dot"></span>
                            <div class="tl-title">
                                {{ __($phases[$event->fase]['label'] ?? $event->fase) }}
                                <a href="#" class="ml-1 text-primary small" data-toggle="modal" data-target="#wc-step-{{ $event->id }}" title="{{ __('Editar passo') }}"><i class="fa fa-pencil-alt"></i></a>
                            </div>
                            <div class="tl-meta">{{ $event->data->format('d/m/Y') }} · {{ $event->user->name ?? '—' }}</div>
                            @if($event->fase === 'pedido_alemanha')<div class="tl-meta">{{ __('Artigo') }} {{ $case->artigo_numero }} · {{ __('Cliente') }} {{ $case->cliente_numero }}</div>@endif
                            @if($event->nota)<div class="tl-note">{{ $event->nota }}</div>@endif
                        </li>
                    @endforeach
                </ul>
                @if($case->events->count() > 1)
                    <form method="POST" action="{{ route('backoffice.warranty.undo', $case->id) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Desfazer o último passo? Os dados e o documento desse passo são apagados.')) }})">
                        @csrf
                        <button class="btn btn-sm btn-link text-secondary p-0"><i class="fa fa-undo"></i> {{ __('Desfazer último passo') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Editar cada passo do histórico --}}
@foreach($case->events as $event)
    <div class="modal fade" id="wc-step-{{ $event->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('backoffice.warranty.step.update', [$case->id, $event->id]) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-pencil-alt"></i> {{ __($phases[$event->fase]['label'] ?? $event->fase) }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold mb-1">{{ $event->fase === 'registado' ? __('Data da avaria') : __('Data') }}</label>
                        <input type="date" name="data" class="form-control" value="{{ $event->data->toDateString() }}" max="{{ now()->toDateString() }}" required>
                    </div>
                    @if($event->fase === 'cotacao_recebida')
                        <div class="form-group">
                            <label class="small font-weight-bold mb-1">{{ __('Nº da cotação') }}</label>
                            <input type="text" name="cotacao_numero" class="form-control" value="{{ $case->cotacao_numero }}" required>
                        </div>
                    @elseif($event->fase === 'enviada_cliente')
                        <div class="form-group">
                            <label class="small font-weight-bold mb-1">{{ __('Enviada para') }}</label>
                            <input type="text" name="enviada_para" class="form-control" value="{{ $case->enviada_para }}">
                        </div>
                    @elseif($event->fase === 'oc_recebida')
                        <div class="form-group">
                            <label class="small font-weight-bold mb-1">{{ __('Nº da ordem de compra') }}</label>
                            <input type="text" name="oc_numero" class="form-control" value="{{ $case->oc_numero }}" required>
                        </div>
                    @elseif($event->fase === 'pedido_alemanha')
                        <div class="form-group">
                            <label class="small font-weight-bold mb-1">{{ __('Nº do artigo') }}</label>
                            <input type="text" name="artigo_numero" class="form-control" value="{{ $case->artigo_numero }}" required>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold mb-1">{{ __('Nº do cliente') }}</label>
                            <input type="text" name="cliente_numero" class="form-control" value="{{ $case->cliente_numero }}" required>
                        </div>
                    @endif
                    @if($event->fase === 'recusado')
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold mb-1">{{ __('Motivo da recusa') }}</label>
                            <textarea name="motivo_recusa" class="form-control" rows="2" required>{{ $case->motivo_recusa }}</textarea>
                        </div>
                    @else
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold mb-1">{{ __('Nota') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                            <textarea name="nota" class="form-control" rows="2">{{ $event->nota }}</textarea>
                        </div>
                    @endif
                    @if(in_array($event->fase, ['cotacao_recebida', 'oc_recebida'], true))
                        <small class="form-text text-muted mt-2"><i class="fa fa-info-circle"></i> {{ __('Para trocar o PDF use o botão de substituir no cartão Documentos.') }}</small>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

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

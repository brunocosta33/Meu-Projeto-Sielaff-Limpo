{{--
    Uma metade de um bloco. Preenchida mostra o resumo; por preencher mostra o formulário.
    O formulário é o mesmo para registar e para corrigir.
    Espera: $case, $blockKey, $block, $halfKey, $step, $isNext
--}}
@php
    $id = $blockKey . '-' . $halfKey;
    $done = $case->stepDone($step);
    $stepId = $blockKey . '.' . $halfKey;
    $hasErrors = $errors->any() && old('__step') === $stepId;
    $open = $hasErrors || (!$done && $isNext);
    $doc = $step['doc'] ?? null;
    $docPath = $doc ? $case->{$doc . '_path'} : null;
    $val = fn ($field, $default = null) => $hasErrors ? old($field) : ($default ?? $case->$field);
@endphp

<div class="wc-half {{ $done ? 'done' : ($isNext ? 'next' : '') }}">
    <div class="wc-half-head">
        <span class="wc-tick"><i class="fas {{ $done ? 'fa-check' : ($isNext ? 'fa-arrow-right' : 'fa-circle') }}"></i></span>
        <div class="flex-grow-1">
            <strong>{{ __($step['label']) }}</strong>
            @if($done)
                <span class="wc-when">{{ $case->{$step['date']}->format('d/m/Y') }}</span>
            @elseif($isNext)
                <span class="badge badge-warning ml-1">{{ __('a fazer') }}</span>
            @endif
        </div>
        @if($done)
            <a href="#wc-f-{{ $id }}" data-toggle="collapse" class="btn btn-sm btn-link text-secondary p-1" title="{{ __('Corrigir') }}"><i class="fa fa-pencil-alt"></i></a>
            <form method="POST" action="{{ route('backoffice.warranty.step.clear', [$case->id, $blockKey, $halfKey]) }}"
                  onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Apagar este passo? Os dados e o documento deste passo são apagados.')) }})">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-link text-danger p-1" title="{{ __('Apagar passo') }}"><i class="fa fa-times"></i></button>
            </form>
        @elseif(!$isNext)
            <a href="#wc-f-{{ $id }}" data-toggle="collapse" class="btn btn-sm btn-link text-secondary p-1">{{ __('registar') }}</a>
        @endif
    </div>

    @if($done)
        <div class="wc-half-body">
            @if($blockKey === 'visita')
                <div><strong>{{ __('OS') }} {{ $case->os_numero }}</strong></div>
                <p class="wc-desc small mb-1">{{ $case->os_texto }}</p>
            @elseif($stepId === 'sielaff.recebida' || ($stepId === 'cliente.enviada' && !$case->needsParts()))
                <div>{{ __('Nº da cotação') }}: <strong>{{ $case->cotacao_numero }}</strong></div>
            @elseif($stepId === 'cliente.oc')
                <div>{{ __('Nº da ordem de compra') }}: <strong>{{ $case->oc_numero }}</strong></div>
            @elseif($stepId === 'alemanha.pedida')
                <div>{{ __('Artigo') }}: <strong>{{ $case->artigo_numero }}</strong> · {{ __('Cliente') }}: <strong>{{ $case->cliente_numero }}</strong></div>
            @elseif($stepId === 'alemanha.recebida')
                <div>{{ $case->partOriginLabel() }}</div>
            @elseif($stepId === 'fecho.recusado')
                <div>{{ __('Motivo') }}: {{ $case->motivo_recusa }}</div>
            @endif
            @if($stepId === 'cliente.enviada' && $case->enviada_para)
                <div class="text-muted">{{ __('Enviada para') }}: {{ $case->enviada_para }}</div>
            @endif
            @if($doc)
                <div class="mt-1">
                    @if($docPath)
                        <a href="{{ route('backoffice.warranty.document', [$case->id, $doc]) }}" target="_blank"><i class="fa fa-file-pdf"></i> {{ __('Abrir documento') }}</a>
                        <a href="#wc-doc-{{ $id }}" data-toggle="collapse" class="text-secondary small ml-2"><i class="fa fa-sync-alt"></i> {{ __('trocar') }}</a>
                    @else
                        <a href="#wc-doc-{{ $id }}" data-toggle="collapse" class="text-secondary small"><i class="fa fa-paperclip"></i> {{ __('anexar documento') }}</a>
                    @endif
                    <div class="collapse" id="wc-doc-{{ $id }}">
                        <form method="POST" action="{{ route('backoffice.warranty.document.replace', [$case->id, $doc]) }}" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <input type="file" name="documento" class="form-control-file form-control-sm mb-2" accept=".pdf,.jpg,.jpeg,.png" required>
                            <button class="btn btn-sm btn-primary"><i class="fa fa-upload"></i> {{ $docPath ? __('Substituir') : __('Anexar') }}</button>
                        </form>
                    </div>
                </div>
            @endif
            @if($nota = $case->events->firstWhere('fase', $step['fase'])?->nota)
                <div class="wc-note">{{ $nota }}</div>
            @endif
        </div>
    @endif

    <div class="collapse {{ $open ? 'show' : '' }}" id="wc-f-{{ $id }}">
        <form method="POST" action="{{ route('backoffice.warranty.step.save', [$case->id, $blockKey, $halfKey]) }}" enctype="multipart/form-data" class="wc-half-form">
            @csrf
            <input type="hidden" name="__step" value="{{ $stepId }}">

            @if($blockKey === 'visita')
                <div class="form-group">
                    <label>{{ __('Nº da ordem de serviço') }}</label>
                    <input type="text" name="os_numero" class="form-control form-control-sm" value="{{ $val('os_numero') }}" required>
                </div>
                <div class="form-group">
                    <label>{{ __('O que o técnico encontrou') }}</label>
                    <textarea name="os_texto" class="form-control form-control-sm" rows="4" required
                              placeholder="{{ __('Descrição da avaria escrita na ordem de serviço, tal como foi para o cliente.') }}">{{ $val('os_texto', $case->os_texto ?: $case->descricao) }}</textarea>
                </div>
            @elseif($stepId === 'sielaff.recebida' || ($stepId === 'cliente.enviada' && !$case->needsParts()))
                <div class="form-group">
                    <label>{{ __('Nº da cotação') }}</label>
                    <input type="text" name="cotacao_numero" class="form-control form-control-sm" value="{{ $val('cotacao_numero') }}" required>
                    @unless($case->needsParts())<small class="form-text text-muted">{{ __('A cotação que fez para a deslocação e a mão de obra.') }}</small>@endunless
                </div>
            @elseif($stepId === 'cliente.oc')
                <div class="form-group">
                    <label>{{ __('Nº da ordem de compra') }}</label>
                    <input type="text" name="oc_numero" class="form-control form-control-sm" value="{{ $val('oc_numero') }}" required>
                </div>
            @elseif($stepId === 'alemanha.pedida')
                <div class="form-group">
                    <label>{{ __('Nº do artigo') }}</label>
                    <input type="text" name="artigo_numero" id="wc-artigo" class="form-control form-control-sm" value="{{ $val('artigo_numero') }}" required placeholder="{{ __('Se forem vários, separe por vírgulas') }}">
                </div>
                <div class="form-group">
                    <label>{{ __('Nº do cliente') }}</label>
                    <input type="text" name="cliente_numero" id="wc-cliente-num" class="form-control form-control-sm" value="{{ $val('cliente_numero', $case->cliente_numero ?: $suggestedClientNumber) }}" required>
                </div>
                <a href="#" class="btn btn-outline-dark btn-sm btn-block mb-3" id="wc-mail"><i class="fa fa-envelope"></i> {{ __('Abrir email para :email', ['email' => $germanyEmail]) }}</a>
            @elseif($stepId === 'alemanha.recebida')
                <div class="form-group">
                    @foreach(config('warranty.part_origins') as $value => $label)
                        <div class="custom-control custom-radio mb-1">
                            <input type="radio" class="custom-control-input" name="peca_origem" id="wc-origem-{{ $value }}" value="{{ $value }}" @checked($val('peca_origem', $case->peca_origem ?: 'recebida') === $value) required>
                            <label class="custom-control-label" for="wc-origem-{{ $value }}">{{ __($label) }}</label>
                        </div>
                    @endforeach
                </div>
            @elseif($stepId === 'fecho.recusado')
                <div class="form-group">
                    <label>{{ __('Motivo da recusa') }}</label>
                    <textarea name="motivo_recusa" class="form-control form-control-sm" rows="2" required>{{ $val('motivo_recusa') }}</textarea>
                </div>
            @endif

            @if($stepId === 'cliente.enviada')
                <div class="form-group">
                    <label>{{ __('Enviada para') }} <span class="text-muted">({{ __('opcional') }})</span></label>
                    <input type="text" name="enviada_para" class="form-control form-control-sm" value="{{ $val('enviada_para') }}" placeholder="{{ __('Ex.: nome ou email do contacto no cliente') }}">
                </div>
            @endif

            <div class="form-row">
                <div class="form-group col-sm-6">
                    <label>{{ __('Data') }}</label>
                    <input type="date" name="data" class="form-control form-control-sm"
                           value="{{ $hasErrors ? old('data') : ($done ? $case->{$step['date']}->toDateString() : now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                </div>
                @if($doc)
                    <div class="form-group col-sm-6">
                        <label>{{ __('Documento') }} <span class="text-muted">({{ __('opcional') }})</span></label>
                        <input type="file" name="documento" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                @endif
            </div>
            <div class="form-group">
                <label>{{ __('Nota') }} <span class="text-muted">({{ __('opcional') }})</span></label>
                <textarea name="nota" class="form-control form-control-sm" rows="2">{{ $hasErrors ? old('nota') : ($done ? $case->events->firstWhere('fase', $step['fase'])?->nota : null) }}</textarea>
            </div>
            <button type="submit" class="btn btn-sm text-white" style="background: {{ $block['color'] }}">
                <i class="fa fa-check"></i> {{ $done ? __('Gravar correção') : __('Registar') }}
            </button>
        </form>
    </div>
</div>

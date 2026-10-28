{{--
    O formulário de uma metade. Serve para registar e para corrigir.
    Espera: $case, $step, $done
--}}
@php
    $stepId = $step['block'] . '.' . $step['half'];
    $hasErrors = $errors->any() && old('__step') === $stepId;
    $doc = $step['doc'] ?? null;
    // old() só quando o erro veio deste formulário: senão um passo sujava os outros
    $val = fn ($field, $default = null) => $hasErrors ? old($field) : ($default ?? $case->$field);
    $evento = $case->events->firstWhere('fase', $step['fase']);
@endphp

<form method="POST" action="{{ route('backoffice.warranty.step.save', [$case->id, $step['block'], $step['half']]) }}"
      enctype="multipart/form-data" class="wc-form">
    @csrf
    <input type="hidden" name="__step" value="{{ $stepId }}">

    @if($step['block'] === 'visita')
        <div class="form-group">
            <label>{{ __('Nº da ordem de serviço') }}</label>
            <input type="text" name="os_numero" class="form-control" value="{{ $val('os_numero') }}" required>
        </div>
        <div class="form-group">
            <label>{{ __('O que o técnico encontrou') }}</label>
            <textarea name="os_texto" class="form-control" rows="4" required
                      placeholder="{{ __('Descrição da avaria escrita na ordem de serviço, tal como foi para o cliente.') }}">{{ $val('os_texto', $case->os_texto ?: $case->descricao) }}</textarea>
        </div>
    @elseif($stepId === 'sielaff.recebida' || ($stepId === 'cliente.enviada' && !$case->needsParts()))
        <div class="form-group">
            <label>{{ __('Nº da cotação') }}</label>
            <input type="text" name="cotacao_numero" class="form-control" value="{{ $val('cotacao_numero') }}" required>
            @unless($case->needsParts())<small class="form-text text-muted">{{ __('A cotação que fez para a deslocação e a mão de obra.') }}</small>@endunless
        </div>
    @elseif($stepId === 'cliente.oc')
        <div class="form-group">
            <label>{{ __('Nº da ordem de compra') }}</label>
            <input type="text" name="oc_numero" class="form-control" value="{{ $val('oc_numero') }}" required>
        </div>
    @elseif($stepId === 'alemanha.pedida')
        <div class="form-group">
            <label>{{ __('Nº do artigo') }}</label>
            <input type="text" name="artigo_numero" id="wc-artigo" class="form-control" value="{{ $val('artigo_numero') }}" required
                   placeholder="{{ __('Se forem vários, separe por vírgulas') }}">
        </div>
        <div class="form-group">
            <label>{{ __('Nº do cliente') }}</label>
            <input type="text" name="cliente_numero" id="wc-cliente-num" class="form-control"
                   value="{{ $val('cliente_numero', $case->cliente_numero ?: $suggestedClientNumber) }}" required>
        </div>
        <a href="#" class="btn btn-outline-dark btn-sm btn-block mb-3" id="wc-mail">
            <i class="fa fa-envelope"></i> {{ __('Abrir email para :email', ['email' => $germanyEmail]) }}
        </a>
    @elseif($stepId === 'alemanha.recebida')
        <div class="form-group">
            @foreach(config('warranty.part_origins') as $value => $label)
                <div class="custom-control custom-radio mb-1">
                    <input type="radio" class="custom-control-input" name="peca_origem" id="wc-origem-{{ $step['half'] }}-{{ $value }}"
                           value="{{ $value }}" @checked($val('peca_origem', $case->peca_origem ?: 'recebida') === $value) required>
                    <label class="custom-control-label" for="wc-origem-{{ $step['half'] }}-{{ $value }}">{{ __($label) }}</label>
                </div>
            @endforeach
        </div>
    @elseif($stepId === 'fecho.recusado')
        <div class="form-group">
            <label>{{ __('Motivo da recusa') }}</label>
            <textarea name="motivo_recusa" class="form-control" rows="2" required>{{ $val('motivo_recusa') }}</textarea>
        </div>
    @endif

    @if($stepId === 'cliente.enviada')
        <div class="form-group">
            <label>{{ __('Enviada para') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
            <input type="text" name="enviada_para" class="form-control" value="{{ $val('enviada_para') }}"
                   placeholder="{{ __('Ex.: nome ou email do contacto no cliente') }}">
        </div>
    @endif

    <div class="form-row">
        <div class="form-group col-sm-{{ $doc ? '6' : '12' }}">
            <label>{{ __('Data') }}</label>
            <input type="date" name="data" class="form-control"
                   value="{{ $hasErrors ? old('data') : ($done ? $case->{$step['date']}->toDateString() : now()->toDateString()) }}"
                   max="{{ now()->toDateString() }}" required>
        </div>
        @if($doc)
            <div class="form-group col-sm-6">
                <label>{{ __('Documento') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                <input type="file" name="documento" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
            </div>
        @endif
    </div>
    <div class="form-group">
        <label>{{ __('Nota') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
        <textarea name="nota" class="form-control" rows="2">{{ $hasErrors ? old('nota') : ($done ? $evento?->nota : null) }}</textarea>
    </div>

    <div class="d-flex align-items-center" style="gap: .5rem">
        <button type="submit" class="btn text-white" style="background: {{ config('warranty.blocks.' . $step['block'] . '.color') }}">
            <i class="fa fa-check"></i> {{ $done ? __('Gravar correção') : __('Registar') }}
        </button>
        @if($done)
            <button type="button" class="btn btn-link text-danger btn-sm"
                    onclick="document.getElementById('wc-apagar-{{ $step['block'] }}-{{ $step['half'] }}').submit()">
                <i class="fa fa-trash"></i> {{ __('Apagar passo') }}
            </button>
        @endif
    </div>
</form>

@if($done)
    <form method="POST" id="wc-apagar-{{ $step['block'] }}-{{ $step['half'] }}" class="d-none"
          action="{{ route('backoffice.warranty.step.clear', [$case->id, $step['block'], $step['half']]) }}"
          onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Apagar este passo? Os dados e o documento deste passo são apagados.')) }})">
        @csrf
        @method('DELETE')
    </form>
@endif

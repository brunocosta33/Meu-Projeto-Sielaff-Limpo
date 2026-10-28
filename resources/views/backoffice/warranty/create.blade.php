@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Novo processo fora da garantia') }}</title>
@endsection

@php
    $prefillType = old('tipo', $prefillType);
    $prefillStore = old('store_id', $technicalRequest->store_id ?? null);
    $prefillMachine = old('machine_id', $technicalRequest->machine_id ?? null);
    $prefillClient = old('cliente', \App\Models\WarrantyCase::clientFromStore($technicalRequest->store ?? null));
    $machinesByStore = $stores->mapWithKeys(fn ($s) => [$s->id => [
        'cliente' => \App\Models\WarrantyCase::clientFromStore($s),
        'machines' => $s->machines->map(fn ($m) => ['id' => $m->id, 'label' => trim($m->serial_number . ($m->descricao ? ' — ' . $m->descricao : ''))])->values(),
    ]]);
@endphp

@push('styles')
<style>
    .wc-type { display: block; cursor: pointer; margin: 0; height: 100%; padding: .75rem .9rem; border-radius: 12px;
               border: 2px solid #e2e8f0; background: #fff; transition: all .15s; }
    .wc-type:hover { border-color: var(--c); }
    .wc-type.active { border-color: var(--c); background: var(--bg); }
    .wc-type input { position: absolute; opacity: 0; }
    .wc-type strong { display: block; font-size: .95rem; color: var(--c); }
    .wc-type small { display: block; color: #64748b; line-height: 1.3; margin-top: .2rem; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="mb-0"><i class="fas fa-file-invoice-dollar text-danger"></i> {{ __('Novo processo fora da garantia') }}</h4>
                    <a href="{{ route('backoffice.warranty.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left"></i> {{ __('Voltar') }}</a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('backoffice.warranty.store') }}" enctype="multipart/form-data">
                    @csrf

                    @if($technicalRequest)
                        <input type="hidden" name="technical_request_id" value="{{ $technicalRequest->id }}">
                        <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between" style="gap: .5rem">
                            <span><i class="fa fa-link"></i> {{ __('Criado a partir do pedido de assistência') }} #{{ $technicalRequest->id }} ({{ \Carbon\Carbon::parse($technicalRequest->data_pedido)->format('d/m/Y') }}).</span>
                            @if($requestPhotos->count())
                                <label class="mb-0">
                                    <input type="checkbox" name="copiar_fotos" value="1" checked>
                                    {{ __('Copiar as :total fotografias do pedido', ['total' => $requestPhotos->count()]) }}
                                </label>
                            @endif
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="small font-weight-bold mb-1">{{ __('Tipo de processo') }}</label>
                        <div class="form-row">
                            @foreach($types as $value => $type)
                                <div class="col-md-6 mb-2">
                                    <label class="wc-type {{ $prefillType === $value ? 'active' : '' }}" style="--c: {{ $type['color'] }}; --bg: {{ $type['bg'] }}">
                                        <input type="radio" name="tipo" value="{{ $value }}" @checked($prefillType === $value) required>
                                        <strong><i class="fas {{ $type['icon'] }}"></i> {{ __($type['label']) }}</strong>
                                        <small>{{ __($type['ajuda']) }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-7">
                            <label class="small font-weight-bold mb-1">{{ __('Loja') }}</label>
                            <select name="store_id" id="wc-store" class="form-control selectpicker" data-live-search="true" title="{{ __('Escolha a loja…') }}" required>
                                @foreach($stores as $s)
                                    <option value="{{ $s->id }}" @selected($prefillStore == $s->id)>{{ $s->codigo_loja }} — {{ $s->nome_loja }}{{ $s->cidade ? ' (' . $s->cidade . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-5">
                            <label class="small font-weight-bold mb-1">{{ __('Cliente') }}</label>
                            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                @foreach($clients as $value => $label)
                                    <label class="btn btn-outline-dark flex-fill {{ $prefillClient === $value ? 'active' : '' }}">
                                        <input type="radio" name="cliente" value="{{ $value }}" autocomplete="off" @checked($prefillClient === $value) required> {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            <small class="form-text text-muted">{{ __('Escolhido automaticamente pela insígnia da loja.') }}</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold mb-1">{{ __('Data da avaria') }}</label>
                            <input type="date" name="data_avaria" class="form-control" value="{{ old('data_avaria', $technicalRequest ? \Carbon\Carbon::parse($technicalRequest->data_pedido)->toDateString() : now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="form-group col-md-8">
                            <label class="small font-weight-bold mb-1">{{ __('Máquina') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                            <select name="machine_id" id="wc-machine" class="form-control" data-selected="{{ $prefillMachine }}">
                                <option value="">—</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold mb-1">{{ __('Problema') }}</label>
                        <textarea name="descricao" class="form-control" rows="5" required placeholder="{{ __('O que aconteceu e porque é culpa do cliente (ex.: máquina danificada por empilhador, objeto estranho no tapete…)') }}">{{ old('descricao', $technicalRequest->descricao_problema ?? '') }}</textarea>
                    </div>

                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <strong class="small text-uppercase" style="color: {{ config('warranty.blocks.visita.color') }}">
                                    <i class="fas fa-clipboard-check"></i> {{ __('Ordem de serviço da visita') }}
                                </strong>
                                <a href="#wc-os" data-toggle="collapse" class="small">{{ __('preencher agora') }}</a>
                            </div>
                            <small class="form-text text-muted mb-0">{{ __('Se o técnico já foi à loja, registe já a ordem de serviço. Se ainda não, deixe em branco e preencha depois na ficha do processo.') }}</small>
                            <div class="collapse {{ old('os_em') ? 'show' : '' }}" id="wc-os">
                                <div class="form-row mt-3">
                                    <div class="form-group col-md-4 mb-2">
                                        <label class="small font-weight-bold mb-1">{{ __('Data da visita') }}</label>
                                        <input type="date" name="os_em" class="form-control" value="{{ old('os_em') }}" max="{{ now()->toDateString() }}">
                                    </div>
                                    <div class="form-group col-md-8 mb-2">
                                        <label class="small font-weight-bold mb-1">{{ __('Nº da ordem de serviço') }}</label>
                                        <input type="text" name="os_numero" class="form-control" value="{{ old('os_numero') }}">
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold mb-1">{{ __('O que o técnico encontrou') }}</label>
                                    <textarea name="os_texto" class="form-control" rows="3" placeholder="{{ __('Descrição da avaria escrita na ordem de serviço, tal como foi para o cliente.') }}">{{ old('os_texto') }}</textarea>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold mb-1">{{ __('PDF da ordem de serviço') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                                    <input type="file" name="documento" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold mb-1">{{ __('Fotografias') }}</label>
                        @include('backoffice.warranty.partials.photo_input', ['inputId' => 'wc-photos'])
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold mb-1">{{ __('Observações') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                        <textarea name="observacoes" class="form-control" rows="2">{{ old('observacoes') }}</textarea>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> {{ __('Criar processo') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var data = {{ \Illuminate\Support\Js::from($machinesByStore) }};
        var store = document.getElementById('wc-store');
        var machine = document.getElementById('wc-machine');

        var fill = function (setClient) {
            var info = data[store.value] || { machines: [], cliente: null };
            var selected = machine.dataset.selected;
            machine.innerHTML = '<option value="">—</option>';
            info.machines.forEach(function (m) {
                var o = new Option(m.label, m.id, false, String(m.id) === String(selected));
                machine.add(o);
            });
            machine.dataset.selected = '';
            if (setClient && info.cliente) {
                document.querySelectorAll('input[name=cliente]').forEach(function (r) {
                    r.checked = r.value === info.cliente;
                    r.closest('label').classList.toggle('active', r.checked);
                });
            }
        };

        document.querySelectorAll('input[name=tipo]').forEach(function (r) {
            r.addEventListener('change', function () {
                document.querySelectorAll('.wc-type').forEach(function (l) {
                    l.classList.toggle('active', l.contains(r) && r.checked);
                });
            });
        });

        // O bootstrap-select dispara o "change" via jQuery (não chega aos addEventListener)
        if (window.jQuery) $(store).on('change', function () { fill(true); });
        else store.addEventListener('change', function () { fill(true); });
        fill(false);
        if (window.jQuery && $.fn.selectpicker) $('.selectpicker').selectpicker();
    });
</script>
@endpush

@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Novo processo fora da garantia') }}</title>
@endsection

@php
    $prefillStore = old('store_id', $technicalRequest->store_id ?? null);
    $prefillMachine = old('machine_id', $technicalRequest->machine_id ?? null);
    $prefillClient = old('cliente', \App\Models\WarrantyCase::clientFromStore($technicalRequest->store ?? null));
    $machinesByStore = $stores->mapWithKeys(fn ($s) => [$s->id => [
        'cliente' => \App\Models\WarrantyCase::clientFromStore($s),
        'machines' => $s->machines->map(fn ($m) => ['id' => $m->id, 'label' => trim($m->serial_number . ($m->descricao ? ' — ' . $m->descricao : ''))])->values(),
    ]]);
@endphp

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

                    <div class="form-group">
                        <label class="small font-weight-bold mb-1">{{ __('Máquina') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                        <select name="machine_id" id="wc-machine" class="form-control" data-selected="{{ $prefillMachine }}">
                            <option value="">—</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold mb-1">{{ __('Problema') }}</label>
                        <textarea name="descricao" class="form-control" rows="5" required placeholder="{{ __('O que aconteceu e porque é culpa do cliente (ex.: máquina danificada por empilhador, objeto estranho no tapete…)') }}">{{ old('descricao', $technicalRequest->descricao_problema ?? '') }}</textarea>
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

        // O bootstrap-select dispara o "change" via jQuery (não chega aos addEventListener)
        if (window.jQuery) $(store).on('change', function () { fill(true); });
        else store.addEventListener('change', function () { fill(true); });
        fill(false);
        if (window.jQuery && $.fn.selectpicker) $('.selectpicker').selectpicker();
    });
</script>
@endpush

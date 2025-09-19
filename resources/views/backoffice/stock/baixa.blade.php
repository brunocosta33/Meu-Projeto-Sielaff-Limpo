@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Dar Baixa no Cliente') }}</title>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Dar Baixa de Peças no Cliente') }}</h5>

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('backoffice.stock.baixa.store') }}" method="POST">
            @csrf

            {{-- Selecionar Carrinha --}}
            <div class="form-group">
                <label>{{ __('Carrinha (Origem)') }}</label>
                <select name="carrinha_id" id="carrinha_id" class="form-control" required>
                    <option value="">{{ __('-- Selecionar Carrinha --') }}</option>
                    @foreach($carrinhas as $c)
                    <option value="{{ $c->id }}">{{ $c->nome }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Selecionar Cliente/Loja --}}
            <div class="form-group">
                <label>{{ __('Loja (Cliente)') }}</label>
                <select name="store_id" id="store_id" class="form-control" required>
                    <option value="">{{ __('-- Selecionar Loja --') }}</option>
                    @foreach($stores as $s)
                    <option value="{{ $s->store->id }}">
                        {{ $s->store->codigo_loja }} - {{ $s->store->nome_loja }}
                    </option>
                    @endforeach
                </select>
            </div>


            {{-- Selecionar Máquina --}}
            <div class="form-group">
                <label>{{ __('Máquina (Serial Number)') }}</label>
                <select name="machine_id" id="machine_id" class="form-control">
                    <option value="">{{ __('-- Selecionar Máquina --') }}</option>
                </select>
            </div>

            {{-- Selecionar Peça --}}
            <div class="form-group">
                <label>{{ __('Peça') }}</label>
                <select name="item_id" class="form-control" required>
                    <option value="">{{ __('-- Selecionar Peça --') }}</option>
                    @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->nome }} ({{ $item->referencia }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Quantidade --}}
            <div class="form-group">
                <label>{{ __('Quantidade') }}</label>
                <input type="number" name="quantidade" class="form-control" min="1" value="1" required>
            </div>

            {{-- Observações --}}
            <div class="form-group">
                <label>{{ __('Observações') }}</label>
                <textarea name="observacoes" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-success">{{ __('Dar Baixa') }}</button>
            <a href="{{ route('backoffice.stock.movements.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const storeSelect = document.getElementById('store_id');
        const machineSelect = document.getElementById('machine_id');

        storeSelect.addEventListener('change', function() {
            const storeId = this.value;
            machineSelect.innerHTML = '<option value="">A carregar...</option>';

            if (!storeId) {
                machineSelect.innerHTML = '<option value="">-- Selecionar Máquina --</option>';
                return;
            }

            fetch(`/api/machines/by-store/${storeId}`)
                .then(res => res.json())
                .then(data => {
                    machineSelect.innerHTML = '<option value="">-- Selecionar Máquina --</option>';
                    data.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = `${m.serial_number} - ${m.descricao ?? ''}`;
                        machineSelect.appendChild(opt);
                    });
                })
                .catch(() => {
                    machineSelect.innerHTML = '<option value="">Erro ao carregar máquinas</option>';
                });
        });
    });
</script>
@endsection
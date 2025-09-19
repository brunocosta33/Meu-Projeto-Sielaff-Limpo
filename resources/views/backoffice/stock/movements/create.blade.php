@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Novo Movimento de Stock') }}</title>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Novo Movimento de Stock') }}</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('backoffice.stock.movements.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>{{ __('Peça') }}</label>
                <select id="item_id" name="item_id" class="form-control" required>
                    <option value="">{{ __('-- Selecionar Peça --') }}</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->nome }} ({{ $item->referencia }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Painel de stock da peça --}}
            <div class="form-group mt-2">
                <strong>{{ __('Stock atual desta peça:') }}</strong>
                <ul id="stock-list">
                    <li>Selecione uma peça para ver o stock.</li>
                </ul>
            </div>

            <div class="form-group">
                <label>{{ __('Quantidade') }}</label>
                <input type="number" name="quantidade" class="form-control" min="1" value="1" required>
            </div>

            <div class="form-group">
                <label>{{ __('Origem') }}</label>
                <select id="origem" name="origem_id" class="form-control" required>
                    <option value="">{{ __('-- Selecionar Origem --') }}</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" data-tipo="{{ $loc->tipo }}">
                            {{ $loc->nome }} ({{ $loc->tipo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>{{ __('Destino') }}</label>
                <select id="destino" name="destino_id" class="form-control" required>
                    <option value="">{{ __('-- Selecionar Destino --') }}</option>
                </select>
            </div>

            <input type="hidden" id="tipo_movimento" name="tipo_movimento">
            <p><strong>{{ __('Tipo de Movimento:') }}</strong> <span id="tipo_label">-</span></p>

            <div class="form-group">
                <label>{{ __('Observações') }}</label>
                <textarea name="observacoes" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-success">{{ __('Guardar Movimento') }}</button>
            <a href="{{ route('backoffice.stock.movements.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>
        </form>
    </div>
</div>

@php
    $locData = $locations->map(fn($l) => ['id'=>$l->id,'nome'=>$l->nome,'tipo'=>$l->tipo])->values();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const allLocations   = @json($locData);
    const origemSelect   = document.getElementById('origem');
    const destinoSelect  = document.getElementById('destino');
    const tipoHidden     = document.getElementById('tipo_movimento');
    const tipoLabel      = document.getElementById('tipo_label');

    const itemSelect     = document.getElementById('item_id');
    const stockList      = document.getElementById('stock-list');

    const allowedByOrigem = {
        armazem:    ['carrinha','fornecedor'],
        carrinha:   ['armazem','cliente'],
        fornecedor: ['armazem'],
        cliente:    []
    };

    function label(l) {
        return `${l.nome} (${l.tipo})`;
    }

    function sugerirTipo(origemTipo, destinoTipo) {
        if (origemTipo === 'fornecedor' && destinoTipo === 'armazem') return 'entrada';
        if (origemTipo === 'armazem' && destinoTipo === 'carrinha')   return 'transferencia';
        if (origemTipo === 'carrinha' && destinoTipo === 'cliente')   return 'saida';
        if (origemTipo === 'carrinha' && destinoTipo === 'armazem')   return 'devolucao';
        if (origemTipo === 'armazem' && destinoTipo === 'fornecedor') return 'devolucao';
        return '';
    }

    origemSelect.addEventListener('change', function () {
        const origemId = parseInt(origemSelect.value, 10);
        const origem   = allLocations.find(l => l.id === origemId);

        destinoSelect.innerHTML = '<option value="">-- Selecionar Destino --</option>';
        tipoHidden.value = '';
        tipoLabel.textContent = '-';

        if (!origem) return;

        const allowedTipos = allowedByOrigem[origem.tipo] || [];
        const candidatos = allLocations.filter(l => allowedTipos.includes(l.tipo) && l.id !== origem.id);

        candidatos.forEach(l => {
            const opt = document.createElement('option');
            opt.value = l.id;
            opt.textContent = label(l);
            opt.dataset.tipo = l.tipo;
            destinoSelect.appendChild(opt);
        });
    });

    destinoSelect.addEventListener('change', function () {
        const origemId  = parseInt(origemSelect.value, 10);
        const destinoId = parseInt(destinoSelect.value, 10);
        const origem  = allLocations.find(l => l.id === origemId);
        const destino = allLocations.find(l => l.id === destinoId);

        if (!origem || !destino) {
            tipoHidden.value = '';
            tipoLabel.textContent = '-';
            return;
        }

        const tipo = sugerirTipo(origem.tipo, destino.tipo);
        tipoHidden.value = tipo;
        tipoLabel.textContent = tipo ? tipo.charAt(0).toUpperCase() + tipo.slice(1) : '-';
    });

    itemSelect.addEventListener('change', function () {
        const itemId = this.value;
        stockList.innerHTML = '<li>A carregar...</li>';

        if (!itemId) {
            stockList.innerHTML = '<li>Selecione uma peça para ver o stock.</li>';
            return;
        }

        fetch(`/api/stock/item/${itemId}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    stockList.innerHTML = '';
                    data.forEach(s => {
                        const li = document.createElement('li');
                        li.textContent = `${s.location}: ${s.quantidade}`;
                        stockList.appendChild(li);
                    });
                } else {
                    stockList.innerHTML = '<li>Sem stock registado.</li>';
                }
            })
            .catch(() => {
                stockList.innerHTML = '<li>Erro a carregar stock.</li>';
            });
    });
});
</script>
@endsection

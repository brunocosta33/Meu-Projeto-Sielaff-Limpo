@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h1 class="mb-4">Novo Movimento de Stock</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('backoffice.stock.movements.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="item_id" class="form-label">Item:</label>
            <select name="item_id" id="item_id" class="form-control" required>
                <option value="">-- Selecionar --</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->nome }} ({{ $item->referencia }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo:</label>
            <select name="tipo" id="tipo" class="form-control" required>
                <option value="entrada">Entrada</option>
                <option value="saida">Saída</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="quantidade" class="form-label">Quantidade:</label>
            <input type="number" name="quantidade" id="quantidade" class="form-control" min="1" required>
        </div>

        <div class="mb-3">
            <label for="motivo" class="form-label">Motivo:</label>
            <input type="text" name="motivo" id="motivo" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Guardar Movimento</button>
    </form>
</div>
@endsection

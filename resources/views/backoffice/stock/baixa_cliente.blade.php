@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - Dar Baixa no Cliente</title>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Dar Baixa no Cliente</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('backoffice.stock.baixa.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Carrinha</label>
                <select name="carrinha_id" class="form-control" required>
                    <option value="">-- Selecionar Carrinha --</option>
                    @foreach($carrinhas as $c)
                        <option value="{{ $c->id }}">{{ $c->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Cliente (Loja)</label>
                <select name="store_id" class="form-control" required>
                    <option value="">-- Selecionar Cliente --</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}">{{ $s->codigo_loja }} - {{ $s->nome_loja }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Peça</label>
                <select name="item_id" class="form-control" required>
                    <option value="">-- Selecionar Peça --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->nome }} ({{ $item->referencia }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Quantidade</label>
                <input type="number" name="quantidade" min="1" class="form-control" required>
            </div>

            <button class="btn btn-success" type="submit">Dar Baixa</button>
        </form>
    </div>
</div>
@endsection

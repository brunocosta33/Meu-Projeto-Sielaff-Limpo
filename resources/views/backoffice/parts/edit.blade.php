@extends('layouts.backoffice_master')

@section('content')
<h4>Editar Peça</h4>

<form method="POST" action="{{ route('backoffice.parts.update', $part->id) }}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" value="{{ $part->nome }}" required>
    </div>

    <div class="form-group">
        <label>Referência</label>
        <input type="text" name="referencia" class="form-control" value="{{ $part->referencia }}">
    </div>

    <div class="form-group">
        <label>Quantidade</label>
        <input type="number" name="quantidade" class="form-control" min="0" value="{{ $part->quantidade }}">
    </div>

    <div class="form-group">
        <label>Descrição</label>
        <textarea name="descricao" class="form-control">{{ $part->descricao }}</textarea>
    </div>

    <div class="form-group">
        <label>Tipo</label>
        <select name="type" class="form-control" required>
            <option value="peca" {{ $part->type == 'peca' ? 'selected' : '' }}>Peça</option>
            <option value="ferramenta" {{ $part->type == 'ferramenta' ? 'selected' : '' }}>Ferramenta</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Atualizar</button>
    <a href="{{ route('backoffice.parts.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection

@extends('layouts.backoffice_master')
@section('content')
<h4>Editar Peça</h4>
<form method="POST" action="{{ route('backoffice.parts.update', $part->id) }}">
    @csrf
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
    <button type="submit" class="btn btn-primary">Atualizar</button>
</form>
@endsection

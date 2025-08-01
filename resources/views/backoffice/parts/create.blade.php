@extends('layouts.backoffice_master')

@section('content')
<h4>Nova Peça</h4>

<form method="POST" action="{{ route('backoffice.parts.store') }}">
    @csrf

    <div class="form-group">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Referência</label>
        <input type="text" name="referencia" class="form-control">
    </div>

    <div class="form-group">
        <label>Quantidade</label>
        <input type="number" name="quantidade" class="form-control" min="0" value="0">
    </div>

    <div class="form-group">
        <label>Descrição</label>
        <textarea name="descricao" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label>Tipo</label>
        <select name="type" class="form-control" required>
            <option value="peca">Peça</option>
            <option value="ferramenta">Ferramenta</option>
        </select>
    </div>

    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="{{ route('backoffice.parts.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection

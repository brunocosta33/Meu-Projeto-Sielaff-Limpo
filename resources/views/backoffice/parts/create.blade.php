@extends('layouts.backoffice_master')

@section('content')
<h4>{{ __('Nova Peça') }}</h4>

<form method="POST" action="{{ route('backoffice.parts.store') }}">
    @csrf

    <div class="form-group">
        <label>{{ __('Nome') }}</label>
        <input type="text" name="nome" class="form-control" required>
    </div>

    <div class="form-group">
        <label>{{ __('Referência') }}</label>
        <input type="text" name="referencia" class="form-control">
    </div>

    <div class="form-group">
        <label>{{ __('Quantidade') }}</label>
        <input type="number" name="quantidade" class="form-control" min="0" value="0">
    </div>

    <div class="form-group">
        <label>{{ __('Descrição') }}</label>
        <textarea name="descricao" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label>{{ __('Tipo') }}</label>
        <select name="type" class="form-control" required>
            <option value="peca">{{ __('Peça') }}</option>
            <option value="ferramenta">{{ __('Ferramenta') }}</option>
        </select>
    </div>

    <button type="submit" class="btn btn-success">{{ __('Guardar') }}</button>
    <a href="{{ route('backoffice.parts.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>
</form>
@endsection

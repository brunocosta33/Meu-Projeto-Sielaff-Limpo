@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Nova Localização') }}</title>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Nova Localização') }}</h5>

        {!! Form::open(['route' => 'backoffice.locations.store', 'method' => 'POST']) !!}
        {{ csrf_field() }}

        <div class="form-group">
            <label>{{ __('Nome') }}</label>
            <input type="text" name="nome" class="form-control" required>
        </div>

        <div class="form-group">
            <label>{{ __('Tipo') }}</label>
            <select name="tipo" class="form-control" required>
                <option value="armazem">Armazém</option>
                <option value="carrinha">Carrinha</option>
                <option value="cliente">Cliente</option>
                <option value="fornecedor">Fornecedor</option>
            </select>
        </div>

        <div class="form-group">
            <label>{{ __('Código') }}</label>
            <input type="text" name="codigo" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">{{ __('Guardar') }}</button>
        <a href="{{ route('backoffice.locations.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>

        {!! Form::close() !!}
    </div>
</div>
@endsection

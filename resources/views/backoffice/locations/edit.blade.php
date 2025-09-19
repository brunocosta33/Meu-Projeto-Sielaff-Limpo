@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Localização') }}</title>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Editar Localização') }}</h5>

        {!! Form::model($location, ['route' => ['backoffice.locations.update',$location->id], 'method' => 'PUT']) !!}
        {{ csrf_field() }}

        <div class="form-group">
            <label>{{ __('Nome') }}</label>
            <input type="text" name="nome" class="form-control" value="{{ $location->nome }}" required>
        </div>

        <div class="form-group">
            <label>{{ __('Tipo') }}</label>
            <select name="tipo" class="form-control" required>
                <option value="armazem" {{ $location->tipo=='armazem'?'selected':'' }}>Armazém</option>
                <option value="carrinha" {{ $location->tipo=='carrinha'?'selected':'' }}>Carrinha</option>
                <option value="cliente" {{ $location->tipo=='cliente'?'selected':'' }}>Cliente</option>
                <option value="fornecedor" {{ $location->tipo=='fornecedor'?'selected':'' }}>Fornecedor</option>
            </select>
        </div>

        <div class="form-group">
            <label>{{ __('Código') }}</label>
            <input type="text" name="codigo" class="form-control" value="{{ $location->codigo }}">
        </div>

        <button type="submit" class="btn btn-primary">{{ __('Atualizar') }}</button>
        <a href="{{ route('backoffice.locations.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>

        {!! Form::close() !!}
    </div>
</div>
@endsection

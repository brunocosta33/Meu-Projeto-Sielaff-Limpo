@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Nova Peça') }}</title>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Nova Peça') }}</h5>

        {!! Form::open(['route' => 'backoffice.items.store', 'method' => 'POST']) !!}
        {{ csrf_field() }}

        <div class="form-group">
            <label>{{ __('Nome') }}</label>
            <input type="text" name="nome" class="form-control" required>
        </div>

        <div class="form-group">
            <label>{{ __('Referência') }}</label>
            <input type="text" name="referencia" class="form-control">
        </div>

        <div class="form-group">
            <label>{{ __('Descrição') }}</label>
            <textarea name="descricao" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">{{ __('Guardar') }}</button>
        <a href="{{ route('backoffice.items.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>

        {!! Form::close() !!}
    </div>
</div>
@endsection

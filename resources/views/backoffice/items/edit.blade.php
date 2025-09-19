@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Peça') }}</title>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Editar Peça') }}</h5>

        {!! Form::model($item, ['route' => ['backoffice.items.update',$item->id], 'method' => 'PUT']) !!}
        {{ csrf_field() }}

        <div class="form-group">
            <label>{{ __('Nome') }}</label>
            <input type="text" name="nome" class="form-control" value="{{ $item->nome }}" required>
        </div>

        <div class="form-group">
            <label>{{ __('Referência') }}</label>
            <input type="text" name="referencia" class="form-control" value="{{ $item->referencia }}">
        </div>

        <div class="form-group">
            <label>{{ __('Descrição') }}</label>
            <textarea name="descricao" class="form-control">{{ $item->descricao }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('Atualizar') }}</button>
        <a href="{{ route('backoffice.items.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>

        {!! Form::close() !!}
    </div>
</div>
@endsection

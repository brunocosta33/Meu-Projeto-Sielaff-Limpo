@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Loja') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <h5 class="card-title mb-4">{{ __('Editar Loja') }}</h5>

                {!! Form::open(['route' => ['backoffice.stores.update', $store->id]]) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>{{ __('Região') }}</label>
                    <input type="text" name="regiao" class="form-control" value="{{ $store->regiao }}" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Código da Loja') }}</label>
                    <input type="text" name="codigo_loja" class="form-control" value="{{ $store->codigo_loja }}" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Nome da Loja') }}</label>
                    <input type="text" name="nome_loja" class="form-control" value="{{ $store->nome_loja }}" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Morada') }}</label>
                    <textarea name="morada" class="form-control" rows="3">{{ $store->morada }}</textarea>
                </div>

                <div class="form-group">
                    <label>{{ __('Código Postal') }}</label>
                    <input type="text" name="codigo_postal" class="form-control" value="{{ $store->codigo_postal }}">
                </div>

                <a class="btn btn-outline-secondary" href="{{ URL::previous() }}">
                    <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                </a>

                {!! Form::button('<i class="fa fa-save"></i> ' . __('Atualizar'), ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>
@endsection

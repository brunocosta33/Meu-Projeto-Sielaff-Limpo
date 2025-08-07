@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ str_replace('.', ' ', config('app.name')) }} - {{ __('Novo Fornecedor') }}</title>
    <link rel="stylesheet" href="{{ URL::asset('css/style.css') }}">
@endsection

@section('head-script')
@endsection

@section('content')
<div class="row">
    @include('flash::message')
</div>
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-5">
                        <h5 class="card-title" style="margin-bottom: 30px;">{{ __('Novo Fornecedor') }}</h5>
                    </div>
                </div>
                <div>
                    {!! Form::open(['route' => ['backoffice.suppliers.store']]) !!}
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label>{{ __('Nome do Fornecedor') }}</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Contacto') }}</label>
                        <input type="text" name="contacto" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Email') }}</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>{{ __('NIF') }}</label>
                        <input type="text" name="nif" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Morada') }}</label>
                        <textarea name="morada" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mt-4">
                        <a class="btn btn-outline-secondary" href="{!! URL::previous() !!}">
                            <span class="fa fa-arrow-left"></span> &nbsp; {{ __('Voltar') }}
                        </a>
                        {!! Form::button('<i class="fa fa-save"></i> ' . __('Gravar'), ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('foot-scripts')
@endsection

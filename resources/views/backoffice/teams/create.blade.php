@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Nova Equipa') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('Nova Equipa') }}</h5>

                {!! Form::open(['route' => 'backoffice.teams.store']) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>{{ __('Nome da Equipa') }}</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Contacto') }}</label>
                    <input type="text" name="contacto" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Email') }}</label>
                    <input type="text" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="3"></textarea>
                </div>

                <a class="btn btn-outline-secondary" href="{{ route('backoffice.teams.index') }}">
                    <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                </a>

                {!! Form::button('<i class="fa fa-save"></i> ' . __('Gravar'), ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

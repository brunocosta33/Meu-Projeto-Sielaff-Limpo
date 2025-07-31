@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Nova Equipa</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Nova Equipa</h5>

                {!! Form::open(['route' => 'backoffice.teams.store']) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>Nome da Equipa</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Contacto</label>
                    <input type="text" name="contacto" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3"></textarea>
                </div>

                <a class="btn btn-outline-secondary" href="{{ route('backoffice.teams.index') }}">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>

                {!! Form::button('<i class="fa fa-save"></i> Gravar', ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

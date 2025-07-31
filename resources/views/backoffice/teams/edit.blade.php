@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Editar Equipa</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Editar Equipa</h5>

                {!! Form::model($team, ['route' => ['backoffice.teams.update', $team->id], 'method' => 'POST']) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>Nome da Equipa</label>
                    <input type="text" name="nome" class="form-control" value="{{ $team->nome }}" required>
                </div>

                <div class="form-group">
                    <label>Contacto</label>
                  <input type="text" name="contacto" class="form-control" value="{{ $team->contacto }}" required>
                </div>

                 <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" value="{{ $team->email }}" required>
                </div>

                 <div class="form-group">
                    <label>Observações</label>
                    <textarea name="membros" class="form-control" rows="3">{{ $team->observacoes }}</textarea>
                </div>



                <a class="btn btn-outline-secondary" href="{{ route('backoffice.teams.index') }}">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>

                {!! Form::button('<i class="fa fa-save"></i> Atualizar', ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

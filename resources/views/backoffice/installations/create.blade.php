@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Nova Instalação</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Nova Instalação</h5>

                {!! Form::open(['route' => ['backoffice.installations.store']]) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>Loja</label>
                    <select name="store_id" class="form-control" required>
                        <option value="">-- Selecione a Loja --</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->nome_loja }} ({{ $store->codigo_loja }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Equipa Técnica</label>
                    <select name="team_id" class="form-control" required>
                        <option value="">-- Selecione a Equipa --</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="scheduled_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Hora</label>
                    <input type="time" name="scheduled_time" class="form-control" required>
                </div>

               <div class="form-group">
                    <label>Tipo de Serviço</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        <option value="Instalação">Instalação</option>
                        <option value="Desinstalação">Desinstalação</option>
                    </select>
                </div>

                 <div class="form-group">
                    <label>Estado</label>
                    <select name="status" class="form-control" required>
                        <option value="Agendado">Agendado</option>
                        <option value="Concluído">Concluído</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control"></textarea>
                </div>

                <input type="hidden" name="page" value="{{ request('page') }}">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('backoffice.installations.index', ['page' => request('page')]) }}" class="btn btn-secondary">Cancelar</a>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

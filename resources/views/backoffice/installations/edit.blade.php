@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Editar Instalação</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Editar Instalação</h5>

                {!! Form::model($installation, ['route' => ['backoffice.installations.update', $installation->id], 'method' => 'PUT']) !!}
                {{ csrf_field() }}

               <div class="form-group">
                    <label>Loja</label>
                    <select name="store_id" class="form-control" required>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $installation->store_id == $store->id ? 'selected' : '' }}>
                                {{ $store->nome_loja }} ({{ $store->codigo_loja }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Equipa Técnica</label>
                    <select name="team_id" class="form-control" required>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ $installation->team_id == $team->id ? 'selected' : '' }}>
                                {{ $team->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="scheduled_date" class="form-control"
                        value="{{ \Carbon\Carbon::parse($installation->scheduled_date)->format('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label>Hora</label>
                    <input type="time" name="scheduled_time" class="form-control"
                        value="{{ \Carbon\Carbon::parse($installation->scheduled_time)->format('H:i') }}" required>
                </div>

                <div class="form-group">
                    <label>Tipo de Serviço</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="Instalação" {{ $installation->tipo_servico == 'Instalação' ? 'selected' : '' }}>Instalação</option>
                        <option value="Desinstalação" {{ $installation->tipo_servico == 'Desinstalação' ? 'selected' : '' }}>Desinstalação</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="status" class="form-control" required>
                        <option value="Agendado" {{ $installation->status == 'Agendado' ? 'selected' : '' }}>Agendado</option>
                        <option value="Concluído" {{ $installation->status == 'Concluído' ? 'selected' : '' }}>Concluído</option>
                        <option value="Cancelado" {{ $installation->status == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control">{{ $installation->observacoes }}</textarea>
                </div>

                <input type="hidden" name="page" value="{{ request('page') }}">
                <button type="submit" class="btn btn-primary">Atualizar</button>
               <a href="{{ route('backoffice.installations.index', ['page' => request('page')]) }}" class="btn btn-secondary">Cancelar</a>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
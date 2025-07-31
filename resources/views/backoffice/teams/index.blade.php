@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Equipas</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.teams.create') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-plus"></i> Nova Equipa
                    </a>
                </div>

                <h5 class="card-title">Lista de Equipas</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome da Equipa</th>
                                <th>Contacto</th>
                                <th>Email</th>
                                <th>Observações</th>

                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teams as $team)
                                <tr>
                                    <td>{{ $team->id }}</td>
                                    <td>{{ $team->nome }}</td>
                                    <td>{{ $team->contacto }}</td>
                                    <td>{{ $team->email }}</td>
                                    <td>{{ $team->observacoes }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('backoffice.teams.edit', $team->id) }}" class="ml-2"><i class="fa fa-edit" title="Editar"></i></a>
                                        <a href="{{ route('backoffice.teams.delete', $team->id) }}"
                                           onclick="return confirm('Tem a certeza que deseja apagar esta equipa?')"
                                           class="ml-2 text-danger">
                                           <i class="fa fa-trash" title="Apagar"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4">Nenhuma equipa registada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

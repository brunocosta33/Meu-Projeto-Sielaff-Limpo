@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Agendamentos Técnicos</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.technical_schedules.create') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-plus"></i> Novo Agendamento Técnico
                    </a>
                </div>

                <h5 class="card-title">Lista de Agendamentos Técnicos</h5>

                

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pedido</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Estado</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($technical_schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->id }}</td>
                                    <td>#{{ $schedule->technicalRequest->id ?? '-' }} - {{ $schedule->technicalRequest->origem ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($schedule->data)->format('d/m/Y') }}</td>
                                    <td>{{ $schedule->hora }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($schedule->estado) {
                                                'Concluído' => 'badge-success',
                                                'Cancelado' => 'badge-danger',
                                                default => 'badge-primary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $schedule->estado }}</span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('backoffice.technical_schedules.show', $schedule->id) }}" class="ml-2 text-info">
                                            <i class="fa fa-eye" title="Ver Detalhes"></i>
                                        </a>
                                        <a href="{{ route('backoffice.technical_schedules.edit', $schedule->id) }}" class="ml-2">
                                            <i class="fa fa-edit" title="Editar"></i>
                                        </a>
                                        <a href="{{ route('backoffice.technical_schedules.delete', $schedule->id) }}"
                                           onclick="return confirm('Tem a certeza que deseja apagar este agendamento técnico?')"
                                           class="ml-2 text-danger">
                                            <i class="fa fa-trash" title="Apagar"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">Nenhum agendamento técnico registado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $technical_schedules->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

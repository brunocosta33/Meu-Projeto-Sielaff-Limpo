@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Detalhes do Agendamento</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Detalhes do Agendamento</h5>

                <table class="table table-bordered">
                    <tr>
                        <th>ID</th>
                        <td>{{ $appointment->id }}</td>
                    </tr>
                    <tr>
                        <th>Loja</th>
                        <td>{{ $appointment->store->nome_loja ?? '-' }} ({{ $appointment->store->codigo_loja ?? '-' }})</td>
                    </tr>
                    <tr>
                        <th>Transportadora</th>
                        <td>{{ $appointment->supplier->nome ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Hora Agendada</th>
                        <td>{{ $appointment->scheduled_time }}</td>
                    </tr>
                    <tr>
                        <th>Data Agendada</th>
                        <td>{{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Tipo de Serviço</th>
                        <td>{{ $appointment->tipo_servico }}</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            @php
                                $badgeClass = match($appointment->status) {
                                    'Concluído' => 'badge-success',
                                    'Cancelado' => 'badge-danger',
                                    default => 'badge-primary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $appointment->status }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Observações</th>
                        <td>{{ $appointment->observacoes ?? '—' }}</td>
                    </tr>
                </table>

                <div class="mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.appointments.index', ['page' => request('page')]) }}">
                        <i class="fa fa-arrow-left"></i> Voltar à Lista
                    </a>
                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.appointments.edit', ['id' => $appointment->id, 'page' => request('page')]) }}">
                        <i class="fa fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

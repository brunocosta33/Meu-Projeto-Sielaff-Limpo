@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Agendamentos</title>
@endsection
@push('styles')
    <style>
        .week-0 { background-color: #f8f9fa !important; }
        .week-1 { background-color: #e3f2fd !important; }
        .week-2 { background-color: #f1f8e9 !important; }
        .week-3 { background-color: #fff3e0 !important; }
        .week-4 { background-color: #fce4ec !important; }
        .week-5 { background-color: #ede7f6 !important; }
    </style>
@endpush


@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.appointments.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> Novo Agendamento
                    </a>
                </div>

                <h5 class="card-title">Lista de Agendamentos</h5>
                <form method="GET" action="{{ route('backoffice.appointments.index') }}" class="mb-4">
                <div class="form-row">
                    <div class="col">
                        <input type="text" name="codigo_loja" class="form-control" placeholder="Código da Loja"
                            value="{{ request('codigo_loja') }}">
                    </div>
                    <div class="col">
                        <input type="text" name="nome_loja" class="form-control" placeholder="Nome da Loja"
                            value="{{ request('nome_loja') }}">
                    </div>
                    <div class="col">
                        <input type="text" name="transportadora" class="form-control" placeholder="Transportadora"
                            value="{{ request('transportadora') }}">
                    </div>
                    <div class="col">
                        <input type="date" name="data" class="form-control" value="{{ request('data') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fa fa-search"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('backoffice.appointments.index') }}" class="btn btn-outline-secondary">
                            Limpar
                        </a>
                    </div>
                </div>
            </form>


                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Código Loja</th>
                                <th>Loja</th>
                                <th>Transportadora</th>
                                <th>Hora</th>
                                <th>Data</th>
                                <th>Tipo de Serviço</th>
                                <th>Estado</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                <tr>
                                    @php
                                        $date = \Carbon\Carbon::parse($appointment->scheduled_date);
                                        $weekNumber = $date->week;
                                        $colorClass = 'week-' . ($weekNumber % 6);
                                    @endphp

                                    <td class="{{ $colorClass }}">{{ $appointment->store->codigo_loja ?? '-' }}</td>
                                    <td class="{{ $colorClass }}">{{ $appointment->store->nome_loja ?? '-' }}</td>
                                    <td class="{{ $colorClass }}">{{ $appointment->supplier->nome ?? '-' }}</td>
                                    <td class="{{ $colorClass }}">{{ $appointment->scheduled_time }}</td>
                                   <td class="{{ $colorClass }}">
                                        {{ $date->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">
                                            {{ ucfirst($date->translatedFormat('l')) }} | CW{{ $weekNumber }}
                                        </small>
                                    </td>
                                    <td>{{ $appointment->tipo_servico }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($appointment->status) {
                                                'Concluído' => 'badge-success',
                                                'Cancelado' => 'badge-danger',
                                                default => 'badge-primary',
                                            };
                                        @endphp

                                        <span class="badge {{ $badgeClass }}">{{ $appointment->status }}</span>

                                        @if (!empty($appointment->observacoes))
                                            <br>
                                           <span class="badge badge-warning mt-1" title="Este agendamento tem observações.">
                                                <i class="fa fa-sticky-note"></i> Observações
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('backoffice.appointments.show', $appointment->id) }}" class="ml-2" title="Ver Detalhes">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('backoffice.appointments.edit', $appointment->id) }}" class="ml-2" title="Editar">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="{{ route('backoffice.appointments.delete', $appointment->id) }}"
                                            onclick="return confirm('Tem a certeza que deseja apagar este agendamento?')"
                                            class="ml-2 text-danger" title="Apagar">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8">Nenhum agendamento registado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $appointments->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

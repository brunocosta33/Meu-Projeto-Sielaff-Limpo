@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Agendamentos') }}</title>
@endsection

@push('styles')
    <style>
        .week-0 { background-color: #f8f9fa !important; }
        .week-1 { background-color: #e3f2fd !important; }
        .week-2 { background-color: #f1f8e9 !important; }
        .week-3 { background-color: #fff3e0 !important; }
        .week-4 { background-color: #fce4ec !important; }
        .week-5 { background-color: #ede7f6 !important; }
        .today-row { background-color: #ffebee !important; color: #c62828 !important; font-weight: bold; }
    </style>
@endpush

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.appointments.create', ['page' => request('page')]) }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> {{ __('Novo Agendamento') }}
                    </a>
                </div>

                <h5 class="card-title">{{ __('Lista de Agendamentos') }}</h5>

                <form method="GET" action="{{ route('backoffice.appointments.index') }}" class="mb-4">
                    <div class="form-row">
                        <div class="col">
                            <input type="text" name="codigo_loja" class="form-control" placeholder="{{ __('Código da Loja') }}"
                                value="{{ request('codigo_loja') }}">
                        </div>
                        <div class="col">
                            <input type="text" name="nome_loja" class="form-control" placeholder="{{ __('Nome da Loja') }}"
                                value="{{ request('nome_loja') }}">
                        </div>
                        <div class="col">
                            <input type="text" name="transportadora" class="form-control" placeholder="{{ __('Transportadora') }}"
                                value="{{ request('transportadora') }}">
                        </div>
                        <div class="col">
                            <input type="date" name="data" class="form-control" value="{{ request('data') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fa fa-search"></i> {{ __('Filtrar') }}
                            </button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('backoffice.appointments.index') }}" class="btn btn-outline-secondary">
                                {{ __('Limpar') }}
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Código Loja') }}</th>
                                <th>{{ __('Loja') }}</th>
                                <th>{{ __('Transportadora') }}</th>
                                <th>{{ __('Hora') }}</th>
                                <th>{{ __('Data') }}</th>
                                <th>{{ __('Tipo de Serviço') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th class="text-right">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                @php
                                    $date = \Carbon\Carbon::parse($appointment->scheduled_date);
                                    $weekNumber = $date->week;
                                    $colorClass = 'week-' . ($weekNumber % 6);
                                    $isToday = $date->isToday();
                                @endphp
                                <tr class="{{ $isToday ? 'today-row' : '' }}">
                                    <td class="{{ $colorClass }}">{{ $appointment->store->codigo_loja ?? '-' }}</td>
                                    <td class="{{ $colorClass }}">{{ $appointment->store->nome_loja ?? '-' }}</td>
                                    <td class="{{ $colorClass }}">{{ $appointment->supplier->nome ?? '-' }}</td>
                                    <td class="{{ $colorClass }}">{{ $appointment->scheduled_time }}</td>
                                    <td class="{{ $colorClass }}">
                                        {{ $date->format('d/m/Y') }}
                                        @if($isToday)
                                            <br>
                                            <span class="badge badge-danger">{{ __('Hoje') }}</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            {{ ucfirst($date->translatedFormat('l')) }} | CW{{ $weekNumber }}
                                        </small>
                                    </td>
                                    <td>{{ __($appointment->tipo_servico) }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($appointment->status) {
                                                'Concluído' => 'badge-success',
                                                'Cancelado' => 'badge-danger',
                                                default => 'badge-primary',
                                            };
                                        @endphp

                                        <span class="badge {{ $badgeClass }}">{{ __($appointment->status) }}</span>

                                        @if (!empty($appointment->observacoes))
                                            <br>
                                            <span class="badge badge-warning mt-1" title="{{ __('Este agendamento tem observações.') }}">
                                                <i class="fa fa-sticky-note"></i> {{ __('Observações') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('backoffice.appointments.show', ['id' => $appointment->id, 'page' => request('page')]) }}" class="ml-2" title="{{ __('Ver Detalhes') }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('backoffice.appointments.edit', ['id' => $appointment->id, 'page' => request('page')]) }}" class="ml-2" title="{{ __('Editar') }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="{{ route('backoffice.appointments.delete', $appointment->id) }}"
                                               onclick="return confirm('{{ __('Tem a certeza que deseja apagar este agendamento?') }}')"
                                               class="ml-2 text-danger" title="{{ __('Apagar') }}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8">{{ __('Nenhum agendamento registado.') }}</td></tr>
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

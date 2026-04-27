@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Agendamentos') }}</title>
@endsection

@push('styles')
    <style>
        .logistics-page {
            --logistics-ink: #172033;
            --logistics-muted: #6b7280;
            --logistics-border: #dfe7f1;
            --logistics-soft: #f7f9fc;
            --logistics-primary: #1557b0;
            --logistics-red: #dc3545;
        }

        .logistics-hero,
        .logistics-panel,
        .logistics-stat {
            border: 1px solid var(--logistics-border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(23, 32, 51, 0.06);
        }

        .logistics-hero {
            padding: 22px 24px;
        }

        .logistics-title {
            color: var(--logistics-ink);
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .logistics-copy {
            color: var(--logistics-muted);
            margin-bottom: 0;
            max-width: 680px;
            line-height: 1.55;
        }

        .logistics-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .logistics-stat {
            padding: 16px;
            height: 100%;
        }

        .logistics-stat-label {
            color: var(--logistics-muted);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 8px;
        }

        .logistics-stat-value {
            color: var(--logistics-ink);
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
        }

        .logistics-filter {
            background: var(--logistics-soft);
            border: 1px solid var(--logistics-border);
            border-radius: 8px;
            padding: 16px;
        }

        .logistics-filter label {
            color: var(--logistics-ink);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .logistics-filter .form-control {
            min-height: 42px;
        }

        .logistics-table {
            margin-bottom: 0;
        }

        .logistics-table thead th {
            border-top: 0;
            border-bottom: 1px solid var(--logistics-border);
            background: #f8fafc;
            color: #4b5563;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            vertical-align: middle;
        }

        .logistics-table tbody td {
            vertical-align: middle;
            border-top: 1px solid #edf2f7;
        }

        .logistics-table {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .logistics-table tbody tr:not(.logistics-week-heading) td:first-child {
            border-left-width: 5px;
            border-left-style: solid;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .logistics-table tbody tr:not(.logistics-week-heading) td:last-child {
            border-right-width: 3px;
            border-right-style: solid;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .logistics-table tbody tr:not(.logistics-week-heading) td {
            border-top-width: 3px;
            border-top-style: solid;
            border-bottom-width: 3px;
            border-bottom-style: solid;
        }

        .logistics-table tbody tr:not(.logistics-week-heading) {
            filter: drop-shadow(0 6px 12px rgba(23, 32, 51, 0.05));
        }

        .logistics-table tbody tr:not(.logistics-week-heading):hover td {
            background: #fbfdff;
        }

        .logistics-week-heading td {
            border-top: 0;
            background: #fff;
            padding: 18px 0 8px;
        }

        .logistics-week-heading:first-child td {
            padding-top: 0;
        }

        .logistics-week-band {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid var(--logistics-border);
            border-radius: 8px;
            padding: 10px 12px;
            background: #f8fafc;
        }

        .logistics-week-name {
            color: var(--logistics-ink);
            font-weight: 800;
        }

        .logistics-week-range {
            color: var(--logistics-muted);
            font-size: 0.86rem;
            font-weight: 600;
        }

        .logistics-week-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.28rem 0.62rem;
            background: #fff;
            color: var(--logistics-primary);
            border: 1px solid #d8e7ff;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .logistics-week-band.logistics-week-0 { border-left: 5px solid #94a3b8; }
        .logistics-week-band.logistics-week-1 { border-left: 5px solid #60a5fa; }
        .logistics-week-band.logistics-week-2 { border-left: 5px solid #86efac; }
        .logistics-week-band.logistics-week-3 { border-left: 5px solid #fbbf24; }
        .logistics-week-band.logistics-week-4 { border-left: 5px solid #f9a8d4; }
        .logistics-week-band.logistics-week-5 { border-left: 5px solid #a78bfa; }

        .logistics-table tbody tr.logistics-week-0 td {
            background: #fbfcfe;
            border-color: #94a3b8;
        }

        .logistics-table tbody tr.logistics-week-1 td {
            background: #f6fbff;
            border-color: #60a5fa;
        }

        .logistics-table tbody tr.logistics-week-2 td {
            background: #f8fdf9;
            border-color: #86efac;
        }

        .logistics-table tbody tr.logistics-week-3 td {
            background: #fffdf5;
            border-color: #fbbf24;
        }

        .logistics-table tbody tr.logistics-week-4 td {
            background: #fff8fb;
            border-color: #f9a8d4;
        }

        .logistics-table tbody tr.logistics-week-5 td {
            background: #fbf9ff;
            border-color: #a78bfa;
        }

        .logistics-table tbody tr.logistics-today-row {
            box-shadow: none;
        }

        .logistics-table tbody tr.logistics-today-row td {
            border-color: var(--logistics-red);
            background: #fffafa;
        }

        .logistics-store-code {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.55rem;
            border-radius: 6px;
            background: #edf4ff;
            color: var(--logistics-primary);
            font-weight: 800;
        }

        .logistics-date {
            color: var(--logistics-ink);
            font-weight: 800;
        }

        .logistics-muted {
            color: var(--logistics-muted);
            font-size: 0.82rem;
        }

        .logistics-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.38rem 0.62rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .logistics-status.scheduled {
            background: #eaf2ff;
            color: #1557b0;
        }

        .logistics-status.done {
            background: #e8f6ee;
            color: #146c43;
        }

        .logistics-status.cancelled {
            background: #fdecec;
            color: #b02a37;
        }

        .logistics-pdf-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 8px;
        }

        .logistics-action-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid #d8e2ef;
            color: #475569;
            background: #fff;
        }

        .logistics-action-btn:hover {
            background: #f8fafc;
            color: var(--logistics-primary);
            text-decoration: none;
        }

        .logistics-action-btn.danger:hover {
            color: var(--logistics-red);
            border-color: #f3b5bd;
            background: #fff5f6;
        }

        @media (max-width: 767.98px) {
            .logistics-actions {
                justify-content: flex-start;
            }

            .logistics-table thead {
                display: none;
            }

            .logistics-table tbody tr {
                display: block;
                border: 1px solid var(--logistics-border);
                border-radius: 8px;
                margin-bottom: 12px;
                overflow: hidden;
            }

            .logistics-table tbody td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                border-top: 1px solid #edf2f7;
            }

            .logistics-table tbody td::before {
                content: attr(data-label);
                color: var(--logistics-muted);
                font-weight: 700;
            }

            .logistics-week-heading td {
                display: block;
                padding: 12px 0 8px;
            }

            .logistics-week-heading td::before {
                content: none;
            }

            .logistics-week-band {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
<div class="row">@include('flash::message')</div>

<div class="logistics-page">
    <div class="row mb-4">
        <div class="col-12">
            <div class="logistics-hero">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div>
                        <h3 class="logistics-title">{{ __('Agendamento de Transporte') }}</h3>
                        <p class="logistics-copy">{{ __('Acompanhe entregas e levantamentos por loja, transportadora, data, estado e documentação associada.') }}</p>
                    </div>
                    <div class="logistics-actions mt-3 mt-lg-0">
                        <a href="{{ route('backoffice.appointments.create', ['page' => request('page')]) }}" class="btn btn-primary">
                            <i class="fa fa-plus mr-1"></i>{{ __('Novo Agendamento') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="logistics-stat">
                <div class="logistics-stat-label">{{ __('Total filtrado') }}</div>
                <div class="logistics-stat-value">{{ $summary['total'] ?? $appointments->total() }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="logistics-stat">
                <div class="logistics-stat-label">{{ __('Hoje') }}</div>
                <div class="logistics-stat-value">{{ $summary['today'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="logistics-stat">
                <div class="logistics-stat-label">{{ __('Próximos 7 dias') }}</div>
                <div class="logistics-stat-value">{{ $summary['next_7_days'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="logistics-stat">
                <div class="logistics-stat-label">{{ __('Agendados') }}</div>
                <div class="logistics-stat-value">{{ $summary['scheduled'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="logistics-stat">
                <div class="logistics-stat-label">{{ __('Concluídos') }}</div>
                <div class="logistics-stat-value">{{ $summary['completed'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="logistics-stat">
                <div class="logistics-stat-label">{{ __('Cancelados') }}</div>
                <div class="logistics-stat-value">{{ $summary['cancelled'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="logistics-panel">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                        <div>
                            <h5 class="mb-1">{{ __('Lista de Agendamentos') }}</h5>
                            <p class="text-muted mb-0">{{ __('Use os filtros para encontrar rapidamente uma loja, transportadora ou data específica.') }}</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('backoffice.appointments.index') }}" class="logistics-filter mb-4">
                        <div class="form-row align-items-end">
                            <div class="col-md-3 mb-3">
                                <label>{{ __('Código da Loja') }}</label>
                                <input type="text" name="codigo_loja" class="form-control" placeholder="{{ __('Ex: 123') }}" value="{{ request('codigo_loja') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>{{ __('Nome da Loja') }}</label>
                                <input type="text" name="nome_loja" class="form-control" placeholder="{{ __('Ex: Loja Central') }}" value="{{ request('nome_loja') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>{{ __('Transportadora') }}</label>
                                <input type="text" name="transportadora" class="form-control" placeholder="{{ __('Nome da transportadora') }}" value="{{ request('transportadora') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>{{ __('Data') }}</label>
                                <input type="date" name="data" class="form-control" value="{{ request('data') }}">
                            </div>
                            <div class="col-12 d-flex flex-wrap justify-content-end">
                                <button type="submit" class="btn btn-primary mr-2 mb-2">
                                    <i class="fa fa-search mr-1"></i>{{ __('Filtrar') }}
                                </button>
                                <a href="{{ route('backoffice.appointments.index') }}" class="btn btn-outline-secondary mb-2">
                                    <i class="fa fa-undo mr-1"></i>{{ __('Limpar') }}
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table logistics-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Loja') }}</th>
                                    <th>{{ __('Transportadora') }}</th>
                                    <th>{{ __('Data e hora') }}</th>
                                    <th>{{ __('Serviço') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th>{{ __('Documentos') }}</th>
                                    <th class="text-right">{{ __('Ações') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedAppointments = $appointments->getCollection()->groupBy(function ($appointment) {
                                        return \Carbon\Carbon::parse($appointment->scheduled_date)
                                            ->timezone('Europe/Lisbon')
                                            ->format('o-W');
                                    });
                                @endphp

                                @forelse($groupedAppointments as $weekKey => $weekAppointments)
                                    @php
                                        $firstDate = \Carbon\Carbon::parse($weekAppointments->first()->scheduled_date)->timezone('Europe/Lisbon');
                                        $weekNumber = $firstDate->week;
                                        $weekColorClass = 'logistics-week-' . ($weekNumber % 6);
                                        $weekStart = $firstDate->copy()->startOfWeek();
                                        $weekEnd = $firstDate->copy()->endOfWeek();
                                    @endphp
                                    <tr class="logistics-week-heading">
                                        <td colspan="7">
                                            <div class="logistics-week-band {{ $weekColorClass }}">
                                                <div>
                                                    <div class="logistics-week-name">{{ __('Semana') }} {{ $weekNumber }}</div>
                                                    <div class="logistics-week-range">
                                                        {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}
                                                    </div>
                                                </div>
                                                <span class="logistics-week-count">
                                                    {{ $weekAppointments->count() }} {{ $weekAppointments->count() === 1 ? __('agendamento') : __('agendamentos') }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    @foreach($weekAppointments as $appointment)
                                        @php
                                            $date = \Carbon\Carbon::parse($appointment->scheduled_date)->timezone('Europe/Lisbon');
                                            $weekNumber = $date->week;
                                            $isToday = $date->toDateString() === \Carbon\Carbon::today('Europe/Lisbon')->toDateString();
                                            $statusClass = match($appointment->status) {
                                                'Concluído' => 'done',
                                                'Cancelado' => 'cancelled',
                                                default => 'scheduled',
                                            };
                                        @endphp
                                        <tr class="logistics-week-{{ $weekNumber % 6 }} {{ $isToday ? 'logistics-today-row' : '' }}">
                                            <td class="logistics-week-cell" data-label="{{ __('Loja') }}">
                                                <span class="logistics-store-code">{{ $appointment->store->codigo_loja ?? '-' }}</span>
                                                <div class="mt-2 font-weight-bold">{{ $appointment->store->nome_loja ?? '-' }}</div>
                                            </td>
                                            <td class="logistics-week-cell" data-label="{{ __('Transportadora') }}">
                                                <div class="font-weight-bold">{{ $appointment->supplier->nome ?? '-' }}</div>
                                            </td>
                                            <td class="logistics-week-cell" data-label="{{ __('Data e hora') }}">
                                                <div class="logistics-date">
                                                    {{ $date->format('d/m/Y') }}
                                                    @if($isToday)
                                                        <span class="badge badge-danger ml-1">{{ __('Hoje') }}</span>
                                                    @endif
                                                </div>
                                                <div class="logistics-muted">
                                                    <i class="fa fa-clock mr-1"></i>{{ $appointment->scheduled_time }}
                                                </div>
                                                <div class="logistics-muted">
                                                    {{ ucfirst($date->translatedFormat('l')) }} | CW{{ $weekNumber }}
                                                </div>
                                            </td>
                                            <td data-label="{{ __('Serviço') }}">{{ __($appointment->tipo_servico) }}</td>
                                            <td data-label="{{ __('Estado') }}">
                                                <span class="logistics-status {{ $statusClass }}">
                                                    {{ __($appointment->status) }}
                                                </span>
                                                @if (!empty($appointment->observacoes))
                                                    <div class="mt-2">
                                                        <span class="badge badge-warning" title="{{ __('Este agendamento tem observações.') }}">
                                                            <i class="fa fa-sticky-note mr-1"></i>{{ __('Observações') }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('Documentos') }}">
                                                @if($appointment->files && $appointment->files->count() > 0)
                                                    <div class="logistics-pdf-list">
                                                        @foreach($appointment->files as $pdf)
                                                            <a href="{{ asset('storage/' . $pdf->file_path) }}" target="_blank" class="badge badge-info" title="{{ __('Abrir PDF:') }} {{ $pdf->file_name }}">
                                                                <i class="fa fa-file-pdf mr-1"></i>{{ __('PDF') }} {{ strtok($pdf->file_name, ' ') }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('Ações') }}" class="text-right">
                                                <div class="d-flex justify-content-end">
                                                    <a href="{{ route('backoffice.appointments.show', ['id' => $appointment->id, 'page' => request('page')]) }}" class="logistics-action-btn mr-2" title="{{ __('Ver Detalhes') }}" aria-label="{{ __('Ver Detalhes') }}">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('backoffice.appointments.edit', ['id' => $appointment->id, 'page' => request('page')]) }}" class="logistics-action-btn mr-2" title="{{ __('Editar') }}" aria-label="{{ __('Editar') }}">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('backoffice.appointments.delete', $appointment->id) }}"
                                                       onclick="return confirm('{{ __('Tem a certeza que deseja apagar este agendamento?') }}')"
                                                       class="logistics-action-btn danger" title="{{ __('Apagar') }}" aria-label="{{ __('Apagar') }}">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            {{ __('Nenhum agendamento registado.') }}
                                        </td>
                                    </tr>
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
</div>
@endsection

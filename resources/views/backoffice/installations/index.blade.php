@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Instalações') }}</title>
@endsection

@push('styles')
    <style>
        .installations-page {
            --installations-ink: #172033;
            --installations-muted: #6b7280;
            --installations-border: #dfe7f1;
            --installations-soft: #f7f9fc;
            --installations-primary: #1557b0;
            --installations-green: #198754;
            --installations-red: #dc3545;
            --installations-yellow: #ffc107;
        }

        .installations-hero,
        .installations-panel,
        .installations-stat {
            border: 1px solid var(--installations-border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(23, 32, 51, 0.06);
        }

        .installations-hero {
            padding: 22px 24px;
        }

        .installations-title {
            color: var(--installations-ink);
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .installations-copy {
            color: var(--installations-muted);
            margin-bottom: 0;
            max-width: 680px;
            line-height: 1.55;
        }

        .installations-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .installations-stat {
            padding: 16px;
            height: 100%;
        }

        .installations-stat-label {
            color: var(--installations-muted);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 8px;
        }

        .installations-stat-value {
            color: var(--installations-ink);
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
        }

        .installations-filter {
            background: var(--installations-soft);
            border: 1px solid var(--installations-border);
            border-radius: 8px;
            padding: 16px;
        }

        .installations-filter label {
            color: var(--installations-ink);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .installations-filter .form-control {
            min-height: 42px;
        }

        .installations-table {
            margin-bottom: 0;
        }

        .installations-table thead th {
            border-top: 0;
            border-bottom: 1px solid var(--installations-border);
            background: #f8fafc;
            color: #4b5563;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            vertical-align: middle;
        }

        .installations-table tbody td {
            vertical-align: middle;
            border-top: 1px solid #edf2f7;
        }

        .installations-table {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .installations-table tbody tr:not(.installation-week-heading) td:first-child {
            border-left-width: 5px;
            border-left-style: solid;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .installations-table tbody tr:not(.installation-week-heading) td:last-child {
            border-right-width: 3px;
            border-right-style: solid;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .installations-table tbody tr:not(.installation-week-heading) td {
            border-top-width: 3px;
            border-top-style: solid;
            border-bottom-width: 3px;
            border-bottom-style: solid;
        }

        .installations-table tbody tr:not(.installation-week-heading) {
            filter: drop-shadow(0 6px 12px rgba(23, 32, 51, 0.05));
        }

        .installations-table tbody tr:not(.installation-week-heading):hover td {
            background: #fbfdff;
        }

        .installation-week-heading td {
            border-top: 0;
            background: #fff;
            padding: 18px 0 8px;
        }

        .installation-week-heading:first-child td {
            padding-top: 0;
        }

        .installation-week-band {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid var(--installations-border);
            border-radius: 8px;
            padding: 10px 12px;
            background: #f8fafc;
        }

        .installation-week-name {
            color: var(--installations-ink);
            font-weight: 800;
        }

        .installation-week-range {
            color: var(--installations-muted);
            font-size: 0.86rem;
            font-weight: 600;
        }

        .installation-week-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.28rem 0.62rem;
            background: #fff;
            color: var(--installations-primary);
            border: 1px solid #d8e7ff;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .installation-week-band.installation-week-0 { border-left: 5px solid #94a3b8; }
        .installation-week-band.installation-week-1 { border-left: 5px solid #60a5fa; }
        .installation-week-band.installation-week-2 { border-left: 5px solid #86efac; }
        .installation-week-band.installation-week-3 { border-left: 5px solid #fbbf24; }
        .installation-week-band.installation-week-4 { border-left: 5px solid #f9a8d4; }
        .installation-week-band.installation-week-5 { border-left: 5px solid #a78bfa; }

        .installation-store-code {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.55rem;
            border-radius: 6px;
            background: #edf4ff;
            color: var(--installations-primary);
            font-weight: 800;
        }

        .installation-date {
            color: var(--installations-ink);
            font-weight: 800;
        }

        .installation-muted {
            color: var(--installations-muted);
            font-size: 0.82rem;
        }

        .installation-today-row {
            background: #fff7f7;
        }

        .installations-table tbody tr.installation-week-0 td {
            background: #fbfcfe;
            border-color: #94a3b8;
        }

        .installations-table tbody tr.installation-week-1 td {
            background: #f6fbff;
            border-color: #60a5fa;
        }

        .installations-table tbody tr.installation-week-2 td {
            background: #f8fdf9;
            border-color: #86efac;
        }

        .installations-table tbody tr.installation-week-3 td {
            background: #fffdf5;
            border-color: #fbbf24;
        }

        .installations-table tbody tr.installation-week-4 td {
            background: #fff8fb;
            border-color: #f9a8d4;
        }

        .installations-table tbody tr.installation-week-5 td {
            background: #fbf9ff;
            border-color: #a78bfa;
        }

        .installations-table tbody tr.installation-today-row {
            box-shadow: none;
        }

        .installations-table tbody tr.installation-today-row td {
            border-color: var(--installations-red);
            background: #fffafa;
        }

        .installation-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.38rem 0.62rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .installation-status.scheduled {
            background: #eaf2ff;
            color: #1557b0;
        }

        .installation-status.done {
            background: #e8f6ee;
            color: #146c43;
        }

        .installation-status.cancelled {
            background: #fdecec;
            color: #b02a37;
        }

        .installation-pdf-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 8px;
        }

        .installation-action-btn {
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

        .installation-action-btn:hover {
            background: #f8fafc;
            color: var(--installations-primary);
            text-decoration: none;
        }

        .installation-action-btn.danger:hover {
            color: var(--installations-red);
            border-color: #f3b5bd;
            background: #fff5f6;
        }

        @media (max-width: 767.98px) {
            .installations-actions {
                justify-content: flex-start;
            }

            .installations-table thead {
                display: none;
            }

            .installations-table tbody tr {
                display: block;
                border: 1px solid var(--installations-border);
                border-radius: 8px;
                margin-bottom: 12px;
                overflow: hidden;
            }

            .installations-table tbody td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                border-top: 1px solid #edf2f7;
            }

            .installations-table tbody td::before {
                content: attr(data-label);
                color: var(--installations-muted);
                font-weight: 700;
            }

            .installation-week-heading td {
                display: block;
                padding: 12px 0 8px;
            }

            .installation-week-heading td::before {
                content: none;
            }

            .installation-week-band {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
<div class="row">@include('flash::message')</div>

<div class="installations-page">
    <div class="row mb-4">
        <div class="col-12">
            <div class="installations-hero">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div>
                        <h3 class="installations-title">{{ __('Instalações') }}</h3>
                        <p class="installations-copy">{{ __('Acompanhe instalações e desinstalações por loja, equipa técnica, data, estado e documentação associada.') }}</p>
                    </div>
                    <div class="installations-actions mt-3 mt-lg-0">
                        <a href="{{ route('backoffice.installations.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus mr-1"></i>{{ __('Nova Instalação') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="installations-stat">
                <div class="installations-stat-label">{{ __('Total filtrado') }}</div>
                <div class="installations-stat-value">{{ $summary['total'] ?? $installations->total() }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="installations-stat">
                <div class="installations-stat-label">{{ __('Hoje') }}</div>
                <div class="installations-stat-value">{{ $summary['today'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="installations-stat">
                <div class="installations-stat-label">{{ __('Próximos 7 dias') }}</div>
                <div class="installations-stat-value">{{ $summary['next_7_days'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="installations-stat">
                <div class="installations-stat-label">{{ __('Agendadas') }}</div>
                <div class="installations-stat-value">{{ $summary['scheduled'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="installations-stat">
                <div class="installations-stat-label">{{ __('Concluídas') }}</div>
                <div class="installations-stat-value">{{ $summary['completed'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-3">
            <div class="installations-stat">
                <div class="installations-stat-label">{{ __('Canceladas') }}</div>
                <div class="installations-stat-value">{{ $summary['cancelled'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="installations-panel">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                        <div>
                            <h5 class="mb-1">{{ __('Lista de Instalações') }}</h5>
                            <p class="text-muted mb-0">{{ __('Use os filtros para encontrar rapidamente uma loja, equipa ou data específica.') }}</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('backoffice.installations.index') }}" class="installations-filter mb-4">
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
                                <label>{{ __('Equipa Técnica') }}</label>
                                <input type="text" name="team" class="form-control" placeholder="{{ __('Nome da equipa') }}" value="{{ request('team') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>{{ __('Data') }}</label>
                                <input type="date" name="data" class="form-control" value="{{ request('data') }}">
                            </div>
                            <div class="col-12 d-flex flex-wrap justify-content-end">
                                <button type="submit" class="btn btn-primary mr-2 mb-2">
                                    <i class="fa fa-search mr-1"></i>{{ __('Filtrar') }}
                                </button>
                                <a href="{{ route('backoffice.installations.index') }}" class="btn btn-outline-secondary mb-2">
                                    <i class="fa fa-undo mr-1"></i>{{ __('Limpar') }}
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table installations-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Loja') }}</th>
                                    <th>{{ __('Equipa Técnica') }}</th>
                                    <th>{{ __('Data e hora') }}</th>
                                    <th>{{ __('Serviço') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th>{{ __('Documentos') }}</th>
                                    <th class="text-right">{{ __('Ações') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedInstallations = $installations->getCollection()->groupBy(function ($installation) {
                                        return \Carbon\Carbon::parse($installation->scheduled_date)
                                            ->timezone('Europe/Lisbon')
                                            ->format('o-W');
                                    });
                                @endphp

                                @forelse($groupedInstallations as $weekKey => $weekInstallations)
                                    @php
                                        $firstDate = \Carbon\Carbon::parse($weekInstallations->first()->scheduled_date)->timezone('Europe/Lisbon');
                                        $weekNumber = $firstDate->week;
                                        $weekColorClass = 'installation-week-' . ($weekNumber % 6);
                                        $weekStart = $firstDate->copy()->startOfWeek();
                                        $weekEnd = $firstDate->copy()->endOfWeek();
                                    @endphp
                                    <tr class="installation-week-heading">
                                        <td colspan="7">
                                            <div class="installation-week-band {{ $weekColorClass }}">
                                                <div>
                                                    <div class="installation-week-name">{{ __('Semana') }} {{ $weekNumber }}</div>
                                                    <div class="installation-week-range">
                                                        {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}
                                                    </div>
                                                </div>
                                                <span class="installation-week-count">
                                                    {{ $weekInstallations->count() }} {{ $weekInstallations->count() === 1 ? __('instalação') : __('instalações') }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    @foreach($weekInstallations as $installation)
                                    @php
                                        $date = \Carbon\Carbon::parse($installation->scheduled_date)->timezone('Europe/Lisbon');
                                        $weekNumber = $date->week;
                                        $isToday = $date->toDateString() === \Carbon\Carbon::today('Europe/Lisbon')->toDateString();
                                        $statusClass = match($installation->status) {
                                            'Concluído' => 'done',
                                            'Cancelado' => 'cancelled',
                                            default => 'scheduled',
                                        };
                                    @endphp
                                    <tr class="installation-week-{{ $weekNumber % 6 }} {{ $isToday ? 'installation-today-row' : '' }}">
                                        <td class="installation-week-cell" data-label="{{ __('Loja') }}">
                                            <span class="installation-store-code">{{ $installation->store->codigo_loja ?? '-' }}</span>
                                            <div class="mt-2 font-weight-bold">{{ $installation->store->nome_loja ?? '-' }}</div>
                                        </td>
                                        <td class="installation-week-cell" data-label="{{ __('Equipa Técnica') }}">
                                            <div class="font-weight-bold">{{ $installation->team->nome ?? '-' }}</div>
                                        </td>
                                        <td class="installation-week-cell" data-label="{{ __('Data e hora') }}">
                                            <div class="installation-date">
                                                {{ $date->format('d/m/Y') }}
                                                @if($isToday)
                                                    <span class="badge badge-danger ml-1">{{ __('Hoje') }}</span>
                                                @endif
                                            </div>
                                            <div class="installation-muted">
                                                <i class="fa fa-clock mr-1"></i>{{ $installation->scheduled_time }}
                                            </div>
                                            <div class="installation-muted">
                                                {{ ucfirst($date->translatedFormat('l')) }} | CW{{ $weekNumber }}
                                            </div>
                                        </td>
                                        <td data-label="{{ __('Serviço') }}">{{ $installation->tipo_servico }}</td>
                                        <td data-label="{{ __('Estado') }}">
                                            <span class="installation-status {{ $statusClass }}">
                                                {{ $installation->status }}
                                            </span>
                                            @if (!empty($installation->observacoes))
                                                <div class="mt-2">
                                                    <span class="badge badge-warning" title="{{ __('Este registo tem observações.') }}">
                                                        <i class="fa fa-sticky-note mr-1"></i>{{ __('Observações') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td data-label="{{ __('Documentos') }}">
                                            @if($installation->pdfs && count($installation->pdfs) > 0)
                                                <div class="installation-pdf-list">
                                                    @foreach($installation->pdfs as $pdf)
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
                                                <a href="{{ route('backoffice.installations.show', ['installation' => $installation->id, 'page' => request('page')]) }}" class="installation-action-btn mr-2" title="{{ __('Ver Detalhes') }}" aria-label="{{ __('Ver Detalhes') }}">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('backoffice.installations.edit', ['installation' => $installation->id, 'page' => request('page')]) }}" class="installation-action-btn mr-2" title="{{ __('Editar') }}" aria-label="{{ __('Editar') }}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="{{ route('backoffice.installations.delete', $installation->id) }}"
                                                   onclick="return confirm('{{ __('Tem a certeza que deseja apagar esta instalação?') }}')"
                                                   class="installation-action-btn danger" title="{{ __('Apagar') }}" aria-label="{{ __('Apagar') }}">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            {{ __('Nenhuma instalação registada.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $installations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

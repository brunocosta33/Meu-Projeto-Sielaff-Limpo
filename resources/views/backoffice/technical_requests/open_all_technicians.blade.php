@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Todos os pedidos em aberto') }}</title>
@endsection

@section('head-scripts')
<style>
    .open-tech-page {
        --open-ink: #132238;
        --open-muted: #6c7a89;
        --open-border: #dfe7f1;
        --open-primary: #0b5ed7;
    }

    .open-tech-hero,
    .open-tech-panel {
        background: #fff;
        border: 1px solid var(--open-border);
        border-radius: 22px;
        box-shadow: 0 18px 40px rgba(19, 34, 56, 0.08);
    }

    .open-tech-hero {
        padding: 28px;
        background:
            radial-gradient(circle at top right, rgba(11, 94, 215, 0.14), transparent 28%),
            linear-gradient(135deg, #ffffff 0%, #f3f8ff 100%);
    }

    .open-tech-title {
        color: var(--open-ink);
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .open-tech-copy {
        color: var(--open-muted);
        margin-bottom: 0;
    }

    .open-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 1rem;
    }

    .open-summary-card {
        border: 1px solid var(--open-border);
        border-radius: 18px;
        background: #fff;
        padding: 0.95rem 1rem;
        box-shadow: 0 10px 22px rgba(19, 34, 56, 0.05);
    }

    .open-summary-label {
        display: block;
        color: var(--open-muted);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
    }

    .open-summary-value {
        color: var(--open-ink);
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1;
    }

    .open-summary-note {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 0.75rem;
        padding: 0.5rem 0.78rem;
        border-radius: 999px;
        background: #eef4fb;
        color: #26415f;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .open-tech-panel {
        padding: 1.2rem;
    }

    .open-tech-group {
        border: 1px solid #dbe5f0;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }

    .open-tech-group + .open-tech-group {
        margin-top: 1rem;
    }

    .open-tech-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 0.9rem 1rem;
        background: linear-gradient(135deg, #f6faff 0%, #eef4fb 100%);
        border-bottom: 1px solid #dbe5f0;
    }

    .open-tech-group-title {
        color: var(--open-ink);
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 0.12rem;
    }

    .open-tech-group-count {
        background: #0b5ed7;
        color: #fff;
        border-radius: 999px;
        padding: 0.35rem 0.72rem;
        font-size: 0.78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .open-tech-table-wrap {
        overflow-x: visible;
    }

    .open-tech-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        font-size: 0.74rem;
    }

    .open-tech-table thead th {
        background: #eef4fb;
        color: var(--open-ink);
        border-bottom: 1px solid #dbe5f0;
        font-size: 0.74rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .open-tech-table td,
    .open-tech-table th {
        padding: 0.55rem 0.65rem;
        vertical-align: middle;
        border-color: #e8eef6;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .open-tech-table td.open-tech-description {
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .open-tech-table .open-col-brand {
        width: 7%;
    }

    .open-tech-table .open-col-serial {
        width: 8%;
    }

    .open-tech-table .open-col-status {
        width: 11%;
    }

    .open-tech-table .open-col-priority {
        width: 10%;
    }

    .open-tech-table .open-col-date {
        width: 9%;
    }

    .open-tech-table .open-col-status,
    .open-tech-table .open-col-priority,
    .open-tech-table .open-col-date,
    .open-tech-table td.open-col-status,
    .open-tech-table td.open-col-priority,
    .open-tech-table td.open-col-date {
        text-align: center;
    }

    .open-tech-table .open-col-action {
        width: 8%;
    }

    .open-tech-table tbody tr:hover {
        background: #f8fbff;
    }

    .open-tech-store {
        color: var(--open-ink);
        font-weight: 800;
    }

    .open-tech-address,
    .open-tech-muted {
        color: #26415f;
        font-size: 0.8rem;
        margin-top: 0.2rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .open-brand-badge,
    .open-status-badge,
    .open-priority-badge,
    .open-zone-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.32rem 0.68rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .open-brand-badge.brand-lidl {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #ffeb3b;
    }

    .open-brand-badge.brand-sonae {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: #fff7d6;
    }

    .open-zone-badge {
        margin-left: 6px;
        padding: 0.15rem 0.42rem;
        font-size: 0.62rem;
        letter-spacing: 0.02em;
        vertical-align: middle;
    }

    .open-zone-badge.zone-norte {
        background: linear-gradient(135deg, #0b5ed7 0%, #2f89d9 100%);
        color: #fff;
    }

    .open-zone-badge.zone-centro {
        background: linear-gradient(135deg, #1f6d3b 0%, #2ea95b 100%);
        color: #fff;
    }

    .open-zone-badge.zone-sul {
        background: linear-gradient(135deg, #a7281d 0%, #dc4c3f 100%);
        color: #fff;
    }

    .open-status-badge.status-pendente {
        background: #fff3cd;
        color: #8a6500;
        border: 1px solid #f3d27a;
    }

    .open-status-badge.status-agendado {
        background: #dff3ff;
        color: #0f5f77;
        border: 1px solid #9dd7e6;
    }

    .open-status-badge.status-aguarda_peca {
        background: #ffe2dc;
        color: #9f2419;
        border: 1px solid #f0a79b;
    }

    .open-status-badge.status-cancelado {
        background: #eef2f7;
        color: #42526b;
        border: 1px solid #cfd8e5;
    }

    .open-priority-badge.priority-baixa {
        background: #e8f7fb;
        color: #0f5f77;
        border: 1px solid #9dd7e6;
    }

    .open-priority-badge.priority-media {
        background: #fff3cd;
        color: #8a6500;
        border: 1px solid #f3d27a;
    }

    .open-priority-badge.priority-alta {
        background: #fdecec;
        color: #9f2419;
        border: 1px solid #f0a79b;
    }

    .open-tech-empty {
        border: 1px dashed #cfd8e5;
        border-radius: 18px;
        background: linear-gradient(180deg, #fbfcfe 0%, #f5f8fc 100%);
        padding: 2rem 1.2rem;
        text-align: center;
        color: var(--open-muted);
    }

    .open-assign-form {
        display: flex;
        flex-direction: row;
        gap: 8px;
        align-items: center;
    }

    .open-assign-form .form-control {
        min-height: 36px;
        border-radius: 10px;
        font-size: 0.82rem;
    }

    .open-assign-form .btn {
        border-radius: 10px;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .open-tech-hero,
        .open-tech-panel {
            padding: 0.9rem;
            border-radius: 14px;
        }

        .open-tech-title {
            font-size: 1.25rem;
        }

        .open-tech-copy,
        .open-summary-note {
            font-size: 0.82rem;
        }

        .open-tech-group-header {
            padding: 0.8rem;
        }

        .open-tech-group-title {
            font-size: 0.95rem;
        }

        .open-tech-group-count {
            padding: 0.3rem 0.55rem;
            font-size: 0.72rem;
        }

        .open-tech-table {
            display: table;
            width: 100%;
            min-width: 0;
            table-layout: fixed;
            border: 1px solid #dbe5f0;
            font-size: 0.5rem;
        }

        .open-tech-table thead {
            display: table-header-group;
        }

        .open-tech-table tbody {
            display: table-row-group;
        }

        .open-tech-table tr {
            display: table-row;
        }

        .open-tech-table td,
        .open-tech-table th {
            display: table-cell;
            padding: 0.16rem 0.13rem;
            vertical-align: middle;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            overflow-wrap: anywhere;
            word-break: normal;
            line-height: 1.1;
        }

        .open-tech-table thead th {
            font-size: 0.43rem;
            letter-spacing: 0;
        }

        .open-tech-table .open-col-brand,
        .open-tech-table td.open-col-brand {
            display: none;
        }

        .open-tech-table .open-col-serial {
            width: 12%;
        }

        .open-tech-table .open-col-status,
        .open-tech-table .open-col-priority {
            width: 13%;
        }

        .open-tech-table .open-col-date {
            width: 10%;
        }

        .open-tech-table .open-col-action {
            width: 8%;
        }

        .open-tech-table .open-col-assign {
            width: 20%;
        }

        .open-tech-table .open-col-description,
        .open-tech-table td.open-tech-description {
            display: none;
        }

        .open-brand-badge,
        .open-status-badge,
        .open-priority-badge {
            padding: 0.1rem 0.2rem;
            border-radius: 5px;
            font-size: 0.42rem;
            letter-spacing: 0;
            white-space: normal;
            line-height: 1.05;
            max-width: 100%;
            overflow-wrap: anywhere;
        }

        .open-tech-store,
        .open-tech-address,
        .open-tech-muted {
            font-size: 0.5rem;
            line-height: 1.1;
            overflow-wrap: anywhere;
        }

        .open-tech-address,
        .open-tech-muted {
            white-space: normal;
        }

        .open-assign-form {
            flex-direction: column;
            gap: 2px;
            width: 100%;
        }

        .open-assign-form .form-control {
            min-height: 22px;
            height: 22px;
            padding: 0.08rem 0.18rem;
            border-radius: 5px;
            font-size: 0.46rem;
        }

        .open-assign-form .btn,
        .open-tech-table .btn {
            padding: 0.1rem 0.2rem;
            border-radius: 5px;
            font-size: 0.46rem;
            line-height: 1.05;
        }

    }

</style>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row open-tech-page">
    <div class="col">
        <div class="open-tech-hero mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div>
                    <h4 class="open-tech-title mb-2">{{ __('Todos os pedidos em aberto') }}</h4>
                    <p class="open-tech-copy">
                        {{ ($includeUnassigned ?? false) ? __('Todos os pedidos ainda não concluídos, incluindo pedidos por atribuir') : __('Pedidos com técnico atribuído e ainda não concluídos') }}
                        <span class="ml-2">({{ $requests->count() }} {{ __('pedidos') }})</span>
                    </p>
                    <div class="open-summary-note">
                        <i class="fa fa-user-check"></i> {{ ($includeUnassigned ?? false) ? __('Inclui pedidos por atribuir') : __('Não inclui pedidos por atribuir') }}
                    </div>
                </div>
                <div class="mt-3 mt-lg-0">
                    <a href="{{ route('backoffice.technical_requests.export', array_merge(request()->only(['mes', 'data_inicio', 'data_fim']), ['open_only' => 1])) }}" class="btn btn-outline-primary mr-2 px-4">
                        <i class="fa fa-file-excel"></i> {{ __('Exportar Global') }}
                    </a>
                    <a href="{{ $backRoute ?? route('backoffice.technical_requests.technicians', request()->only(['mes', 'data_inicio', 'data_fim'])) }}" class="btn btn-outline-secondary px-4">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="open-summary-grid">
            <div class="open-summary-card">
                <span class="open-summary-label">{{ __('Técnicos') }}</span>
                <div class="open-summary-value">{{ $openSummary['technicians'] }}</div>
            </div>
            @if($includeUnassigned ?? false)
                <div class="open-summary-card">
                    <span class="open-summary-label">{{ __('Por atribuir') }}</span>
                    <div class="open-summary-value text-primary">{{ $openSummary['unassigned'] }}</div>
                </div>
            @endif
            <div class="open-summary-card">
                <span class="open-summary-label">{{ __('Pendentes') }}</span>
                <div class="open-summary-value text-warning">{{ $openSummary['pending'] }}</div>
            </div>
            <div class="open-summary-card">
                <span class="open-summary-label">{{ __('Agendados') }}</span>
                <div class="open-summary-value text-info">{{ $openSummary['scheduled'] }}</div>
            </div>
            <div class="open-summary-card">
                <span class="open-summary-label">{{ __('Aguarda Peça') }}</span>
                <div class="open-summary-value text-danger">{{ $openSummary['awaiting_part'] }}</div>
            </div>
            <div class="open-summary-card">
                <span class="open-summary-label">{{ __('Cancelados') }}</span>
                <div class="open-summary-value text-muted">{{ $openSummary['cancelled'] }}</div>
            </div>
        </div>

        <div class="open-tech-panel">
            @if($requests->isNotEmpty())
                @foreach($requestsByTechnician as $technicianRequests)
                    @php($technician = $technicianRequests->first()->assignedTechnician)
                    <section class="open-tech-group">
                        <div class="open-tech-group-header">
                            <div>
                                <div class="open-tech-group-title">{{ $technician->name ?? $technician->email ?? __('Por atribuir') }}</div>
                                @if($technician?->email)
                                    <div class="open-tech-muted">{{ $technician->email }}</div>
                                @endif
                            </div>
                            <div class="open-tech-group-count">{{ $technicianRequests->count() }} {{ __('em aberto') }}</div>
                        </div>

                        <div class="open-tech-table-wrap">
                            <table class="table table-sm table-bordered open-tech-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Loja') }}</th>
                                        <th class="open-col-brand">{{ __('Insígnia') }}</th>
                                        <th class="open-col-serial">{{ __('S/N') }}</th>
                                        <th class="open-col-status">{{ __('Estado') }}</th>
                                        <th class="open-col-priority">{{ __('Prioridade') }}</th>
                                        <th class="open-col-date">{{ __('Pedido') }}</th>
                                        @if($includeUnassigned ?? false)
                                            <th class="open-col-assign">{{ __('Atribuir') }}</th>
                                        @endif
                                        <th class="open-col-description">{{ __('Descrição') }}</th>
                                        <th class="open-col-action">{{ __('Abrir') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($technicianRequests as $technicalRequest)
                                        <tr>
                                            <td class="open-tech-main" data-label="{{ __('Loja') }}">
                                                <div class="open-tech-store">
                                                    {{ optional($technicalRequest->store)->codigo_loja ?: '-' }} - {{ optional($technicalRequest->store)->nome_loja ?: '-' }}@if($technicalRequest->zona)<span class="open-zone-badge zone-{{ $technicalRequest->zona }}">{{ ucfirst($technicalRequest->zona) }}</span>@endif
                                                </div>
                                                @php($address = implode(', ', array_filter([
                                                    optional($technicalRequest->store)->morada,
                                                    trim(implode(' ', array_filter([
                                                        optional($technicalRequest->store)->codigo_postal,
                                                        optional($technicalRequest->store)->cidade,
                                                    ]))),
                                                ])))
                                                @if($address)
                                                    <div class="open-tech-address">
                                                        <i class="fa fa-map-marker-alt"></i> {{ $address }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('Insígnia') }}">
                                                @if(optional($technicalRequest->store)->insignia)
                                                    <span class="open-brand-badge brand-{{ optional($technicalRequest->store)->insignia }}">
                                                        {{ ucfirst(optional($technicalRequest->store)->insignia) }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td data-label="{{ __('S/N') }}">{{ optional($technicalRequest->machine)->serial_number ?: '-' }}</td>
                                            <td class="open-col-status" data-label="{{ __('Estado') }}">
                                                <span class="open-status-badge status-{{ $technicalRequest->estado }}">
                                                    {{ $statuses[$technicalRequest->estado] ?? ucfirst(str_replace('_', ' ', $technicalRequest->estado)) }}
                                                </span>
                                            </td>
                                            <td class="open-col-priority" data-label="{{ __('Prioridade') }}">
                                                @if($technicalRequest->prioridade)
                                                    <span class="open-priority-badge priority-{{ $technicalRequest->prioridade }}">
                                                        {{ ucfirst($technicalRequest->prioridade) }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="open-col-date" data-label="{{ __('Pedido') }}">{{ optional($technicalRequest->data_pedido)->format('d/m/Y') ?: '-' }}</td>
                                            @if($includeUnassigned ?? false)
                                                <td class="open-col-assign" data-label="{{ __('Atribuir') }}">
                                                    <form method="POST" action="{{ route('backoffice.technical_requests.assign_technician', $technicalRequest->id) }}" class="open-assign-form">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="assigned_technician_id" class="form-control">
                                                            <option value="">{{ __('-- Por atribuir --') }}</option>
                                                            @foreach($technicians as $assignableTechnician)
                                                                <option value="{{ $assignableTechnician->id }}" {{ (int) $technicalRequest->assigned_technician_id === (int) $assignableTechnician->id ? 'selected' : '' }}>
                                                                    {{ $assignableTechnician->name ?: $assignableTechnician->email }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-sm {{ $technicalRequest->assigned_technician_id ? 'btn-outline-primary' : 'btn-primary' }}">
                                                            {{ $technicalRequest->assigned_technician_id ? __('Alterar') : __('Atribuir') }}
                                                        </button>
                                                    </form>
                                                </td>
                                            @endif
                                            <td class="open-tech-description" data-label="{{ __('Descrição') }}">{{ \Illuminate\Support\Str::limit($technicalRequest->descricao_problema ?: '-', 70) }}</td>
                                            <td data-label="{{ __('Abrir') }}">
                                                <a href="{{ route('backoffice.technical_requests.show', ['id' => $technicalRequest->id, 'return_url' => url()->full()]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Abrir') }}" aria-label="{{ __('Abrir') }}">
                                                    <i class="fa fa-eye"></i> {{ __('Abrir') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach
            @else
                <div class="open-tech-empty">
                    {{ ($includeUnassigned ?? false) ? __('Não existem pedidos em aberto.') : __('Não existem pedidos em aberto atribuídos a técnicos.') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

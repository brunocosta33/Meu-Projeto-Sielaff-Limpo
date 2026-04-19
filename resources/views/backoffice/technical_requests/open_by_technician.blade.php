@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Pedidos em aberto') }}</title>
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

    .open-tech-panel {
        padding: 1.2rem;
        overflow-x: visible;
    }

    .open-tech-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        font-size: 0.76rem;
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
        padding: 0.72rem 0.8rem;
        vertical-align: middle;
        border-color: #e8eef6;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .open-tech-table td.open-tech-description {
        max-width: 160px;
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

    .open-tech-muted {
        color: var(--open-muted);
        font-size: 0.8rem;
    }

    .open-tech-address {
        color: #26415f;
        font-size: 0.8rem;
        margin-top: 0.2rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .open-brand-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.62rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .open-brand-badge.brand-lidl {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #ffeb3b;
    }

    .open-brand-badge.brand-sonae {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: #fff7d6;
    }

    .open-status-badge,
    .open-priority-badge {
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

    .open-status-badge.status-concluido {
        background: #def7e8;
        color: #17663a;
        border: 1px solid #9fd6af;
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

    @media (max-width: 767.98px) {
        .open-tech-hero,
        .open-tech-panel {
            padding: 0.9rem;
            border-radius: 14px;
        }

        .open-tech-title {
            font-size: 1.25rem;
        }

        .open-tech-copy {
            font-size: 0.82rem;
        }

        .open-tech-table,
        .open-tech-table tbody,
        .open-tech-table tr,
        .open-tech-table td {
            display: block;
            width: 100%;
        }

        .open-tech-table {
            border: 0;
            font-size: 0.86rem;
        }

        .open-tech-table thead {
            display: none;
        }

        .open-tech-table tbody tr {
            border: 1px solid #dbe5f0;
            border-radius: 16px;
            margin-bottom: 0.75rem;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 10px 22px rgba(19, 34, 56, 0.06);
        }

        .open-tech-table td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.55rem 0.75rem;
            border: 0;
            border-bottom: 1px solid #edf2f8;
            white-space: normal;
            overflow: visible;
        }

        .open-tech-table td:last-child {
            border-bottom: 0;
        }

        .open-tech-table td::before {
            content: attr(data-label);
            flex: 0 0 34%;
            color: #6c7a89;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .open-tech-table td.open-tech-main {
            display: block;
            padding: 0.75rem;
            background: linear-gradient(135deg, #f8fbff 0%, #eef4fb 100%);
        }

        .open-tech-table td.open-tech-main::before {
            display: none;
        }

        .open-brand-badge,
        .open-status-badge,
        .open-priority-badge {
            padding: 0.26rem 0.55rem;
            font-size: 0.66rem;
        }

        .open-tech-store,
        .open-tech-address,
        .open-tech-muted {
            font-size: 0.8rem;
        }

        .open-tech-address,
        .open-tech-muted {
            white-space: normal;
        }

        .open-tech-table .btn {
            padding: 0.28rem 0.55rem;
            font-size: 0.76rem;
        }

        .open-tech-table td.open-tech-description {
            max-width: none;
            white-space: normal;
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
                    <h4 class="open-tech-title mb-2">{{ __('Pedidos em aberto') }}</h4>
                    <p class="open-tech-copy">
                        {{ __('Responsável') }}: <strong>{{ $technician->name ?: $technician->email }}</strong>
                        <span class="ml-2">({{ $requests->count() }} {{ __('pedidos') }})</span>
                    </p>
                </div>
                <div class="mt-3 mt-lg-0">
                    @if($canExport ?? false)
                        <a href="{{ route('backoffice.technical_requests.export_by_technician', array_merge(['id' => $technician->id], request()->only(['mes', 'data_inicio', 'data_fim']))) }}" class="btn btn-outline-primary mr-2 px-4">
                            <i class="fa fa-file-excel"></i> {{ __('Exportar Excel') }}
                        </a>
                    @endif
                    <a href="{{ $backRoute ?? route('backoffice.technical_requests.technicians', request()->only(['mes', 'data_inicio', 'data_fim'])) }}" class="btn btn-outline-secondary px-4">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="open-tech-panel">
            @if($requests->isNotEmpty())
                <table class="table table-sm table-bordered open-tech-table">
                    <thead>
                        <tr>
                            <th>{{ __('Loja') }}</th>
                            <th class="open-col-brand">{{ __('Insígnia') }}</th>
                            <th class="open-col-serial">{{ __('S/N') }}</th>
                            <th class="open-col-status">{{ __('Estado') }}</th>
                            <th class="open-col-priority">{{ __('Prioridade') }}</th>
                            <th class="open-col-date">{{ __('Pedido') }}</th>
                            <th>{{ __('Descrição') }}</th>
                            <th class="open-col-action">{{ __('Abrir') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $technicalRequest)
                            <tr>
                                <td class="open-tech-main" data-label="{{ __('Loja') }}">
                                    <div class="open-tech-store">
                                        {{ optional($technicalRequest->store)->codigo_loja ?: '-' }} - {{ optional($technicalRequest->store)->nome_loja ?: '-' }}
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
                                <td class="open-tech-description" data-label="{{ __('Descrição') }}">{{ \Illuminate\Support\Str::limit($technicalRequest->descricao_problema ?: '-', 90) }}</td>
                                <td data-label="{{ __('Abrir') }}">
                                    <a href="{{ route('backoffice.technical_requests.show', ['id' => $technicalRequest->id, 'return_url' => url()->full()]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Abrir') }}" aria-label="{{ __('Abrir') }}">
                                        <i class="fa fa-eye"></i> {{ __('Abrir') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="open-tech-empty">
                    {{ __('Este responsável não tem pedidos em aberto.') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

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

    .open-tech-table .open-col-store {
        width: 27%;
    }

    .open-tech-table .open-col-serial {
        width: 8%;
    }

    .open-tech-table .open-col-model {
        width: 10%;
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

    .open-brand-pill,
    .open-zone-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.66rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #fff;
        vertical-align: middle;
    }

    .open-brand-pill {
        margin-right: 8px;
        flex-shrink: 0;
    }

    .open-brand-pill.brand-lidl {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #ffeb3b;
    }

    .open-brand-pill.brand-sonae {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: #fff7d6;
    }

    .open-zone-pill.zone-norte {
        background: linear-gradient(135deg, #0b5ed7 0%, #2f89d9 100%);
    }

    .open-zone-pill.zone-centro {
        background: linear-gradient(135deg, #1f6d3b 0%, #2ea95b 100%);
    }

    .open-zone-pill.zone-sul {
        background: linear-gradient(135deg, #a7281d 0%, #dc4c3f 100%);
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

    .open-mobile-summary {
        display: none;
    }

    .open-mobile-stat {
        display: block;
        background: #fff;
        border: 1px solid var(--open-border);
        border-radius: 12px;
        color: inherit;
        padding: 0.65rem 0.7rem;
        box-shadow: 0 10px 20px rgba(19, 34, 56, 0.06);
        min-height: 72px;
        text-decoration: none;
        transition: border-color 0.16s ease, box-shadow 0.16s ease, transform 0.16s ease;
    }

    .open-mobile-stat:hover,
    .open-mobile-stat:focus {
        color: inherit;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(19, 34, 56, 0.1);
    }

    .open-mobile-stat.is-active {
        border-width: 2px;
        box-shadow: 0 14px 28px rgba(11, 94, 215, 0.18);
    }

    .open-mobile-stat:nth-child(1) {
        background: linear-gradient(135deg, #eef4ff 0%, #ffffff 100%);
        border-color: #cfe0ff;
    }

    .open-mobile-stat:nth-child(2) {
        background: linear-gradient(135deg, #fff4d6 0%, #ffffff 100%);
        border-color: #f3d27a;
    }

    .open-mobile-stat:nth-child(3) {
        background: linear-gradient(135deg, #dff3ff 0%, #ffffff 100%);
        border-color: #9dd7e6;
    }

    .open-mobile-stat:nth-child(4) {
        background: linear-gradient(135deg, #ffe2dc 0%, #ffffff 100%);
        border-color: #f0a79b;
    }

    .open-mobile-stat-label {
        color: var(--open-muted);
        display: block;
        font-size: 0.66rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        line-height: 1.15;
        text-transform: uppercase;
    }

    .open-mobile-stat-value {
        color: var(--open-ink);
        display: block;
        font-size: 1.25rem;
        font-weight: 900;
        line-height: 1.15;
        margin-top: 0.35rem;
    }

    .open-mobile-stat:nth-child(1) .open-mobile-stat-value {
        color: #0b5ed7;
    }

    .open-mobile-stat:nth-child(2) .open-mobile-stat-value {
        color: #8a6500;
    }

    .open-mobile-stat:nth-child(3) .open-mobile-stat-value {
        color: #0f5f77;
    }

    .open-mobile-stat:nth-child(4) .open-mobile-stat-value {
        color: #9f2419;
    }

    @media (max-width: 767.98px) {
        .open-tech-hero,
        .open-tech-panel {
            padding: 0.9rem;
            border-radius: 14px;
        }

        .open-tech-panel {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .open-tech-title {
            font-size: 1.25rem;
        }

        .open-tech-copy {
            font-size: 0.82rem;
        }

        .open-mobile-summary {
            display: grid;
            gap: 0.55rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .open-tech-table {
            display: table;
            width: auto;
            min-width: 720px;
            table-layout: auto;
            border: 1px solid #dbe5f0;
            font-size: 0.62rem;
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
            padding: 0.3rem 0.35rem;
            vertical-align: middle;
            white-space: nowrap;
            overflow: visible;
            text-overflow: clip;
            line-height: 1.15;
        }

        .open-tech-table thead th {
            font-size: 0.56rem;
            letter-spacing: 0;
        }

        .open-tech-table .open-col-brand,
        .open-tech-table td.open-col-brand {
            display: none;
        }

        .open-tech-table .open-col-serial,
        .open-tech-table .open-col-model,
        .open-tech-table .open-col-status,
        .open-tech-table .open-col-priority,
        .open-tech-table .open-col-date,
        .open-tech-table .open-col-action,
        .open-tech-table .open-col-store,
        .open-tech-table .open-tech-main {
            width: auto;
        }

        .open-tech-table .open-col-description,
        .open-tech-table td.open-tech-description {
            display: none;
        }

        .open-brand-badge,
        .open-status-badge,
        .open-priority-badge {
            padding: 0.22rem 0.45rem;
            border-radius: 999px;
            font-size: 0.58rem;
            letter-spacing: 0;
            white-space: nowrap;
            line-height: 1.05;
        }

        .open-brand-pill,
        .open-zone-pill {
            padding: 0.16rem 0.4rem;
            border-radius: 999px;
            font-size: 0.54rem;
            letter-spacing: 0;
            line-height: 1.05;
            margin-right: 4px;
            white-space: nowrap;
        }

        .open-tech-address,
        .open-tech-muted {
            font-size: 0.56rem;
            line-height: 1.1;
            white-space: nowrap;
            overflow: visible;
        }

        .open-tech-store {
            display: block;
            font-size: 0.6rem;
            line-height: 1.15;
            white-space: nowrap;
            overflow: visible;
        }

        .open-tech-address {
            display: none;
        }

        .open-tech-table .btn {
            padding: 0.2rem 0.4rem;
            border-radius: 6px;
            font-size: 0.58rem;
            line-height: 1.05;
        }

        .open-tech-table .open-action-text {
            display: none;
        }

    }
</style>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

@php
    $openStats = $openStats ?? [
        'total' => $requests->count(),
        'pendente' => $requests->where('estado', 'pendente')->count(),
        'agendado' => $requests->where('estado', 'agendado')->count(),
        'aguarda_peca' => $requests->where('estado', 'aguarda_peca')->count(),
    ];
    $selectedStatus = $selectedStatus ?? request('estado');
    $baseFilterParams = request()->except(['estado', 'page']);
    $filterUrl = function (array $params) {
        $query = http_build_query($params);

        return url()->current() . ($query ? '?' . $query : '');
    };
    $mobileStats = [
        ['label' => __('Total'), 'value' => $openStats['total'] ?? 0, 'status' => null, 'url' => $filterUrl($baseFilterParams)],
        ['label' => $statuses['pendente'] ?? __('Pendente'), 'value' => $openStats['pendente'] ?? 0, 'status' => 'pendente', 'url' => $filterUrl(array_merge($baseFilterParams, ['estado' => 'pendente']))],
        ['label' => $statuses['agendado'] ?? __('Agendado'), 'value' => $openStats['agendado'] ?? 0, 'status' => 'agendado', 'url' => $filterUrl(array_merge($baseFilterParams, ['estado' => 'agendado']))],
        ['label' => $statuses['aguarda_peca'] ?? __('Aguarda Peça'), 'value' => $openStats['aguarda_peca'] ?? 0, 'status' => 'aguarda_peca', 'url' => $filterUrl(array_merge($baseFilterParams, ['estado' => 'aguarda_peca']))],
    ];
@endphp

<div class="row open-tech-page">
    <div class="col">
        <div class="open-tech-hero mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div>
                    <h4 class="open-tech-title mb-2">{{ $pageTitle ?? __('Pedidos em aberto') }}</h4>
                    <p class="open-tech-copy">
                        @if(isset($pageCopy))
                            {{ $pageCopy }}
                        @else
                            {{ __('Responsável') }}: <strong>{{ $technician->name ?: $technician->email }}</strong>
                        @endif
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

        @if($showMobileSummary ?? true)
            <div class="open-mobile-summary mb-3">
                @foreach($mobileStats as $stat)
                    <a href="{{ $stat['url'] }}" class="open-mobile-stat {{ $selectedStatus === $stat['status'] || (!$selectedStatus && $stat['status'] === null) ? 'is-active' : '' }}">
                        <span class="open-mobile-stat-label">{{ $stat['label'] }}</span>
                        <span class="open-mobile-stat-value">{{ $stat['value'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="open-tech-panel">
            @if($requests->isNotEmpty())
                <table class="table table-sm table-bordered open-tech-table">
                    <thead>
                        <tr>
                            <th class="open-col-store">{{ __('Loja') }}</th>
                            <th class="open-col-brand">{{ __('Região') }}</th>
                            <th class="open-col-serial">{{ __('S/N') }}</th>
                            <th class="open-col-model">{{ __('Modelo') }}</th>
                            <th class="open-col-status">{{ __('Estado') }}</th>
                            <th class="open-col-priority">{{ __('Prioridade') }}</th>
                            <th class="open-col-date">{{ $dateLabel ?? __('Pedido') }}</th>
                            <th class="open-col-description">{{ __('Descrição') }}</th>
                            <th class="open-col-action">{{ __('Abrir') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $technicalRequest)
                            <tr>
                                <td class="open-tech-main open-col-store" data-label="{{ __('Loja') }}">
                                    <div class="open-tech-store">
                                        @if(optional($technicalRequest->store)->insignia)
                                            <span class="open-brand-pill brand-{{ optional($technicalRequest->store)->insignia }}">{{ ucfirst(optional($technicalRequest->store)->insignia) }}</span>
                                        @endif
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
                                <td class="open-col-brand" data-label="{{ __('Região') }}">
                                    @if($technicalRequest->zona)
                                        <span class="open-zone-pill zone-{{ $technicalRequest->zona }}">{{ ucfirst($technicalRequest->zona) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="open-col-serial" data-label="{{ __('S/N') }}">{{ optional($technicalRequest->machine)->serial_number ?: '-' }}</td>
                                <td class="open-col-model" data-label="{{ __('Modelo') }}">{{ optional($technicalRequest->machine)->descricao ?: '-' }}</td>
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
                                @php($dateValue = optional($technicalRequest->{$dateField ?? 'data_pedido'}))
                                <td class="open-col-date" data-label="{{ $dateLabel ?? __('Pedido') }}">{{ $dateValue->format('d/m/Y') ?: '-' }}</td>
                                <td class="open-tech-description" data-label="{{ __('Descrição') }}">{{ \Illuminate\Support\Str::limit($technicalRequest->descricao_problema ?: '-', 90) }}</td>
                                <td class="open-col-action" data-label="{{ __('Abrir') }}">
                                    <a href="{{ route('backoffice.technical_requests.show', ['id' => $technicalRequest->id, 'return_url' => url()->full()]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Abrir') }}" aria-label="{{ __('Abrir') }}">
                                        <i class="fa fa-eye"></i> <span class="open-action-text">{{ __('Abrir') }}</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="open-tech-empty">
                    {{ $emptyMessage ?? __('Este responsável não tem pedidos em aberto.') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Detalhes do Pedido') }}</title>
@endsection

@section('head-scripts')
<style>
    .technical-request-show-card {
        overflow: hidden;
        border: 1px solid #dfe8e3 !important;
        border-radius: 8px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.1) !important;
    }

    .technical-request-show-card .card-body {
        padding: 0;
    }

    .technical-request-show-header {
        padding: 1.35rem 1.5rem;
        color: #ffffff;
        background:
            linear-gradient(135deg, rgba(18, 52, 59, 0.98), rgba(23, 116, 91, 0.94)),
            #12343b;
        border-bottom: 1px solid rgba(255, 255, 255, 0.16);
    }

    .technical-request-show-title {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .technical-request-show-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #12343b;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.18);
    }

    .technical-request-show-header h5 {
        color: #ffffff;
        font-weight: 800;
    }

    .technical-request-show-header .text-muted {
        color: rgba(255, 255, 255, 0.74) !important;
    }

    .technical-request-show-header .btn {
        font-weight: 700;
    }

    .technical-request-show-header .btn-outline-primary,
    .technical-request-show-header .btn-outline-secondary {
        border-color: rgba(255, 255, 255, 0.55);
        color: #ffffff;
        background: rgba(255, 255, 255, 0.08);
    }

    .technical-request-show-header .btn-outline-primary:hover,
    .technical-request-show-header .btn-outline-secondary:hover {
        color: #12343b;
        background: #ffffff;
        border-color: #ffffff;
    }

    .technical-request-show-body {
        padding: 1.5rem;
        background:
            linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(13, 110, 253, 0.08) 48%, rgba(111, 66, 193, 0.1)),
            #f6faf8;
    }

    .technical-request-info-card {
        height: 100%;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #dde8e3;
        border-left: 6px solid #198754;
        border-radius: 8px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.07);
    }

    .technical-request-info-card.status-card {
        border-left-color: #0d6efd;
    }

    .technical-request-info-card.text-card {
        border-left-color: #6f42c1;
    }

    .technical-request-info-header {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.95rem 1rem 0.85rem 1rem;
        border-bottom: 1px solid rgba(25, 135, 84, 0.18);
        background: linear-gradient(90deg, rgba(25, 135, 84, 0.16), rgba(25, 135, 84, 0.04));
        color: #14532d;
        font-size: 0.84rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .technical-request-info-card.status-card .technical-request-info-header {
        border-bottom-color: rgba(13, 110, 253, 0.18);
        background: linear-gradient(90deg, rgba(13, 110, 253, 0.15), rgba(13, 110, 253, 0.04));
        color: #1d4ed8;
    }

    .technical-request-info-card.text-card .technical-request-info-header {
        border-bottom-color: rgba(111, 66, 193, 0.18);
        background: linear-gradient(90deg, rgba(111, 66, 193, 0.15), rgba(111, 66, 193, 0.04));
        color: #5b21b6;
    }

    .technical-request-info-header i {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        background: #198754;
        border-radius: 8px;
    }

    .technical-request-info-card.status-card .technical-request-info-header i {
        background: #0d6efd;
    }

    .technical-request-info-card.text-card .technical-request-info-header i {
        background: #6f42c1;
    }

    .technical-request-info-body {
        padding: 1rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.88), #ffffff);
    }

    .technical-request-field {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr);
        gap: 0.75rem;
        padding: 0.55rem 0;
        border-bottom: 1px solid #eef3f0;
    }

    .technical-request-field:last-child {
        border-bottom: 0;
    }

    .technical-request-field-label {
        color: #66766f;
        font-weight: 800;
    }

    .technical-request-field-value {
        min-width: 0;
        color: #26342f;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .technical-request-text-block {
        min-height: 96px;
        padding: 1rem;
        color: #53645d;
        background: #ffffff;
        border: 1px solid #e2eae6;
        border-radius: 8px;
        line-height: 1.55;
        white-space: pre-wrap;
    }

    .technical-request-brand-badge {
        display: inline-flex;
        align-items: center;
        margin-left: 0.45rem;
        padding: 0.28rem 0.62rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .technical-request-brand-badge.brand-lidl {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #ffeb3b;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
    }

    .technical-request-brand-badge.brand-sonae {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: #fff7d6;
        box-shadow: 0 8px 18px rgba(245, 158, 11, 0.2);
    }

    .technical-request-address {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 0.35rem;
        padding: 0.55rem 0.8rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #eef4ff 0%, #f6faff 100%);
        border: 1px solid #d8e5fb;
        color: #26415f;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .technical-request-schedule-highlight {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0.85rem 1rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #e8f7fb 0%, #d7f0f8 100%);
        border: 1px solid #9dd7e6;
        color: #0f5f77;
        font-weight: 700;
        box-shadow: 0 10px 22px rgba(15, 95, 119, 0.12);
    }

    .technical-request-schedule-highlight i {
        font-size: 1.05rem;
    }

    .technical-request-resolution-highlight {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0.85rem 1rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #e8f8ec 0%, #d8f2df 100%);
        border: 1px solid #9fd6af;
        color: #17663a;
        font-weight: 700;
        box-shadow: 0 10px 22px rgba(23, 102, 58, 0.12);
    }

    .technical-request-resolution-highlight i {
        font-size: 1.05rem;
    }

    @media (max-width: 767.98px) {
        .technical-request-show-card {
            border-radius: 8px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08) !important;
        }

        .technical-request-show-header,
        .technical-request-show-body {
            padding: 0.65rem;
        }

        .technical-request-show-title {
            align-items: flex-start;
            gap: 0.5rem;
        }

        .technical-request-show-icon {
            width: 34px;
            height: 34px;
            flex-basis: 34px;
            border-radius: 7px;
            font-size: 0.85rem;
        }

        .technical-request-show-header h5 {
            font-size: 0.92rem;
            line-height: 1.2;
            margin-bottom: 0.15rem !important;
        }

        .technical-request-show-header .text-muted {
            font-size: 0.68rem;
            line-height: 1.25;
        }

        .technical-request-show-header .mt-3 {
            display: flex;
            gap: 5px;
            width: 100%;
            margin-top: 0.55rem !important;
        }

        .technical-request-show-header .btn {
            flex: 1 1 0;
            min-width: 0;
            padding: 0.32rem 0.42rem;
            border-radius: 7px;
            font-size: 0.66rem;
            line-height: 1.1;
            white-space: normal;
        }

        .technical-request-show-body > .row {
            margin-right: -4px;
            margin-left: -4px;
        }

        .technical-request-show-body > .row > [class*="col-"] {
            padding-right: 4px;
            padding-left: 4px;
            margin-bottom: 0.45rem !important;
        }

        .technical-request-info-card {
            border-left-width: 4px;
            border-radius: 8px;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
        }

        .technical-request-info-header {
            gap: 0.35rem;
            padding: 0.45rem 0.55rem;
            font-size: 0.62rem;
            line-height: 1.1;
            letter-spacing: 0.02em;
        }

        .technical-request-info-header i {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            font-size: 0.68rem;
        }

        .technical-request-info-body {
            padding: 0.45rem 0.55rem;
        }

        .technical-request-field {
            grid-template-columns: minmax(82px, 34%) minmax(0, 1fr);
            gap: 0.35rem;
            padding: 0.28rem 0;
            align-items: start;
        }

        .technical-request-field-label {
            font-size: 0.62rem;
            line-height: 1.15;
        }

        .technical-request-field-value {
            font-size: 0.68rem;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .technical-request-brand-badge {
            margin-left: 0.2rem;
            padding: 0.14rem 0.32rem;
            border-radius: 6px;
            font-size: 0.52rem;
            letter-spacing: 0;
            line-height: 1.05;
        }

        .technical-request-address {
            display: flex;
            align-items: flex-start;
            gap: 4px;
            width: 100%;
            margin-top: 0.2rem;
            padding: 0.32rem 0.42rem;
            border-radius: 7px;
            font-size: 0.62rem;
            line-height: 1.18;
            overflow-wrap: anywhere;
        }

        .technical-request-schedule-highlight,
        .technical-request-resolution-highlight {
            display: flex;
            align-items: flex-start;
            gap: 5px;
            max-width: 100%;
            padding: 0.36rem 0.45rem;
            border-radius: 7px;
            font-size: 0.62rem;
            line-height: 1.18;
            box-shadow: none;
            overflow-wrap: anywhere;
        }

        .technical-request-text-block {
            min-height: 54px;
            padding: 0.5rem;
            border-radius: 7px;
            font-size: 0.7rem;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .technical-request-field-value .badge {
            padding: 0.18rem 0.34rem;
            border-radius: 6px;
            font-size: 0.58rem;
            line-height: 1.1;
        }
    }
</style>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

@php
    $returnUrl = request('return_url');
    $backRoute = $returnUrl ?: route('backoffice.technical_requests.index');
@endphp

<div class="row">
    <div class="col-xl-10">
        <div class="card shadow-sm border-0 technical-request-show-card">
            <div class="card-body">
                <div class="technical-request-show-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div class="technical-request-show-title">
                        <span class="technical-request-show-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </span>
                        <div>
                            <h5 class="card-title mb-1">{{ __('Detalhes do Pedido de Assistência Técnica') }}</h5>
                            <p class="text-muted mb-0">{{ __('Resumo completo do pedido para consulta rápida.') }}</p>
                        </div>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        @if($canManageAll || $request->estado !== 'concluido')
                            <a href="{{ route('backoffice.technical_requests.edit', ['id' => $request->id, 'return_url' => $backRoute]) }}" class="btn btn-outline-primary mr-2">
                                <i class="fa fa-edit"></i> {{ __('Editar') }}
                            </a>
                        @endif
                        <a href="{{ $backRoute }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                        </a>
                    </div>
                </div>

                <div class="technical-request-show-body">
                @php
                    $tipos = [
                        'software' => 'Software',
                        'reparacao' => 'Assistência/Reparação',
                        'manutencao' => 'Manutenção',
                        'pre_visita' => 'Pré-Visita',
                    ];
                    $hasEdition = $request->updated_at && $request->created_at && $request->updated_at->ne($request->created_at);
                @endphp

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="technical-request-info-card">
                            <div class="technical-request-info-header">
                                <i class="fas fa-store"></i>
                                {{ __('Identificação') }}
                            </div>
                            <div class="technical-request-info-body">
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('ID') }}</div>
                                <div class="technical-request-field-value">#{{ $request->id }}</div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Loja') }}</div>
                                <div class="technical-request-field-value">
                                    {{ $request->store->codigo_loja ?? '-' }} - {{ $request->store->nome_loja ?? '-' }}
                                @if($request->store->insignia ?? null)
                                    <span class="technical-request-brand-badge brand-{{ $request->store->insignia }}">
                                        {{ ucfirst($request->store->insignia) }}
                                    </span>
                                @endif
                                </div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Morada') }}</div>
                                <div class="technical-request-field-value">
                                @php($storeAddress = implode(', ', array_filter([
                                    $request->store->morada ?? null,
                                    trim(implode(' ', array_filter([
                                        $request->store->codigo_postal ?? null,
                                        $request->store->cidade ?? null,
                                    ]))),
                                ])))
                                @if($storeAddress)
                                    <div class="technical-request-address">
                                        <i class="fa fa-map-marker-alt"></i>
                                        {{ $storeAddress }}
                                    </div>
                                @else
                                    —
                                @endif
                                </div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Número de Série') }}</div>
                                <div class="technical-request-field-value">{{ $request->machine->serial_number ?? '—' }}</div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Origem') }}</div>
                                <div class="technical-request-field-value">{{ $request->origem ?: '—' }}</div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ $request->assignedPersonTypeLabel() }} {{ __('atribuído') }}</div>
                                <div class="technical-request-field-value">{{ $request->assignedPersonLabel() }}</div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Criado por') }}</div>
                                <div class="technical-request-field-value">{{ $request->creator->name ?? $request->creator->email ?? '—' }}</div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Criado em') }}</div>
                                <div class="technical-request-field-value">{{ $request->created_at ? \Carbon\Carbon::parse($request->created_at)->format('d/m/Y H:i') : '—' }}</div>
                            </div>
                            @if($hasEdition)
                                <div class="technical-request-field">
                                    <div class="technical-request-field-label">{{ __('Última edição por') }}</div>
                                    <div class="technical-request-field-value">{{ $request->editor->name ?? $request->editor->email ?? '—' }}</div>
                                </div>
                                <div class="technical-request-field">
                                    <div class="technical-request-field-label">{{ __('Editado em') }}</div>
                                    <div class="technical-request-field-value">{{ $request->updated_at ? \Carbon\Carbon::parse($request->updated_at)->format('d/m/Y H:i') : '—' }}</div>
                                </div>
                            @endif
                            @if($request->estado === 'concluido')
                                <div class="technical-request-field">
                                    <div class="technical-request-field-label">{{ __('Concluído por') }}</div>
                                    <div class="technical-request-field-value">{{ $request->editor->name ?? $request->editor->email ?? '—' }}</div>
                                </div>
                            @endif
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Tipo de Serviço') }}</div>
                                <div class="technical-request-field-value">{{ $tipos[$request->tipo_servico] ?? ucfirst($request->tipo_servico) }}</div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="technical-request-info-card status-card">
                            <div class="technical-request-info-header">
                                <i class="fas fa-clipboard-check"></i>
                                {{ __('Estado e datas') }}
                            </div>
                            <div class="technical-request-info-body">
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Prioridade') }}</div>
                                <div class="technical-request-field-value">
                                <span class="badge 
                                    @switch($request->prioridade)
                                        @case('baixa') bg-info @break
                                        @case('media') bg-warning text-dark @break
                                        @case('alta') bg-danger text-white @break
                                        @default bg-secondary
                                    @endswitch">
                                    {{ __(ucfirst($request->prioridade)) }}
                                </span>
                                </div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Estado') }}</div>
                                <div class="technical-request-field-value">
                                <span class="badge 
                                    @switch($request->estado)
                                        @case('agendado') bg-info text-dark @break
                                        @case('concluido') bg-success @break
                                        @case('cancelado') bg-danger @break
                                        @case('pendente') bg-warning @break
                                        @case('aguarda_peca') bg-danger text-white @break
                                        @default bg-light
                                    @endswitch">
                                    {{ __(ucfirst(str_replace('_', ' ', $request->estado))) }}
                                </span>
                                </div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Data do Pedido') }}</div>
                                <div class="technical-request-field-value">{{ $request->data_pedido ? \Carbon\Carbon::parse($request->data_pedido)->format('d/m/Y') : '—' }}</div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Data de Agendamento') }}</div>
                                <div class="technical-request-field-value">
                                @if($request->data_agendamento)
                                    <div class="mt-2">
                                        <span class="technical-request-schedule-highlight">
                                            <i class="fa fa-calendar-alt"></i>
                                            {{ __('Data de Agendamento') }}:
                                            {{ \Carbon\Carbon::parse($request->data_agendamento)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @else
                                    —
                                @endif
                                </div>
                            </div>
                            <div class="technical-request-field">
                                <div class="technical-request-field-label">{{ __('Data da Resolução') }}</div>
                                <div class="technical-request-field-value">
                                @if($request->data_resolucao)
                                    <div class="mt-2">
                                        <span class="technical-request-resolution-highlight">
                                            <i class="fa fa-check-circle"></i>
                                            {{ __('Data de Resolução') }}:
                                            {{ \Carbon\Carbon::parse($request->data_resolucao)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @else
                                    —
                                @endif
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="technical-request-info-card text-card">
                            <div class="technical-request-info-header">
                                <i class="fas fa-tools"></i>
                                {{ __('Descrição') }}
                            </div>
                            <div class="technical-request-info-body">
                                <div class="technical-request-text-block">{{ $request->descricao_problema ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="technical-request-info-card text-card">
                            <div class="technical-request-info-header">
                                <i class="fas fa-sticky-note"></i>
                                {{ __('Observações') }}
                            </div>
                            <div class="technical-request-info-body">
                                <div class="technical-request-text-block">{{ $request->observacoes ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

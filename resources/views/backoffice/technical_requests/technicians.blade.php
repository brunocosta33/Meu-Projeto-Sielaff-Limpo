@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Pedidos por Responsável') }}</title>
@endsection

@section('head-scripts')
<style>
    .hotline-tech-page {
        --tech-ink: #132238;
        --tech-muted: #6c7a89;
        --tech-surface: #f6f8fb;
        --tech-border: #dfe7f1;
        --tech-primary: #0b5ed7;
        --tech-success: #208a47;
        --tech-warning: #d39d00;
        --tech-danger: #b3261e;
    }

    .tech-hero,
    .tech-panel,
    .tech-card,
    .tech-summary-card {
        background: #fff;
        border: 1px solid var(--tech-border);
        border-radius: 22px;
        box-shadow: 0 18px 40px rgba(19, 34, 56, 0.08);
    }

    .tech-hero {
        padding: 30px;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 28%),
            radial-gradient(circle at bottom left, rgba(255, 209, 102, 0.2), transparent 24%),
            linear-gradient(135deg, #0b5ed7 0%, #1b9aaa 48%, #20a464 100%);
        color: #fff;
    }

    .tech-title {
        color: #fff;
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.03em;
    }

    .tech-copy {
        color: rgba(255, 255, 255, 0.88);
        max-width: 720px;
    }

    .tech-hero .btn-outline-primary,
    .tech-hero .btn-outline-secondary {
        border-color: rgba(255, 255, 255, 0.6);
        color: #fff;
        background: rgba(255, 255, 255, 0.08);
    }

    .tech-hero .btn-outline-primary:hover,
    .tech-hero .btn-outline-secondary:hover {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        border-color: #fff;
    }

    .tech-summary-card .card-body {
        padding: 1.1rem 1.2rem;
    }

    .tech-summary-card {
        overflow: hidden;
    }

    .tech-summary-card.summary-neutral {
        background: linear-gradient(135deg, #ffffff 0%, #eef4fb 100%);
    }

    .tech-summary-card.summary-assignments {
        background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
    }

    .tech-summary-card.summary-unassigned {
        background: linear-gradient(135deg, #fff4d6 0%, #fff9ec 100%);
    }

    .tech-summary-card.summary-pending {
        background: linear-gradient(135deg, #fff1cc 0%, #fff8e5 100%);
    }

    .tech-summary-card.summary-scheduled {
        background: linear-gradient(135deg, #dff3ff 0%, #f1faff 100%);
    }

    .tech-summary-card.summary-awaiting {
        background: linear-gradient(135deg, #ffe2dc 0%, #fff2ef 100%);
    }

    .tech-summary-card.summary-completed {
        background: linear-gradient(135deg, #def7e8 0%, #effcf4 100%);
    }

    .tech-panel {
        padding: 1.3rem;
    }

    .tech-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: 18px;
    }

    .tech-card {
        padding: 1.2rem;
        background:
            radial-gradient(circle at top right, rgba(11, 94, 215, 0.07), transparent 26%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .tech-card-top {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .tech-name {
        color: var(--tech-ink);
        font-weight: 700;
        font-size: 1.05rem;
    }

    .tech-email {
        color: var(--tech-muted);
        font-size: 0.88rem;
    }

    .tech-total-badge {
        background: #edf4ff;
        color: var(--tech-primary);
        border-radius: 999px;
        padding: 0.42rem 0.8rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .tech-state-list {
        display: grid;
        gap: 10px;
        margin-bottom: 1rem;
    }

    .tech-state-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0.9rem;
        background: var(--tech-surface);
        border-radius: 14px;
        color: var(--tech-ink);
    }

    .tech-state-label {
        color: var(--tech-muted);
        font-size: 0.82rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    .tech-state-row.state-pendente {
        background: #fff6d9;
    }

    .tech-state-row.state-agendado {
        background: #e6f4ff;
    }

    .tech-state-row.state-aguarda_peca {
        background: #ffe8e2;
    }

    .tech-state-row.state-concluido {
        background: #e8f8ee;
    }

    .tech-state-row.state-cancelado {
        background: #eef2f7;
    }

    .tech-card-actions .btn {
        width: 100%;
        border-radius: 12px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row hotline-tech-page">
    <div class="col">
        <div class="tech-hero mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div>
                    <h4 class="tech-title mb-2">{{ __('Pedidos por Responsável') }}</h4>
                    <p class="tech-copy mb-0">{{ __('Consulte rapidamente quantos pedidos cada responsável tem atribuídos, o detalhe por estado e exporte uma lista Excel individual.') }}</p>
                </div>
                <div class="mt-3 mt-lg-0">
                    <a href="{{ route('backoffice.technical_requests.export', request()->only(['mes', 'data_inicio', 'data_fim'])) }}" class="btn btn-outline-primary mr-2 px-4">
                        <i class="fa fa-file-excel"></i> {{ __('Exportar Global') }}
                    </a>
                    <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar à Hotline') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="tech-panel mb-4">
            <form method="GET">
                <div class="row align-items-end">
                    <div class="col-md-4 col-xl-3 mb-3">
                        <label for="mes" class="text-uppercase text-muted small font-weight-bold">{{ __('Mês') }}</label>
                        <input type="month" name="mes" id="mes" value="{{ request('mes') }}" class="form-control">
                    </div>
                    <div class="col-md-4 col-xl-3 mb-3">
                        <label for="data_inicio" class="text-uppercase text-muted small font-weight-bold">{{ __('Data início') }}</label>
                        <input type="date" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}" class="form-control">
                    </div>
                    <div class="col-md-4 col-xl-3 mb-3">
                        <label for="data_fim" class="text-uppercase text-muted small font-weight-bold">{{ __('Data fim') }}</label>
                        <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" class="form-control">
                    </div>
                    <div class="col-md-12 col-xl-3 mb-3">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fa fa-search"></i> {{ __('Aplicar') }}
                        </button>
                        <a href="{{ route('backoffice.technical_requests.technicians') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-undo"></i> {{ __('Limpar') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 col-xl-2 mb-3">
                <div class="card tech-summary-card summary-neutral h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase d-block mb-2">{{ __('Responsáveis') }}</small>
                        <h3 class="mb-0">{{ $summary['technicians'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2 mb-3">
                <div class="card tech-summary-card summary-assignments h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase d-block mb-2">{{ __('Total Atribuídos') }}</small>
                        <h3 class="mb-0">{{ $summary['assigned_requests'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2 mb-3">
                <div class="card tech-summary-card summary-unassigned h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase d-block mb-2">{{ __('Por atribuir') }}</small>
                        <h3 class="mb-0">{{ $summary['unassigned_requests'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2 mb-3">
                <div class="card tech-summary-card summary-pending h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase d-block mb-2">{{ __('Pendentes') }}</small>
                        <h3 class="mb-0 text-warning">{{ $summary['pending'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2 mb-3">
                <div class="card tech-summary-card summary-scheduled h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase d-block mb-2">{{ __('Agendados') }}</small>
                        <h3 class="mb-0 text-info">{{ $summary['scheduled'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2 mb-3">
                <div class="card tech-summary-card summary-awaiting h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase d-block mb-2">{{ __('Aguarda Peça') }}</small>
                        <h3 class="mb-0 text-danger">{{ $summary['awaiting_part'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2 mb-3">
                <div class="card tech-summary-card summary-completed h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase d-block mb-2">{{ __('Concluídos') }}</small>
                        <h3 class="mb-0 text-success">{{ $summary['completed'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="tech-panel">
            @if($technicianStats->isNotEmpty() || $unassignedStats['total'] > 0)
                <div class="tech-grid">
                    @if($unassignedStats['total'] > 0)
                        <article class="tech-card">
                            <div class="tech-card-top">
                                <div>
                                    <div class="tech-name">{{ __('Por atribuir') }}</div>
                                    <div class="tech-email">{{ __('Pedidos ainda sem técnico associado') }}</div>
                                </div>
                                <div class="tech-total-badge">{{ $unassignedStats['total'] }} {{ __('pedidos') }}</div>
                            </div>

                                <div class="tech-state-list">
                                @foreach($statuses as $statusKey => $statusLabel)
                                    <div class="tech-state-row state-{{ $statusKey }}">
                                        <span class="tech-state-label">{{ __($statusLabel) }}</span>
                                        <strong>{{ $unassignedStats['states'][$statusKey] ?? 0 }}</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div class="tech-card-actions">
                                <a href="{{ route('backoffice.technical_requests.index', array_merge(['assigned_technician_id' => 'unassigned'], request()->only(['mes', 'data_inicio', 'data_fim']))) }}" class="btn btn-outline-secondary">
                                    <i class="fa fa-eye"></i> {{ __('Ver pedidos por atribuir') }}
                                </a>
                            </div>
                        </article>
                    @endif

                    @foreach($technicianStats as $item)
                        <article class="tech-card">
                            <div class="tech-card-top">
                                <div>
                                    <div class="tech-name">{{ $item['technician']->hasRole('user') ? __('Técnico') : __('Pessoa') }}: {{ $item['technician']->name ?: $item['technician']->email }}</div>
                                    <div class="tech-email">{{ $item['technician']->email }}</div>
                                </div>
                                <div class="tech-total-badge">{{ $item['total'] }} {{ __('pedidos') }}</div>
                            </div>

                            <div class="tech-state-list">
                                @foreach($statuses as $statusKey => $statusLabel)
                                    <div class="tech-state-row state-{{ $statusKey }}">
                                        <span class="tech-state-label">{{ __($statusLabel) }}</span>
                                        <strong>{{ $item['states'][$statusKey] ?? 0 }}</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div class="tech-card-actions">
                                <a href="{{ route('backoffice.technical_requests.export_by_technician', array_merge(['id' => $item['technician']->id], request()->only(['mes', 'data_inicio', 'data_fim']))) }}" class="btn btn-outline-primary">
                                    <i class="fa fa-file-excel"></i>
                                    {{ $item['technician']->hasRole('user') ? __('Exportar Excel deste técnico') : __('Exportar Excel desta pessoa') }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    {{ __('Ainda não existem pedidos atribuídos a técnicos.') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ str_replace('.', ' ', config('app.name')) }} - {{ __('Dashboard') }}</title>
@endsection



@push('styles')
<style>
    .today-row {
        background-color: #ffebee !important;
        color: #c62828 !important;
        font-weight: bold;
    }

    .dashboard-kpi-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        height: 100%;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        text-decoration: none !important;
    }

    .dashboard-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12);
    }

    .dashboard-kpi-card .card-body {
        padding: 1.2rem 1.3rem;
    }

    .dashboard-kpi-card.kpi-assign {
        background: linear-gradient(135deg, #0f4c81 0%, #1f76c2 100%);
    }

    .dashboard-kpi-card.kpi-pending {
        background: linear-gradient(135deg, #a7281d 0%, #dc4c3f 100%);
    }

    .dashboard-kpi-card.kpi-scheduled {
        background: linear-gradient(135deg, #145da0 0%, #2f89d9 100%);
    }

    .dashboard-kpi-card.kpi-awaiting {
        background: linear-gradient(135deg, #a56200 0%, #e0a11b 100%);
    }

    .dashboard-kpi-card.kpi-done {
        background: linear-gradient(135deg, #1f6d3b 0%, #2ea95b 100%);
    }

    .dashboard-kpi-card.kpi-overdue {
        background: linear-gradient(135deg, #20242a 0%, #444b55 100%);
    }

    .dashboard-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 1rem;
    }

    .dashboard-kpi-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        opacity: 0.82;
        margin-bottom: 0.3rem;
    }

    .dashboard-kpi-title {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0;
    }

    .dashboard-kpi-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.18);
        font-size: 1.15rem;
    }

    .dashboard-kpi-value {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.35rem;
    }

    .dashboard-kpi-copy {
        opacity: 0.82;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .dashboard-section-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .dashboard-section-card .card-header {
        border: 0;
        padding: 1rem 1.2rem;
    }

    .dashboard-section-subtitle {
        display: block;
        font-size: 0.88rem;
        opacity: 0.85;
        margin-top: 0.2rem;
    }

    .dashboard-mini-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 0.45rem;
    }

    .dashboard-agenda-item {
        border: 1px solid #e8edf3;
        border-radius: 14px;
        padding: 0.95rem 1rem;
        margin-bottom: 0.9rem;
        background: #fff;
    }

    .dashboard-priority-table th,
    .dashboard-priority-table td {
        vertical-align: middle;
    }

    .dashboard-priority-table td {
        padding: 1rem 0.85rem;
    }

    .dashboard-card-link {
        color: inherit;
        text-decoration: none;
    }

    .dashboard-card-link:hover {
        color: inherit;
        text-decoration: none;
    }

    .dashboard-kpi-hint {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 0.65rem;
        font-size: 0.82rem;
        font-weight: 600;
        opacity: 0.9;
    }

    .dashboard-empty-state {
        padding: 2rem 1.25rem;
        color: #6c757d;
        text-align: center;
    }
</style>
@endpush

@section('head-scripts')
@endsection

@section('content')

@php
    $tarefasHoje = collect($proximosAgendamentos ?? [])->filter(function($agendamento) {
        if(isset($agendamento->data_limite) && \Carbon\Carbon::parse($agendamento->data_limite)->isToday()) {
            return true;
        }
        if(isset($agendamento->initial_date) && isset($agendamento->final_date) && $agendamento->repetir) {
            $start = $agendamento->initial_date instanceof \Carbon\Carbon ? $agendamento->initial_date->copy() : \Carbon\Carbon::parse($agendamento->initial_date);
            $end = $agendamento->final_date instanceof \Carbon\Carbon ? $agendamento->final_date->copy() : \Carbon\Carbon::parse($agendamento->final_date);
            $today = \Carbon\Carbon::today();
            if ($today->between($start, $end)) {
                // Verifica se é para hoje conforme o tipo de recorrência
                $period = strtolower($agendamento->period);
                if (in_array($period, ['diario','diária','diaria','daily','day'])) {
                    return true;
                } elseif (in_array($period, ['semanal','weekly','week'])) {
                    if (is_array($agendamento->days_of_week) && count($agendamento->days_of_week)) {
                        return in_array($today->dayOfWeek, $agendamento->days_of_week);
                    } else {
                        return $today->dayOfWeek === $start->dayOfWeek;
                    }
                } elseif (in_array($period, ['mensal','monthly','month'])) {
                    return $today->day === $start->day;
                }
            }
        }
        return false;
    });
@endphp

<div class="row">
    <div class="col" style="padding:0">
        @include('flash::message')
        @if($tarefasHoje->count())
            <div class="alert alert-warning d-flex align-items-center mt-2" style="font-size:1.1em;">
                <i class="fas fa-bell fa-2x me-3 text-danger"></i>
                <div>
                    <strong>{{ __('Atenção!') }}</strong> {{ __('Você tem') }} {{ $tarefasHoje->count() }} {{ $tarefasHoje->count() > 1 ? __('tarefas agendadas para hoje:') : __('tarefa agendada para hoje:') }}
                    <ul class="mb-0 ps-3">
                        @foreach($tarefasHoje as $tarefaHoje)
                            <li>
                                <span class="fw-bold @if($tarefaHoje->isOverdue ?? false) text-danger @endif">
                                    {{ $tarefaHoje->task->title ?? __('Tarefa') }}
                                    @if($tarefaHoje->repetir ?? false)
                                        <span class="badge bg-info ms-2">{{ __('Recorrente') }}</span>
                                    @endif
                                @if($tarefaHoje->isOverdue ?? false)
                                        <span class="badge bg-danger ms-2">{{ __('Atrasada') }}</span>
                                    @endif
                                </span>
                                @if($tarefaHoje->hora_limite)
                                    {{ __('às') }} <span class="text-primary">{{ \Carbon\Carbon::parse($tarefaHoje->hora_limite)->format('H:i') }}</span>
                                @endif
                                @if($tarefaHoje->description)
                                    <br><span class="text-muted">{{ $tarefaHoje->description }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="row mb-4">
    @if($proximosAgendamentos->count())
        <div class="col-12 mb-3">
            <div class="alert alert-info shadow-sm d-flex align-items-center" style="font-size:1.1em;">
                <i class="fas fa-calendar-alt fa-2x me-3 text-primary"></i>
                <div>
                    <strong>{{ __('Próximos Agendamentos:') }}</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($proximosAgendamentos as $agendamento)
                            @php
                                $isHoje = false;
                                if(isset($agendamento->data_limite) && \Carbon\Carbon::parse($agendamento->data_limite)->isToday()) {
                                    $isHoje = true;
                                } elseif(isset($agendamento->initial_date) && isset($agendamento->final_date) && $agendamento->repetir) {
                                    $start = $agendamento->initial_date instanceof \Carbon\Carbon ? $agendamento->initial_date->copy() : \Carbon\Carbon::parse($agendamento->initial_date);
                                    $end = $agendamento->final_date instanceof \Carbon\Carbon ? $agendamento->final_date->copy() : \Carbon\Carbon::parse($agendamento->final_date);
                                    $today = \Carbon\Carbon::today();
                                    if ($today->between($start, $end)) {
                                        $period = strtolower($agendamento->period);
                                        if (in_array($period, ['diario','diária','diaria','daily','day'])) {
                                            $isHoje = true;
                                        } elseif (in_array($period, ['semanal','weekly','week'])) {
                                            if (is_array($agendamento->days_of_week) && count($agendamento->days_of_week)) {
                                                $isHoje = in_array($today->dayOfWeek, $agendamento->days_of_week);
                                            } else {
                                                $isHoje = $today->dayOfWeek === $start->dayOfWeek;
                                            }
                                        } elseif (in_array($period, ['mensal','monthly','month'])) {
                                            $isHoje = $today->day === $start->day;
                                        }
                                    }
                                }
                            @endphp
                            @if(!$isHoje)
                            <li>
                                <span class="fw-bold">
                                    {{ $agendamento->task->title ?? __('Tarefa') }}
                                    @if($agendamento->repetir ?? false)
                                        <span class="badge bg-info ms-2">{{ __('Recorrente') }}</span>
                                    @endif
                                </span>
                                @if($agendamento->data_limite)
                                    {{ __('em') }} <span class="text-primary">{{ \Carbon\Carbon::parse($agendamento->data_limite)->format('d/m/Y') }}</span>
                                    {{ __('às') }} <span class="text-primary">{{ $agendamento->hora_limite ? \Carbon\Carbon::parse($agendamento->hora_limite)->format('H:i') : '-' }}</span>
                                @elseif($agendamento->initial_date)
                                    {{ __('a partir de') }} <span class="text-primary">{{ \Carbon\Carbon::parse($agendamento->initial_date)->format('d/m/Y') }}</span>
                                @endif
                                @if($agendamento->period)
                                    <span class="badge bg-secondary ms-2">{{ ucfirst($agendamento->period) }}</span>
                                @endif
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<style>
    #calendar-agendamentos {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        padding: 16px;
        margin-bottom: 2rem;
    }
</style>
@endpush
<div class="row mb-4">
    <div class="col-12">
        <div id="calendar-agendamentos"></div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar-agendamentos');
        if(calendarEl) {
            var events = @json($eventosCalendario);
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: @json(app()->getLocale()),
                height: 500,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: events,
                eventDidMount: function(info) {
                    var tooltip = document.createElement('div');
                    tooltip.className = 'fc-tooltip-custom';
                    tooltip.style.position = 'absolute';
                    tooltip.style.zIndex = 9999;
                    tooltip.style.background = '#fff';
                    tooltip.style.border = '1px solid #ccc';
                    tooltip.style.padding = '8px 12px';
                    tooltip.style.borderRadius = '8px';
                    tooltip.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
                    tooltip.style.display = 'none';
                    var html = '<strong>' + info.event.title + '</strong>';
                    if(info.event.extendedProps.prioridade) html += '<br><span><b>' + @json(__('Prioridade')) + ':</b> ' + info.event.extendedProps.prioridade + '</span>';
                    if(info.event.extendedProps.recorrencia) html += '<br><span><b>' + @json(__('Recorrência')) + ':</b> ' + info.event.extendedProps.recorrencia + '</span>';
                    if(info.event.extendedProps.description) html += '<br><span>' + info.event.extendedProps.description + '</span>';
                    tooltip.innerHTML = html;
                    document.body.appendChild(tooltip);
                    info.el.addEventListener('mouseenter', function(e) {
                        tooltip.style.display = 'block';
                        tooltip.style.left = (e.pageX + 10) + 'px';
                        tooltip.style.top = (e.pageY + 10) + 'px';
                    });
                    info.el.addEventListener('mousemove', function(e) {
                        tooltip.style.left = (e.pageX + 10) + 'px';
                        tooltip.style.top = (e.pageY + 10) + 'px';
                    });
                    info.el.addEventListener('mouseleave', function() {
                        tooltip.style.display = 'none';
                    });
                },
                dayCellDidMount: function(arg) {
                    var today = new Date();
                    if(arg.date.getDate() === today.getDate() && arg.date.getMonth() === today.getMonth() && arg.date.getFullYear() === today.getFullYear()) {
                        arg.el.style.backgroundColor = '#ffebee';
                        var num = arg.el.querySelector('.fc-daygrid-day-number');
                        if(num) {
                            num.style.color = '#c62828';
                            num.style.fontWeight = 'bold';
                            num.style.fontSize = '1.2em';
                        }
                    }
                }
            });
            calendar.render();
        }
    });
</script>
@endpush
    <div class="col-md-6 col-xl-3 mb-3">
        <a href="{{ route('backoffice.technical_requests.index', ['assigned_technician_id' => 'unassigned']) }}" class="card text-white dashboard-kpi-card dashboard-card-link kpi-assign">
            <div class="card-body">
                <div class="dashboard-kpi-top">
                    <div>
                        <div class="dashboard-kpi-label">{{ __('Hotline') }}</div>
                        <h5 class="dashboard-kpi-title">{{ __('Pedidos por Atribuir') }}</h5>
                    </div>
                    <span class="dashboard-kpi-icon">
                        <i class="fas fa-user-plus"></i>
                    </span>
                </div>
                <div class="dashboard-kpi-value">{{ $totalPedidosPorAtribuir ?? '0' }}</div>
                <p class="dashboard-kpi-copy">{{ __('Pedidos ainda sem responsável definido.') }}</p>
                <span class="dashboard-kpi-hint"><i class="fas fa-arrow-right"></i> {{ __('Abrir pedidos sem responsável') }}</span>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <a href="{{ route('backoffice.technical_requests.index', ['estado' => ['pendente']]) }}" class="card text-white dashboard-kpi-card dashboard-card-link kpi-pending">
            <div class="card-body">
                <div class="dashboard-kpi-top">
                    <div>
                        <div class="dashboard-kpi-label">{{ __('Hotline') }}</div>
                        <h5 class="dashboard-kpi-title">{{ __('Pedidos Pendentes') }}</h5>
                    </div>
                    <span class="dashboard-kpi-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </span>
                </div>
                <div class="dashboard-kpi-value">{{ $totalPedidosPendentes ?? '0' }}</div>
                <p class="dashboard-kpi-copy">{{ __('Pedidos abertos que ainda precisam de seguimento.') }}</p>
                <span class="dashboard-kpi-hint"><i class="fas fa-arrow-right"></i> {{ __('Ver pedidos pendentes') }}</span>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <a href="{{ route('backoffice.technical_requests.index', ['estado' => ['agendado']]) }}" class="card text-white dashboard-kpi-card dashboard-card-link kpi-scheduled">
            <div class="card-body">
                <div class="dashboard-kpi-top">
                    <div>
                        <div class="dashboard-kpi-label">{{ __('Hotline') }}</div>
                        <h5 class="dashboard-kpi-title">{{ __('Pedidos Agendados') }}</h5>
                    </div>
                    <span class="dashboard-kpi-icon">
                        <i class="fas fa-calendar-check"></i>
                    </span>
                </div>
                <div class="dashboard-kpi-value">{{ $totalPedidosAgendados ?? '0' }}</div>
                <p class="dashboard-kpi-copy">{{ __('Intervenções já marcadas para acompanhamento.') }}</p>
                <span class="dashboard-kpi-hint"><i class="fas fa-arrow-right"></i> {{ __('Abrir pedidos agendados') }}</span>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <a href="{{ route('backoffice.technical_requests.index', ['estado' => ['aguarda_peca']]) }}" class="card text-white dashboard-kpi-card dashboard-card-link kpi-awaiting">
            <div class="card-body">
                <div class="dashboard-kpi-top">
                    <div>
                        <div class="dashboard-kpi-label">{{ __('Hotline') }}</div>
                        <h5 class="dashboard-kpi-title">{{ __('Aguarda Peça') }}</h5>
                    </div>
                    <span class="dashboard-kpi-icon">
                        <i class="fas fa-tools"></i>
                    </span>
                </div>
                <div class="dashboard-kpi-value">{{ $totalAguardaPeca ?? '0' }}</div>
                <p class="dashboard-kpi-copy">{{ __('Pedidos bloqueados à espera de material.') }}</p>
                <span class="dashboard-kpi-hint"><i class="fas fa-arrow-right"></i> {{ __('Ver pedidos à espera de peça') }}</span>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <a href="{{ route('backoffice.technical_requests.index', ['estado' => ['concluido'], 'data_inicio' => now()->format('Y-m-d'), 'data_fim' => now()->format('Y-m-d')]) }}" class="card text-white dashboard-kpi-card dashboard-card-link kpi-done">
            <div class="card-body">
                <div class="dashboard-kpi-top">
                    <div>
                        <div class="dashboard-kpi-label">{{ __('Produtividade') }}</div>
                        <h5 class="dashboard-kpi-title">{{ __('Concluídos Hoje') }}</h5>
                    </div>
                    <span class="dashboard-kpi-icon">
                        <i class="fas fa-check-circle"></i>
                    </span>
                </div>
                <div class="dashboard-kpi-value">{{ $totalConcluidosHoje ?? '0' }}</div>
                <p class="dashboard-kpi-copy">{{ __('Esta semana') }}: {{ $totalConcluidosSemana ?? '0' }} {{ __('pedidos concluídos.') }}</p>
                <span class="dashboard-kpi-hint"><i class="fas fa-arrow-right"></i> {{ __('Abrir concluídos de hoje') }}</span>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <a href="{{ route('backoffice.task_schedules.minhas') }}" class="card text-white dashboard-kpi-card dashboard-card-link kpi-overdue">
            <div class="card-body">
                <div class="dashboard-kpi-top">
                    <div>
                        <div class="dashboard-kpi-label">{{ __('Agenda') }}</div>
                        <h5 class="dashboard-kpi-title">{{ __('Tarefas em Atraso') }}</h5>
                    </div>
                    <span class="dashboard-kpi-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                </div>
                <div class="dashboard-kpi-value">{{ $totalTarefasAtrasadas ?? '0' }}</div>
                <p class="dashboard-kpi-copy">{{ __('Tarefas vencidas que continuam por concluir.') }}</p>
                <span class="dashboard-kpi-hint"><i class="fas fa-arrow-right"></i> {{ __('Abrir a minha agenda') }}</span>
            </div>
        </a>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-7 mb-4">
            <div class="card dashboard-section-card">
                <div class="card-header bg-danger text-white">
                <strong>{{ __('Pedidos Prioritários') }}</strong>
                <span class="dashboard-section-subtitle">{{ __('Pedidos que merecem acompanhamento mais rápido pela prioridade, estado e antiguidade.') }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0 dashboard-priority-table">
                    <thead>
                        <tr>
                            <th>{{ __('Pedido') }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th>{{ __('Responsável') }}</th>
                            <th>{{ __('Urgência') }}</th>
                            <th class="text-right">{{ __('Ação') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidosPrioritarios as $pedidoPrioritario)
                        <tr>
                            <td>
                                {{ $pedidoPrioritario->store->codigo_loja ?? '-' }} - {{ $pedidoPrioritario->store->nome_loja ?? '-' }}
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($pedidoPrioritario->descricao_problema, 55) }}</div>
                                <div class="dashboard-mini-meta">
                                    <span class="badge badge-light">{{ __('Aberto') }} {{ \Carbon\Carbon::parse($pedidoPrioritario->data_pedido)->diffForHumans() }}</span>
                                    @if($pedidoPrioritario->data_agendamento)
                                        <span class="badge badge-info">{{ __('Agendado') }} {{ \Carbon\Carbon::parse($pedidoPrioritario->data_agendamento)->format('d/m H:i') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge
                                    @switch($pedidoPrioritario->estado)
                                        @case('pendente') bg-warning text-dark @break
                                        @case('agendado') bg-info text-white @break
                                        @case('aguarda_peca') bg-danger text-white @break
                                        @default bg-secondary
                                    @endswitch">
                                    {{ ucfirst(str_replace('_', ' ', $pedidoPrioritario->estado)) }}
                                </span>
                            </td>
                            <td>
                                @if($pedidoPrioritario->assignedTechnician)
                                    <div class="font-weight-bold">{{ $pedidoPrioritario->assignedTechnician->name ?? $pedidoPrioritario->assignedTechnician->email }}</div>
                                    <div class="small text-muted">{{ $pedidoPrioritario->assignedPersonTypeLabel() }}</div>
                                @else
                                    <span class="badge badge-light">{{ __('Por atribuir') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge
                                    @switch($pedidoPrioritario->prioridade)
                                        @case('alta') bg-danger text-white @break
                                        @case('media') bg-warning text-dark @break
                                        @case('baixa') bg-info text-white @break
                                        @default bg-secondary
                                    @endswitch">
                                    {{ ucfirst($pedidoPrioritario->prioridade) }}
                                </span>
                                <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($pedidoPrioritario->data_pedido)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('backoffice.technical_requests.show', $pedidoPrioritario->id) }}" class="btn btn-outline-secondary btn-sm">
                                    {{ __('Abrir') }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="dashboard-empty-state">{{ __('Sem pedidos críticos neste momento.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white text-right">
                <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary btn-sm">
                    {{ __('Ver todos os pedidos') }} <i class="fa fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-4">
        <div class="card dashboard-section-card h-100">
            <div class="card-header bg-info text-white">
                <strong>{{ __('Agenda de Hoje e Próximos Dias') }}</strong>
                <span class="dashboard-section-subtitle">{{ __('Destaque para tarefas de hoje, amanhã e tarefas já em atraso.') }}</span>
            </div>
            <div class="card-body">
                @forelse($proximosAgendamentos->take(6) as $agendamento)
                    <div class="dashboard-agenda-item">
                        <div class="font-weight-bold">
                            {{ $agendamento->task->title ?? __('Tarefa') }}
                            @if($agendamento->isOverdue ?? false)
                                <span class="badge badge-danger ml-2">{{ __('Em atraso') }}</span>
                            @elseif(isset($agendamento->data_limite) && \Carbon\Carbon::parse($agendamento->data_limite)->isToday())
                                <span class="badge badge-warning ml-2">{{ __('Hoje') }}</span>
                            @elseif(isset($agendamento->data_limite) && \Carbon\Carbon::parse($agendamento->data_limite)->isTomorrow())
                                <span class="badge badge-info ml-2">{{ __('Amanhã') }}</span>
                            @endif
                        </div>
                        <div class="text-muted small mt-1">
                            @if($agendamento->data_limite)
                                {{ \Carbon\Carbon::parse($agendamento->data_limite)->format('d/m/Y') }}
                            @elseif($agendamento->initial_date)
                                {{ \Carbon\Carbon::parse($agendamento->initial_date)->format('d/m/Y') }}
                            @endif
                            @if($agendamento->hora_limite)
                                {{ __('às') }} {{ \Carbon\Carbon::parse($agendamento->hora_limite)->format('H:i') }}
                            @endif
                        </div>
                        @if($agendamento->description)
                            <div class="small text-muted mt-2">{{ \Illuminate\Support\Str::limit($agendamento->description, 90) }}</div>
                        @endif
                    </div>
                @empty
                    <div class="dashboard-empty-state">{{ __('Sem agendamentos próximos.') }}</div>
                @endforelse
            </div>
            <div class="card-footer bg-white text-right">
                <a href="{{ route('backoffice.task_schedules.minhas') }}" class="btn btn-outline-secondary btn-sm">
                    {{ __('Ver agenda completa') }} <i class="fa fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('foot-scripts')
@endsection

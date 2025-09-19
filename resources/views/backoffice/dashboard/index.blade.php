@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ str_replace('.', ' ', config('app.name')) }} - Dashboard</title>
@endsection



@push('styles')
<style>
    .today-row {
        background-color: #ffebee !important;
        color: #c62828 !important;
        font-weight: bold;
    }
</style>
@endpush

@section('head-scripts')
@endsection

@section('content')
<div class="row">
    <div class="col" style="padding:0">
        @include('flash::message')
    </div>
</div>

<div class="row mb-4">
    @if($proximosAgendamentos->count())
        <div class="col-12 mb-3">
            <div class="alert alert-info shadow-sm d-flex align-items-center" style="font-size:1.1em;">
                <i class="fas fa-calendar-alt fa-2x me-3 text-primary"></i>
                <div>
                    <strong>Próximos Agendamentos:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($proximosAgendamentos as $agendamento)
                            <li>
                                <span class="fw-bold">{{ $agendamento->task->title ?? 'Tarefa' }}</span>
                                @if($agendamento->data_limite)
                                    em <span class="text-primary">{{ \Carbon\Carbon::parse($agendamento->data_limite)->format('d/m/Y') }}</span>
                                    às <span class="text-primary">{{ $agendamento->hora_limite ? \Carbon\Carbon::parse($agendamento->hora_limite)->format('H:i') : '-' }}</span>
                                @elseif($agendamento->initial_date)
                                    a partir de <span class="text-primary">{{ \Carbon\Carbon::parse($agendamento->initial_date)->format('d/m/Y') }}</span>
                                @endif
                                @if($agendamento->period)
                                    <span class="badge bg-secondary ms-2">{{ ucfirst($agendamento->period) }}</span>
                                @endif
                            </li>
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
                locale: 'pt',
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
                    if(info.event.extendedProps.prioridade) html += '<br><span><b>Prioridade:</b> ' + info.event.extendedProps.prioridade + '</span>';
                    if(info.event.extendedProps.recorrencia) html += '<br><span><b>Recorrência:</b> ' + info.event.extendedProps.recorrencia + '</span>';
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
                    // Garante comparação exata de datas (YYYY-MM-DD)
                    var cellDate = arg.date.toISOString().slice(0,10);
                    var hasEvent = events.some(function(ev) {
                        // Evita erro se ev.start for undefined
                        if (!ev.start) return false;
                        // Se ev.start for string com hora, pega só a data
                        var eventDate = ev.start.length > 10 ? ev.start.slice(0,10) : ev.start;
                        return eventDate === cellDate;
                    });
                    if(hasEvent) {
                        arg.el.classList.add('fc-has-event');
                    }
                }
            });
            calendar.render();
        }
    });
</script>
@endpush
    <div class="col-md-6">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total de Instalações Agendadas</h5>
                <p class="card-text display-4">{{ $totalInstallations ?? '0' }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Tarefas Pendentes</h5>
                <p class="card-text display-4">{{ $tarefasPendentes ?? '0' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- INSTALAÇÕES NAS PRÓXIMAS 3 SEMANAS --}}
@if($instalacoesBreve->count())
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <strong>Instalações nas Próximas 3 Semanas</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Loja</th>
                            <th>Data da Instalação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instalacoesBreve as $instalacao)
                        @php
                        $visitada = $instalacao->pdfs && count($instalacao->pdfs) > 0;
                        $date = \Carbon\Carbon::parse($instalacao->scheduled_date)->timezone('Europe/Lisbon');
                        $isToday = $date->isToday();
                        @endphp
                        <tr class="{{ $isToday ? 'today-row' : '' }}">
                            <td>
                                {{ $instalacao->store->codigo_loja ?? '-' }} - {{ $instalacao->store->nome_loja ?? '-' }}
                                @if($visitada)
                                <span class="badge badge-success ml-2">
                                    <i class="fa fa-check-circle"></i> Visitada
                                </span>
                                @endif
                            </td>
                            <td>
                                {{ $date->format('d/m/Y') }}
                                @if($isToday)
                                <br>
                                <span class="badge badge-danger">Hoje a decorrer a Instalação</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- .card-body --}}
        </div> {{-- .card --}}
    </div> {{-- .col --}}
</div> {{-- .row --}}
@endif

{{-- PEDIDOS TÉCNICOS PENDENTES / AGENDADOS --}}
@if($pedidosPendentes->count())
<div class="row mt-4">
    <div class="col-12 mb-3">
        <div class="px-3 py-2 rounded bg-warning text-dark font-weight-bold">
            Pedidos Técnicos Pendentes / Agendados
        </div>
    </div>

    @foreach($pedidosPendentes as $pedido)
    <div class="col-md-4 col-lg-3 mb-3">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body p-3">
                <h6 class="font-weight-bold mb-1">
                    {{ $pedido->store->codigo_loja ?? '---' }} - {{ $pedido->store->nome_loja ?? '-' }}
                </h6>
                <p class="mb-2 text-muted" style="font-size: 0.875rem;">
                    {{ Str::limit($pedido->descricao_problema, 50) }}
                </p>

                <div class="d-flex justify-content-between align-items-center">
                    {{-- Prioridade --}}
                    <span class="badge 
                        @switch($pedido->prioridade)
                            @case('baixa') bg-info @break
                            @case('media') bg-warning text-dark @break
                            @case('alta') bg-danger @break
                            @default bg-light
                        @endswitch">
                        {{ ucfirst($pedido->prioridade) }}
                    </span>

                    {{-- Estado + data agendamento --}}
                    <span class="badge 
                        @switch($pedido->estado)
                            @case('pendente') bg-warning text-dark @break
                            @case('agendado') bg-info text-white @break
                            @default bg-light
                        @endswitch"
                        style="display:block; line-height:1.2; text-align:center;">
                        {{ ucfirst($pedido->estado) }}

                        @if($pedido->estado === 'agendado' && $pedido->data_agendamento)
                        <br>
                        <small style="color:#ffffff; font-weight:bold; font-size:0.8rem;">
                            <i class="fa fa-calendar-check"></i>
                            {{ \Carbon\Carbon::parse($pedido->data_agendamento)->format('d/m/Y H:i') }}
                        </small>
                        @endif
                    </span>

                </div>

                <small class="text-muted d-block mt-2">
                    <i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y') }}
                </small>
            </div>
        </div>
    </div>
    @endforeach

    <div class="col-12 mt-2 text-right">
        <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary btn-sm">
            Ver todos os pedidos <i class="fa fa-arrow-right ml-1"></i>
        </a>
    </div>
</div>
@endif
@endsection

@section('foot-scripts')
@endsection
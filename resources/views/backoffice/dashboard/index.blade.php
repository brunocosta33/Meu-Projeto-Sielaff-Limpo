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
    /* Cartões compactos por baixo dos indicadores */
    .dashboard-compact .dashboard-section-card { font-size: .88rem; }
    .dashboard-compact .card-header { padding: .5rem .9rem; }
    .dashboard-compact .dashboard-section-subtitle { display: none; }
    .dashboard-compact .card-body { max-height: 300px; overflow-y: auto; }
    .dashboard-compact .card-body:not(.p-0) { padding: .6rem .9rem; }
    .dashboard-compact .card-footer { padding: .4rem .9rem; }
    .dashboard-compact .dashboard-agenda-item { padding-top: .35rem; padding-bottom: .35rem; }
    /* ===== Calendário da dashboard ===== */
    .cal-card { background: #fff; border-radius: 18px; box-shadow: 0 12px 28px rgba(15, 23, 42, .1); padding: 0 0 18px; margin-bottom: 2rem; overflow: hidden; }
    #calendar-agendamentos.fc { --fc-border-color: #d9e1ec; --fc-today-bg-color: #dcebff; --fc-neutral-bg-color: #eef2f8; font-size: .9rem; }
    #calendar-agendamentos .fc-toolbar.fc-header-toolbar { margin: 0 0 14px; padding: 16px 20px; background: linear-gradient(135deg, #0f4c81 0%, #1f76c2 100%); }
    #calendar-agendamentos .fc-view-harness { margin: 0 18px; }
    #calendar-agendamentos .fc-toolbar-title { font-size: 1.4rem; font-weight: 700; color: #fff; }
    #calendar-agendamentos .fc-toolbar-title::first-letter { text-transform: uppercase; }
    #calendar-agendamentos .fc-button { background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .35); color: #fff; font-weight: 600; font-size: .82rem; padding: .4rem .85rem; box-shadow: none !important; text-transform: none; }
    #calendar-agendamentos .fc-button:hover { background: rgba(255, 255, 255, .26); color: #fff; }
    #calendar-agendamentos .fc-button-primary:not(:disabled).fc-button-active,
    #calendar-agendamentos .fc-button-primary:not(:disabled):active { background: #fff; border-color: #fff; color: #0f4c81; }
    #calendar-agendamentos .fc-today-button { background: #fff; border-color: #fff; color: #0f4c81; border-radius: 10px; margin-left: .5rem !important; }
    #calendar-agendamentos .fc-today-button:disabled { background: rgba(255, 255, 255, .55); border-color: transparent; color: #0f4c81; opacity: 1; }
    #calendar-agendamentos .fc-button-group > .fc-button:first-child { border-radius: 10px 0 0 10px; }
    #calendar-agendamentos .fc-button-group > .fc-button:last-child { border-radius: 0 10px 10px 0; }
    #calendar-agendamentos .fc-scrollgrid { border-radius: 12px; overflow: hidden; border-color: #d9e1ec; }
    #calendar-agendamentos .fc-col-header-cell { background: #1d3557; padding: .5rem 0; border-color: #1d3557; }
    #calendar-agendamentos .fc-col-header-cell-cushion { color: #fff; font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; text-decoration: none; }
    #calendar-agendamentos .fc-daygrid-day, #calendar-agendamentos .fc-timegrid-col { background: #f4f7fb; }
    #calendar-agendamentos .fc-day-sat, #calendar-agendamentos .fc-day-sun { background: #e7edf6 !important; }
    #calendar-agendamentos .fc-col-header-cell.fc-day-sat, #calendar-agendamentos .fc-col-header-cell.fc-day-sun { background: #2b4a75 !important; }
    #calendar-agendamentos .fc-day-other { background: #eceff4 !important; }
    #calendar-agendamentos .fc-col-header-cell.fc-day-today { background: #1f6fd1 !important; box-shadow: none; }
    #calendar-agendamentos .fc-day-today { background: #dcebff !important; box-shadow: inset 0 0 0 2px #1f6fd1; }
    #calendar-agendamentos .fc-daygrid-day-frame { min-height: 96px; padding: 2px 3px 4px; }
    #calendar-agendamentos .fc-daygrid-day-number { font-size: .82rem; font-weight: 700; color: #3c4b5f; padding: 6px 7px; text-decoration: none; }
    #calendar-agendamentos .fc-day-other .fc-daygrid-day-number { opacity: .4; }
    #calendar-agendamentos .fc-day-today .fc-daygrid-day-number { background: #1f6fd1; color: #fff; border-radius: 999px; min-width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; margin: 3px; padding: 0 6px; }
    #calendar-agendamentos .fc-timegrid-slot-label-cushion, #calendar-agendamentos .fc-timegrid-axis-cushion { color: #3c4b5f; font-weight: 600; font-size: .78rem; }
    #calendar-agendamentos a.fc-event { cursor: default; }
    #calendar-agendamentos .fc-daygrid-event-harness { margin-bottom: 3px; }

    /* Turnos (Hotline e Piquete): blocos de cor cheia */
    .fc-duty-event { background: transparent !important; border: 0 !important; box-shadow: none !important; }
    .fc-duty-chip { display: flex; align-items: center; gap: 6px; width: 100%; padding: 4px 7px 4px 4px; border-radius: 8px; overflow: hidden;
                    background: var(--duty); color: #fff; line-height: 1.15; box-shadow: 0 1px 3px rgba(15, 23, 42, .18); }
    .fc-duty-avatar { flex: 0 0 24px; height: 24px; border-radius: 50%; background: #fff; color: var(--duty); font-size: .62rem; font-weight: 800;
                      display: flex; align-items: center; justify-content: center; letter-spacing: .02em; }
    .fc-duty-text { min-width: 0; flex: 1; }
    .fc-duty-name { display: block; font-weight: 700; font-size: .78rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fc-duty-meta { display: flex; align-items: center; gap: 4px; font-size: .68rem; color: rgba(255, 255, 255, .9); white-space: nowrap; overflow: hidden; }
    .fc-duty-meta i { font-size: .62rem; color: #fff; opacity: .85; }
    .fc-duty-zone { font-size: .56rem; font-weight: 800; text-transform: uppercase; background: rgba(255, 255, 255, .92); color: var(--duty); border-radius: 4px; padding: 0 4px; }
    .fc-duty-chip.is-override { box-shadow: 0 0 0 2px #ffc107, 0 2px 6px rgba(0, 0, 0, .15); }
    .fc-duty-chip.is-uncovered { --duty: #c62828; }
    .fc-duty-swap { color: #ffe082 !important; opacity: 1 !important; font-size: .7rem !important; }
    .fc-timegrid-event .fc-duty-chip { height: 100%; align-items: flex-start; padding-top: 6px; }

    /* Ausências, férias e feriados (pílulas de dia inteiro) */
    .fc-pill-event { background: transparent !important; border: 0 !important; }
    .fc-pill { display: flex; align-items: center; gap: 6px; width: 100%; padding: 3px 10px; border-radius: 999px; font-size: .74rem; font-weight: 700;
               background: var(--pill); color: #fff; overflow: hidden; white-space: nowrap; box-shadow: 0 2px 6px color-mix(in srgb, var(--pill) 35%, transparent); }
    .fc-pill i { font-size: .7rem; }
    .fc-pill span { overflow: hidden; text-overflow: ellipsis; }

    /* Tarefas */
    #calendar-agendamentos .fc-task-event { border-radius: 7px !important; border: 0 !important; padding: 1px 5px; font-size: .74rem; font-weight: 600; }

    /* Tooltip */
    .fc-tooltip-custom { position: absolute; z-index: 9999; display: none; max-width: 280px; background: #132238; color: #fff; padding: 9px 12px;
                         border-radius: 10px; box-shadow: 0 10px 24px rgba(15, 23, 42, .25); font-size: .8rem; line-height: 1.4; pointer-events: none; }
    .fc-tooltip-custom strong { display: block; font-size: .85rem; margin-bottom: 2px; }
    .fc-tooltip-custom .muted { color: #b8c4d4; }

    /* Legenda por baixo do calendário */
    .cal-legend { display: flex; flex-wrap: wrap; align-items: center; gap: .45rem 1.1rem; margin: 14px 18px 0; padding: 10px 14px; background: #f4f7fb; border-radius: 12px; font-size: .8rem; color: #3c4b5f; }
    .cal-legend:empty { display: none; }
    .cal-legend .lg-title { font-weight: 700; text-transform: uppercase; font-size: .68rem; letter-spacing: .07em; color: #6c7a89; }
    .cal-legend .lg-item { display: inline-flex; align-items: center; gap: .4rem; white-space: nowrap; }
    .cal-legend .lg-dot { width: 22px; height: 22px; border-radius: 50%; background: var(--c); color: #fff; font-size: .58rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
    .cal-legend .lg-pill { width: 26px; height: 12px; border-radius: 999px; background: var(--c); display: inline-block; }
    .cal-legend .lg-swap { width: 22px; height: 14px; border-radius: 4px; background: #8a96a8; box-shadow: 0 0 0 2px #ffc107; display: inline-block; }
    .cal-legend .lg-sep { width: 1px; height: 18px; background: #d9e1ec; }
</style>
@endpush
<div class="row mb-4">
    <div class="col-12">
        <div class="cal-card">
            <div id="calendar-agendamentos"></div>
            <div id="calendar-legend" class="cal-legend"></div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar-agendamentos');
        if(calendarEl) {
            var events = @json($eventosCalendario);
            var esc = function (t) { var d = document.createElement('div'); d.textContent = t || ''; return d.innerHTML; };
            var initials = function (name) {
                var parts = (name || '').split(' · ')[0].trim().split(/\s+/);
                return ((parts[0] || '')[0] || '') + (parts.length > 1 ? parts[parts.length - 1][0] : '');
            };
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: @json(app()->getLocale()),
                firstDay: 1, // semana de segunda a domingo
                buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', day: 'Dia', list: 'Lista' },
                allDayText: 'Dia todo',
                moreLinkText: 'mais',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                nextDayThreshold: '09:00',
                eventSources: [
                    events,
                    { url: @json(route('backoffice.hr.holidays.events')) },
                    @if(\App\Services\HrAccess::canAccess(auth()->user()))
                    { url: @json(route('backoffice.hr.calendar.events')), display: 'block', className: 'fc-duty-event' },
                    { url: @json(route('backoffice.hr.absences.events')), display: 'block' }
                    @endif
                ],
                slotEventOverlap: false,
                slotMinTime: '07:00:00',
                slotMaxTime: '21:00:00',
            eventContent: function (arg) {
                var e = arg.event, p = e.extendedProps;
                if (p.funcao) {
                    var piquete = /piquete/i.test(p.funcao);
                    var parts = (p.nome || '').split(' · ');
                    var uncovered = /sem substituto/i.test(p.nome || '');
                    return { html:
                        '<div class="fc-duty-chip' + (p.substituicao ? ' is-override' : '') + (uncovered ? ' is-uncovered' : '') + '" style="--duty:' + e.backgroundColor + '">' +
                            '<span class="fc-duty-avatar">' + (uncovered ? '!' : esc(initials(parts[0]).toUpperCase())) + '</span>' +
                            '<span class="fc-duty-text">' +
                                '<span class="fc-duty-name">' + esc(parts[0]) + '</span>' +
                                '<span class="fc-duty-meta"><i class="fas ' + (piquete ? 'fa-tools' : 'fa-headset') + '"></i>' + esc(p.horario) +
                                    (parts[1] ? ' <span class="fc-duty-zone">' + esc(parts[1]) + '</span>' : '') +
                                    (p.substituicao ? ' <i class="fas fa-exchange-alt fc-duty-swap"></i>' : '') +
                                '</span>' +
                            '</span>' +
                        '</div>' };
                }
                if (p.ausencia || p.feriado) {
                    var icon = p.feriado ? 'fa-flag' : (/^Férias/.test(e.title) ? 'fa-umbrella-beach' : 'fa-user-clock');
                    return { html: '<div class="fc-pill" style="--pill:' + e.backgroundColor + '"><i class="fas ' + icon + '"></i><span>' + esc(e.title) + '</span></div>' };
                }
                return true;
            },
            eventClassNames: function (arg) {
                var p = arg.event.extendedProps;
                if (p.funcao) return ['fc-duty-event'];
                if (p.ausencia || p.feriado) return ['fc-pill-event'];
                return arg.event.display === 'background' ? [] : ['fc-task-event'];
            },
            eventClick: function (info) { info.jsEvent.preventDefault(); },
            // Legenda: pessoas (com a sua cor) e significado das restantes marcas
            eventsSet: function (events) {
                var box = document.getElementById('calendar-legend');
                if (!box) return;
                var people = {}, hasHoliday = false, hasVacation = false, hasAbsent = false, hasSwap = false, hasUncovered = false;
                events.forEach(function (e) {
                    var p = e.extendedProps;
                    if (p.funcao) {
                        var name = (p.nome || '').split(' · ')[0];
                        if (/sem substituto/i.test(name)) { hasUncovered = true; }
                        else if (!people[name]) { people[name] = { color: e.backgroundColor, piquete: /piquete/i.test(p.funcao) }; }
                        if (p.substituicao) hasSwap = true;
                    } else if (p.feriado) { hasHoliday = true; }
                    else if (p.ausencia) { if (/^Férias/.test(e.title)) hasVacation = true; else hasAbsent = true; }
                });
                var names = Object.keys(people).sort(function (a, b) { return (people[a].piquete - people[b].piquete) || a.localeCompare(b); });
                if (!names.length && !hasHoliday && !hasVacation && !hasAbsent) { box.innerHTML = ''; return; }
                var html = '';
                if (names.length) {
                    html += '<span class="lg-title">Escala</span>';
                    names.forEach(function (n) {
                        html += '<span class="lg-item"><span class="lg-dot" style="--c:' + people[n].color + '">' + esc(initials(n).toUpperCase()) + '</span>' +
                                esc(n) + (people[n].piquete ? ' <i class="fas fa-tools" style="color:' + people[n].color + '"></i>' : '') + '</span>';
                    });
                    html += '<span class="lg-sep"></span>';
                }
                if (hasVacation) html += '<span class="lg-item"><span class="lg-pill" style="--c:#0288d1"></span>Férias</span>';
                if (hasAbsent) html += '<span class="lg-item"><span class="lg-pill" style="--c:#78909c"></span>Ausente / falta</span>';
                if (hasHoliday) html += '<span class="lg-item"><span class="lg-pill" style="--c:#c62828"></span>Feriado</span>';
                if (hasSwap) html += '<span class="lg-item"><span class="lg-swap"></span>Troca / substituição</span>';
                if (hasUncovered) html += '<span class="lg-item"><span class="lg-dot" style="--c:#c62828">!</span>Sem substituto</span>';
                box.innerHTML = html;
            },
            eventDidMount: function (info) {
                if (info.event.display === 'background') return;
                var p = info.event.extendedProps;
                var tip = document.createElement('div');
                tip.className = 'fc-tooltip-custom';
                var html = '<strong>' + esc(info.event.title) + '</strong>';
                if (p.funcao) html += '<span class="muted">' + esc(p.funcao) + '</span><br>';
                if (p.description) html += esc(p.description);
                if (p.prioridade) html += '<br><span class="muted">Prioridade:</span> ' + esc(p.prioridade);
                if (p.recorrencia) html += '<br><span class="muted">Recorrência:</span> ' + esc(p.recorrencia);
                tip.innerHTML = html;
                document.body.appendChild(tip);
                var move = function (ev) { tip.style.left = (ev.pageX + 12) + 'px'; tip.style.top = (ev.pageY + 12) + 'px'; };
                info.el.addEventListener('mouseenter', function (ev) { tip.style.display = 'block'; move(ev); });
                info.el.addEventListener('mousemove', move);
                info.el.addEventListener('mouseleave', function () { tip.style.display = 'none'; });
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

<div class="row mt-4 dashboard-compact">
    <div class="col-lg-7 mb-4">
            <div class="card dashboard-section-card">
                <div class="card-header bg-danger text-white">
                <strong>{{ __('Pedidos Prioritários') }}</strong>
                <span class="dashboard-section-subtitle">{{ __('Pedidos que merecem acompanhamento mais rápido pela prioridade, estado e antiguidade.') }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0 dashboard-priority-table">
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

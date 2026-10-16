{{-- Calendário da equipa (turnos Hotline/Piquete, férias, ausências e feriados), só de consulta.
     Usado na dashboard e em Recursos Humanos → Horário da Equipa. --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<style>
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
    /* Feriados: fundo vermelho claro, linha no topo e número do dia a vermelho */
    #calendar-agendamentos .fc-bg-event.fc-holiday-bg { background: #ffe1e1 !important; opacity: 1 !important; box-shadow: inset 0 3px 0 #d32f2f; }
    #calendar-agendamentos .fc-daygrid-day.is-holiday .fc-daygrid-day-number { color: #b71c1c; font-weight: 800; }
    #calendar-agendamentos .fc-daygrid-day.is-holiday .fc-daygrid-day-frame { position: relative; }
    #calendar-agendamentos .fc-daygrid-day.is-holiday .fc-daygrid-day-frame::before { content: '{!! str_replace("'", "\\'", __('Feriado')) !!}'; display: inline-block !important; white-space: nowrap; clear: none; position: absolute; top: 9px; left: 7px; z-index: 5; font-size: .66rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #fff; background: #d32f2f; border-radius: 999px; padding: 3px 9px; line-height: 1.2; box-shadow: 0 1px 3px rgba(183, 28, 28, .35); }

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
            var events = @json($taskEvents ?? []);
            var T = {{ \Illuminate\Support\Js::from([
                'escala' => __('Escala'), 'ferias' => __('Férias'), 'ausente' => __('Ausente / falta'), 'feriado' => __('Feriado'),
                'troca' => __('Troca / substituição'), 'semSubstituto' => __('Sem substituto'),
                'prioridade' => __('Prioridade:'), 'recorrencia' => __('Recorrência:'),
            ]) }};
            var isPiquete = function (p) { return p.escala ? p.escala === 'piquete' : /piquete/i.test(p.funcao || ''); };
            var isVacation = function (e) { return e.extendedProps.ferias || /^Férias/.test(e.title); };
            var esc = function (t) { var d = document.createElement('div'); d.textContent = t || ''; return d.innerHTML; };
            var initials = function (name) {
                var parts = (name || '').split(' · ')[0].trim().split(/\s+/);
                return ((parts[0] || '')[0] || '') + (parts.length > 1 ? parts[parts.length - 1][0] : '');
            };
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: @json(app()->getLocale()),
                firstDay: 1, // semana de segunda a domingo
                buttonText: { today: @json(__('Hoje')), month: @json(__('Mês')), week: @json(__('Semana')), day: @json(__('Dia')), list: @json(__('Lista')) },
                allDayText: @json(__('Dia todo')),
                moreLinkText: @json(__('mais')),
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
            // Ordem dentro de cada dia: hotliners, piquete, feriado, férias/ausências, tarefas
            eventDataTransform: function (data) {
                var f = data.funcao || (data.extendedProps && data.extendedProps.funcao) || '';
                var esc2 = data.escala || (data.extendedProps && data.extendedProps.escala) || '';
                data.ordem = f ? (esc2 === 'piquete' || (!esc2 && /piquete/i.test(f)) ? 1 : 0) : (data.feriado ? 2 : (data.ausencia ? 3 : 4));
                return data;
            },
            eventOrder: 'ordem,start,-duration,title',
            eventContent: function (arg) {
                var e = arg.event, p = e.extendedProps;
                if (p.funcao) {
                    var piquete = isPiquete(p);
                    var parts = (p.nome || '').split(' · ');
                    var uncovered = !!p.semSubstituto;
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
                    var icon = p.feriado ? 'fa-flag' : (isVacation(e) ? 'fa-umbrella-beach' : 'fa-user-clock');
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
                        if (p.semSubstituto) { hasUncovered = true; }
                        else if (!people[name]) { people[name] = { color: e.backgroundColor, piquete: isPiquete(p) }; }
                        if (p.substituicao) hasSwap = true;
                    } else if (p.feriado) { hasHoliday = true; }
                    else if (p.ausencia) { if (isVacation(e)) hasVacation = true; else hasAbsent = true; }
                });
                var names = Object.keys(people).sort(function (a, b) { return (people[a].piquete - people[b].piquete) || a.localeCompare(b); });
                if (!names.length && !hasHoliday && !hasVacation && !hasAbsent) { box.innerHTML = ''; return; }
                var html = '';
                if (names.length) {
                    html += '<span class="lg-title">' + esc(T.escala) + '</span>';
                    names.forEach(function (n) {
                        html += '<span class="lg-item"><span class="lg-dot" style="--c:' + people[n].color + '">' + esc(initials(n).toUpperCase()) + '</span>' +
                                esc(n) + (people[n].piquete ? ' <i class="fas fa-tools" style="color:' + people[n].color + '"></i>' : '') + '</span>';
                    });
                    html += '<span class="lg-sep"></span>';
                }
                if (hasVacation) html += '<span class="lg-item"><span class="lg-pill" style="--c:#0288d1"></span>' + esc(T.ferias) + '</span>';
                if (hasAbsent) html += '<span class="lg-item"><span class="lg-pill" style="--c:#78909c"></span>' + esc(T.ausente) + '</span>';
                if (hasHoliday) html += '<span class="lg-item"><span class="lg-pill" style="--c:#d32f2f"></span>' + esc(T.feriado) + '</span>';
                if (hasSwap) html += '<span class="lg-item"><span class="lg-swap"></span>' + esc(T.troca) + '</span>';
                if (hasUncovered) html += '<span class="lg-item"><span class="lg-dot" style="--c:#c62828">!</span>' + esc(T.semSubstituto) + '</span>';
                box.innerHTML = html;
            },
            eventDidMount: function (info) {
                if (info.event.display === 'background') {
                    if (info.event.extendedProps.feriadoFundo) { var cell = info.el.closest('.fc-daygrid-day'); if (cell) cell.classList.add('is-holiday'); }
                    return;
                }
                var p = info.event.extendedProps;
                var tip = document.createElement('div');
                tip.className = 'fc-tooltip-custom';
                var html = '<strong>' + esc(info.event.title) + '</strong>';
                if (p.funcao) html += '<span class="muted">' + esc(p.funcao) + '</span><br>';
                if (p.description) html += esc(p.description);
                if (p.prioridade) html += '<br><span class="muted">' + esc(T.prioridade) + '</span> ' + esc(p.prioridade);
                if (p.recorrencia) html += '<br><span class="muted">' + esc(T.recorrencia) + '</span> ' + esc(p.recorrencia);
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

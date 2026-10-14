@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale('pt'))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Escala Hotline') }}</title>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<style>
    #hotline-calendar .fc-daygrid-day.fc-day-today { background: #e8f1ff; }
    #hotline-calendar .fc-day-sat, #hotline-calendar .fc-day-sun { background: #f8f9fc; }
    #hotline-calendar .fc-daygrid-day-frame { cursor: pointer; }
    .fc-duty-event { border: 0 !important; border-radius: 8px !important; margin-bottom: 3px !important; }
    .fc-duty-override { box-shadow: inset 0 0 0 3px #ffc107; }
    .fc-duty { padding: 3px 7px; line-height: 1.2; color: #fff; overflow: hidden; }
    .fc-duty-role { display: block; font-size: .65rem; text-transform: uppercase; letter-spacing: .06em; opacity: .85; font-weight: 700; }
    .fc-duty-name { display: block; font-weight: 700; font-size: .85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fc-duty-hours { display: block; font-size: .74rem; opacity: .9; }
    .fc-duty-note { display: block; font-size: .68rem; background: rgba(255, 255, 255, .22); border-radius: 4px; padding: 0 4px; margin-top: 2px; white-space: normal; }
    .hr-legend { display: flex; flex-wrap: wrap; gap: .4rem 1rem; font-size: .85rem; }
    .hr-legend span { display: inline-flex; align-items: center; gap: .35rem; }
    .hr-legend i { width: 14px; height: 14px; border-radius: 4px; display: inline-block; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-2">{{ __('Escala Hotline') }}</h5>
                @if($hotliners->isEmpty())
                    <p class="mb-0">{{ __('Ainda não há hotliners ativos com grupo de rotação.') }}
                        <a href="{{ route('backoffice.hr.staff.index') }}">{{ __('Definir colaboradores') }}</a></p>
                @else
                    <div class="hr-legend mb-2">
                        @foreach($legend as $item)
                            <span><i style="background: {{ $item['color'] }}"></i>{{ $item['name'] }} <small class="text-muted">({{ __('Grupo') }} {{ $item['group'] }})</small></span>
                        @endforeach
                        <span><i style="background: #fff; box-shadow: inset 0 0 0 3px #ffc107"></i>{{ __('Troca / substituição') }}</span>
                        <span><i style="background: {{ \App\Services\HotlineRotation::UNCOVERED_COLOR }}"></i>{{ __('Sem substituto') }}</span>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="fa fa-exchange-alt"></i> {{ __('Clique num turno para o trocar, ou num dia para o escolher no cartão ao lado.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="card h-100 border-primary" id="swap-card">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-exchange-alt text-primary"></i> {{ __('Registar troca') }}</h5>
                <form method="GET" action="{{ route('backoffice.hr.hotline.override.create') }}">
                    <div class="form-row">
                        <div class="col-6 mb-2">
                            <label class="small mb-1">{{ __('Dia') }}</label>
                            <input type="date" name="data" id="swap-date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="small mb-1">{{ __('Turno de') }}</label>
                            <select name="staff" class="form-control form-control-sm" required>
                                @foreach($hotliners as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-block" @disabled($hotliners->isEmpty())>{{ __('Continuar') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card mb-3">
            <div class="card-body">
                <div id="hotline-calendar"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('Trocas e substituições') }} <small class="text-muted">({{ __('a partir deste mês') }})</small></h5>
                @if($upcomingOverrides->isEmpty())
                    <p class="text-muted mb-0">{{ __('Sem alterações à rotação.') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Dia') }}</th>
                                    <th>{{ __('Turno de') }}</th>
                                    <th>{{ __('Feito por') }}</th>
                                    <th>{{ __('Motivo') }}</th>
                                    <th>{{ __('Observações') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingOverrides as $o)
                                    <tr>
                                        <td class="text-nowrap">{{ ucfirst($o->data->translatedFormat('D d/m/Y')) }}</td>
                                        <td>{{ $o->staff->user->name ?? '—' }}</td>
                                        <td>{!! $o->substitute ? e($o->substitute->name) : '<span class="text-danger">' . e(__('Sem substituto')) . '</span>' !!}</td>
                                        <td>{{ config('hr.override_reasons.' . $o->motivo) }}</td>
                                        <td class="small text-muted">{{ $o->observacoes }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('backoffice.hr.hotline.override.create', ['staff' => $o->hr_staff_id, 'data' => $o->data->toDateString()]) }}"><i class="fa fa-edit" title="{{ __('Editar') }}"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('hotline-calendar');
        var esc = function (t) { var d = document.createElement('div'); d.textContent = t || ''; return d.innerHTML; };

        var calendar = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            initialDate: @json($month->toDateString()),
            locale: 'pt',
            firstDay: 1,
            height: 'auto',
            nextDayThreshold: '09:00',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listMonth' },
            buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', list: 'Lista' },
            slotMinTime: '00:00:00',
            eventSources: [
                { url: @json(route('backoffice.hr.calendar.events')), display: 'block', className: 'fc-duty-event' },
                { url: @json(route('backoffice.hr.holidays.events')) }
            ],
            eventContent: function (arg) {
                var p = arg.event.extendedProps;
                if (!p.funcao) return true; // feriados mantêm o aspeto normal
                if (arg.view.type === 'listMonth') {
                    return { html: '<strong>' + esc(p.funcao) + ' – ' + esc(p.nome) + '</strong>' + (p.substituicao ? ' <small class="text-muted">⇄ ' + esc(p.substituicao) + '</small>' : '') };
                }
                return { html:
                    '<div class="fc-duty">' +
                        '<span class="fc-duty-role">' + esc(p.funcao) + (p.substituicao ? ' ⇄' : '') + '</span>' +
                        '<span class="fc-duty-name">' + esc(p.nome) + '</span>' +
                        '<span class="fc-duty-hours">' + esc(p.horario) + '</span>' +
                        (p.substituicao ? '<span class="fc-duty-note">' + esc(p.substituicao) + '</span>' : '') +
                    '</div>'
                };
            },
            eventDidMount: function (info) {
                if (info.event.display === 'background') return;
                info.el.title = info.event.extendedProps.description || '';
            },
            dateClick: function (info) {
                document.getElementById('swap-date').value = info.dateStr.substring(0, 10);
                var card = document.getElementById('swap-card');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                card.classList.add('shadow');
                setTimeout(function () { card.classList.remove('shadow'); }, 1200);
            }
        });
        calendar.render();
    });
</script>
@endpush

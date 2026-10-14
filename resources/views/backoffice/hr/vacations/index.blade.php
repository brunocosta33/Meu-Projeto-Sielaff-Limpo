@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale('pt'))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Férias e Ausências') }} {{ $year }}</title>
@endsection

@push('styles')
<style>
    .vac-months { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1rem; }
    .vac-month h6 { font-weight: 700; text-transform: capitalize; margin-bottom: .35rem; }
    .vac-month table { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 2px; }
    .vac-month th { font-size: .68rem; text-align: center; color: #6c757d; font-weight: 600; }
    .vac-day { height: 34px; vertical-align: top; text-align: center; font-size: .78rem; border-radius: 5px; background: #f8f9fc; cursor: pointer; position: relative; padding: 1px 0 0; }
    .vac-day:hover { outline: 2px solid #90caf9; }
    .vac-day.is-weekend { background: #eceff4; color: #9aa0a6; }
    .vac-day.is-holiday { background: #fde2e1; color: #c62828; font-weight: 600; }
    .vac-day.is-today { outline: 2px solid #1976d2; font-weight: 700; }
    .vac-day.is-selected { background: #bbdefb !important; }
    .vac-day.is-empty { background: transparent; cursor: default; }
    .vac-day.is-empty:hover { outline: none; }
    .vac-bars { position: absolute; left: 3px; right: 3px; bottom: 3px; display: flex; flex-direction: column; gap: 1px; }
    .vac-bar { height: 4px; border-radius: 2px; background: var(--vac-color); }
    .vac-bar.is-pending { background: repeating-linear-gradient(45deg, var(--vac-color) 0 3px, transparent 3px 5px); }
    .vac-legend { display: flex; flex-wrap: wrap; gap: .3rem .9rem; font-size: .82rem; }
    .vac-legend span { display: inline-flex; align-items: center; gap: .35rem; }
    .vac-legend i { width: 14px; height: 8px; border-radius: 2px; display: inline-block; background: var(--vac-color); }
    .vac-legend i.is-pending { background: repeating-linear-gradient(45deg, #6c757d 0 3px, transparent 3px 5px); }
    .vac-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; background: var(--vac-color); margin-right: .35rem; }
    .vac-form-card { position: sticky; top: 1rem; }
    .vac-day.has-absence { background: var(--abs-bg) !important; box-shadow: inset 0 0 0 2px var(--abs-color); color: #212529; font-weight: 700; }
    .abs-tag { position: absolute; top: 1px; right: 2px; font-size: .58rem; font-weight: 700; line-height: 1.35; color: #fff; background: var(--abs-color); border-radius: 3px; padding: 0 3px; }
    .vac-legend .abs-swatch { position: relative; width: 22px; height: 16px; border-radius: 4px; display: inline-block; background: var(--abs-bg); box-shadow: inset 0 0 0 2px var(--abs-color); }
    .vac-legend .abs-swatch .abs-tag { top: 2px; right: 2px; font-size: .5rem; padding: 0 2px; }
    .no-proof { display: inline-flex; align-items: center; gap: .3rem; font-size: .75rem; font-weight: 600; color: #8a3b00; background: #ffe8d1; border: 1px solid #ffb877; border-radius: 999px; padding: .1rem .55rem; white-space: nowrap; }
    .vac-balance input { width: 70px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center mb-2">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.hr.vacations.index', ['ano' => $year - 1, 'colaborador' => $filterStaff]) }}"><i class="fa fa-chevron-left"></i></a>
                    <h4 class="mb-0 mx-3">{{ __('Férias e Ausências') }} {{ $year }}</h4>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.hr.vacations.index', ['ano' => $year + 1, 'colaborador' => $filterStaff]) }}"><i class="fa fa-chevron-right"></i></a>
                </div>
                <form method="GET" class="form-inline mb-2">
                    <input type="hidden" name="ano" value="{{ $year }}">
                    <label class="mr-2 small">{{ __('Colaborador') }}</label>
                    <select name="colaborador" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">{{ __('Todos') }}</option>
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}" @selected($filterStaff == $s->id)>{{ $s->user->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

@if($staff->isEmpty())
    <div class="alert alert-info">
        {{ __('Ainda não há colaboradores registados.') }} <a href="{{ route('backoffice.hr.staff.index') }}">{{ __('Adicionar colaboradores') }}</a>
    </div>
@endif

<div class="row">
    {{-- Calendário anual --}}
    <div class="col-xl-8 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="vac-legend mb-3">
                    @foreach($staff as $s)
                        @if(!$filterStaff || $filterStaff == $s->id)
                            <span style="--vac-color: {{ $colors[$s->id] }}"><i></i>{{ $s->user->name }}</span>
                        @endif
                    @endforeach
                    <span><i class="is-pending"></i>{{ __('Férias pendentes') }}</span>
                    @foreach(\App\Models\HrAbsence::STATES as $state)
                        <span><b class="abs-swatch" style="--abs-color: {{ $state['color'] }}; --abs-bg: {{ $state['bg'] }}"><span class="abs-tag">F</span></b>{{ __('Falta') }} {{ mb_strtolower($state['label']) }}</span>
                    @endforeach
                    <span><i style="background:#fde2e1"></i>{{ __('Feriado') }}</span>
                    <span><i style="background:#eceff4"></i>{{ __('Fim de semana') }}</span>
                </div>
                <p class="small text-muted"><i class="fa fa-mouse-pointer"></i> {{ __('Clique no dia de início e depois no dia de fim para preencher o formulário.') }}</p>

                <div class="vac-months">
                    @for($m = 1; $m <= 12; $m++)
                        @php($first = \Carbon\Carbon::create($year, $m, 1))
                        <div class="vac-month">
                            <h6>{{ $first->translatedFormat('F') }}</h6>
                            <table>
                                <thead>
                                    <tr>@foreach(['S', 'T', 'Q', 'Q', 'S', 'S', 'D'] as $wd)<th>{{ $wd }}</th>@endforeach</tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @for($pad = 1; $pad < $first->dayOfWeekIso; $pad++)<td class="vac-day is-empty"></td>@endfor
                                        @for($d = 1; $d <= $first->daysInMonth; $d++)
                                            @php($date = $first->copy()->day($d))
                                            @php($key = $date->toDateString())
                                            @php($dayVacations = $days[$key] ?? [])
                                            @php($dayAbsences = $absenceDays[$key] ?? [])
                                            @php($title = collect($dayVacations)->map(fn ($v) => 'Férias: ' . $v->staff->user->name . ($v->estado === 'pendente' ? ' (pendente)' : ''))
                                                ->merge(collect($dayAbsences)->map(fn ($a) => 'Falta: ' . $a->staff->user->name . ' – ' . $a->motivo_label . ' (' . mb_strtolower($a->situacao_label) . ')' . ($a->isPartial() ? ' ' . $a->duracao : '')))
                                                ->prepend($holidays[$key] ?? null)->filter()->implode("\n"))
                                            @php($worstAbsence = collect($dayAbsences)->sortByDesc(fn ($a) => \App\Models\HrAbsence::STATES[$a->situacao]['priority'] ?? 0)->first())
                                            @php($absState = $worstAbsence ? \App\Models\HrAbsence::STATES[$worstAbsence->situacao] : null)
                                            <td class="vac-day {{ $date->isWeekend() ? 'is-weekend' : '' }} {{ isset($holidays[$key]) ? 'is-holiday' : '' }} {{ $date->isToday() ? 'is-today' : '' }} {{ $absState ? 'has-absence' : '' }}"
                                                @if($absState) style="--abs-color: {{ $absState['color'] }}; --abs-bg: {{ $absState['bg'] }}" @endif
                                                data-date="{{ $key }}" title="{{ $title }}">
                                                {{ $d }}
                                                @if($absState)
                                                    <span class="abs-tag">F{{ count($dayAbsences) > 1 ? count($dayAbsences) : '' }}</span>
                                                @endif
                                                @if($dayVacations)
                                                    <div class="vac-bars">
                                                        @foreach(array_slice($dayVacations, 0, 3) as $v)
                                                            <span class="vac-bar {{ $v->estado === 'pendente' ? 'is-pending' : '' }}" style="--vac-color: {{ $colors[$v->hr_staff_id] ?? '#999' }}"></span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            @if($date->dayOfWeekIso === 7 && $d < $first->daysInMonth)</tr><tr>@endif
                                        @endfor
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário de marcação --}}
    <div class="col-xl-4 mb-3">
        <div class="card border-primary vac-form-card">
            <div class="card-body">
                @php($tipo = old('tipo', 'ferias'))
                <div class="btn-group btn-group-toggle d-flex mb-3" id="vac-type">
                    <button type="button" data-type="ferias" class="btn w-100 {{ $tipo === 'ferias' ? 'btn-primary' : 'btn-outline-primary' }}"><i class="fas fa-umbrella-beach"></i> {{ __('Férias') }}</button>
                    <button type="button" data-type="falta" class="btn w-100 {{ $tipo === 'falta' ? 'btn-danger' : 'btn-outline-danger' }}"><i class="fas fa-user-times"></i> {{ __('Falta') }}</button>
                </div>
                <form method="POST" id="vac-form" enctype="multipart/form-data"
                      action="{{ $tipo === 'falta' ? route('backoffice.hr.absences.store') : route('backoffice.hr.vacations.store') }}"
                      data-action-ferias="{{ route('backoffice.hr.vacations.store') }}" data-action-falta="{{ route('backoffice.hr.absences.store') }}">
                    @csrf
                    <input type="hidden" name="tipo" id="vac-tipo" value="{{ $tipo }}">
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Colaborador') }}</label>
                        <select name="hr_staff_id" class="form-control" required>
                            <option value="">{{ __('Selecione...') }}</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" @selected(old('hr_staff_id', $filterStaff) == $s->id)>{{ $s->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="small mb-1">{{ __('De') }}</label>
                            <input type="date" name="data_inicio" id="vac-from" class="form-control" value="{{ old('data_inicio') }}" required>
                        </div>
                        <div class="form-group col-6">
                            <label class="small mb-1">{{ __('Até') }}</label>
                            <input type="date" name="data_fim" id="vac-to" class="form-control" value="{{ old('data_fim') }}" required>
                        </div>
                    </div>
                    <div class="alert alert-light border py-2 mb-3" id="vac-days-box">
                        <strong id="vac-days">0</strong> {{ __('dias úteis') }}
                        <small class="text-muted d-block">{{ __('Não conta fins de semana nem feriados.') }}</small>
                    </div>

                    {{-- Só para faltas --}}
                    <div data-only="falta">
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="abs-partial" @checked(old('hora_inicio'))>
                            <label class="form-check-label" for="abs-partial">{{ __('Só parte do dia (horas)') }}</label>
                        </div>
                        <div class="form-row" id="abs-hours">
                            <div class="form-group col-6">
                                <label class="small mb-1">{{ __('Das') }}</label>
                                <input type="time" name="hora_inicio" class="form-control" value="{{ old('hora_inicio') }}">
                            </div>
                            <div class="form-group col-6">
                                <label class="small mb-1">{{ __('Às') }}</label>
                                <input type="time" name="hora_fim" class="form-control" value="{{ old('hora_fim') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1">{{ __('Motivo') }}</label>
                            <select name="motivo" class="form-control" required>
                                @foreach(config('hr.absence_reasons') as $key => $label)
                                    <option value="{{ $key }}" @selected(old('motivo', 'sem_motivo') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1">{{ __('Situação') }}</label>
                            <select name="situacao" class="form-control" required>
                                @foreach(\App\Models\HrAbsence::STATES as $key => $state)
                                    <option value="{{ $key }}" @selected(old('situacao', 'aguarda') === $key)>{{ $state['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1">{{ __('Comprovativo (opcional)') }}</label>
                            <input type="file" name="documento" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx">
                            <small class="form-text text-muted">{{ __('Pode ser anexado mais tarde. PDF, imagem ou Word, até 10 MB.') }}</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small mb-1">{{ __('Observações') }}</label>
                        <textarea name="observacoes" class="form-control" rows="2">{{ old('observacoes') }}</textarea>
                    </div>

                    {{-- Só para férias --}}
                    @if($canApprove)
                        <div class="form-group form-check" data-only="ferias">
                            <input type="checkbox" class="form-check-input" name="aprovar" value="1" id="vac-approve" @checked(old('aprovar'))>
                            <label class="form-check-label" for="vac-approve">{{ __('Aprovar já') }}</label>
                            <small class="form-text text-muted">{{ __('Se não marcar, fica pendente até ser aprovado.') }}</small>
                        </div>
                    @else
                        <p class="small text-muted" data-only="ferias">
                            <i class="fa fa-info-circle"></i> {{ __('As férias ficam pendentes até serem aprovadas por quem tem a função') }} {{ $approverNames }}.
                        </p>
                    @endif
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Resumo / saldos --}}
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">{{ __('Resumo de férias') }} {{ $year }}</h5>
        <div class="table-responsive">
            <table class="table table-sm vac-balance mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Colaborador') }}</th>
                        <th>{{ __('Dias de direito') }}</th>
                        <th>{{ __('Transitados de') }} {{ $year - 1 }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Gozados / marcados') }}</th>
                        <th>{{ __('Pendentes') }}</th>
                        <th>{{ __('Disponíveis') }}</th>
                        <th>{{ __('Faltas') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary as $row)
                        <tr>
                            <td class="align-middle"><span class="vac-dot" style="--vac-color: {{ $row['color'] }}"></span>{{ $row['staff']->user->name }}
                                @if($row['staff']->funcao_label)<span class="badge text-white ml-1" style="background: {{ $row['staff']->funcao_color }}">{{ $row['staff']->funcao_label }}</span>@endif
                            </td>
                            <td colspan="2" class="align-middle">
                                <form method="POST" action="{{ route('backoffice.hr.vacations.balance', $row['staff']->id) }}" class="form-inline" id="balance-{{ $row['staff']->id }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="ano" value="{{ $year }}">
                                    <input type="number" name="dias_direito" class="form-control form-control-sm mr-4" min="0" max="60" value="{{ $row['direito'] }}" title="{{ __('Dias de direito') }}">
                                    <input type="number" name="dias_transitados" class="form-control form-control-sm" min="0" max="60"
                                           value="{{ $row['transitados_auto'] ? '' : $row['transitados'] }}" placeholder="{{ $row['transitados'] }}"
                                           title="{{ __('Em branco = calculado automaticamente a partir do ano anterior') }}">
                                    @if($row['transitados_auto'])<small class="text-muted ml-1">{{ __('auto') }}</small>@endif
                                </form>
                            </td>
                            <td class="align-middle font-weight-bold">{{ $row['total'] }}</td>
                            <td class="align-middle">{{ $row['aprovados'] }}</td>
                            <td class="align-middle">{{ $row['pendentes'] }}</td>
                            <td class="align-middle">
                                <span class="badge {{ $row['disponiveis'] < 0 ? 'badge-danger' : 'badge-success' }}" style="font-size: .9rem">{{ $row['disponiveis'] }}</span>
                            </td>
                            <td class="align-middle">
                                @php($absenceLabels = ['aguarda' => __('Por justificar'), 'justificada' => __('Justificadas'), 'injustificada' => __('Injustificadas')])
                                @php($hasAbsences = false)
                                @foreach(\App\Models\HrAbsence::STATES as $key => $state)
                                    @php($t = $row['faltas'][$key])
                                    @if($t['dias'] > 0 || $t['horas'] > 0)
                                        @php($hasAbsences = true)
                                        @php($parts = array_filter([
                                            $t['dias'] > 0 ? $t['dias'] . ' d' : null,
                                            $t['horas'] > 0 ? rtrim(rtrim(number_format($t['horas'], 2, ',', ''), '0'), ',') . ' h' : null,
                                        ]))
                                        <span class="badge mb-1" style="{{ \App\Models\HrAbsence::badgeStyle($key) }}">
                                            {{ $absenceLabels[$key] }}: {{ implode(' + ', $parts) }}
                                        </span>
                                    @endif
                                @endforeach
                                @unless($hasAbsences)<span class="text-muted">—</span>@endunless
                            </td>
                            <td class="align-middle text-right">
                                <button type="submit" form="balance-{{ $row['staff']->id }}" class="btn btn-outline-secondary btn-sm" title="{{ __('Gravar dias') }}"><i class="fa fa-save"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <small class="text-muted">
            {{ __('Os dias transitados são calculados automaticamente com o que sobrou do ano anterior (deixe o campo em branco). Escreva um valor para o definir manualmente, por exemplo no primeiro ano.') }}
        </small>
    </div>
</div>

{{-- Pedidos / marcações --}}
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Marcações de férias') }} {{ $year }}</h5>
        @if($vacations->isEmpty())
            <p class="text-muted mb-0">{{ __('Sem férias marcadas.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Colaborador') }}</th>
                            <th>{{ __('Período') }}</th>
                            <th>{{ __('Dias úteis') }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th>{{ __('Marcado por') }}</th>
                            <th>{{ __('Decisão') }}</th>
                            <th>{{ __('Observações') }}</th>
                            <th class="text-right">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vacations as $v)
                            <tr class="{{ $v->estado === 'rejeitado' ? 'text-muted' : '' }}">
                                <td class="text-nowrap"><span class="vac-dot" style="--vac-color: {{ $colors[$v->hr_staff_id] ?? '#999' }}"></span>{{ $v->staff->user->name ?? '—' }}</td>
                                <td class="text-nowrap">{{ $v->data_inicio->format('d/m/Y') }} – {{ $v->data_fim->format('d/m/Y') }}</td>
                                <td>{{ $v->dias_uteis }}</td>
                                <td>
                                    <span class="badge {{ ['pendente' => 'badge-warning', 'aprovado' => 'badge-success', 'rejeitado' => 'badge-secondary'][$v->estado] ?? 'badge-light' }}">
                                        {{ \App\Models\HrVacation::STATES[$v->estado] ?? $v->estado }}
                                    </span>
                                </td>
                                <td class="small">{{ $v->creator->name ?? '—' }}<br><span class="text-muted">{{ $v->created_at->format('d/m/Y') }}</span></td>
                                <td class="small">
                                    @if($v->decided_at)
                                        {{ $v->decider->name ?? '—' }}<br><span class="text-muted">{{ $v->decided_at->format('d/m/Y H:i') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small">
                                    {{ $v->observacoes }}
                                    @if($v->motivo_rejeicao)<div class="text-danger">{{ __('Motivo da rejeição') }}: {{ $v->motivo_rejeicao }}</div>@endif
                                </td>
                                <td class="text-right text-nowrap">
                                    @if($canApprove && $v->estado !== 'aprovado')
                                        <form method="POST" action="{{ route('backoffice.hr.vacations.approve', $v->id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="{{ __('Aprovar') }}"><i class="fa fa-check"></i></button>
                                        </form>
                                    @endif
                                    @if($canApprove && $v->estado !== 'rejeitado')
                                        <form method="POST" action="{{ route('backoffice.hr.vacations.reject', $v->id) }}" class="d-inline"
                                              onsubmit="var m = prompt('Motivo da rejeição (opcional):'); if (m === null) return false; this.motivo_rejeicao.value = m; return true;">
                                            @csrf
                                            <input type="hidden" name="motivo_rejeicao">
                                            <button class="btn btn-sm btn-outline-warning" title="{{ __('Rejeitar') }}"><i class="fa fa-times"></i></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('backoffice.hr.vacations.destroy', $v->id) }}" class="d-inline"
                                          onsubmit="return confirm('Apagar esta marcação de férias?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Apagar') }}"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Faltas --}}
<div class="card mt-3">
    <div class="card-body">
        <h5 class="card-title">{{ __('Faltas') }} {{ $year }}</h5>
        @if($absences->isEmpty())
            <p class="text-muted mb-0">{{ __('Sem faltas registadas.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Colaborador') }}</th>
                            <th>{{ __('Quando') }}</th>
                            <th>{{ __('Duração') }}</th>
                            <th>{{ __('Motivo') }}</th>
                            <th>{{ __('Situação') }}</th>
                            <th>{{ __('Comprovativo') }}</th>
                            <th>{{ __('Observações') }}</th>
                            <th class="text-right">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absences as $a)
                            <tr>
                                <td class="text-nowrap"><span class="vac-dot" style="--vac-color: {{ $colors[$a->hr_staff_id] ?? '#999' }}"></span>{{ $a->staff->user->name ?? '—' }}</td>
                                <td class="text-nowrap">
                                    {{ $a->data_inicio->format('d/m/Y') }}@if(!$a->data_inicio->isSameDay($a->data_fim)) – {{ $a->data_fim->format('d/m/Y') }}@endif
                                </td>
                                <td class="text-nowrap">{{ $a->duracao }}</td>
                                <td>{{ $a->motivo_label }}</td>
                                <td class="small">
                                    <span class="badge" style="{{ \App\Models\HrAbsence::badgeStyle($a->situacao) }}">{{ $a->situacao_label }}</span>
                                    @if($a->decided_at)<div class="text-muted">{{ $a->decider->name ?? '' }}, {{ $a->decided_at->format('d/m/Y') }}</div>@endif
                                </td>
                                <td class="small">
                                    @if($a->documento_path)
                                        <a href="{{ route('backoffice.hr.absences.document', $a->id) }}" target="_blank"><i class="fa fa-paperclip"></i> {{ \Illuminate\Support\Str::limit($a->documento_nome, 22) }}</a>
                                    @elseif($a->situacao === 'justificada')
                                        <span class="no-proof" title="{{ __('Justificada sem documento anexado. Pode anexar em Editar.') }}"><i class="fa fa-exclamation-triangle"></i> {{ __('Sem comprovativo') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $a->observacoes }}</td>
                                <td class="text-right text-nowrap">
                                    @if($a->situacao !== 'justificada' && $a->motivo !== 'sem_motivo')
                                        <form method="POST" action="{{ route('backoffice.hr.absences.decide', $a->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="situacao" value="justificada">
                                            <button class="btn btn-sm btn-outline-success" title="{{ __('Marcar como justificada') }}"><i class="fa fa-check"></i></button>
                                        </form>
                                    @endif
                                    @if($a->situacao !== 'injustificada')
                                        <form method="POST" action="{{ route('backoffice.hr.absences.decide', $a->id) }}" class="d-inline"
                                              onsubmit="return confirm('Marcar esta falta como injustificada?')">
                                            @csrf
                                            <input type="hidden" name="situacao" value="injustificada">
                                            <button class="btn btn-sm btn-outline-danger" title="{{ __('Marcar como injustificada') }}"><i class="fa fa-times"></i></button>
                                        </form>
                                    @endif
                                    <a href="{{ route('backoffice.hr.absences.edit', $a->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Editar / anexar comprovativo') }}"><i class="fa fa-edit"></i></a>
                                    <form method="POST" action="{{ route('backoffice.hr.absences.destroy', $a->id) }}" class="d-inline"
                                          onsubmit="return confirm('Apagar esta falta?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Apagar') }}"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var holidays = @json($holidaysJs);
        var from = document.getElementById('vac-from');
        var to = document.getElementById('vac-to');
        var daysEl = document.getElementById('vac-days');
        var cells = document.querySelectorAll('.vac-day[data-date]');
        var picking = 'from';

        function parse(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
        function iso(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }

        function refresh() {
            var count = 0;
            if (from.value && to.value && to.value >= from.value) {
                for (var d = parse(from.value); iso(d) <= to.value; d.setDate(d.getDate() + 1)) {
                    var wd = d.getDay();
                    if (wd !== 0 && wd !== 6 && !holidays[iso(d)]) count++;
                }
            }
            daysEl.textContent = count;
            cells.forEach(function (c) {
                var dt = c.dataset.date;
                c.classList.toggle('is-selected', !!from.value && dt >= from.value && dt <= (to.value || from.value));
            });
        }

        cells.forEach(function (cell) {
            cell.addEventListener('click', function () {
                var dt = cell.dataset.date;
                if (picking === 'from' || dt < from.value) {
                    from.value = dt; to.value = dt; picking = 'to';
                } else {
                    to.value = dt; picking = 'from';
                }
                refresh();
            });
        });

        // Alternar entre marcar férias e registar falta
        var form = document.getElementById('vac-form');
        var tipoInput = document.getElementById('vac-tipo');
        var partial = document.getElementById('abs-partial');
        var hours = document.getElementById('abs-hours');
        function setType(type) {
            tipoInput.value = type;
            form.action = form.dataset['action' + (type === 'falta' ? 'Falta' : 'Ferias')];
            document.querySelectorAll('#vac-type button').forEach(function (b) {
                var on = b.dataset.type === type;
                var color = b.dataset.type === 'falta' ? 'danger' : 'primary';
                b.className = 'btn w-100 btn-' + (on ? '' : 'outline-') + color;
            });
            document.querySelectorAll('[data-only]').forEach(function (el) {
                var show = el.dataset.only === type;
                el.style.display = show ? '' : 'none';
                el.querySelectorAll('input, select, textarea').forEach(function (i) { i.disabled = !show; });
            });
            togglePartial();
        }
        function togglePartial() {
            var on = tipoInput.value === 'falta' && partial.checked;
            hours.style.display = on ? '' : 'none';
            hours.querySelectorAll('input').forEach(function (i) { i.disabled = !on; i.required = on; });
            if (on && from.value) { to.value = from.value; refresh(); }
        }
        document.querySelectorAll('#vac-type button').forEach(function (b) {
            b.addEventListener('click', function () { setType(b.dataset.type); });
        });
        partial.addEventListener('change', togglePartial);
        setType(tipoInput.value);

        from.addEventListener('change', function () { if (!to.value || to.value < from.value) to.value = from.value; refresh(); });
        to.addEventListener('change', refresh);
        refresh();
    });
</script>
@endpush

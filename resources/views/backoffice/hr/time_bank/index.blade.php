@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($fmtH = fn ($h, $s = false) => \App\Models\HrTimeEntry::formatHours($h, $s))
@php($filters = request()->only(['colaborador', 'tipo', 'estado']))
@php($selfTypes = array_filter($types, fn ($t) => $t['self']))
@php($workDay = (float) config('hr.work_day_hours', 8))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Horas e Piquetes') }}</title>
@endsection

@push('styles')
<style>
    .tb-hero { background: linear-gradient(135deg, #0f4c81 0%, #1f76c2 100%); border-radius: 18px; padding: 18px 22px; color: #fff; margin-bottom: 1.2rem; box-shadow: 0 12px 28px rgba(15, 76, 129, .25); }
    .tb-hero h4 { margin: 0; font-weight: 800; }
    .tb-hero .month { display: flex; align-items: center; gap: .6rem; }
    .tb-hero .month a { color: #fff; width: 34px; height: 34px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, .4); display: inline-flex; align-items: center; justify-content: center; }
    .tb-hero .month span { font-weight: 700; font-size: 1.05rem; min-width: 150px; text-align: center; text-transform: capitalize; }
    .tb-hero .btn-light, .tb-hero .btn-ghost { border-radius: 999px; font-weight: 700; }
    .tb-hero .btn-ghost { color: #fff; border: 1px solid rgba(255, 255, 255, .45); background: rgba(255, 255, 255, .1); }
    .tb-hero .btn-ghost:hover { background: rgba(255, 255, 255, .2); color: #fff; }

    .tb-card { background: #fff; border-radius: 16px; box-shadow: 0 6px 18px rgba(15, 23, 42, .07); padding: 18px; margin-bottom: 1.2rem; }
    .tb-card h5 { font-weight: 800; color: #132238; font-size: 1rem; margin-bottom: .9rem; }

    .tb-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: .8rem; }
    .tb-stat { border-radius: 14px; padding: 14px 16px; background: var(--bg); color: var(--fg); }
    .tb-stat .num { font-size: 1.9rem; font-weight: 800; line-height: 1.1; }
    .tb-stat .lbl { font-size: .8rem; font-weight: 700; opacity: .9; }
    .tb-stat .hint { font-size: .74rem; opacity: .8; margin-top: .2rem; }

    .tb-table td, .tb-table th { vertical-align: middle; }
    .tb-table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #6c7a89; border-top: 0; }
    .tb-amount { font-weight: 800; white-space: nowrap; }
    .tb-amount.pos { color: #2e7d32; }
    .tb-amount.neg { color: #c62828; }
    .tb-amount.zero { color: #8a96a8; }
    .tb-amount.pay { color: #0d47a1; }
    .tb-type { display: inline-flex; align-items: center; gap: .35rem; font-size: .76rem; font-weight: 700; padding: .2rem .6rem; border-radius: 999px;
               background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); white-space: nowrap; }
    .tb-balance { display: inline-block; font-weight: 800; padding: .25rem .65rem; border-radius: 999px; font-size: .9rem; white-space: nowrap; }
    .tb-balance.pos { background: #dff0e0; color: #1b5e20; }
    .tb-balance.neg { background: #fde2e1; color: #a31515; }
    .tb-balance.zero { background: #eef2f7; color: #6c7a89; }
    .tb-note { font-size: .72rem; color: #8a96a8; }
    .tb-sup .form-control { border-radius: 8px; }
    .tb-proposal { margin-top: .25rem; font-size: .74rem; font-weight: 700; color: #7a5600; background: #fff4cc; border-radius: 999px; padding: .1rem .5rem; display: inline-block; white-space: nowrap; }
    .tb-files { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .35rem; }
    .tb-file { display: inline-flex; align-items: center; gap: .25rem; background: #e3f0ff; border-radius: 999px; padding: .1rem .5rem; font-size: .72rem; }
    .tb-file a { color: #0d47a1; text-decoration: none; font-weight: 600; }
    .tb-file-x { color: #8a96a8; font-size: .9rem; line-height: 1; }
    .tb-attach { margin-top: .3rem; }
    .tb-attach label { cursor: pointer; font-size: .72rem; color: #546e7a; font-weight: 600; }
    .tb-attach label:hover { color: #1f6fd1; }
</style>
@endpush

@section('content')
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

{{-- Cabeçalho --}}
<div class="tb-hero">
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: .8rem 1.2rem">
        <div>
            <h4><i class="fas fa-business-time"></i> {{ __('Horas e Piquetes') }}</h4>
            <div style="opacity: .8; font-size: .88rem">{{ __('Piquetes e trabalho suplementar, horas extra, horas em falta e acertos') }}</div>
        </div>
        <div class="month">
            <a href="{{ route('backoffice.hr.timebank.index', ['mes' => $month->copy()->subMonth()->format('Y-m')] + $filters) }}"><i class="fa fa-chevron-left"></i></a>
            <span>{{ $month->translatedFormat('F Y') }}</span>
            <a href="{{ route('backoffice.hr.timebank.index', ['mes' => $month->copy()->addMonth()->format('Y-m')] + $filters) }}"><i class="fa fa-chevron-right"></i></a>
        </div>
        @if($isManager)
            <div class="d-flex flex-wrap" style="gap: .5rem">
                <a href="{{ route('backoffice.hr.timebank.export', ['mes' => $month->format('Y-m')]) }}" class="btn btn-light btn-sm"><i class="fas fa-file-excel"></i> {{ __('Exportar Excel') }}</a>
            </div>
        @endif
    </div>
</div>

@if($isManager && $pendingTotal)
    <div class="alert alert-warning d-flex align-items-center justify-content-between">
        <span><i class="fa fa-hourglass-half"></i> <strong>{{ $pendingTotal }}</strong> {{ $pendingTotal === 1 ? __('pedido de horas pendente de aprovação') : __('pedidos de horas pendentes de aprovação') }}</span>
        <a href="{{ route('backoffice.hr.timebank.index', ['mes' => $month->format('Y-m'), 'estado' => 'pendente']) }}" class="btn btn-sm btn-warning">{{ __('Ver pendentes') }}</a>
    </div>
@endif

<div class="row">
    {{-- Saldos --}}
    <div class="col-xl-8">
        <div class="tb-card">
            @if(!$isManager)
                @php($me = $summary->first())
                <h5><i class="fas fa-wallet text-primary"></i> {{ __('O meu saldo') }}</h5>
                @if($me)
                    <div class="tb-stats">
                        <div class="tb-stat" style="--bg: {{ $me->horas < 0 ? '#fde2e1' : '#dff0e0' }}; --fg: {{ $me->horas < 0 ? '#a31515' : '#1b5e20' }}">
                            <div class="lbl">{{ __('Saldo de horas') }}</div>
                            <div class="num">{{ $fmtH($me->horas, true) }}</div>
                            <div class="hint">{{ $me->horas < 0 ? __('horas que deve à empresa') : __('horas por pagar ou gozar') }}</div>
                        </div>
                        <div class="tb-stat" style="--bg: #e3f0ff; --fg: #0d47a1">
                            <div class="lbl">{{ __('Piquetes confirmados em') }} {{ $month->translatedFormat('F') }}</div>
                            <div class="num">{{ $me->piquetes }}</div>
                            <div class="hint">
                                @if($me->aConfirmar){{ $me->aConfirmar }} {{ __('a confirmar pela gestão') }} · @endif
                                {{ __('trabalho suplementar:') }} {{ $fmtH($me->suplementar) }} · {{ __('pago à parte') }}
                            </div>
                        </div>
                        <div class="tb-stat" style="--bg: #fff8db; --fg: #7a5600">
                            <div class="lbl">{{ __('Pedidos pendentes') }}</div>
                            <div class="num">{{ $me->pendentes }}</div>
                            <div class="hint">{{ __('a aguardar aprovação') }}</div>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">{{ __('Não está registado como colaborador.') }}</p>
                @endif
            @else
                <h5><i class="fas fa-users text-primary"></i> {{ __('Saldos por colaborador') }} <small class="text-muted font-weight-normal">— {{ __('acumulado até hoje') }}</small></h5>
                <div class="table-responsive">
                    <table class="table table-sm tb-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Colaborador') }}</th>
                                <th>{{ __('Saldo de horas') }}</th>
                                <th>{{ __('Piquetes confirmados em') }} {{ $month->translatedFormat('F') }}</th>
                                <th>{{ __('Pendentes') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary as $row)
                                <tr>
                                    <td>
                                        <strong>{{ $row->staff->user->name }}</strong>
                                        @if($row->staff->funcao_label)<span class="badge text-white ml-1" style="background: {{ $row->staff->funcao_color }}">{{ __($row->staff->funcao_label) }}</span>@endif
                                    </td>
                                    <td>
                                        <span class="tb-balance {{ $row->horas > 0 ? 'pos' : ($row->horas < 0 ? 'neg' : 'zero') }}">{{ $fmtH($row->horas, true) }}</span>
                                        @if($row->horas < 0)<div class="tb-note">{{ __('deve horas à empresa') }}</div>@elseif($row->horas > 0)<div class="tb-note">{{ __('por pagar ou gozar') }}</div>@endif
                                    </td>
                                    <td>
                                        @if($row->piquetes)
                                            <strong>{{ $row->piquetes }}</strong>
                                            @if($row->suplementar > 0)<span class="tb-note">· {{ $fmtH($row->suplementar) }} {{ __('suplementar') }}</span>@endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                        @if($row->aConfirmar)<div class="tb-note" style="color: #b26a00">{{ $row->aConfirmar }} {{ __('a confirmar') }}</div>@endif
                                    </td>
                                    <td>@if($row->pendentes)<span class="badge badge-warning">{{ $row->pendentes }}</span>@else<span class="text-muted">—</span>@endif</td>
                                    <td class="text-right">
                                        <a href="{{ route('backoffice.hr.timebank.index', ['mes' => $month->format('Y-m'), 'colaborador' => $row->staff->id]) }}" class="btn btn-sm btn-link" title="{{ __('Ver movimentos') }}"><i class="fa fa-list"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Registar --}}
    <div class="col-xl-4">
        <div class="tb-card" style="border-top: 5px solid #1f6fd1">
            <h5><i class="fas fa-plus-circle text-primary"></i> {{ $isManager ? __('Registar movimento') : __('Registar horas') }}</h5>
            @if($isManager || $ownStaff)
            <form method="POST" action="{{ route('backoffice.hr.timebank.store') }}" id="tb-form" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="small mb-1">{{ __('Tipo') }}</label>
                    <select name="tipo" id="tb-tipo" class="form-control" required>
                        @if($isManager)
                            <optgroup label="{{ __('Piquete') }}">
                                <option value="piquete" @selected(old('tipo') === 'piquete')>{{ __($types['piquete']['label']) }}</option>
                                <option value="piquete_feito" @selected(old('tipo') === 'piquete_feito')>{{ __('Piquete realizado (acrescentar à mão)') }}</option>
                            </optgroup>
                            <optgroup label="{{ __('A favor do colaborador') }}">
                                @foreach(['hora_extra', 'reposicao'] as $k)<option value="{{ $k }}" @selected(old('tipo') === $k)>{{ __($types[$k]['label']) }}</option>@endforeach
                            </optgroup>
                            <optgroup label="{{ __('A favor da empresa') }}">
                                <option value="horas_em_falta" @selected(old('tipo') === 'horas_em_falta')>{{ __($types['horas_em_falta']['label']) }}</option>
                            </optgroup>
                            <optgroup label="{{ __('Acertos do saldo de horas') }}">
                                @foreach(['pagamento_horas', 'folga_compensatoria'] as $k)<option value="{{ $k }}" @selected(old('tipo') === $k)>{{ __($types[$k]['label']) }}</option>@endforeach
                            </optgroup>
                        @else
                            @foreach($selfTypes as $k => $t)<option value="{{ $k }}" @selected(old('tipo') === $k)>{{ __($t['self_label'] ?? $t['label']) }}</option>@endforeach
                        @endif
                    </select>
                    <small class="form-text text-muted" id="tb-help"></small>
                </div>
                @if($isManager)
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Colaborador') }}</label>
                        <select name="hr_staff_id" class="form-control" required>
                            <option value="">{{ __('Selecione...') }}</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" @selected(old('hr_staff_id', $filterStaff) == $s->id)>{{ $s->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="form-row">
                    <div class="form-group col-6">
                        <label class="small mb-1">{{ __('Data') }}</label>
                        <input type="date" name="data" class="form-control" value="{{ old('data', now()->toDateString()) }}" required>
                    </div>
                    <div class="form-group col-6" id="tb-horas-field">
                        <label class="small mb-1" id="tb-horas-label">{{ __('Horas') }}</label>
                        <input type="text" name="horas" id="tb-horas" class="form-control" value="{{ old('horas') }}" placeholder="{{ __('ex.: 2,5 ou 2:30') }}" inputmode="decimal">
                    </div>
                </div>
                {{-- Folga compensatória: escolher o período em vez de horas --}}
                <div class="form-group" id="tb-folga-field" style="display: none">
                    <label class="small mb-1">{{ __('Período da folga') }}</label>
                    <div class="btn-group btn-group-toggle d-flex" id="tb-folga">
                        <button type="button" class="btn btn-outline-primary w-100" data-horas="{{ $workDay }}">{{ __('Dia inteiro') }}<small class="d-block">{{ $workDay }}h</small></button>
                        <button type="button" class="btn btn-outline-primary w-100" data-horas="{{ $workDay / 2 }}">{{ __('Meio dia') }}<small class="d-block">{{ str_replace('.', ',', $workDay / 2) }}h</small></button>
                        <button type="button" class="btn btn-outline-primary w-100" data-horas="">{{ __('Outro') }}<small class="d-block">{{ __('em horas') }}</small></button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Descrição') }}</label>
                    <input type="text" name="descricao" class="form-control" value="{{ old('descricao') }}" placeholder="{{ __('ex.: loja 472, avaria do sorter') }}">
                </div>
                <div class="form-group">
                    <label class="small mb-1"><i class="fa fa-camera"></i> {{ __('Comprovativos (opcional)') }}</label>
                    <input type="file" name="anexos[]" class="form-control-file" accept="image/*,application/pdf" multiple>
                    <small class="form-text text-muted">{{ __('Fotografias ou PDF (ex.: folha de serviço, máquina reparada). No telemóvel pode tirar a fotografia na hora.') }}</small>
                </div>
                <button type="submit" class="btn btn-primary btn-block" style="border-radius: 10px; font-weight: 700"><i class="fa fa-save"></i> {{ $isManager ? __('Registar') : __('Enviar para aprovação') }}</button>
                @unless($isManager)<small class="form-text text-muted text-center">{{ __('Fica pendente até ser aprovado pela gestão de RH.') }}</small>@endunless
            </form>
            @endif
        </div>
    </div>
</div>

{{-- Piquetes da escala a confirmar (gestão) --}}
@if($isManager)
<div class="tb-card">
    @php($pendingSlots = collect($slots)->where('decision', null)->count())
    <h5 class="mb-1"><i class="fas fa-shield-alt text-primary"></i> {{ __('Piquetes de') }} {{ $month->translatedFormat('F') }}
        @if($pendingSlots)<span class="badge badge-warning ml-1">{{ $pendingSlots }} {{ __('a confirmar') }}</span>@endif
    </h5>
    <p class="small text-muted">{{ __('Sábados já passados da escala de piquete. Só contam (e aparecem no Excel) os que forem confirmados.') }}</p>
    @if(empty($slots))
        <p class="text-muted mb-0">{{ __('Ainda não há sábados de piquete passados neste mês.') }}</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm tb-table mb-0">
                <thead>
                    <tr><th>{{ __('Sábado') }}</th><th>{{ __('Zona') }}</th><th>{{ __('Previsto na escala') }}</th><th>{{ __('Decisão') }}</th></tr>
                </thead>
                <tbody>
                    @foreach($slots as $slot)
                        <tr>
                            <td class="text-nowrap">{{ ucfirst($slot->date->translatedFormat('D d/m')) }}</td>
                            <td>{{ $slot->zona ?? '—' }}</td>
                            <td>
                                <strong>{{ $slot->worker_name }}</strong>
                                @if($slot->note)<div class="tb-note">{{ $slot->note }}</div>@endif
                            </td>
                            <td>
                                @if($slot->decision && $slot->decision->estado === 'aprovado')
                                    <span class="badge badge-success"><i class="fa fa-check"></i> {{ __('Confirmado') }} · {{ $slot->decision->staff->user->name ?? '' }}</span>
                                @elseif($slot->decision)
                                    <span class="badge badge-secondary"><i class="fa fa-times"></i> {{ __('Não fez') }}</span>
                                @endif
                                @if($slot->decision)
                                    <form method="POST" action="{{ route('backoffice.hr.timebank.destroy', $slot->decision->id) }}" class="d-inline" onsubmit="return confirm({{ json_encode(__('Desfazer esta decisão? O piquete volta a ficar por confirmar.')) }})">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-link px-1" title="{{ __('Desfazer') }}"><i class="fa fa-undo"></i></button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('backoffice.hr.timebank.piquetes.confirm') }}" class="d-inline-flex align-items-center" style="gap: .3rem">
                                        @csrf
                                        <input type="hidden" name="data" value="{{ $slot->date->toDateString() }}">
                                        <input type="hidden" name="slot_staff_id" value="{{ $slot->slot_staff->id }}">
                                        <select name="hr_staff_id" class="form-control form-control-sm" style="width: auto" title="{{ __('Quem fez o piquete') }}">
                                            <option value="">{{ __('Quem fez?') }}</option>
                                            @foreach($staff as $st)
                                                <option value="{{ $st->id }}" @selected($slot->worker_staff_id === $st->id)>{{ $st->user->name }}</option>
                                            @endforeach
                                        </select>
                                        <button name="acao" value="confirmar" class="btn btn-sm btn-success" title="{{ __('Confirmar') }}"><i class="fa fa-check"></i> {{ __('Confirmar') }}</button>
                                        <button name="acao" value="recusar" class="btn btn-sm btn-outline-secondary" title="{{ __('Não fez') }}" onclick="return confirm({{ json_encode(__('Marcar este piquete como não feito?')) }})"><i class="fa fa-times"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif

{{-- Movimentos do mês --}}
<div class="tb-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap: .5rem">
        <h5 class="mb-0"><i class="fas fa-stream text-primary"></i> {{ __('Movimentos de') }} {{ $month->translatedFormat('F Y') }}</h5>
        <form method="GET" class="form-inline" style="gap: .4rem">
            <input type="hidden" name="mes" value="{{ $month->format('Y-m') }}">
            @if($isManager)
                <select name="colaborador" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Todos os colaboradores') }}</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}" @selected($filterStaff == $s->id)>{{ $s->user->name }}</option>@endforeach
                </select>
            @endif
            <select name="tipo" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">{{ __('Todos os tipos') }}</option>
                @foreach($types as $k => $t)<option value="{{ $k }}" @selected(request('tipo') === $k)>{{ __($t['label']) }}</option>@endforeach
            </select>
            <select name="estado" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">{{ __('Todos os estados') }}</option>
                @foreach(\App\Models\HrTimeEntry::STATES as $k => $st)<option value="{{ $k }}" @selected(request('estado') === $k)>{{ __($st['label']) }}</option>@endforeach
            </select>
        </form>
    </div>

    @if($entries->isEmpty())
        <p class="text-muted mb-0 py-3 text-center">{{ __('Sem movimentos neste mês.') }}</p>
    @else
        <div class="table-responsive">
            <table class="table tb-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Data') }}</th>
                        @if($isManager)<th>{{ __('Colaborador') }}</th>@endif
                        <th>{{ __('Tipo') }}</th>
                        <th>{{ __('Quantidade') }}</th>
                        <th>{{ __('Descrição') }}</th>
                        <th>{{ __('Estado') }}</th>
                        @if($isManager)<th class="text-right">{{ __('Ações') }}</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $e)
                        @php($amount = $e->signed_amount)
                        <tr class="{{ $e->estado === 'rejeitado' ? 'text-muted' : '' }}">
                            <td class="text-nowrap">{{ ucfirst($e->data->translatedFormat('D d/m')) }}</td>
                            @if($isManager)<td class="text-nowrap">{{ $e->staff->user->name ?? '—' }}</td>@endif
                            <td><span class="tb-type" style="--c: {{ $e->type['color'] }}"><i class="fas {{ $e->type['icon'] }}"></i> {{ $e->type['label'] }}</span></td>
                            <td>
                                @if($e->tipo === 'piquete_feito')
                                    <span class="tb-amount {{ $e->estado === 'aprovado' ? 'pay' : 'zero' }}">{{ $e->estado === 'aprovado' ? __('1 piquete') : __('não fez') }}</span>
                                    <div class="tb-note">{{ __('pago à parte') }}</div>
                                @elseif($e->tipo === 'piquete' && $isManager)
                                    <form method="POST" action="{{ route('backoffice.hr.timebank.update', $e->id) }}" class="tb-sup">
                                        @csrf
                                        @method('PUT')
                                        <label class="tb-note mb-0">{{ __('Trabalho suplementar · pago à parte') }}</label>
                                        <div class="d-flex" style="gap: .3rem">
                                            <input type="text" name="horas" class="form-control form-control-sm" value="{{ $e->horas > 0 ? str_replace('h', ':', $fmtH($e->horas)) : '' }}" placeholder="0:00" style="width: 78px" inputmode="decimal">
                                            <button class="btn btn-sm btn-outline-primary" title="{{ __('Gravar horas') }}"><i class="fa fa-save"></i></button>
                                        </div>
                                    </form>
                                @elseif($e->tipo === 'piquete')
                                    <span class="tb-amount {{ $e->horas > 0 ? 'pay' : 'zero' }}">{{ $e->horas > 0 ? $fmtH($e->horas) : '—' }}</span>
                                    <div class="tb-note">{{ __('trabalho suplementar · pago à parte') }}</div>
                                @else
                                    <span class="tb-amount {{ $amount > 0 ? 'pos' : 'neg' }}" @if($e->estado === 'rejeitado') style="text-decoration: line-through; opacity: .6" @endif>
                                        {{ $fmtH($amount, true) }}
                                    </span>
                                @endif
                                @if($e->hasProposal())
                                    <div class="tb-proposal"><i class="fa fa-hourglass-half"></i> {{ __('Proposto:') }} {{ $fmtH($e->horas_propostas) }}</div>
                                @endif
                            </td>
                            <td class="small">
                                {{ $e->descricao }}<div class="tb-note">{{ __('registado por') }} {{ $e->creator->name ?? '—' }}</div>
                                @if($e->files->isNotEmpty())
                                    <div class="tb-files">
                                        @foreach($e->files as $f)
                                            <span class="tb-file">
                                                <a href="{{ route('backoffice.hr.timebank.files.show', $f->id) }}" target="_blank" title="{{ $f->nome }}">
                                                    <i class="fas {{ $f->isImage() ? 'fa-image' : 'fa-file-pdf' }}"></i> {{ \Illuminate\Support\Str::limit($f->nome, 18) }}
                                                </a>
                                                @if($isManager || $f->uploaded_by === auth()->id())
                                                    <form method="POST" action="{{ route('backoffice.hr.timebank.files.delete', $f->id) }}" class="d-inline" onsubmit="return confirm({{ json_encode(__('Remover este comprovativo?')) }})">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-link p-0 tb-file-x" title="{{ __('Remover') }}">&times;</button>
                                                    </form>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('backoffice.hr.timebank.files.store', $e->id) }}" enctype="multipart/form-data" class="tb-attach">
                                    @csrf
                                    <label class="mb-0" title="{{ __('Anexar fotografia ou PDF') }}">
                                        <i class="fa fa-paperclip"></i> {{ __('Anexar') }}
                                        <input type="file" name="anexos[]" accept="image/*,application/pdf" multiple hidden onchange="this.form.submit()">
                                    </label>
                                </form>
                            </td>
                            <td>
                                @if($e->hasProposal() && $e->estado === 'aprovado')
                                    <span class="badge badge-warning">{{ __('Proposta pendente') }}</span>
                                @else
                                    <span class="badge {{ \App\Models\HrTimeEntry::STATES[$e->estado]['class'] ?? 'badge-light' }}">{{ __(\App\Models\HrTimeEntry::STATES[$e->estado]['label'] ?? $e->estado) }}</span>
                                @endif
                                @if($e->decided_at && $e->decided_by !== $e->created_by)<div class="tb-note">{{ __('por') }} {{ $e->decider->name ?? '' }}</div>@endif
                            </td>
                            @if($isManager)
                                <td class="text-right text-nowrap">
                                    @if($e->estado === 'pendente' || $e->hasProposal())
                                        <form method="POST" action="{{ route('backoffice.hr.timebank.decide', $e->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="estado" value="aprovado">
                                            <button class="btn btn-sm btn-success" title="{{ __('Aprovar') }}"><i class="fa fa-check"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('backoffice.hr.timebank.decide', $e->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="estado" value="rejeitado">
                                            <button class="btn btn-sm btn-outline-warning" title="{{ __('Rejeitar') }}"><i class="fa fa-times"></i></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('backoffice.hr.timebank.destroy', $e->id) }}" class="d-inline" onsubmit="return confirm({{ json_encode(__('Apagar este movimento?')) }})">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger" title="{{ __('Apagar') }}"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var types = @json($types);
        var help = {
            piquete: {{ \Illuminate\Support\Js::from($isManager
                ? __('Horas trabalhadas em clientes durante um sábado de piquete. São pagas em dinheiro e não contam para o saldo de horas. (Os piquetes feitos contam-se sozinhos a partir da escala.)')
                : __('Indique o sábado de piquete e as horas que trabalhou em clientes (pagas em dinheiro, não contam para o saldo). Junte fotografias para validar.')) }},
            hora_extra: @json(__('Horas trabalhadas além do horário. Ficam no saldo para pagar ou gozar.')),
            reposicao: @json(__('Horas que o colaborador repôs (reduz o que deve à empresa).')),
            horas_em_falta: @json(__('O colaborador trabalhou menos e fica a dever estas horas.')),
            pagamento_horas: @json(__('Horas pagas: saem do saldo do colaborador.')),
            piquete_feito: @json(__('Piquete que não está na escala (ex.: troca não registada). Os da escala confirmam-se no cartão "Piquetes a confirmar".')),
            folga_compensatoria: @json(__('Dia ou meio dia de descanso para compensar horas extra: sai do saldo do colaborador.'))
        };
        var tipo = document.getElementById('tb-tipo');
        if (!tipo) return;
        var horasLabel = document.getElementById('tb-horas-label');
        var helpEl = document.getElementById('tb-help');
        var horasField = document.getElementById('tb-horas-field');
        var horasInput = document.getElementById('tb-horas');
        var folgaField = document.getElementById('tb-folga-field');
        var folgaButtons = document.querySelectorAll('#tb-folga button');
        var pickFolga = function (btn) {
            folgaButtons.forEach(function (b) { b.classList.toggle('active', b === btn); b.classList.toggle('btn-primary', b === btn); b.classList.toggle('btn-outline-primary', b !== btn); });
            var other = btn.dataset.horas === '';
            horasField.style.visibility = other ? 'visible' : 'hidden';
            if (!other) horasInput.value = String(btn.dataset.horas).replace('.', ',');
            else { horasInput.value = ''; horasInput.focus(); }
        };
        folgaButtons.forEach(function (b) { b.addEventListener('click', function () { pickFolga(b); }); });
        var sync = function () {
            var folga = tipo.value === 'folga_compensatoria';
            var noHours = tipo.value === 'piquete_feito';
            horasLabel.textContent = tipo.value === 'piquete' ? @json(__('Trabalho suplementar (h)')) : @json(__('Horas'));
            helpEl.textContent = help[tipo.value] || '';
            folgaField.style.display = folga ? '' : 'none';
            horasField.style.visibility = noHours ? 'hidden' : 'visible';
            if (noHours) horasInput.value = '';
            if (folga) pickFolga(folgaButtons[0]); // por defeito: dia inteiro
        };
        tipo.addEventListener('change', sync);
        sync();
    });
</script>
@endpush

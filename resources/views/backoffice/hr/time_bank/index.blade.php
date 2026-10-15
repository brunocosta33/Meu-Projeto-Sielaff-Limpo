@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale('pt'))
@php($fmtH = fn ($h, $s = false) => \App\Models\HrTimeEntry::formatHours($h, $s))
@php($fmtE = fn ($v, $s = false) => \App\Models\HrTimeEntry::formatEuros($v, $s))
@php($filters = request()->only(['colaborador', 'tipo', 'estado']))
@php($selfTypes = array_filter($types, fn ($t) => $t['self']))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Banco de Horas') }}</title>
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
    .tb-type { display: inline-flex; align-items: center; gap: .35rem; font-size: .76rem; font-weight: 700; padding: .2rem .6rem; border-radius: 999px;
               background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); white-space: nowrap; }
    .tb-balance { display: inline-block; font-weight: 800; padding: .25rem .65rem; border-radius: 999px; font-size: .9rem; white-space: nowrap; }
    .tb-balance.pos { background: #dff0e0; color: #1b5e20; }
    .tb-balance.neg { background: #fde2e1; color: #a31515; }
    .tb-balance.zero { background: #eef2f7; color: #6c7a89; }
    .tb-note { font-size: .72rem; color: #8a96a8; }
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
            <h4><i class="fas fa-business-time"></i> {{ __('Banco de Horas') }}</h4>
            <div style="opacity: .8; font-size: .88rem">{{ __('Horas extra, intervenções e prevenções de piquete, horas em falta e acertos') }}</div>
        </div>
        <div class="month">
            <a href="{{ route('backoffice.hr.timebank.index', ['mes' => $month->copy()->subMonth()->format('Y-m')] + $filters) }}"><i class="fa fa-chevron-left"></i></a>
            <span>{{ $month->translatedFormat('F Y') }}</span>
            <a href="{{ route('backoffice.hr.timebank.index', ['mes' => $month->copy()->addMonth()->format('Y-m')] + $filters) }}"><i class="fa fa-chevron-right"></i></a>
        </div>
        @if($isManager)
            <div class="d-flex flex-wrap" style="gap: .5rem">
                <form method="POST" action="{{ route('backoffice.hr.timebank.prevencoes') }}" onsubmit="return confirm('Registar o subsídio de prevenção ({{ $fmtE($prevencaoValor) }}) para cada sábado de piquete de {{ $month->translatedFormat('F Y') }}? Os que já existem não são duplicados.')">
                    @csrf
                    <input type="hidden" name="mes" value="{{ $month->format('Y-m') }}">
                    <button class="btn btn-ghost btn-sm"><i class="fas fa-shield-alt"></i> {{ __('Gerar prevenções do mês') }}</button>
                </form>
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
                        <div class="tb-stat" style="--bg: #fff1e0; --fg: #a85400">
                            <div class="lbl">{{ __('Prevenções por pagar') }}</div>
                            <div class="num">{{ $fmtE($me->euros) }}</div>
                            <div class="hint">{{ __('subsídio de prevenção de piquete') }}</div>
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
                                <th>{{ __('Prevenções por pagar') }}</th>
                                <th>{{ __('Pendentes') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary as $row)
                                <tr>
                                    <td>
                                        <strong>{{ $row->staff->user->name }}</strong>
                                        @if($row->staff->funcao_label)<span class="badge text-white ml-1" style="background: {{ $row->staff->funcao_color }}">{{ $row->staff->funcao_label }}</span>@endif
                                    </td>
                                    <td>
                                        <span class="tb-balance {{ $row->horas > 0 ? 'pos' : ($row->horas < 0 ? 'neg' : 'zero') }}">{{ $fmtH($row->horas, true) }}</span>
                                        @if($row->horas < 0)<div class="tb-note">{{ __('deve horas à empresa') }}</div>@elseif($row->horas > 0)<div class="tb-note">{{ __('por pagar ou gozar') }}</div>@endif
                                    </td>
                                    <td><span class="tb-amount {{ $row->euros > 0 ? 'pos' : 'zero' }}">{{ $fmtE($row->euros) }}</span></td>
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
            <h5><i class="fas fa-plus-circle text-primary"></i> {{ $isManager ? __('Registar movimento') : __('Pedir horas') }}</h5>
            @if($isManager || $ownStaff)
            <form method="POST" action="{{ route('backoffice.hr.timebank.store') }}" id="tb-form">
                @csrf
                <div class="form-group">
                    <label class="small mb-1">{{ __('Tipo') }}</label>
                    <select name="tipo" id="tb-tipo" class="form-control" required>
                        @if($isManager)
                            <optgroup label="{{ __('A favor do colaborador') }}">
                                @foreach(['hora_extra', 'intervencao_piquete', 'reposicao'] as $k)<option value="{{ $k }}" @selected(old('tipo') === $k)>{{ $types[$k]['label'] }}</option>@endforeach
                            </optgroup>
                            <optgroup label="{{ __('A favor da empresa') }}">
                                <option value="horas_em_falta" @selected(old('tipo') === 'horas_em_falta')>{{ $types['horas_em_falta']['label'] }}</option>
                            </optgroup>
                            <optgroup label="{{ __('Acertos do saldo de horas') }}">
                                @foreach(['pagamento_horas', 'folga_compensatoria'] as $k)<option value="{{ $k }}" @selected(old('tipo') === $k)>{{ $types[$k]['label'] }}</option>@endforeach
                            </optgroup>
                            <optgroup label="{{ __('Prevenção de piquete (euros)') }}">
                                @foreach(['prevencao_piquete', 'pagamento_prevencao'] as $k)<option value="{{ $k }}" @selected(old('tipo') === $k)>{{ $types[$k]['label'] }}</option>@endforeach
                            </optgroup>
                        @else
                            @foreach($selfTypes as $k => $t)<option value="{{ $k }}" @selected(old('tipo') === $k)>{{ $t['label'] }}</option>@endforeach
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
                        <label class="small mb-1">{{ __('Horas') }}</label>
                        <input type="text" name="horas" class="form-control" value="{{ old('horas') }}" placeholder="ex.: 2,5 ou 2:30" inputmode="decimal">
                    </div>
                    <div class="form-group col-6" id="tb-valor-field" style="display: none">
                        <label class="small mb-1">{{ __('Valor (€)') }}</label>
                        <input type="number" name="valor" class="form-control" step="0.01" min="0" value="{{ old('valor', $prevencaoValor) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Descrição') }}</label>
                    <input type="text" name="descricao" class="form-control" value="{{ old('descricao') }}" placeholder="{{ __('ex.: intervenção na loja 472, avaria do sorter') }}">
                </div>
                <button type="submit" class="btn btn-primary btn-block" style="border-radius: 10px; font-weight: 700"><i class="fa fa-save"></i> {{ $isManager ? __('Registar') : __('Enviar pedido') }}</button>
                @unless($isManager)<small class="form-text text-muted text-center">{{ __('Fica pendente até ser aprovado pela gestão de RH.') }}</small>@endunless
            </form>
            @endif
        </div>
    </div>
</div>

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
                @foreach($types as $k => $t)<option value="{{ $k }}" @selected(request('tipo') === $k)>{{ $t['label'] }}</option>@endforeach
            </select>
            <select name="estado" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">{{ __('Todos os estados') }}</option>
                @foreach(\App\Models\HrTimeEntry::STATES as $k => $st)<option value="{{ $k }}" @selected(request('estado') === $k)>{{ $st['label'] }}</option>@endforeach
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
                                <span class="tb-amount {{ $amount > 0 ? 'pos' : 'neg' }}" @if($e->estado === 'rejeitado') style="text-decoration: line-through; opacity: .6" @endif>
                                    {{ $e->type['unit'] === 'euros' ? $fmtE($amount, true) : $fmtH($amount, true) }}
                                </span>
                            </td>
                            <td class="small">{{ $e->descricao }}<div class="tb-note">{{ __('registado por') }} {{ $e->creator->name ?? '—' }}</div></td>
                            <td>
                                <span class="badge {{ \App\Models\HrTimeEntry::STATES[$e->estado]['class'] ?? 'badge-light' }}">{{ \App\Models\HrTimeEntry::STATES[$e->estado]['label'] ?? $e->estado }}</span>
                                @if($e->decided_at && $e->decided_by !== $e->created_by)<div class="tb-note">{{ __('por') }} {{ $e->decider->name ?? '' }}</div>@endif
                            </td>
                            @if($isManager)
                                <td class="text-right text-nowrap">
                                    @if($e->estado === 'pendente')
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
                                    <form method="POST" action="{{ route('backoffice.hr.timebank.destroy', $e->id) }}" class="d-inline" onsubmit="return confirm('Apagar este movimento?')">
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
            hora_extra: 'Horas trabalhadas além do horário. Ficam no saldo para pagar ou gozar.',
            intervencao_piquete: 'Horas de deslocação a clientes durante o piquete. São pagas à hora.',
            reposicao: 'Horas que o colaborador repôs (reduz o que deve à empresa).',
            horas_em_falta: 'O colaborador trabalhou menos e fica a dever estas horas.',
            pagamento_horas: 'Horas pagas: saem do saldo do colaborador.',
            folga_compensatoria: 'Horas gozadas em descanso: saem do saldo do colaborador.',
            prevencao_piquete: 'Subsídio de prevenção de um sábado de piquete (normalmente gerado automaticamente).',
            pagamento_prevencao: 'Valor de prevenções pago: sai do valor por pagar.'
        };
        var tipo = document.getElementById('tb-tipo');
        if (!tipo) return;
        var horas = document.getElementById('tb-horas-field');
        var valor = document.getElementById('tb-valor-field');
        var helpEl = document.getElementById('tb-help');
        var sync = function () {
            var t = types[tipo.value] || {};
            var euros = t.unit === 'euros';
            horas.style.display = euros ? 'none' : '';
            valor.style.display = euros ? '' : 'none';
            horas.querySelector('input').disabled = euros;
            valor.querySelector('input').disabled = !euros;
            helpEl.textContent = help[tipo.value] || '';
        };
        tipo.addEventListener('change', sync);
        sync();
    });
</script>
@endpush

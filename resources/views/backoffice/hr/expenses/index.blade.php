@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Despesas') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filtros = request()->only(['colaborador', 'categoria', 'de', 'ate']))
@php($euros = fn ($v) => number_format((float) $v, 2, ',', ' ') . ' €')

@push('styles')
<style>
    .ex-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: .75rem; }
    .ex-stat { display: block; border-radius: 14px; padding: .8rem 1rem; background: var(--bg); color: var(--fg);
               border: 2px solid transparent; text-decoration: none !important; transition: transform .15s; }
    .ex-stat:hover { transform: translateY(-2px); color: var(--fg); }
    .ex-stat.is-active { border-color: var(--fg); }
    .ex-stat .num { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
    .ex-stat .lbl { font-size: .78rem; font-weight: 600; margin-top: .25rem; }
    .ex-stat .sub { font-size: .72rem; opacity: .8; }

    .ex-card { background: #fff; border: 1px solid #e2e8f0; border-left: 5px solid var(--c); border-radius: 12px;
               padding: .85rem 1rem; margin-bottom: .75rem; }
    .ex-head { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; }
    .ex-quem { font-weight: 700; color: #0f172a; }
    .ex-cat { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
              padding: .1rem .45rem; border-radius: 5px; background: #f1f5f9; color: #475569; }
    .ex-valor { margin-left: auto; font-size: 1.1rem; font-weight: 800; color: #0f172a; font-variant-numeric: tabular-nums; }
    .ex-estado { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 700;
                 padding: .15rem .55rem; border-radius: 999px; background: var(--bg); color: var(--text); border: 1px solid var(--c); }
    .ex-desc { margin: .4rem 0 .2rem; color: #334155; }
    .ex-meta { font-size: .78rem; color: #94a3b8; }
    .ex-nota { font-size: .82rem; color: #475569; background: #f8fafc; border-left: 3px solid #cbd5e1;
               border-radius: 0 6px 6px 0; padding: .3rem .6rem; margin-top: .4rem; }
    .ex-pago { font-size: .82rem; font-weight: 700; color: #1b5e20; background: #dff0e0;
               border-radius: 8px; padding: .3rem .6rem; margin-top: .5rem; display: inline-flex; align-items: center; gap: .4rem; }

    .ex-anexos { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .5rem; }
    .ex-anexo { display: inline-flex; align-items: center; gap: .35rem; font-size: .76rem; padding: .2rem .5rem;
                border: 1px solid #e2e8f0; border-radius: 8px; color: #334155; background: #fff; max-width: 260px; }
    .ex-anexo:hover { border-color: #0f4c81; color: #0f4c81; text-decoration: none; }
    .ex-anexo .nome { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ex-anexo .tam { color: #94a3b8; font-size: .7rem; }
    .ex-anexo form { margin: 0; }
    .ex-anexo button { border: 0; background: none; color: #c62828; padding: 0 0 0 .15rem; font-size: .72rem; }
    .ex-sem-anexo { font-size: .76rem; color: #c2410c; background: #fff7ed; border-radius: 8px; padding: .2rem .5rem; display: inline-block; margin-top: .5rem; }

    .ex-acoes { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .6rem; padding-top: .6rem; border-top: 1px dashed #e2e8f0; }
    .ex-acoes form { margin: 0; }
</style>
@endpush

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: .75rem">
            <div>
                <h4 class="mb-1"><i class="fas fa-receipt text-success"></i> {{ __('Despesas') }}</h4>
                <p class="text-muted small mb-0">
                    {{ $isManager ? __('Despesas de toda a equipa. A direção aprova e marca o pagamento.') : __('As suas despesas. Anexe sempre o comprovativo.') }}
                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center" style="gap: .5rem">
                <form method="GET" class="form-inline" style="gap: .5rem">
                    @if(request('estado'))<input type="hidden" name="estado" value="{{ request('estado') }}">@endif
                    @if($isManager)
                        <select name="colaborador" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">{{ __('Todos os colaboradores') }}</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" @selected(request('colaborador') == $s->id)>{{ $s->user->name ?? '—' }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select name="categoria" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">{{ __('Todas as categorias') }}</option>
                        @foreach($categories as $valor => $label)
                            <option value="{{ $valor }}" @selected(request('categoria') === $valor)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="de" class="form-control form-control-sm" value="{{ request('de') }}" title="{{ __('De') }}" onchange="this.form.submit()">
                    <input type="date" name="ate" class="form-control form-control-sm" value="{{ request('ate') }}" title="{{ __('Até') }}" onchange="this.form.submit()">
                </form>
                <a href="{{ route('backoffice.hr.expenses.export', request()->only(['colaborador', 'categoria', 'de', 'ate', 'estado'])) }}"
                   class="btn btn-outline-success btn-sm"><i class="fa fa-file-excel"></i> {{ __('Exportar Excel') }}</a>
                <button type="button" class="btn btn-success btn-sm" data-toggle="collapse" data-target="#ex-nova">
                    <i class="fa fa-plus"></i> {{ __('Nova despesa') }}
                </button>
            </div>
        </div>

        <div class="ex-summary">
            <a href="{{ route('backoffice.hr.expenses.index', $filtros) }}" class="ex-stat {{ request('estado') ? '' : 'is-active' }}" style="--bg: #f1f3f6; --fg: #263238">
                <div class="num">{{ $counts->sum('total') }}</div>
                <div class="lbl">{{ __('Todas') }}</div>
                <div class="sub">{{ $euros($counts->sum('soma')) }}</div>
            </a>
            <a href="{{ route('backoffice.hr.expenses.index', ['estado' => 'por_pagar'] + $filtros) }}" class="ex-stat {{ request('estado') === 'por_pagar' ? 'is-active' : '' }}" style="--bg: #e0f2fe; --fg: #0369a1">
                <div class="num">{{ $numPorPagar }}</div>
                <div class="lbl"><i class="fas fa-hand-holding-usd"></i> {{ __('Por pagar') }}</div>
                <div class="sub">{{ $euros($totalPorPagar) }}</div>
            </a>
            @foreach($states as $chave => $estado)
                <a href="{{ route('backoffice.hr.expenses.index', ['estado' => $chave] + $filtros) }}" class="ex-stat {{ request('estado') === $chave ? 'is-active' : '' }}" style="--bg: {{ $estado['bg'] }}; --fg: {{ $estado['text'] }}">
                    <div class="num">{{ $counts[$chave]->total ?? 0 }}</div>
                    <div class="lbl"><i class="fas {{ $estado['icon'] }}"></i> {{ __($estado['label']) }}</div>
                    <div class="sub">{{ $euros($counts[$chave]->soma ?? 0) }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>
@endif

{{-- Lançar uma despesa --}}
<div class="collapse {{ $errors->any() ? 'show' : '' }} mb-3" id="ex-nova">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fa fa-plus-circle text-success"></i> {{ __('Nova despesa') }}</h5>
            <form method="POST" action="{{ route('backoffice.hr.expenses.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    @if($isManager)
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold mb-1">{{ __('Colaborador') }}</label>
                            <select name="hr_staff_id" class="form-control" required>
                                <option value="">{{ __('Escolha…') }}</option>
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}" @selected(old('hr_staff_id', $ownStaffId) == $s->id)>{{ $s->user->name ?? '—' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="form-group col-md-2">
                        <label class="small font-weight-bold mb-1">{{ __('Data') }}</label>
                        <input type="date" name="data" class="form-control" value="{{ old('data', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small font-weight-bold mb-1">{{ __('Categoria') }}</label>
                        <select name="categoria" class="form-control" required>
                            @foreach($categories as $valor => $label)
                                <option value="{{ $valor }}" @selected(old('categoria') === $valor)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small font-weight-bold mb-1">{{ __('Valor') }} (€)</label>
                        <input type="number" name="valor" class="form-control" step="0.01" min="0.01" value="{{ old('valor') }}" placeholder="0,00" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold mb-1">{{ __('Descrição') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                    <input type="text" name="descricao" class="form-control" value="{{ old('descricao') }}" maxlength="255"
                           placeholder="{{ __('Ex.: deslocação a Braga para reparação na loja 229') }}">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold mb-1">{{ __('Comprovativos') }}</label>
                    <input type="file" name="anexos[]" class="form-control-file" multiple accept=".jpg,.jpeg,.png,.heic,.heif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt">
                    <small class="form-text text-muted">{{ __('Fotografia, PDF, Word, Excel ou texto. Até 10 ficheiros, 32 MB cada.') }}</small>
                </div>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> {{ __('Lançar despesa') }}</button>
            </form>
        </div>
    </div>
</div>

{{-- Lista --}}
@if($expenses->isEmpty())
    <div class="card"><div class="card-body text-center text-muted py-5">
        <i class="fas fa-receipt fa-3x mb-3"></i>
        <p class="mb-0">{{ __('Nenhuma despesa corresponde aos filtros.') }}</p>
    </div></div>
@else
    @foreach($expenses as $e)
        @php($estado = $e->state())
        @php($meu = $e->hr_staff_id === $ownStaffId)
        <div class="ex-card" style="--c: {{ $e->isPaid() ? '#15803d' : $estado['color'] }}">
            <div class="ex-head">
                <span class="ex-quem">{{ $e->staff->user->name ?? '—' }}</span>
                <span class="ex-cat">{{ $e->categoriaLabel() }}</span>
                <span class="ex-estado" style="--bg: {{ $estado['bg'] }}; --text: {{ $estado['text'] }}; --c: {{ $estado['color'] }}">
                    <i class="fas {{ $estado['icon'] }}"></i> {{ $e->stateLabel() }}
                </span>
                <span class="ex-valor">{{ $euros($e->valor) }}</span>
            </div>

            @if($e->descricao)<p class="ex-desc">{{ $e->descricao }}</p>@endif
            <div class="ex-meta {{ $e->descricao ? '' : 'mt-2' }}">
                {{ __('Despesa de') }} {{ $e->data->format('d/m/Y') }}
                @if($e->decided_at)
                    · {{ $e->estado === 'aprovada' ? __('Aprovada por') : __('Rejeitada por') }}
                    {{ $e->decider->name ?? '—' }} {{ __('a') }} {{ $e->decided_at->format('d/m/Y') }}
                @endif
            </div>

            @if($e->motivo_rejeicao)
                <div class="ex-nota"><strong>{{ __('Motivo') }}:</strong> {{ $e->motivo_rejeicao }}</div>
            @endif

            @if($e->isPaid())
                <div class="ex-pago">
                    <i class="fas fa-check-circle"></i>
                    {{ __('Paga a') }} {{ $e->pago_em->format('d/m/Y') }}
                    @if($e->paymentLabel()) · {{ $e->paymentLabel() }} @endif
                    @if($e->payer) · {{ $e->payer->name }} @endif
                </div>
            @endif

            {{-- Comprovativos --}}
            @if($e->files->isNotEmpty())
                <div class="ex-anexos">
                    @foreach($e->files as $ficheiro)
                        <span class="ex-anexo">
                            <a href="{{ route('backoffice.hr.expenses.files.show', $ficheiro->id) }}" target="_blank" class="d-inline-flex align-items-center" style="gap: .35rem; color: inherit">
                                <i class="fa {{ $ficheiro->icon() }}"></i>
                                <span class="nome">{{ $ficheiro->nome }}</span>
                                <span class="tam">{{ $ficheiro->sizeLabel() }}</span>
                            </a>
                            @if(($meu && $e->isOpen()) || $isManager)
                                <form method="POST" action="{{ route('backoffice.hr.expenses.files.delete', $ficheiro->id) }}"
                                      onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Remover este comprovativo?')) }})">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="{{ __('Remover') }}"><i class="fa fa-times"></i></button>
                                </form>
                            @endif
                        </span>
                    @endforeach
                </div>
            @else
                <div class="ex-sem-anexo"><i class="fa fa-exclamation-triangle"></i> {{ __('Sem comprovativo anexado') }}</div>
            @endif

            {{-- Ações --}}
            @php($podeAnexar = ($meu && $e->isOpen()) || $isManager)
            @if($canApprove || $podeAnexar)
                <div class="ex-acoes">
                    @if($podeAnexar)
                        <a href="#ex-anexar-{{ $e->id }}" data-toggle="collapse" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-paperclip"></i> {{ __('Anexar') }}
                        </a>
                    @endif

                    @if($canApprove && $e->estado === 'pendente')
                        <form method="POST" action="{{ route('backoffice.hr.expenses.approve', $e->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-success"><i class="fa fa-check"></i> {{ __('Aprovar') }}</button>
                        </form>
                        <a href="#ex-rejeitar-{{ $e->id }}" data-toggle="collapse" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-times"></i> {{ __('Rejeitar') }}
                        </a>
                    @endif

                    @if($canApprove && $e->canBePaid())
                        <a href="#ex-pagar-{{ $e->id }}" data-toggle="collapse" class="btn btn-sm btn-primary">
                            <i class="fa fa-euro-sign"></i> {{ __('Marcar como paga') }}
                        </a>
                    @endif

                    @if($canApprove && $e->isPaid())
                        <form method="POST" action="{{ route('backoffice.hr.expenses.unpay', $e->id) }}"
                              onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Desmarcar o pagamento desta despesa?')) }})">
                            @csrf
                            <button class="btn btn-sm btn-link text-secondary"><i class="fa fa-undo"></i> {{ __('Afinal não foi paga') }}</button>
                        </form>
                    @endif

                    @if(($meu && $e->isOpen()) || $isManager)
                        <form method="POST" action="{{ route('backoffice.hr.expenses.destroy', $e->id) }}" class="ml-auto"
                              onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Apagar esta despesa? Os comprovativos também são apagados.')) }})">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger"><i class="fa fa-trash"></i> {{ __('Apagar') }}</button>
                        </form>
                    @endif
                </div>
            @endif

            @if($podeAnexar)
                <div class="collapse mt-2" id="ex-anexar-{{ $e->id }}">
                    <form method="POST" action="{{ route('backoffice.hr.expenses.files.store', $e->id) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="anexos[]" class="form-control-file mb-2" multiple required
                               accept=".jpg,.jpeg,.png,.heic,.heif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt">
                        <button class="btn btn-sm btn-primary"><i class="fa fa-upload"></i> {{ __('Anexar comprovativos') }}</button>
                    </form>
                </div>
            @endif

            @if($canApprove && $e->estado === 'pendente')
                <div class="collapse mt-2" id="ex-rejeitar-{{ $e->id }}">
                    <form method="POST" action="{{ route('backoffice.hr.expenses.reject', $e->id) }}">
                        @csrf
                        <div class="form-group mb-2">
                            <textarea name="motivo_rejeicao" class="form-control form-control-sm" rows="2" required
                                      placeholder="{{ __('Porque está a rejeitar? O colaborador vai ler isto.') }}"></textarea>
                        </div>
                        <button class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i> {{ __('Rejeitar despesa') }}</button>
                    </form>
                </div>
            @endif

            @if($canApprove && $e->canBePaid())
                <div class="collapse mt-2" id="ex-pagar-{{ $e->id }}">
                    <form method="POST" action="{{ route('backoffice.hr.expenses.pay', $e->id) }}" class="form-inline" style="gap: .5rem">
                        @csrf
                        <input type="date" name="pago_em" class="form-control form-control-sm" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                        <select name="pago_como" class="form-control form-control-sm">
                            <option value="">{{ __('Como foi paga…') }}</option>
                            @foreach($paymentMethods as $valor => $label)
                                <option value="{{ $valor }}">{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-primary"><i class="fa fa-check"></i> {{ __('Confirmar pagamento') }}</button>
                    </form>
                </div>
            @endif
        </div>
    @endforeach

    @if($expenses->hasPages())
        <div class="mt-3 d-flex justify-content-center">{{ $expenses->links() }}</div>
    @endif
@endif
@endsection

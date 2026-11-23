@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filters = request()->only(['q', 'estado', 'cliente', 'mes']))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Manutenções Anuais') }} {{ $year }}</title>
@endsection

@push('styles')
<style>
    .mt-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .75rem; }
    .mt-stat { display: block; border-radius: 14px; padding: .8rem 1rem; background: var(--bg); color: var(--fg); border: 2px solid transparent;
               text-decoration: none !important; transition: transform .15s; }
    .mt-stat:hover { transform: translateY(-2px); color: var(--fg); }
    .mt-stat.is-active { border-color: var(--fg); }
    .mt-stat .num { font-size: 1.8rem; font-weight: 700; line-height: 1; }
    .mt-stat .lbl { font-size: .8rem; font-weight: 600; margin-top: .25rem; }
    .mt-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .78rem; font-weight: 600; padding: .2rem .55rem;
                border-radius: 999px; background: var(--bg); color: var(--fg); border: 1px solid var(--fg); white-space: nowrap; }
    .mt-table td { vertical-align: middle; }
    /* O cliente salta à vista: cor própria na barra da linha e na etiqueta */
    .mt-table tr { border-left: 5px solid var(--cliente, transparent); }
    .mt-cliente { display: inline-flex; align-items: center; gap: .32rem; font-size: .74rem; font-weight: 800;
                  text-transform: uppercase; letter-spacing: .05em; padding: .26rem .66rem; border-radius: 999px;
                  background: var(--grad, var(--bg)); color: var(--txt, var(--fg));
                  box-shadow: 0 6px 14px var(--sombra, transparent); vertical-align: middle; }
    .mt-clientes { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .75rem; }
    .mt-cliente-btn { display: inline-flex; align-items: center; gap: .35rem; border: 1px solid #dde3ea; background: #fff;
                      border-radius: 999px; padding: .22rem .75rem; font-size: .82rem; font-weight: 700; color: #475569;
                      text-decoration: none !important; }
    .mt-cliente-btn:hover { border-color: var(--fg, #94a3b8); color: var(--fg, #334155); }
    .mt-cliente-btn.on { background: var(--grad, #eef2f7); color: var(--txt, #263238); border-color: transparent;
                         box-shadow: 0 6px 14px var(--sombra, transparent); }
    .mt-cliente-btn .n { font-weight: 800; opacity: .75; }
    .mt-mes { font-weight: 800; font-size: .95rem; text-transform: capitalize; }
    /* Barra dos meses */
    .mt-meses { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .75rem; }
    .mt-mes-btn { display: inline-flex; align-items: center; gap: .3rem; border: 1px solid #dde3ea; background: #fff;
                  color: #475569; border-radius: 9px; padding: .22rem .6rem; font-size: .82rem; font-weight: 700;
                  text-decoration: none !important; text-transform: capitalize; }
    .mt-mes-btn:hover { border-color: #0f4c81; color: #0f4c81; }
    .mt-mes-btn.on { background: #0f4c81; border-color: #0f4c81; color: #fff; }
    .mt-mes-btn.zero { color: #b9c3cf; }
    .mt-mes-btn.zero .n { opacity: .5; }
    .mt-mes-btn.agora { border-color: #0f4c81; box-shadow: 0 0 0 2px #dbeafe; }
    .mt-mes-btn.agora::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #0f4c81; }
    .mt-mes-btn.agora.on::before { background: #fff; }
    .mt-mes-btn .n { background: #eef2f7; color: #475569; border-radius: 999px; padding: 0 .4rem; font-size: .72rem; }
    .mt-mes-btn.on .n { background: rgba(255, 255, 255, .25); color: #fff; }
    /* Definir a data/mês na própria linha, sem abrir formulários */
    .mt-definir { display: flex; gap: .3rem; align-items: center; }
    .mt-definir input { max-width: 10.5rem; }
    .mt-store { font-weight: 600; }
    .mt-store small { display: block; font-weight: 400; color: #6c757d; }
    .mt-window { white-space: nowrap; font-size: .85rem; }
    .mt-window .due { font-weight: 700; font-size: .95rem; }
    /* Mesmo verde da etiqueta "Feita" (config maintenance.states.feita) */
    .mt-done-date { display: inline-flex; align-items: center; gap: .3rem; padding: .15rem .55rem; border-radius: 999px; background: #dff0e0; color: #2e7d32;
                    border: 1px solid #2e7d32; font-weight: 700; font-size: .78rem; white-space: nowrap; }
    .mt-doc { display: inline-flex; align-items: center; gap: .3rem; margin-top: .3rem; font-weight: 600; font-size: .78rem; color: #1565c0; background: #e3f0ff; border-radius: 999px; padding: .1rem .55rem; text-decoration: none !important; }
    .mt-doc:hover { background: #cfe3ff; }
    .mt-notes { font-size: .84rem; color: #334155; max-width: 280px; white-space: pre-line; overflow-wrap: anywhere; }
    .mt-doc-missing { display: inline-block; margin-top: .3rem; font-size: .74rem; color: #8a96a8; }
    /* As máquinas da loja, com o mesmo aspeto que têm na página das Lojas */
    .mt-maquinas { display: flex; flex-wrap: wrap; gap: 6px; margin-top: .4rem; }
    .mt-maquina { min-width: 9rem; background: linear-gradient(180deg, #fff 0%, #f8fbff 100%); border: 1px solid #dfe9f5;
                  border-radius: 12px; padding: .4rem .5rem; box-shadow: 0 4px 10px rgba(19, 34, 56, .04); font-weight: 400; }
    .mt-maquina .serie { display: inline-flex; align-items: center; gap: 5px; padding: .2rem .55rem; border-radius: 999px;
                         background: linear-gradient(135deg, #bbf7d0 0%, #6ee7b7 100%); color: #065f46;
                         font-size: .76rem; font-weight: 800; }
    .mt-maquina .modelo { color: #6c7a89; font-size: .73rem; line-height: 1.3; margin-top: .3rem; }
    .mt-maquina .modelo i { color: #b6c2d1; margin-right: .1rem; }
    /* Anexos: folhas de serviço e fotografias */
    .mt-anexos { display: flex; flex-wrap: wrap; gap: .25rem; margin-top: .35rem; }
    .mt-anexo { display: inline-flex; align-items: center; gap: .15rem; border-radius: 999px; padding: .05rem .25rem .05rem .5rem;
                font-size: .74rem; font-weight: 600; background: #e3f0ff; color: #1565c0; }
    .mt-anexo.foto { background: #efe7fb; color: #6d28d9; }
    .mt-anexo a { color: inherit; text-decoration: none !important; }
    .mt-anexo form { display: inline; margin: 0; }
    .mt-anexo button { border: 0; background: none; color: inherit; opacity: .45; line-height: 1; padding: 0 .15rem; font-size: .9rem; }
    .mt-anexo button:hover { opacity: 1; color: #b3261e; }
</style>
@endpush

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: .75rem">
            <div class="d-flex align-items-center">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.maintenances.index', ['ano' => $year - 1] + $filters) }}"><i class="fa fa-chevron-left"></i></a>
                <h4 class="mb-0 mx-3"><i class="fas fa-clipboard-check text-primary"></i> {{ __('Manutenções Anuais') }} {{ $year }}</h4>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.maintenances.index', ['ano' => $year + 1] + $filters) }}"><i class="fa fa-chevron-right"></i></a>
            </div>
            <div class="d-flex flex-wrap" style="gap: .5rem">
                <form method="GET" class="form-inline">
                    <input type="hidden" name="ano" value="{{ $year }}">
                    @foreach(['estado', 'cliente', 'mes'] as $campo)
                        @if(request($campo))<input type="hidden" name="{{ $campo }}" value="{{ request($campo) }}">@endif
                    @endforeach
                    <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ __('Pesquisar loja, código, insígnia…') }}" style="min-width: 240px">
                </form>
                <a class="btn btn-outline-success btn-sm" href="{{ route('backoffice.maintenances.export', ['ano' => $year] + $filters) }}">
                    <i class="fa fa-file-excel"></i> {{ __('Excel') }}
                </a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('backoffice.maintenances.export.zip', ['ano' => $year] + $filters) }}"
                   title="{{ __('Excel mais as folhas de serviço e as fotografias') }}">
                    <i class="fa fa-file-archive"></i> {{ __('Excel + anexos') }}
                </a>
            </div>
        </div>

        <div class="mt-summary">
            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year] + \Illuminate\Support\Arr::except($filters, 'estado')) }}"
               class="mt-stat {{ request('estado') ? '' : 'is-active' }}" style="--bg: #f1f3f6; --fg: #263238">
                <div class="num">{{ $total }}</div><div class="lbl">{{ __('Total de lojas') }}</div>
            </a>
            @foreach(['atrasada', 'janela', 'proxima', 'futura', 'contrato_futuro', 'por_definir', 'feita'] as $key)
                @php($state = $states[$key])
                @php($n = $key === 'feita' ? ($counts['feita'] ?? 0) + ($counts['feita_fora'] ?? 0) : ($counts[$key] ?? 0))
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'estado' => $key] + $filters) }}"
                   class="mt-stat {{ request('estado') === $key ? 'is-active' : '' }}" style="--bg: {{ $state['bg'] }}; --fg: {{ $state['color'] }}">
                    <div class="num">{{ $n }}</div><div class="lbl"><i class="fas {{ $state['icon'] }}"></i> {{ $key === 'feita' ? __('Feitas') : __($state['label']) }}</div>
                </a>
            @endforeach
        </div>
        <div class="mt-clientes">
            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year] + \Illuminate\Support\Arr::except($filters, 'cliente')) }}"
               class="mt-cliente-btn {{ request('cliente') ? '' : 'on' }}">
                <i class="fas fa-building"></i> {{ __('Todos os clientes') }} <span class="n">{{ $total }}</span>
            </a>
            @foreach($clientesConfig as $key => $cfg)
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'cliente' => $key] + $filters) }}"
                   class="mt-cliente-btn {{ request('cliente') === $key ? 'on' : '' }}"
                   style="--bg: {{ $cfg['bg'] }}; --fg: {{ $cfg['color'] }}; --grad: {{ $cfg['gradiente'] }}; --txt: {{ $cfg['texto'] }}; --sombra: {{ $cfg['sombra'] }}">
                    <i class="fas {{ $cfg['icon'] }}"></i> {{ $cfg['label'] }} <span class="n">{{ $clientes[$key] ?? 0 }}</span>
                </a>
            @endforeach
            @if(($clientes['outros'] ?? 0) > 0)
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'cliente' => 'outros'] + $filters) }}"
                   class="mt-cliente-btn {{ request('cliente') === 'outros' ? 'on' : '' }}">
                    {{ __('Outros') }} <span class="n">{{ $clientes['outros'] }}</span>
                </a>
            @endif
        </div>

        {{-- Os meses do ano que se está a ver: é por aqui que se planeia --}}
        <div class="mt-meses">
            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year] + \Illuminate\Support\Arr::except($filters, 'mes')) }}"
               class="mt-mes-btn {{ $mesEscolhido ? '' : 'on' }}">{{ __('Ano todo') }}</a>

            @foreach(range(1, 12) as $m)
                @php($n = $meses[$m] ?? 0)
                @php($agora = $m === now()->month && $year === now()->year)
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'mes' => $m] + $filters) }}"
                   class="mt-mes-btn {{ (int) $mesEscolhido === $m ? 'on' : '' }} {{ $n ? '' : 'zero' }} {{ $agora ? 'agora' : '' }}"
                   title="{{ $agora ? __('Este mês') : '' }}">
                    {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::create($year, $m, 1)->isoFormat('MMM')) }}
                    <span class="n">{{ $n }}</span>
                </a>
            @endforeach

            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'mes' => 'sem_data'] + $filters) }}"
               class="mt-mes-btn {{ $mesEscolhido === 'sem_data' ? 'on' : '' }}">
                {{ __('Sem data neste ano') }}
                <span class="n">{{ ($counts['por_definir'] ?? 0) + ($counts['contrato_futuro'] ?? 0) }}</span>
            </a>
        </div>

        <p class="small text-muted mb-0 mt-2">
            <i class="fa fa-info-circle"></i>
            <b>{{ __('Sonae') }}:</b> {{ __('a manutenção pode ser feita em qualquer dia do mês contratado.') }}
            <b class="ml-2">{{ __('Lidl') }}:</b> {{ __('entre :dias dias úteis antes e :dias dias úteis depois da data contratada (de segunda a sábado; domingos e feriados não contam).', ['dias' => $windowDays]) }}
        </p>
    </div>
</div>

{{-- Lista --}}
<div class="card">
    <div class="card-body">
        @if($rows->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                <p class="mb-0">{{ $total ? __('Nenhuma loja corresponde aos filtros.') : __('Ainda não há lojas com manutenção anual registada para este ano.') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table mt-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Loja') }}</th>
                            <th>{{ __('Data contratada / prazo') }} {{ $year }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th>{{ __('Feita em') }}</th>
                            <th>{{ __('Observações') }}</th>
                            <th class="text-right">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @php($state = $states[$row->status])
                            @php($cliente = $row->store->clienteConfig())
                            <tr style="--cliente: {{ $cliente['color'] ?? 'transparent' }}">
                                <td class="mt-store">
                                    @if($cliente)
                                        <span class="mt-cliente"
                                              style="--grad: {{ $cliente['gradiente'] }}; --txt: {{ $cliente['texto'] }}; --sombra: {{ $cliente['sombra'] }}">
                                            <i class="fas {{ $cliente['icon'] }}"></i> {{ $cliente['label'] }}
                                        </span>
                                    @endif
                                    {{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}
                                    <small>{{ $row->store->cidade }}</small>
                                    @if($row->store->machines->isNotEmpty())
                                        <div class="mt-maquinas">
                                            @foreach($row->store->machines as $maquina)
                                                <div class="mt-maquina">
                                                    <span class="serie"><i class="fa fa-hashtag"></i> {{ $maquina->serial_number }}</span>
                                                    <div class="modelo">
                                                        <i class="fa fa-cogs"></i>
                                                        {{ $maquina->descricao ?: ($maquina->modelo ?: __('Modelo não definido')) }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="mt-window">
                                    @if(!$row->maintenance)
                                        @php($mensal = in_array($row->store->clienteKey(), config('maintenance.month_clients', []), true))
                                        <form method="POST" action="{{ route('backoffice.maintenances.store') }}" class="mt-definir">
                                            @csrf
                                            <input type="hidden" name="store_id" value="{{ $row->store->id }}">
                                            <input type="{{ $mensal ? 'month' : 'date' }}" name="data_contratada"
                                                   class="form-control form-control-sm" required>
                                            <button type="submit" class="btn btn-sm btn-primary" title="{{ __('Gravar') }}"><i class="fa fa-check"></i></button>
                                        </form>
                                        <div class="text-muted">{{ $mensal ? __('escolha o mês contratado') : __('escolha a data contratada') }}</div>
                                    @elseif($row->status === 'contrato_futuro')
                                        <span class="mt-mes">{{ $row->maintenance->data_contratada->translatedFormat('F Y') }}</span>
                                        <div class="text-muted">
                                            {{ __('começa em :ano', ['ano' => $row->maintenance->data_contratada->year]) }} ·
                                            <a href="{{ route('backoffice.maintenances.index', ['ano' => $row->maintenance->data_contratada->year] + $filters) }}">{{ __('ver esse ano') }}</a>
                                        </div>
                                    @elseif($row->maintenance->isMensal())
                                        <span class="mt-mes">{{ $row->due->translatedFormat('F') }} {{ $year }}</span>
                                        <div class="text-muted">{{ __('o mês inteiro') }}</div>
                                    @else
                                        <span class="due">{{ $row->due->format('d/m/Y') }}</span>
                                        <div class="text-muted">{{ __('de') }} {{ $row->start->format('d/m') }} {{ __('a') }} {{ $row->end->format('d/m') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="mt-badge" style="--bg: {{ $state['bg'] }}; --fg: {{ $state['color'] }}"><i class="fas {{ $state['icon'] }}"></i> {{ __($state['label']) }}</span>
                                    @if($row->status === 'atrasada')
                                        <div class="small text-danger mt-1">{{ $row->end->diffInDays(now()->startOfDay()) }} {{ __('dias de atraso') }}</div>
                                    @elseif($row->status === 'janela' && $row->maintenance->isMensal())
                                        <div class="small mt-1" style="color: {{ $state['color'] }}">{{ trans_choice('{0} hoje é o último dia do mês|{1} falta :count dia para acabar o mês|[2,*] faltam :count dias para acabar o mês', $n = now()->startOfDay()->diffInDays($row->end), ['count' => $n]) }}</div>
                                    @elseif($row->status === 'janela')
                                        <div class="small mt-1" style="color: {{ $state['color'] }}">{{ trans_choice('{0} hoje é o último dia do prazo|{1} falta :count dia útil para o fim do prazo|[2,*] faltam :count dias úteis para o fim do prazo', $n = \App\Models\StoreMaintenance::workingDaysBetween(now()->startOfDay(), $row->end), ['count' => $n]) }}</div>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($row->record)
                                        <span class="mt-done-date"><i class="fa fa-check-circle"></i> {{ $row->record->data_realizada->format('d/m/Y') }}</span>
                                        @if($row->record->technician)<div>{{ $row->record->technician->name }}</div>@endif
                                        @if($row->record->observacoes)<div class="text-muted">{{ \Illuminate\Support\Str::limit($row->record->observacoes, 60) }}</div>@endif
                                        <div class="mt-anexos">
                                            @forelse($row->record->files as $anexo)
                                                <span class="mt-anexo {{ $anexo->tipo }}">
                                                    <a href="{{ route('backoffice.maintenances.file', $anexo->id) }}" target="_blank" rel="noopener"
                                                       title="{{ $anexo->nome }} {{ $anexo->tamanhoLegivel() }}">
                                                        <i class="fas {{ \App\Models\StoreMaintenanceFile::TIPOS[$anexo->tipo]['icon'] }}"></i>
                                                        {{ \Illuminate\Support\Str::limit($anexo->nome, 16) }}
                                                    </a>
                                                    <form method="POST" action="{{ route('backoffice.maintenances.file.delete', $anexo->id) }}"
                                                          onsubmit="return confirm({{ json_encode(__('Remover este anexo?')) }})">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" title="{{ __('Remover') }}">&times;</button>
                                                    </form>
                                                </span>
                                            @empty
                                                <span class="mt-doc-missing"><i class="fa fa-paperclip"></i> {{ __('sem anexos') }}</span>
                                            @endforelse
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="mt-notes">
                                    @if($row->maintenance?->observacoes)
                                        {{ $row->maintenance->observacoes }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right text-nowrap">
                                    @if(!$row->maintenance)
                                        <span class="text-muted">—</span>
                                    @elseif($row->record)
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#mt-attach"
                                                title="{{ __('Anexar folhas de serviço ou fotografias') }}"
                                                data-action="{{ route('backoffice.maintenances.files.store', $row->record->id) }}"
                                                data-store="{{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}">
                                            <i class="fa fa-paperclip"></i>
                                        </button>
                                        <form method="POST" action="{{ route('backoffice.maintenances.undo', $row->record->id) }}" class="d-inline"
                                              onsubmit="return confirm({{ json_encode(__('Anular o registo desta manutenção? Volta a ficar por fazer (a folha de obra anexada também é apagada).')) }})">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-secondary" title="{{ __('Anular registo') }}"><i class="fa fa-undo"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#mt-done"
                                                data-action="{{ route('backoffice.maintenances.done', $row->maintenance->id) }}"
                                                data-store="{{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}"
                                                data-window="{{ $row->maintenance->isMensal()
                                                    ? \Illuminate\Support\Str::ucfirst($row->due->translatedFormat('F \d\e Y')) . ' (' . __('o mês inteiro') . ')'
                                                    : $row->start->format('d/m/Y') . ' ' . __('a') . ' ' . $row->end->format('d/m/Y') }}">
                                            <i class="fa fa-check"></i> {{ __('Marcar feita') }}
                                        </button>
                                    @endif
                                    @if($row->maintenance)
                                    <button type="button" class="btn btn-sm btn-link text-secondary px-1" title="{{ __('Editar') }}" data-toggle="modal" data-target="#mt-edit"
                                            data-action="{{ route('backoffice.maintenances.update', $row->maintenance->id) }}"
                                            data-delete="{{ route('backoffice.maintenances.destroy', $row->maintenance->id) }}"
                                            data-store="{{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}"
                                            data-date="{{ $row->maintenance->data_contratada->toDateString() }}"
                                            data-mes="{{ $row->maintenance->data_contratada->format('Y-m') }}"
                                            data-mensal="{{ $row->maintenance->isMensal() ? 1 : 0 }}"
                                            data-notes="{{ $row->maintenance->observacoes }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Modal: marcar como feita --}}
<div class="modal fade" id="mt-done" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content" id="mt-done-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ano" value="{{ $year }}">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-check-circle text-success"></i> {{ __('Manutenção feita') }} {{ $year }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-1 font-weight-bold" id="mt-done-store"></p>
                <p class="small text-muted">{{ __('Prazo') }}: <span id="mt-done-window"></span></p>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Data em que foi feita') }}</label>
                    <input type="date" name="data_realizada" class="form-control" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                </div>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Técnico') }}</label>
                    <select name="user_id" class="form-control">
                        <option value="">—</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="small mb-1"><i class="fa fa-file-alt"></i> {{ __('Folhas de serviço (opcional)') }}</label>
                    <input type="file" name="folhas[]" multiple class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx">
                    <small class="form-text text-muted">{{ __('PDF, foto ou Word, até 32 MB cada. Podem ser anexadas mais tarde.') }}</small>
                </div>
                <div class="form-group mb-0">
                    <label class="small mb-1"><i class="fa fa-camera"></i> {{ __('Fotografias (opcional)') }}</label>
                    <input type="file" name="fotos[]" multiple class="form-control-file" accept="image/*,.heic,.heif">
                    <small class="form-text text-muted">{{ __('Pode escolher várias de uma vez.') }}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{ __('Marcar como feita') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: anexar folhas de serviço ou fotografias --}}
<div class="modal fade" id="mt-attach" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="mt-attach-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-paperclip text-primary"></i> {{ __('Anexar à manutenção') }} {{ $year }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="font-weight-bold" id="mt-attach-store"></p>
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Tipo de anexo') }}</label>
                        <select name="tipo" class="form-control">
                            @foreach(\App\Models\StoreMaintenanceFile::TIPOS as $chave => $tipo)
                                <option value="{{ $chave }}">{{ __($tipo['label']) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <input type="file" name="ficheiros[]" multiple class="form-control-file"
                               accept=".pdf,.jpg,.jpeg,.png,.heic,.heif,.webp,.doc,.docx" required>
                        <small class="form-text text-muted">{{ __('Pode escolher vários ficheiros de uma vez, até 32 MB cada.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> {{ __('Anexar') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: editar contrato --}}
<div class="modal fade" id="mt-edit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="mt-edit-form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> {{ __('Editar manutenção anual') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="font-weight-bold" id="mt-edit-store"></p>
                    <div class="form-group">
                        <label class="small mb-1" id="mt-edit-label">{{ __('Data contratada') }}</label>
                        <input type="date" name="data_contratada" id="mt-edit-date" class="form-control" required>
                        <small class="form-text text-muted" id="mt-edit-help">{{ __('Repete-se todos os anos no mesmo dia e mês.') }}</small>
                    </div>
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Observações') }}</label>
                        <textarea name="observacoes" id="mt-edit-notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" form="mt-delete-form" class="btn btn-link text-danger" onclick="return confirm({{ json_encode(__('Remover esta loja das manutenções anuais? O histórico também é apagado.')) }})">
                        <i class="fa fa-trash"></i> {{ __('Remover') }}
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                    </div>
                </div>
            </form>
            <form method="POST" id="mt-delete-form">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.jQuery) return;
        $('#mt-done').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-done-form').attr('action', b.data('action'));
            $('#mt-done-store').text(b.data('store'));
            $('#mt-done-window').text(b.data('window'));
        });
        $('#mt-attach').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-attach-form').attr('action', b.data('action'));
            $('#mt-attach-store').text(b.data('store'));
        });
        // Clientes mensais (Sonae): o campo pergunta o mês, não o dia
        var textos = {
            mes: @json(__('Mês contratado')),
            data: @json(__('Data contratada')),
            ajudaMes: @json(__('A manutenção pode ser feita em qualquer dia deste mês, todos os anos.')),
            ajudaData: @json(__('Repete-se todos os anos no mesmo dia e mês.'))
        };
        var porMes = function (input, label, help, mensal, valor) {
            input.attr('type', mensal ? 'month' : 'date');
            input.val(mensal && valor ? valor.substring(0, 7) : (valor || ''));
            if (label) label.text(mensal ? textos.mes : textos.data);
            if (help) help.text(mensal ? textos.ajudaMes : textos.ajudaData);
        };

        $('#mt-edit').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-edit-form').attr('action', b.data('action'));
            $('#mt-delete-form').attr('action', b.data('delete'));
            $('#mt-edit-store').text(b.data('store'));
            porMes($('#mt-edit-date'), $('#mt-edit-label'), $('#mt-edit-help'), b.data('mensal') == 1, b.data('date'));
            $('#mt-edit-notes').val(b.data('notes') || '');
        });

        if ($('.selectpicker').length && $.fn.selectpicker) $('.selectpicker').selectpicker();
    });
</script>
@endpush

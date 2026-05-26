@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Movimentos de Stock') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                    <div>
                        <h3 class="mb-1">{{ __('Movimentos de Stock') }}</h3>
                        <p class="text-muted mb-0">{{ __('Histórico completo das entradas, transferências, devoluções, consumos e ajustes.') }}</p>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        <a href="{{ route('backoffice.stock.export.movements', request()->only(['item_id', 'technician_id', 'movement_type', 'date_from', 'date_to', 'period', 'q'])) }}" class="btn btn-outline-success mr-2">
                            <i class="fas fa-file-excel mr-1"></i>{{ __('Exportar Excel') }}
                        </a>
                        <a href="{{ route('backoffice.stock.items.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>{{ __('Voltar ao stock') }}
                        </a>
                    </div>
                </div>

                @php
                    $periodOptions = [
                        '' => __('Tudo'),
                        'today' => __('Hoje'),
                        'week' => __('Esta semana'),
                        'month' => __('Este mês'),
                    ];
                    $activePeriod = (string) request('period', '');
                @endphp

                <div class="btn-group btn-group-sm mb-3" role="group" aria-label="{{ __('Período') }}">
                    @foreach($periodOptions as $value => $label)
                        <a href="{{ route('backoffice.stock.movements.index', array_merge(request()->except(['period', 'date_from', 'date_to', 'page']), $value === '' ? [] : ['period' => $value])) }}"
                           class="btn {{ $activePeriod === $value ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('backoffice.stock.movements.index') }}" class="row">
                    <div class="col-md-4 mb-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                   placeholder="{{ __('Pesquisar por peça, técnico, origem, destino ou notas...') }}">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <select name="item_id" class="form-control">
                            <option value="">{{ __('Todas as peças') }}</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ (string) request('item_id') === (string) $item->id ? 'selected' : '' }}>
                                    {{ $item->reference }} - {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <select name="technician_id" class="form-control">
                            <option value="">{{ __('Todos os técnicos') }}</option>
                            @foreach($technicians as $technician)
                                <option value="{{ $technician->id }}" {{ (string) request('technician_id') === (string) $technician->id ? 'selected' : '' }}>
                                    {{ $technician->name ?: $technician->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <select name="movement_type" class="form-control">
                            <option value="">{{ __('Todos os tipos') }}</option>
                            @foreach($movementTypes as $value => $label)
                                <option value="{{ $value }}" {{ request('movement_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" placeholder="{{ __('De') }}">
                    </div>
                    <div class="col-md-4 mb-2">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" placeholder="{{ __('Até') }}">
                    </div>
                    <div class="col-md-12 d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter mr-1"></i>{{ __('Filtrar') }}</button>
                        <a href="{{ route('backoffice.stock.movements.index') }}" class="btn btn-outline-secondary">{{ __('Limpar') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">
                        <i class="fas fa-list-ul mr-1"></i>{{ trans_choice('{0} Sem movimentos|{1} 1 movimento|[2,*] :count movimentos', $movements->total(), ['count' => $movements->total()]) }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Data') }}</th>
                                <th>{{ __('Peça') }}</th>
                                <th>{{ __('Tipo') }}</th>
                                <th>{{ __('Técnico') }}</th>
                                <th>{{ __('Quantidade') }}</th>
                                <th>{{ __('Origem') }}</th>
                                <th>{{ __('Destino') }}</th>
                                <th>{{ __('Notas') }}</th>
                                @if($canManageWarehouse)
                                    <th>{{ __('Criado por') }}</th>
                                    <th class="text-right">{{ __('Ações') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                <tr>
                                    <td>{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $movement->item->reference ?? '-' }}<div class="small text-muted">{{ $movement->item->name ?? '' }}</div></td>
                                    <td>
                                        <span class="mv-badge mv-badge--{{ $movement->type_color }}">
                                            <i class="fas {{ $movement->type_icon }}"></i>{{ $movement->type_label }}
                                        </span>
                                    </td>
                                    <td>{{ $movement->technician->name ?? $movement->technician->email ?? '—' }}</td>
                                    <td>
                                        @php
                                            $quantityClass = match ($movement->flow) {
                                                'in' => 'text-success',
                                                'out' => 'text-danger',
                                                default => 'text-info',
                                            };
                                        @endphp
                                        <strong class="{{ $quantityClass }}">{{ $movement->signed_quantity }}</strong>
                                    </td>
                                    <td>{{ $movement->source ?? '—' }}</td>
                                    <td>{{ $movement->destination ?? '—' }}</td>
                                    <td>{{ $movement->notes ?: '—' }}</td>
                                    @if($canManageWarehouse)
                                        <td>{{ $movement->creator->name ?? $movement->creator->email ?? '—' }}</td>
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('backoffice.stock.movements.destroy', $movement) }}" class="d-inline"
                                                  onsubmit="return confirm('{{ __('Remover este movimento e repor o stock? Esta ação não pode ser anulada.') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Remover movimento') }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageWarehouse ? 10 : 8 }}" class="text-center text-muted py-4">
                                        {{ __('Ainda não existem movimentos registados.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .mv-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35em .8em;
        font-size: .8rem;
        font-weight: 600;
        line-height: 1.2;
        border-radius: 50rem;
        white-space: nowrap;
    }
    .mv-badge i { font-size: .75em; }
    .mv-badge--success   { background-color: #e6f4ea; color: #1e7e34; }
    .mv-badge--info      { background-color: #e8f1fc; color: #1c64b4; }
    .mv-badge--primary   { background-color: #e8f0fe; color: #1857c4; }
    .mv-badge--danger    { background-color: #fdecea; color: #c0392b; }
    .mv-badge--warning   { background-color: #fff4e0; color: #9a6b00; }
    .mv-badge--secondary { background-color: #eceff1; color: #546e7a; }
</style>
@endpush
@endsection

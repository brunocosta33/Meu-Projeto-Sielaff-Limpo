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
                        <a href="{{ route('backoffice.stock.export.movements', request()->only(['item_id', 'technician_id', 'movement_type', 'date_from', 'date_to'])) }}" class="btn btn-outline-success mr-2">
                            <i class="fas fa-file-excel mr-1"></i>{{ __('Exportar Excel') }}
                        </a>
                        <a href="{{ route('backoffice.stock.items.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>{{ __('Voltar ao stock') }}
                        </a>
                    </div>
                </div>

                <form method="GET" action="{{ route('backoffice.stock.movements.index') }}" class="row">
                    <div class="col-md-3 mb-2">
                        <select name="item_id" class="form-control">
                            <option value="">{{ __('Todas as peças') }}</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ (string) request('item_id') === (string) $item->id ? 'selected' : '' }}>
                                    {{ $item->reference }} - {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="technician_id" class="form-control">
                            <option value="">{{ __('Todos os técnicos') }}</option>
                            @foreach($technicians as $technician)
                                <option value="{{ $technician->id }}" {{ (string) request('technician_id') === (string) $technician->id ? 'selected' : '' }}>
                                    {{ $technician->name ?: $technician->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="movement_type" class="form-control">
                            <option value="">{{ __('Todos os tipos') }}</option>
                            @foreach($movementTypes as $value => $label)
                                <option value="{{ $value }}" {{ request('movement_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>
                    <div class="col-md-2 mb-2">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>
                    <div class="col-md-12 d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-primary mr-2">{{ __('Filtrar') }}</button>
                        <a href="{{ route('backoffice.stock.movements.index') }}" class="btn btn-outline-secondary">{{ __('Limpar') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
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
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                <tr>
                                    <td>{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $movement->item->reference ?? '-' }}<div class="small text-muted">{{ $movement->item->name ?? '' }}</div></td>
                                    <td><span class="badge badge-info">{{ $movementTypes[$movement->movement_type] ?? $movement->movement_type }}</span></td>
                                    <td>{{ $movement->technician->name ?? $movement->technician->email ?? '—' }}</td>
                                    <td>
                                        @php $signedQuantity = $movement->movement_type === 'adjustment' ? $movement->quantity : abs($movement->quantity); @endphp
                                        <strong>{{ $signedQuantity > 0 ? '+' : '' }}{{ $signedQuantity }}</strong>
                                    </td>
                                    <td>{{ $movement->source ?? '—' }}</td>
                                    <td>{{ $movement->destination ?? '—' }}</td>
                                    <td>{{ $movement->notes ?: '—' }}</td>
                                    @if($canManageWarehouse)
                                        <td>{{ $movement->creator->name ?? $movement->creator->email ?? '—' }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageWarehouse ? 9 : 8 }}" class="text-center text-muted py-4">
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
@endsection

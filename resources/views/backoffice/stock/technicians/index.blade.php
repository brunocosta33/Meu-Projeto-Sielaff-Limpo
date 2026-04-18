@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Stock por Técnico') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div>
                        <h3 class="mb-1">{{ $canManageWarehouse ? __('Stock por Técnico') : __('Meu Stock') }}</h3>
                        <p class="text-muted mb-0">{{ __('Veja rapidamente que peças estão atribuídas a cada carrinha/técnico.') }}</p>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        <a href="{{ route('backoffice.stock.export.technicians') }}" class="btn btn-outline-success mr-2">
                            <i class="fas fa-file-excel mr-1"></i>{{ __('Exportar Excel') }}
                        </a>
                        @if($canManageWarehouse)
                            <a href="{{ route('backoffice.stock.items.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-boxes mr-1"></i>{{ __('Gerir stock') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse($technicians as $technician)
                @php
                    $technicianStocks = $stocks->get($technician->id, collect());
                    $totalQuantity = $technicianStocks->sum('quantity');
                @endphp
                <div class="col-xl-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">{{ $technician->name ?: $technician->email }}</h5>
                                    <div class="text-muted">{{ $technician->email }}</div>
                                </div>
                                <span class="badge badge-primary px-3 py-2">{{ $totalQuantity }} {{ __('unid.') }}</span>
                            </div>

                            @if($technicianStocks->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Referência') }}</th>
                                                <th>{{ __('Peça') }}</th>
                                                <th class="text-right">{{ __('Qtd.') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($technicianStocks as $stock)
                                                <tr>
                                                    <td><strong>{{ $stock->item->reference ?? '-' }}</strong></td>
                                                    <td>{{ $stock->item->name ?? '-' }}</td>
                                                    <td class="text-right">{{ $stock->quantity }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-muted">{{ __('Sem peças atribuídas neste momento.') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border text-muted">
                        {{ __('Não foram encontrados técnicos com stock atribuído.') }}
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

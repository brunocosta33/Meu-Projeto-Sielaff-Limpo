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

        @if($technicians->count() > 1)
            <div class="mb-3" style="max-width: 360px;">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="technicianFilter" class="form-control" placeholder="{{ __('Filtrar por técnico ou peça...') }}" autocomplete="off">
                </div>
            </div>
        @endif

        <div class="row" id="technicianCards">
            @forelse($technicians as $technician)
                @php
                    $technicianStocks = $stocks->get($technician->id, collect());
                    $totalQuantity = $technicianStocks->sum('quantity');
                    $distinctItems = $technicianStocks->count();
                    $technicianName = strtolower(trim($technician->name . ' ' . $technician->email));
                @endphp
                <div class="col-xl-6 mb-4 technician-card" data-technician="{{ $technicianName }}">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">{{ $technician->name ?: $technician->email }}</h5>
                                    <div class="text-muted small">{{ $technician->email }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-primary px-3 py-2"><span class="stock-unid">{{ $totalQuantity }}</span> {{ __('unid.') }}</span>
                                    <div class="text-muted small mt-1"><span class="stock-distinct">{{ $distinctItems }}</span> {{ __('peças') }}</div>
                                </div>
                            </div>

                            @if($technicianStocks->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Referência') }}</th>
                                                <th>{{ __('Peça') }}</th>
                                                <th class="text-right">{{ __('Qtd.') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($technicianStocks as $stock)
                                                <tr class="stock-row" data-item="{{ strtolower(($stock->item->reference ?? '') . ' ' . ($stock->item->name ?? '')) }}" data-qty="{{ (int) $stock->quantity }}">
                                                    <td><strong>{{ $stock->item->reference ?? '-' }}</strong></td>
                                                    <td>{{ $stock->item->name ?? '-' }}</td>
                                                    <td class="text-right">{{ $stock->quantity }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="font-weight-bold border-top">
                                                <td colspan="2" class="text-right">{{ __('Total') }}</td>
                                                <td class="text-right stock-total">{{ $totalQuantity }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="text-right mt-3">
                                    <a href="{{ route('backoffice.stock.export.technician', $technician) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-file-excel mr-1"></i>{{ __('Exportar este técnico') }}
                                    </a>
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

        <div id="technicianNoResults" class="alert alert-light border text-muted d-none">
            {{ __('Nenhum técnico corresponde à pesquisa.') }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('technicianFilter');
        if (!input) return;

        var cards = Array.prototype.slice.call(document.querySelectorAll('#technicianCards .technician-card'));
        var noResults = document.getElementById('technicianNoResults');

        input.addEventListener('input', function () {
            var term = this.value.trim().toLowerCase();
            var visibleCards = 0;

            cards.forEach(function (card) {
                var techMatch = card.getAttribute('data-technician').indexOf(term) !== -1;
                var rows = card.querySelectorAll('.stock-row');
                var visibleRows = 0;
                var visibleQty = 0;

                rows.forEach(function (row) {
                    // Sem pesquisa ou técnico corresponde: mostra todas as linhas.
                    // Caso contrário: só as linhas cuja peça corresponde.
                    var show = term === '' || techMatch || row.getAttribute('data-item').indexOf(term) !== -1;
                    row.classList.toggle('d-none', !show);
                    if (show) {
                        visibleRows++;
                        visibleQty += parseInt(row.getAttribute('data-qty'), 10) || 0;
                    }
                });

                // Cartão visível se o técnico corresponde (mostra mesmo sem peças) ou tem linhas a mostrar.
                var cardVisible = techMatch || visibleRows > 0;
                card.classList.toggle('d-none', !cardVisible);
                if (cardVisible) visibleCards++;

                // Recalcula os totais com base no que está visível.
                var totalEl = card.querySelector('.stock-total');
                if (totalEl) totalEl.textContent = visibleQty;
                var unidEl = card.querySelector('.stock-unid');
                if (unidEl) unidEl.textContent = visibleQty;
                var distinctEl = card.querySelector('.stock-distinct');
                if (distinctEl) distinctEl.textContent = visibleRows;
            });

            if (noResults) noResults.classList.toggle('d-none', visibleCards !== 0);
        });
    });
</script>
@endpush
@endsection

@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Histórico da Máquina') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

@php
    $statusColors = [
        'pendente' => 'warning',
        'agendado' => 'info',
        'aguarda_peca' => 'secondary',
        'concluido' => 'success',
        'cancelado' => 'danger',
    ];
@endphp

<div class="row">
    <div class="col-12">
        {{-- Ficha da máquina --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div>
                        <h3 class="mb-1"><i class="fas fa-server mr-2"></i>{{ $machine->serial_number }}</h3>
                        <p class="text-muted mb-0">
                            @if($machine->store)
                                {{ $machine->store->codigo_loja }} — {{ $machine->store->nome_loja }}
                            @else
                                {{ __('Sem loja associada') }}
                            @endif
                        </p>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        <a href="{{ route('backoffice.machines.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>{{ __('Voltar às máquinas') }}
                        </a>
                    </div>
                </div>

                <hr>

                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-2">
                        <div class="text-muted small text-uppercase">{{ __('Assistências') }}</div>
                        <div class="h4 mb-0">{{ $requests->count() }}</div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="text-muted small text-uppercase">{{ __('Peças gastas') }}</div>
                        <div class="h4 mb-0">{{ $totalPartsConsumed }}</div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="text-muted small text-uppercase">{{ __('Descrição') }}</div>
                        <div class="mb-0">{{ $machine->descricao ?: '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="text-muted small text-uppercase">{{ __('IP') }}</div>
                        <div class="mb-0">{{ $machine->ip_address ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- O que foi feito --}}
            <div class="col-lg-7 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="fas fa-tools mr-2 text-primary"></i>{{ __('O que foi feito') }}</h5>

                        @forelse($requests as $request)
                            @php $color = $statusColors[$request->estado] ?? 'secondary'; @endphp
                            <div class="border-left border-{{ $color }} pl-3 mb-3" style="border-left-width: 3px !important;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ optional($request->data_resolucao ?? $request->data_pedido)->format('d/m/Y') ?? '—' }}</strong>
                                        <span class="badge badge-{{ $color }} ml-1">{{ $statuses[$request->estado] ?? $request->estado }}</span>
                                        @if($request->tipo_servico)
                                            <span class="badge badge-light border ml-1">{{ $serviceTypes[$request->tipo_servico] ?? $request->tipo_servico }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('backoffice.technical_requests.show', $request->id) }}" class="btn btn-sm btn-link p-0">{{ __('ver') }}</a>
                                </div>
                                @if($request->descricao_problema)
                                    <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit($request->descricao_problema, 160) }}</div>
                                @endif
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-user-cog mr-1"></i>{{ $request->assignedPersonLabel() }}
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">{{ __('Ainda não há assistências registadas para esta máquina.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- O que foi gasto --}}
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="fas fa-box-open mr-2 text-danger"></i>{{ __('O que foi gasto') }}</h5>

                        @if($partsSummary->isNotEmpty())
                            <div class="text-muted small text-uppercase mb-1">{{ __('Total por peça') }}</div>
                            <table class="table table-sm mb-3">
                                <tbody>
                                    @foreach($partsSummary as $part)
                                        <tr>
                                            <td><strong>{{ $part['reference'] }}</strong> <span class="text-muted">{{ $part['name'] }}</span></td>
                                            <td class="text-right font-weight-bold">{{ $part['quantity'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="text-muted small text-uppercase mb-1">{{ __('Detalhe') }}</div>
                            <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Data') }}</th>
                                            <th>{{ __('Peça') }}</th>
                                            <th class="text-right">{{ __('Qtd.') }}</th>
                                            <th>{{ __('Técnico') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($consumptions as $movement)
                                            <tr>
                                                <td class="text-nowrap">{{ $movement->created_at?->format('d/m/Y') }}</td>
                                                <td>{{ $movement->item->reference ?? '—' }}</td>
                                                <td class="text-right">{{ $movement->quantity }}</td>
                                                <td>{{ $movement->technician->name ?? $movement->technician->email ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-muted">{{ __('Ainda não foram gastas peças nesta máquina.') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

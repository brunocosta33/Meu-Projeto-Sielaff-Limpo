@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Detalhes do Pedido') }}</title>
@endsection

@section('head-scripts')
<style>
    .technical-request-schedule-highlight {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0.85rem 1rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #e8f7fb 0%, #d7f0f8 100%);
        border: 1px solid #9dd7e6;
        color: #0f5f77;
        font-weight: 700;
        box-shadow: 0 10px 22px rgba(15, 95, 119, 0.12);
    }

    .technical-request-schedule-highlight i {
        font-size: 1.05rem;
    }

    .technical-request-resolution-highlight {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0.85rem 1rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #e8f8ec 0%, #d8f2df 100%);
        border: 1px solid #9fd6af;
        color: #17663a;
        font-weight: 700;
        box-shadow: 0 10px 22px rgba(23, 102, 58, 0.12);
    }

    .technical-request-resolution-highlight i {
        font-size: 1.05rem;
    }
</style>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col-xl-10">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
                    <div>
                        <h5 class="card-title mb-1">{{ __('Detalhes do Pedido de Assistência Técnica') }}</h5>
                        <p class="text-muted mb-0">{{ __('Resumo completo do pedido para consulta rápida.') }}</p>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        <a href="{{ route('backoffice.technical_requests.edit', ['id' => $request->id]) }}" class="btn btn-outline-primary mr-2">
                            <i class="fa fa-edit"></i> {{ __('Editar') }}
                        </a>
                        <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                        </a>
                    </div>
                </div>

                @php
                    $tipos = [
                        'software' => 'Software',
                        'reparacao' => 'Assistência/Reparação',
                        'manutencao' => 'Manutenção',
                        'pre_visita' => 'Pré-Visita',
                    ];
                    $hasEdition = $request->updated_at && $request->created_at && $request->updated_at->ne($request->created_at);
                @endphp

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="mb-2"><strong>{{ __('ID') }}:</strong> #{{ $request->id }}</div>
                            <div class="mb-2"><strong>{{ __('Loja') }}:</strong> {{ $request->store->codigo_loja ?? '-' }} - {{ $request->store->nome_loja ?? '-' }}</div>
                            <div class="mb-2"><strong>{{ __('Número de Série') }}:</strong> {{ $request->machine->serial_number ?? '—' }}</div>
                            <div class="mb-2"><strong>{{ __('Origem') }}:</strong> {{ $request->origem ?: '—' }}</div>
                            <div class="mb-2"><strong>{{ $request->assignedPersonTypeLabel() }} {{ __('atribuído') }}:</strong> {{ $request->assignedPersonLabel() }}</div>
                            <div class="mb-2"><strong>{{ __('Criado por') }}:</strong> {{ $request->creator->name ?? $request->creator->email ?? '—' }}</div>
                            <div class="mb-2"><strong>{{ __('Criado em') }}:</strong> {{ $request->created_at ? \Carbon\Carbon::parse($request->created_at)->format('d/m/Y H:i') : '—' }}</div>
                            @if($hasEdition)
                                <div class="mb-2"><strong>{{ __('Última edição por') }}:</strong> {{ $request->editor->name ?? $request->editor->email ?? '—' }}</div>
                                <div><strong>{{ __('Editado em') }}:</strong> {{ $request->updated_at ? \Carbon\Carbon::parse($request->updated_at)->format('d/m/Y H:i') : '—' }}</div>
                            @endif
                            <div class="mt-2"><strong>{{ __('Tipo de Serviço') }}:</strong> {{ $tipos[$request->tipo_servico] ?? ucfirst($request->tipo_servico) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="mb-2">
                                <strong>{{ __('Prioridade') }}:</strong>
                                <span class="badge 
                                    @switch($request->prioridade)
                                        @case('baixa') bg-info @break
                                        @case('media') bg-warning text-dark @break
                                        @case('alta') bg-danger text-white @break
                                        @default bg-secondary
                                    @endswitch">
                                    {{ __(ucfirst($request->prioridade)) }}
                                </span>
                            </div>
                            <div class="mb-2">
                                <strong>{{ __('Estado') }}:</strong>
                                <span class="badge 
                                    @switch($request->estado)
                                        @case('agendado') bg-info text-dark @break
                                        @case('concluido') bg-success @break
                                        @case('cancelado') bg-danger @break
                                        @case('pendente') bg-warning @break
                                        @case('aguarda_peca') bg-danger text-white @break
                                        @default bg-light
                                    @endswitch">
                                    {{ __(ucfirst(str_replace('_', ' ', $request->estado))) }}
                                </span>
                            </div>
                            <div class="mb-2"><strong>{{ __('Data do Pedido') }}:</strong> {{ $request->data_pedido ? \Carbon\Carbon::parse($request->data_pedido)->format('d/m/Y') : '—' }}</div>
                            <div class="mb-2">
                                <strong>{{ __('Data de Agendamento') }}:</strong>
                                @if($request->data_agendamento)
                                    <div class="mt-2">
                                        <span class="technical-request-schedule-highlight">
                                            <i class="fa fa-calendar-alt"></i>
                                            {{ __('Data de Agendamento') }}:
                                            {{ \Carbon\Carbon::parse($request->data_agendamento)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @else
                                    —
                                @endif
                            </div>
                            <div>
                                <strong>{{ __('Data da Resolução') }}:</strong>
                                @if($request->data_resolucao)
                                    <div class="mt-2">
                                        <span class="technical-request-resolution-highlight">
                                            <i class="fa fa-check-circle"></i>
                                            {{ __('Data de Resolução') }}:
                                            {{ \Carbon\Carbon::parse($request->data_resolucao)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <strong>{{ __('Descrição') }}</strong>
                            <div class="mt-2 text-muted">{{ $request->descricao_problema ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <strong>{{ __('Observações') }}</strong>
                            <div class="mt-2 text-muted">{{ $request->observacoes ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

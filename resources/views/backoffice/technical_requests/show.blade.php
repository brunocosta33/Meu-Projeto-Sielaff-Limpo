@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Detalhes do Pedido') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('Detalhes do Pedido de Assistência Técnica') }}</h5>

                <div class="mb-3">
                    <strong>{{ __('ID') }}:</strong> {{ $request->id }}
                </div>
                <div class="mb-3">
                    <strong>{{ __('Loja') }}:</strong>
                    {{ $request->store->codigo_loja ?? '-' }} - {{ $request->store->nome_loja ?? '-' }}
                </div>
                <div class="mb-3">
                    <strong>{{ __('Origem') }}:</strong> {{ $request->origem }}
                </div>
                <div class="mb-3">
                    <strong>{{ __('Descrição') }}:</strong><br>
                    {{ $request->descricao_problema }}
                </div>
                <div class="mb-3">
                    <strong>{{ __('Prioridade') }}:</strong>
                    <span class="badge 
                        @switch($request->prioridade)
                            @case('baixa') bg-success @break
                            @case('media') bg-warning text-dark @break
                            @case('alta') bg-danger @break
                            @default bg-secondary
                        @endswitch">
                        {{ __(ucfirst($request->prioridade)) }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>{{ __('Estado') }}:</strong>
                    <span class="badge 
                        @switch($request->estado)
                            @case('agendado') bg-info text-dark @break
                            @case('concluído') bg-success @break
                            @case('cancelado') bg-danger @break
                            @case('pendente') bg-secondary @break
                            @default bg-light
                        @endswitch">
                        {{ __(ucfirst($request->estado)) }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>{{ __('Data') }}:</strong> {{ \Carbon\Carbon::parse($request->data)->format('d/m/Y') }}
                </div>
                <div class="mb-3">
                    <strong>{{ __('Observações') }}:</strong><br>
                    {{ $request->observacoes }}
                </div>

                <div class="mt-4">
                    <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Detalhes da Instalação') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('Detalhes da Instalação') }}</h5>

                <ul class="list-group mb-4">
                    <li class="list-group-item"><strong>{{ __('Loja') }}:</strong> {{ $installation->store->nome_loja ?? '—' }}</li>
                    <li class="list-group-item"><strong>{{ __('Equipa Técnica') }}:</strong> {{ $installation->team->nome ?? '—' }}</li>
                    <li class="list-group-item"><strong>{{ __('Data') }}:</strong> {{ \Carbon\Carbon::parse($installation->scheduled_date)->format('d/m/Y') }}</li>
                    <li class="list-group-item"><strong>{{ __('Hora') }}:</strong> {{ $installation->scheduled_time }}</li>
                    <li class="list-group-item"><strong>{{ __('Tipo de Serviço') }}:</strong> {{ __($installation->tipo_servico) }}</li>
                    <li class="list-group-item"><strong>{{ __('Estado') }}:</strong> {{ __($installation->status) }}</li>
                    <li class="list-group-item"><strong>{{ __('Observações') }}:</strong> {{ $installation->observacoes ?? '—' }}</li>
                </ul>

                <div class="d-flex gap-2">
                    <a href="{{ route('backoffice.installations.index', ['page' => request('page')]) }}" class="btn btn-secondary">
                        ← {{ __('Voltar para a lista') }}
                    </a>

                    <a href="{{ route('backoffice.installations.edit', ['installation' => $installation->id, 'page' => request('page')]) }}" class="btn btn-primary">
                        ✎ {{ __('Editar Instalação') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

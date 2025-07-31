
@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Detalhes da Instalação</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Detalhes da Instalação</h5>
                <ul class="list-group">
                    <li class="list-group-item"><strong>Loja:</strong> {{ $installation->store->nome_loja ?? '-' }}</li>
                    <li class="list-group-item"><strong>Equipa:</strong> {{ $installation->team->nome ?? '-' }}</li>
                    <li class="list-group-item"><strong>Data:</strong> {{ \Carbon\Carbon::parse($installation->scheduled_date)->format('d/m/Y') }}</li>
                    <li class="list-group-item"><strong>Hora:</strong> {{ $installation->scheduled_time }}</li>
                    <li class="list-group-item"><strong>Tipo de Serviço:</strong> {{ $installation->service_type }}</li>
                    <li class="list-group-item"><strong>Estado:</strong> {{ $installation->status }}</li>
                    <li class="list-group-item"><strong>Observações:</strong> {{ $installation->notes }}</li>
                </ul>
                <a href="{{ route('backoffice.installations.index') }}" class="btn btn-secondary mt-3">Voltar</a>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Detalhes do Agendamento Técnico</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Detalhes do Agendamento Técnico</h5>

                <dl class="row">
                    <dt class="col-sm-3">Pedido Nº</dt>
                    <dd class="col-sm-9">#{{ $schedule->technical_request_id }}</dd>

                    <dt class="col-sm-3">Data</dt>
                    <dd class="col-sm-9">{{ \Carbon\Carbon::parse($schedule->data)->format('d/m/Y') }}</dd>

                    <dt class="col-sm-3">Hora</dt>
                    <dd class="col-sm-9">{{ $schedule->hora }}</dd>

                    <dt class="col-sm-3">Observações</dt>
                    <dd class="col-sm-9">{{ $schedule->observacoes ?? '-' }}</dd>

                    <dt class="col-sm-3">Criado em</dt>
                    <dd class="col-sm-9">{{ $schedule->created_at->format('d/m/Y H:i') }}</dd>
                </dl>

                <div class="mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.technical-schedules.index') }}">
                        <i class="fa fa-arrow-left"></i> Voltar</a>
                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.technical-schedules.edit', $schedule->id) }}">
                        <i class="fa fa-edit"></i> Editar</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

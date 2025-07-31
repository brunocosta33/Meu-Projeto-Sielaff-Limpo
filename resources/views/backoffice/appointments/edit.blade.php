@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Editar Agendamento</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Editar Agendamento</h5>

                {!! Form::model($appointment, ['route' => ['backoffice.appointments.update', $appointment->id], 'method' => 'POST']) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>Loja</label>
                    <select name="store_id" class="form-control" required>
                        <option value="">-- Selecione a Loja --</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $appointment->store_id == $store->id ? 'selected' : '' }}>
                                {{ $store->nome_loja }} ({{ $store->codigo_loja }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Transportadora</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">-- Selecione a Transportadora --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ $appointment->supplier_id == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Hora</label>
                    <input type="time" name="scheduled_time" class="form-control"
                        value="{{ \Carbon\Carbon::parse($appointment->scheduled_time)->format('H:i') }}" required>
                </div>


                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="scheduled_date" class="form-control"
                        value="{{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('Y-m-d') }}" required>
                </div>

            
                <div class="form-group">
                    <label>Tipo de Serviço</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="Entrega" {{ $appointment->tipo_servico == 'Entrega' ? 'selected' : '' }}>Entrega</option>
                        <option value="Levantamento" {{ $appointment->tipo_servico == 'Levantamento' ? 'selected' : '' }}>Levantamento</option>
                        <option value="Outro" {{ $appointment->tipo_servico == 'Outro' ? 'selected' : '' }}>Outro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="status" class="form-control" required>
                        <option value="Agendado" {{ $appointment->status == 'Agendado' ? 'selected' : '' }}>Agendado</option>
                        <option value="Concluído" {{ $appointment->status == 'Concluído' ? 'selected' : '' }}>Concluído</option>
                        <option value="Cancelado" {{ $appointment->status == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3">{{ $appointment->observacoes }}</textarea>
                </div>

                <div class="mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.appointments.index') }}">
                        <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                    {!! Form::button('<i class="fa fa-save"></i> Atualizar', ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

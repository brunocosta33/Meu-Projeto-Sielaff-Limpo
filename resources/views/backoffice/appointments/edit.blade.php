@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Agendamento') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('Editar Agendamento') }}</h5>

                {!! Form::model($appointment, ['route' => ['backoffice.appointments.update', $appointment->id], 'method' => 'POST']) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>{{ __('Loja') }}</label>
                    <select name="store_id" class="form-control" required>
                        <option value="">{{ __('-- Selecione a Loja --') }}</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $appointment->store_id == $store->id ? 'selected' : '' }}>
                                {{ $store->nome_loja }} ({{ $store->codigo_loja }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Transportadora') }}</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">{{ __('-- Selecione a Transportadora --') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ $appointment->supplier_id == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <label for="scheduled_time" style="font-size: 0.95em; margin-bottom: 2px;">{{ __('Hora') }}</label>
                <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" style="max-width: 120px; font-size: 0.95em; padding: 2px 8px; margin-bottom: 10px;" value="{{ \Carbon\Carbon::parse($appointment->scheduled_time)->format('H:i') }}" required>

                <label for="scheduled_date" style="font-size: 0.95em; margin-bottom: 2px;">{{ __('Data') }}</label>
                <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" style="max-width: 180px; font-size: 0.95em; padding: 2px 8px; margin-bottom: 10px;" value="{{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('Y-m-d') }}" required min="{{ date('Y-m-d') }}" onkeydown="return false;">

                <div class="form-group">
                    <label>{{ __('Tipo de Serviço') }}</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="Entrega" {{ $appointment->tipo_servico == 'Entrega' ? 'selected' : '' }}>{{ __('Entrega') }}</option>
                        <option value="Levantamento" {{ $appointment->tipo_servico == 'Levantamento' ? 'selected' : '' }}>{{ __('Levantamento') }}</option>
                        <option value="Outro" {{ $appointment->tipo_servico == 'Outro' ? 'selected' : '' }}>{{ __('Outro') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Estado') }}</label>
                    <select name="status" class="form-control" required>
                        <option value="Agendado" {{ $appointment->status == 'Agendado' ? 'selected' : '' }}>{{ __('Agendado') }}</option>
                        <option value="Concluído" {{ $appointment->status == 'Concluído' ? 'selected' : '' }}>{{ __('Concluído') }}</option>
                        <option value="Cancelado" {{ $appointment->status == 'Cancelado' ? 'selected' : '' }}>{{ __('Cancelado') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="3">{{ $appointment->observacoes }}</textarea>
                </div>

                <div class="mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.appointments.index', ['page' => request('page')]) }}">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                    <input type="hidden" name="page" value="{{ request('page') }}">
                    {!! Form::button('<i class="fa fa-save"></i> ' . __('Atualizar'), ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.querySelector('input[type="date"][name="scheduled_date"]');
            if (dateInput) {
                dateInput.addEventListener('keydown', function(e) {
                    e.preventDefault();
                });
            }
        });
    </script>
@endsection

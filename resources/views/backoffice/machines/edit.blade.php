@extends('layouts.backoffice_master')
@section('content')
<h4>{{ __('Editar Máquina') }}</h4>
<form method="POST" action="{{ route('backoffice.machines.update', $machine->id) }}">
    @csrf
    <div class="form-group">
        <label>{{ __('Modelo') }}</label>
        <input type="text" name="modelo" class="form-control" value="{{ $machine->modelo }}" required>
    </div>
    <div class="form-group">
        <label>{{ __('Nº de Série') }}</label>
        <input type="text" name="numero_serie" class="form-control" value="{{ $machine->numero_serie }}" required>
    </div>
    <div class="form-group">
        <label>{{ __('Data de Recebimento') }}</label>
        <input type="date" name="data_recebimento" class="form-control" value="{{ $machine->data_recebimento }}" required>
    </div>
    <div class="form-group">
        <label>{{ __('Observações') }}</label>
        <textarea name="observacoes" class="form-control">{{ $machine->observacoes }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">{{ __('Atualizar') }}</button>
</form>
@endsection
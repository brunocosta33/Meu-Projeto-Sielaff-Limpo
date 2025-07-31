@extends('layouts.backoffice_master')
@section('content')
<h4>Nova Máquina</h4>
<form method="POST" action="{{ route('backoffice.machines.store') }}">
    @csrf
    <div class="form-group">
        <label>Modelo</label>
        <input type="text" name="modelo" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Número de Série</label>
        <input type="text" name="numero_serie" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Data de Recebimento</label>
        <input type="date" name="data_recebimento" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Observações</label>
        <textarea name="observacoes" class="form-control"></textarea>
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
</form>
@endsection
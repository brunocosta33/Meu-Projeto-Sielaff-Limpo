@extends('layouts.backoffice_master')

@section('content')
<h4>Detalhes da Máquina</h4>

<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <td>{{ $machine->id }}</td>
    </tr>
    <tr>
        <th>Modelo</th>
        <td>{{ $machine->modelo }}</td>
    </tr>
    <tr>
        <th>Número de Série</th>
        <td>{{ $machine->numero_serie }}</td>
    </tr>
    <tr>
        <th>Data de Recebimento</th>
        <td>{{ \Carbon\Carbon::parse($machine->data_recebimento)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <th>Observações</th>
        <td>{{ $machine->observacoes }}</td>
    </tr>
</table>

<a href="{{ route('backoffice.machines.index') }}" class="btn btn-outline-secondary">
    <i class="fa fa-arrow-left"></i> Voltar
</a>
<a href="{{ route('backoffice.machines.edit', $machine->id) }}" class="btn btn-outline-primary">
    <i class="fa fa-edit"></i> Editar
</a>
@endsection

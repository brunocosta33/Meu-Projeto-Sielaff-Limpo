@extends('layouts.backoffice_master')

@section('content')
<h4>Detalhes da Peça</h4>

<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <td>{{ $part->id }}</td>
    </tr>
    <tr>
        <th>Nome</th>
        <td>{{ $part->nome }}</td>
    </tr>
    <tr>
        <th>Referência</th>
        <td>{{ $part->referencia }}</td>
    </tr>
    <tr>
        <th>Descrição</th>
        <td>{{ $part->descricao }}</td>
    </tr>
</table>

<a href="{{ route('backoffice.parts.index') }}" class="btn btn-outline-secondary">
    <i class="fa fa-arrow-left"></i> Voltar
</a>
<a href="{{ route('backoffice.parts.edit', $part->id) }}" class="btn btn-outline-primary">
    <i class="fa fa-edit"></i> Editar
</a>
@endsection

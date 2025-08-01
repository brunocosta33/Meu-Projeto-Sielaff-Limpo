
@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h3>Lista de Técnicos</h3>
    <a href="{{ route('backoffice.technicians.create') }}" class="btn btn-success mb-3">Novo Técnico</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($technicians as $technician)
                <tr>
                    <td>{{ $technician->nome }}</td>
                    <td>{{ $technician->telefone }}</td>
                    <td>{{ $technician->email }}</td>
                    <td>
                        <a href="{{ route('backoffice.technicians.edit', $technician->id) }}" class="btn btn-sm btn-primary">Editar</a>
                        <form action="{{ route('backoffice.technicians.destroy', $technician->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem a certeza que deseja remover este técnico?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
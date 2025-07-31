@extends('layouts.backoffice_master')
@section('content')
<div class="container">
    <h2>Tarefas</h2>
    <a href="{{ route('backoffice.tasks.create') }}" class="btn btn-success">Nova Tarefa</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Título</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
            <tr>
                <td>{{ $task->titulo }}</td>
                <td>{{ $task->descricao }}</td>
                <td>
                    <a href="{{ route('backoffice.tasks.edit', $task) }}" class="btn btn-primary btn-sm">Editar</a>
                    <form action="{{ route('backoffice.tasks.destroy', $task) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem a certeza que quer apagar?')">Apagar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
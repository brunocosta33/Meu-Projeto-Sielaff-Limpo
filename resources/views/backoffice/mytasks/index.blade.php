@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h2 class="mb-4">Minhas Tarefas</h2>

    <!-- Botões de filtro -->
    <div class="mb-4">
        <a href="{{ route('backoffice.mytasks.index') }}" class="btn {{ request('estado') != 'por_concluir' ? 'btn-dark' : 'btn-outline-dark' }}">
            TODAS
        </a>
        <a href="{{ route('backoffice.mytasks.index', ['estado' => 'por_concluir']) }}" class="btn {{ request('estado') == 'por_concluir' ? 'btn-dark' : 'btn-outline-dark' }}">
            POR CONCLUIR
        </a>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Data Limite</th>
                <th>Hora Limite</th>
                <th>Prioridade</th>
                <th>Activa</th>
                <th>Grupo</th>
                <th>Tarefa</th>
                <th>Estado</th>
                <th>Responsável</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tasks as $task)
                <tr>
                    <td class="{{ $task->prioridade == 'Alta' ? 'text-danger fw-bold' : '' }}">
                        {{ \Carbon\Carbon::parse($task->data_limite)->format('d/m/Y') }}
                    </td>
                    <td class="{{ $task->prioridade == 'Alta' ? 'text-danger fw-bold' : '' }}">
                        {{ \Carbon\Carbon::parse($task->hora_limite)->format('H:i') }}
                    </td>
                    <td class="{{ $task->prioridade == 'Alta' ? 'text-danger' : 'text-success' }}">
                        {{ $task->prioridade }}
                    </td>
                    <td>{{ $task->ativa ? 'Sim' : 'Não' }}</td>
                    <td>{{ $task->grupo ? 'Sim' : 'Não' }}</td>
                    <td>{{ $task->titulo }}</td>
                    <td>{{ $task->concluida ? 'Concluída' : 'Pendente' }}</td>
                    <td>{{ $task->user->name ?? '-' }}</td>

                    <td>
                        <a href="{{ route('backoffice.mytasks.show', $task) }}" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">Sem tarefas atribuídas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h2>Agendamentos de Tarefas</h2>

    <a href="{{ route('backoffice.task_schedules.create') }}" class="btn btn-success mb-3">Novo Agendamento</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Data Limite</th>
                <th>Hora</th>
                <th>Prioridade</th>
                <th>Activa</th>
                <th>Grupo</th>
                <th>Repetição</th>
                <th>Tarefa</th>
                <th>Utilizadores</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $schedule)
                <tr>
                    <td>{{ $schedule->deadline_date }}</td>
                    <td>{{ \Carbon\Carbon::parse($schedule->deadline_time)->format('H:i') }}</td>
                    <td>{{ $schedule->priority }}</td>
                    <td>{{ $schedule->is_active ? 'Sim' : 'Não' }}</td>
                    <td>{{ $schedule->is_group_task ? 'Sim' : 'Não' }}</td>
                    <td>{{ $schedule->is_repeating ? 'Sim' : 'Não' }}</td>
                    <td>{{ $schedule->task->titulo ?? '-' }}</td>
                    <td>
                        @foreach ($schedule->users as $user)
                            <span class="badge bg-info">{{ $user->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('backoffice.task_schedules.edit', $schedule->id) }}" class="btn btn-sm btn-primary">Editar</a>
                        <form action="{{ route('backoffice.task_schedules.destroy', $schedule->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem a certeza que pretende apagar?')">Apagar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

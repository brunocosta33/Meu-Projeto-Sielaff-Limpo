@extends('layouts.backoffice_master')

@section('content')

<div class="container-fluid">
    <div class="bg-white d-flex justify-content-between align-items-center flex-wrap mb-4 p-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="d-flex justify-content-center align-items-center bg-light rounded-3 me-3" style="width: 48px; height: 48px;">
                <i class="fas fa-user-check fa-lg text-dark"></i> {{-- ícone de tarefas atribuídas --}}
            </div>
            <h1 class="h4 mb-0">Minhas Tarefas</h1>
        </div>
    </div>

    <div class="bg-white p-3 shadow-sm rounded">
        @if($taskSchedules->isEmpty())
            <p class="text-muted text-center">Nenhuma tarefa agendada encontrada.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tarefa</th>
                            <th>Descrição</th>
                            <th>Data Limite</th>
                            <th>Hora Limite</th>
                            <th>Prioridade</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($taskSchedules as $schedule)
                            <tr style="background-color: #e5e5e5; border-bottom: 8px solid #fff;">
                                <td>{{ $schedule->task->title ?? '-' }}</td>
                                <td>{{ $schedule->task->description ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($schedule->data_limite)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($schedule->hora_limite)->format('H:i') }}</td>
                                <td>
                                    <span class="badge 
                                        @if($schedule->prioridade == 'Alta') bg-danger 
                                        @elseif($schedule->prioridade == 'Média') bg-warning 
                                        @else bg-secondary @endif">
                                        {{ $schedule->prioridade }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($schedule->estado == 'Concluída') bg-success 
                                        @elseif($schedule->estado == 'Visualizada') bg-info 
                                        @else bg-light text-dark @endif">
                                        {{ $schedule->estado }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

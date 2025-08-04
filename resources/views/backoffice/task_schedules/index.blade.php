@extends('layouts.backoffice_master')

@section('content')

<div class="container-fluid">
    <div class="bg-white d-flex justify-content-between align-items-center p-3 border-bottom mb-4">
        <div class="d-flex align-items-center">
            <div class="bg-light rounded-3 d-flex justify-content-center align-items-center me-3" style="width: 48px; height: 48px;">
                <i class="fas fa-calendar-check fa-lg text-dark"></i>
            </div>
            <h1 class="h4 fw-bold mb-0">Agendamentos de Tarefas</h1>
        </div>
        <a href="{{ route('backoffice.task_schedules.create') }}" class="btn btn-primary rounded-pill px-4">
            Criar
        </a>
    </div>

    <div class="bg-white p-3 shadow-sm rounded">
        <form method="GET" class="mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group" style="width: 260px;">
                    <span class="input-group-text bg-white border-0 pe-0" style="border-radius: 24px 0 0 24px; height: 36px;">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="pesquisa" value="{{ request('pesquisa') }}" class="form-control border-0 bg-light" placeholder="Pesquisa por tarefa" style="border-radius: 0 24px 24px 0; font-size: 1em; height: 36px;">
                </div>
            </div>
            <div class="mt-3 d-flex gap-2 align-items-center" style="max-width: 400px;">
                <select name="prioridade" id="prioridade" class="form-select bg-light border-0" style="border-radius: 24px; font-size: 1em; width: 140px; height: 36px; padding: 0 8px;" onchange="this.form.submit()">
                    <option value="" {{ request('prioridade') == '' ? 'selected' : '' }}>Todas</option>
                    <option value="Alta" {{ request('prioridade') == 'Alta' ? 'selected' : '' }}>Alta</option>
                    <option value="Média" {{ request('prioridade') == 'Média' ? 'selected' : '' }}>Média</option>
                    <option value="Baixa" {{ request('prioridade') == 'Baixa' ? 'selected' : '' }}>Baixa</option>
                </select>
                <a href="{{ route('backoffice.task_schedules.index') }}" class="btn btn-dark rounded-pill px-3 ms-1" style="height: 36px; font-size: 1em;">LIMPAR FILTROS</a>
            </div>
            
            <!-- Botão LIMPAR FILTROS movido para ao lado do select de prioridades -->
            </div>
        </form>
        <div class="mt-4 mb-3 d-flex justify-content-center">
            <div class="btn-group" role="group" aria-label="Filtro Estado">
                <a href="?prioridade={{ request('prioridade') }}&pesquisa={{ request('pesquisa') }}&estado=" class="btn btn-outline-dark rounded-pill px-4 {{ request('estado') == '' ? 'active' : '' }}">Todas</a>
                <a href="?prioridade={{ request('prioridade') }}&pesquisa={{ request('pesquisa') }}&estado=Concluir" class="btn btn-outline-dark rounded-pill px-4 {{ request('estado') == 'Concluir' ? 'active' : '' }}">Concluir</a>
            </div>
        </div>
        <table class="table table-hover align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>Tarefa</th>
                    <th>Data Limite</th>
                    <th>Hora Limite</th>
                    <th>Prioridade</th>
                    <th>Estado</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $prioridadeFiltro = request('prioridade');
                    $estadoFiltro = request('estado');
                    $pesquisa = request('pesquisa');
                    $schedulesFiltrados = $schedules;
                    if ($prioridadeFiltro) {
                        $schedulesFiltrados = $schedulesFiltrados->where('prioridade', $prioridadeFiltro);
                    }
                    if ($estadoFiltro) {
                        $schedulesFiltrados = $schedulesFiltrados->where('estado', $estadoFiltro);
                    }
                    if ($pesquisa) {
                        $schedulesFiltrados = $schedulesFiltrados->filter(function($s) use ($pesquisa) {
                            return stripos($s->task->title, $pesquisa) !== false;
                        });
                    }
                @endphp
               @forelse($schedulesFiltrados as $schedule)
                <tr style="background-color: #f6f6f6;">
                    <td class="border-0">{{ $schedule->task->title }}</td>
                    <td class="border-0">{{ \Carbon\Carbon::parse($schedule->data_limite)->format('d/m/Y') }}</td>
                    <td class="border-0">{{ $schedule->hora_limite ? \Carbon\Carbon::parse($schedule->hora_limite)->format('H:i') : '-' }}</td>
                    <td class="border-0">{{ $schedule->prioridade }}</td>
                    <td class="border-0">{{ $schedule->estado }}</td>
                    <td class="text-end border-0">
                        <a href="{{ route('backoffice.task_schedules.show', $schedule->id) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('backoffice.task_schedules.edit', $schedule->id) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Editar">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Sem agendamentos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

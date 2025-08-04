@extends('layouts.backoffice_master')

@section('content')
<main role="main" class="main-content-wrapper">
    <div class="bg-white d-flex align-items-center gap-4 mb-4 page-main-title">
        <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <i class="fas fa-user-check fa-lg"></i>
        </div>
        <h1 class="mb-0">Minhas Tarefas</h1>
    </div>

    <div class="bg-white p-3">
        <div class="d-flex justify-content-center mb-4" style="background: #ededed; padding: 32px 0 16px 0;">
            <form method="GET" class="d-flex" style="width: 480px;">
                <input type="hidden" name="filtro" id="filtroInput" value="{{ request('filtro', 'todas') }}">
                <button type="submit" class="btn" style="background: {{ request('filtro', 'todas') == 'todas' ? '#232529' : '#ededed' }}; color: {{ request('filtro', 'todas') == 'todas' ? '#fff' : '#232529' }}; border-radius: 32px 0 0 32px; border: 2px solid #232529; width: 50%; font-weight: bold; font-size: 1.3em;" onclick="document.getElementById('filtroInput').value='todas'">TODAS</button>
                <button type="submit" class="btn" style="background: {{ request('filtro') == 'por_concluir' ? '#232529' : '#ededed' }}; color: {{ request('filtro') == 'por_concluir' ? '#fff' : '#232529' }}; border-radius: 0 32px 32px 0; border: 2px solid #232529; border-left: none; width: 50%; font-weight: bold; font-size: 1.3em;" onclick="document.getElementById('filtroInput').value='por_concluir'">POR CONCLUIR</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tarefa</th>
                        <th>Prioridade</th>
                        <th style="text-align: center;">Data Limite</th>
                        <th style="text-align: center;">Hora Limite</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $filtro = request('filtro', 'todas');
                        $tarefas = collect($taskSchedules);
                        if ($filtro === 'por_concluir') {
                            $tarefas = $tarefas->filter(function($t) {
                                return ($t->pivot->estado ?? 'Pendente') !== 'Concluída';
                            });
                        } else {
                            $tarefas = $tarefas->sortByDesc(function($t) {
                                return ($t->pivot->estado ?? 'Pendente') === 'Concluída' ? 1 : 0;
                            });
                        }
                    @endphp
                    @forelse($tarefas as $schedule)
                        <tr style="background-color: #f2f2f2">
                            <td>{{ $schedule->task->title }}</td>
                            <td>{{ $schedule->prioridade }}</td>
                            <td style="text-align: center;">{{ $schedule->data_limite ? \Carbon\Carbon::parse($schedule->data_limite)->format('d/m/Y') : '-' }}</td>
                            <td style="text-align: center;">{{ $schedule->hora_limite ? \Carbon\Carbon::parse($schedule->hora_limite)->format('H:i') : '-' }}</td>
                            <td style="text-align: center;">{{ $schedule->pivot->estado ?? 'Pendente' }}</td>
                            <td style="text-align: center;">
                                @php
                                    $isConcluida = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $schedule->pivot->estado ?? '')) === strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', 'Concluída'));
                                @endphp
                                @if($isConcluida)
                                    <a href="{{ route('backoffice.task_schedules.minhas.show', $schedule->id) }}" class="btn btn-sm btn-outline-success rounded-circle" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <a href="{{ route('backoffice.task_schedules.minhas.show', $schedule->id) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Nenhuma tarefa encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <a class="btn btn-secondary mt-3" href="{{ route('backoffice.task_schedules.index') }}">Voltar</a>
    </div>
</main>
@endsection

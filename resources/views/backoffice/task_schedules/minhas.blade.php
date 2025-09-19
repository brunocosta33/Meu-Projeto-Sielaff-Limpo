@extends('layouts.backoffice_master')

@section('content')
<main role="main" class="main-content-wrapper">
    <div class="bg-white d-flex align-items-center gap-4 mb-4 page-main-title">
        <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center icon-48">
            <i class="fas fa-user-check fa-lg"></i>
        </div>
        <h1 class="mb-0">{{ __('Minhas Tarefas') }}</h1>
    </div>

    <div class="bg-white p-3">
        <div class="d-flex justify-content-center mb-4 task-filters">
            <form method="GET" class="d-flex task-toggle">
                @php $filtroAtual = request('filtro','todas'); @endphp

                <button type="submit"
                        name="filtro" value="todas"
                        class="btn btn-sm btn-outline-dark rounded-pill btn-left {{ $filtroAtual==='todas' ? 'active' : '' }}">
                    {{ __('TODAS') }}
                </button>

                <button type="submit"
                        name="filtro" value="por_concluir"
                        class="btn btn-sm btn-outline-dark rounded-pill btn-right {{ $filtroAtual==='por_concluir' ? 'active' : '' }}">
                    {{ __('POR CONCLUIR') }}
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr class="text-center">
                        <th>{{ __('Data Limite') }}</th>
                        <th>{{ __('Hora Limite') }}</th>
                        <th>{{ __('Prioridade') }}</th>
                        <th>{{ __('Activa') }}</th>
                        <th>{{ __('Grupo') }}</th>
                        <th>{{ __('Tarefa') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th>{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $tarefas = collect($taskSchedules)
                            ->filter(fn($t) => $t->activa)
                            ->values();
                        if ($filtroAtual === 'por_concluir') {
                            $tarefas = $tarefas->filter(fn($t) => ($t->pivot->estado ?? 'Pendente') !== 'Concluída');
                        } else {
                            $tarefas = $tarefas->sortByDesc(fn($t) => ($t->pivot->estado ?? 'Pendente') === 'Concluída' ? 1 : 0);
                        }
                    @endphp

                    @forelse($tarefas as $schedule)
                        <tr class="align-middle" style="background: linear-gradient(90deg, #f8fafc 60%, #e5e5e5 100%); box-shadow: 0 2px 8px rgba(0,0,0,0.04); border-radius: 12px;">
                            <td class="text-center">
                                <span class="badge bg-gradient text-danger px-3 py-2" style="background: linear-gradient(90deg, #ffe0e0 0%, #fff 100%); border-radius: 12px;">{{ $schedule->data_limite ? \Carbon\Carbon::parse($schedule->data_limite)->format('d/m/Y') : '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-gradient text-danger px-3 py-2" style="background: linear-gradient(90deg, #ffe0e0 0%, #fff 100%); border-radius: 12px;">{{ $schedule->hora_limite ? \Carbon\Carbon::parse($schedule->hora_limite)->format('H:i') : '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if(strtolower($schedule->prioridade) === 'baixa')
                                    <span class="badge px-3 py-2" style="background: #e6f4ea; color: #256029; font-weight: 500;">{{ $schedule->prioridade }}</span>
                                @elseif(strtolower($schedule->prioridade) === 'alta')
                                    <span class="badge px-3 py-2" style="background: #fdeaea; color: #a4262c; font-weight: 500;">{{ $schedule->prioridade }}</span>
                                @else
                                    <span class="badge px-3 py-2" style="background: #f3f3f3; color: #333; font-weight: 500;">{{ $schedule->prioridade }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $schedule->activa ? __('Sim') : __('Não') }}</td>
                            <td class="text-center">{{ $schedule->grupo ? __('Sim') : __('Não') }}</td>
                            <td class="text-center">
                                <span class="badge bg-gradient text-dark px-3 py-2" style="background: linear-gradient(90deg, #e0eafc 0%, #cfdef3 100%); border-radius: 12px; font-weight: 600; letter-spacing: 0.5px;">
                                    <i class="fas fa-tasks me-2 text-primary"></i>{{ $schedule->task->title }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $isConcluida = strtolower(iconv('UTF-8','ASCII//TRANSLIT', $schedule->pivot->estado ?? ''))
                                                 === strtolower(iconv('UTF-8','ASCII//TRANSLIT', 'Concluída'));
                                @endphp
                                @php
                                    $estadoBg = $isConcluida ? '#e6f4ea' : '#fffbe6';
                                    $estadoColor = $isConcluida ? '#256029' : '#a67c00';
                                @endphp
                                <span class="badge px-3 py-2" {!! 'style="background: '.$estadoBg.'; color: '.$estadoColor.'; font-weight: 500;"' !!}>
                                    {{ $schedule->pivot->estado ?? 'Pendente' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($isConcluida)
                                    <a href="{{ route('backoffice.task_schedules.minhas.show', $schedule->id) }}"
                                       class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm" title="{{ __('Visualizar') }}">
                                        <i class="fas fa-eye"></i> Visualizar
                                    </a>
                                @else
                                    <a href="{{ route('backoffice.task_schedules.minhas.show', $schedule->id) }}"
                                       class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" title="{{ __('Editar') }}">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('Nenhuma tarefa encontrada.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection

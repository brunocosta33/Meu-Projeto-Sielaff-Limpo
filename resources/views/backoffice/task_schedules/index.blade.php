@extends('layouts.backoffice_master')

@section('content')

<div class="container-fluid">
    <div class="bg-white d-flex justify-content-between align-items-center p-3 border-bottom mb-4">
        <div class="d-flex align-items-center">
            <div class="bg-light rounded-3 d-flex justify-content-center align-items-center me-3" style="width: 48px; height: 48px;">
                <i class="fas fa-calendar-check fa-lg text-dark"></i>
            </div>
            <h1 class="h4 fw-bold mb-0">{{ __('Agendamentos de Tarefas') }}</h1>
        </div>
        <a href="{{ route('backoffice.task_schedules.create') }}" class="btn btn-primary rounded-pill px-4">
            {{ __('Criar') }}
        </a>
    </div>

    <div class="bg-white p-3 shadow-sm rounded">
        <form method="GET" class="mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group" style="width: 260px;">
                    <span class="input-group-text bg-white border-0 pe-0" style="border-radius: 24px 0 0 24px; height: 36px;">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="pesquisa" value="{{ request('pesquisa') }}" class="form-control border-0 bg-light" placeholder="{{ __('Pesquisa por tarefa') }}" style="border-radius: 0 24px 24px 0; font-size: 1em; height: 36px;">
                </div>
            </div>
            <div class="mt-3 d-flex gap-2 align-items-center" style="max-width: 400px;">
                <select name="prioridade" id="prioridade" class="form-select bg-light border-0" style="border-radius: 24px; font-size: 1em; width: 140px; height: 36px; padding: 0 8px;" onchange="this.form.submit()">
                    <option value="" {{ request('prioridade') == '' ? 'selected' : '' }}>{{ __('Todas') }}</option>
                    <option value="Alta" {{ request('prioridade') == 'Alta' ? 'selected' : '' }}>{{ __('Alta') }}</option>
                    <option value="Média" {{ request('prioridade') == 'Média' ? 'selected' : '' }}>{{ __('Média') }}</option>
                    <option value="Baixa" {{ request('prioridade') == 'Baixa' ? 'selected' : '' }}>{{ __('Baixa') }}</option>
                </select>
                <a href="{{ route('backoffice.task_schedules.index') }}" class="btn btn-dark rounded-pill px-3 ms-1" style="height: 36px; font-size: 1em;">{{ __('LIMPAR FILTROS') }}</a>
            </div>
            <!-- mantém só prioridade/pesquisa aqui -->
        </form>

        <div class="mt-4 mb-3 d-flex justify-content-center">
            <form method="GET" class="d-flex task-toggle">
                {{-- preserva filtros atuais --}}
                <input type="hidden" name="prioridade" value="{{ request('prioridade') }}">
                <input type="hidden" name="pesquisa" value="{{ request('pesquisa') }}">

                @php $estadoAtual = request('estado'); @endphp

                <button type="submit"
                        name="estado" value=""
                        class="btn btn-sm btn-outline-dark rounded-pill btn-left {{ $estadoAtual !== 'por_concluir' ? 'active' : '' }}">
                {{ __('Todas') }}
                </button>

                <button type="submit"
                        name="estado" value="por_concluir"
                        class="btn btn-sm btn-outline-dark rounded-pill btn-right {{ $estadoAtual === 'por_concluir' ? 'active' : '' }}">
                {{ __('Por concluir') }}
                </button>
            </form>
        </div>

        <table class="table table-hover align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Data Limite') }}</th>
                    <th>{{ __('Hora Limite') }}</th>
                    <th>{{ __('Prioridade') }}</th>
                    <th>{{ __('Activa') }}</th>
                    <th style="text-align: center;">{{ __('Repetição') }}</th>
                    <th>{{ __('Tarefa') }}</th>
                    <th>{{ __('Estado') }}</th>
                    <th style="text-align: center;">{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $prioridadeFiltro = request('prioridade');
                    $pesquisa = request('pesquisa');
                    $estadoFiltro = request('estado'); // '', null, ou 'por_concluir'

                    $schedulesFiltrados = $schedules;

                    if ($prioridadeFiltro) {
                        $schedulesFiltrados = $schedulesFiltrados->where('prioridade', $prioridadeFiltro);
                    }

                    // Por concluir = tudo o que NÃO está Concluída (ou sem estado)
                    if ($estadoFiltro === 'por_concluir') {
                        $schedulesFiltrados = $schedulesFiltrados->filter(function($s) {
                            return mb_strtolower($s->estado ?? '') !== mb_strtolower('Concluída');
                        });
                    }

                    if ($pesquisa) {
                        $schedulesFiltrados = $schedulesFiltrados->filter(function($s) use ($pesquisa) {
                            return stripos($s->task->title, $pesquisa) !== false;
                        });
                    }
                @endphp

                @forelse($schedulesFiltrados as $schedule)
                    @php
                        $isConcluida = strtolower(iconv('UTF-8','ASCII//TRANSLIT', $schedule->estado ?? '')) === strtolower(iconv('UTF-8','ASCII//TRANSLIT', 'Concluída'));
                        $estadoBg = $isConcluida ? '#e6f4ea' : '#fff';
                    @endphp
                    <tr class="align-middle" {!! 'style="background: '.$estadoBg.'; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border-radius: 12px;"' !!}>
                        <td class="text-center">
                            <span class="badge bg-gradient text-danger px-3 py-2" style="background: linear-gradient(90deg, #ffe0e0 0%, #fff 100%); border-radius: 12px;">{{ \Carbon\Carbon::parse($schedule->data_limite)->format('d/m/Y') }}</span>
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
                        <td class="text-center">{{ $schedule->repetir ? __('Sim') : __('Não') }}</td>
                        <td class="text-center">
                            <span class="badge bg-gradient text-dark px-3 py-2" style="background: linear-gradient(90deg, #e0eafc 0%, #cfdef3 100%); border-radius: 12px; font-weight: 600; letter-spacing: 0.5px;">
                                <i class="fas fa-tasks me-2 text-primary"></i>{{ $schedule->task->title }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                // Estado real: se todos os users têm pivot.estado = Concluída, então concluída
                                $users = $schedule->users;
                                $totalUsers = $users->count();
                                $concluidas = $users->filter(function($u) { return strtolower(iconv('UTF-8','ASCII//TRANSLIT', $u->pivot->estado ?? '')) === strtolower(iconv('UTF-8','ASCII//TRANSLIT', 'Concluída')); })->count();
                                $isConcluida = $totalUsers > 0 && $concluidas === $totalUsers;
                                $estadoBg = $isConcluida ? '#e6f4ea' : '#fffbe6';
                                $estadoColor = $isConcluida ? '#256029' : '#a67c00';
                                $estadoLabel = $totalUsers === 0 ? ($schedule->estado ?? 'Pendente') : ($isConcluida ? 'Concluída' : ($concluidas > 0 ? "$concluidas/$totalUsers Concluídas" : ($users->first()->pivot->estado ?? 'Pendente')));
                            @endphp
                            <span class="badge px-3 py-2" {!! 'style="background: '.$estadoBg.'; color: '.$estadoColor.'; font-weight: 500;"' !!}>
                                @if($isConcluida)
                                    <i class="fas fa-check-circle me-1 text-success"></i>
                                @endif
                                <span style="font-weight: bold;">{{ $estadoLabel }}</span>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1 flex-wrap align-items-center">
                                <a href="{{ route('backoffice.task_schedules.show', $schedule->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1 shadow-sm" title="{{ __('Ver') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('backoffice.task_schedules.edit', $schedule->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1 shadow-sm" title="{{ __('Editar') }}">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('backoffice.task_schedules.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja apagar este agendamento?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1 shadow-sm" title="{{ __('Apagar') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- 8 colunas visíveis --}}
                    <tr>
                        <td colspan="8" class="text-center text-muted">{{ __('Sem agendamentos.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

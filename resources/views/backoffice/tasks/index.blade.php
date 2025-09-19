@extends('layouts.backoffice_master')

@section('content')

<div class="container-fluid">
    <div class="bg-white d-flex justify-content-between align-items-center flex-wrap mb-4 p-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="d-flex justify-content-center align-items-center bg-light rounded-3 me-3" style="width: 48px; height: 48px;">
                <i class="fas fa-tasks fa-lg text-dark"></i> {{-- ícone de tarefas --}}
            </div>
            <h1 class="h4 fw-bold mb-0">{{ __('Tarefas') }}</h1>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('backoffice.tasks.create') }}" class="btn btn-primary rounded-pill px-4">
                {{ __('Criar') }}
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger" id="alert" role="alert">
            {{ session('error') }}
        </div>
    @endif
    <div class="bg-white p-4 shadow rounded-4 border-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-primary fs-5" style="letter-spacing: 1px;">{{ __('Tarefa') }}</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr class="align-middle" style="background: linear-gradient(90deg, #f8fafc 60%, #e5e5e5 100%); box-shadow: 0 2px 8px rgba(0,0,0,0.04); border-radius: 12px;">
                            <td>
                                <span class="badge bg-gradient text-dark px-4 py-2 fs-6 shadow-sm" style="background: linear-gradient(90deg, #e0eafc 0%, #cfdef3 100%); border-radius: 16px; font-weight: 600; letter-spacing: 0.5px;">
                                    <i class="fas fa-tasks me-2 text-primary"></i>{{ $task->title }}
                                    @if($task->schedules && $task->schedules->count() > 0)
                                        <span class="badge ms-4 px-3 py-2" style="background: #ff9800; color: #fff; font-weight: bold; font-size: 1em; letter-spacing: 0.5px; vertical-align: middle; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                            <i class="fas fa-calendar-check me-1"></i> Agendada
                                        </span>
                                    @endif
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-3">
                                    <a href="{{ route('backoffice.tasks.edit', $task->id) }}"
                                       class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm"
                                       title="{{ __('Editar') }}">
                                        <i class="fas fa-pencil-alt"></i> Editar
                                    </a>
                                    <form method="POST"
                                          action="{{ route('backoffice.tasks.destroy', $task->id) }}"
                                          style="display:inline"
                                          onsubmit="return confirm('Tem a certeza que quer apagar esta tarefa?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm"
                                                title="{{ __('Apagar') }}">
                                            <i class="fas fa-trash-alt"></i> Apagar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4 fs-5">{{ __('Sem tarefas disponíveis.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
        @if(method_exists($tasks, 'links') && $tasks->hasPages())
        <div class="d-flex justify-content-end mt-3">
            {{ $tasks->links('vendor.pagination.bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

@endsection

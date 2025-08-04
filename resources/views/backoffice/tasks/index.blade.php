@extends('layouts.backoffice_master')

@section('content')

<div class="container-fluid">
    <div class="bg-white d-flex justify-content-between align-items-center flex-wrap mb-4 p-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="d-flex justify-content-center align-items-center bg-light rounded-3 me-3" style="width: 48px; height: 48px;">
                <i class="fas fa-tasks fa-lg text-dark"></i> {{-- ícone de tarefas --}}
            </div>
            <h1 class="h4 fw-bold mb-0">Tarefas</h1>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('backoffice.tasks.create') }}" class="btn btn-primary rounded-pill px-4">
                Criar
            </a>
        </div>
    </div>

    <div class="bg-white p-3 shadow-sm rounded">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tarefa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr style="background-color: #e5e5e5;"> {{-- Fundo cinzento claro --}}
                            <td>{{ $task->title }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-4">

                                    {{-- Botão Editar --}}
                                    <a href="{{ route('backoffice.tasks.edit', $task->id) }}"
                                    class="btn btn-sm btn-outline-secondary rounded-circle"
                                    title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>

                                    {{-- Botão Apagar --}}
                                    <form method="POST"
                                        action="{{ route('backoffice.tasks.destroy', $task->id) }}"
                                        style="display:inline"
                                        onsubmit="return confirm('Tem a certeza que quer apagar esta tarefa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger rounded-circle"
                                                title="Apagar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted">Sem tarefas disponíveis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($tasks, 'links') && $tasks->hasPages())
        <div class="d-flex justify-content-end mt-3">
            {{ $tasks->links('vendor.pagination.bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

@endsection

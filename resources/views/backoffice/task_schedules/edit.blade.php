@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Editar Agendamento</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.default.min.css">
@endsection

@push('styles')
    <link rel="stylesheet" href="https://interno.farmaciagaiajardim.com/assets/css/style.css">
    <link rel="stylesheet" href="https://interno.farmaciagaiajardim.com/assets/css/style_header_footer.css">
    <link rel="stylesheet" href="https://interno.farmaciagaiajardim.com/assets/css/style_content_pages.css">
@endpush

@section('content')
<div class="bg-white d-flex align-items-center gap-4 mb-4 page-main-title">
    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
        <i class="fas fa-tasks fa-lg"></i>
    </div>
    <h1 class="mb-0">Editar Tarefa</h1>
</div>

<form method="POST" action="{{ route('backoffice.task_schedules.update', $schedule->id) }}">
    @csrf
    @method('PUT')
    <div class="bg-white p-3">
        <div class="col-md-7 col-lg-6">

            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" name="activa" class="custom-control-input" id="customSwitches" {{ $schedule->activa ? 'checked' : '' }}>
                <label class="custom-control-label" for="customSwitches">Ativa</label>
            </div>

            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" name="grupo" class="custom-control-input" id="customSwitches2" {{ $schedule->grupo ? 'checked' : '' }}>
                <label class="custom-control-label" for="customSwitches2">Tarefa Grupo</label>
            </div>

            <div class="form-group my-4">
                <label for="prioridade" class="mb-2"><strong>Prioridade</strong></label>
                <select class="form-control form-select" name="prioridade" required>
                    <option value="">Selecionar</option>
                    @foreach(['Baixa', 'Média', 'Alta'] as $value)
                        <option value="{{ $value }}" {{ $schedule->prioridade === $value ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group my-4">
                <label for="task_id" class="mb-2"><strong>Tarefa</strong></label>
                <select name="task_id" id="task" class="form-control form-select" required>
                    <option value="">Selecionar</option>
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}" {{ $schedule->task_id == $task->id ? 'selected' : '' }}>
                            {{ $task->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="description" class="mb-2"><strong>Descrição</strong></label>
                <textarea name="description" id="description" class="form-control" rows="5" maxlength="255">{{ $schedule->description }}</textarea>
                <small class="text-muted fa-pull-right">máx. 255</small>
            </div>

            <div class="d-flex flex-wrap gap-2 text-center">
                <div class="form-group flex-fill">
                    <label for="data_limite" class="mb-2"><strong>Data Limite</strong></label>
                    <input type="date" name="data_limite" id="data_limite" value="{{ $schedule->data_limite ? \Carbon\Carbon::parse($schedule->data_limite)->format('Y-m-d') : '' }}" class="form-control">
                </div>
                <div class="form-group flex-fill">
                    <label for="hora_limite" class="mb-2"><strong>Hora Limite</strong></label>
                    <input type="time" name="hora_limite" id="hora_limite" value="{{ $schedule->hora_limite ? \Carbon\Carbon::parse($schedule->hora_limite)->format('H:i') : '' }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 mb-2">
                <label class="fs-6"><strong>Selecionar Colaboradores</strong></label>
                <input type="button" class="btn btn-info rounded-pill px-4" onclick="selectsUsers()" value="Todos">
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td><input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="form-check-input" {{ $schedule->users->contains($user->id) ? 'checked' : '' }}></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between page-main-actions position-sticky px-4 py-3" style="background-color: #fff; bottom: 0; z-index: 10; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
            <a href="{{ route('backoffice.task_schedules.index') }}" class="btn btn-outline-dark rounded-pill px-4">Voltar</a>
            <button type="submit" class="btn btn-success rounded-pill px-4">Salvar Alterações</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js"></script>
<script>
    function selectsUsers() {
        document.querySelectorAll('input[name="user_ids[]"]').forEach(cb => cb.checked = true);
    }

    window.addEventListener('load', () => {
        $('#task').selectize();
    });
</script>
@endpush

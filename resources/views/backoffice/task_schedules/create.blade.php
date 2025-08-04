@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Criar Agendamento</title>
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
    <h1 class="mb-0">Criar Tarefa</h1>
</div>

<form method="POST" action="{{ route('backoffice.task_schedules.store') }}">

    @csrf
    <div class="bg-white p-3">
        <div class="col-md-7 col-lg-6">

            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" name="activa" class="custom-control-input" id="customSwitches" checked>
                <label class="custom-control-label" for="customSwitches">Ativa</label>
            </div>

            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" name="grupo" class="custom-control-input" id="customSwitches2">
                <label class="custom-control-label" for="customSwitches2">Tarefa Grupo</label>
            </div>

            <div class="form-group my-4">
                <label for="prioridade" class="mb-2"><strong>Prioridade</strong></label>
                <select class="form-control form-select" name="prioridade" required>
                    <option value="">Selecionar</option>
                    <option value="Baixa">Baixa</option>
                    <option value="Média">Média</option>
                    <option value="Alta">Alta</option>
                </select>
            </div>

            <div class="form-group my-4">
                <label for="task_id" class="mb-2"><strong>Tarefa</strong></label>
                <select name="task_id" id="task" class="form-control form-select" onchange="getTaskDescription()" required>
                    <option value="">Selecionar</option>
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3" id="description_div" style="display:none;">
                <label for="description" class="mb-2"><strong>Descrição</strong></label>
                <textarea name="description" id="description" class="form-control" rows="5" placeholder="Escreva a descrição da tarefa" maxlength="255"></textarea>
                <small class="text-muted fa-pull-right">máx. 255</small>
            </div>

            @include('backoffice.task_schedules.partials.repeat_section')
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
                                <td><input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="form-check-input"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between page-main-actions position-sticky px-4 py-3" style="background-color: #fff; bottom: 0; z-index: 10; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
            <a href="{{ route('backoffice.task_schedules.index') }}" class="btn btn-outline-dark rounded-pill px-4">Voltar</a>
            <button type="submit" class="btn btn-success rounded-pill px-4">Salvar</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js"></script>
<script>
    function getTaskDescription() {
        const task_id = document.getElementById('task').value;
        document.getElementById("description_div").style.display = "";

        fetch('{{ route("backoffice.task_schedules.getDescription") }}?task_id=' + task_id)
            .then(res => res.text())
            .then(text => {
                document.getElementById("description").value = text || "";
            });
    }

    function selectsUsers() {
        document.querySelectorAll('input[name="user_ids[]"]').forEach(cb => cb.checked = true);
    }

    window.addEventListener('load', () => {
        $('#task').selectize();
    });

    function validateForm() {
        const now = new Date();

        if (!document.getElementById("no_repeat").checked && !document.getElementById("repeat").checked) {
            alert("Selecione a repetição!");
            return false;
        }

        const limitDate = new Date(document.getElementById("data_limite")?.value + ' ' + document.getElementById("hora_limite")?.value);
        const initialDate = new Date(document.getElementById("initial_date")?.value + ' ' + document.getElementById("time")?.value);
        const finalDate = new Date(document.getElementById("final_date")?.value);

        if (document.getElementById("no_repeat").checked && limitDate <= now) {
            alert("Data/hora inferior ou igual ao momento atual.");
            return false;
        }

        if (document.getElementById("repeat").checked) {
            if (initialDate >= finalDate) {
                alert("Data inicial deve ser anterior à final.");
                return false;
            }
            if (initialDate <= now) {
                alert("Data/hora de início inferior ou igual ao momento atual.");
                return false;
            }
        }

        const anyChecked = [...document.querySelectorAll('input[name="user_ids[]"]')].some(cb => cb.checked);
        if (!anyChecked) {
            alert("Selecione pelo menos um colaborador.");
            return false;
        }

        return true;
    }

    function showNoRepeat() {
        document.getElementById("no_repeat_div").style.display = "";
        document.getElementById("repeat_div").style.display = "none";

        document.getElementById('data_limite').required = true;
        document.getElementById('hora_limite').required = true;

        document.getElementById('initial_date').required = false;
        document.getElementById('final_date').required = false;
        document.getElementById('time').required = false;
        document.getElementById('period').required = false;
    }

    function showRepeat() {
        document.getElementById("no_repeat_div").style.display = "none";
        document.getElementById("repeat_div").style.display = "";

        document.getElementById('data_limite').required = false;
        document.getElementById('hora_limite').required = false;

        document.getElementById('initial_date').required = true;
        document.getElementById('final_date').required = true;
        document.getElementById('time').required = true;
        document.getElementById('period').required = true;
    }

    function showDaysOfWeek(divId, element) {
        document.getElementById(divId).style.display = element.value === "day" ? 'block' : 'none';
    }

    window.addEventListener('DOMContentLoaded', () => {
        const noRepeat = document.getElementById('no_repeat');
        const repeat = document.getElementById('repeat');

        if (noRepeat && noRepeat.checked) showNoRepeat();
        else if (repeat && repeat.checked) showRepeat();
    });
</script>
@endpush

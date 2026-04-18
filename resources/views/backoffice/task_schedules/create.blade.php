@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Criar Agendamento') }}</title>
@endsection

@section('content')
<div class="bg-white d-flex align-items-center gap-4 mb-4 page-main-title">
    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
        <i class="fas fa-tasks fa-lg"></i>
    </div>
    <h1 class="mb-0">{{ __('Criar Agendamento') }}</h1>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('Corrija os campos assinalados antes de gravar o agendamento.') }}</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('backoffice.task_schedules.store') }}" novalidate id="create-schedule-form">

        @csrf
    <div class="bg-white p-3">
        <div class="col-md-7 col-lg-6">

            <div class="custom-control custom-switch mb-3">
                <input type="hidden" name="activa" value="0">
                <input type="checkbox" name="activa" value="1" class="custom-control-input" id="customSwitches" {{ old('activa', 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="customSwitches">{{ __('Ativa') }}</label>
            </div>

            <div class="custom-control custom-switch mb-3">
                <input type="hidden" name="grupo" value="0">
                <input type="checkbox" name="grupo" value="1" class="custom-control-input" id="customSwitches2" {{ old('grupo') ? 'checked' : '' }}>
                <label class="custom-control-label" for="customSwitches2">{{ __('Tarefa Grupo') }}</label>
            </div>

            <div class="form-group my-4">
                <label for="prioridade" class="mb-2"><strong>{{ __('Prioridade') }}</strong></label>
                <select class="form-control form-select" name="prioridade">
                    <option value="">{{ __('Selecionar') }}</option>
                    <option value="Baixa" {{ old('prioridade') === 'Baixa' ? 'selected' : '' }}>{{ __('Baixa') }}</option>
                    <option value="Média" {{ old('prioridade') === 'Média' ? 'selected' : '' }}>{{ __('Média') }}</option>
                    <option value="Alta" {{ old('prioridade') === 'Alta' ? 'selected' : '' }}>{{ __('Alta') }}</option>
                </select>
            </div>

            <div class="form-group my-4">
                <label for="task_id" class="mb-2"><strong>{{ __('Tarefa') }}</strong></label>
                <select name="task_id" id="task" class="form-control form-select" onchange="getTaskDescription()">
                    <option value="">{{ __('Selecionar') }}</option>
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}" {{ (string) old('task_id') === (string) $task->id ? 'selected' : '' }}>{{ $task->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3" id="description_div" style="display:none;">
                <label for="description" class="mb-2"><strong>{{ __('Descrição') }}</strong></label>
                <textarea name="description" id="description" class="form-control" rows="5" placeholder="{{ __('Escreva a descrição da tarefa') }}" maxlength="255">{{ old('description') }}</textarea>
                <small class="text-muted fa-pull-right">{{ __('máx. 255') }}</small>
            </div>

            @include('backoffice.task_schedules.partials.repeat_section')
        </div>

        <div class="col-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 mb-2">
                <label class="fs-6"><strong>{{ __('Selecionar Colaboradores') }}</strong></label>
                <input type="button" class="btn btn-info rounded-pill px-4" onclick="selectsUsers()" value="{{ __('Todos') }}">
            </div>

            <div id="users-validation-message" class="alert alert-danger py-2 px-3 mb-3" style="display: none;">
                {{ __('Selecione pelo menos um colaborador.') }}
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Nome') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td><input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="form-check-input" {{ collect(old('user_ids', []))->contains($user->id) ? 'checked' : '' }}></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between page-main-actions position-sticky px-4 py-3" style="background-color: #fff; bottom: 0; z-index: 10; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
            <a href="{{ route('backoffice.task_schedules.index') }}" class="btn btn-outline-dark rounded-pill px-4">{{ __('Voltar') }}</a>
            <button type="submit" class="btn btn-success rounded-pill px-4">{{ __('SALVAR') }}</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
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
        const validationMessage = document.getElementById('users-validation-message');
        if (validationMessage) {
            validationMessage.style.display = 'none';
        }
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

    window.addEventListener('DOMContentLoaded', () => {
        const noRepeat = document.getElementById('no_repeat');
        const repeat = document.getElementById('repeat');
        const taskField = document.getElementById('task');
        const descriptionDiv = document.getElementById('description_div');
        const form = document.getElementById('create-schedule-form');
        const userCheckboxes = document.querySelectorAll('input[name="user_ids[]"]');
        const validationMessage = document.getElementById('users-validation-message');

        if (noRepeat && noRepeat.checked) showNoRepeat();
        else if (repeat && repeat.checked) showRepeat();

        if (taskField && taskField.value && descriptionDiv) {
            descriptionDiv.style.display = '';
        }

        userCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if ([...userCheckboxes].some(cb => cb.checked) && validationMessage) {
                    validationMessage.style.display = 'none';
                }
            });
        });

        if (form) {
            form.addEventListener('submit', function (event) {
                if (![...userCheckboxes].some(cb => cb.checked)) {
                    event.preventDefault();
                    if (validationMessage) {
                        validationMessage.style.display = 'block';
                    }
                }
            });
        }
    });
</script>
@endpush

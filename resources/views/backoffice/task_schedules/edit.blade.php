@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Agendamento') }}</title>
@endsection

@section('content')
<div class="bg-white d-flex align-items-center gap-4 mb-4 page-main-title">
    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
        <i class="fas fa-tasks fa-lg"></i>
    </div>
    <h1 class="mb-0">{{ __('Editar Tarefa') }}</h1>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('Corrija os campos assinalados antes de guardar as alterações.') }}</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<style>
    .repeat-toggle-wrapper {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 2rem;
        padding: 0.5rem;
        display: flex;
        justify-content: center;
        max-width: 420px;
        margin-bottom: 1rem;
    }

    .repeat-toggle-wrapper label {
        flex: 1;
        text-align: center;
        padding: 0.5rem 1rem;
        margin: 0;
        border-radius: 2rem;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.2s, color 0.2s;
    }

    .repeat-toggle-wrapper input[type="radio"] {
        display: none;
    }

    .repeat-toggle-wrapper input[type="radio"]:checked + label {
        background-color: #0d6efd;
        color: #fff;
    }
</style>

<form method="POST" action="{{ route('backoffice.task_schedules.update', $schedule->id) }}" id="edit-schedule-form">
    @csrf
    @method('PUT')
    <div class="bg-white p-3">
        <div class="col-md-7 col-lg-6">
            @if($schedule->repetir)
            <div class="alert alert-info mb-3">
                <strong>Esta tarefa é recorrente.</strong><br>
                <label class="mt-2">
                    <input type="radio" name="edit_mode" value="apenas_esta" checked> Editar apenas esta ocorrência
                </label>
                <label class="ms-3">
                    <input type="radio" name="edit_mode" value="todas_futuras"> Editar esta e todas as futuras
                </label>
            </div>
            @endif

            {{-- ATIVA — ÚNICO CAMPO EDITÁVEL --}}
            <div class="custom-control custom-switch mb-3">
                <input type="hidden" name="activa" value="0">
                <input type="checkbox" name="activa" value="1" class="custom-control-input" id="customSwitches" {{ old('activa', $schedule->activa) ? 'checked' : '' }}>
                <label class="custom-control-label" for="customSwitches">{{ __('Ativa') }}</label>
            </div>

            <div class="custom-control custom-switch mb-3">
                <input type="hidden" name="grupo" value="0">
                <input type="checkbox" name="grupo" value="1" class="custom-control-input" id="customSwitches2" {{ old('grupo', $schedule->grupo) ? 'checked' : '' }}>
                <label class="custom-control-label" for="customSwitches2">{{ __('Tarefa Grupo') }}</label>
            </div>

            @php
                $repetirAtual = (string) old('repetir', $schedule->repetir ? '1' : '0');
            @endphp

            <div class="form-group my-4">
                <label class="mb-2 d-block"><strong>{{ __('Tipo de Agendamento') }}</strong></label>
                <div class="repeat-toggle-wrapper">
                    <input type="radio" name="repetir" id="no_repeat" value="0" {{ $repetirAtual === '0' ? 'checked' : '' }} onclick="showNoRepeat()">
                    <label for="no_repeat">{{ __('Não Repete') }}</label>

                    <input type="radio" name="repetir" id="repeat" value="1" {{ $repetirAtual === '1' ? 'checked' : '' }} onclick="showRepeat()">
                    <label for="repeat">{{ __('Repetir') }}</label>
                </div>
            </div>

            {{-- PRIORIDADE (bloqueado + hidden) --}}
            <div class="form-group my-4">
                <label class="mb-2"><strong>{{ __('Prioridade') }}</strong></label>
                <select class="form-control form-select" disabled>
                    <option value="">{{ __('Selecionar') }}</option>
                    @foreach(['Baixa', 'Média', 'Alta'] as $value)
                        <option value="{{ $value }}" {{ $schedule->prioridade === $value ? 'selected' : '' }}>{{ __($value) }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="prioridade" value="{{ $schedule->prioridade }}">
            </div>

            {{-- TAREFA (bloqueado + hidden) --}}
            <div class="form-group my-4">
                <label class="mb-2"><strong>{{ __('Tarefa') }}</strong></label>
                <select id="task" class="form-control form-select" disabled>
                    <option value="">{{ __('Selecionar') }}</option>
                    <option value="{{ $schedule->task_id }}" selected>{{ $schedule->task->title ?? '-' }}</option>
                </select>
                <input type="hidden" name="task_id" value="{{ $schedule->task_id }}">
            </div>

            {{-- DESCRIÇÃO (bloqueado + hidden) --}}
            <div class="form-group mb-3">
                <label class="mb-2"><strong>{{ __('Descrição') }}</strong></label>
                <textarea class="form-control" rows="5" maxlength="255" disabled>{{ $schedule->description }}</textarea>
                <input type="hidden" name="description" value="{{ $schedule->description }}">
                <small class="text-muted fa-pull-right">{{ __('máx. 255') }}</small>
            </div>

            <div id="repeat_div" style="{{ $repetirAtual === '1' ? '' : 'display: none;' }}">
                <div class="d-flex flex-wrap gap-2 text-center">
                    <div class="form-group flex-fill">
                        <label class="mb-2"><strong>{{ __('Data de Início') }}</strong></label>
                        <input type="date" name="initial_date" class="form-control" value="{{ old('initial_date', $schedule->initial_date ? \Carbon\Carbon::parse($schedule->initial_date)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="form-group flex-fill">
                        <label class="mb-2"><strong>{{ __('Data de Fim') }}</strong></label>
                        <input type="date" name="final_date" class="form-control" value="{{ old('final_date', $schedule->final_date ? \Carbon\Carbon::parse($schedule->final_date)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="form-group flex-fill">
                        <label class="mb-2"><strong>{{ __('Hora') }}</strong></label>
                        <input type="time" name="time" class="form-control" value="{{ old('time', $schedule->time ? \Carbon\Carbon::parse($schedule->time)->format('H:i') : '') }}">
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label class="mb-2"><strong>{{ __('Repetir a cada') }}</strong></label>
                    <select name="period" class="form-control form-select">
                        <option value="">{{ __('Selecionar') }}</option>
                        <option value="day" {{ old('period', $schedule->period) === 'day' ? 'selected' : '' }}>{{ __('Dia') }}</option>
                        <option value="week" {{ old('period', $schedule->period) === 'week' ? 'selected' : '' }}>{{ __('Semana') }}</option>
                        <option value="month" {{ old('period', $schedule->period) === 'month' ? 'selected' : '' }}>{{ __('Mês') }}</option>
                        <option value="year" {{ old('period', $schedule->period) === 'year' ? 'selected' : '' }}>{{ __('Ano') }}</option>
                    </select>
                </div>
            </div>

            <div id="no_repeat_div" style="{{ $repetirAtual === '0' ? '' : 'display: none;' }}">
                <div class="d-flex flex-wrap gap-2 text-center">
                    <div class="form-group flex-fill">
                        <label class="mb-2"><strong>{{ __('Data Limite') }}</strong></label>
                        <input type="date" name="data_limite" class="form-control" value="{{ old('data_limite', $schedule->data_limite ? \Carbon\Carbon::parse($schedule->data_limite)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="form-group flex-fill">
                        <label class="mb-2"><strong>{{ __('Hora Limite') }}</strong></label>
                        <input type="time" name="hora_limite" class="form-control" value="{{ old('hora_limite', $schedule->hora_limite ? \Carbon\Carbon::parse($schedule->hora_limite)->format('H:i') : '') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 mb-2">
                <label class="fs-6"><strong>{{ __('Selecionar Colaboradores') }}</strong></label>
                <input type="button" class="btn btn-info rounded-pill px-4" value="{{ __('Todos') }}" onclick="selectAllUsers()">
            </div>

            <div id="users-validation-message" class="alert alert-danger py-2 px-3 mb-3" style="display: none;">
                {{ __('Selecione pelo menos um colaborador.') }}
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Nome') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>
                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="form-check-input" {{ collect(old('user_ids', $schedule->users->pluck('id')->all()))->contains($user->id) ? 'checked' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between page-main-actions position-sticky px-4 py-3" style="background-color: #fff; bottom: 0; z-index: 10; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
            <a href="{{ route('backoffice.task_schedules.index') }}" class="btn btn-outline-dark rounded-pill px-4">{{ __('Voltar') }}</a>
            <button type="submit" class="btn btn-success rounded-pill px-4">{{ __('Salvar Alterações') }}</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function showNoRepeat() {
        document.getElementById('no_repeat_div').style.display = '';
        document.getElementById('repeat_div').style.display = 'none';
    }

    function showRepeat() {
        document.getElementById('no_repeat_div').style.display = 'none';
        document.getElementById('repeat_div').style.display = '';
    }

    function selectAllUsers() {
        document.querySelectorAll('input[name="user_ids[]"]').forEach(function (checkbox) {
            checkbox.checked = true;
        });

        const validationMessage = document.getElementById('users-validation-message');
        if (validationMessage) {
            validationMessage.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('edit-schedule-form');
        const validationMessage = document.getElementById('users-validation-message');
        const userCheckboxes = document.querySelectorAll('input[name="user_ids[]"]');
        const noRepeat = document.getElementById('no_repeat');
        const repeat = document.getElementById('repeat');

        if (noRepeat && noRepeat.checked) {
            showNoRepeat();
        } else if (repeat && repeat.checked) {
            showRepeat();
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

@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Agendamento') }}</title>
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
    <h1 class="mb-0">{{ __('Editar Tarefa') }}</h1>
</div>

<form method="POST" action="{{ route('backoffice.task_schedules.update', $schedule->id) }}">
    @csrf
    @method('PUT')
    <div class="bg-white p-3">
        <div class="col-md-7 col-lg-6">

            {{-- ATIVA — ÚNICO CAMPO EDITÁVEL --}}
            <div class="custom-control custom-switch mb-3">
                <input type="hidden" name="activa" value="0">
                <input type="checkbox" name="activa" value="1" class="custom-control-input" id="customSwitches" {{ $schedule->activa ? 'checked' : '' }}>
                <label class="custom-control-label" for="customSwitches">{{ __('Ativa') }}</label>
            </div>

            {{-- GRUPO (bloqueado + hidden) --}}
            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" class="custom-control-input" id="customSwitches2" disabled {{ $schedule->grupo ? 'checked' : '' }}>
                <label class="custom-control-label" for="customSwitches2">{{ __('Tarefa Grupo') }}</label>
                <input type="hidden" name="grupo" value="{{ $schedule->grupo ? 1 : 0 }}">
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
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}" {{ $schedule->task_id == $task->id ? 'selected' : '' }}>
                            {{ $task->title }}
                        </option>
                    @endforeach
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

            {{-- DATA/HORA LIMITE (bloqueado + hidden) --}}
            <div class="d-flex flex-wrap gap-2 text-center">
                <div class="form-group flex-fill">
                    <label class="mb-2"><strong>{{ __('Data Limite') }}</strong></label>
                    <input type="date" class="form-control" value="{{ $schedule->data_limite ? \Carbon\Carbon::parse($schedule->data_limite)->format('Y-m-d') : '' }}" disabled>
                    <input type="hidden" name="data_limite" value="{{ $schedule->data_limite ? \Carbon\Carbon::parse($schedule->data_limite)->format('Y-m-d') : '' }}">
                </div>
                <div class="form-group flex-fill">
                    <label class="mb-2"><strong>{{ __('Hora Limite') }}</strong></label>
                    <input type="time" class="form-control" value="{{ $schedule->hora_limite ? \Carbon\Carbon::parse($schedule->hora_limite)->format('H:i') : '' }}" disabled>
                    <input type="hidden" name="hora_limite" value="{{ $schedule->hora_limite ? \Carbon\Carbon::parse($schedule->hora_limite)->format('H:i') : '' }}">
                </div>
            </div>

            {{-- REPETIR (se aplicável) --}}
            <input type="hidden" name="repetir" value="{{ $schedule->repetir ? 1 : 0 }}">
        </div>

        <div class="col-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 mb-2">
                <label class="fs-6"><strong>{{ __('Selecionar Colaboradores') }}</strong></label>
                {{-- Botão Todos desativado, pois não é editável neste ecrã --}}
                <input type="button" class="btn btn-info rounded-pill px-4" value="{{ __('Todos') }}" disabled>
            </div>

            {{-- UTILIZADORES (bloqueado) --}}
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
                                <td>
                                    <input type="checkbox" class="form-check-input" disabled {{ $schedule->users->contains($user->id) ? 'checked' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Enviar os IDs atuais para cumprir a validação no update --}}
            @foreach($schedule->users->pluck('id') as $uid)
                <input type="hidden" name="user_ids[]" value="{{ $uid }}">
            @endforeach
        </div>

        <div class="d-flex flex-wrap justify-content-between page-main-actions position-sticky px-4 py-3" style="background-color: #fff; bottom: 0; z-index: 10; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
            <a href="{{ route('backoffice.task_schedules.index') }}" class="btn btn-outline-dark rounded-pill px-4">{{ __('Voltar') }}</a>
            <button type="submit" class="btn btn-success rounded-pill px-4">{{ __('Salvar Alterações') }}</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js"></script>
<script>
</script>
@endpush

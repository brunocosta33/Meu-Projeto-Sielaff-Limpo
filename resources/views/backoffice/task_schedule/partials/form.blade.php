
<div class="mb-3">
    <label for="task_id" class="form-label">Tarefa</label>
    <select name="task_id" class="form-select" required>
        <option value="">-- Selecionar Tarefa --</option>
        @foreach($tasks as $task)
            <option value="{{ $task->id }}" {{ old('task_id', optional($schedule)->task_id) == $task->id ? 'selected' : '' }}>
                {{ $task->titulo }}
            </option>
        @endforeach
    </select>
</div>

<div class="row mb-3">
    <div class="col">
        <label for="deadline_date" class="form-label">Data Limite</label>
        <input type="date" name="deadline_date" class="form-control" value="{{ old('deadline_date', optional($schedule)->deadline_date) }}" required>
    </div>
    <div class="col">
        <label for="deadline_time" class="form-label">Hora Limite</label>
        <input type="time" name="deadline_time" class="form-control" value="{{ old('deadline_time', optional($schedule)->deadline_time) }}" required>
    </div>
</div>

<div class="mb-3">
    <label for="priority" class="form-label">Prioridade</label>
    <select name="priority" class="form-select" required>
        <option value="Alta" {{ old('priority', optional($schedule)->priority) == 'Alta' ? 'selected' : '' }}>Alta</option>
        <option value="Média" {{ old('priority', optional($schedule)->priority) == 'Média' ? 'selected' : '' }}>Média</option>
        <option value="Baixa" {{ old('priority', optional($schedule)->priority) == 'Baixa' ? 'selected' : '' }}>Baixa</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-check-label">
        <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', optional($schedule)->is_active) ? 'checked' : '' }}> Activa
    </label>
</div>

<div class="mb-3">
    <label class="form-check-label">
        <input type="checkbox" name="is_group_task" value="1" class="form-check-input" {{ old('is_group_task', optional($schedule)->is_group_task) ? 'checked' : '' }}> Grupo
    </label>
</div>

<div class="mb-3">
    <label class="form-check-label">
        <input type="checkbox" name="is_repeating" value="1" class="form-check-input" {{ old('is_repeating', optional($schedule)->is_repeating) ? 'checked' : '' }}> Repetição
    </label>
</div>

<div class="mb-3">
    <label for="user_ids" class="form-label">Utilizadores</label>
    <select name="user_ids[]" class="form-select" multiple required>
        @foreach($users as $user)
            <option value="{{ $user->id }}" {{ optional($schedule)->users && $schedule->users->pluck('id')->contains($user->id) ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="row mb-3">
    <div class="col-md-4">
        <label for="data_limite" class="form-label">{{ __('Data Limite') }}</label>
        <input type="date" class="form-control" name="data_limite" id="data_limite"
               value="{{ old('data_limite', optional($schedule ?? null)->data_limite) }}">
    </div>

    <div class="col-md-4">
        <label for="hora_limite" class="form-label">{{ __('Hora Limite') }}</label>
        <input type="time" class="form-control" name="hora_limite" id="hora_limite"
               value="{{ old('hora_limite', optional($schedule ?? null)->hora_limite) }}">
    </div>

    <div class="col-md-4">
        <label for="prioridade" class="form-label">{{ __('Prioridade') }}</label>
        <select name="prioridade" id="prioridade" class="form-select">
            <option value="Baixa" {{ old('prioridade', $schedule->prioridade ?? '') == 'Baixa' ? 'selected' : '' }}>{{ __('Baixa') }}</option>
            <option value="Média" {{ old('prioridade', $schedule->prioridade ?? '') == 'Média' ? 'selected' : '' }}>{{ __('Média') }}</option>
            <option value="Alta"  {{ old('prioridade', $schedule->prioridade ?? '') == 'Alta' ? 'selected' : '' }}>{{ __('Alta') }}</option>
        </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-2 form-check">
        <input class="form-check-input" type="checkbox" name="activa" id="activa"
               {{ old('activa', $schedule->activa ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="activa">{{ __('Activa') }}</label>
    </div>

    <div class="col-md-2 form-check">
        <input class="form-check-input" type="checkbox" name="grupo" id="grupo"
               {{ old('grupo', $schedule->grupo ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="grupo">{{ __('Grupo') }}</label>
    </div>

    <div class="col-md-3">
        <label for="repetir" class="form-label">{{ __('Repetição') }}</label>
        <select name="repetir" id="repetir" class="form-select">
            <option value="0" {{ old('repetir', $schedule->repetir ?? false) == false ? 'selected' : '' }}>{{ __('Não repete') }}</option>
            <option value="1" {{ old('repetir', $schedule->repetir ?? false) == true ? 'selected' : '' }}>{{ __('Repetir') }}</option>
        </select>
    </div>

    <div class="col-md-5">
        <label for="task_id" class="form-label">{{ __('Tarefa') }}</label>
        <select name="task_id" id="task_id" class="form-select" required>
            <option value="">{{ __('Selecionar') }}</option>
            @foreach($tasks as $task)
                <option value="{{ $task->id }}" {{ old('task_id', $schedule->task_id ?? '') == $task->id ? 'selected' : '' }}>
                    {{ $task->title }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="form-label">{{ __('Selecionar Colaboradores') }}</label>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Selecionar') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->nome }}</td>
                        <td class="text-center">
                            <input type="checkbox"
                                   name="users[]"
                                   value="{{ $user->id }}"
                                   {{ (isset($schedule) && $schedule->users->contains($user->id)) || in_array($user->id, old('users', [])) ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

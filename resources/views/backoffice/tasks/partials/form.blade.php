<form action="{{ isset($task) ? route('backoffice.tasks.update', $task) : route('backoffice.tasks.store') }}" method="POST">
    @csrf
    @if(isset($task))
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="titulo" class="form-label">Título</label>
        <input type="text" name="titulo" class="form-control"
               value="{{ old('titulo', $task->titulo ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição</label>
        <textarea name="descricao" class="form-control" rows="4" required>{{ old('descricao', $task->descricao ?? '') }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        {{ isset($task) ? 'Atualizar' : 'Guardar' }}
    </button>
</form>

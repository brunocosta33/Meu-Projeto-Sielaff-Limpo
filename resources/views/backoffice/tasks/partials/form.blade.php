<div class="mb-4" style="max-width: 700px;">
    <label for="title" class="form-label fw-bold">Tarefa</label>
    <input type="text" id="title" name="title"
           class="form-control bg-light border-0 rounded-pill px-4 py-2 @error('title') is-invalid @enderror"
           placeholder="Escreva a tarefa"
           value="{{ old('title', $task->title ?? '') }}"
           maxlength="255" required
           style="font-size: 0.8rem;">
    <div class="text-muted small text-end">máx. 255</div>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4" style="max-width: 700px;">
    <label for="description" class="form-label fw-bold">Descrição</label>
    <textarea id="description" name="description"
              class="form-control bg-light border-0 rounded-pill px-4 py-2 @error('description') is-invalid @enderror"
              placeholder="Escreva a descrição da tarefa"
              rows="2"
              maxlength="255"
              style="font-size: 0.8rem;">{{ old('description', $task->description ?? '') }}</textarea>
    <div class="text-muted small text-end">máx. 255</div>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

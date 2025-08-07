<div class="mb-3">
    <label for="nome" class="form-label">Nome:</label>
    <input type="text" name="nome" id="nome" class="form-control" value="{{ old('nome', $item->nome ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="referencia" class="form-label">Referência:</label>
    <input type="text" name="referencia" id="referencia" class="form-control" value="{{ old('referencia', $item->referencia ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="tipo" class="form-label">Tipo:</label>
    <select name="tipo" id="tipo" class="form-control">
        <option value="">-- Selecionar --</option>
        <option value="peça" {{ old('tipo', $item->tipo ?? '') == 'peça' ? 'selected' : '' }}>Peça</option>
        <option value="ferramenta" {{ old('tipo', $item->tipo ?? '') == 'ferramenta' ? 'selected' : '' }}>Ferramenta</option>
    </select>
</div>

<div class="mb-3">
    <label for="quantidade_atual" class="form-label">Quantidade Atual:</label>
    <input type="number" name="quantidade_atual" id="quantidade_atual" class="form-control" min="0" value="{{ old('quantidade_atual', $item->quantidade_atual ?? 0) }}">
</div>

<div class="mb-3">
    <label for="unidade_medida" class="form-label">Unidade de Medida:</label>
    <input type="text" name="unidade_medida" id="unidade_medida" class="form-control" value="{{ old('unidade_medida', $item->unidade_medida ?? 'un') }}">
</div>

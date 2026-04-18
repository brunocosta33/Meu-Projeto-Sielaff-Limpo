@php
    $isEdit = $isEdit ?? false;
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('Existem campos por corrigir.') }}</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">{{ $isEdit ? __('Editar peça') : __('Criar peça') }}</h4>
                <p class="text-muted mb-0">{{ __('Registe apenas o essencial para controlar o armazém e as carrinhas.') }}</p>
            </div>
            <a href="{{ route('backoffice.stock.items.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>{{ __('Voltar') }}
            </a>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="reference">{{ __('Referência') }}</label>
                <input type="text" name="reference" id="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference', $item->reference) }}" required>
            </div>
            <div class="form-group col-md-8">
                <label for="name">{{ __('Nome') }}</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description">{{ __('Descrição') }}</label>
            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $item->description) }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="warehouse_stock">{{ __('Stock em armazém') }}</label>
                <input type="number" min="0" name="warehouse_stock" id="warehouse_stock" class="form-control @error('warehouse_stock') is-invalid @enderror" value="{{ old('warehouse_stock', $item->warehouse_stock ?? 0) }}">
            </div>
            <div class="form-group col-md-4">
                <label for="minimum_stock">{{ __('Stock mínimo') }}</label>
                <input type="number" min="0" name="minimum_stock" id="minimum_stock" class="form-control @error('minimum_stock') is-invalid @enderror" value="{{ old('minimum_stock', $item->minimum_stock ?? 0) }}" required>
            </div>
            <div class="form-group col-md-4 d-flex align-items-end">
                <div class="form-check border rounded px-3 py-2 w-100">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        {{ __('Peça ativa') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="text-right">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i>{{ $isEdit ? __('Guardar alterações') : __('Criar peça') }}
            </button>
        </div>
    </div>
</div>

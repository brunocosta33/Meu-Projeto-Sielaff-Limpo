@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Stock de Peças') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

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

<div id="stockAjaxAlert" class="alert d-none" role="alert"></div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div>
                        <h3 class="mb-1">{{ __('Gestão de Stock') }}</h3>
                        <p class="text-muted mb-0">{{ __('Controle o armazém, distribua peças para os técnicos e registe devoluções, consumos e ajustes.') }}</p>
                    </div>
                    <div class="mt-3 mt-lg-0 d-flex flex-wrap gap-2">
                        @if($canManageWarehouse)
                            <a href="{{ route('backoffice.stock.items.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i>{{ __('Nova peça') }}
                            </a>
                            <a href="{{ route('backoffice.stock.export.items', request()->only(['q', 'is_active', 'low_stock'])) }}" class="btn btn-outline-success">
                                <i class="fas fa-file-excel mr-1"></i>{{ __('Exportar armazém') }}
                            </a>
                        @endif
                        <a href="{{ route('backoffice.stock.movements.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-exchange-alt mr-1"></i>{{ __('Ver movimentos') }}
                        </a>
                        <a href="{{ route('backoffice.stock.technicians.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-truck-loading mr-1"></i>{{ __('Stock por técnico') }}
                        </a>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 col-xl mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="text-muted small text-uppercase">{{ __('Peças') }}</div>
                            <div class="h3 mb-0" data-summary-field="items">{{ $summary['items'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="text-muted small text-uppercase">{{ __('Stock em armazém') }}</div>
                            <div class="h3 mb-0" data-summary-field="warehouse_stock">{{ $summary['warehouse_stock'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="text-muted small text-uppercase">{{ __('Stock em técnicos') }}</div>
                            <div class="h3 mb-0" data-summary-field="technician_stock">{{ $summary['technician_stock'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="text-muted small text-uppercase">{{ __('Stock total') }}</div>
                            <div class="h3 mb-0" data-summary-field="total_stock">{{ $summary['total_stock'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="border rounded p-3 h-100 {{ $summary['low_stock'] > 0 ? 'bg-warning' : 'bg-light' }}" data-summary-card="low_stock">
                            <div class="text-muted small text-uppercase">{{ __('Abaixo do mínimo') }}</div>
                            <div class="h3 mb-0" data-summary-field="low_stock">{{ $summary['low_stock'] }}</div>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('backoffice.stock.items.index') }}" class="row mt-2">
                    <div class="col-md-5 mb-2">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Pesquisar por referência, nome ou descrição') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="is_active" class="form-control">
                            <option value="">{{ __('Ativas e inativas') }}</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Só ativas') }}</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Só inativas') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <div class="form-check border rounded px-3 py-2 h-100 d-flex align-items-center">
                            <input class="form-check-input mt-0 mr-2" type="checkbox" name="low_stock" value="1" id="low_stock" {{ request()->boolean('low_stock') ? 'checked' : '' }}>
                            <label class="form-check-label" for="low_stock">{{ __('Só baixo stock') }}</label>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2 d-flex">
                        <button class="btn btn-primary w-100 mr-2" type="submit">{{ __('Filtrar') }}</button>
                        <a href="{{ route('backoffice.stock.items.index') }}" class="btn btn-outline-secondary w-100">{{ __('Limpar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="mb-1">{{ __('Peças') }}</h5>
                <p class="text-muted mb-0">{{ __('Lista simples com stock de armazém e total distribuído pelos técnicos.') }}</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Referência') }}</th>
                                <th>{{ __('Nome') }}</th>
                                <th>{{ __('Armazém') }}</th>
                                <th>{{ __('Mínimo') }}</th>
                                <th>{{ __('Técnicos') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th>{{ __('Estado') }}</th>
                                @if($canManageWarehouse)
                                    <th class="text-right">{{ __('Ações') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                @php
                                    $technicianStockTotal = (int) ($item->technician_stock_total ?? 0);
                                    $totalStock = (int) $item->warehouse_stock + $technicianStockTotal;
                                    $isLow = $item->warehouse_stock <= $item->minimum_stock;
                                @endphp
                                <tr data-item-row="{{ $item->id }}">
                                    <td>
                                        <strong>{{ $item->reference }}</strong>
                                        @if($item->description)
                                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($item->description, 90) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $item->name }}</td>
                                    <td>
                                        <span class="badge {{ $isLow ? 'badge-warning' : 'badge-primary' }} px-3 py-2" data-item-field="warehouse_stock">{{ $item->warehouse_stock }}</span>
                                    </td>
                                    <td data-item-field="minimum_stock">{{ $item->minimum_stock }}</td>
                                    <td data-item-field="technician_stock_total">{{ $technicianStockTotal }}</td>
                                    <td>
                                        <strong data-item-field="total_stock">{{ $totalStock }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->is_active ? 'badge-success' : 'badge-secondary' }}" data-item-field="is_active">
                                            {{ $item->is_active ? __('Ativa') : __('Inativa') }}
                                        </span>
                                    </td>
                                    @if($canManageWarehouse)
                                        <td class="text-right">
                                            <a href="{{ route('backoffice.stock.items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit mr-1"></i>{{ __('Editar') }}
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageWarehouse ? 8 : 7 }}" class="text-center text-muted py-4">
                                        {{ __('Ainda não existem peças registadas.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 mb-4">
        @include('backoffice.stock.partials.operation_panels', [
            'activeItems' => $activeItems,
            'technicians' => $technicians,
            'canManageWarehouse' => $canManageWarehouse,
        ])
    </div>
</div>
@endsection

@section('foot-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const alertBox = document.getElementById('stockAjaxAlert');
    const forms = document.querySelectorAll('[data-stock-operation-form]');
    const stockTranslations = {
        selectItem: @json(__('Selecione uma peça.')),
        enterQuantity: @json(__('Indique uma quantidade.')),
        adjustNotZero: @json(__('O ajuste não pode ser zero.')),
        quantityPositive: @json(__('A quantidade tem de ser maior que zero.')),
        selectTechnician: @json(__('Selecione um técnico.')),
        selectMachine: @json(__('Selecione a máquina/nº de série.')),
        fixFields: @json(__('Existem campos por corrigir antes de gravar.')),
        saving: @json(__('A guardar...')),
        saveFailed: @json(__('Não foi possível concluir a operação.')),
        saveUnexpected: @json(__('Ocorreu um erro inesperado ao gravar a operação.')),
        saveSuccess: @json(__('Operação concluída com sucesso.')),
        active: @json(__('Ativa')),
        inactive: @json(__('Inativa')),
    };

    function showAlert(type, message) {
        alertBox.className = 'alert alert-' + type;
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function clearAlert() {
        alertBox.className = 'alert d-none';
        alertBox.textContent = '';
    }

    function initSelectPickers(scope = document) {
        if (!(window.jQuery && typeof window.jQuery.fn.selectpicker === 'function')) {
            return;
        }

        window.jQuery(scope).find('.selectpicker').selectpicker('render');
        window.jQuery(scope).find('.selectpicker').selectpicker('refresh');
    }

    function clearFieldErrors(form) {
        form.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
        form.querySelectorAll('[data-field-error]').forEach((errorBox) => {
            errorBox.textContent = '';
            errorBox.classList.add('d-none');
        });
    }

    function setFieldError(form, fieldName, message) {
        const field = form.querySelector('[name="' + fieldName + '"]');
        if (field) {
            field.classList.add('is-invalid');
        }

        const errorBox = form.querySelector('[data-field-error="' + fieldName + '"]');
        if (errorBox) {
            errorBox.textContent = message;
            errorBox.classList.remove('d-none');
        }
    }

    function validateForm(form) {
        clearFieldErrors(form);

        const item = form.querySelector('[name="item_id"]');
        const quantity = form.querySelector('[name="quantity"]');
        const technician = form.querySelector('[name="technician_id"]');
        const scope = form.querySelector('[name="adjustment_scope"]');
        let valid = true;

        if (item && !item.value) {
            setFieldError(form, 'item_id', stockTranslations.selectItem);
            valid = false;
        }

        if (quantity) {
            const quantityValue = Number(quantity.value);
            const isAdjustment = form.dataset.operationType === 'adjust';

            if (!quantity.value) {
                setFieldError(form, 'quantity', stockTranslations.enterQuantity);
                valid = false;
            } else if (isAdjustment && quantityValue === 0) {
                setFieldError(form, 'quantity', stockTranslations.adjustNotZero);
                valid = false;
            } else if (!isAdjustment && quantityValue <= 0) {
                setFieldError(form, 'quantity', stockTranslations.quantityPositive);
                valid = false;
            }
        }

        if (technician) {
            const scopeRequiresTechnician = !scope || scope.value === 'technician';
            if (scopeRequiresTechnician && !technician.value && form.dataset.requiresTechnician === 'true') {
                setFieldError(form, 'technician_id', stockTranslations.selectTechnician);
                valid = false;
            }
        }

        const machine = form.querySelector('[name="machine_id"]');
        if (machine && form.dataset.requiresMachine === 'true' && !machine.value) {
            setFieldError(form, 'machine_id', stockTranslations.selectMachine);
            valid = false;
        }

        return valid;
    }

    function updateSummary(summary) {
        Object.entries(summary).forEach(([key, value]) => {
            const node = document.querySelector('[data-summary-field="' + key + '"]');
            if (node) {
                node.textContent = value;
            }
        });

        const lowStockCard = document.querySelector('[data-summary-card="low_stock"]');
        if (lowStockCard) {
            lowStockCard.classList.toggle('bg-warning', Number(summary.low_stock) > 0);
            lowStockCard.classList.toggle('bg-light', Number(summary.low_stock) <= 0);
        }
    }

    function updateItemRow(item) {
        const row = document.querySelector('[data-item-row="' + item.id + '"]');
        if (!row) {
            return;
        }

        const warehouseBadge = row.querySelector('[data-item-field="warehouse_stock"]');
        if (warehouseBadge) {
            warehouseBadge.textContent = item.warehouse_stock;
            warehouseBadge.classList.toggle('badge-warning', item.is_low_stock);
            warehouseBadge.classList.toggle('badge-primary', !item.is_low_stock);
        }

        const minimumStock = row.querySelector('[data-item-field="minimum_stock"]');
        if (minimumStock) {
            minimumStock.textContent = item.minimum_stock;
        }

        const technicianStock = row.querySelector('[data-item-field="technician_stock_total"]');
        if (technicianStock) {
            technicianStock.textContent = item.technician_stock_total;
        }

        const totalStock = row.querySelector('[data-item-field="total_stock"]');
        if (totalStock) {
            totalStock.textContent = item.total_stock;
        }

        const activeBadge = row.querySelector('[data-item-field="is_active"]');
        if (activeBadge) {
            activeBadge.textContent = item.is_active ? stockTranslations.active : stockTranslations.inactive;
            activeBadge.classList.toggle('badge-success', item.is_active);
            activeBadge.classList.toggle('badge-secondary', !item.is_active);
        }
    }

    async function submitForm(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalLabel = submitButton ? submitButton.innerHTML : '';

        if (!validateForm(form)) {
            showAlert('warning', stockTranslations.fixFields);
            return;
        }

        clearAlert();
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = stockTranslations.saving;
        }

        try {
            const response = await fetch(form.action, {
                method: form.method.toUpperCase(),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                },
                body: new FormData(form),
            });

            const payload = await response.json();

            if (!response.ok) {
                clearFieldErrors(form);

                if (payload.errors) {
                    Object.entries(payload.errors).forEach(([field, messages]) => {
                        setFieldError(form, field, messages[0]);
                    });
                    showAlert('warning', stockTranslations.fixFields);
                } else {
                    showAlert('danger', payload.message || stockTranslations.saveFailed);
                }
                return;
            }

            form.reset();
            initSelectPickers(form);
            clearFieldErrors(form);
            updateSummary(payload.summary);
            updateItemRow(payload.item);
            showAlert('success', payload.message || stockTranslations.saveSuccess);
        } catch (error) {
            showAlert('danger', stockTranslations.saveUnexpected);
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalLabel;
            }
        }
    }

    forms.forEach((form) => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submitForm(form);
        });

        form.querySelectorAll('input, select, textarea').forEach((field) => {
            field.addEventListener('input', function () {
                if (field.classList.contains('is-invalid')) {
                    validateForm(form);
                }
            });

            field.addEventListener('change', function () {
                if (field.name === 'adjustment_scope') {
                    const technicianField = form.querySelector('[name="technician_id"]');
                    if (technicianField) {
                        technicianField.closest('.form-group').classList.toggle('d-none', field.value !== 'technician');
                    }
                }

                if (field.classList.contains('is-invalid')) {
                    validateForm(form);
                }
            });
        });
    });

    initSelectPickers();
});
</script>
@endsection

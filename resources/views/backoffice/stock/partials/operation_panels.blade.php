<div class="accordion" id="stockOperations">
    @if($canManageWarehouse)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white" id="headingWarehouseIn">
                <button class="btn btn-link text-left w-100 p-0" type="button" data-toggle="collapse" data-target="#collapseWarehouseIn" aria-expanded="true">
                    <strong>{{ __('Adicionar stock ao armazém') }}</strong>
                </button>
            </div>
            <div id="collapseWarehouseIn" class="collapse show" data-parent="#stockOperations">
                <div class="card-body">
                    <form method="POST" action="{{ route('backoffice.stock.warehouse_in') }}" data-stock-operation-form data-operation-type="warehouse_in" data-requires-technician="false">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('Peça') }}</label>
                            <select name="item_id" class="form-control selectpicker" data-live-search="true" title="{{ __('Selecionar peça') }}" data-size="8" data-container="body" required>
                                @foreach($activeItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->reference }} - {{ $item->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-none" data-field-error="item_id"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Quantidade') }}</label>
                            <input type="number" min="1" name="quantity" class="form-control" required>
                            <div class="invalid-feedback d-none" data-field-error="quantity"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Notas') }}</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                            <div class="invalid-feedback d-none" data-field-error="notes"></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">{{ __('Adicionar ao armazém') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white" id="headingTransfer">
                <button class="btn btn-link text-left w-100 p-0 collapsed" type="button" data-toggle="collapse" data-target="#collapseTransfer">
                    <strong>{{ __('Transferir para técnico') }}</strong>
                </button>
            </div>
            <div id="collapseTransfer" class="collapse" data-parent="#stockOperations">
                <div class="card-body">
                    <form method="POST" action="{{ route('backoffice.stock.transfer') }}" data-stock-operation-form data-operation-type="transfer" data-requires-technician="true">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('Peça') }}</label>
                            <select name="item_id" class="form-control selectpicker" data-live-search="true" title="{{ __('Selecionar peça') }}" data-size="8" data-container="body" required>
                                @foreach($activeItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->reference }} - {{ $item->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-none" data-field-error="item_id"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Técnico') }}</label>
                            <select name="technician_id" class="form-control" required>
                                <option value="">{{ __('Selecionar técnico') }}</option>
                                @foreach($technicians as $technician)
                                    <option value="{{ $technician->id }}">{{ $technician->name ?: $technician->email }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-none" data-field-error="technician_id"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Quantidade') }}</label>
                            <input type="number" min="1" name="quantity" class="form-control" required>
                            <div class="invalid-feedback d-none" data-field-error="quantity"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Notas') }}</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                            <div class="invalid-feedback d-none" data-field-error="notes"></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">{{ __('Transferir') }}</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white" id="headingReturn">
            <button class="btn btn-link text-left w-100 p-0 collapsed" type="button" data-toggle="collapse" data-target="#collapseReturn">
                <strong>{{ __('Devolver para o armazém') }}</strong>
            </button>
        </div>
        <div id="collapseReturn" class="collapse" data-parent="#stockOperations">
            <div class="card-body">
                <form method="POST" action="{{ route('backoffice.stock.return') }}" data-stock-operation-form data-operation-type="return" data-requires-technician="{{ $canManageWarehouse ? 'true' : 'false' }}">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('Peça') }}</label>
                        <select name="item_id" class="form-control selectpicker" data-live-search="true" title="{{ __('Selecionar peça') }}" data-size="8" data-container="body" required>
                            @foreach($activeItems as $item)
                                <option value="{{ $item->id }}">{{ $item->reference }} - {{ $item->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-none" data-field-error="item_id"></div>
                    </div>
                    @if($canManageWarehouse)
                        <div class="form-group">
                            <label>{{ __('Técnico') }}</label>
                            <select name="technician_id" class="form-control" required>
                                <option value="">{{ __('Selecionar técnico') }}</option>
                                @foreach($technicians as $technician)
                                    <option value="{{ $technician->id }}">{{ $technician->name ?: $technician->email }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-none" data-field-error="technician_id"></div>
                        </div>
                    @endif
                    <div class="form-group">
                        <label>{{ __('Quantidade') }}</label>
                        <input type="number" min="1" name="quantity" class="form-control" required>
                        <div class="invalid-feedback d-none" data-field-error="quantity"></div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Notas') }}</label>
                        <textarea name="notes" rows="2" class="form-control"></textarea>
                        <div class="invalid-feedback d-none" data-field-error="notes"></div>
                    </div>
                    <button type="submit" class="btn btn-outline-secondary btn-block">{{ __('Registar devolução') }}</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white" id="headingConsume">
            <button class="btn btn-link text-left w-100 p-0 collapsed" type="button" data-toggle="collapse" data-target="#collapseConsume">
                <strong>{{ __('Consumir peça') }}</strong>
            </button>
        </div>
        <div id="collapseConsume" class="collapse" data-parent="#stockOperations">
            <div class="card-body">
                <form method="POST" action="{{ route('backoffice.stock.consume') }}" data-stock-operation-form data-operation-type="consume" data-requires-technician="{{ $canManageWarehouse ? 'true' : 'false' }}">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('Peça') }}</label>
                        <select name="item_id" class="form-control selectpicker" data-live-search="true" title="{{ __('Selecionar peça') }}" data-size="8" data-container="body" required>
                            @foreach($activeItems as $item)
                                <option value="{{ $item->id }}">{{ $item->reference }} - {{ $item->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-none" data-field-error="item_id"></div>
                    </div>
                    @if($canManageWarehouse)
                        <div class="form-group">
                            <label>{{ __('Técnico') }}</label>
                            <select name="technician_id" class="form-control" required>
                                <option value="">{{ __('Selecionar técnico') }}</option>
                                @foreach($technicians as $technician)
                                    <option value="{{ $technician->id }}">{{ $technician->name ?: $technician->email }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-none" data-field-error="technician_id"></div>
                        </div>
                    @endif
                    <div class="form-group">
                        <label>{{ __('Quantidade') }}</label>
                        <input type="number" min="1" name="quantity" class="form-control" required>
                        <div class="invalid-feedback d-none" data-field-error="quantity"></div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Notas') }}</label>
                        <textarea name="notes" rows="2" class="form-control"></textarea>
                        <div class="invalid-feedback d-none" data-field-error="notes"></div>
                    </div>
                    <button type="submit" class="btn btn-outline-danger btn-block">{{ __('Registar consumo') }}</button>
                </form>
            </div>
        </div>
    </div>

    @if($canManageWarehouse)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white" id="headingAdjust">
                <button class="btn btn-link text-left w-100 p-0 collapsed" type="button" data-toggle="collapse" data-target="#collapseAdjust">
                    <strong>{{ __('Ajuste manual') }}</strong>
                </button>
            </div>
            <div id="collapseAdjust" class="collapse" data-parent="#stockOperations">
                <div class="card-body">
                    <form method="POST" action="{{ route('backoffice.stock.adjust') }}" data-stock-operation-form data-operation-type="adjust" data-requires-technician="false">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('Peça') }}</label>
                            <select name="item_id" class="form-control selectpicker" data-live-search="true" title="{{ __('Selecionar peça') }}" data-size="8" data-container="body" required>
                                @foreach($activeItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->reference }} - {{ $item->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-none" data-field-error="item_id"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Onde ajustar') }}</label>
                            <select name="adjustment_scope" class="form-control" required>
                                <option value="warehouse">{{ __('Armazém') }}</option>
                                <option value="technician">{{ __('Carrinha do técnico') }}</option>
                            </select>
                            <div class="invalid-feedback d-none" data-field-error="adjustment_scope"></div>
                        </div>
                        <div class="form-group d-none">
                            <label>{{ __('Técnico') }}</label>
                            <select name="technician_id" class="form-control">
                                <option value="">{{ __('Selecionar técnico') }}</option>
                                @foreach($technicians as $technician)
                                    <option value="{{ $technician->id }}">{{ $technician->name ?: $technician->email }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-none" data-field-error="technician_id"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Quantidade do ajuste') }}</label>
                            <input type="number" name="quantity" class="form-control" placeholder="{{ __('Ex: 5 ou -2') }}" required>
                            <small class="text-muted">{{ __('Valor positivo soma stock. Valor negativo retira stock.') }}</small>
                            <div class="invalid-feedback d-none" data-field-error="quantity"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Notas') }}</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                            <div class="invalid-feedback d-none" data-field-error="notes"></div>
                        </div>
                        <button type="submit" class="btn btn-outline-dark btn-block">{{ __('Registar ajuste') }}</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

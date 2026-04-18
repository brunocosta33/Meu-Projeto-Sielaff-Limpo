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

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="card-title mb-1">{{ isset($store) ? __('Editar Loja') : __('Nova Loja') }}</h5>
                <p class="text-muted mb-0">{{ __('Centralize aqui toda a informação da loja para depois a Hotline usar essa ficha.') }}</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('backoffice.stores.index') }}">
                <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
            </a>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="border rounded p-3 mb-3">
                    <h6 class="text-uppercase text-muted mb-3">{{ __('Identificação') }}</h6>

                    <div class="form-group mb-3">
                        <label>{{ __('Insígnia') }}</label>
                        <select name="insignia" class="form-control" required>
                            <option value="">{{ __('-- Selecione --') }}</option>
                            @foreach($insignias as $value => $label)
                                <option value="{{ $value }}" {{ old('insignia', $store->insignia ?? '') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>{{ __('Região') }}</label>
                        <input type="text" name="regiao" class="form-control" value="{{ old('regiao', $store->regiao ?? '') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>{{ __('Código da Loja') }}</label>
                        <input type="text" name="codigo_loja" class="form-control" value="{{ old('codigo_loja', $store->codigo_loja ?? '') }}" required>
                    </div>

                    <div class="form-group mb-0">
                        <label>{{ __('Nome da Loja') }}</label>
                        <input type="text" name="nome_loja" class="form-control" value="{{ old('nome_loja', $store->nome_loja ?? '') }}" required>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="border rounded p-3 mb-3">
                    <h6 class="text-uppercase text-muted mb-3">{{ __('Localização e contacto') }}</h6>

                    <div class="form-group mb-3">
                        <label>{{ __('Morada') }}</label>
                        <textarea name="morada" class="form-control" rows="3">{{ old('morada', $store->morada ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>{{ __('Cidade') }}</label>
                                <input type="text" name="cidade" class="form-control" value="{{ old('cidade', $store->cidade ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>{{ __('Código Postal') }}</label>
                                <input type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal', $store->codigo_postal ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>{{ __('Contacto da Loja') }}</label>
                        <input type="text" name="contacto_loja" class="form-control" value="{{ old('contacto_loja', $store->contacto_loja ?? '') }}" placeholder="{{ __('Nome do responsável ou contacto principal') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>{{ __('Telefone') }}</label>
                                <input type="text" name="telefone" class="form-control" value="{{ old('telefone', $store->telefone ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>{{ __('Email') }}</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $store->email ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>{{ __('Observações') }}</label>
                        <textarea name="observacoes" class="form-control" rows="3" placeholder="{{ __('Características, horários, notas importantes, acessos, etc.') }}">{{ old('observacoes', $store->observacoes ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="border rounded p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="text-uppercase text-muted mb-1">{{ __('Máquinas da Loja') }}</h6>
                    <p class="text-muted mb-0">{{ __('Adicione aqui os números de série sem precisar de uma área separada.') }}</p>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="add-machine-row">
                    <i class="fa fa-plus"></i> {{ __('Adicionar máquina') }}
                </button>
            </div>

            <div id="machines-wrapper">
                @forelse($machineRows as $index => $machine)
                    <div class="machine-row border rounded p-3 mb-3" data-index="{{ $index }}">
                        <input type="hidden" name="machines[{{ $index }}][id]" value="{{ $machine['id'] ?? '' }}">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>{{ __('Número de Série') }}</label>
                                    <input type="text" name="machines[{{ $index }}][serial_number]" class="form-control" value="{{ $machine['serial_number'] ?? '' }}" placeholder="{{ __('Obrigatório se existir máquina') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label>{{ __('IP') }}</label>
                                    <input type="text" name="machines[{{ $index }}][ip_address]" class="form-control" value="{{ $machine['ip_address'] ?? '' }}" placeholder="192.168.1.100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>{{ __('Modelo / Descrição') }}</label>
                                    <input type="text" name="machines[{{ $index }}][descricao]" class="form-control" value="{{ $machine['descricao'] ?? '' }}" placeholder="{{ __('Ex: Glory, recycler, kiosk...') }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group mb-3 text-right">
                                    <button type="button" class="btn btn-outline-danger remove-machine-row">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="machine-row border rounded p-3 mb-3" data-index="0">
                        <input type="hidden" name="machines[0][id]" value="">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>{{ __('Número de Série') }}</label>
                                    <input type="text" name="machines[0][serial_number]" class="form-control" placeholder="{{ __('Obrigatório se existir máquina') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label>{{ __('IP') }}</label>
                                    <input type="text" name="machines[0][ip_address]" class="form-control" placeholder="192.168.1.100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>{{ __('Modelo / Descrição') }}</label>
                                    <input type="text" name="machines[0][descricao]" class="form-control" placeholder="{{ __('Ex: Glory, recycler, kiosk...') }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group mb-3 text-right">
                                    <button type="button" class="btn btn-outline-danger remove-machine-row">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <small class="text-muted">{{ __('Se remover uma máquina existente e gravar a loja, ela deixa de estar associada a esta ficha.') }}</small>
        </div>

        {!! Form::button('<i class="fa fa-save"></i> ' . (isset($store) ? __('Atualizar') : __('Gravar')), ['type' => 'submit', 'class' => 'btn btn-success']) !!}
    </div>
</div>

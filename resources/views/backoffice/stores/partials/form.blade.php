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
                                <option value="{{ $value }}" {{ old('insignia', mb_strtolower($store->insignia ?? '')) === $value ? 'selected' : '' }}>
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

        <div class="row">
            {{-- Fotografias da loja --}}
            <div class="col-lg-7">
                <div class="border rounded p-3 mb-4 h-100">
                    <h6 class="text-uppercase text-muted mb-1"><i class="fa fa-camera"></i> {{ __('Fotografias') }}</h6>
                    <p class="text-muted small mb-3">{{ __('Ex.: fachada, local da máquina, acessos. Aparecem na lista junto ao nome da loja.') }}</p>

                    @php($existingPhotos = isset($store) ? $store->files->where('tipo', 'foto') : collect())
                    @if($existingPhotos->isNotEmpty())
                        <div class="store-files-grid mb-3">
                            @foreach($existingPhotos as $file)
                                <label class="store-file-item" title="{{ $file->nome }}">
                                    <a href="{{ route('backoffice.stores.file', $file->id) }}" target="_blank" class="store-file-thumb">
                                        <img src="{{ route('backoffice.stores.file', $file->id) }}" alt="{{ $file->nome }}" loading="lazy">
                                    </a>
                                    <span class="store-file-remove"><input type="checkbox" name="remover_ficheiros[]" value="{{ $file->id }}"> {{ __('Remover') }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <label class="store-file-drop" for="store-photos-input">
                        <i class="fa fa-camera"></i>
                        <span>
                            <strong>{{ __('Adicionar fotografias') }}</strong>
                            <small>{{ __('Pode escolher várias (até 10 de cada vez).') }}</small>
                        </span>
                    </label>
                    <input type="file" name="fotos[]" id="store-photos-input" class="d-none store-upload" data-kind="foto" accept="image/*" multiple>
                    <div class="store-files-preview" id="store-photos-input-preview"></div>
                </div>
            </div>

            {{-- IBN (PDF) --}}
            <div class="col-lg-5">
                <div class="border rounded p-3 mb-4 h-100">
                    <h6 class="text-uppercase text-muted mb-1"><i class="fa fa-file-pdf"></i> {{ __('IBN') }}</h6>
                    <p class="text-muted small mb-3">{{ __('Documento IBN da loja em PDF. Aparece na coluna IBN da lista.') }}</p>

                    @php($existingIbn = isset($store) ? $store->files->where('tipo', 'ibn') : collect())
                    @foreach($existingIbn as $file)
                        <div class="store-ibn-row">
                            <a href="{{ route('backoffice.stores.file', $file->id) }}" target="_blank"><i class="fa fa-file-pdf"></i> {{ $file->nome }}</a>
                            <label class="store-file-remove mb-0"><input type="checkbox" name="remover_ficheiros[]" value="{{ $file->id }}"> {{ __('Remover') }}</label>
                        </div>
                    @endforeach

                    <label class="store-file-drop {{ $existingIbn->isNotEmpty() ? 'mt-3' : '' }}" for="store-ibn-input">
                        <i class="fa fa-file-upload"></i>
                        <span>
                            <strong>{{ $existingIbn->isNotEmpty() ? __('Adicionar outro IBN') : __('Adicionar IBN (PDF)') }}</strong>
                            <small>{{ __('Só PDF, até 20 MB.') }}</small>
                        </span>
                    </label>
                    <input type="file" name="ibn[]" id="store-ibn-input" class="d-none store-upload" data-kind="ibn" accept=".pdf,application/pdf" multiple>
                    <div class="store-files-preview" id="store-ibn-input-preview"></div>
                </div>
            </div>
        </div>
        <small class="text-muted d-block mb-3" id="store-files-status"></small>

        {!! Form::button('<i class="fa fa-save"></i> ' . (isset($store) ? __('Atualizar') : __('Gravar')), ['type' => 'submit', 'class' => 'btn btn-success']) !!}
    </div>
</div>

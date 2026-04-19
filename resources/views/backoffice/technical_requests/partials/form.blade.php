@php
    $isEdit = isset($technicalRequest);
    $canManageAll = $canManageAll ?? true;
    $backRoute = request('return_url') ?: route('backoffice.technical_requests.index', request()->only(['page', 'q', 'codigo_loja', 'serial_number', 'estado', 'prioridade', 'assigned_technician_id']));
    $selectedStatus = old('estado', $technicalRequest->estado ?? 'pendente');
    $selectedStore = old('store_id', $technicalRequest->store_id ?? null);
    $selectedMachine = old('machine_id', $technicalRequest->machine_id ?? null);
    $selectedTechnician = old('assigned_technician_id', $technicalRequest->assigned_technician_id ?? null);
    $currentMachine = $technicalRequest->machine ?? null;
    $selectedStoreModel = $stores->firstWhere('id', $selectedStore);
    $selectedMachineModel = $selectedStoreModel?->machines->firstWhere('id', $selectedMachine) ?? $currentMachine;
    $storeMachinesMap = $stores->mapWithKeys(function ($store) {
        return [
            $store->id => $store->machines->map(function ($machine) {
                return [
                    'id' => $machine->id,
                    'serial_number' => $machine->serial_number,
                    'ip_address' => $machine->ip_address,
                    'descricao' => $machine->descricao,
                ];
            })->values()->all(),
        ];
    });
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

@if($isEdit && $canManageAll && ($technicalRequest->estado ?? null) === 'concluido')
    <div class="alert alert-warning border">
        <strong>{{ __('Este pedido está concluído.') }}</strong>
        {{ __('Altere apenas para corrigir dados do pedido.') }}
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
            <div>
                <h5 class="card-title mb-1">
                    <i class="fas fa-headset mr-2"></i>
                    {{ $isEdit ? __('Atualizar pedido de assistência') : __('Registar pedido de assistência') }}
                </h5>
                <p class="text-muted mb-0">{{ __('Preencha apenas o essencial. Os campos ajustam-se ao estado escolhido.') }}</p>
            </div>
            <div class="mt-3 mt-lg-0">
                <a href="{{ $backRoute }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left mr-1"></i> {{ __('Voltar à lista') }}
                </a>
            </div>
        </div>

        <div class="alert alert-light border d-flex align-items-start">
            <i class="fas fa-lightbulb text-warning mt-1 mr-2"></i>
            <div>
                <strong>{{ __('Dica rápida') }}:</strong>
                {{ $canManageAll ? __('Use "Agendado" para desbloquear a data de visita e "Concluído" para registar a data de resolução.') : __('Pode atualizar o estado do pedido conforme o progresso da assistência.') }}
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-xl-6">
                <div class="border rounded p-3 h-100 mb-3">
                    <h6 class="text-uppercase text-muted mb-3">{{ __('Identificação') }}</h6>

                    @if($canManageAll)
                        <div class="form-group mb-3">
                            <label for="store_id">{{ __('Loja') }}</label>
                            <select name="store_id" id="store_id" class="form-control selectpicker @error('store_id') is-invalid @enderror" data-live-search="true" title="{{ __('Selecione a loja') }}" required>
                                @foreach($stores as $store)
                                    <option
                                        value="{{ $store->id }}"
                                        data-insignia="{{ ucfirst($store->insignia ?? '-') }}"
                                        data-regiao="{{ $store->regiao ?? '-' }}"
                                        data-cidade="{{ $store->cidade ?? '-' }}"
                                        data-morada="{{ $store->morada ?? '-' }}"
                                        data-contacto="{{ $store->contacto_loja ?? '-' }}"
                                        data-telefone="{{ $store->telefone ?? '-' }}"
                                        data-email="{{ $store->email ?? '-' }}"
                                        {{ (string) $selectedStore === (string) $store->id ? 'selected' : '' }}>
                                        {{ $store->codigo_loja }} - {{ $store->nome_loja }} {{ $store->insignia ? '(' . ucfirst($store->insignia) . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Pode pesquisar por código, nome ou insígnia da loja.') }}</small>
                            <small id="open_requests_warning" class="text-danger font-weight-bold mt-1" style="display: none;">
                                {{ __('Já existe um pedido aberto para esta loja.') }}
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="machine_id">{{ __('Número de Série') }}</label>
                            <select name="machine_id" id="machine_id" class="form-control selectpicker @error('machine_id') is-invalid @enderror" data-live-search="true" title="{{ __('Selecione a máquina da loja') }}" data-selected-machine="{{ $selectedMachine }}">
                                <option value="">{{ __('-- Sem máquina associada --') }}</option>
                                @if($selectedStoreModel)
                                    @foreach($selectedStoreModel->machines as $machine)
                                        <option value="{{ $machine->id }}" {{ (string) $selectedMachine === (string) $machine->id ? 'selected' : '' }}>
                                            {{ $machine->serial_number }}{{ $machine->descricao ? ' - ' . $machine->descricao : '' }}
                                        </option>
                                    @endforeach
                                @endif
                                @if($selectedMachineModel && !($selectedStoreModel && $selectedStoreModel->machines->contains('id', $selectedMachineModel->id)))
                                    <option value="{{ $selectedMachineModel->id }}" selected>
                                        {{ $selectedMachineModel->serial_number }}{{ $selectedMachineModel->descricao ? ' - ' . $selectedMachineModel->descricao : '' }}
                                    </option>
                                @endif
                            </select>
                            <small class="text-muted">{{ __('Mostra o número de série gravado e pode alterá-lo se precisar.') }}</small>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="font-weight-bold d-block">{{ __('Loja') }}</label>
                            <div class="text-muted">
                                {{ $technicalRequest->store->codigo_loja ?? '-' }} - {{ $technicalRequest->store->nome_loja ?? '-' }}
                                @if($technicalRequest->store->insignia ?? null)
                                    ({{ ucfirst($technicalRequest->store->insignia) }})
                                @endif
                            </div>
                            @if(($technicalRequest->store->morada ?? null) || ($technicalRequest->store->cidade ?? null) || ($technicalRequest->store->codigo_postal ?? null))
                                <div class="text-muted small">
                                    {{ implode(', ', array_filter([
                                        $technicalRequest->store->morada ?? null,
                                        trim(implode(' ', array_filter([
                                            $technicalRequest->store->codigo_postal ?? null,
                                            $technicalRequest->store->cidade ?? null,
                                        ]))),
                                    ])) }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold d-block">{{ __('Número de Série') }}</label>
                            <div class="text-muted">{{ $technicalRequest->machine->serial_number ?? '—' }}</div>
                        </div>
                    @endif

                    <div id="store_summary" class="alert alert-light border mb-3" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>{{ __('Ficha da loja') }}</strong>
                            <span id="store_summary_insignia" class="badge badge-secondary"></span>
                        </div>
                        <div class="small text-muted mb-1"><strong>{{ __('Região') }}:</strong> <span id="store_summary_regiao">—</span></div>
                        <div class="small text-muted mb-1"><strong>{{ __('Cidade') }}:</strong> <span id="store_summary_cidade">—</span></div>
                        <div class="small text-muted mb-1"><strong>{{ __('Morada') }}:</strong> <span id="store_summary_morada">—</span></div>
                        <div class="small text-muted mb-1"><strong>{{ __('Contacto') }}:</strong> <span id="store_summary_contacto">—</span></div>
                        <div class="small text-muted mb-1"><strong>{{ __('Telefone') }}:</strong> <span id="store_summary_telefone">—</span></div>
                        <div class="small text-muted"><strong>{{ __('Email') }}:</strong> <span id="store_summary_email">—</span></div>
                    </div>

                    @if($canManageAll)
                        <div class="form-group mb-3">
                            <label for="origem">{{ __('Origem') }}</label>
                            <input type="text" name="origem" id="origem" value="{{ old('origem', $technicalRequest->origem ?? '') }}" class="form-control @error('origem') is-invalid @enderror" placeholder="{{ __('Ex: Loja, cliente, equipa técnica') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="zona">{{ __('Zona') }}</label>
                            <select name="zona" id="zona" class="form-control @error('zona') is-invalid @enderror">
                                <option value="">{{ __('-- Selecione --') }}</option>
                                @foreach($zones as $value => $label)
                                    <option value="{{ $value }}" {{ old('zona', $technicalRequest->zona ?? '') === $value ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Importante para filtrar depois por Norte, Centro ou Sul.') }}</small>
                        </div>

                        <div class="form-group mb-0">
                            <label for="tipo_servico">{{ __('Tipo de Serviço') }}</label>
                            <select name="tipo_servico" id="tipo_servico" class="form-control @error('tipo_servico') is-invalid @enderror" required>
                                <option value="">{{ __('-- Selecione --') }}</option>
                                @foreach($serviceTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('tipo_servico', $technicalRequest->tipo_servico ?? '') === $value ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="font-weight-bold d-block">{{ __('Origem') }}</label>
                            <div class="text-muted">{{ $technicalRequest->origem ?? '—' }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold d-block">{{ __('Zona') }}</label>
                            <div class="text-muted">{{ $zones[$technicalRequest->zona] ?? '—' }}</div>
                        </div>

                        <div class="mb-0">
                            <label class="font-weight-bold d-block">{{ __('Tipo de Serviço') }}</label>
                            <div class="text-muted">{{ $serviceTypes[$technicalRequest->tipo_servico] ?? ucfirst($technicalRequest->tipo_servico) }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="border rounded p-3 h-100 mb-3">
                    <h6 class="text-uppercase text-muted mb-3">{{ __('Gestão do pedido') }}</h6>

                    <div class="row">
                        @if($canManageAll)
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="prioridade">{{ __('Prioridade') }}</label>
                                    <select name="prioridade" id="prioridade" class="form-control @error('prioridade') is-invalid @enderror" required>
                                        <option value="">{{ __('-- Selecione --') }}</option>
                                        @foreach($priorities as $value => $label)
                                            <option value="{{ $value }}" {{ old('prioridade', $technicalRequest->prioridade ?? 'media') === $value ? 'selected' : '' }}>
                                                {{ __($label) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @else
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="font-weight-bold d-block">{{ __('Prioridade') }}</label>
                                    <div class="text-muted">{{ ucfirst($technicalRequest->prioridade ?? '—') }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="assigned_technician_id">{{ __('Responsável atribuído') }}</label>
                                @if($canManageAll)
                                    <select name="assigned_technician_id" id="assigned_technician_id" class="form-control selectpicker @error('assigned_technician_id') is-invalid @enderror" data-live-search="true" title="{{ __('Selecionar técnico') }}">
                                        <option value="">{{ __('-- Por atribuir --') }}</option>
                                        @foreach($technicians as $technician)
                                            <option value="{{ $technician->id }}" {{ (string) $selectedTechnician === (string) $technician->id ? 'selected' : '' }}>
                                                {{ $technician->name ?: $technician->email }}{{ $technician->email && $technician->name ? ' - ' . $technician->email : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">{{ __('Pode deixar sem técnico e atribuir ou trocar mais tarde.') }}</small>
                                @else
                                    <div class="text-muted pt-2">{{ $technicalRequest->assignedPersonTypeLabel() }}: {{ $technicalRequest->assignedPersonLabel() }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="estado">{{ __('Estado') }}</label>
                                <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                    </div>

                    @if($canManageAll)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="data_pedido">{{ __('Data do Pedido') }}</label>
                                    <input type="date" name="data_pedido" id="data_pedido" value="{{ old('data_pedido', isset($technicalRequest) && $technicalRequest->data_pedido ? \Carbon\Carbon::parse($technicalRequest->data_pedido)->format('Y-m-d') : now()->format('Y-m-d')) }}" class="form-control @error('data_pedido') is-invalid @enderror" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3" id="data_agendamento_group">
                                    <label for="data_agendamento">{{ __('Data de Agendamento') }}</label>
                                    <input type="datetime-local" name="data_agendamento" id="data_agendamento" value="{{ old('data_agendamento', isset($technicalRequest) && $technicalRequest->data_agendamento ? \Carbon\Carbon::parse($technicalRequest->data_agendamento)->format('Y-m-d\TH:i') : '') }}" class="form-control @error('data_agendamento') is-invalid @enderror">
                                    <small class="text-muted">{{ __('Só é necessária quando o pedido fica agendado.') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0" id="data_resolucao_group">
                            <label for="data_resolucao">{{ __('Data da Resolução') }}</label>
                            <input type="datetime-local" name="data_resolucao" id="data_resolucao" min="{{ old('data_pedido', isset($technicalRequest) && $technicalRequest->data_pedido ? \Carbon\Carbon::parse($technicalRequest->data_pedido)->format('Y-m-d') : now()->format('Y-m-d')) }}T00:00" value="{{ old('data_resolucao', isset($technicalRequest) && $technicalRequest->data_resolucao ? \Carbon\Carbon::parse($technicalRequest->data_resolucao)->format('Y-m-d\TH:i') : '') }}" class="form-control @error('data_resolucao') is-invalid @enderror">
                            <small class="text-muted">{{ __('Preencha quando o pedido estiver concluído, com data e hora.') }}</small>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="font-weight-bold d-block">{{ __('Data do Pedido') }}</label>
                                    <div class="text-muted">{{ isset($technicalRequest) && $technicalRequest->data_pedido ? \Carbon\Carbon::parse($technicalRequest->data_pedido)->format('d/m/Y') : '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3" id="data_agendamento_group">
                                    <label for="data_agendamento">{{ __('Data de Agendamento') }}</label>
                                    <input type="datetime-local" name="data_agendamento" id="data_agendamento" value="{{ old('data_agendamento', isset($technicalRequest) && $technicalRequest->data_agendamento ? \Carbon\Carbon::parse($technicalRequest->data_agendamento)->format('Y-m-d\TH:i') : '') }}" class="form-control @error('data_agendamento') is-invalid @enderror">
                                    <small class="text-muted">{{ __('Obrigatória quando o pedido fica agendado.') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0" id="data_resolucao_group">
                            <label for="data_resolucao">{{ __('Data da Resolução') }}</label>
                            <input type="datetime-local" name="data_resolucao" id="data_resolucao" min="{{ isset($technicalRequest) && $technicalRequest->data_pedido ? \Carbon\Carbon::parse($technicalRequest->data_pedido)->format('Y-m-d') : now()->format('Y-m-d') }}T00:00" value="{{ old('data_resolucao', isset($technicalRequest) && $technicalRequest->data_resolucao ? \Carbon\Carbon::parse($technicalRequest->data_resolucao)->format('Y-m-d\TH:i') : '') }}" class="form-control @error('data_resolucao') is-invalid @enderror">
                            <small class="text-muted">{{ __('Preencha quando o pedido estiver concluído, com data e hora.') }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12">
                <div class="border rounded p-3">
                    <h6 class="text-uppercase text-muted mb-3">{{ __('Detalhes técnicos') }}</h6>

                    @if($canManageAll)
                        <div class="form-group mb-3">
                            <label for="descricao_problema">{{ __('Descrição do Problema') }}</label>
                            <textarea name="descricao_problema" id="descricao_problema" class="form-control @error('descricao_problema') is-invalid @enderror" rows="4" placeholder="{{ __('Explique o problema de forma curta e objetiva.') }}">{{ old('descricao_problema', $technicalRequest->descricao_problema ?? '') }}</textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label for="observacoes">{{ __('Observações internas') }}</label>
                            <textarea name="observacoes" id="observacoes" class="form-control @error('observacoes') is-invalid @enderror" rows="3" placeholder="{{ __('Informação adicional, peças em falta, contacto efetuado, etc.') }}">{{ old('observacoes', $technicalRequest->observacoes ?? '') }}</textarea>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="font-weight-bold d-block">{{ __('Descrição do Problema') }}</label>
                            <div class="text-muted">{{ $technicalRequest->descricao_problema ?: '—' }}</div>
                        </div>

                        <div class="mb-0">
                            <label class="font-weight-bold d-block">{{ __('Observações internas') }}</label>
                            <textarea name="observacoes" id="observacoes" class="form-control @error('observacoes') is-invalid @enderror" rows="4" placeholder="{{ __('Atualize aqui notas internas, peças, contacto feito ou informação útil para a assistência.') }}">{{ old('observacoes', $technicalRequest->observacoes ?? '') }}</textarea>
                            <small class="text-muted">{{ __('Pode acrescentar ou atualizar notas internas do pedido.') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($isEdit)
            <input type="hidden" name="page" value="{{ request('page') }}">
        @endif

        <div class="mt-4">
            {!! Form::button('<i class="fa fa-save mr-1"></i> ' . ($isEdit ? __('Atualizar pedido') : __('Gravar pedido')), ['type' => 'submit', 'class' => 'btn btn-success mr-2 mb-2']) !!}
            <a href="{{ $backRoute }}" class="btn btn-outline-secondary">{{ __('Cancelar') }}</a>
        </div>
    </div>
</div>

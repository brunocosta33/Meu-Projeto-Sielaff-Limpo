@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Pedidos de Assistência Técnica') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between mb-3">
                    <a href="{{ route('backoffice.technical_requests.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> {{ __('Novo Pedido') }}
                    </a>
                    <a href="{{ route('backoffice.technical_requests.export') }}" class="btn btn-outline-primary">
                        <i class="fa fa-file-excel"></i> {{ __('Exportar Excel') }}
                    </a>
                </div>

                <h5 class="card-title">{{ __('Lista de Pedidos de Assistência Técnica') }}</h5>

                {{-- 🔹 Filtros --}}
                <form method="GET" class="form-inline mb-3">

                    {{-- Filtro Código da Loja (campo texto) --}}
                    <label for="codigo_loja" class="mr-2">{{ __('Código da Loja:') }}</label>
                    <input type="text" name="codigo_loja" id="codigo_loja"
                        value="{{ request('codigo_loja') }}"
                        class="form-control mr-3"
                        placeholder="Ex: 123">

                    {{-- Filtro Estado (multi-select) --}}
                    <label for="estado" class="mr-2">{{ __('Estado:') }}</label>
                    <select name="estado[]" id="estado"
                            class="form-control selectpicker mr-2"
                            multiple data-actions-box="true"
                            title="Selecionar Estados">
                        <option value="pendente" {{ in_array('pendente', (array) request('estado')) ? 'selected' : '' }}>Pendente</option>
                        <option value="agendado" {{ in_array('agendado', (array) request('estado')) ? 'selected' : '' }}>Agendado</option>
                        <option value="concluido" {{ in_array('concluido', (array) request('estado')) ? 'selected' : '' }}>Concluído</option>
                        <option value="cancelado" {{ in_array('cancelado', (array) request('estado')) ? 'selected' : '' }}>Cancelado</option>
                        <option value="aguarda_peca" {{ in_array('aguarda_peca', (array) request('estado')) ? 'selected' : '' }}>Aguarda Peça</option>
                    </select>

                    <button type="submit" class="btn btn-primary ml-2">
                        <i class="fa fa-search"></i> {{ __('Filtrar') }}
                    </button>

                    <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-secondary ml-2">
                        <i class="fa fa-undo"></i> {{ __('Limpar') }}
                    </a>
                </form>

                {{-- 🔹 Tabela --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th style="white-space: nowrap;">{{ __('Loja') }}</th>
                                <th style="white-space: nowrap;">{{ __('Tipo de Serviço') }}</th>
                                <th style="white-space: nowrap;">{{ __('Prioridade') }}</th>
                                <th style="white-space: nowrap;">{{ __('Estado') }}</th>
                                <th style="white-space: nowrap;">{{ __('Data do Pedido') }}</th>
                                <th style="white-space: nowrap;">{{ __('Data da Resolução') }}</th>
                                <th class="text-right" style="white-space: nowrap;">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                            <tr>
                                <td style="white-space: nowrap;">
                                    {{ Str::limit(($request->store->codigo_loja ?? '-') . ' - ' . ($request->store->nome_loja ?? '-'), 35) }}
                                </td>

                                @php
                                $tipos = [
                                    'software' => 'Software',
                                    'reparacao' => 'Assistência/Reparação',
                                    'manutencao' => 'Manutenção',
                                    'pre_visita' => 'Pré-Visita',
                                ];
                                @endphp
                                <td>{{ $tipos[$request->tipo_servico] ?? ucfirst($request->tipo_servico) }}</td>

                                {{-- Prioridade --}}
                                <td style="white-space: nowrap;">
                                    <span class="badge 
                                        @switch($request->prioridade)
                                            @case('baixa') bg-info @break
                                            @case('media') bg-warning text-dark @break
                                            @case('alta') bg-danger text-white @break
                                            @default bg-light
                                        @endswitch">
                                        {{ ucfirst($request->prioridade) }}
                                    </span>
                                </td>

                                {{-- Estado + data agendamento --}}
                                <td style="white-space: nowrap;">
                                    <span class="badge 
                                        @switch($request->estado)
                                            @case('pendente') bg-warning @break
                                            @case('agendado') bg-info @break
                                            @case('concluido') bg-success @break
                                            @case('cancelado') bg-danger @break
                                            @case('aguarda_peca') bg-danger text-white @break
                                            @default bg-light
                                        @endswitch">
                                        {{ ucfirst(str_replace('_', ' ', $request->estado)) }}
                                    </span>
                                    @if($request->estado === 'agendado' && $request->data_agendamento)
                                        <div>
                                            <small style="color:#198754; font-weight:bold;">
                                                <i class="fa fa-calendar-alt"></i>
                                                {{ \Carbon\Carbon::parse($request->data_agendamento)->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    @endif
                                </td>

                                {{-- Datas --}}
                                <td style="white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($request->data_pedido)->format('d/m/Y') }}
                                </td>
                                <td style="white-space: nowrap;">
                                    {{ $request->data_resolucao ? \Carbon\Carbon::parse($request->data_resolucao)->format('d/m/Y') : '—' }}
                                </td>

                                {{-- Ações --}}
                                <td class="text-right" style="white-space: nowrap;">
                                    <a href="{{ route('backoffice.technical_requests.show', $request->id) }}" class="ml-2">
                                        <i class="fa fa-eye" title="{{ __('Ver Detalhes') }}"></i>
                                    </a>
                                    <a href="{{ route('backoffice.technical_requests.edit', ['id' => $request->id, 'page' => request('page')]) }}" class="ml-2">
                                        <i class="fa fa-edit" title="{{ __('Editar') }}"></i>
                                    </a>
                                    <a href="{{ route('backoffice.technical_requests.delete', ['id' => $request->id, 'page' => request('page')]) }}"
                                       onclick="return confirm('@lang('Tem a certeza que deseja apagar este pedido?')')"
                                       class="ml-2 text-danger">
                                        <i class="fa fa-trash" title="{{ __('Apagar') }}"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8">{{ __('Nenhum pedido registado.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('foot-scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    $('.selectpicker').selectpicker();
});
</script>
@endsection

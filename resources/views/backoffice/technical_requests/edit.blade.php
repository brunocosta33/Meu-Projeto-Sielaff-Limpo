@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Editar Pedido de Assistência') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <h5 class="card-title mb-4">{{ __('Editar Pedido de Assistência Técnica') }}</h5>

                {!! Form::model($technicalRequest, ['route' => ['backoffice.technical_requests.update', $technicalRequest->id], 'method' => 'PUT']) !!}
                {{ csrf_field() }}

                <input type="hidden" name="page" value="{{ request('page') }}">

                {{-- Loja --}}
                <div class="form-group">
                    <label>{{ __('Loja') }}</label>
                    <select name="store_id" class="form-control">
                        <option value="">{{ __('-- Selecionar Loja --') }}</option>
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $technicalRequest->store_id == $store->id ? 'selected' : '' }}>
                            {{ $store->codigo_loja }} - {{ $store->nome_loja }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Origem --}}
                <div class="form-group">
                    <label>{{ __('Origem') }}</label>
                    <input type="text" name="origem" value="{{ old('origem', $technicalRequest->origem) }}" class="form-control" required>
                </div>

                {{-- Tipo de Serviço --}}
                <div class="form-group">
                    <label>{{ __('Tipo de Serviço') }}</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="software" {{ $technicalRequest->tipo_servico == 'software' ? 'selected' : '' }}>{{ __('Software') }}</option>
                        <option value="reparacao" {{ $technicalRequest->tipo_servico == 'reparacao' ? 'selected' : '' }}>{{ __('Assistência/Reparação') }}</option>
                        <option value="manutencao" {{ $technicalRequest->tipo_servico == 'manutencao' ? 'selected' : '' }}>{{ __('Manutenção') }}</option>
                        <option value="pre_visita" {{ $technicalRequest->tipo_servico == 'pre_visita' ? 'selected' : '' }}>{{ __('Pré Visita') }}</option>
                    </select>
                </div>

                {{-- Descrição --}}
                <div class="form-group">
                    <label>{{ __('Descrição') }}</label>
                    <textarea name="descricao_problema" class="form-control" rows="3" required>{{ old('descricao_problema', $technicalRequest->descricao_problema) }}</textarea>
                </div>

                {{-- Prioridade --}}
                <div class="form-group">
                    <label>{{ __('Prioridade') }}</label>
                    <select name="prioridade" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="baixa" {{ $technicalRequest->prioridade == 'baixa' ? 'selected' : '' }}>{{ __('Baixa') }}</option>
                        <option value="media" {{ $technicalRequest->prioridade == 'media' ? 'selected' : '' }}>{{ __('Média') }}</option>
                        <option value="alta" {{ $technicalRequest->prioridade == 'alta' ? 'selected' : '' }}>{{ __('Alta') }}</option>
                    </select>
                </div>

                {{-- Estado --}}
                <div class="form-group">
                    <label>{{ __('Estado') }}</label>
                    <select name="estado" id="estado" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="pendente" {{ $technicalRequest->estado == 'pendente' ? 'selected' : '' }}>{{ __('Pendente') }}</option>
                        <option value="agendado" {{ $technicalRequest->estado == 'agendado' ? 'selected' : '' }}>{{ __('Agendado') }}</option>
                        <option value="concluido" {{ $technicalRequest->estado == 'concluido' ? 'selected' : '' }}>{{ __('Concluído') }}</option>
                        <option value="cancelado" {{ $technicalRequest->estado == 'cancelado' ? 'selected' : '' }}>{{ __('Cancelado') }}</option>
                        <option value="aguarda_peca" {{ $technicalRequest->estado == 'aguarda_peca' ? 'selected' : '' }}>{{ __('Aguarda Peça') }}</option>
                    </select>
                </div>

                {{-- Data de Agendamento (só se estado = agendado) --}}
                <div class="form-group" id="data_agendamento_group" style="{{ $technicalRequest->estado == 'agendado' ? '' : 'display:none;' }}">
                    <label>{{ __('Data de Agendamento') }}</label>
                    <input type="datetime-local" name="data_agendamento" class="form-control bg-warning text-dark"
                        value="{{ old('data_agendamento', $technicalRequest->data_agendamento ? \Carbon\Carbon::parse($technicalRequest->data_agendamento)->format('Y-m-d\TH:i') : '') }}">
                </div>


                {{-- Data do Pedido --}}
                <div class="form-group">
                    <label>{{ __('Data do Pedido') }}</label>
                    <input type="date" name="data_pedido" value="{{ old('data_pedido', $technicalRequest->data_pedido) }}" class="form-control" required>
                </div>

                {{-- Data da Resolução --}}
                <div class="form-group">
                    <label>{{ __('Data da Resolução') }}</label>
                    <input type="date" name="data_resolucao" value="{{ old('data_resolucao', $technicalRequest->data_resolucao) }}" class="form-control">
                </div>

                {{-- Observações --}}
                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="2">{{ old('observacoes', $technicalRequest->observacoes) }}</textarea>
                </div>

                <div class="mt-4">
                    <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                    {!! Form::button('<i class="fa fa-save"></i> ' . __('Atualizar'), ['type' => 'submit', 'class' => 'btn btn-success']) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const estadoSelect = document.getElementById('estado');
        const dataAgendamentoGroup = document.getElementById('data_agendamento_group');

        estadoSelect.addEventListener('change', function() {
            if (this.value === 'agendado') {
                dataAgendamentoGroup.style.display = 'block';
            } else {
                dataAgendamentoGroup.style.display = 'none';
            }
        });
    });
</script>
@endsection
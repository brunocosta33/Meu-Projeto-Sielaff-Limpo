@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Novo Pedido de Assistência') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card shadow-sm">
            <div class="card-body">

                <h5 class="card-title mb-4">
                    <i class="fas fa-tools"></i> {{ __('Novo Pedido de Assistência Técnica') }}
                </h5>

                {!! Form::open(['route' => ['backoffice.technical_requests.store']]) !!}
                {{ csrf_field() }}

                {{-- Loja --}}
                <div class="form-group">
                    <label>{{ __('Loja') }}</label>
                    <select name="store_id" class="form-control" required>
                        <option value="">{{ __('-- Selecione a Loja --') }}</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">
                                {{ $store->codigo_loja }} - {{ $store->nome_loja }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Origem --}}
                <div class="form-group">
                    <label>{{ __('Origem') }}</label>
                    <input type="text" name="origem" class="form-control" required>
                </div>

                {{-- Tipo de Serviço --}}
                <div class="form-group">
                    <label>{{ __('Tipo de Serviço') }}</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="software">{{ __('Software') }}</option>
                        <option value="reparacao">{{ __('Assistência/Reparação') }}</option>
                        <option value="manutencao">{{ __('Manutenção') }}</option>
                        <option value="pre_visita">{{ __('Pré-Visita') }}</option>
                    </select>
                </div>

                {{-- Descrição --}}
                <div class="form-group">
                    <label>{{ __('Descrição do Problema') }}</label>
                    <textarea name="descricao_problema" class="form-control" rows="3"></textarea>
                </div>

                {{-- Prioridade --}}
                <div class="form-group">
                    <label>{{ __('Prioridade') }}</label>
                    <select name="prioridade" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="baixa">{{ __('Baixa') }}</option>
                        <option value="media">{{ __('Média') }}</option>
                        <option value="alta">{{ __('Alta') }}</option>
                    </select>
                </div>

                {{-- Estado --}}
                <div class="form-group">
                    <label>{{ __('Estado') }}</label>
                    <select name="estado" id="estado" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="pendente">{{ __('Pendente') }}</option>
                        <option value="agendado">{{ __('Agendado') }}</option>
                        <option value="concluido">{{ __('Concluído') }}</option>
                        <option value="cancelado">{{ __('Cancelado') }}</option>
                        <option value="aguarda_peca">{{ __('Aguarda Peça') }}</option>
                    </select>
                </div>

                {{-- Data de Agendamento (só se estado = agendado) --}}
                <div class="form-group" id="data_agendamento_group" style="display:none;">
                    <label>{{ __('Data de Agendamento') }}</label>
                    <input type="datetime-local" name="data_agendamento" class="form-control bg-warning text-dark">
                </div>

                {{-- Data do Pedido --}}
                <div class="form-group">
                    <label>{{ __('Data do Pedido') }}</label>
                    <input type="date" name="data_pedido" class="form-control" required>
                </div>

                {{-- Data da Resolução --}}
                <div class="form-group">
                    <label>{{ __('Data da Resolução') }}</label>
                    <input type="date" name="data_resolucao" class="form-control">
                </div>

                {{-- Observações --}}
                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="2"></textarea>
                </div>

                <div class="mt-4">
                    <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                    {!! Form::button('<i class="fa fa-save"></i> ' . __('Gravar'), ['type' => 'submit', 'class' => 'btn btn-success']) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const estadoSelect = document.getElementById('estado');
    const dataAgendamentoGroup = document.getElementById('data_agendamento_group');

    estadoSelect.addEventListener('change', function () {
        if (this.value === 'agendado') {
            dataAgendamentoGroup.style.display = 'block';
        } else {
            dataAgendamentoGroup.style.display = 'none';
        }
    });
});
</script>
@endsection

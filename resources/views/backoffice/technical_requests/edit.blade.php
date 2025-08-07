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

                <div class="form-group">
                    <label>{{ __('Origem') }}</label>
                    <input type="text" name="origem" value="{{ old('origem', $technicalRequest->origem) }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Descrição') }}</label>
                    <textarea name="descricao_problema" class="form-control" rows="3" required>{{ old('descricao_problema', $technicalRequest->descricao_problema) }}</textarea>
                </div>

                <div class="form-group">
                    <label>{{ __('Prioridade') }}</label>
                    <select name="prioridade" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="baixa" {{ $technicalRequest->prioridade == 'baixa' ? 'selected' : '' }}>{{ __('Baixa') }}</option>
                        <option value="media" {{ $technicalRequest->prioridade == 'media' ? 'selected' : '' }}>{{ __('Média') }}</option>
                        <option value="alta" {{ $technicalRequest->prioridade == 'alta' ? 'selected' : '' }}>{{ __('Alta') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Estado') }}</label>
                    <select name="estado" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="pendente" {{ $technicalRequest->estado == 'pendente' ? 'selected' : '' }}>{{ __('Pendente') }}</option>
                        <option value="agendado" {{ $technicalRequest->estado == 'agendado' ? 'selected' : '' }}>{{ __('Agendado') }}</option>
                        <option value="concluído" {{ $technicalRequest->estado == 'concluído' ? 'selected' : '' }}>{{ __('Concluído') }}</option>
                        <option value="cancelado" {{ $technicalRequest->estado == 'cancelado' ? 'selected' : '' }}>{{ __('Cancelado') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Data do Pedido') }}</label>
                    <input type="date" name="data_pedido" value="{{ old('data_pedido', $technicalRequest->data_pedido) }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="2">{{ old('observacoes', $technicalRequest->observacoes) }}</textarea>
                </div>

                <div class="mt-4">
                    <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                    {!! Form::button('<i class="fa fa-save"></i> ' . __('Atualizar'), ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>

@endsection

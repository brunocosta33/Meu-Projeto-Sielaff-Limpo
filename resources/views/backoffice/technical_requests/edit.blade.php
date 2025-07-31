@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Editar Pedido de Assistência</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <h5 class="card-title mb-4">Editar Pedido de Assistência Técnica</h5>

                {!! Form::model($technicalRequest, ['route' => ['backoffice.technical_requests.update', $technicalRequest->id], 'method' => 'PUT']) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>Loja</label>
                    <select name="store_id" class="form-control">
                        <option value="">-- Selecionar Loja --</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $technicalRequest->store_id == $store->id ? 'selected' : '' }}>
                                {{ $store->codigo_loja }} - {{ $store->nome_loja }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Origem</label>
                    <input type="text" name="origem" value="{{ old('origem', $technicalRequest->origem) }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao_problema" class="form-control" rows="3" required>{{ old('descricao_problema', $technicalRequest->descricao_problema) }}</textarea>
                </div>

                <div class="form-group">
                    <label>Prioridade</label>
                    <select name="prioridade" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        <option value="baixa" {{ $technicalRequest->prioridade == 'baixa' ? 'selected' : '' }}>Baixa</option>
                        <option value="media" {{ $technicalRequest->prioridade == 'media' ? 'selected' : '' }}>Média</option>
                        <option value="alta" {{ $technicalRequest->prioridade == 'alta' ? 'selected' : '' }}>Alta</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        <option value="pendente" {{ $technicalRequest->estado == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="agendado" {{ $technicalRequest->estado == 'agendado' ? 'selected' : '' }}>Agendado</option>
                        <option value="concluído" {{ $technicalRequest->estado == 'concluído' ? 'selected' : '' }}>Concluído</option>
                        <option value="cancelado" {{ $technicalRequest->estado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data do Pedido</label>
                    <input type="date" name="data_pedido" value="{{ old('data_pedido', $technicalRequest->data_pedido) }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2">{{ old('observacoes', $technicalRequest->observacoes) }}</textarea>
                </div>

                <div class="mt-4">
                    <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                    {!! Form::button('<i class="fa fa-save"></i> Atualizar', ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>

@endsection

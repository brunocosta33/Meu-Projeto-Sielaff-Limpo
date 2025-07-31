@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Novo Pedido de Assistência</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <h5 class="card-title mb-4">Novo Pedido de Assistência Técnica</h5>

                {!! Form::open(['route' => ['backoffice.technical_requests.store']]) !!}
                {{ csrf_field() }}

                 <div class="form-group">
                    <label>Loja</label>
                    <select name="store_id" class="form-control" required>
                        <option value="">-- Selecione a Loja --</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">
                                {{ $store->codigo_loja }} - {{ $store->nome_loja }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Origem</label>
                    <input type="text" name="origem" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao_problema" class="form-control" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label>Prioridade</label>
                    <select name="prioridade" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        <option value="baixa">Baixa</option>
                        <option value="media">Média</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        <option value="pendente">Pendente</option>
                        <option value="agendado">Agendado</option>
                        <option value="concluído">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data do Pedido</label>
                    <input type="date" name="data_pedido" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"></textarea>
                </div>

                <div class="mt-4">
                    <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                    {!! Form::button('<i class="fa fa-save"></i> Gravar', ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>

@endsection
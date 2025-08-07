@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Novo Pedido de Assistência') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <h5 class="card-title mb-4">{{ __('Novo Pedido de Assistência Técnica') }}</h5>

                {!! Form::open(['route' => ['backoffice.technical_requests.store']]) !!}
                {{ csrf_field() }}

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

                <div class="form-group">
                    <label>{{ __('Origem') }}</label>
                    <input type="text" name="origem" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Descrição') }}</label>
                    <textarea name="descricao_problema" class="form-control" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label>{{ __('Prioridade') }}</label>
                    <select name="prioridade" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="baixa">{{ __('Baixa') }}</option>
                        <option value="media">{{ __('Média') }}</option>
                        <option value="alta">{{ __('Alta') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Estado') }}</label>
                    <select name="estado" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="pendente">{{ __('Pendente') }}</option>
                        <option value="agendado">{{ __('Agendado') }}</option>
                        <option value="concluído">{{ __('Concluído') }}</option>
                        <option value="cancelado">{{ __('Cancelado') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Data do Pedido') }}</label>
                    <input type="date" name="data_pedido" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="2"></textarea>
                </div>

                <div class="mt-4">
                    <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                    {!! Form::button('<i class="fa fa-save"></i> ' . __('Gravar'), ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>

@endsection
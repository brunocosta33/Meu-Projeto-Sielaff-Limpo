@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Agendar Assistência Técnica</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Novo Agendamento de Assistência</h5>

                {!! Form::open(['route' => ['backoffice.technical-schedules.store']]) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>Pedido</label>
                    <select name="technical_request_id" class="form-control" required>
                        <option value="">-- Selecione o Pedido --</option>
                        @foreach($requests as $req)
                            <option value="{{ $req->id }}">#{{ $req->id }} - {{ Str::limit($req->descricao, 50) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="data" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Hora</label>
                    <input type="time" name="hora" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3"></textarea>
                </div>

                <div class="mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.technical-schedules.index') }}">
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

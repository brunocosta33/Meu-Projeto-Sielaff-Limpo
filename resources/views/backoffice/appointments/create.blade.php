@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Novo Agendamento') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('Novo Agendamento') }}</h5>

                {!! Form::open(['route' => ['backoffice.appointments.store'], 'files' => true]) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>{{ __('Loja') }}</label>
                    <select name="store_id" class="form-control" required>
                        <option value="">{{ __('-- Selecione a Loja --') }}</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->nome_loja }} ({{ $store->codigo_loja }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Transportadora') }}</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">{{ __('-- Selecione a Transportadora --') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->nome }}</option>
                        @endforeach
                    </select>
                </div>
                
                <label for="scheduled_time" style="font-size: 0.95em; margin-bottom: 2px;">{{ __('Hora') }}</label>
                <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" style="max-width: 120px; font-size: 0.95em; padding: 2px 8px; margin-bottom: 10px;" required>

                <label for="scheduled_date" style="font-size: 0.95em; margin-bottom: 2px;">{{ __('Data') }}</label>
                <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" style="max-width: 180px; font-size: 0.95em; padding: 2px 8px; margin-bottom: 10px;" required onkeydown="return false;">

               <div class="form-group">
                    <label>{{ __('Tipo de Serviço') }}</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="Entrega">{{ __('Entrega') }}</option>
                        <option value="Levantamento">{{ __('Levantamento') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Estado') }}</label>
                    <select name="status" class="form-control" required>
                        <option value="Agendado">{{ __('Agendado') }}</option>
                        <option value="Concluído">{{ __('Concluído') }}</option>
                        <option value="Cancelado">{{ __('Cancelado') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>{{ __('PDFs Relacionados') }}</label>
                    <input type="file" name="pdfs[]" class="form-control" accept="application/pdf" multiple>
                    <small class="form-text text-muted">Pode selecionar vários ficheiros PDF.</small>
                </div>

                <input type="hidden" name="page" value="{{ request('page') }}">

                <div class="mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.appointments.index', ['page' => request('page')]) }}">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                    {!! Form::button('<i class="fa fa-save"></i> ' . __('Gravar'), ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.querySelector('input[type="date"][name="scheduled_date"]');
            if (dateInput) {
                dateInput.addEventListener('keydown', function(e) {
                    e.preventDefault();
                });
            }
        });
    </script>
@endsection

@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Nova Instalação') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('Nova Instalação') }}</h5>

                {!! Form::open(['route' => ['backoffice.installations.store']]) !!}
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
                    <label>{{ __('Equipa Técnica') }}</label>
                    <select name="team_id" class="form-control" required>
                        <option value="">{{ __('-- Selecione a Equipa --') }}</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <label for="scheduled_date" style="font-size: 0.95em; margin-bottom: 2px;">{{ __('Data') }}</label>
                <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" style="max-width: 180px; font-size: 0.95em; padding: 2px 8px; margin-bottom: 10px;" required min="{{ date('Y-m-d') }}" onkeydown="return false;">

                <label for="scheduled_time" style="font-size: 0.95em; margin-bottom: 2px;">{{ __('Hora') }}</label>
                <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" style="max-width: 120px; font-size: 0.95em; padding: 2px 8px; margin-bottom: 10px;" required>

                <div class="form-group">
                    <label>{{ __('Tipo de Serviço') }}</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="">{{ __('-- Selecione --') }}</option>
                        <option value="Instalação">{{ __('Instalação') }}</option>
                        <option value="Desinstalação">{{ __('Desinstalação') }}</option>
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
                    <textarea name="observacoes" class="form-control"></textarea>
                </div>

                <input type="hidden" name="page" value="{{ request('page') }}">

                <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
                <a href="{{ route('backoffice.installations.index', ['page' => request('page')]) }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>

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

@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h3>{{ __('Novo Técnico') }}</h3>

    <form method="POST" action="{{ route('backoffice.technicians.store') }}">
        @csrf

        <div class="form-group">
            <label>{{ __('Nome') }}</label>
            <input type="text" name="nome" class="form-control" required>
        </div>

        <div class="form-group">
            <label>{{ __('Telefone') }}</label>
            <input type="text" name="telefone" class="form-control">
        </div>

        <div class="form-group">
            <label>{{ __('Email') }}</label>
            <input type="email" name="email" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">{{ __('Guardar') }}</button>
        <a href="{{ route('backoffice.technicians.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>
    </form>
</div>
@endsection
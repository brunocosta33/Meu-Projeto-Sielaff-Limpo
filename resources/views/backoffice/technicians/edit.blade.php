
@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h3>Editar Técnico</h3>

    <form method="POST" action="{{ route('backoffice.technicians.update', $technician->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="nome" class="form-control" value="{{ $technician->nome }}" required>
        </div>

        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" class="form-control" value="{{ $technician->telefone }}">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $technician->email }}">
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('backoffice.technicians.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

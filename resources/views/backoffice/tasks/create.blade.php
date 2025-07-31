@extends('layouts.backoffice_master')
@section('content')
<div class="container">
    <h2>Criar Tarefa</h2>
    <form action="{{ route('backoffice.tasks.store') }}" method="POST">
        @csrf
        @include('backoffice.tasks.partials.form')
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
</div>
@endsection

// View: resources/views/backoffice/tasks/edit.blade.php
@extends('layouts.backoffice_master')
@section('content')
<div class="container">
    <h2>Editar Tarefa</h2>
    <form action="{{ route('backoffice.tasks.update', $task) }}" method="POST">
        @csrf
        @method('PUT')
        @include('backoffice.tasks.partials.form')
        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>
</div>
@endsection
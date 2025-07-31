
@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h2>Criar Agendamento de Tarefa</h2>

    <form action="{{ route('backoffice.task_schedules.store') }}" method="POST">
        @csrf

        @include('backoffice.task_schedules.partials.form', ['schedule' => null])

        <button type="submit" class="btn btn-success">Guardar Agendamento</button>
    </form>
</div>
@endsection


// resources/views/backoffice/task_schedules/edit.blade.php

@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h2>Editar Agendamento de Tarefa</h2>

    <form action="{{ route('backoffice.task_schedules.update', $schedule->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('backoffice.task_schedules.partials.form', ['schedule' => $schedule])

        <button type="submit" class="btn btn-primary">Atualizar Agendamento</button>
    </form>
</div>
@endsection

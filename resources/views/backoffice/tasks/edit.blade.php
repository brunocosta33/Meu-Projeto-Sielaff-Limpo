@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h2>{{ __('Editar Tarefa') }}</h2>

    <form action="{{ route('backoffice.tasks.update', $task->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('backoffice.tasks.partials.form', ['task' => $task])

        <button class="btn btn-primary">{{ __('Atualizar Tarefa') }}</button>
        <a href="{{ route('backoffice.tasks.index') }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>
    </form>
</div>
@endsection

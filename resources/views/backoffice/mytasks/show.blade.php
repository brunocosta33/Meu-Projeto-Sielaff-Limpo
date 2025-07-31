@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h2>Mostrar Tarefa</h2>

    <form action="{{ route('backoffice.mytasks.updateStatus', $task) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-bold">Tarefa</label>
            <input type="text" class="form-control" value="{{ $task->titulo }}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Descrição</label>
            <textarea class="form-control" rows="3" readonly>{{ $task->descricao }}</textarea>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label class="form-label fw-bold">Data Limite</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($task->data_limite)->format('d/m/Y') }}" readonly>
            </div>
            <div class="col">
                <label class="form-label fw-bold">Hora Limite</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($task->hora_limite)->format('H:i') }}" readonly>
            </div>
            <div class="col">
                <label class="form-label fw-bold">Prioridade</label>
                <input type="text" class="form-control" value="{{ $task->prioridade }}" readonly>
            </div>
            <div class="col">
                <label class="form-label fw-bold">Tarefa de Grupo</label>
                <input type="text" class="form-control" value="{{ $task->grupo ? 'Sim' : 'Não' }}" readonly>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Estado da Tarefa</label>
            <select name="concluida" class="form-select">
                <option value="1" {{ $task->concluida ? 'selected' : '' }}>Concluída</option>
                <option value="0" {{ !$task->concluida ? 'selected' : '' }}>Pendente</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Comentários</label>
            <textarea name="comentario" class="form-control" rows="3" maxlength="255">{{ old('comentario', $task->comentario) }}</textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('backoffice.mytasks.index') }}" class="btn btn-outline-dark">Voltar</a>
            <button type="submit" class="btn btn-success">Salvar</button>
        </div>
    </form>
</div>
@endsection

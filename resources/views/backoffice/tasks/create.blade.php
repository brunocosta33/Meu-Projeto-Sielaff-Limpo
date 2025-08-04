@extends('layouts.backoffice_master')

@section('content')

<div class="container-fluid">
    {{-- Título com ícone à esquerda --}}
    <div class="bg-white d-flex align-items-center flex-wrap mb-4 p-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="d-flex justify-content-center align-items-center bg-light rounded-3 me-3" style="width: 48px; height: 48px;">
                <i class="fas fa-tasks fa-lg text-dark"></i> {{-- ícone de lista de tarefas --}}

            </div>
            <h1 class="h4 fw-bold mb-0">Criar tarefa</h1>
        </div>
    </div>

    {{-- Formulário --}}
    <div class="bg-white p-4 shadow-sm rounded">
        <form action="{{ route('backoffice.tasks.store') }}" method="POST">
            @csrf
            @include('backoffice.tasks.partials.form')

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('backoffice.tasks.index') }}" class="btn btn-outline-dark rounded-pill px-4">VOLTAR</a>
                <button type="submit" class="btn btn-success rounded-pill px-4">SALVAR</button>
            </div>
        </form>
    </div>
</div>

@endsection

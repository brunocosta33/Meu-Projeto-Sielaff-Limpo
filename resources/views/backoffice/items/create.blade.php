@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h1 class="mb-4">Criar Novo Item</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('backoffice.items.store') }}" method="POST">
        @csrf

    @include('backoffice.items.partials.form')

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('backoffice.items.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

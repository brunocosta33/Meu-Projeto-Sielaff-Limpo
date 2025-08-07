@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h1 class="mb-4">Editar Item</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('backoffice.items.update', ['item' => $item->id]) }}" method="POST">
        @csrf
        @method('PUT')

        @include('backoffice.items.partials.form', ['item' => $item])

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('backoffice.items.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

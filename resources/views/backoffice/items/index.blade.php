@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h1 class="mb-4">Itens (Peças e Ferramentas)</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('backoffice.items.create') }}" class="btn btn-primary mb-3">Novo Item</a>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Referência</th>
                    <th>Tipo</th>
                    <th>Unidade</th>
                    <th>Stock Atual</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->nome }}</td>
                        <td>{{ $item->referencia }}</td>
                        <td>{{ $item->tipo }}</td>
                        <td>{{ $item->unidade_medida }}</td>
                        <td>{{ $item->quantidade_atual }}</td>
                        <td>
                            <a href="{{ route('backoffice.items.edit', ['item' => $item->id]) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('backoffice.items.destroy', ['item' => $item->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar este item?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Sem itens registados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $items->links() }}
</div>
@endsection

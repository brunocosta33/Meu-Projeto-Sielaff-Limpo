@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h3 class="mb-4">Stock nas Carrinhas dos Técnicos</h3>

    <form method="GET" class="form-inline mb-3">
        <div class="form-group mr-2">
            <select name="technician_id" class="form-control">
                <option value="">Todos os Técnicos</option>
                @foreach($technicians as $technician)
                    <option value="{{ $technician->id }}" {{ request('technician_id') == $technician->id ? 'selected' : '' }}>
                        {{ $technician->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mr-2">
            <select name="type" class="form-control">
                <option value="">Todos os Tipos</option>
                <option value="peca" {{ request('type') == 'peca' ? 'selected' : '' }}>Peça</option>
                <option value="ferramenta" {{ request('type') == 'ferramenta' ? 'selected' : '' }}>Ferramenta</option>
            </select>
        </div>

        <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
        <a href="{{ route('backoffice.van-stocks.index') }}" class="btn btn-outline-danger ml-2">Limpar</a>
    </form>

    <a href="{{ route('backoffice.van-stocks.create') }}" class="btn btn-success mb-3">Adicionar Stock</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Técnico</th>
                <th>Peça/Ferramenta</th>
                <th>Tipo</th>
                <th>Quantidade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $stock)
                <tr>
                    <td>{{ $stock->technician->nome ?? '-' }}</td>
                    <td>{{ $stock->part->nome ?? '-' }}</td>
                    <td>{{ ucfirst($stock->part->type ?? '-') }}</td>
                    <td>{{ $stock->quantity }}</td>
                    <td>
                        <a href="{{ route('backoffice.van-stocks.edit', $stock->id) }}" class="btn btn-sm btn-primary">Editar</a>

                        <form action="{{ route('backoffice.van-stocks.destroy', $stock->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja remover este registo?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Nenhum stock encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

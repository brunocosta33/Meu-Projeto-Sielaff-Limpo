@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h3 class="mb-4">Reservas de Peças</h3>

    <form method="GET" class="form-inline mb-3">
        <div class="form-group mr-2">
            <select name="store_id" class="form-control">
                <option value="">Todas as Lojas</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                        {{ $store->nome_loja }} ({{ $store->codigo_loja }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group mr-2">
            <input type="date" name="data_reserva" class="form-control" value="{{ request('data_reserva') }}">
        </div>
        <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
        <a href="{{ route('backoffice.part_reservations.index') }}" class="btn btn-outline-danger ml-2">Limpar</a>
    </form>

    <a href="{{ route('backoffice.part_reservations.create') }}" class="btn btn-success mb-3">Nova Reserva</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Peça</th>
                <th>Loja</th>
                <th>Quantidade</th>
                <th>Data da Reserva</th>
                <th>Notas</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $reservation)
                <tr>
                    <td>{{ $reservation->part->nome ?? '-' }}</td>
                    <td>{{ $reservation->store->nome_loja ?? '-' }}</td>
                    <td>{{ $reservation->quantity }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->reserved_at)->format('d/m/Y') }}</td>
                    <td>{{ $reservation->notes }}</td>
                    <td>
                        <a href="{{ route('backoffice.part_reservations.edit', $reservation->id) }}" class="btn btn-sm btn-primary">Editar</a>
                        <form action="{{ route('backoffice.part_reservations.destroy', $reservation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem a certeza que deseja apagar esta reserva?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Nenhuma reserva encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

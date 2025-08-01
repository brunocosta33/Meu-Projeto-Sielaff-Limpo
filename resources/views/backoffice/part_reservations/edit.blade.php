@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h3>Editar Reserva de Peça</h3>

    <form method="POST" action="{{ route('backoffice.part_reservations.update', $reservation->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Peça</label>
            <select name="part_id" class="form-control" required>
                @foreach($parts as $part)
                    <option value="{{ $part->id }}" {{ $part->id == $reservation->part_id ? 'selected' : '' }}>
                        {{ $part->nome }} — Stock: {{ $part->quantidade }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Loja</label>
            <select name="store_id" class="form-control" required>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}" {{ $store->id == $reservation->store_id ? 'selected' : '' }}>
                        {{ $store->nome_loja }} ({{ $store->codigo_loja }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group d-inline-block mr-3">
            <label>Quantidade</label>
            <input type="number"
                   name="quantity"
                   class="form-control form-control-sm w-auto"
                   min="1"
                   value="{{ $reservation->quantity }}"
                   required>
        </div>

        <div class="form-group d-inline-block">
            <label>Data da Reserva</label>
            <input type="text"
                   class="form-control form-control-sm w-auto"
                   value="{{ \Carbon\Carbon::parse($reservation->reserved_at)->format('d/m/Y') }}"
                   readonly>
            <input type="hidden"
                   name="reserved_at"
                   value="{{ \Carbon\Carbon::parse($reservation->reserved_at)->format('Y-m-d') }}">
        </div>

        <div class="form-group">
            <label>Notas</label>
            <textarea name="notes" class="form-control" rows="3">{{ $reservation->notes }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('backoffice.part_reservations.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

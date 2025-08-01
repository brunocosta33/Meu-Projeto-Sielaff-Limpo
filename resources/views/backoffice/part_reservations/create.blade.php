@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h3>Nova Reserva de Peça</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('backoffice.part_reservations.store') }}">
        @csrf

        <div class="form-group">
            <label>Peça</label>
            <select name="part_id" class="form-control" required>
                @foreach($parts as $part)
                    <option value="{{ $part->id }}">
                        {{ $part->nome }} (stock: {{ $part->quantidade }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Loja</label>
            <select name="store_id" class="form-control" required>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->nome_loja }} ({{ $store->codigo_loja }})</option>
                @endforeach
            </select>
        </div>

        <div class="form-group d-inline-block mr-3">
            <label>Quantidade</label>
            <input type="number"
                   name="quantity"
                   class="form-control form-control-sm w-auto"
                   min="1"
                   required>
        </div>

        <div class="form-group d-inline-block">
            <label>Data da Reserva</label>
            <input type="text"
                   class="form-control form-control-sm w-auto"
                   value="{{ \Carbon\Carbon::now()->format('d/m/Y') }}"
                   readonly>
            <input type="hidden"
                   name="reserved_at"
                   value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
        </div>

        <div class="form-group">
            <label>Notas</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('backoffice.part_reservations.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

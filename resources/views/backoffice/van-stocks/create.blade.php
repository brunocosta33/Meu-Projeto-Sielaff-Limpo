@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h3 class="mb-4">Adicionar Stock à Carrinha</h3>

    {{-- 🔎 Filtro por tipo de peca/ferramenta --}}
    <form method="GET" action="{{ route('backoffice.van-stocks.create') }}" class="form-inline mb-3">
        <label for="type" class="mr-2">Tipo:</label>
        <select name="type" id="type" class="form-control mr-2" onchange="this.form.submit()">
            <option value="peca" {{ request('type') == 'peca' ? 'selected' : '' }}>Peça</option>
            <option value="ferramenta" {{ request('type') == 'ferramenta' ? 'selected' : '' }}>Ferramenta</option>
        </select>
        <noscript><button type="submit" class="btn btn-sm btn-secondary">Filtrar</button></noscript>
    </form>

    {{-- 📝 Formulário de submissão do stock --}}
    <form method="POST" action="{{ route('backoffice.van-stocks.store') }}">
        @csrf

        <div class="form-group">
            <label for="technician_id">Técnico</label>
            <select name="technician_id" class="form-control" required>
                <option value="">-- Selecione --</option>
                @foreach($technicians as $technician)
                    <option value="{{ $technician->id }}" {{ old('technician_id') == $technician->id ? 'selected' : '' }}>
                        {{ $technician->nome }}
                    </option>
                @endforeach
            </select>
            @error('technician_id')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

       <div class="form-group">
            <label for="part_id">Peça/Ferramenta</label>
            <select name="part_id" class="form-control" required>
                <option value="">-- Selecione --</option>
                @foreach($parts as $part)
                    <option value="{{ $part->id }}" {{ old('part_id') == $part->id ? 'selected' : '' }}>
                        {{ $part->nome }} (stock: {{ $part->quantidade }})
                    </option>
                @endforeach
            </select>
            @error('part_id')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <input type="hidden" name="type" value="{{ $type }}">

        <div class="form-group">
            <label for="quantity">Quantidade</label>
            <input type="number" name="quantity" class="form-control form-control-sm w-auto d-inline-block"
                   min="1" required value="{{ old('quantity') }}">
            @error('quantity')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('backoffice.van-stocks.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h1 class="mb-4">Importar Stock via Excel</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('backoffice.stock_import.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="ficheiro" class="form-label">Selecionar Ficheiro Excel:</label>
            <input type="file" name="ficheiro" id="ficheiro" class="form-control" accept=".xlsx,.xls" required>
        </div>

        <button type="submit" class="btn btn-primary">Importar</button>
    </form>
</div>
@endsection

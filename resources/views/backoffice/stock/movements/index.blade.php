@extends('layouts.backoffice_master')

@section('content')
<div class="container">
    <h1 class="mb-4">Histórico de Movimentos de Stock</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

   <a href="{{ route('backoffice.stock.movements.create') }}" class="btn btn-primary mb-3">Novo Movimento</a>


    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Item</th>
                    <th>Referência</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Motivo</th>
                    <th>Origem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimentos as $mov)
                    <tr>
                        <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $mov->item->nome }}</td>
                        <td>{{ $mov->item->referencia }}</td>
                        <td><strong class="{{ $mov->tipo == 'entrada' ? 'text-success' : 'text-danger' }}">{{ ucfirst($mov->tipo) }}</strong></td>
                        <td>{{ $mov->quantidade }}</td>
                        <td>{{ $mov->motivo }}</td>
                        <td>{{ $mov->origem }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Sem movimentos registados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $movimentos->links() }}
</div>
@endsection

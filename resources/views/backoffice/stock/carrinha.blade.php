@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Stock da Carrinha') }}</title>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Stock da Carrinha: ') . $location->nome }}</h5>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{{ __('Peça') }}</th>
                    <th>{{ __('Referência') }}</th>
                    <th>{{ __('Quantidade') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stock as $s)
                <tr>
                    <td>{{ $s->item->nome }}</td>
                    <td>{{ $s->item->referencia }}</td>
                    <td>{{ $s->quantidade }}</td>
                    <td>
                        {{-- Enviar ao Cliente --}}
                        <form action="{{ route('backoffice.stock.movements.store') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $s->item_id }}">
                            <input type="hidden" name="origem_id" value="{{ $location->id }}">
                            <input type="hidden" name="tipo_movimento" value="saida">

                            <input type="number" name="quantidade" value="1" min="1" max="{{ $s->quantidade }}" style="width:70px;display:inline-block;">

                            {{-- Selecionar Cliente --}}
                            <select name="destino_id" onchange="loadMachines(this.value, {{ $s->item_id }})" required style="width:150px;display:inline-block;">
                                <option value="">{{ __('Cliente') }}</option>
                                @foreach(\App\Models\Location::where('tipo','cliente')->get() as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                                @endforeach
                            </select>

                            {{-- Selecionar Máquina do Cliente --}}
                            <select name="machine_id" id="machine-select-{{ $s->item_id }}" style="width:150px;display:inline-block;">
                                <option value="">{{ __('Máquina') }}</option>
                            </select>

                            <button type="submit" class="btn btn-sm btn-primary">{{ __('Aplicar Peça') }}</button>
                        </form>

                        {{-- Devolver ao Armazém --}}
                        <form action="{{ route('backoffice.stock.movements.store') }}" method="POST" style="display:inline; margin-left:5px;">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $s->item_id }}">
                            <input type="hidden" name="origem_id" value="{{ $location->id }}">
                            <input type="hidden" name="destino_id" value="{{ \App\Models\Location::where('tipo','armazem')->first()->id }}">
                            <input type="hidden" name="tipo_movimento" value="devolucao">
                            <input type="hidden" name="quantidade" value="1">
                            <button type="submit" class="btn btn-sm btn-warning">{{ __('Devolver Armazém') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">{{ __('Sem stock registado.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function loadMachines(clienteId, itemId) {
    if (!clienteId) return;
    fetch(`/api/machines/by-client/${clienteId}`)
        .then(res => res.json())
        .then(data => {
            let select = document.getElementById(`machine-select-${itemId}`);
            select.innerHTML = '<option value="">{{ __('Máquina') }}</option>';
            data.forEach(m => {
                select.innerHTML += `<option value="${m.id}">${m.serial_number} - ${m.descricao ?? ''}</option>`;
            });
        });
}
</script>
@endsection

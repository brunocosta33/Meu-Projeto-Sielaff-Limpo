@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Peças</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="form-inline mb-3">
                    <div class="form-group mr-2">
                        <label for="type" class="mr-2">Tipo:</label>
                        <select name="type" id="type" class="form-control" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="peca" {{ request('type') == 'peca' ? 'selected' : '' }}>Peça</option>
                            <option value="ferramenta" {{ request('type') == 'ferramenta' ? 'selected' : '' }}>Ferramenta</option>
                        </select>
                    </div>
                    <noscript><button type="submit" class="btn btn-sm btn-primary ml-2">Filtrar</button></noscript>
                </form>
                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.parts.create') }}" class="btn btn-success mb-3">
                        <i class="fa fa-plus"></i> Nova Peça
                    </a>
                </div>

                <h5 class="card-title">Lista de Peças no Armazém</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead>
                            <tr>
                                <th>Stock</th>
                                <th>Tipo</th> {{-- NOVO --}}
                                <th>Nome</th>
                                <th>Referência</th>
                                <th>Descrição</th>
                                <th>Reserva/Loja</th>
                                <th>Quantidade Reservada</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parts as $part)
                                <tr>
                                    <td>{{ $part->quantidade }}</td>
                                    <td>{{ $part->type == 'ferramenta' ? 'Ferramenta' : 'Peça' }}</td> {{-- NOVO --}}
                                    <td>{{ $part->nome }}</td>
                                    <td>{{ $part->referencia }}</td>
                                    <td>{{ $part->descricao }}</td>
                                    <td>
                                        @if ($part->reservations->count())
                                            <ul class="mb-0 pl-3">
                                                @foreach($part->reservations as $reservation)
                                                    <li>{{ $reservation->store->nome_loja ?? '—' }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td>
                                        @php
                                            $totalReservado = $part->reservations->sum('quantity');
                                        @endphp
                                        {{ $totalReservado > 0 ? $totalReservado : '—' }}
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('backoffice.parts.show', $part->id) }}" class="text-info" title="Ver Detalhes">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('backoffice.parts.edit', $part->id) }}" class="text-primary" title="Editar">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="{{ route('backoffice.parts.delete', $part->id) }}"
                                            onclick="return confirm('Tem a certeza que deseja apagar esta peça?')"
                                            class="text-danger" title="Apagar">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8">Nenhuma peça registada.</td></tr> {{-- Atualiza o colspan para 8 --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $parts->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

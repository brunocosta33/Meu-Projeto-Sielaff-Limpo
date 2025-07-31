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

                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.parts.create') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-plus"></i> Nova Peça
                    </a>
                </div>

                <h5 class="card-title">Lista de Peças no Armazém</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Stock</th>
                                <th>Nome</th>
                                <th>Referência</th>
                                <th>Descrição</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parts as $part)
                                <tr>
                                    <td>{{ $part->quantidade }}</td>
                                    <td>{{ $part->nome }}</td>
                                    <td>{{ $part->referencia }}</td>
                                    <td>{{ $part->descricao }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('backoffice.parts.show', $part->id) }}" class="ml-2 text-info">
                                            <i class="fa fa-eye" title="Ver Detalhes"></i>
                                        </a>
                                        <a href="{{ route('backoffice.parts.edit', $part->id) }}" class="ml-2">
                                            <i class="fa fa-edit" title="Editar"></i>
                                        </a>
                                        <a href="{{ route('backoffice.parts.delete', $part->id) }}"
                                           onclick="return confirm('Tem a certeza que deseja apagar esta peça?')"
                                           class="ml-2 text-danger">
                                            <i class="fa fa-trash" title="Apagar"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">Nenhuma peça registada.</td></tr>
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

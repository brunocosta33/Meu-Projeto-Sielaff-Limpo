@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Lojas') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.stores.create') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-plus"></i> {{ __('Nova Loja') }}
                    </a>
                </div>

                <h5 class="card-title">{{ __('Lista de Lojas') }}</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Região') }}</th>
                                <th>{{ __('Código Loja') }}</th>
                                <th>{{ __('Nome Loja') }}</th>
                                <th>{{ __('Código Postal') }}</th>
                                <th class="text-right">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stores as $store)
                                <tr>
                                    <td>{{ $store->id }}</td>
                                    <td>{{ $store->regiao }}</td>
                                    <td>{{ $store->codigo_loja }}</td>
                                    <td>{{ $store->nome_loja }}</td>
                                    <td>{{ $store->codigo_postal }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('backoffice.stores.edit', $store->id) }}" class="ml-2">
                                            <i class="fa fa-edit" title="Editar"></i>
                                        </a>
                                        <a href="{{ route('backoffice.stores.delete', $store->id) }}"
                                           onclick="return confirm('Tem a certeza que deseja apagar esta loja?')"
                                           class="ml-2 text-danger">
                                            <i class="fa fa-trash" title="Apagar"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">Nenhuma loja registada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $stores->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

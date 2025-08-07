@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Pedidos de Assistência Técnica') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.technical_requests.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> {{ __('Novo Pedido') }}
                    </a>
                </div>

                <h5 class="card-title">{{ __('Lista de Pedidos de Assistência Técnica') }}</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Loja') }}</th>
                                <th>{{ __('Origem') }}</th>
                                <th>{{ __('Prioridade') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th>{{ __('Data do Pedido') }}</th>
                                <th class="text-right">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr>
                                    <td>{{ $request->id }}</td>
                                    <td>{{ $request->store->codigo_loja ?? '-' }} - {{ $request->store->nome_loja ?? '-' }}</td>
                                    <td>{{ $request->origem }}</td>
                                    <td>
                                        <span class="badge 
                                            @switch($request->prioridade)
                                                @case('baixa') bg-success @break
                                                @case('media') bg-warning text-dark @break
                                                @case('alta') bg-danger @break
                                                @default bg-light
                                            @endswitch">
                                            {{ ucfirst($request->prioridade) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            @switch($request->estado)
                                                @case('pendente') bg-secondary @break
                                                @case('agendado') bg-info @break
                                                @case('concluído') bg-success @break
                                                @case('cancelado') bg-danger @break
                                                @default bg-light
                                            @endswitch">
                                            {{ ucfirst($request->estado) }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($request->data_pedido)->format('d/m/Y') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('backoffice.technical_requests.show', $request->id) }}" class="ml-2">
                                            <i class="fa fa-eye" title="{{ __('Ver Detalhes') }}"></i>
                                        </a>
                                        <a href="{{ route('backoffice.technical_requests.edit', $request->id) }}" class="ml-2">
                                            <i class="fa fa-edit" title="{{ __('Editar') }}"></i>
                                        </a>
                                        <a href="{{ route('backoffice.technical_requests.delete', $request->id) }}"
                                            onclick="return confirm('@lang('Tem a certeza que deseja apagar este pedido?')')"
                                           class="ml-2 text-danger">
                                            <i class="fa fa-trash" title="{{ __('Apagar') }}"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">{{ __('Nenhum pedido registado.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $requests->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

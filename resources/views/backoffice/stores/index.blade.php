@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Lojas') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-store"></i> {{ __('Lista de Lojas') }}
                    </h5>
                    <a href="{{ route('backoffice.stores.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> {{ __('Nova Loja') }}
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background: linear-gradient(90deg, #007bff, #00c6ff); color: white;">
                            <tr>
                                <th>{{ __('Código Loja') }}</th>
                                <th>{{ __('Nome Loja') }}</th>
                                <th>{{ __('Região') }}</th>
                                <th>{{ __('Máquinas') }}</th>
                                <th>{{ __('Código Postal') }}</th>
                                <th class="text-right">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stores as $store)
                            <tr>
                                {{-- Código Loja destacado --}}
                                <td>
                                    <span class="badge badge-primary px-4 py-2" 
                                          style="font-size: 1rem; font-weight: bold;">
                                        {{ $store->codigo_loja }}
                                    </span>
                                </td>

                                <td><strong>{{ $store->nome_loja }}</strong></td>
                                <td>{{ $store->regiao }}</td>

                                {{-- Máquinas com destaque verde --}}
                                <td>
                                    @if($store->machines->isNotEmpty())
                                        <div class="d-flex flex-wrap">
                                            @foreach($store->machines as $machine)
                                                <span class="badge badge-warning mr-2 mb-2 px-3 py-2" style="font-size: 1rem;">
                                                    <i class="fa fa-cogs"></i> {{ $machine->serial_number }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            <i class="fa fa-minus-circle"></i> {{ __('Sem máquinas') }}
                                        </span>
                                    @endif
                                </td>

                                <td>{{ $store->codigo_postal }}</td>

                                {{-- Ações --}}
                                <td class="text-right">
                                    <a href="{{ route('backoffice.stores.edit', $store->id) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="{{ route('backoffice.stores.delete', $store->id) }}"
                                       onclick="return confirm('Tem a certeza que deseja apagar esta loja?')"
                                       class="btn btn-sm btn-outline-danger" title="Apagar">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="fa fa-info-circle"></i> {{ __('Nenhuma loja registada.') }}
                                </td>
                            </tr>
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

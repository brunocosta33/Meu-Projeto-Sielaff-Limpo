@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Máquinas') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.machines.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> {{ __('Nova Máquina') }}
                    </a>
                </div>

                <h5 class="card-title">{{ __('Lista de Máquinas') }}</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Modelo') }}</th>
                                <th>{{ __('Nº de Série') }}</th>
                                <th>{{ __('Data de Recebimento') }}</th>
                                <th class="text-right">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($machines as $machine)
                                <tr>
                                    <td>{{ $machine->id }}</td>
                                    <td>{{ $machine->modelo }}</td>
                                    <td>{{ $machine->numero_serie }}</td>
                                    <td>{{ \Carbon\Carbon::parse($machine->data_recebimento)->format('d/m/Y') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('backoffice.machines.show', $machine->id) }}" class="ml-2 text-info">
                                            <i class="fa fa-eye" title="{{ __('Ver Detalhes') }}"></i>
                                        </a>
                                        <a href="{{ route('backoffice.machines.edit', $machine->id) }}" class="ml-2">
                                            <i class="fa fa-edit" title="{{ __('Editar') }}"></i>
                                        </a>
                                        <a href="{{ route('backoffice.machines.delete', $machine->id) }}"
                                            onclick="return confirm('@lang('Tem a certeza que deseja apagar esta máquina?')')"
                                           class="ml-2 text-danger">
                                            <i class="fa fa-trash" title="{{ __('Apagar') }}"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">{{ __('Nenhuma máquina registada.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $machines->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

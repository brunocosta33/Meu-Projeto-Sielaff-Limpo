@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ str_replace('.', ' ', config('app.name')) }} - {{ __('Fornecedores') }}</title>
@endsection

@section('head-script')
@endsection

@section('content')
    <div class="row">
        @include('flash::message')
    </div>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="float-right">
                        <a href="{{ route('backoffice.suppliers.create') }}" class="collapsed" data-parent="#sidebar">
                            <button type="button" class="btn btn-success" title="{{ __('Criar') }}">
                                <span class="fa fa-plus"></span>&nbsp;&nbsp;{{ __('Criar') }}
                            </button>
                        </a>
                    </div>
                    <h5 class="card-title">{{ __('Fornecedores') }}</h5>
                    <br>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('Nome') }}</th>
                                    <th scope="col">{{ __('Contacto') }}</th>
                                    <th scope="col">{{ __('Email') }}</th>
                                    <th scope="col">{{ __('Morada') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $supplier)
                                    <tr scope="row">
                                        <td>{{ $supplier->nome }}</td>
                                        <td>{{ $supplier->contacto }}</td>
                                        <td>{{ $supplier->email }}</td>
                                        <td>{{ $supplier->morada }}</td>
                                        <td class="text-right">
                                            <a class="ml-2" href="{{ route('backoffice.suppliers.edit', $supplier->id) }}">
                                                <i class="fa fa-edit mr-1" title="{{ __('Editar') }}"></i>
                                            </a>

                                            @if (auth()->user()->role()->first()->name == 'admin')
                                                <a class="ml-2"
                                                    href="{{ route('backoffice.suppliers.delete', $supplier->id) }}"
                                                    onclick="return confirm('{{ __('Tem a certeza que deseja apagar?') }}')">
                                                    <i class="fa fa-trash mr-1" title="{{ __('Apagar') }}"></i>
                                                </a>
                                            @else
                                                <i class="text-secondary ml-2 fa fa-trash mr-1" title="{{ __('Indisponível') }}"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">{{ __('Nenhum fornecedor registado') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $suppliers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('foot-scripts')
@endsection

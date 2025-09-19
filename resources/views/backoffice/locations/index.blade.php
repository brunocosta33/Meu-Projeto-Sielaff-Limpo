@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Localizações') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="card">
    <div class="card-body">
        <div class="float-right mb-3">
            <a href="{{ route('backoffice.locations.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> {{ __('Nova Localização') }}
            </a>
        </div>

        <h5 class="card-title">{{ __('Lista de Localizações') }}</h5>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Tipo') }}</th>
                    <th>{{ __('Código') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($locations as $loc)
                <tr>
                    <td>{{ $loc->id }}</td>
                    <td>{{ $loc->nome }}</td>
                    <td>{{ ucfirst($loc->tipo) }}</td>
                    <td>{{ $loc->codigo }}</td>
                    <td>
                        <a href="{{ route('backoffice.locations.edit',$loc->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                        <form action="{{ route('backoffice.locations.destroy',$loc->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem a certeza?')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">{{ __('Sem localizações registadas.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

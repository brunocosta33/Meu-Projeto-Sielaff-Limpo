@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Peças (Items)') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="card">
    <div class="card-body">
        <div class="float-right mb-3">
            <a href="{{ route('backoffice.items.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> {{ __('Nova Peça') }}
            </a>
        </div>

        <h5 class="card-title">{{ __('Lista de Peças') }}</h5>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Referência') }}</th>
                    <th>{{ __('Descrição') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->nome }}</td>
                    <td>{{ $item->referencia }}</td>
                    <td>{{ Str::limit($item->descricao, 40) }}</td>
                    <td>
                        <a href="{{ route('backoffice.items.edit',$item->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                        <form action="{{ route('backoffice.items.destroy',$item->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem a certeza?')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">{{ __('Sem peças registadas.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

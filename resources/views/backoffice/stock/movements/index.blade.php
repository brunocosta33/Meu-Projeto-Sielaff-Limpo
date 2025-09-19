@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Movimentos de Stock') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="card">
    <div class="card-body">
        <div class="float-right mb-3">
            <a href="{{ route('backoffice.stock.movements.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> {{ __('Novo Movimento') }}
            </a>
        </div>

        <h5 class="card-title">{{ __('Lista de Movimentos de Stock') }}</h5>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{{ __('Data') }}</th>
                    <th>{{ __('Peça') }}</th>
                    <th>{{ __('Quantidade') }}</th>
                    <th>{{ __('Origem') }}</th>
                    <th>{{ __('Destino') }}</th>
                    <th>{{ __('Tipo') }}</th>
                    <th>{{ __('Utilizador') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->item->nome }} ({{ $m->item->referencia }})</td>
                    <td>{{ $m->quantidade }}</td>
                    <td>{{ $m->origem->nome }}</td>
                    <td>{{ $m->destino->nome }}</td>
                    <td><span class="badge badge-info">{{ ucfirst($m->tipo_movimento) }}</span></td>
                    <td>{{ $m->user->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">{{ __('Sem movimentos registados.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $movements->links() }}
    </div>
</div>
@endsection

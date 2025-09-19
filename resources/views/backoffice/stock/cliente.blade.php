@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Stock do Cliente') }}</title>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Stock do Cliente: ') . $location->nome }}</h5>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{{ __('Peça') }}</th>
                    <th>{{ __('Referência') }}</th>
                    <th>{{ __('Quantidade') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stock as $s)
                <tr>
                    <td>{{ $s->item->nome }}</td>
                    <td>{{ $s->item->referencia }}</td>
                    <td>{{ $s->quantidade }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">{{ __('Sem stock registado.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

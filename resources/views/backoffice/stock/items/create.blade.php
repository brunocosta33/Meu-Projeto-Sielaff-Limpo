@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Nova Peça') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col-xl-8">
        <form method="POST" action="{{ route('backoffice.stock.items.store') }}">
            @csrf
            @include('backoffice.stock.items.partials.form', ['isEdit' => false])
        </form>
    </div>
</div>
@endsection

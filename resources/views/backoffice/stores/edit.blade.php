@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Loja') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col-xl-10">
        {!! Form::open(['route' => ['backoffice.stores.update', $store->id]]) !!}
        {{ csrf_field() }}
        @include('backoffice.stores.partials.form')
        {!! Form::close() !!}
    </div>
</div>
@endsection

@section('foot-scripts')
@include('backoffice.stores.partials.form_scripts')
@endsection

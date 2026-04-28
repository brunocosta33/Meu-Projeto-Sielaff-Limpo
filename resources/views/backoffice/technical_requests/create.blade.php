@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Novo Pedido de Assistência') }}</title>
@endsection

@section('head-scripts')
    @include('backoffice.technical_requests.partials.form_styles')
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col-xl-10">
        {!! Form::open(['route' => ['backoffice.technical_requests.store'], 'files' => true]) !!}
        {{ csrf_field() }}
        @include('backoffice.technical_requests.partials.form')
        {!! Form::close() !!}
    </div>
</div>
@endsection

@section('foot-scripts')
@include('backoffice.technical_requests.partials.form_scripts')
@endsection

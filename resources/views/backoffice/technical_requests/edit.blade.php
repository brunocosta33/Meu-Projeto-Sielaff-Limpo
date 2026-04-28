@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Editar Pedido de Assistência') }}</title>
@endsection

@section('head-scripts')
    @include('backoffice.technical_requests.partials.form_styles')
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col-xl-10">
        {!! Form::model($technicalRequest, ['route' => ['backoffice.technical_requests.update', $technicalRequest->id], 'method' => 'PUT', 'files' => true]) !!}
        {{ csrf_field() }}
        @if(request('return_url'))
            <input type="hidden" name="return_url" value="{{ request('return_url') }}">
        @endif
        @include('backoffice.technical_requests.partials.form')
        {!! Form::close() !!}

        @foreach($technicalRequest->files as $file)
            <form id="technical-request-file-delete-{{ $file->id }}" method="POST" action="{{ route('backoffice.technical_requests.files.delete', $file->id) }}" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
</div>
@endsection

@section('foot-scripts')
@include('backoffice.technical_requests.partials.form_scripts')
@endsection

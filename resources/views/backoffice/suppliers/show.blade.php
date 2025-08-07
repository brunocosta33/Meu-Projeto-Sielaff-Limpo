@extends('layouts.backoffice_master')

@section('head-meta')

    <title>{{ str_replace('.', ' ', config('app.name')) }} - {{ __('Dados do Contacto') }}</title>
    <link rel="stylesheet" href="{{URL::asset('css/style.css')}}">

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
                <div class="row">
                    <div class="col-5">
                        <h5 class="card-title" style="margin-bottom: 30px;">{{ __('Dados do Cliente') }}</h5>
                    </div>
                </div>
                <div>
                    <div>
                        <div class="form-group">
                            {!! Form::label('name', __('Nome do Cliente')) !!}
                            <div class="textwrapper-evaluation-text-area">
                                {{$client->name}}
                            </div>
                        </div>
                        <div>
                            <a class="btn btn-outline-secondary" href="{!! URL::previous() !!}">
                                <span class="fa fa-arrow-left"></span>
                                &nbsp; {{ __('Voltar') }}
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>



@endsection

@section('foot-scripts')

@endsection

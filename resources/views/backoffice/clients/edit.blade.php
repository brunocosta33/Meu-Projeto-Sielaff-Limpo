@extends('layouts.backoffice_master')

@section('head-meta')

    <title>{{ str_replace('.', ' ', config('app.name')) }} - Dados do Contacto</title>
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
                        <h5 class="card-title" style="margin-bottom: 30px;">Editar Contacto</h5>
                    </div>
                </div>
                <div>
                    <div>
                        {!! Form::open(['route' => ['backoffice.clients.update',$client->id]]) !!}
                        {{ csrf_field() }}

                            <div class="form-group">
                                <label>{{ __('Nome do Cliente') }}</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ $client->name }}" required>
                            </div>
                        <div>
                            <a class="btn btn-outline-secondary" href="{!! URL::previous() !!}">
                                <span class="fa fa-arrow-left"></span>
                                &nbsp; Voltar
                            </a>
                            {!! Form::button('<i class="fa fa-save"></i> Gravar', array('type' => 'submit', 'class' => 'btn btn-outline-secondary')) !!}
                            {!! Form::close() !!}
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

@extends('layouts.backoffice_master')

@section('head-meta')

    <title>{{ str_replace('.', ' ', config('app.name')) }} - Editar do Contacto</title>
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
                        <h5 class="card-title" style="margin-bottom: 30px;">Editar do Contacto</h5>
                    </div>
                </div>
                <div>
                    <div>
                        {!! Form::open(['route' => ['backoffice.contacts.update',$contacts->id]]) !!}
                        {{ csrf_field() }}
                        <div class="form-group">
                            {!! Form::label('client_id', 'Select Client:', ['class' => 'control-label']) !!}
                            {!! Form::select('client_id', $contacts->client->pluck('name', 'id'), $contacts->client_id, ['class' => 'form-control']) !!}

                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>{{ __('Nome do Contato') }}</label>
                                <input type="text" name="name" value="{{$contacts->name}}" class="form-control"
                                     required>
                                @error('name')
                                     <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>{{ __('Email do Contato') }}</label>
                                <input type="text" name="email" value="{{$contacts->email}}"  class="form-control"
                                     required>
                                @error('email')
                                     <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>{{ __('Telemovel/Fixo') }}</label>
                                <input type="text" name="telephone" value="{{$contacts->telephone}}"  class="form-control">
                                @error('telephone')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
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

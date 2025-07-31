@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ str_replace('.', ' ', config('app.name')) }} - {{__('Create Permission')}}</title>
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
                            <h5 class="card-title mb-3">{{__('New Permission')}}</h5>
                        </div>
                    </div>
                    <div>
                        {!! Form::open(['route' => 'backoffice.permissions.store']) !!}
                        {{ csrf_field() }}
                        <div class="custom-control custom-switch">                        
                            {!! Form::checkbox('is_active', '1', true, ['class' => 'form-control', 'class' => 'custom-control-input', 'id'=>'customSwitches']) !!}
                            {!! Form::label('customSwitches', __('Active'), ['class' => 'form-control', 'class' => 'custom-control-label']) !!}
                        </div>
                        <br>
                        <div class="form-group">
                            <label>{{__('Name')}}</label>
                            <input type="text" name="name" class="form-control {{ $errors->has('name') ? "is-invalid" : "" }}">
                            <small class="text-muted fa-pull-right">{{__('max. 255')}}</small>
                            <small class="text-danger">{{ $errors->first('name') }}</small>
                        </div>
                        <div class="form-group">
                            {!! Form::label('msg', __('Description')) !!}
                            {!! Form::textarea('msg',  null, ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group mb-5">
                            <label>{{__('Display Name')}}</label>
                            <input type="text" name="display_name" class="form-control {{ $errors->has('display_name') ? "is-invalid" : "" }}">
                            <small class="text-muted fa-pull-right">{{__('max. 255')}}</small>
                            <small class="text-danger">{{ $errors->first('display_name') }}</small>
                        </div>
                        <div style="display:flex; justify-content: flex-end">
                            <a class="btn btn-outline-secondary float-right" href="{{ route('backoffice.permissions.index') }}"><span class="fa fa-arrow-left"></span>&nbsp; {{__('Back')}}</a>
                            {!! Form::button('<i class="fa fa-save"></i> &nbsp' .__('Save'), array('type' => 'submit', 'class' => 'btn btn-outline-secondary float-right', 'style' => 'margin-left: 5px;')); !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('foot-scripts')
@endsection
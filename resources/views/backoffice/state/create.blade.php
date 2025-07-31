@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ str_replace('.', ' ', config('app.name')) }} - {{ __('Create State') }}</title>

    <link rel="stylesheet" href="{{ URL::asset('css/style.css') }}">

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/datasheetsimage.js') }}"></script>
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
                        <div class="col d-flex justify-content-end">
                            <div class="dropdown">
                                @include('includes.language-switch')
                              </ul>
                            </div>
                          </div>
                    </div>
                    <div class="row">
                        <div class="col-5">
                            <h5 class="card-title mb-3">{{ __('New State') }}</h5>
                        </div>
                    </div>
                    <div>
                        {!! Form::open(['route' => 'backoffice.state.store']) !!}
                        {{ csrf_field() }}
                        <div class="form-group">
                                <div class="row" style="height: 30px"> </div>
                                {!! Form::label('name', __('Name')) !!}
                                <div class="row" style="height: 15px"> </div>
                                {!! Form::input('text', 'name', null, ['class' => 'form-control']) !!}

                                <div class="row" style="height: 30px"> </div>
                                {!! Form::label('desc', __('Description')) !!}
                                <div class="row" style="height: 15px"> </div>
                                {!! Form::input('text', 'desc', null, ['class' => 'form-control']) !!}

                                <div class="row" style="height: 30px"> </div>

                        </div>
                        <div style="display:flex; justify-content: flex-end">
                            <a class="btn btn-outline-secondary float-right"
                                href="{{ route('backoffice.state.index') }}"><span
                                    class="fa fa-arrow-left"></span>&nbsp; {{ __('Back') }}</a>
                            {!! Form::button('<i class="fa fa-save"></i> &nbsp' . __('Save'), [
                                'type' => 'submit',
                                'class' => 'btn btn-outline-secondary float-right',
                                'style' => 'margin-left: 5px;',
                            ]) !!}
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

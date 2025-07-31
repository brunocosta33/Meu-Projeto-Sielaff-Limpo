@extends('layouts.backoffice_master')
@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} - {{$user->name}} - {{__('Profile')}}</title>
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
                        <div class="col">
                            <h4 class="card-title mb-0">{{__('Profile')}}:&nbsp;{{$user->name}}</h4>
                        </div>                    
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col mt-4">
                            <dl class="row">
                                <dt class="col-3">{{__('Name')}}</dt>
                                <dd class="col-9">{{$user->name}}</dd>
                                <dt class="col-3">{{__('Email')}}</dt>
                                <dd class="col-9">{{$user->email}}</dd>
                                <dt class="col-3">{{__('Language')}}</dt>
                                <dd class="d-flex">
                                    <form action="{{ route('user.locale', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <select class="form-control col-12" name="lang" onchange='this.form.submit()'>
                                            <option value="">{{ $user->lang }}</option>
                                            @if ($user->lang == "pt")
                                                <option value="en">en</option>
                                            @else
                                                <option value="pt">pt</option>
                                            @endif
                                        </select>
                                        <noscript><input type="submit" value="Submit"></noscript>
                                    </form>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mb-3"> 
                        <button onclick="showChangePassword()"  class="btn btn-outline-danger" >
                            {{ __('Change Password') }}
                        </button>
                    </div>
                    <div class="changePassword" @if($errors->isEmpty()) style="display: none" @endif>
                        <form id="form-change-password" role="form" method="POST" action="{{ route('user.password', $user->id) }}" novalidate class="form-horizontal">
                            @csrf
                            @method('PUT')
                            <div>             
                                <label for="current-password" class="control-label">{{ __('Current Password') }}</label>
                                <div>
                                    <div class="form-group">
                                        <input type="hidden"> 
                                        <input type="password" class="form-control {{ $errors->has('current') ? "is-invalid" : "" }}" id="current" name="current" placeholder="{{ __('Type your current password') }}">
                                        <small class="text-danger">{{ $errors->first('current') }}</small>
                                    </div>
                                </div>
                                <label for="password" class="control-label">{{ __('New Password') }}</label>
                                <div>
                                    <div class="form-group">
                                        <input type="password" class="form-control {{ $errors->has('password') ? "is-invalid" : "" }}" id="password" name="password" placeholder="{{ __('Type the new password') }}">
                                        <small class="text-danger">{{ $errors->first('password') }}</small>
                                    </div>
                                </div>
                                <label for="password_confirmation" class="control-label">{{ __('Password Confirmation') }}</label>
                                <div>
                                    <div class="form-group">
                                        <input type="password" class="form-control {{ $errors->has('password_confirmation') ? "is-invalid" : "" }}" id="password_confirmation" name="password_confirmation" placeholder="{{ __('Type the new password again') }}">
                                        <small class="text-danger">{{ $errors->first('password_confirmation') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="float-right">
                                <button type="submit" class="btn btn-outline-secondary"><i class="fa fa-save"></i>&nbsp&nbsp{{ __('Save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    function showChangePassword(){
        $(".changePassword").css("display", "");
       
    }
</script>

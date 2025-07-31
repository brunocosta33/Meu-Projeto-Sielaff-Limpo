@extends('layouts.backoffice_master')

@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} {{__('Profile')}} - {{$user->name}}</title>
@endsection

@section('head-script')
@endsection

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h3 class="card-title mb-0">{{$user->name}}</h3>                            
                        </div>                    
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col mt-4">
                            <dl class="row">
                                <dt class="col-3">Nome</dt>
                                <dd class="col-9">{{$user->name}}</dd>
                                <dt class="col-3">Email</dt>
                                <dd class="col-9">{{$user->email}}</dd>
                            </dl>
                        </div>
                    </div>
                    <hr>
                    <div class="ml-4">
                        <a href="{{route('dashboard.profile.change-password')}}" class="btn btn-danger">Mudar Password</a>                      
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('foot-script')
@endsection
@extends('layouts.backoffice_master_client')

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
                                <dt class="col-3">Nome</dt>
                                <dd class="col-9">{{$user->name}}</dd>
                                <dt class="col-3">{{__('Email')}}</dt>
                                <dd class="col-9">{{$user->email}}</dd>
                                {{--<dt class="col-3">{{__('Language')}}</dt>
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
                                </dd>--}}
                            </dl>
                        </div>
                    </div>
                    <div> 
                        <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#exampleModal">
                            Mudar Password
                        </button>
                    </div>
                        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Mudar Password</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="form-change-password" role="form" method="POST" action="{{ route('user.password', $user->id) }}" novalidate class="form-horizontal">
                                        @csrf
                                        @method('PUT')
                                        <div>             
                                            <label for="current-password" class="control-label">Password Atual</label>
                                            <div>
                                                <div class="form-group">
                                                    <input type="hidden"> 
                                                    <input type="password" class="form-control" id="current" name="current" placeholder="{{ __('Enter your password') }}">
                                                </div>
                                            </div>
                                            <label for="password" class="control-label">Nova Password</label>
                                            <div>
                                                <div class="form-group">
                                                    <input type="password" class="form-control {{ $errors->has('password') ? "is-invalid" : "" }}" id="password" name="password" placeholder="Digite a nova password">
                                                    <small class="text-danger">{{ $errors->first('password') }}</small>
                                                </div>
                                            </div>
                                            <label for="password_confirmation" class="control-label">Confirmação da Password</label>
                                            <div>
                                                <div class="form-group">
                                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Digite novamente a nova password">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="float-right">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                            <button type="submit" class="btn btn-danger">Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('foot-script')
@endsection
@extends('layouts.backoffice_master_nm')

@section('head-meta')
    <style>
        .inner-addon { 
            position: relative; 
        }

        /* style icon */
        .inner-addon .fas {
            position: absolute;
            padding: 15px;
            pointer-events: none;
        }

        /* align icon */
        .left-addon .fas  { left:  0px;}
        .right-addon .fas { right: 0px;}

        /* add padding  */
        .left-addon input  { padding-left:  40px; }
        .right-addon input { padding-right: 30px; }

        #login .fa-at, #login .fa-mobile-alt {
            color: gray;
            margin: 5px;
            display: inline-block;
            border-radius: 60px;
            box-shadow: 0px 0px 2px #888;
            padding: 0.5em 0.6em;
        }

        #login {
            background-repeat: no-repeat;
            background-size: cover;
        }
	</style>
@endsection

@section('content')
    <section id="login">
        <div class="wrapper fadeInDown">
            <div id="formContent">
                <div class="fadeIn first">
                    <h3 class="py-4">{{ __('Reset Password') }}</h3>
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="input-group">
                            <div class="inner-addon left-addon">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="inner-addon left-addon">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="inner-addon left-addon">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                                <input id="password-confirm" type="password" class="form-control " name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Reset Password') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
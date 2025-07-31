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
        <div class="wrapper fadeInDown" style="height: 100vh">
            <div id="formContent">
                <div class="fadeIn first">
                    <h3 class="py-4">{{ __('Reset Password') }}</h3>
                   
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>{{ __('Please enter your email address so we can send you a password reset email.') }}</p>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="input-group">
                            <div class="inner-addon left-addon">
				                <i class="fas fa-user" aria-hidden="true"></i>
                                <input id="email" type="email" style="background: transparent; border: none;" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Send Email') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

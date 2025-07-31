@extends('layouts.backoffice_master_nm')

<?php
use App\Models\Base; 
?>

@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} - Login</title>
	
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

		@if(!empty(Base::bg()->url))
			#login {
			background-image: url({{ url( Base::bg()->url  . Base::bg()->file) }}) !important;
			background-repeat: no-repeat;
			background-size: cover;
			}
		@endif
		
		#login a {
			color: #666 !important;
			display: inline-block;
			text-decoration: none;
			font-weight: 400;
		}

		#content {
			padding: 0px !important;
		}
	</style>

@endsection

@section('content')
	<section id="login" class="login">
		<div class="wrapper fadeInDown" style="height: 100vh">
			<div class="my-3">
				@include('flash::message')
			</div>
			<div id="formContent">
				<div class="fadeIn first">
					<div style="margin: 20px;">	
						@if(!empty(Base::logo()->url))
							<img id="img_logo" src="{{ url( Base::logo()->url  . Base::logo()->file) }}" style="max-height: 15vh;" />
						@endif		
					</div>		
				</div>
				<form method="POST" action="{{ route('login') }}" id="loginform" name="loginform">
					@csrf
					<div class="input-group">
						<div class="inner-addon left-addon">
							<i class="fas fa-user" aria-hidden="true"></i>
							<input id="email" style="background: transparent; border: none;" type="email" style="background-color: none;" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
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
							<input id="password" type="password" style="background: transparent; border: none;"  class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
							@error('password')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>
					<div class="mt-3">
						<button type="submit" class="fadeIn fourth" id="login1">
							{{ __('Login') }}
						</button>
					</div>
				</form>
				<div id="formFooter">
					@if (Route::has('password.request'))
						<a class="btn btn-link" id="passwordReset" href="{{ route('password.request') }}">
						{{ __('Forgot your password?') }}
						</a>
					@endif					
				</div>
			</div>
		</div>
	</section>
@endsection

@section('foot-scripts')	
@endsection

@extends('layouts.backoffice_master')

@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} - {{__('Change Password')}}</title>
@endsection

@section('head-scripts')
@endsection

@section('content')
	<div class="row">
		@include('flash::message')
	</div>
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title mb-3">{{__('Change Password')}}</h5>
					<div id="edit-form">
						<form action="{{ route('dashboard.users.passstore') }}" method="POST">
							@csrf
							<div class="row">
								<div class="form-group col-6">
									<label>{{__('Name')}}</label>								
									<input type="hidden" name="id" class="form-control" readonly  value="{{ $user->id }}">
									<input type="text" name="name" class="form-control" readonly  value="{{ $user->name }}">
								</div>
							</div>
							<div class="row">
								<div class="form-group col-6 ">
									<label>{{__('Password')}}</label>
									<input type="password" name="password" class="form-control {{ $errors->has('password') ? "is-invalid" : "" }}">
									<small class="text-danger">{{ $errors->first('password') }}</small>
								</div>
							</div>
							<br>
							<div style="display:flex; justify-content: flex-end">
								<a class="btn btn-outline-secondary" href="{{ route('backoffice.users.index') }}"><span class="fa fa-arrow-left"></span>&nbsp; {{__('Back')}}</a>
								<button class="btn btn-outline-secondary" type="submit" style="margin-left:5px;"><i class="fa fa-save"></i>&nbsp; {{__('Save')}}</button>
							</div>
						</form>						
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('foot-scripts')
@endsection
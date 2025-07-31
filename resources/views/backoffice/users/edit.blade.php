@extends('layouts.backoffice_master')

@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} - {{__('Edit User')}}</title>
@endsection

@section('head-scripts')
@endsection

@section('content')
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title mb-3">{{__('Edit User')}}</h5>
					<form action="{{ route('dashboard.users.update', $user->id) }}" method="POST">
						@csrf
						@method('PUT')
						<div class="row">
							<div class="form-group col-12">
								<div class="custom-control custom-switch">
									{!! Form::checkbox('is_active', '1', $user->is_active, ['class' => 'form-control', 'class' => 'custom-control-input', 'id'=>'customSwitches']) !!}
									{!! Form::label('customSwitches', __('Active'), ['class' => 'form-control', 'class' => 'custom-control-label']) !!}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-6">
								<label>{{__('Name')}}</label>
								<input type="text" name="name" class="form-control {{ $errors->has('name') ? "is-invalid" : "" }}" value="{{ $user->name }}">
								<small class="text-muted fa-pull-right">{{__('max. 255')}}</small>
								<small class="text-danger">{{ $errors->first('name') }}</small>
							</div>
							<div class="form-group col-6">
								<label>Email</label>
								<input type="text" name="email" class="form-control {{ $errors->has('email') ? "is-invalid" : "" }}" value="{{ $user->email }}">
								<small class="text-muted fa-pull-right">{{__('max. 255')}}</small>
								<small class="text-danger">{{ $errors->first('email') }}</small>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-4">
								<label>{{__('Role')}}</label>
								<select class="selectpicker form-control" {{ $errors->has('role') ? "is-invalid" : "" }} name="role">
									<option value="{{ $user->role[0]->id }}">{{ $user->role[0]->display_name }}</option>
									@forelse ($roles as $role)
										@if ($role->id != $user->role[0]->id)
											<option value="{{ $role->id }}">{{ $role->display_name }}</option>
										@endif
									@empty
										{{__('No registered roles')}}
									@endforelse
								</select>
								<small class="text-danger">{{ $errors->first('role') }}</small>
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
@endsection

@section('foot-scripts')
@endsection

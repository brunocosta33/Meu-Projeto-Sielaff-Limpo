@extends('layouts.backoffice_master')

@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} - {{__('Roles')}}</title>
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
					<div class="float-right">
						<a href="{{ route('backoffice.roles.create') }}" class="collapsed" data-parent="#sidebar">
							<button type="submit" class="btn btn-outline-secondary" title={{__('Create')}}><span class="fa fa-plus"></span>&nbsp;&nbsp;{{__('Create')}}</button>
						</a>
					</div>
					<h5 class="card-title">{{__('Roles')}}</h5>
					<br>
					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th scope="col">ID</th>
									<th scope="col">{{__('Name')}}</th>
									<th scope="col">{{__('Display Name')}}</th>									
									<th scope="col"></th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								@forelse($roles as $role)
									<tr scope="row">
										<td>{{ $role->id }}</td>
										<td>{{ $role->name }}</td>
										<td>{{ $role->display_name }}</td>										
										<td class="text-right">
											<a class="ml-2" href="{{ route('backoffice.roles.edit', $role->id) }}"><i class="fa fa-edit mr-1" title={{__('Edit')}}></i></a>
										</td>
										@if ($role->name != "administrador")
											<td>
												<a class="ml-2" href="{{ route('backoffice.roles.delete', $role->id) }}" onclick="return confirm('{{__('Are you sure?')}}')"><i class="fa fa-trash mr-1" title={{__('Delete')}}></i></a>
											</td>
												@else
											<td>
												<a href=""><i class="text-secondary ml-2 fa fa-trash mr-1" title={{__('Unavailable')}}></i></a>
											</td>
										@endif
									</tr>
								@empty
									<td>{{__('No registered roles')}}</td>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('foot-scripts')
@endsection
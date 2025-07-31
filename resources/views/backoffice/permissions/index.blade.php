@extends('layouts.backoffice_master')

@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} - {{__('Permissions')}}</title>
@endsection

@section('head-script')
@endsection

@section('content')
	<div class="row mb-3">
		<div class="col">
			<div class="card">
				<div class="card-body" style="text-align:center; color: #761b18; background-color:#f9d6d5">
					{{__('Warning! Do not change or delete any permissions without contacting your system administrator!')}}
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		@include('flash::message')
	</div>
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<div class="float-right">
						<a href="{{ route('backoffice.permissions.create') }}" class="collapsed" data-parent="#sidebar">
							<button type="submit" class="btn btn-outline-secondary" title={{__('Create')}}><span class="fa fa-plus"></span>&nbsp;&nbsp;{{__('Create')}}</button>
						</a>
					</div>
					<h5 class="card-title">{{__('Permissions')}}</h5>
					<br>
					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th scope="col">ID</th>
									<th scope="col">{{__('Name')}}</th>
									<th scope="col">{{__('Display Name')}}</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								@forelse($permissions as $permission)
									<tr scope="row">
										<td>{{ $permission->id }}</td>
										<td>{{ $permission->name }}</td>
										<td>{{ $permission->display_name }}</td>										
										<td class="text-right">
											<a class="ml-2" href="{{ route('backoffice.permissions.edit', $permission->id) }}"><i class="fa fa-edit mr-1" title={{__('Edit')}}></i></a>
											<a class="ml-2" href="{{ route('backoffice.permissions.delete', $permission->id) }}" onclick="return confirm('{{__('Are you sure?')}}')"><i class="fa fa-trash mr-1" title={{__('Delete')}}></i></a>
										</td>
									</tr>
								@empty
									<td>{{__('No registered permissions')}}</td>
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
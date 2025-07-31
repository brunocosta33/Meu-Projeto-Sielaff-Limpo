@extends('layouts.backoffice_master')

@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} - {{__('Users Activities')}}</title>
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
						<div class="col-5">
							<h5 class="card-title">{{__('Users Activities')}}</h5>
						</div>
					</div>
					<br>
					<div class="table-responsive ">
						<table class="table table-striped">
							<thead>
								<tr>
									<th scope="col">ID</th>
									<th scope="col">{{__('User')}}</th>
									<th scope="col">IP</th>
									<th scope="col">{{__('Login Date and Time')}}</th>
								</tr>
							</thead>
							<tbody>
								@foreach($loginActivities as $loginActivity)
									<tr scope="row">
										<td>{{ $loginActivity->id }}</td>
										<td>{{ $loginActivity->name }}</td>
										<td>{{ $loginActivity->ip_address }}</td>
										<td>{{ $loginActivity->created_at }}</td>
									</tr>								
								@endforeach
							</tbody>
						</table>
						{!! $loginActivities->render() !!}
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('foot-scripts')
@endsection
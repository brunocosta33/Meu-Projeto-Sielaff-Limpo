@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ str_replace('.', ' ', config('app.name')) }} - Dashboard</title>
@endsection

@section('head-scripts')
@endsection

@section('content')
<div class="row">
	<div class="col" style="padding:0">
		@include('flash::message')
	</div>
</div>
<div class="row">
	<div class="col">
	<div class="col-md-12">
			<div class="card mb-2">
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h5 class="card-title">Dashboard</h5>
						</div>
					</div>
			</div>
		</div>
	</div>
	</div>
</div>


@endsection

@section('foot-scripts')


@endsection
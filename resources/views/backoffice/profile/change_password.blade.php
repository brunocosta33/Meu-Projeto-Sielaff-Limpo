@extends('layouts.backoffice_master')

@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} - {{ __('Change password') }}</title>
@endsection

@section('head-script')
@endsection

@section('content')
	<div class="row">
		<div class="col">
			<div id="change-password">
				{!! form($form) !!}
			</div>
		</div>
	</div>
@endsection

@section('foot-script')
@endsection
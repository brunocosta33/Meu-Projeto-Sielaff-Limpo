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
					<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
						<h5 class="card-title mb-2">{{__('Users Activities')}} <small class="text-muted">— {{ __('entradas e saídas') }}</small></h5>
						<form method="GET" class="form-inline mb-2">
							<div class="btn-group mr-2" role="group">
								<a href="{{ request()->fullUrlWithQuery(['tipo' => null, 'page' => null]) }}" class="btn btn-sm {{ request('tipo') ? 'btn-outline-secondary' : 'btn-secondary' }}">{{ __('Todos') }}</a>
								<a href="{{ request()->fullUrlWithQuery(['tipo' => 'login', 'page' => null]) }}" class="btn btn-sm {{ request('tipo') === 'login' ? 'btn-success' : 'btn-outline-success' }}"><i class="fa fa-sign-in-alt"></i> {{ __('Entradas') }}</a>
								<a href="{{ request()->fullUrlWithQuery(['tipo' => 'logout', 'page' => null]) }}" class="btn btn-sm {{ request('tipo') === 'logout' ? 'btn-danger' : 'btn-outline-danger' }}"><i class="fa fa-sign-out-alt"></i> {{ __('Saídas') }}</a>
							</div>
							@if(request('tipo'))<input type="hidden" name="tipo" value="{{ request('tipo') }}">@endif
							<select name="user_id" class="form-control form-control-sm" onchange="this.form.submit()">
								<option value="">{{ __('Todos os utilizadores') }}</option>
								@foreach($users as $user)
									<option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
								@endforeach
							</select>
						</form>
					</div>
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th scope="col">{{ __('Tipo') }}</th>
									<th scope="col">{{__('User')}}</th>
									<th scope="col">{{ __('Data e hora') }}</th>
									<th scope="col">IP</th>
								</tr>
							</thead>
							<tbody>
								@forelse($loginActivities as $loginActivity)
									<tr scope="row">
										<td>
											@if($loginActivity->tipo === 'logout')
												<span class="badge badge-danger" style="font-size: .85rem"><i class="fa fa-sign-out-alt"></i> {{ __('Saída') }}</span>
											@else
												<span class="badge badge-success" style="font-size: .85rem"><i class="fa fa-sign-in-alt"></i> {{ __('Entrada') }}</span>
											@endif
										</td>
										<td>{{ $loginActivity->name }}</td>
										<td class="text-nowrap">{{ $loginActivity->created_at ? \Carbon\Carbon::parse($loginActivity->created_at)->format('d/m/Y H:i:s') : '—' }}</td>
										<td>{{ $loginActivity->ip_address }}</td>
									</tr>
								@empty
									<tr><td colspan="4" class="text-muted">{{ __('Sem registos.') }}</td></tr>
								@endforelse
							</tbody>
						</table>
						{!! $loginActivities->render() !!}
					</div>
					<small class="text-muted">
						<i class="fa fa-info-circle"></i> {{ __('A saída só fica registada quando a pessoa carrega em "Logout". Se fechar o browser ou a sessão expirar, não há registo de saída.') }}
					</small>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('foot-scripts')
@endsection
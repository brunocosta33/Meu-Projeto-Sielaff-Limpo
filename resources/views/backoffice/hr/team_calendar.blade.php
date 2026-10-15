@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Horário da Equipa') }}</title>
@endsection

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <h4 class="mb-1"><i class="fas fa-calendar-alt text-primary"></i> {{ __('Horário da Equipa') }}</h4>
    <span class="text-muted small"><i class="fa fa-eye"></i> {{ __('Turnos da Hotline e do Piquete, férias e ausências de toda a equipa (só consulta).') }}</span>
</div>

@include('backoffice.partials.team_calendar', ['taskEvents' => []])
@endsection

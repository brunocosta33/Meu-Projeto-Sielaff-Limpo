@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Editar Instalação</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Editar Instalação</h5>

                {!! Form::model($installation, ['route' => ['backoffice.installations.update', $installation->id], 'method' => 'PUT']) !!}
                {{ csrf_field() }}

                @include('backoffice.installations._form')

                <button type="submit" class="btn btn-primary">Atualizar</button>
                <a href="{{ route('backoffice.installations.index') }}" class="btn btn-secondary">Cancelar</a>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
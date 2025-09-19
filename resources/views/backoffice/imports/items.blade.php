@extends('layouts.backoffice_master')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Importar Peças do Excel') }}</h5>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form action="{{ route('backoffice.import.items.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <input type="file" name="file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Importar</button>
        </form>
    </div>
</div>
@endsection

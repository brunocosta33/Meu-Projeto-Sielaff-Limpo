@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ str_replace('.', ' ', config('app.name')) }} - Visualizar Vinho</title>
    <link rel="stylesheet" href="{{ URL::asset('css/style.css') }}">
    <script type="text/javascript" src="{{ URL::asset('js/datasheetsimage.js') }}"></script>
@endsection

@section('head-script')
@endsection

@section('content')

<div class="card">
        <div class="card-body">
            <img src="{{ url($img->url . $img->file) }}" alt="" width="1000" height="450" class="w-100">
        </div>
    </div>
<div class="card">
    <div class="card-body">
        <a class="btn btn-outline-secondary" href="{!! URL::previous() !!}">
        <span class="fa fa-arrow-left"></span>
         &nbsp; Voltar
        </a>
            </div>
        </div>
    </div>
</div>
</div>


@endsection

@section('foot-scripts')
@endsection

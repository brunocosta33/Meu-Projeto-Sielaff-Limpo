@extends('layouts.backoffice_master')

@section('head-meta')

    <title>{{ str_replace('.', ' ', config('app.name')) }} - Email de Boas-Vindas</title>
    <link rel="stylesheet" href="{{URL::asset('css/style.css')}}">

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
                        <h5 class="card-title" style="margin-bottom: 30px;">Definir Email de Boas-Vindas</h5>
                    </div>
                </div>

                <div>
                    <div>
                        {!! Form::open(['route' => ['backoffice.returnText.store']]) !!}
                        {{ csrf_field() }}
                        <div class="form-group">
                            {!! Form::label('return_text', 'Email PT') !!}
                            <div class="textwrapper-evaluation-text-area">
                                <textarea name="return_text" id="return_text" class="evaluation-text-area" cols="10" rows="5"> {!! $text->first() ? $text[0]->response_text : '' !!}</textarea>
                            </div>
                        </div>
                        <div class="row" style="height: 50px;"></div>
                        <div class="form-group">
                            {!! Form::label('return_text_en', 'Email EN') !!}
                            <div class="textwrapper-evaluation-text-area">
                                <textarea name="return_text_en" id="return_text_en" class="evaluation-text-area" cols="10" rows="5"> {!! $text->first() ? $text[0]->response_text_en : '' !!}</textarea>
                            </div>
                        </div>
                        <div>
                            <a class="btn btn-outline-secondary" href="{!! URL::previous() !!}">
                                <span class="fa fa-arrow-left"></span>
                                &nbsp; Voltar
                            </a>
                            {!! Form::button('<i class="fa fa-save"></i> Gravar', array('type' => 'submit', 'class' => 'btn btn-outline-secondary')) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>



@endsection

@section('foot-scripts')
<script src="{{ asset('js/summernote.js') }}"></script>
<script>
    $(document).ready(function() {
            $('#return_text').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],

                ]
            });
        });
        $(document).ready(function() {
            $('#return_text_en').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],

                ]
            });
        });
</script>



@endsection

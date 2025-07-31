@extends('layouts.backoffice_master')

@section('head-meta')

    <title>{{ str_replace('.', ' ', config('app.name')) }} - Dados do Contacto</title>
    <link rel="stylesheet" href="{{URL::asset('css/style.css')}}">

@endsection

@section('head-script')

@endsection

@section('content')
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
                        <h5 class="card-title" style="margin-bottom: 30px;">Resposta Alterada</h5>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label for="created_at">Data Alteração</label>
                        <div class="form-control">
                            {{date('d-m-Y', strtotime($lead_hist->created_at))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Estado alterado por:</label>
                        <div class="form-control">
                            {{$lead_hist->reply_by}}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Estado Anterior</label>
                        <div class="form-control">
                            {{$lead_hist->state->name}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="message">Mensagem</label>
                        <div>
                            <div class="textwrapper-evaluation-text-area">
                                {!!$lead_hist->message!!}
                            </div>
                        </div>
                    </div>
                    <hr>

                        <div>
                            <a class="btn btn-outline-secondary" href="{!! URL::previous() !!}">
                                <span class="fa fa-arrow-left"></span>
                                &nbsp; Voltar
                            </a>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('foot-scripts')
<script src="{{ asset('js/summernote.js') }}"></script>

   
@endsection

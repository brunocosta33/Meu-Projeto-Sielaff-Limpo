@extends('layouts.backoffice_master')

@section('head-meta')

    <title>{{ str_replace('.', ' ', config('app.name')) }} - Dados do Contacto</title>
    <link rel="stylesheet" href="{{URL::asset('css/style.css')}}">
    <link href="{{ asset('css/summernote.css') }}" rel="stylesheet">

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
                        <h5 class="card-title" style="margin-bottom: 30px;">Dados do Contacto</h5>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label for="created_at">Data Receção</label>
                        <div class="form-control">
                            {{date('d-m-Y', strtotime($lead->created_at))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Nome</label>
                        <div class="form-control">
                            {{$lead->name}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Assunto</label>
                        <div class="form-control">
                            {{$lead->type}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="relation_id">Nome do Produto Relacionado</label>
                        <div class="form-control">
                            {{ $lead->product ? $lead->product->name_pt : 'Produto não encontrado' }}
                        </div>
                    </div>                             
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="form-control">
                            {{$lead->contact->email}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Telefone</label>
                        <div class="form-control">
                            {{$lead->contact->telephone}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="message">Mensagem</label>
                        <div>
                            <div class="textwrapper-evaluation-text-area">
                                <textarea class="evaluation-text-area" cols="10" rows="8" disabled>{{$lead->message}}</textarea>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row" style="margin-top: 30px;">
                        <div class="col-5">
                            <h5 class="card-title" style="margin-bottom: 30px;">Editar Estado e Resposta</h5>
                        </div>
                    </div>
                    {{--Lead History--}}

                    <div>
                        {!! Form::open(['route' => ['backoffice.leads.update', $lead->id]]) !!}
                        {{ csrf_field() }}
                            <div class="form-group">
                                {!! Form::label('state_id', 'Select State:', ['class' => 'control-label']) !!}
                                {!! Form::select('state_id', $states->pluck('name', 'id'), $lead->state_id, ['class' => 'form-control', 'placeholder' => 'Select a state']) !!}

                            </div>
                            <div class="custom-control custom-switch">
                                {!! Form::checkbox('send_email', '1', false, [
                                    'class' => 'form-control',
                                    'class' => 'custom-control-input',
                                    'id' => 'customSwitches',
                                ]) !!}
                                {!! Form::label('customSwitches', __('Enviar Email de Resposta'), ['class' => 'form-control', 'class' => 'custom-control-label']) !!}
                            </div>
                            <div class="row" style="height: 15px;"></div>
                        <div class="form-group">
                            {!! Form::label('lead_message', 'Resposta') !!}
                            <div class="textwrapper-evaluation-text-area">
                                <textarea name="lead_message" id="lead_message" class="evaluation-text-area" cols="10" rows="5">@if($leadHistory->first()){{$leadHistory[0]->message}}@endif</textarea>
                            </div>
                        </div>
                        <input type="text" name="reply_by" value="{{auth()->user()->name}}" hidden>
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

<br>
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
            <h5 class="card-title" style="margin-bottom: 30px;">Estado de Resposta</h5>
                <div class="table-responsive">
                    <table class="table table-striped " id="table">
                        <thead>
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Name</th>
                            <th scope="col">Data</th>
                            <th scope="col">Estado</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($leadHistory as $lead_hist)
                                <tr scope="row">
                                    <td >{{$lead_hist->id}}</td>
                                    <td >{{$lead_hist->reply_by}}</td>
                                    <td >{{$lead_hist->created_at}}</td>
                                    <td >{{$lead_hist->state->name}}</td>
                                    <td class="text-right">
                                        <a class="ml-2" href="{{ route('backoffice.leadHistory.show', $lead_hist->id) }}"><i
                                        class="fa fa-eye mr-1" title={{ __('Show') }}></i></a>
                                    </td>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<br>
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title" style="margin-bottom: 30px;">Outros Contactos</h5>
                <div class="table-responsive">
                    <table class="table table-striped " id="table">
                        <thead>
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Name</th>
                            <th scope="col">Data</th>
                            <th scope="col">Estado</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($relatedLeads as $relatedLead)
                            @if($lead->id != $relatedLead->id )
                                <tr scope="row">
                                    <td >{{$relatedLead->id}}</td>
                                    <td >{{$relatedLead->name}}</td>
                                    <td >{{$relatedLead->created_at}}</td>
                                    <td >{{$relatedLead->state_id}}</td>
                                    <td class="text-right">
                                        <a class="ml-2" href="{{ route('backoffice.leads.show', $relatedLead->id) }}"><i
                                        class="fa fa-eye mr-1" title={{ __('Show') }}></i></a>
                                    </td>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('foot-scripts')
<script src="{{ asset('js/summernote.js') }}"></script>
<script>
    function setRequired(){
        if(document.getElementById('customSwitches').checked){
            document.getElementById('reply').required = true;
        }
        else{
            document.getElementById('reply').required = false;
        }
    }
</script>
<script>
    $(document).ready(function() {
            $('#lead_message').summernote({
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

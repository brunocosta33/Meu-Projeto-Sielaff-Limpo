@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ str_replace('.', ' ', config('app.name')) }} - {{__('Settings')}}</title>

    <link rel="stylesheet" href="{{URL::asset('css/style.css')}}">

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script type="text/javascript" src="{{URL::asset('js/appimage.js')}}"></script>
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
                        <div class="col">
                            <h5 class="card-title">{{__('Edit Settings')}}</h5>                        
                        </div>
                    </div>
                    <div>
                        {!! Form::open(['route' => 'backoffice.configurations.store', 'files' => 'true']) !!}
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    {!! Form::label('img-tag', 'Logo') !!}    
                                    <ul id="media-list" class="clearfix">
                                        @if(!empty($logo))
                                            <li id="imgtmp" value="{{ $logo->id }}">
                                                <img src="{{ url( $logo->url  . $logo->file) }} " id="img-tag" class="imagens" />
                                                <div class="post-thumb">
                                                    <div class="inner-post-thumb">
                                                        <a href="javascript:void(0);" data-id="{{ $logo->id }}" data-refresh="1" class="remove-pic-saved"><i class="fa fa-times" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                        <li class="myupload">
                                            <span><i class="fa fa-plus" aria-hidden="true"></i><input type="file" title="" click-type="type2" id="picupload" name="imagens[]" class="onepicupload" multiple style="cursor: pointer"></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    {!! Form::label('img-tag', 'Background') !!}
                                	<ul id="media-list" class="clearfix-mobile">
										@if(!empty($bg))
											<li id="imgtmp-mobile" value="{{ $bg->id }}">
												<img src="{{ url( $bg->url  . $bg->file) }} " id="img-tag" class="imagens" />
												<div class="post-thumb">
													<div class="inner-post-thumb">
                                                        <a href="javascript:void(0);" data-id="{{ $bg->id }}"  class="remove-pic-saved"><i class="fa fa-times" aria-hidden="true"></i></a>
                                                    </div>
												</div>
											</li>
										@endif
										<li class="myupload">
											<span><i class="fa fa-plus" aria-hidden="true"></i><input type="file" title="" click-type="type2" id="picupload" name="imagens-mobile[]" class="onepicupload-mobile" multiple style="cursor: pointer"></span>
										</li>
									</ul>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('text_about_pt', 'About Us PT') !!}
                            {!! Form::textarea('text_about_pt',  $configurations->text_about_pt, ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('text_about_en', 'About Us EN') !!}
                            {!! Form::textarea('text_about_en',  $configurations->text_about_en, ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('text_termos_pt', 'Texto Termos e Condições PT') !!}
                            {!! Form::textarea('text_termos_pt',  $configurations->text_termos_pt, ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('text_termos_en', 'Texto Termos e Condições EN') !!}
                            {!! Form::textarea('text_termos_en',  $configurations->text_termos_en, ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                        {!! Form::label('text_rgpd_pt', 'Texto Rgpd e Politica de Privacidade PT') !!}
                        {!! Form::textarea('text_rgpd_pt',  $configurations->text_rgpd_pt, ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('text_rgpd_en', 'Texto Rgpd e Politica de Privacidade EN') !!}
                            {!! Form::textarea('text_rgpd_en',  $configurations->text_rgpd_en, ['class' => 'form-control']) !!}
                        </div>                      
                            <div style="display:flex; justify-content: flex-end">
                                {!! Form::button('<i class="fa fa-save"></i> &nbsp' .__('Save'), array('type' => 'submit', 'class' => 'btn btn-outline-secondary')); !!}
                                {!! Form::close() !!}
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('foot-scripts')
@endsection
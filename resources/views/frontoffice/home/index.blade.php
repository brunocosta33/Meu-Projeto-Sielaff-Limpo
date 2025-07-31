@extends('layouts.frontoffice_master')

@section('head-meta')
	<title>{{ str_replace('.', ' ', config('app.name')) }} </title>
  <script src='https://www.google.com/recaptcha/api.js'></script>
@endsection

@section('head-scripts')


@endsection

@section('content')
@include('frontoffice.includes.header')
<div class="swiper swiper-homepage-intro">
          <div class="swiper-wrapper">
            @foreach($banners as $banner)
            <div class="swiper-slide">
              <picture>
                <source media="(max-width: 767.98px)" srcset="{{ url( $banner->image->url  . $banner->image->file) }}" />
                <img src="{{ url( $banner->image->url  . $banner->image->file) }} " class="w-100" alt="" width="1369" height="382" />
              </picture>
            </div>
            @endforeach
          </div>
        <button class="btn swiper-button swiper-button-prev" aria-label="Slider Anterior"></button>
        <button class="btn swiper-button swiper-button-next" aria-label="Slider Seguinte"></button>
      </div>
      <div class="py-5 home-about-wrapper anchor-target" id="aboutUs">
        <div class="container-xxl">
          <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
            
          </div>
        </div>
      </div>
      <div class="bg-light py-5 anchor-target" id="businessAreas">
        <div class="container-xxl">
          <div class="row justify-content-center g-4">
            <h2 class="h1 mb-4 text-primary text-center scroll-detect scroll-fade-in-up">{{ app()->getLocale() === 'pt' ? 'Principais Áreas' : '' }}{{ app()->getLocale() === 'en' ? 'Main Areas' : '' }}</h2>
            @foreach($categories as $category)
                  <div class="col-8 col-md-6 col-lg-3 business-area-block scroll-detect scroll-fade-in-up">
                      @if($category->image)
                          <img src="{{ url($category->image->url . $category->image->file) }}" class="w-100" alt="{{ $category->title_pt }}" width="1369" height="382" />
                      @endif
                      <h3>{{app()->getLocale()=== 'pt' ? $category->title_pt:''}} {{app()->getLocale()=== 'en' ? $category->title_en:''}}</h3>
                      <div class="text">
                          <p>{{app()->getLocale()=== 'pt' ? $category->text_pt:''}} {{app()->getLocale()=== 'en' ? $category->text_en:''}}</p>
                      </div>
                  </div>
              @endforeach
          </div>
        </div>
      </div>
      <div class="bg-secondary py-5 anchor-target" id="brands">
        <div class="container-xxl scroll-detect scroll-fade-in-up">
          <h2 class="h1 text-center text-primary">
            {{ app()->getLocale() === 'pt' ? 'Marcas de sucesso' : '' }}
            {{ app()->getLocale() === 'en' ? 'Successful brands' : '' }}
          </h2>
          <div class="d-flex justify-content-center">
            <div class="nav nav-pills mb-4" id="marcas-tabs" role="tablist">
              @foreach($categories as $key => $category)
              @if($key == 0)
              <button class="nav-link active" 
                        id="marcas-btn-{{$category->id}}" 
                        data-bs-toggle="pill" 
                        data-bs-target="#marcas-tab-{{$category->id}}" 
                        type="button" role="tab" 
                        aria-controls="marcas-tab-{{$category->id}}" 
                        aria-selected="{{ $category->id == 0 ? 'true' : 'false' }}">
                  {{ app()->getLocale() === 'pt' ? $category->title_pt : $category->title_en }}
                </button>
              @elseif($key != 0)
              <button class="nav-link {{ $category->id == 0 ? 'active' : '' }}" 
                        id="marcas-btn-{{$category->id}}" 
                        data-bs-toggle="pill" 
                        data-bs-target="#marcas-tab-{{$category->id}}" 
                        type="button" role="tab" 
                        aria-controls="marcas-tab-{{$category->id}}" 
                        aria-selected="{{ $category->id == 0 ? 'true' : 'false' }}">
                  {{ app()->getLocale() === 'pt' ? $category->title_pt : $category->title_en }}
                </button>
              
              @endif
              @endforeach
            </div>
          </div>
          <div class="tab-content">
            @foreach($categories as $key => $category)
            @if($key == 0)
            <div class="tab-pane fade show active" 
                  id="marcas-tab-{{$category->id}}" 
                  role="tabpanel" 
                  aria-labelledby="marcas-btn-{{$category->id}}" 
                  tabindex="0">
                <div class="row justify-content-center g-4">
                  @foreach($category->brandCategory as $bc)
                    <div class="col-6 col-md-3 col-lg-2">
                      <div class="card">
                        <div class="card-body">
                          <img src="{{ url($bc->image->url.$bc->image->file) }}" alt="marca" width="150" height="84" class="w-100" />
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @elseif($key != 0)
            
            <div class="tab-pane fade {{ $category->id == 0 ? 'active' : '' }}" 
                  id="marcas-tab-{{$category->id}}" 
                  role="tabpanel" 
                  aria-labelledby="marcas-btn-{{$category->id}}" 
                  tabindex="0">
                <div class="row justify-content-center g-4">
                  @foreach($category->brandCategory as $bc)
                    <div class="col-6 col-md-3 col-lg-2">
                      <div class="card">
                        <div class="card-body">
                          <img src="{{ url($bc->image->url.$bc->image->file) }}" alt="marca" width="150" height="84" class="w-100" />
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
              
            @endforeach
          </div>
        </div>
      </div>
      <div class="bg-light py-5 anchor-target" id="contactUs">
        <div class="container-xxl scroll-detect scroll-fade-in-up">
          <div class="row">
            <div class="col-lg-8 mx-auto">
              <div class="text-center mb-4">
                <h2 class="mb-3 text-primary"> {{ app()->getLocale() === 'pt' ? 'Contacte-nos' : '' }}{{ app()->getLocale() === 'en' ? 'Contact us' : '' }}</h2>
                <p>{{ app()->getLocale() === 'pt' ? 'Preencha os seus dados. Entraremos em contacto consigo assim que possível!' : '' }}{{ app()->getLocale() === 'en' ? 'Fill in your details. We will contact you as soon as possible!' : '' }}</p>
              </div>
              <form method="POST" action="{{ route('backoffice.leads.store') }}">
              {{ csrf_field() }}
                <input type="text" hidden name="type" value="contact">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="inputName" placeholder="Nome*" name="name" required />
                      <label for="inputName"> {{ app()->getLocale() === 'pt' ? 'Nome' : '' }}{{ app()->getLocale() === 'en' ? 'Name' : '' }}*</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="email" class="form-control" id="inputMail" placeholder="E-mail*" name="email" required />
                      <label for="inputMail">E-mail*</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="inputPhone" placeholder="Telefone" name="telephone"/>
                      <label for="inputPhone">{{ app()->getLocale() === 'pt' ? 'Telefone' : '' }}{{ app()->getLocale() === 'en' ? 'Phone' : '' }}</label>
                    </div>
                    <div class="form-floating mb-3">
                      <textarea type="text" class="form-control" id="inputMessage" placeholder="Mensagem" name="message" rows="1"></textarea>
                      <label for="inputMessage">{{ app()->getLocale() === 'pt' ? 'Mensagem' : '' }}{{ app()->getLocale() === 'en' ? 'Message' : '' }}</label>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                      <div class="form-group">
                          <div class="g-recaptcha" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>
                          @if ($errors->has('g-recaptcha-response'))
                              <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
                          @endif
                      </div>  
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <small>*{{ app()->getLocale() === 'pt' ? 'Campos de preenchimento obrigatório' : '' }}{{ app()->getLocale() === 'en' ? 'Required field' : '' }}</small>
                    <div class="form-check small my-2">
                      <input class="form-check-input" type="checkbox" value=" {{1 ?? 0}}" id="checkPrivacy"name="is_authorized" required />
                      <label class="form-check-label" for="checkPrivacy">{{ app()->getLocale() === 'pt' ? 'Autorizo o envio de dados de acordo com a Política de Privacidade' : '' }}{{ app()->getLocale() === 'en' ? 'I authorize the sending of data in accordance with the Privacy Policy' : '' }}</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <button type="submit" class="btn btn-lg btn-primary w-100">{{ app()->getLocale() === 'pt' ? 'Contactar' : '' }}{{ app()->getLocale() === 'en' ? 'Contact' : '' }}</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

	  @include('frontoffice.includes.footer')
@endsection


@section('foot-scripts')

@endsection

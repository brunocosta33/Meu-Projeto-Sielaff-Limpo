<div class="header-top-bar text-decoration-none">
    <div class="container-xxl text-truncate">
        @php
            $activeLinks = \App\Models\NewLink::where('is_active', 1)->get();
            $currentLocale = App::getLocale();
        @endphp
        <nav>
            @foreach($activeLinks as $link)
                <a href="{{ $link->link }}" title="{{ $currentLocale == 'pt' ? $link->title_pt : $link->title_en }}">
                    {{ $currentLocale == 'pt' ? $link->title_pt : $link->title_en }}
                </a>
            @endforeach
        </nav>
    </div>
</div>

<header class="main-header-wrapper">
      <div class="container-xxl">
        <div class="row align-items-center nav gx-md-4">
          <div class="col-auto d-none d-md-block d-lg-none">
            <button class="btn btn-header-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-controls="offcanvasMenu" aria-label="Menu"><i class="fa fa-bars"></i></button>
          </div>
          <div class="col-auto px-4 me-lg-5">
            <a href="/" title="Homepage" class="nav-brand"><img src="{{asset('assets/img/logo.png')}}" alt="RS Alimentar Logo" width="164" height="52" class="img-fluid" /></a>
          </div>
          <div class="col col-lg-auto px-0 offcanvas-lg offcanvas-start main-menu-offcanvas" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
            <div class="offcanvas-header">
              <a href="/" title="Homepage" class="nav-brand"><img src="assets/img/.png" alt="RS Alimentar Icon" width="31" height="50" /></a>
              <button type="button" class="btn btn-square btn-secondary" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasMenu" aria-label="Close"><i class="fa fa-close"></i></button>
            </div>
            <div class="offcanvas-body">
              <ul class="main-menu-wrapper">
                <li><a href="/#aboutUs" title="{{__('Sobre nós')}}" class="anchor-link">{{__('Sobre nós')}}</a></li>
                <li><a href="/#businessAreas" title="{{__('Áreas')}}" class="anchor-link">{{__('Áreas')}}</a></li>
                <li><a href="/#brands" title="{{__('Marcas')}}" class="anchor-link">{{__('Marcas')}}</a></li>
                <li><a href="/#contactUs" title="{{__('Contactos')}}" class="anchor-link">{{__('Contactos')}}</a></li>
                <li class="menu-divider"></li>
              @php
                  $activeFlyers = \App\Models\Flyer::where('is_active', 1)->get();
              @endphp
              @foreach($activeFlyers as $flyer)
                    <li>
                        <a href="{{ route('frontoffice.flyer.show', $flyer->id) }}" 
                          title="{{ app()->getLocale() == 'en' ? $flyer->name_en : $flyer->name_pt }}">
                            {{ app()->getLocale() == 'en' ? $flyer->name_en : $flyer->name_pt }}
                        </a>
                    </li>
                @endforeach
              </ul>
            </div>
          </div>
          <div class="col d-grid d-md-none nav-mobile-wrapper">
            <button class="btn btn-header-item d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-controls="offcanvasMenu" aria-label="Menu">
              <i class="fa fa-bars"></i>
              <div class="btn-header-item-label">Menu</div>
            </button>
            <a href="/#contactUs" title="Contacte-nos" class="btn btn-header-item d-md-none">
              <i class="far fa-message"></i>
              <div class="btn-header-item-label">Contacte-nos</div>
            </a>
          </div>
          <div class="col-auto ms-auto">
          @include('includes.language-switch')           
          </div>
        </div>
      </div>
    </header>

    <main role="main">

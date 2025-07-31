<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">

    
        @if((session('ViewAs') == null || session('ViewAs') == "") && Auth::user() != null
        && Auth::user()->hasAnyPermissionsOrRole(['editar_clientes','editar_fornecedores','editar_materiais','editar_campanhas','editar_temas','editar_produtos','editar_precos','editar_utilizadores','editar_cargos']))
        <button type="button" id="sidebarCollapse" class="navbar-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-align-justify"></i>
        </button>
        <ul class="nav navbar-nav navbar-logo mx-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{route('backoffice.index')}}">
                    <img style="max-height: 40px;" id="img_logo" src="{{asset('/images/logo.png')}}" class="invisible" />
                </a>
            </li>
        </ul>
        @else
        <ul class="nav navbar-nav navbar-logo mx-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{route('backoffice.index')}}">
                    <img style="max-height: 40px;" id="img_logo" src="{{asset('/images/logo.png')}}" />
                </a>
            </li>
        </ul>
        @endif

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto">
                @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                </li>
                @if(Route::has('register'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">{{ __('Registar') }}</a>
                </li>
                @endif
                @else
                {{-- <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fa fa-envelope fa-2x"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fa fa-calendar-alt fa-2x"></i> </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fa fa-comments fa-2x"></i></a>
                </li> --}}

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ Auth::user()->name }} <i class="fas fa-user navbar-icon"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('backoffice.profile.index') }}">{{ __('Minha Conta') }}</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="collapsed" data-parent="#sidebar">
                            <i class="fas fa-sign-out-alt"></i><span class="hidden-sm-down"> {{ __('Logout') }}</span>
                        </a>
                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>
               
                @endguest
            </ul>
        </div>
    </div>
</nav>
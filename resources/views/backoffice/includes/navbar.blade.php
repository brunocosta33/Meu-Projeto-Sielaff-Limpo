<?php

use App\Models\Base;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">

        @if (!Auth::user()->roles->contains(3))
        <button type="button" id="sidebarCollapse" class="navbar-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>
        @if(!empty(Base::logo()->url))
        <a class="nav-link" href="#">
            <img style="max-height: 50px;" id="img_logo" src="{{ url(Base::logo()->url  . Base::logo()->file) }}" class="invisible" />
        </a>
        @endif
        @endif

       
        <?php

        use App\Models\Client; ?>
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ Auth::user()->name }} <i class="fas fa-user navbar-icon"></i>
        </a>



        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" data-parent="#sidebar" href="{{ route('backoffice.profile.index') }}">{{__('Profile')}}</a>

            <a href="#" class="dropdown-item" data-parent="#sidebar" style="cursor: default">
                <div class="dropdown-divider"></div>
                <span class="hidden-sm-down">{{ __('Last access') }}:</span><br>
                <span class="hidden-sm-down"> {{ Auth::user()->last_login }} </span>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="collapsed" data-parent="#sidebar">
                <i class="fas fa-sign-out-alt"></i><span class="hidden-sm-down"> {{ __('Logout') }}</span>
            </a>
            <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </div>
</nav>
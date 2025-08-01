<?php

use App\Models\Base;
?>

<nav id="sidebar">
    <div class="sidebar-header">
        <div id="sidebar-brand-mage" class="text-center">
            <a class="left-logo" href="#">
                @if(!empty(Base::logo()->url))
                <img style="max-height: 150px;" id="" src="{{ url(Base::logo()->url  . Base::logo()->file) }}" />
                @endif
            </a>
        </div>
    </div>
    <ul class="list-unstyled components" id="menu-left">
        <li>
            <a href="{{ route('backoffice.index') }}" class="collapsed" data-parent="#sidebar">
                <i class="fas fa-tachometer-alt"></i>
                <span class="hidden-sm-down"> {{ __('Dashboard') }} </span>
            </a>
        </li>
         @if(Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        
        <li>
            <a href="#pageSubmenuTarefas" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fa fa-box"></i>
                <span class="hidden-sm-down"> {{__('Tarefas')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuTarefas" data-parent="#menu-left">
                <li><a href="{{ route('backoffice.tasks.index') }}">{{ __('Tarefas') }}</a></li>
               <li><a href="{{ route('backoffice.appointments.index') }}">{{ __('Agendamento') }}</a></li>
               <li><a href="{{ route('backoffice.mytasks.index') }}">{{ __('Minhas Tarefas') }}</a></li>
            </ul>
        </li>
        @endif
        @if(Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        
        <li>
            <a href="#pageSubmenuProd" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fa fa-box"></i>
                <span class="hidden-sm-down"> {{__('Logistíca')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuProd" data-parent="#menu-left">
                <li><a href="{{ route('backoffice.suppliers.index') }}">{{ __('Transportadoras') }}</a></li>
               <li><a href="{{ route('backoffice.appointments.index') }}">{{ __('Agendamento') }}</a></li>
            </ul>
        </li>
        @endif
       
             @if(Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        
        <li>
            <a href="#pageSubmenuInstalacoes" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fa fa-box"></i>
                <span class="hidden-sm-down"> {{__('Instalações')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuInstalacoes" data-parent="#menu-left">
                <li><a href="{{ route('backoffice.teams.index') }}">{{ __('Empresas') }}</a></li>
               <li><a href="{{ route('backoffice.installations.index') }}">{{ __('Agendamento das Instalações') }}</a></li>
            </ul>
        </li>
        @endif
        @if(Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        
        <li>
            <a href="#pageSubmenuTecnicas" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fa fa-box"></i>
                <span class="hidden-sm-down"> {{__('Assitencia')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuTecnicas" data-parent="#menu-left">
                <li><a href="{{ route('backoffice.technical_requests.index') }}">{{ __('Pedidos') }}</a></li>
               <li><a href="{{ route('backoffice.technical_schedules.index') }}">{{ __('Agendamento das Assitencias') }}</a></li>
                <li><a href="{{ route('backoffice.technicians.index') }}">{{ __('Técnicos') }}</a></li>
            </ul>
        </li>
        @endif
        @if(Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        
        <li>
            <a href="#pageSubmenuArm" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fa fa-box"></i>
                <span class="hidden-sm-down"> {{__('Armazém')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuArm" data-parent="#menu-left">
                <li><a href="{{ route('backoffice.parts.index') }}">{{ __('Stock Peças Armazém') }}</a></li>
                <li><a href="{{ route('backoffice.van-stocks.index') }}">{{ __('Stock Carrinhas Técnicos') }}</a></li>
                <li><a href="{{ route('backoffice.part_reservations.index') }}">{{ __('Reservas') }}</a></li>
                <li><a href="{{ route('backoffice.machines.index') }}">{{ __('Máquinas') }}</a></li>
            </ul>
        </li>
        @endif
        @if(Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        <li>
            <a href="#pageSubmenuFlyer" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fa fa-book-open"></i>
                <span class="hidden-sm-down"> {{__('Lojas')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuFlyer" data-parent="#menu-left">
               <li><a href="{{ route('backoffice.stores.index') }}">{{ __('Lojas') }}</a></li>
               <li><a href="{{ route('backoffice.teams.index') }}">{{ __('Equipas') }}</a></li>
            </ul>
        </li>
        @endif
        @if(Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        <li>
            <a href="#pageSubmenuAbout" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fa fa-globe"></i>
                <span class="hidden-sm-down"> {{__('Site')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuAbout" data-parent="#menu-left">
            </ul>
        </li>
        @endif
        @if(Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        <li>
            <a href="#pageSubmenuCRM" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fas fa-project-diagram"></i>
                <span class="hidden-sm-down"> {{__('CRM')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuCRM" data-parent="#menu-left">
                <li><a href="{{ route('backoffice.leads.index') }}">{{ __('Leads') }}</a></li>
               <li><a href="{{ route('backoffice.clients.index') }}">{{ __('Clients') }}</a></li>
                <li><a href="{{ route('backoffice.contacts.index') }}">{{ __('Contacts  ') }}</a></li>
               <li><a href="{{ route('backoffice.state.index') }}">{{ __('Status') }}</a></li>
               <li><a href="{{ route('backoffice.returnText.index') }}">{{ __('Email de Receção') }}</a></li>
            </ul>
        </li>
        <hr>

        <li>
            <a href="#pageSubmenuUsers" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-tools"></i>
                <span class="hidden-sm-down"> {{__('Administration')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuUsers" data-parent="#menu-left">
                @if(Auth::user()->hasPermissionsOrRole(['view_configurations']))<li><a href="{{ route('backoffice.configurations.index') }}">{{ __('Settings') }}</a></li>@endif
                @if(Auth::user()->hasPermissionsOrRole(['view_loginactivity']))<li><a href="{{ route('backoffice.loginactivity.index') }}">{{ __('Users Activities') }}</a></li>@endif
                @if(Auth::user()->hasPermissionsOrRole(['view_users']))<li><a href="{{ route('backoffice.users.index') }}">{{ __('Users') }}</a></li>@endif
                @if(Auth::user()->hasPermissionsOrRole(['view_roles']))<li><a href="{{ route('backoffice.roles.index') }}">{{ __('Roles') }}</a></li>@endif
                @if(Auth::user()->hasPermissionsOrRole(['view_permissions']))<li><a href="{{ route('backoffice.permissions.index') }}">{{ __('Permissions') }}</a></li>@endif
            </ul>
        </li>
        @endif
        <hr>
        <li>
            <a href="#pageSubmenuProfile" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-user"></i>
                <span class="hidden-sm-down">{{ Auth::user()->name }}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuProfile" data-parent="#menu-left">
                <a class="collapsed" data-parent="#sidebar" href="{{ route('backoffice.profile.index') }}">{{__('Profile')}}</a>
                <a href="#" class="collapsed" data-parent="#sidebar" style="cursor: default">
                    <span class="hidden-sm-down">{{ __('Last access') }}:</span><br>
                    <span class="hidden-sm-down">{{ Auth::user()->last_login }}</span>
                </a>
            </ul>
        </li>

        <li>
            <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="collapsed" data-parent="#sidebar">
                <i class="fas fa-sign-out-alt"></i><span class="hidden-sm-down">{{ __('Logout') }}</span>
            </a>
        </li>
        <hr>
        <li>
            <div class="col col-lg-auto d-flex justify-content-center">
                <div class="dropdown">
                    @include('includes.language-switch')
                </div>
            </div>

        </li>
    </ul>
    <div class="sidebar-footer"></div>
    <a href=" " class="sidebar-signature" title="vital check system" target="_blank">
        <div class="sidebar-signature-name">Vital<strong>Check</strong>System</div>
        <div class="version">v1.0</div>
    </a>
</nav>
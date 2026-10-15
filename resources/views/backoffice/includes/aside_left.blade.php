<?php

use App\Models\Base;
?>

<nav id="sidebar">
    <div class="sidebar-header">
        <div id="sidebar-brand-mage" class="text-center">
            <a class="left-logo" href="#">
                @if(!empty(Base::logo()->url))
                <img style="max-height: 150px;" src="{{ url(Base::logo()->url . Base::logo()->file) }}" />
                @endif
            </a>
        </div>
    </div>

    <ul class="list-unstyled components" id="menu-left">

        @php($isTechnician = Auth::user()->hasRole('user'))

        @if($isTechnician)
        <li>
            <a href="{{ route('backoffice.technical_requests.index') }}" class="collapsed" data-parent="#sidebar">
                <i class="fas fa-headset"></i>
                <span class="hidden-sm-down"> {{ __('Os Meus Pedidos') }} </span>
            </a>
        </li>
        <li>
            <a href="{{ route('backoffice.task_schedules.minhas') }}">
                <i class="fas fa-tasks me-2"></i>
                <span class="hidden-sm-down">{{ __('Minhas Tarefas') }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('backoffice.stores.index') }}">
                <i class="fas fa-store me-2"></i>
                <span class="hidden-sm-down">{{ __('Lojas') }}</span>
            </a>
        </li>
        @else
        <li>
            <a href="{{ route('backoffice.index') }}" class="collapsed" data-parent="#sidebar">
                <i class="fas fa-tachometer-alt"></i>
                <span class="hidden-sm-down"> {{ __('Dashboard') }} </span>
            </a>
        </li>
        <li>
            <a href="#pageSubmenuTarefas" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-tasks me-2"></i>
                <span class="hidden-sm-down">{{ __('Tarefas') }}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuTarefas" data-parent="#menu-left">
                <li>
                    <a href="{{ route('backoffice.tasks.index') }}">
                        <i class="fas fa-clipboard-list me-2"></i>{{ __('Tarefas') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('backoffice.task_schedules.index') }}">
                        <i class="fas fa-calendar-alt me-2"></i>{{ __('Agendamento') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('backoffice.task_schedules.minhas') }}">
                        <i class="fas fa-user me-2"></i>{{ __('Minhas Tarefas') }}
                    </a>
                </li>
            </ul>
        </li>
        @endif

        @if(!$isTechnician && Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        <li>
            <a href="#pageSubmenuProd" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-truck"></i>
                <span class="hidden-sm-down"> {{__('Logistíca')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuProd" data-parent="#menu-left">
                <li><a href="{{ route('backoffice.suppliers.index') }}"><i class="fas fa-building me-2"></i>{{ __('Criar Transportadoras') }}</a></li>
                <li><a href="{{ route('backoffice.appointments.index') }}"><i class="fas fa-calendar-check me-2"></i>{{ __('Agendamento de Transporte') }}</a></li>
            </ul>
        </li>
        @endif

        @if(!$isTechnician && Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        <li>
            <a href="#pageSubmenuInstalacoes" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-tools"></i>
                <span class="hidden-sm-down"> {{__('Instalações')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuInstalacoes" data-parent="#menu-left">
                <li><a href="{{ route('backoffice.teams.index') }}"><i class="fas fa-building me-2"></i>{{ __('Empresas') }}</a></li>
                <li><a href="{{ route('backoffice.installations.index') }}"><i class="fas fa-calendar-alt me-2"></i>{{ __('Agendamento das Instalações') }}</a></li>
            </ul>
        </li>
        @endif

        @if(!$isTechnician && Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
        <li>
            <a href="#pageSubmenuTecnicas" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-wrench"></i>
                <span class="hidden-sm-down"> {{__('Assistência')}}</span>
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenuTecnicas" data-parent="#menu-left">
                <li><a href="{{ route('backoffice.technical_requests.index') }}"><i class="fas fa-headset me-2"></i>{{ __('HotLine') }}</a></li>
                <li><a href="{{ route('backoffice.technical_requests.open_all') }}"><i class="fas fa-folder-open me-2"></i>{{ __('Todos os Pedidos Abertos') }}</a></li>
                <li><a href="{{ route('backoffice.technical_requests.technicians') }}"><i class="fas fa-users me-2"></i>{{ __('Pedidos por Responsável') }}</a></li>
                <li><a href="{{ route('backoffice.maintenances.index') }}"><i class="fas fa-clipboard-check me-2"></i>{{ __('Manutenções Anuais') }}</a></li>
                {{-- <li><a href="{{ route('backoffice.technical_schedules.index') }}"><i class="fas fa-calendar-day me-2"></i>{{ __('Agendamento das Assistências') }}</a>
        </li> --}}
    </ul>
    </li>
    @endif
    @if(!$isTechnician && Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
    <li>
        <a href="{{ route('backoffice.stores.index') }}" class="collapsed" data-parent="#sidebar">
            <i class="fas fa-store"></i>
            <span class="hidden-sm-down"> {{__('Lojas')}}</span>
        </a>
    </li>
    @endif

    @if(!$isTechnician && Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
    <li>
        <a href="#pageSubmenuStock" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fas fa-boxes"></i>
            <span class="hidden-sm-down"> {{__('Stock')}}</span>
        </a>
        <ul class="collapse list-unstyled" id="pageSubmenuStock" data-parent="#menu-left">
            <li><a href="{{ route('backoffice.stock.items.index') }}"><i class="fas fa-cubes me-2"></i>{{ __('Peças e Armazém') }}</a></li>
            <li><a href="{{ route('backoffice.stock.technicians.index') }}"><i class="fas fa-truck-loading me-2"></i>{{ __('Stock por Técnico') }}</a></li>
            <li><a href="{{ route('backoffice.stock.movements.index') }}"><i class="fas fa-exchange-alt me-2"></i>{{ __('Movimentos de Stock') }}</a></li>
        </ul>
    </li>
    @endif

    @if(\App\Services\HrAccess::canAccess(Auth::user()))
    @php($hrManager = \App\Services\HrAccess::isManager(Auth::user()))
    @php($hrFuncao = \App\Services\HrAccess::staffOf(Auth::user())?->funcao)
    <li>
        <a href="#pageSubmenuRH" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fas fa-user-clock"></i>
            <span class="hidden-sm-down"> {{__('Recursos Humanos')}}</span>
        </a>
        <ul class="collapse list-unstyled" id="pageSubmenuRH" data-parent="#menu-left">
            @if($hrManager || $hrFuncao === 'hotliner')
            <li><a href="{{ route('backoffice.hr.hotline.index') }}"><i class="fas fa-calendar-week me-2"></i>{{ __('Escala Hotline') }}</a></li>
            @endif
            @if($hrManager || $hrFuncao === 'tecnico')
            <li><a href="{{ route('backoffice.hr.piquete.index') }}"><i class="fas fa-tools me-2"></i>{{ __('Escala Piquete') }}</a></li>
            @endif
            <li><a href="{{ route('backoffice.hr.vacations.index') }}"><i class="fas fa-umbrella-beach me-2"></i>{{ __('Férias e Ausências') }}</a></li>
            <li><a href="{{ route('backoffice.hr.holidays.index') }}"><i class="fas fa-flag me-2"></i>{{ __('Feriados') }}</a></li>
            @if(\App\Models\HrStaff::canManageStaff(Auth::user()))
                <li><a href="{{ route('backoffice.hr.staff.index') }}"><i class="fas fa-id-card me-2"></i>{{ __('Colaboradores') }}</a></li>
            @endif
        </ul>
    </li>
    @endif

    @if(!$isTechnician && Auth::user()->hasAnyPermissionsOrRole(['view_configurations','view_loginactivity','view_users','view_roles','view_permissions']))
    <li>
        <a href="#pageSubmenuUsers" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fas fa-cogs"></i>
            <span class="hidden-sm-down"> {{__('Administration')}}</span>
        </a>
        <ul class="collapse list-unstyled" id="pageSubmenuUsers" data-parent="#menu-left">
            @if(Auth::user()->hasPermissionsOrRole(['view_configurations']))
            <li><a href="{{ route('backoffice.configurations.index') }}"><i class="fas fa-sliders-h me-2"></i>{{ __('Settings') }}</a></li>
            @endif
            @if(Auth::user()->hasPermissionsOrRole(['view_loginactivity']))
            <li><a href="{{ route('backoffice.loginactivity.index') }}"><i class="fas fa-user-clock me-2"></i>{{ __('Users Activities') }}</a></li>
            @endif
            @if(Auth::user()->hasPermissionsOrRole(['view_users']))
            <li><a href="{{ route('backoffice.users.index') }}"><i class="fas fa-users me-2"></i>{{ __('Users') }}</a></li>
            @endif
            @if(Auth::user()->hasPermissionsOrRole(['view_roles']))
            <li><a href="{{ route('backoffice.roles.index') }}"><i class="fas fa-user-shield me-2"></i>{{ __('Roles') }}</a></li>
            @endif
            @if(Auth::user()->hasPermissionsOrRole(['view_permissions']))
            <li><a href="{{ route('backoffice.permissions.index') }}"><i class="fas fa-key me-2"></i>{{ __('Permissions') }}</a></li>
            @endif
        </ul>
    </li>
    @endif

    @if(\App\Services\DocumentAccess::canView(Auth::user()))
    <li>
        <a href="{{ route('backoffice.documents.index') }}" class="collapsed" data-parent="#sidebar">
            <i class="fas fa-book"></i>
            <span class="hidden-sm-down"> {{ __('Documentação') }}</span>
        </a>
    </li>
    @endif

    <hr>

    <li>
        <a href="#pageSubmenuProfile" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fas fa-user"></i>
            <span class="hidden-sm-down">{{ Auth::user()->name }}</span>
        </a>
        <ul class="collapse list-unstyled" id="pageSubmenuProfile" data-parent="#menu-left">
            <li><a href="{{ route('backoffice.profile.index') }}"><i class="fas fa-id-badge me-2"></i>{{__('Profile')}}</a></li>
            <li>
                <a href="#" class="collapsed" data-parent="#sidebar" style="cursor: default">
                    <span class="hidden-sm-down"><i class="far fa-clock me-2"></i>{{ __('Last access') }}:</span><br>
                    <span class="hidden-sm-down">{{ Auth::user()->last_login }}</span>
                </a>
            </li>
        </ul>
    </li>

    <li>
        <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="collapsed" data-parent="#sidebar">
            <i class="fas fa-sign-out-alt"></i>
            <span class="hidden-sm-down">{{ __('Logout') }}</span>
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

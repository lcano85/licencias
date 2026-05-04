<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box" data-no-translate="true">
        <a href="{{ route('dashboard') }}" class="logo-dark">
            <img src="{{ asset('admin/images/logo-sm.png') }}" class="logo-sm" alt="logo sm" />
            <img src="{{ asset('admin/images/logo-dark.png') }}" class="logo-lg" alt="logo dark" />
        </a>
        <a href="{{ route('dashboard') }}" class="logo-light">
            <img src="{{ asset('admin/images/logo-sm.png') }}" class="logo-sm" alt="logo sm" />
            <img src="{{ asset('admin/images/logo-light.png') }}" class="logo-lg" alt="logo light" />
        </a>
    </div>

    <button type="button" class="button-sm-hover" aria-label="{{ __('Show Full Sidebar') }}">
        <iconify-icon icon="solar:double-alt-arrow-right-bold-duotone" class="button-sm-hover-icon"></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">
            <li class="menu-title">{{ __('General') }}</li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:widget-5-bold-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.dashboard') }} </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'roles' ? 'active' : '' }}" href="{{ route('roles') }}">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:user-id-bold"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.roles') }} </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'permissions' ? 'active' : '' }}" href="{{ route('permissions') }}">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:shield-user-bold"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.permissions') }} </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'users' ? 'active' : '' }}" href="{{ route('users') }}">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:users-group-rounded-bold"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.users') }} </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'projects' ? 'active' : '' }}" href="{{ route('projects') }}">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:folder-with-files-broken"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.projects') }} </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'activities' ? 'active' : '' }}" href="{{ route('activities') }}">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:file-text-broken"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.activities') }} </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-arrow collapsed" href="#clientsInfo" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="clientsInfo">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:user-id-bold"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.clients') }} </span>
                </a>
                <div class="collapse" id="clientsInfo" style="">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('clients') }}">{{ __('menu.clients') }}</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('basic-info') }}">{{ __('menu.basic-info-status') }}</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'assign-by-role-project' ? 'active' : '' }}" href="{{ route('assign-by-role-project') }}">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:user-speak-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.assign_by_role') }} </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-arrow collapsed" href="#sidebarCalendar" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCalendar">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:calendar-bold"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.calendar') }} </span>
                </a>
                <div class="collapse" id="sidebarCalendar" style="">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('calendar') }}">{{ __('menu.calendar') }}</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('calendar.list') }}">{{ __('menu.calendar_list') }}</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-arrow collapsed" href="#clientCategory" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="clientCategory">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:benzene-ring-bold"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.client_categories') }} </span>
                </a>
                <div class="collapse" id="clientCategory" style="">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('category') }}">{{ __('menu.category') }}</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('sub-category') }}">{{ __('menu.sub_category') }}</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('use-types') }}">{{ __('menu.use_types') }}</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'licenses-agreements' ? 'active' : '' }}" href="{{ route('licenses-agreements') }}">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:document-medicine-broken"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.licenses_agreements') }} </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-arrow collapsed" href="#budgetsRou" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="budgetsRou">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:case-minimalistic-bold-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.budget') }} </span>
                </a>
                <div class="collapse" id="budgetsRou" style="">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('budgets') }}">{{ __('menu.budget') }}</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('criterions') }}">{{ __('menu.budget_criterion') }}</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('invoice-consecutive') }}">{{ __('menu.invoice_consecutive') }}</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('receipt-consecutive') }}">{{ __('menu.receipt_consecutive') }}</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'assign-settlement' ? 'active' : '' }}" href="{{ route('assign-settlement') }}">
                    <span class="nav-icon" data-no-translate="true">
                        <iconify-icon icon="solar:user-speak-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> {{ __('menu.assign_settlement') }} </span>
                </a>
            </li>
            
        </ul>
    </div>
</div>

<header class="topbar">
    <div class="container-fluid">
        <div class="navbar-header">
            <div class="d-flex align-items-center">
                <div class="topbar-item">
                    <button type="button" class="button-toggle-menu me-2">
                        <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                    </button>
                </div>

                <div class="topbar-item">
                    <h4 class="fw-bold topbar-button pe-none text-uppercase mb-0">
                        {{ __('Welcome, :name!', ['name' => auth()->user()->name ?? __('User')]) }}
                    </h4>
                </div>
            </div>

            <div class="d-flex align-items-center gap-1">
                <div class="dropdown language-switcher">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if(app()->getLocale() == 'en')
                            {{ __('English') }}
                        @else
                            {{ __('Spanish') }}
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <form action="{{ route('language.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="lang" value="en">
                                <button type="submit" class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}">
                                    {{ __('English') }}
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('language.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="lang" value="es">
                                <button type="submit" class="dropdown-item {{ app()->getLocale() == 'es' ? 'active' : '' }}">
                                    {{ __('Spanish') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

                <div class="dropdown topbar-item">
                    <a type="button" class="topbar-button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle" width="32" src="{{ asset('admin/images/users/avatar-1.jpg') }}" alt="avatar-3" />
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        @if(auth()->check())
                            <h6 class="dropdown-header">{{ __('Welcome, :name!', ['name' => auth()->user()->name]) }}</h6>
                        @endif
                        <div class="dropdown-divider my-1"></div>
                        <a class="dropdown-item" href="{{ route('user-profile.edit') }}">
                            <span class="align-middle">{{ __('Profile') }}</span>
                        </a>
                        <a class="dropdown-item" href="{{ route('change.password') }}">
                            <span class="align-middle">{{ __('Change Password') }}</span>
                        </a>
                        <a class="dropdown-item" href="{{ route('help-page') }}">
                            <span class="align-middle">{{ __('Help') }}</span>
                        </a>
                        <div class="dropdown-divider my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="bx bx-log-out fs-18 align-middle me-1"></i><span class="align-middle">{{ __('Logout') }}</span>
                            </a>
                        </form>
                    </div>
                </div>

                <form class="app-search d-none d-md-block ms-2">
                    <div class="position-relative">
                        <input type="search" class="form-control" placeholder="{{ __('Search...') }}" autocomplete="off" value="" />
                        <iconify-icon icon="solar:magnifer-linear" class="search-widget-icon"></iconify-icon>
                    </div>
                </form>
            </div>
        </div>
    </div>
</header>

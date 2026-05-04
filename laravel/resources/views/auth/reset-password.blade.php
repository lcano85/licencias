<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-100">
    <head>
        <meta charset="utf-8" />
        <title>{{ __('Reset Password') }} | WebSystem</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="WebSystem" />
        <meta name="author" content="WebSystem" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('auth/images/favicon.ico') }}" />
        <link href="{{ asset('auth/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('auth/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('auth/css/app.min.css') }}" rel="stylesheet" type="text/css" />
        <script src="{{ asset('auth/js/config.js') }}"></script>
    </head>

    <body class="h-100">
        <div class="d-flex flex-column h-100 p-3">
            <div class="d-flex flex-column flex-grow-1">
                <div class="row h-100">
                    <div class="col-xxl-7">
                        <div class="row justify-content-center h-100">
                            <div class="col-lg-6 py-lg-5">
                                <div class="d-flex flex-column h-100 justify-content-center">
                                    <div class="auth-logo mb-4">
                                        <a href="javascript:void(0)" class="logo-dark">
                                            <img src="{{ asset('auth/images/logo-dark.png') }}" height="24" alt="logo" />
                                        </a>
                                        <a href="javascript:void(0)" class="logo-light">
                                            <img src="{{ asset('auth/images/logo-light.png') }}" height="24" alt="logo" />
                                        </a>
                                    </div>
                                    <h2 class="fw-bold fs-24">{{ __('Reset Password') }}</h2>
                                    <x-auth-session-status class="mb-3" :status="session('status')" />
                                    <div class="mb-5">
                                        <form method="POST" action="{{ route('password.store') }}">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $request->route('token') }}">
                                            <div class="mb-3">
                                                <label class="form-label" for="email">{{ __('Email') }}</label>
                                                <x-text-input id="email" class="form-control bg-" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="password">{{ __('Password') }}</label>
                                                <x-text-input id="password" class="form-control" type="password" name="password" required autocomplete="new-password" placeholder="{{ __('Enter password') }}" />
                                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="password">{{ __('Confirm Password') }}</label>
                                                <x-text-input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Enter confirm password') }}" />
                                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                            </div>
                                            <div class="mb-1 text-center d-grid">
                                                <button class="btn btn-soft-primary" type="submit">{{ __('Reset Password') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-5 d-none d-xxl-flex">
                        <div class="card h-100 mb-0 overflow-hidden">
                            <div class="d-flex flex-column h-100">
                                <img src="{{ asset('auth/images/img-10.jpg') }}" alt="" class="w-100 h-100" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('auth/js/vendor.js') }}"></script>
        <script src="{{ asset('auth/js/app.js') }}"></script>
    </body>
</html>

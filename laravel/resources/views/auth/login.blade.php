<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-100">
<head>
     <meta charset="utf-8" />
     <title>{{ __('Sign In') }} | WebSystem</title>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                                                  <img src="{{ asset('auth/images/logo-dark.png') }}" height="24" alt="logo">
                                             </a>
                                             <a href="javascript:void(0)" class="logo-light">
                                                  <img src="{{ asset('auth/images/logo-light.png') }}" height="24" alt="logo">
                                             </a>
                                        </div>

                                        <h2 class="fw-bold fs-24">{{ __('Sign In') }}</h2>

                                        <p class="text-muted mt-1 mb-3">{{ __('Please enter your email address and password to log in to the portal.') }}</p>
                                        <x-auth-session-status class="mb-3" :status="session('status')" />
                                        <div class="mb-5">
                                             <form method="POST" action="{{ route('login') }}">
                                                  @csrf
                                                  <div class="mb-3">
                                                       <label class="form-label" for="email">{{ __('Email') }}</label>
                                                       <input type="email" id="email" name="email" :value="old('email')" required autofocus autocomplete="username" class="form-control bg-" placeholder="{{ __('Enter your email') }}">
                                                       <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                                  </div>
                                                  <div class="mb-3">
                                                       <a href="{{ route('password.request') }}" class="float-end text-muted text-unline-dashed ms-1">{{ __('Forgot your password?') }}</a>
                                                       <label class="form-label" for="password">{{ __('Password') }}</label>
                                                       <input type="password" id="password" name="password" class="form-control" placeholder="{{ __('Enter your password') }}" required autocomplete="current-password">
                                                       <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                                  </div>
                                                  <div class="mb-3">
                                                       <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                                                            <label class="form-check-label" for="remember_me">{{ __('Remember me') }}</label>
                                                       </div>
                                                  </div>
                                                  <div class="mb-1 text-center d-grid">
                                                       <button class="btn btn-soft-primary" type="submit">{{ __('Sign In') }}</button>
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
                                   <img src="{{ asset('auth/images/img-10.jpg') }}" alt="" class="w-100 h-100">
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

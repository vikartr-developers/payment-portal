@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login Cover - Pages')

@section('vendor-style')
    <!-- Vendor -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />
@endsection

@section('page-style')
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>
@endsection
@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">
            <!-- Illustration -->
            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="{{ asset('assets/img/illustrations/bg-shape-image.png') }}" alt="auth-login-cover"
                        class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-login-illustration-light.png"
                        data-app-dark-img="illustrations/auth-login-illustration-dark.png">

                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}"
                        alt="auth-login-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png">
                </div>
            </div>
            <!-- /Illustration -->

            <!-- Login Form -->
            <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
                <div class="w-px-400 mx-auto">
                    <!-- Logo -->
                    <div class="app-brand mb-4 text-center">
                        <a href="{{ url('/') }}" class="app-brand-link mx-auto">
                            <img class="logo m-auto" src="{{ asset('assets/img/logo/logo.jpg') }}" width="100"
                                alt="">

                            {{-- <span class="app-brand-logo demo">
                                @include('_partials.macros')
                            </span> --}}
                        </a>
                    </div>
                    <!-- /Logo -->

                    <h3 class="mb-1 text-center">Welcome to {{ config('app.name') }}! 👋</h3>
                    <p class="mb-4 text-center">Please sign-in to your account</p>
                    <h2>Two-Factor Authentication</h2>

                    <p>Please enter the 6-digit code from your authenticator app to complete login.</p>

                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('2fa.verify.post') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="code">Authentication Code</label>
                                    <input id="code" name="code" class="form-control" required autofocus>
                                </div>
                                <button class="btn btn-primary mt-2">Verify</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Login Form -->
        </div>
    </div>
@endsection

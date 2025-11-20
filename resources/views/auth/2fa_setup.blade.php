@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login Cover - Pages')
{{-- {{ dd('lllll') }} --}}

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
                            <img class="logo m-auto" src="{{ asset('assets/img/logo/logo.png') }}" width="200"
                                alt="">

                            {{-- <span class="app-brand-logo demo">
                                @include('_partials.macros')
                            </span> --}}
                        </a>
                    </div>
                    <!-- /Logo -->
                    {{-- <h3 class="mb-1 text-center">Welcome to {{ config('app.name') }}! 👋</h3> --}}

                    {{-- <h3 class="mb-1 text-center">Welcome to <img class="logo m-auto"
                            src="{{ asset('assets/img/logo/logo.png') }}" width="100" alt=""></h3> --}}
                    {{-- <p class="mb-4 text-center">Scan this QR code with your authenticator app:</p> --}}

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-body mx-auto text-center">


                            @if (!empty($qrImage))
                                <p><strong>Scan this QR code with your authenticator app:</strong></p>
                                <div class="mb-3 mx-auto text-center">
                                    @php
                                        // The QR generator may return either a data URI (PNG) or raw SVG markup.
                                        $isDataUri = \Illuminate\Support\Str::startsWith($qrImage, 'data:');
                                        $isSvg = \Illuminate\Support\Str::startsWith(trim($qrImage), '<svg');
                                    @endphp

                                    @if ($isDataUri) <img src="{{ $qrImage }}" alt="2FA QR Code" />
                        @elseif($isSvg)
                            <div class="mx-auto text-center">

                            {{-- render raw SVG markup inline --}}
                            {!! $qrImage !!}
                            </div>
                        @else
                            {{-- Fallback: output as image src raw --}}
                            <div class="mx-auto text-center">
                            {!! $qrImage !!}
</div>
                            {{-- <img src={!! $qrImage !!} alt="2FA QR Code" /> --}} @endif
                                </div>
                                <p>If your app can't scan the QR, use the following secret:</p>
                                <p><strong>Secret:</strong> <code>{{ $secret }}</code></p>
                            @else
                                <p><strong>Secret:</strong> <code>{{ $secret }}</code></p>
                                <p>If your authenticator app supports scanning QR codes, you can also use the secret above
                                    to create the
                                    account.</p>
                            @endif

                            <form method="POST" action="{{ route('2fa.enable') }}">
                                @csrf
                                {{-- include secret as a hidden fallback in case session is lost between requests --}}
                                <input type="hidden" name="secret" value="{{ $secret }}">
                                <div class="form-group mb-3">
                                    <label for="code" class="form-label">Enter the 6-digit code from your app to confirm
                                        setup</label>
                                    <input id="code" name="code"
                                        class="form-control @error('code') is-invalid @enderror" required maxlength="6"
                                        pattern="[0-9]{6}" placeholder="000000" autocomplete="off">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mt-2">
                                    <i class="ti ti-shield-check me-1"></i>Enable Two-Factor Authentication
                                </button>
                            </form>

                            <div class="text-center mt-3">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="ti ti-logout me-1"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Register -->
                    {{-- @if (Route::has('register'))
                        <p class="text-center mt-3">
                            <span>New on our platform?</span>
                            <a href="{{ route('register') }}">
                                <span>Create an account</span>
                            </a>
                        </p>
                    @endif --}}

                    {{-- <!-- Social Logins -->
                    <div class="divider my-4">
                        <div class="divider-text">or</div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <a href="#" class="btn btn-icon btn-label-facebook me-3">
                            <i class="fa-brands fa-facebook-f fs-5"></i>
                        </a>
                        <a href="#" class="btn btn-icon btn-label-google-plus me-3">
                            <i class="fa-brands fa-google fs-5"></i>
                        </a>
                        <a href="#" class="btn btn-icon btn-label-twitter">
                            <i class="fa-brands fa-twitter fs-5"></i>
                        </a>
                    </div> --}}
                </div>
            </div>
            <!-- /Login Form -->
        </div>
    </div>

    <!-- Toast notifications script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                // Show success toast if available
                if (typeof toastr !== 'undefined') {
                    toastr.success('{{ session('success') }}');
                }
            @endif

            @if (session('error'))
                // Show error toast if available
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ session('error') }}');
                }
            @endif
        });
    </script>
@endsection

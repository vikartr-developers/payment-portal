@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Register Cover - Pages')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-sA+vx6kG2dfeUkhKXmhUNmGk8mPoYv+M2WZ2jskCuoU=" crossorigin="" />
@endsection

@section('page-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">

@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-p4Wy0B6JncblWefWPIU9K/AUqLBtEKKt+dva/xaZ8wk=" crossorigin=""></script>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>


@endsection

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">

            <!-- Illustration -->
            <div class="d-none d-lg-flex col-lg-6 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="{{ asset('assets/img/illustrations/auth-register-illustration-' . $configData['style'] . '.png') }}"
                        alt="auth-register-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-register-illustration-light.png"
                        data-app-dark-img="illustrations/auth-register-illustration-dark.png">

                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}"
                        alt="auth-register-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png">
                </div>
            </div>
            <!-- /Illustration -->

            <!-- Register Form -->
            <div class="d-flex col-12 col-lg-6 align-items-center p-sm-5">
                <div class="w-px-600 mx-auto ">

                    <!-- Logo -->
                    <div class="app-brand mb-4">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])</span>
                        </a>
                    </div>

                    <h3 class="mb-1">Adventure starts here 🚀</h3>
                    <p class="mb-4">Make your app management easy and fun!</p>

                    <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('register') }}">
                        @csrf
                        <input type="hidden" name="role" value="User">
                        <div class="row">
                            {{-- First Name --}}
                            <div class="mb-3 col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                    id="first_name" name="first_name" value="{{ old('first_name') }}"
                                    placeholder="Enter first name" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Last Name --}}
                            <div class="mb-3 col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                    id="last_name" name="last_name" value="{{ old('last_name') }}"
                                    placeholder="Enter last name" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="mb-3 col-md-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}"
                                    placeholder="Enter your email" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Country Code & Phone --}}
                            <div class="mb-3 ">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <select class="form-select @error('country_code') is-invalid @enderror"
                                            name="country_code" required style="max-width: 100px;">
                                            <option value="+91" {{ old('country_code') == '+91' ? 'selected' : '' }}>+91
                                                (IN)</option>
                                            <option value="+1" {{ old('country_code') == '+1' ? 'selected' : '' }}>+1
                                                (US)</option>
                                            <option value="+44" {{ old('country_code') == '+44' ? 'selected' : '' }}>+44
                                                (UK)</option>
                                            <!-- Add more as needed -->
                                        </select>
                                    </div>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone') }}"
                                        placeholder="Phone number" required>
                                </div>

                                @error('country_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>


                            {{-- Address Line 1 --}}
                            <div class="mb-3 col-md-6">
                                <label for="address_line1" class="form-label">Address Line 1</label>
                                <input type="text" class="form-control @error('address_line1') is-invalid @enderror"
                                    id="address_line1" name="address_line1" value="{{ old('address_line1') }}"
                                    placeholder="Enter address line 1" required>
                                @error('address_line1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Address Line 2 --}}
                            <div class="mb-3 col-md-6">
                                <label for="address_line2" class="form-label">Address Line 2</label>
                                <input type="text" class="form-control @error('address_line2') is-invalid @enderror"
                                    id="address_line2" name="address_line2" value="{{ old('address_line2') }}"
                                    placeholder="Enter address line 2 (optional)">
                                @error('address_line2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- City --}}
                            <div class="mb-3 col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                    id="city" name="city" value="{{ old('city') }}" placeholder="Enter city"
                                    required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- State --}}
                            <div class="mb-3 col-md-6">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror"
                                    id="state" name="state" value="{{ old('state') }}" placeholder="Enter state"
                                    required>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Country --}}
                            <div class="mb-3 col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror"
                                    id="country" name="country" value="{{ old('country') }}"
                                    placeholder="Enter country" required>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Zip --}}
                            <div class="mb-3 col-md-6">
                                <label for="zip" class="form-label">Zip Code</label>
                                <input type="text" class="form-control @error('zip') is-invalid @enderror"
                                    id="zip" name="zip" value="{{ old('zip') }}"
                                    placeholder="Enter zip code" required>
                                @error('zip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Location from Map --}}
                            {{-- <div class="mb-3">
                                <label for="location" class="form-label">Location (from map)</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                    id="location" name="location" value="{{ old('location') }}"
                                    placeholder="Pick location on map" readonly required>
                                <div id="map" style="height: 300px; margin-top: 10px;"></div>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}

                            {{-- Password --}}
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        placeholder="············" required>
                                    <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Confirm Password --}}
                            <div class="mb-3 col-md-6">
                                <label for="password-confirm" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password-confirm"
                                    name="password_confirmation" placeholder="············" required>
                            </div>

                            {{-- Terms --}}
                            <div class="mb-3 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms-conditions" required>
                                    <label class="form-check-label" for="terms-conditions">
                                        I agree to <a href="#">privacy policy & terms</a>
                                    </label>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <button type="submit" class="btn btn-primary d-grid w-100">
                                Sign up
                            </button>
                        </div>
                    </form>


                    <p class="text-center">
                        <span>Already have an account?</span>
                        <a href="{{ route('login') }}">
                            <span>Sign in instead</span>
                        </a>
                    </p>

                </div>
            </div>
            <!-- /Register Form -->

        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Default center (India) and zoom
            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                map.setView([lat, lng], 13);
            });

            // Tile layer from OpenStreetMap
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
            }).addTo(map);

            // Marker variable
            let marker;

            // On click, set marker and update hidden input
            map.on("click", function(e) {
                const lat = e.latlng.lat.toFixed(6);
                const lng = e.latlng.lng.toFixed(6);
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map);
                document.getElementById("location").value = `${lat},${lng}`;
            });
        });
    </script>

@endsection

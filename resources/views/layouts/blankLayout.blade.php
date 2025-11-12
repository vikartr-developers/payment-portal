@isset($pageConfigs)
    {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
    $configData = Helper::appClasses();

    /* Display elements */
    $customizerHidden = $customizerHidden ?? '';

@endphp

@extends('layouts/commonMaster')

@section('layoutContent')
    <!-- Content -->
    @yield('content')
    <style>
        .btn-primary {
            color: #fff;
            background-color: #000000;
            border-color: #000000;
        }

        :root {
            --bs-primary: #000000 !important;
        }
    </style>
    <!--/ Content -->
@endsection

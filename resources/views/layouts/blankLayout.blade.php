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
            background-color: #7367F0;
            border-color: #7367F0;
        }

        :root {
            --bs-primary: #7367F0 !important;
        }
    </style>
    <!--/ Content -->
@endsection

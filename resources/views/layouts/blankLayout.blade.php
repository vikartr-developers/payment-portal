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
            background-color: #A3E94B;
            border-color: #A3E94B;
        }

        :root {
            --bs-primary: #A3E94B !important;
        }
    </style>
    <!--/ Content -->
@endsection

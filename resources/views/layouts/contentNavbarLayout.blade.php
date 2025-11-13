@isset($pageConfigs)
    {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
    $configData = Helper::appClasses();
@endphp
@extends('layouts/commonMaster')
{{-- {{ dd('ll') }} --}}
@php
    /* Display elements */
    $contentNavbar = $contentNavbar ?? true;
    $containerNav = $containerNav ?? 'container-xxl';
    $isNavbar = $isNavbar ?? true;
    $isMenu = $isMenu ?? true;
    $isFlex = $isFlex ?? false;
    $isFooter = $isFooter ?? true;
    $customizerHidden = $customizerHidden ?? '';

    /* HTML Classes */
    $navbarDetached = 'navbar-detached';
    $menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
    if (isset($navbarType)) {
        $configData['navbarType'] = $navbarType;
    }
    $navbarType = isset($configData['navbarType']) ? $configData['navbarType'] : '';
    $footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
    $menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

    /* Content classes */
    $container =
        isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
            ? 'container-xxl'
            : 'container-fluid';

@endphp

@section('layoutContent')
    <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
        <div class="layout-container">
            <!-- put in layouts/layoutMaster.blade.php (near footer) -->
            <audio id="notif-sound" src="{{ asset('assets/media/notify.mp3') }}" preload="auto"></audio>
            <style>
                /* table.dataTable,
                                                                                                                                                                                                                    table.dataTable th,
                                                                                                                                                                                                                    table.dataTable td,
                                                                                                                                                                                                                    .dataTables_wrapper {
                                                                                                                                                                                                                        font-size: 12px !important;
                                                                                                                                                                                                                    }

                                                                                                                                                                                                                    .table> :not(caption)>*>* {
                                                                                                                                                                                                                        padding: 0.3rem 0.5rem;
                                                                                                                                                                                                                    }

                                                                                                                                                                                                                    table.table.dataTable,
                                                                                                                                                                                                                    table.table td,
                                                                                                                                                                                                                    table.table th {
                                                                                                                                                                                                                        font-size: 12px !important;
                                                                                                                                                                                                                    } */
            </style>
            <style>
                /* Rounded corners and card effect for the table */
                .table {
                    max-width: 100% !important;
                    color: #000 !important;
                    border-radius: 16px !important;
                    overflow: scroll;
                    /* font-size: 9px !important; */
                    box-shadow: 0 4px 18px rgba(153, 164, 188, 0.13);
                    /* background: #fff; */

                }

                /* Header styles */
                .table thead th {
                    /* background: linear-gradient(90deg, #f3e9fa 0%, #e8f9e9 100%); */
                    color: #000 !important;
                    /* font-size: 12px !important; */
                    letter-spacing: 0px;
                    font-weight: 600;
                    border: none;
                }

                .table td {
                    color: #000 !important;
                    /* font-size: 12px !important; */

                }

                /* Zebra striping for rows */
                .table-striped>tbody>tr:nth-of-type(odd) {
                    background-color: #f8fafc;
                }

                .table-striped>tbody>tr:nth-of-type(even) {
                    background-color: #f3f4f8;
                }

                /* Hover effect on rows */
                .table tbody tr:hover {
                    background: #e0f7fa !important;
                    box-shadow: 0 1px 6px rgba(60, 120, 200, 0.07);
                    transition: background 0.2s, box-shadow 0.2s;
                }

                /* Cell padding and font */
                .table th,
                .table td {
                    padding: 0.85rem 0.75rem;
                    font-size: 1rem;
                    vertical-align: middle !important;
                }

                /* Bolder important cells, like status or actions */
                .table td .fw-medium,
                .table td .text-success,
                .table td .text-danger,
                .table td .text-warning {
                    font-weight: 600;
                    font-size: 1.06em;
                }

                .table td small {
                    font-size: 0.92em;
                    color: #8678c5;
                }

                /* Rounded pagination for DataTables */
                .dataTables_wrapper .dataTables_paginate .paginate_button {
                    border-radius: 8px !important;
                    margin: 0 3px;
                    background: #f3e9fa !important;
                    color: #352e5a !important;
                    border: none !important;
                    transition: background 0.18s;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.current,
                .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                    background: #b3e2b0 !important;
                    color: #283593 !important;
                }

                /* Search and filter boxes styling */
                .dataTables_filter input,
                .dataTables_length select {
                    border-radius: 8px;
                    border: 1px solid #d1c4e9;
                    padding: 0.4em 1em;
                    font-size: 1em;
                    background: #fafaff;
                    margin-right: 6px;
                }

                #users-table_filter {
                    display: none;
                }

                .app-brand-logo.demo {
                    width: 90px;
                    height: auto;
                }

                .app-brand .demo {
                    height: auto;

                }

                .app-brand img {
                    width: 200px !important;
                }

                .light-style .menu .app-brand.demo {
                    height: auto !important;
                }

                .app-brand-logo.demo {
                    width: 200px;
                }

                .app-brand img {
                    z-index: 99999;
                }
            </style>
            {{-- <script>
                // Attempt to unlock audio and WebAudio on first user interaction (some browsers block autoplay)
                (function() {
                    const audio = document.getElementById('notif-sound');
                    if (!audio) return;

                    // expose a manual tester on window for debugging
                    window.testNotifSound = function() {
                        try {
                            audio.currentTime = 0;
                            audio.volume = 0.25;
                            const p = audio.play();
                            if (p && p.catch) p.catch(() => {});
                        } catch (e) {
                            console.warn('Test play failed', e);
                        }
                    };

                    function unlock() {
                        // Try to play & immediately pause to unlock HTMLAudioElement
                        audio.play().then(function() {
                            audio.pause();
                            audio.currentTime = 0;
                        }).catch(function() {
                            // ignore
                        });

                        // Also create & resume a global AudioContext to unlock WebAudio
                        try {
                            const AudioContext = window.AudioContext || window.webkitAudioContext;
                            if (AudioContext) {
                                if (!window._notifAudioContext) {
                                    window._notifAudioContext = new AudioContext();
                                }
                                // resume if suspended
                                if (window._notifAudioContext.state === 'suspended') {
                                    window._notifAudioContext.resume().catch(() => {});
                                }
                            }
                        } catch (e) {
                            // ignore
                        }

                        // Play a short test sound (best-effort)
                        try {
                            audio.currentTime = 0;
                            audio.volume = 0.25;
                            const p2 = audio.play();
                            if (p2 && p2.then) {
                                p2.then(function() {
                                    audio.pause();
                                    audio.currentTime = 0;
                                }).catch(() => {});
                            }
                        } catch (e) {
                            // ignore
                        }

                        window.removeEventListener('click', unlock);
                        window.removeEventListener('touchstart', unlock);
                    }

                    window.addEventListener('click', unlock, {
                        passive: true
                    });
                    window.addEventListener('touchstart', unlock, {
                        passive: true
                    });
                })();
            </script> --}}
            @if ($isMenu)
                @include('layouts/sections/menu/verticalMenu')
            @endif


            <!-- Layout page -->
            <div class="layout-page">

                {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
                {{-- <x-banner /> --}}

                <!-- BEGIN: Navbar-->
                @if ($isNavbar)
                    @include('layouts/sections/navbar/navbar')
                @endif
                <!-- END: Navbar-->


                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Content -->
                    @if ($isFlex)
                        <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
                        @else
                            <div class="{{ $container }} flex-grow-1 container-p-y">
                    @endif

                    @yield('content')

                </div>
                <!-- / Content -->

                <!-- Footer -->
                @if ($isFooter)
                    @include('layouts/sections/footer/footer')
                @endif
                <!-- / Footer -->
                <div class="content-backdrop fade"></div>
            </div>
            <!--/ Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    @if ($isMenu)
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    @endif
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
@endsection

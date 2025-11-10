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
                /* Make DataTables font very small as requested */
                table.dataTable,
                table.dataTable th,
                table.dataTable td,
                .dataTables_wrapper {
                    font-size: 12px !important;
                }

                .table> :not(caption)>*>* {
                    padding: 0.3rem 0.5rem;
                }

                /* Also target any table with class 'table' used by DataTables instances */
                table.table.dataTable,
                table.table td,
                table.table th {
                    font-size: 12px !important;
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

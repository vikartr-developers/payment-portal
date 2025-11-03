@php
    $configData = Helper::appClasses();
    $currentRouteName = Route::currentRouteName();

    function isSubmenuActive($submenu, $currentRouteName)
    {
        foreach ($submenu as $item) {
            if (isset($item->slug)) {
                if (is_array($item->slug)) {
                    foreach ($item->slug as $slug) {
                        if (str_starts_with($currentRouteName, $slug)) {
                            return true;
                        }
                    }
                } else {
                    if (str_starts_with($currentRouteName, $item->slug)) {
                        return true;
                    }
                }
            }
            if (isset($item->submenu) && isSubmenuActive($item->submenu, $currentRouteName)) {
                return true;
            }
        }
        return false;
    }
@endphp
@php
    // Helper function to check if any submenu is permitted and visible
    function hasVisibleSubmenu($submenu)
    {
        foreach ($submenu as $sub) {
            if (isset($sub->permission) && is_array($sub->permission)) {
                foreach ($sub->permission as $perm) {
                    if (auth()->user()->can($perm)) {
                        return true;
                    }
                }
            } else {
                // No permission defined means visible by default
                return true;
            }
        }
        return false;
    }
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    @if (!isset($navbarFull))
        <div class="app-brand demo">
            <a href="{{ url('/') }}" class="app-brand-link">
                <span class="app-brand-logo demo">
                    {{-- Your logo here --}}
                    <img src="{{ asset('home/images/favicon.png') }}" alt="Logo">
                </span>
                <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
            </a>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
                <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
            </a>
        </div>
    @endif

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)
            @if (isset($menu->menuHeader))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
                </li>
            @else
                @php
                    $hasPermission = false;

                    if (isset($menu->permission) && is_array($menu->permission)) {
                        foreach ($menu->permission as $permission) {
                            if (auth()->user()->can($permission)) {
                                $hasPermission = true;
                                break;
                            }
                        }
                    } else {
                        $hasPermission = true; // no permission array means public
                    }

                    $activeClass = '';
                    if ($hasPermission) {
                        if (isset($menu->slug)) {
                            if (is_array($menu->slug)) {
                                foreach ($menu->slug as $slug) {
                                    if ($currentRouteName === $slug) {
                                        $activeClass = 'active';
                                        break;
                                    }
                                }
                            } else {
                                if ($currentRouteName === $menu->slug) {
                                    $activeClass = 'active';
                                }
                            }
                        }

                        if (
                            !$activeClass &&
                            isset($menu->submenu) &&
                            isSubmenuActive($menu->submenu, $currentRouteName)
                        ) {
                            $activeClass = 'active open';
                        }
                    }
                @endphp

                @if ($hasPermission)
                    <li class="menu-item {{ $activeClass }}">
                        <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                            class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                            @if (isset($menu->target) && !empty($menu->target)) target="_blank" @endif>
                            @isset($menu->icon)
                                <i class="{{ $menu->icon }}"></i>
                            @endisset
                            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                            @isset($menu->badge)
                                <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}
                                </div>
                            @endisset
                        </a>

                        @isset($menu->submenu)
                            <ul class="menu-sub">
                                @foreach ($menu->submenu as $submenu)
                                    @php
                                        $subHasPermission = false;
                                        if (isset($submenu->permission) && is_array($submenu->permission)) {
                                            foreach ($submenu->permission as $permission) {
                                                if (auth()->user()->can($permission)) {
                                                    $subHasPermission = true;
                                                    break;
                                                }
                                            }
                                        } else {
                                            $subHasPermission = true;
                                        }

                                        $subActiveClass = '';
                                        if ($subHasPermission) {
                                            if (
                                                $currentRouteName === $submenu->slug ||
                                                (isset($submenu->slug) &&
                                                    str_starts_with($currentRouteName, $submenu->slug))
                                            ) {
                                                $subActiveClass = 'active';
                                            }
                                        }
                                    @endphp

                                    @if ($subHasPermission)
                                        <li class="menu-item {{ $subActiveClass }}">
                                            <a href="{{ url($submenu->url) }}" class="menu-link"
                                                @if (isset($submenu->target) && !empty($submenu->target)) target="_blank" @endif>
                                                {{ __($submenu->name) }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endisset
                    </li>
                @endif
            @endif
        @endforeach
    </ul>
</aside>

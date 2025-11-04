@php
    $configData = Helper::appClasses();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    {{-- App logo --}}
    @if (!isset($navbarFull))
        <div class="app-brand demo">
            <a href="{{ url('/') }}" class="app-brand-link">
                <span class="app-brand-logo demo">
                    @include('_partials.macros', ['height' => 20])
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
            @php
                $showMenu = false;

                // 1. Check top-level menu permissions
                if (isset($menu->permission) && is_array($menu->permission)) {
                    foreach ($menu->permission as $perm) {
                        if (Auth::user()->can($perm)) {
                            $showMenu = true;
                            break;
                        }
                    }
                }

                // 2. If no top-level access, check any submenu permissions
                if (!$showMenu && isset($menu->submenu)) {
                    foreach ($menu->submenu as $submenu) {
                        if (isset($submenu->permission) && is_array($submenu->permission)) {
                            foreach ($submenu->permission as $perm) {
                                if (Auth::user()->can($perm)) {
                                    $showMenu = true;
                                    break 2; // exit both loops
                                }
                            }
                        } else {
                            // No permission tag means show by default
                            $showMenu = true;
                            break;
                        }
                    }
                }
            @endphp

            {{-- Menu headers --}}
            @if (isset($menu->menuHeader) && $showMenu)
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
                </li>
            @elseif($showMenu)
                @php
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName();

                    if ($currentRouteName === $menu->slug) {
                        $activeClass = 'active';
                    } elseif (isset($menu->submenu)) {
                        if (is_array($menu->slug)) {
                            foreach ($menu->slug as $slug) {
                                if (str_contains($currentRouteName, $slug) && strpos($currentRouteName, $slug) === 0) {
                                    $activeClass = 'active open';
                                }
                            }
                        } else {
                            if (
                                str_contains($currentRouteName, $menu->slug) &&
                                strpos($currentRouteName, $menu->slug) === 0
                            ) {
                                $activeClass = 'active open';
                            }
                        }
                    }
                @endphp

                {{-- Main menu --}}
                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($menu->target) && !empty($menu->target)) target="_blank" @endif>
                        @isset($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @endisset
                        <div>{{ __($menu->name ?? '') }}</div>

                        @isset($menu->badge)
                            <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
                        @endisset
                    </a>

                    {{-- Submenus (with permission check) --}}
                    @isset($menu->submenu)
                        <ul class="menu-sub">
                            @foreach ($menu->submenu as $submenu)
                                @php
                                    $showSubmenu = false;
                                    if (isset($submenu->permission) && is_array($submenu->permission)) {
                                        foreach ($submenu->permission as $perm) {
                                            if (Auth::user()->can($perm)) {
                                                $showSubmenu = true;
                                                break;
                                            }
                                        }
                                    } else {
                                        $showSubmenu = true; // if no permission defined
                                    }
                                @endphp

                                @if ($showSubmenu)
                                    <li
                                        class="menu-item {{ Route::currentRouteName() === $submenu->slug ? 'active' : '' }}">
                                        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0);' }}"
                                            class="menu-link" @if (isset($submenu->target) && !empty($submenu->target)) target="_blank" @endif>
                                            @isset($submenu->icon)
                                                <i class="{{ $submenu->icon }}"></i>
                                            @endisset
                                            <div>{{ __($submenu->name ?? '') }}</div>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endisset
                </li>
            @endif
        @endforeach
    </ul>
</aside>

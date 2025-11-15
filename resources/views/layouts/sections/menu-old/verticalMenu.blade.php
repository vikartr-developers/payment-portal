@php
    $configData = Helper::appClasses();
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- ! Hide app brand if navbar-full -->

    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)
            {{-- adding active and open class if child is active --}}
            {{-- menu headers --}}
            @if (isset($menu->menuHeader))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
                </li>
            @else
                {{-- active menu method --}}
                @php
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName();

                    // Check if current menu item is directly active
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

                    // If not directly active, check if it has submenus and any submenu is active
                    if (!$activeClass && isset($menu->submenu)) {
                        $hasActiveSubmenu = false;

                        // Function to recursively check submenu items
                        $checkSubmenu = function ($submenuItems, $currentRoute) use (&$checkSubmenu) {
                            foreach ($submenuItems as $item) {
                                // Check direct match
                                if (isset($item->slug)) {
                                    if (is_array($item->slug)) {
                                        foreach ($item->slug as $slug) {
                                            if (
                                                $currentRoute === $slug ||
                                                (str_contains($currentRoute, $slug) &&
                                                    strpos($currentRoute, $slug) === 0)
                                            ) {
                                                return true;
                                            }
                                        }
                                    } else {
                                        if (
                                            $currentRoute === $item->slug ||
                                            (str_contains($currentRoute, $item->slug) &&
                                                strpos($currentRoute, $item->slug) === 0)
                                        ) {
                                            return true;
                                        }
                                    }
                                }

                                // Recursively check nested submenus
                                if (isset($item->submenu) && $checkSubmenu($item->submenu, $currentRoute)) {
                                    return true;
                                }
                            }
                            return false;
                        };

                        $hasActiveSubmenu = $checkSubmenu($menu->submenu, $currentRouteName);

                        if ($hasActiveSubmenu) {
                            $activeClass = 'active open';
                        } else {
                            // Fallback to original logic for parent menu route matching
                            if (isset($menu->slug)) {
                                if (is_array($menu->slug)) {
                                    foreach ($menu->slug as $slug) {
                                        if (
                                            str_contains($currentRouteName, $slug) &&
                                            strpos($currentRouteName, $slug) === 0
                                        ) {
                                            $activeClass = 'active open';
                                            break;
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
                        }
                    }
                @endphp
                {{-- main menu --}}
                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                        @isset($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @endisset
                        <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                        @isset($menu->badge)
                            <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
                        @endisset
                    </a>
                    {{-- submenu --}}
                    @isset($menu->submenu)
                        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                    @endisset
                </li>
            @endif
        @endforeach
    </ul>
</aside>

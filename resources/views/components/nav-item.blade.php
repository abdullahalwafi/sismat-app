@props(['route', 'icon', 'label'])
@php($active = $route === 'dashboard' ? request()->routeIs('dashboard') : request()->routeIs(explode('.', $route)[0] . '.*'))
@if ($route === 'dashboard' || auth()->user()?->can('module-view', explode('.', $route)[0]))
    <a href="{{ route($route) }}" @class(['nav-item', 'active' => $active]) @if ($active) aria-current="page" @endif>
        <i aria-hidden="true" class="{{ $icon }}"></i>
        <span>{{ $label }}</span>
        @if ($active)
            <span class="nav-active-dot"></span>
        @endif
    </a>

@endif

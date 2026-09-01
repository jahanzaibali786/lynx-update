@php
    $item = $item ?? [];
    $depth = $depth ?? 0;
    $path = $path ?? '0';
    $children = $item['children'] ?? [];
    $hasChildren = ! empty($children);
    $isActive = (bool) ($item['is_active'] ?? false);
    $isExpanded = (bool) ($item['is_expanded'] ?? false);
    $icon = $item['icon'] ?? 'ti ti-circle';
    $label = $item['label'] ?? '';
    $url = $item['url'] ?? null;
    $panelId = 'lynx-sidebar-panel-' . str_replace('.', '-', $path);
    $triggerId = 'lynx-sidebar-trigger-' . str_replace('.', '-', $path);
@endphp

<li
    class="lynx-sidebar__item {{ $hasChildren ? 'lynx-sidebar__item--parent' : 'lynx-sidebar__item--leaf' }} {{ $isActive ? 'is-active' : '' }} {{ $isExpanded ? 'is-open' : '' }}"
    role="none"
    data-lynx-sidebar-item
    data-lynx-sidebar-depth="{{ $depth }}"
>
    @if ($hasChildren)
        <button
            type="button"
            id="{{ $triggerId }}"
            class="lynx-sidebar__trigger"
            role="treeitem"
            aria-haspopup="menu"
            aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
            aria-controls="{{ $panelId }}"
            data-lynx-sidebar-trigger
        >
            <span class="lynx-sidebar__icon"><i class="{{ $icon }}"></i></span>
            <span class="lynx-sidebar__label">{{ __($label) }}</span>
            <span class="lynx-sidebar__chevron"><i class="ti ti-chevron-right"></i></span>
        </button>

        <ul
            id="{{ $panelId }}"
            class="lynx-sidebar__submenu {{ $isExpanded ? 'is-open' : '' }}"
            role="menu"
            aria-labelledby="{{ $triggerId }}"
            data-lynx-sidebar-submenu
            data-lynx-sidebar-depth="{{ $depth + 1 }}"
        >
            @foreach ($children as $index => $child)
                @include('layouts.partials.custom-sidebar-item', [
                    'item' => $child,
                    'depth' => $depth + 1,
                    'path' => $path . '.' . $index,
                ])
            @endforeach
        </ul>
    @else
        <a
            href="{{ $url ?: '#' }}"
            class="lynx-sidebar__link {{ $isActive ? 'is-active' : '' }}"
            role="treeitem"
            @if ($isActive) aria-current="page" @endif
        >
            <span class="lynx-sidebar__icon"><i class="{{ $icon }}"></i></span>
            <span class="lynx-sidebar__label">{{ __($label) }}</span>
        </a>
    @endif
</li>

@php
    use App\Models\Utility;
    use App\Support\LynxSidebarMenu;

    $logo = Utility::get_file('uploads/logo/');
    $companyLogoDark = Utility::getValByName('company_logo_dark');
    $companyLogoLight = Utility::getValByName('company_logo_light');
    $companySmallLogo = Utility::getValByName('company_small_logo');
    $modeSetting = Utility::mode_layout();
    $user = Auth::user();
    $sidebarItems = LynxSidebarMenu::items();
    $sidebarLogo = $modeSetting['cust_darklayout'] && $modeSetting['cust_darklayout'] == 'on'
        ? ($companyLogoLight ?: 'logo-dark.png')
        : ($companyLogoDark ?: 'logo-dark.png');
    $customSidebarId = 'lynx-sidebar-root';
@endphp

<aside class="lynx-sidebar" id="{{ $customSidebarId }}" data-lynx-sidebar data-lynx-sidebar-id="{{ $customSidebarId }}">
    <div class="lynx-sidebar__mobile-bar">
        <button type="button" class="lynx-sidebar__mobile-toggle" data-lynx-sidebar-toggle-drawer aria-label="{{ __('Toggle navigation') }}">
            <i class="ti ti-menu-2"></i>
        </button>
        <a href="{{ route('dashboard') }}" class="lynx-sidebar__brand-link" aria-label="{{ config('app.name', 'Lynx') }}">
            <img
                class="lynx-sidebar__brand-image"
                src="{{ asset('public/assets/images/' . $sidebarLogo) }}"
                alt="{{ config('app.name', 'Lynx') }}"
            >
        </a>
    </div>

    <div class="lynx-sidebar__backdrop" data-lynx-sidebar-backdrop></div>

    <div class="lynx-sidebar__panel" data-lynx-sidebar-panel>
        <div class="lynx-sidebar__brand">
            <a href="{{ route('dashboard') }}" class="lynx-sidebar__brand-link">
                <img
                    class="lynx-sidebar__brand-image"
                    src="{{ asset('public/assets/images/' . $sidebarLogo) }}"
                    alt="{{ config('app.name', 'Lynx') }}"
                >
            </a>
        </div>

        <div class="lynx-sidebar__actions" role="toolbar" aria-label="{{ __('Sidebar actions') }}">
            <a href="#" class="lynx-sidebar__action" data-lynx-sidebar-pin aria-pressed="false" aria-label="{{ __('Pin sidebar') }}">
                <i class="ti ti-lock-open lynx-sidebar__action-icon lynx-sidebar__action-icon--unpinned" data-bs-toggle="tooltip" title="{{ __('Pin sidebar') }}"></i>
                <i class="ti ti-lock lynx-sidebar__action-icon lynx-sidebar__action-icon--pinned" data-bs-toggle="tooltip" title="{{ __('Unpin sidebar') }}"></i>
            </a>

            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout-sidebar').submit();" class="lynx-sidebar__action" data-bs-toggle="tooltip" title="{{ __('Logout') }}">
                <i class="ti ti-power"></i>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['sidebar' => 'acrn']) }}" class="lynx-sidebar__action" data-bs-toggle="tooltip" title="{{ __('Switch to Acrn sidebar') }}">
                <i class="ti ti-layout-sidebar-left-collapse"></i>
            </a>

            <form id="frm-logout-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>

        <div class="lynx-sidebar__scroll" data-lynx-sidebar-scroll>
            <nav class="lynx-sidebar__nav" aria-label="{{ __('Main navigation') }}">
                <ul class="lynx-sidebar__menu" role="tree">
                    @foreach ($sidebarItems as $index => $item)
                        @include('layouts.partials.custom-sidebar-item', [
                            'item' => $item,
                            'depth' => 0,
                            'path' => (string) $index,
                        ])
                    @endforeach
                </ul>
            </nav>
        </div>
    </div>
</aside>


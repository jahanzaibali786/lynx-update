@php
    use App\Models\Utility;
    // $logo=asset(Storage::url('uploads/logo/'));
    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_logo = Utility::getValByName('company_logo_dark');
    $company_logos = Utility::getValByName('company_logo_light');
    $company_small_logo = Utility::getValByName('company_small_logo');
    $setting = \App\Models\Utility::colorset();
    $mode_setting = \App\Models\Utility::mode_layout();
    $emailTemplate = \App\Models\EmailTemplate::first();
    $lang = Auth::user()->lang;

    $profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .nav-container {
        font-size: 16px;
    }

    .dash-submenu .dash-item::before {
        vertical-align: top !important;
        margin-top: 5px !important;
        align-self: flex-start !important;
    }

    .dash-submenu .dash-item>.dash-link {
        display: flex !important;
        align-items: baseline !important;
    }

    #account-id,
    #account-reports-id,
    #hr,
    #hr-admin,
    #accounts-id,
    #banking-id,
    #vouchers-id {
        background-color: white !important;
    }

    /* All hover - main menu and submenus */
    .dash-link:hover,
    .dash-submenu .dash-item>.dash-link:hover {
        color: #ffffff !important;

    }

    /* Hover on dash-item should also affect dash-link text */
    .dash-submenu .dash-item:hover>.dash-link {
        color: #ffffff !important;
    }

    /* Optional: keep background too */
    .dash-submenu .dash-item:hover {
        background: #0d0d5e !important;
    }

    .dash-link,
    .dash-submenu .dash-item {
        transition: none !important;
    }




    .dash-link:hover,
    .dash-submenu .dash-item:hover {
        background: #0d0d5e !important;
        color: #ffffff !important;

        .dash-micon svg path {
            stroke: #fff !important;
        }

    }

    .dash-item .dash-micon:hover svg path {
        stroke: #fff !important;
    }

    .dash-submenu .dash-item:not(.dash-hasmenu) {
        position: relative;
        left: -20px;
        width: 225px;
        padding: 1px 13px !important;
    }


    .dash-submenu {
        animation: none;
        transition: none;
        overflow: hidden;
    }


    .dash-submenu.show {
        animation: easeInDrop 0.3s ease-in forwards;
    }

    @keyframes easeInDrop {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ps__thumb-y {
        display: none;
    }

    .os-viewport,
    .os-content,
    .menu-container,
    #menu {
        touch-action: pan-y;
        -webkit-overflow-scrolling: touch;
        pointer-events: auto;
    }

    .dash-submenu .dash-item {
        border-radius: 8px;
        /* adjust as needed */
        overflow: hidden;
        /* IMPORTANT: keeps child inside rounded corners */
    }

    .dash-item {
        border-radius: 8px;
        overflow: hidden;
    }

    .dash-link {
        display: block;
        width: 100%;
        box-sizing: border-box;
        border-radius: 8px;
    }

    /* submenu hover ONLY */
    .dash-submenu .dash-item:hover>.dash-link {
        background: #0d0d5e !important;
        color: #fff !important;
    }
</style>
{{-- new theam code --}}
<nav class="dash-sidebar light-sidebar ">
    <div id="nav" class="nav-container navbar-wrapper d-flex">
        <div class="nav-content d-flex">
            <!-- Logo Start -->
            <div class="logo position-relative" style="">
                {{-- <a href="#" class="flag">
                </a> --}}

                <!-- Logo can be added directly -->
                <!-- <img src="img/logo/logo-white.svg" alt="logo" /> -->

                <!-- Or added via css to provide different ones for different color themes -->
                <div class="img">
                    <div class="m-header main-logo" id="main_logo_0123">
                        <a href="#" class="b-brand"></a>
                        {{--     <img src="{{ asset(Storage::url('uploads/logo/'.$logo)) }}"
                                alt="{{ env('APP_NAME') }}" class="logo logo-lg" /> --}}
                        @if ($mode_setting['cust_darklayout'] && $mode_setting['cust_darklayout'] == 'on')
                            <img src="{{ $logo . '/' . (isset($company_logos) && !empty($company_logos) ? $company_logos : 'logo-dark.png') }}"
                                alt="{{ config('app.name', 'Lynx') }}" class="logo logo-lg" style="margin-left: -7px;">
                        @else
                            {{-- <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png') }}"
                                        alt="{{ config('app.name', 'Lynx') }}" class="logo logo-lg"
                                        style="margin-left: -7px;"> --}}
                            <img src="{{ asset('public/assets/images/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png')) }}"
                                alt="{{ config('app.name', 'Lynx') }}" class="logo logo-lg" style="margin-left: -7px;">

                        @endif
                    </div>
                </div>
            </div>
            <!-- Logo End -->

            <!-- User Menu Start -->
            {{-- <div class="user-container d-flex">
                <a href="{{ route('profile') }}" class="d-flex user position-relative" aria-haspopup="true"
                    aria-expanded="false">

                    <img class="profile" alt="profile"
                        src="{{ !empty(\Auth::user()->avatar) ? $profile . \Auth::user()->avatar : $profile . 'avatar.png' }}" />
                    <span class="theme-avtar">
                             <img src="{{ !empty(\Auth::user()->avatar) ? $profile . \Auth::user()->avatar :  $profile.'avatar.png'}}"
                    class="img-fluid rounded-circle">
                    </span>
                    <div class="name">{{ \Auth::user()->name }}</div>
                </a>
            </div> --}}
            <!-- User Menu End -->

            @php
                $users = \Auth::user();
                $profile = \App\Models\Utility::get_file('uploads/avatar/');
                $languages = \App\Models\Utility::languages();
                $settings = Utility::settings();
                $lang = isset($users->lang) ? $users->lang : 'en';
                if ($lang == null) {
                    $lang = 'en';
                }
                $LangName = \App\Models\Language::where('code', $lang)->first();

                $setting = \App\Models\Utility::colorset();
                $mode_setting = \App\Models\Utility::mode_layout();
                // dd($settings);
                $unseenCounter = App\Models\ChMessage::where('to_id', Auth::user()->id)
                    ->where('seen', 0)
                    ->count();
            @endphp
            <script>
                function hideDropdown() {
                    var dropdownContent = document.querySelector('.dropdown-content');
                    setTimeout(function() {
                        dropdownContent.style.display = 'none';
                    }, 1000); // Change the delay time (in milliseconds) as needed
                }
            </script>

            <style>
                /* Style for the dropdown menu */
                .dropdowni {
                    position: relative;
                    display: inline-block;
                }

                .dropdown-contente {
                    display: none;
                    position: absolute;
                    background-color: #f9f9f9;
                    min-width: 160px;
                    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
                    z-index: 1;
                    opacity: 0;
                    transition: opacity 10s ease-in-out;
                }

                .dropdowni:hover .dropdown-contente {
                    display: block;
                    opacity: 1;
                }

                /* Style for the list items */
                .dropdown-contente li {
                    padding: 12px;
                    text-decoration: none;
                    display: block;
                }

                .dropdown-contente li:hover {
                    background-color: #f1f1f1;
                }
            </style>

            <!-- Icons Menu Start -->
            <ul class="list-unstyled list-inline text-center menu-icons" style="z-index: 10000;">
                <li class="list-inline-item">
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('frm-logout').submit();"
                        class="dropdown-item">
                        <i data-acorn-icon="logout" data-acorn-size="18" data-bs-toggle="tooltip" title="LogOut"></i>
                    </a>
                </li>

                <form id="frm-logout" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>


                <li class="list-inline-item">
                    <a class="dash-head-link arrow-none me-0" href="{{ url('chats') }}" aria-haspopup="false"
                        aria-expanded="false">
                        <i class="ti ti-brand-hipchat" data-bs-toggle="tooltip" title="Chats"></i>
                    </a>
                </li>
                <li class="list-inline-item">
                    <a href="#" id="pinButton" class="pin-button">
                        <i data-acorn-icon="lock-on" class="unpin" data-acorn-size="18" data-bs-toggle="tooltip"
                            title="UnPin"></i>
                        <i data-acorn-icon="lock-off" class="pin" data-acorn-size="18" data-bs-toggle="tooltip"
                            title="Pin"></i>
                    </a>
                </li>

                <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
                <script>
                    function handleButtonClick(u) {
                        var combinedData = {
                            cust_darklayout: u,
                            _token: "{{ csrf_token() }}",
                        };

                        $.ajax({
                            type: 'POST',
                            url: "{{ route('save.setting') }}",
                            data: combinedData,
                            success: function(response) {

                                console.log(response);
                                location.reload();
                            },
                            error: function(error) {

                                console.log(error);
                            }
                        });

                    };
                </script>

                {{-- <li class="list-inline-item" id="settingsForm1" style="margin-top: 6px;">
                    {{ Form::model($settings, ['route' => 'save.setting', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'settingsForm']) }}
                    <a href="#" id="colorButton" class="hao">
                        <i data-acorn-icon="light-on" class="light hao1 " data-hao="light" data-acorn-size="18"
                            style="color: white"></i>
                        <i data-acorn-icon="light-off" class="dark hao1" data-hao="dark" data-acorn-size="18"
                            style="color: white"></i>
                    </a>
                    {{ Form::close() }}

                </li> --}}


                <li class="dropdowni" onmouseleave="hideDropdown()">
                    {{-- <a href="#"> <i data-acorn-icon="bell" data-acorn-size="18"></i></a>
                    <div
                        class="dropdown-contente dropdown-menu dropdown-menu-end wide notification-dropdown scroll-out">
                        <div class="scroll">
                            <ul class="list-unstyled border-last-none">
                                <li class="mb-3 pb-3 border-bottom border-separator-light d-flex">
                                    <img src="img/profile/profile-1.webp"
                                        class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                                    <div class="align-self-center">
                                        <a href="#">Joisse Kaycee just sent a new comment!</a>
                                    </div>
                                </li>
                                <li class="mb-3 pb-3 border-bottom border-separator-light d-flex">
                                    <img src="img/profile/profile-2.webp"
                                        class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                                    <div class="align-self-center">
                                        <a href="#">New order received! It is total $147,20.</a>
                                    </div>
                                </li>
                                <li class="mb-3 pb-3 border-bottom border-separator-light d-flex">
                                    <img src="img/profile/profile-3.webp"
                                        class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                                    <div class="align-self-center">
                                        <a href="#">3 items just added to wish list by a user!</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div> --}}
                </li>

            </ul>
            <!-- Icons Menu End -->

            <!-- Menu Start -->
            <div class="menu-container navbar-content flex-grow-1" style="display: block;">
                @if (\Auth::user()->type != 'client')

                    <ul id="menu" class="menu">

                        {{-- <li>
                        <a href="{{route('profile')}}" class="dropdown-item">
                    <svg width="50px" height="50px" viewBox="-24 -24 72.00 72.00" fill="none"
                        xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M16.5 7.063C16.5 10.258 14.57 13 12 13c-2.572 0-4.5-2.742-4.5-5.938C7.5 3.868 9.16 2 12 2s4.5 1.867 4.5 5.063zM4.102 20.142C4.487 20.6 6.145 22 12 22c5.855 0 7.512-1.4 7.898-1.857a.416.416 0 0 0 .09-.317C19.9 18.944 19.106 15 12 15s-7.9 3.944-7.989 4.826a.416.416 0 0 0 .091.317z"
                                fill="#ffffff"></path>
                        </g>
                    </svg><span class="label">{{__('Profile')}}</span>
                    </a>
                    </li> --}}
                        <!--------------------- Start Dashboard ----------------------------------->
                        @if (Gate::check('show hrm dashboard') ||
                                Gate::check('show project dashboard') ||
                                Gate::check('show account dashboard') ||
                                Gate::check('show crm dashboard') ||
                                Gate::check('show pos dashboard') ||
                                Gate::check('show clientuser dashboard'))

                            <li class="dash-item dash-hasmenu">
                                <a href="#tent" data-role="button" data-bs-toggle="" data-role="button"
                                    class="dash-link {{ Request::segment(1) == null ||
                                    Request::segment(1) == 'account-dashboard' ||
                                    Request::segment(1) == 'income report' ||
                                    Request::segment(1) == 'report' ||
                                    Request::segment(1) == 'reports-monthly-cashflow' ||
                                    Request::segment(1) == 'reports-quarterly-cashflow' ||
                                    Request::segment(1) == 'reports-payroll' ||
                                    Request::segment(1) == 'reports-leave' ||
                                    Request::segment(1) == 'reports-monthly-attendance' ||
                                    Request::segment(1) == 'reports-lead' ||
                                    Request::segment(1) == 'reports-deal' ||
                                    Request::segment(1) == 'pos-dashboard' ||
                                    Request::segment(1) == 'reports-warehouse' ||
                                    Request::segment(1) == 'reports-daily-purchase' ||
                                    Request::segment(1) == 'reports-monthly-purchase' ||
                                    Request::segment(1) == 'reports-daily-pos' ||
                                    Request::segment(1) == 'reports-monthly-pos' ||
                                    Request::segment(1) == 'hrm-dashboard' ||
                                    Request::segment(1) == 'reports-pos-vs-purchase' ||
                                    Request::segment(1) == 'clientuser-dashboard' ||
                                    Request::segment(1) == 'crm-dashboard' ||
                                    Request::segment(1) == 'workspace-dashboard' ||
                                    Request::segment(1) == 'project-dashboard'
                                        ? 'active'
                                        : '' }}">
                                    <span class="dash-micon">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M13.5277 9.12871H10.5902C10.2031 9.13034 9.83224 9.28469 9.55859 9.55826C9.28502 9.83264 9.13068 10.2034 9.12929 10.5905V13.5279C9.12904 13.7198 9.16669 13.9101 9.24002 14.0873C9.31335 14.2645 9.42099 14.4254 9.55671 14.5617C9.69235 14.6973 9.85347 14.8051 10.0308 14.8778C10.2081 14.9513 10.3982 14.9888 10.5902 14.9888H13.5277C13.9151 14.9888 14.2865 14.8345 14.5605 14.5609C14.8344 14.2865 14.9884 13.915 14.9886 13.5279V10.5905C14.9888 10.3986 14.9511 10.2083 14.8778 10.0311C14.8044 9.85387 14.6969 9.693 14.5612 9.55662C14.4254 9.42106 14.2643 9.31326 14.087 9.24058C13.9097 9.16708 13.7196 9.12871 13.5277 9.12871ZM13.5277 1H2.46087C2.26896 1 2.07885 1.03757 1.90156 1.11106C1.72419 1.18456 1.56307 1.29234 1.42742 1.4279C1.2917 1.56346 1.18415 1.72434 1.11074 1.90155C1.0374 2.07876 0.999757 2.26904 1 2.46095V5.39836C0.999675 5.59027 1.03724 5.78053 1.11057 5.95856C1.18382 6.13576 1.29146 6.29664 1.4271 6.4322C1.56282 6.56858 1.72402 6.67637 1.9014 6.74905C2.07877 6.82255 2.26888 6.86012 2.46087 6.86012H13.5277C13.7196 6.86012 13.9097 6.82256 14.087 6.74988C14.2643 6.67638 14.4254 6.56858 14.5612 6.43302C14.6969 6.29746 14.8044 6.13576 14.8778 5.95856C14.9511 5.78135 14.9888 5.59108 14.9886 5.39917V2.46095C14.9888 2.26904 14.9511 2.07876 14.8778 1.90155C14.8044 1.72434 14.6969 1.56346 14.5612 1.4279C14.4254 1.29234 14.2643 1.18456 14.087 1.11106C13.9097 1.03757 13.7196 1 13.5277 1ZM5.39844 9.12871H2.46087C2.26888 9.12871 2.07877 9.16628 1.9014 9.23977C1.72402 9.31327 1.56282 9.42025 1.4271 9.55662C1.29146 9.69218 1.18382 9.85305 1.11057 10.0311C1.03724 10.2083 0.999675 10.3986 1 10.5905V13.5279C0.999757 13.7198 1.0374 13.9101 1.11074 14.0873C1.18415 14.2645 1.2917 14.4254 1.42742 14.5617C1.56307 14.6973 1.72419 14.8051 1.90156 14.8778C2.07885 14.9513 2.26896 14.9888 2.46087 14.9888H5.39844C5.59035 14.9888 5.78038 14.9513 5.95775 14.8778C6.13504 14.8051 6.29616 14.6973 6.43188 14.5617C6.56761 14.4254 6.67516 14.2645 6.74849 14.0873C6.82182 13.9101 6.85955 13.7198 6.85931 13.5279V10.5905C6.85792 10.2034 6.70358 9.83264 6.42992 9.55826C6.15627 9.28469 5.78552 9.13034 5.39844 9.12871Z"
                                                stroke="#474B4E" stroke-width="1.90548" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="dash-mtext">{{ __('Dashboard') }}</span>
                                </a>
                                <ul id="tent" class="dash-submenu">
                                    @if (\Auth::user()->show_account() == 1 && Gate::check('show account dashboard'))
                                        <li id="account-id" class="dash-item dash-hasmenu">
                                            <a class="dash-link {{ Request::segment(1) == null || Request::segment(1) == 'account-dashboard' || Request::segment(1) == 'report' || Request::segment(1) == 'reports-monthly-cashflow' || Request::segment(1) == 'reports-quarterly-cashflow' ? ' active' : '' }}"
                                                href="#tree">{{ __('Accounting ') }}<span class="dash-arrow"></a>
                                            <ul id="tree" class="dash-submenu ">
                                                @can('show account dashboard')
                                                    <li class="dash-item ">
                                                        <a class="dash-link {{ Request::segment(1) == null || Request::segment(1) == 'account-dashboard' ? ' active' : '' }}"
                                                            href="{{ route('dashboard') }}">{{ __(' Overview') }}</a>
                                                    </li>
                                                @endcan
                                                @if (Gate::check('income report') ||
                                                        Gate::check('expense report') ||
                                                        Gate::check('income vs expense report') ||
                                                        Gate::check('tax report') ||
                                                        Gate::check('loss & profit report') ||
                                                        Gate::check('invoice report') ||
                                                        Gate::check('bill report') ||
                                                        Gate::check('stock report') ||
                                                        Gate::check('invoice report') ||
                                                        Gate::check('manage transaction') ||
                                                        Gate::check('statement report'))
                                                    <li id="account-reports-id" class="dash-item dash-hasmenu">
                                                        <a class="dash-link {{ Request::segment(1) == 'report' || Request::segment(1) == 'reports-monthly-cashflow' || Request::segment(1) == 'reports-quarterly-cashflow' ? 'active ' : '' }}"
                                                            href="#crs">{{ __('Reports') }}</a>
                                                        <ul id="crs" class="dash-submenu">
                                                            @can('statement report')
                                                                <li class="dash-item ">
                                                                    <a class="dash-link {{ Request::route()->getName() == 'report.account.statement' ? ' active' : '' }}"
                                                                        href="{{ route('report.account.statement') }}">{{ __('Account Statement') }}</a>
                                                                </li>
                                                            @endcan
                                                            {{-- @can('invoice report')
                                                                <li class="dash-item ">
                                                                    <a class="dash-link {{ Request::route()->getName() == 'report.invoice.summary' ? ' active' : '' }}"
                            href="{{ route('report.invoice.summary') }}">{{ __('Invoice Summary') }}</a>
                    </li>
                    @endcan --}}
                                                            {{-- <li class="dash-item ">
                                                                <a class="dash-link {{ Request::route()->getName() == 'report.sales' ? ' active' : '' }}"
                                    href="{{ route('report.sales') }}">{{ __('Sales Report') }}</a>
                            </li>
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::route()->getName() == 'report.receivables' ? ' active' : '' }}"
                                    href="{{ route('report.receivables') }}">{{ __('Receivables') }}</a>
                            </li> --}}
                                                            {{-- @can('bill report')
                                                                <li class="dash-item">
                                                                    <a class="dash-link {{ Request::route()->getName() == 'report.bill.summary' ? ' active' : '' }}"
                            href="{{ route('report.bill.summary') }}">{{ __('Bill Summary') }}</a>
                    </li>
                    @endcan
                    @can('stock report')
                    <li class="dash-item ">
                        <a href="{{ route('report.product.stock.report') }}"
                            class="dash-link {{ Request::route()->getName() == 'report.product.stock.report' ? ' active' : '' }}">{{ __('Product Stock') }}</a>
                    </li>
                    @endcan

                    @can('loss & profit report')
                    <li class="dash-item">
                        <a class="dash-link  {{ request()->is('reports-monthly-cashflow') || request()->is('reports-quarterly-cashflow') ? 'active' : '' }}"
                            href="{{ route('report.monthly.cashflow') }}">{{ __('Cash Flow') }}</a>
                    </li>
                    @endcan --}}
                                                            @can('manage transaction')
                                                                <li class="dash-item ">
                                                                    <a class="dash-link {{ Request::route()->getName() == 'transaction.index' || Request::route()->getName() == 'transfer.create' || Request::route()->getName() == 'transaction.edit' ? ' active' : '' }}"
                                                                        href="{{ route('transaction.index') }}">{{ __('Transaction') }}</a>
                                                                </li>
                                                            @endcan
                                                            {{-- @can('income report')
                                                                <li class="dash-item ">
                                                                    <a class="dash-link {{ Request::route()->getName() == 'report.income.summary' ? ' active' : '' }}"
                    href="{{ route('report.income.summary') }}">{{ __('Income Summary') }}</a>
                    </li>
                    @endcan
                    @can('expense report')
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::route()->getName() == 'report.expense.summary' ? ' active' : '' }}"
                            href="{{ route('report.expense.summary') }}">{{ __('Expense Summary') }}</a>
                    </li>
                    @endcan
                    @can('income vs expense report')
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::route()->getName() == 'report.income.vs.expense.summary' ? ' active' : '' }}"
                            href="{{ route('report.income.vs.expense.summary') }}">{{ __('Income VS Expense') }}</a>
                    </li>
                    @endcan
                    @can('tax report')
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::route()->getName() == 'report.tax.summary' ? ' active' : '' }}"
                            href="{{ route('report.tax.summary') }}">{{ __('Tax Summary') }}</a>
                    </li>
                    @endcan --}}
                                                        </ul>
                                                    </li>
                                                @endif
                                            </ul>
                                        </li>
                                    @endif

                                    {{-- @if (\Auth::user()->show_hrm() == 1)
                                        @can('show hrm dashboard')
                                            <li class="dash-item dash-hasmenu ">
                                                <a class="dash-link {{ Request::segment(1) == 'hrm-dashboard' || Request::segment(1) == 'reports-payroll' || Request::segment(1) == 'reports-leave' || Request::segment(1) == 'reports-monthly-attendance' ? ' active dash-trigger' : '' }}"
                href="#game">{{ __('HRM') }}</a>
                <ul id="game" class="dash-submenu">
                    <li class="dash-item ">
                        <a class="dash-link {{ \Request::route()->getName() == 'hrm.dashboard' ? ' active' : '' }}"
                            href="{{ route('hrm.dashboard') }}">{{ __('Overview') }}</a>
                    </li>
                    @can('manage report')
                    <li class="dash-item dash-hasmenu " href="#hr-report" data-toggle="collapse" role="button"
                        aria-expanded="{{ Request::segment(1) == 'reports-monthly-attendance' || Request::segment(1) == 'reports-leave' || Request::segment(1) == 'reports-payroll' ? 'true' : 'false' }}">
                        <a class="dash-link {{ Request::segment(1) == 'reports-monthly-attendance' || Request::segment(1) == 'reports-leave' || Request::segment(1) == 'reports-payroll' ? 'active dash-trigger' : '' }}"
                            href="#kant">{{ __('Reports') }}</a>
                        <ul id="kant" class="dash-submenu">
                            <li class="dash-item ">
                                <a class="dash-link {{ request()->is('reports-payroll') ? 'active' : '' }}"
                                    href="{{ route('report.payroll') }}">{{ __('Payroll') }}</a>
                            </li>
                            <li class="dash-item ">
                                <a class="dash-link {{ request()->is('reports-leave') ? 'active' : '' }}"
                                    href="{{ route('report.leave') }}">{{ __('Leave') }}</a>
                            </li>
                            <li class="dash-item ">
                                <a class="dash-link {{ request()->is('reports-monthly-attendance') ? 'active' : '' }}"
                                    href="{{ route('report.monthly.attendance') }}">{{ __('Monthly Attendance') }}</a>
                            </li>
                        </ul>
                    </li>
                    @endcan
                </ul>
                </li>
                @endcan
                @endif --}}

                                    {{--
                                    @if (\Auth::user()->show_crm() == 1)
                                        @can('show crm dashboard')
                                            <li class="dash-item dash-hasmenu ">
                                                <a class="dash-link {{ Request::segment(1) == 'crm-dashboard' || Request::segment(1) == 'reports-lead' || Request::segment(1) == 'reports-deal' ? ' active dash-trigger' : '' }}"
                href="#trm">{{ __('CRM') }}</a>
                <ul id="trm" class="dash-submenu">
                    <li class="dash-item ">
                        <a class="dash-link {{ \Request::route()->getName() == 'crm.dashboard' ? ' active' : '' }}"
                            href="{{ route('crm.dashboard') }}">{{ __(' Overview') }}</a>
                    </li>
                    <li class="dash-item dash-hasmenu ">
                        <a class="dash-link {{ Request::segment(1) == 'reports-lead' || Request::segment(1) == 'reports-deal' ? 'active dash-trigger' : '' }}"
                            href="#repo" data-toggle="collapse" role="button"
                            aria-expanded="{{ Request::segment(1) == 'reports-lead' || Request::segment(1) == 'reports-deal' ? 'true' : 'false' }}">
                            {{ __('Reports') }}
                        </a>
                        <ul id="repo" class="dash-submenu">
                            <li class="dash-item ">
                                <a class="dash-link {{ request()->is('reports-lead') ? 'active' : '' }}"
                                    href="{{ route('report.lead') }}">{{ __('Lead') }}</a>
                            </li>
                            <li class="dash-item ">
                                <a class="dash-link {{ request()->is('reports-deal') ? 'active' : '' }}"
                                    href="{{ route('report.deal') }}">{{ __('Deal') }}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                </li>
                @endcan
                @endif --}}

                                    {{-- @if (\Auth::user()->type != 'clientuser')
                                        <li class="dash-item ">
                                            <a class="dash-link {{ Request::route()->getName() == 'workspace.dashboard' ? ' active' : '' }}"
                href="{{ route('workspace.dashboard') }}">{{ __('Workspace') }}</a>
                </li>
                @endif

                @if (\Auth::user()->show_project() == 1)
                @can('show project dashboard')
                <li class="dash-item ">
                    <a class="dash-link {{ Request::route()->getName() == 'project.dashboard' ? ' active' : '' }}"
                        href="{{ route('project.dashboard') }}">{{ __('Project ') }}</a>
                </li>
                @endcan
                @endif

                @can('show clientuser dashboard')
                <li class="dash-item ">
                    <a class="dash-link {{ Request::route()->getName() == 'clientuser.dashboard' ? ' active' : '' }}"
                        href="{{ route('clientuser.dashboard') }}">{{ __('Dashboard ') }}</a>
                </li>
                @endcan --}}

                                    {{-- @if (\Auth::user()->show_pos() == 1)
                                    @can('show pos dashboard')
                                        <li class="dash-item dash-hasmenu {{ ( Request::segment(1) == 'pos-dashboard'  || Request::segment(1) == 'reports-warehouse' || Request::segment(1) == 'reports-daily-purchase' || Request::segment(1) == 'reports-monthly-purchase' || Request::segment(1) == 'reports-daily-pos' || Request::segment(1) == 'reports-monthly-pos' ||Request::segment(1) == 'reports-pos-vs-purchase') ? ' active dash-trigger' : ''}}">
                <a class="dash-link" href="#yos">{{__('POS')}}</a>
                <ul id="yos" class="dash-submenu">
                    <li class="dash-item {{ (\Request::route()->getName()=='pos.dashboard') ? ' active' : '' }}">
                        <a class="dash-link" href="{{route('pos.dashboard')}}">{{__(' Overview')}}</a>
                    </li>
                    <li class="dash-item dash-hasmenu {{ ( Request::segment(1) == 'reports-warehouse' || Request::segment(1) == 'reports-daily-purchase' || Request::segment(1) == 'reports-monthly-purchase' || Request::segment(1) == 'reports-daily-pos' || Request::segment(1) == 'reports-monthly-pos' ||Request::segment(1) == 'reports-pos-vs-purchase') ? 'active dash-trigger' : ''}}"
                        href="#crm-report" data-toggle="collapse" role="button"
                        aria-expanded="{{( Request::segment(1) == 'reports-warehouse' || Request::segment(1) == 'reports-daily-purchase' || Request::segment(1) == 'reports-monthly-purchase' || Request::segment(1) == 'reports-daily-pos' || Request::segment(1) == 'reports-monthly-pos' ||Request::segment(1) == 'reports-pos-vs-purchase') ? 'true' : 'false'}}">
                        <a class="dash-link" href="#report">{{__('Reports')}}</a>
                        <ul id="report" class="dash-submenu">
                            <li class="dash-item {{ request()->is('reports-warehouse') ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('report.warehouse') }}">{{__('Warehouse Report')}}</a>
                            </li>
                            <li
                                class="dash-item {{ request()->is('reports-daily-purchase') || request()->is('reports-monthly-purchase') ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('report.daily.purchase') }}">{{__('Purchase Daily/Monthly Report')}}</a>
                            </li>
                            <li
                                class="dash-item {{ request()->is('reports-daily-pos') || request()->is('reports-monthly-pos') ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('report.daily.pos') }}">{{__('POS Daily/Monthly Report')}}</a>
                            </li>
                            <li class="dash-item {{ request()->is('reports-pos-vs-purchase') ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('report.pos.vs.purchase') }}">{{__('Pos VS Purchase Report')}}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                </li>
                @endcan
                @endif --}}

                                </ul>
                            </li>
                        @endif

                        <!--------------------- End Dashboard ----------------------------------->


                        <!--------------------- Start HRM ----------------------------------->

                        @if (\Auth::user()->show_hrm() == 1)
                            @if (Gate::check('manage employee') || Gate::check('manage setsalary'))
                                <li class="dash-item dash-hasmenu">
                                    <a href="#rm"
                                        class="dash-link  {{ Request::segment(1) == 'holiday-calender' ||
                                        Request::segment(1) == 'appointment-letter' ||
                                        Request::segment(1) == 'employee_scale' ||
                                        Request::segment(1) == 'employee-scale-heads' ||
                                        Request::segment(1) == 'leavetype' ||
                                        Request::segment(1) == 'leave' ||
                                        Request::segment(1) == 'attendanceemployee' ||
                                        Request::segment(1) == 'document-upload' ||
                                        Request::segment(1) == 'document' ||
                                        Request::segment(1) == 'performanceType' ||
                                        Request::segment(1) == 'branch' ||
                                        Request::segment(1) == 'department' ||
                                        Request::segment(1) == 'designation' ||
                                        Request::segment(1) == 'employee' ||
                                        Request::segment(1) == 'leave_requests' ||
                                        Request::segment(1) == 'holidays' ||
                                        Request::segment(1) == 'policies' ||
                                        Request::segment(1) == 'leave_calender' ||
                                        Request::segment(1) == 'award' ||
                                        Request::segment(1) == 'transfer' ||
                                        Request::segment(1) == 'resignation' ||
                                        Request::segment(1) == 'training' ||
                                        Request::segment(1) == 'travel' ||
                                        Request::segment(1) == 'promotion' ||
                                        Request::segment(1) == 'complaint' ||
                                        Request::segment(1) == 'warning' ||
                                        Request::segment(1) == 'termination' ||
                                        Request::segment(1) == 'announcement' ||
                                        Request::segment(1) == 'job' ||
                                        Request::segment(1) == 'job-application' ||
                                        Request::segment(1) == 'candidates-job-applications' ||
                                        Request::segment(1) == 'job-onboard' ||
                                        Request::segment(1) == 'custom-question' ||
                                        Request::segment(1) == 'interview-schedule' ||
                                        Request::segment(1) == 'career' ||
                                        Request::segment(1) == 'holiday' ||
                                        Request::segment(1) == 'setsalary' ||
                                        Request::segment(1) == 'payslip' ||
                                        Request::segment(1) == 'paysliptype' ||
                                        Request::segment(1) == 'company-policy' ||
                                        Request::segment(1) == 'job-stage' ||
                                        Request::segment(1) == 'job-category' ||
                                        Request::segment(1) == 'terminationtype' ||
                                        Request::segment(1) == 'awardtype' ||
                                        Request::segment(1) == 'trainingtype' ||
                                        Request::segment(1) == 'goaltype' ||
                                        Request::segment(1) == 'paysliptype' ||
                                        Request::segment(1) == 'allowanceoption' ||
                                        Request::segment(1) == 'competencies' ||
                                        Request::segment(1) == 'loanoption' ||
                                        Request::segment(1) == 'trainer' ||
                                        Request::segment(1) == 'event' ||
                                        Request::segment(1) == 'meeting' ||
                                        Request::segment(1) == 'employee-salary-detail' ||
                                        Request::segment(1) == 'emp-month-sal-attendance' ||
                                        Request::segment(1) == 'final-attendance' ||
                                        Request::segment(1) == 'month_salary' ||
                                        Request::segment(1) == 'salary-history' ||
                                        Request::segment(1) == 'deductionoption' ||
                                        Request::segment(1) == 'emp-final-settlement' ||
                                        Request::segment(1) == 'indicator' ||
                                        Request::segment(1) == 'appraisal' ||
                                        Request::segment(1) == 'goaltracking' ||
                                        Request::segment(1) == 'salary-history-report' ||
                                        Request::segment(1) == 'loan' ||
                                        Request::segment(1) == 'employee-advance' ||
                                        Request::segment(1) == 'advance-tax-collection' ||
                                        Request::segment(1) == 'resignation' ||
                                        Request::segment(1) == 'termination' ||
                                        Request::segment(1) == 'employee-transfer' ||
                                        Request::segment(1) == 'employee-salary-proporal' ||
                                        Request::segment(1) == 'health-insurance-plan' ||
                                        Request::segment(1) == 'emp-leaves' ||
                                        Request::segment(1) == 'sops' ||
                                        Request::segment(1) == 'emp-eobi-allocation' ||
                                        Request::segment(1) == 'emp-concession-order'
                                            ? 'active dash-trigger'
                                            : '' }}">
                                        <span class="dash-micon">
                                            <svg width="17" height="19" viewBox="0 0 17 19" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M15.1282 17.316V15.9488C15.1282 13.6828 13.0288 11.8464 10.4393 11.8464H6.68891C4.09941 11.8464 2 13.6828 2 15.9488V17.316M11.8461 5.28213C11.8461 6.15225 11.5004 6.98776 10.8849 7.603C10.2694 8.21824 9.43454 8.56424 8.56408 8.56424C7.69363 8.56424 6.85879 8.21824 6.2433 7.603C5.62781 6.98776 5.28204 6.15225 5.28204 5.28213C5.28204 4.41202 5.62781 3.57651 6.2433 2.96127C6.85879 2.34603 7.69363 2 8.56408 2C9.43454 2 10.2694 2.34603 10.8849 2.96127C11.5004 3.57651 11.8461 4.41202 11.8461 5.28213Z"
                                                    stroke="#474B4E" stroke-width="2.18804" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>

                                        </span>
                                        <span class="dash-mtext">
                                            {{ __('HRM System') }}
                                        </span>

                                    </a>
                                    <ul id="rm" class="dash-submenu">
                                        <li class="dash-item  ">
                                            @if (\Auth::user()->type == 'company')
                                                <a href="{{ route('appointment-letter') }}"
                                                    class="dash-link {{ Request::segment(1) == 'appointment-letter' ? 'active dash-trigger' : '' }}   ">
                                                    {{ __('Appointment Letter') }}
                                                </a>
                                            @endif
                                        </li>
                                        <li class="dash-item  ">
                                            @if (\Auth::user()->type == 'Employee')
                                                @php
                                                    $employee = App\Models\Employee::where(
                                                        'user_id',
                                                        \Auth::user()->id,
                                                    )->first();
                                                @endphp
                                                <a class="dash-link {{ Request::segment(1) == 'employee' ? 'active dash-trigger' : '' }}   "
                                                    href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ __('Employee') }}</a>
                                            @else
                                                <a href="{{ route('employee.index') }}"
                                                    class="dash-link {{ Request::segment(1) == 'employee' ? 'active dash-trigger' : '' }} ">
                                                    {{ __('Employee List') }}
                                                </a>
                                            @endif
                                        </li>
										<li class="dash-item">
                                            <a href="{{ route('employee.bulk.update') }}"
                                                class="dash-link {{ Request::segment(1) == 'employee-bulk-update' ? 'active' : '' }} ">
                                                {{ __('Employee Bulk Update') }}
                                            </a>
                                        </li>
                                        <li class="dash-item">
                                            @if (\Auth::user()->type == 'company')
                                                <a href="{{ route('employee-scale-heads.index') }}"
                                                    class="dash-link {{ Request::segment(1) == 'employee-scale-heads' ? 'active dash-trigger' : '' }} ">
                                                    {{ __('Employee Scale Heads') }}
                                                </a>
                                            @endif
                                        </li>

                                        <li class="dash-item">
                                            @can('manage employee scale')
                                                <a href="{{ route('employee_scale.index') }}"
                                                    class="dash-link {{ Request::segment(1) == 'employee_scale' ? 'active dash-trigger' : '' }}">
                                                    {{ __('Employee Scale') }}
                                                </a>
                                            @endcan
                                        </li>

                                        <li class="dash-item    ">
                                                <a href="{{ route('employee-salary-detail.index') }}"
                                                    class="dash-link {{ Request::segment(1) == 'employee-salary-detail' ? 'active dash-trigger' : '' }} ">
                                                    {{ __('Emp. Salary Detail') }}
                                                </a>
                                        </li>
                                        <li class="dash-item     ">
                                            {{-- @if (\Auth::user()->type == 'company') --}}
                                            <a href="{{ route('emp-month-sal-attendance.index') }}"
                                                class="dash-link {{ Request::segment(1) == 'emp-month-sal-attendance' ? 'active dash-trigger' : '' }}">
                                                {{ __('Monthly Salary Attend.') }}
                                            </a>
                                            {{-- @endif --}}
                                        </li>
                                        <li class="dash-item  ">
                                            {{-- @if (\Auth::user()->type == 'company') --}}
                                            <a href="{{ route('final_attendance') }}"
                                                class="dash-link  {{ Request::segment(1) == 'final-attendance' ? 'active dash-trigger' : '' }}  ">
                                                {{ __('Final Attendance') }}
                                            </a>
                                            {{-- @endif --}}
                                        </li>
                                        <li class="dash-item  ">
                                           
                                                <a href="{{ route('month_salary') }}"
                                                    class="dash-link {{ Request::segment(1) == 'month_salary' ? 'active dash-trigger' : '' }}   ">
                                                    {{ __('Month Salary') }}
                                                </a>
                                           
                                           
                                        </li>
                                        <li class="dash-item">
                                            @if (\Auth::user()->type == 'company')
                                                <a href="{{ route('salary_history') }}"
                                                    class="dash-link  {{ Request::segment(1) == 'salary-history' ? 'active dash-trigger' : '' }}">
                                                    {{ __('Salary History') }}
                                                </a>
                                            @endif
                                        </li>

                                        <li class="dash-item   ">
                                            @if (\Auth::user()->type == 'company')
                                                <a style="display: flex;" href="{{ route('salary_history_report') }}"
                                                    class="dash-link  {{ Request::segment(1) == 'salary-history-report' ? 'active dash-trigger' : '' }} ">
                                                    <div style="margin-top: -5px;"> {{ __('Payroll History') }} </div>
                                                </a>
                                            @endif
                                        </li>
                                        <li class="dash-item   ">
                                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'branch')
                                                <a href="{{ route('employee-salary-proporal.index') }}"
                                                    class="dash-link  {{ Request::segment(1) == 'employee-salary-proporal' ? 'active dash-trigger' : '' }} ">
                                                    {{ __('Employee Sal. Proposal') }}
                                                </a>
                                            @endif
                                        </li>
                                         @if (\Auth::user()->type == 'company')
                                            <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'tax-slabs' || Request::segment(1) == 'last-salary-revision-report' ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#salary_revision_sub">{{ __('Salary Revision') }}</a>
                                                <ul id="salary_revision_sub" class="dash-submenu">
                                                    <li class="dash-item">
                                                        <a href="{{ route('tax-slab.showRevisePage') }}"
                                                           class="dash-link {{ Request::route()->getName() == 'tax-slab.showRevisePage' ? 'active' : '' }}">
                                                            {{ __('Tax Revise') }}
                                                        </a>
                                                    </li>
                                                    <li class="dash-item">
                                                        <a href="{{ route('last_salary_revision_report') }}"
                                                           class="dash-link {{ Request::route()->getName() == 'last_salary_revision_report' ? 'active' : '' }}">
                                                            {{ __('Last Salary Revision') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </li>
                                        @endif
                                        {{-- @if (Gate::check('manage set salary') || Gate::check('manage pay slip'))

                    <li class="dash-item dash-hasmenu ">
                        <a class="dash-link  {{ Request::segment(1) == 'setsalary' ||Request::segment(1) == 'loan' || Request::segment(1) == 'payslip' ? 'active dash-trigger' : '' }}"
                            href="#payroll">{{ __('Payroll Setup') }}</a>
                        <ul id="payroll" class="dash-submenu">
                            @can('manage set salary')

                            @endcan
                            @can('manage set salary')
                            <li class="dash-item">
                                <a class="dash-link  {{ request()->is('setsalary*') ? 'active' : '' }}"
                                    href="{{ route('setsalary.index') }}">{{ __('Set salary') }}</a>
                            </li>
                            @endcan
                            @can('manage pay slip')
                            <li class="dash-item ">
                                <a class="dash-link {{ request()->is('payslip*') ? 'active' : '' }}"
                                    href="{{ route('payslip.index') }}">{{ __('Payslip') }}</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif --}}

                                        @if (Gate::check('manage leave') || Gate::check('manage attendance'))
                                            <li class="dash-item ">
                                                <a class="dash-link {{ Request::route()->getName() == 'leave.index' ? 'active' : '' }}"
                                                    href="{{ route('leave.index') }}">{{ __('Manage Leave') }}</a>
                                            </li>
                                            {{-- <li class="dash-item dash-hasmenu ">
                        <a class="dash-link {{ Request::segment(1) == 'leave' || Request::segment(1) == 'attendanceemployee' ? 'active dash-trigger' : '' }}"
                            href="#leave">{{ __('Leave Management Setup') }}<span class="dash-arrow"></span></a>
                        <ul id="leave" class="dash-submenu">
                            @can('manage leave')
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::route()->getName() == 'leave.index' ? 'active' : '' }}"
                                    href="{{ route('leave.index') }}">{{ __('Manage Leave') }}</a>
                            </li>
                            @endcan --}}
                                            {{-- @can('manage attendance')
                            <li class="dash-item dash-hasmenu " href="#navbar-attendance" data-toggle="collapse"
                                role="button"
                                aria-expanded="{{ Request::segment(1) == 'attendanceemployee' ? 'true' : 'false' }}">
                                <a class="dash-link {{ Request::segment(1) == 'attendanceemployee' ? 'active dash-trigger' : '' }}"
                                    href="#tender">{{ __('Attendance') }}</a>
                                <ul id="tender" class="dash-submenu">
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::route()->getName() == 'attendanceemployee.index' ? 'active' : '' }}"
                                            href="{{ route('attendanceemployee.index') }}">{{ __('Mark Attendance') }}</a>
                                    </li>
                                    @can('create attendance')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::route()->getName() == 'attendanceemployee.bulkattendance' ? 'active' : '' }}"
                                            href="{{ route('attendanceemployee.bulkattendance') }}">{{ __('Bulk Attendance') }}</a>
                                    </li>
                                    @endcan
                                </ul>
                            </li>
                            @endcan --}}
                                            {{-- </ul>
                    </li> --}}
                                        @endif

                                        {{-- @if (Gate::check('manage indicator') ||
    Gate::check('manage appraisal') ||
    Gate::check('manage goal
                    tracking'))
                    <li class="dash-item dash-hasmenu " href="#navbar-performance" data-toggle="collapse" role="button"
                        aria-expanded="{{ Request::segment(1) == 'indicator' || Request::segment(1) == 'appraisal' || Request::segment(1) == 'goaltracking' ? 'true' : 'false' }}">
                        <a class="dash-link {{ Request::segment(1) == 'indicator' || Request::segment(1) == 'appraisal' || Request::segment(1) == 'goaltracking' ? 'active dash-trigger' : '' }}"
                            href="#performance">{{ __('Performance Setup') }}</a>
                        <ul id="performance"
                            class="dash-submenu {{ Request::segment(1) == 'indicator' || Request::segment(1) == 'appraisal' || Request::segment(1) == 'goaltracking' ? 'show' : 'collapse' }}">
                            @can('manage indicator')
                            <li class="dash-item ">
                                <a class="dash-link {{ request()->is('indicator*') ? 'active' : '' }}"
                                    href="{{ route('indicator.index') }}">{{ __('Indicator') }}</a>
                            </li>
                            @endcan
                            
                            @can('manage goal tracking')
                            <li class="dash-item ">
                                <a class="dash-link {{ request()->is('goaltracking*') ? 'active' : '' }}"
                                    href="{{ route('goaltracking.index') }}">{{ __('Goal Tracking') }}</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif --}}

                                        {{-- @if (Gate::check('manage training') ||
    Gate::check('manage trainer') ||
    Gate::check('show
                    training'))
                    <li class="dash-item dash-hasmenu " href="#navbar-training" data-toggle="collapse" role="button"
                        aria-expanded="{{ Request::segment(1) == 'trainer' || Request::segment(1) == 'training' ? 'true' : 'false' }}">
                        <a class="dash-link {{ Request::segment(1) == 'trainer' || Request::segment(1) == 'training' ? 'active dash-trigger' : '' }}"
                            href="#training">{{ __('Training Setup') }}</a>
                        <ul id="training" class="dash-submenu">
                            @can('manage training')
                            <li class="dash-item ">
                                <a class="dash-link {{ request()->is('training*') ? 'active' : '' }}"
                                    href="{{ route('training.index') }}">{{ __('Training List') }}</a>
                            </li>
                            @endcan
                            @can('manage trainer')
                            <li class="dash-item">
                                <a class="dash-link {{ request()->is('trainer*') ? 'active' : '' }}"
                                    href="{{ route('trainer.index') }}">{{ __('Trainer') }}</a>
                            </li>
                            @endcan

                        </ul>
                    </li>
                    @endif --}}

                                        {{-- @if (Gate::check('manage job') || Gate::check('create job') || Gate::check('manage job application') || Gate::check('manage custom question') || Gate::check('show interview schedule') || Gate::check('show career'))
                                        <li
                                            class="dash-item dash-hasmenu ">
                                            <a class="dash-link {{ Request::segment(1) == 'job' || Request::segment(1) == 'job-application' || Request::segment(1) == 'candidates-job-applications' || Request::segment(1) == 'job-onboard' || Request::segment(1) == 'custom-question' || Request::segment(1) == 'interview-schedule' || Request::segment(1) == 'career' ? 'active dash-trigger' : '' }}"
                    href="#setup">{{ __('Recruitment Setup') }}</a>
                    <ul id="setup" class="dash-submenu">
                        @can('manage job')
                        <li class="dash-item ">
                            <a class="dash-link {{ Request::route()->getName() == 'job.index' || Request::route()->getName() == 'job.create' || Request::route()->getName() == 'job.edit' || Request::route()->getName() == 'job.show' ? 'active' : '' }}"
                                href="{{ route('job.index') }}">{{ __('Jobs') }}</a>
                        </li>
                        @endcan
                        @can('create job')
                        <li class="dash-item ">
                            <a class="dash-link {{ Request::route()->getName() == 'job.create' ? 'active' : '' }} "
                                href="{{ route('job.create') }}">{{ __('Job Create') }}</a>
                        </li>
                        @endcan
                        @can('manage job application')
                        <li class="dash-item">
                            <a class="dash-link  {{ request()->is('job-application*') ? 'active' : '' }}"
                                href="{{ route('job-application.index') }}">{{ __('Job Application') }}</a>
                        </li>
                        @endcan
                        @can('manage job application')
                        <li class="dash-item">
                            <a class="dash-link {{ request()->is('candidates-job-applications') ? 'active' : '' }}"
                                href="{{ route('job.application.candidate') }}">{{ __('Job Candidate') }}</a>
                        </li>
                        @endcan
                        @can('manage job application')
                        <li class="dash-item">
                            <a class="dash-link  {{ request()->is('job-onboard*') ? 'active' : '' }}"
                                href="{{ route('job.on.board') }}">{{ __('Job On-boarding') }}</a>
                        </li>
                        @endcan
                        @can('manage custom question')
                        <li class="dash-item ">
                            <a class="dash-link {{ request()->is('custom-question*') ? 'active' : '' }}"
                                href="{{ route('custom-question.index') }}">{{ __('Custom Question') }}</a>
                        </li>
                        @endcan
                        @can('show interview schedule')
                        <li class="dash-item">
                            <a class="dash-link {{ request()->is('interview-schedule*') ? 'active' : '' }}"
                                href="{{ route('interview-schedule.index') }}">{{ __('Interview Schedule') }}</a>
                        </li>
                        @endcan
                        @can('show career')
                        <li class="dash-item ">
                            <a class="dash-link {{ request()->is('career*') ? 'active' : '' }}"
                                href="{{ route('career', [\Auth::user()->creatorId(), $lang]) }}">{{ __('Career') }}</a>
                        </li>
                        @endcan
                    </ul>
                    </li>
                    @endif
                                                --}}

                                        <li class="dash-item ">
                                            @if (\Auth::user()->type == 'company')
                                                <a href="{{ route('emp-final-settlement.index') }}"
                                                    class="dash-link {{ Request::segment(1) == 'emp-final-settlement' ? 'active' : '' }}">
                                                    {{ __('Employee Final Settl.') }}
                                                </a>
                                            @endif
                                        </li>
                                        @if (Gate::check('manage award') ||
                                                Gate::check('manage transfer') ||
                                                Gate::check('manage resignation') ||
                                                Gate::check('manage travel') ||
                                                Gate::check('manage promotion') ||
                                                Gate::check('manage complaint') ||
                                                Gate::check('manage warning') ||
                                                Gate::check('manage termination') ||
                                                Gate::check('manage announcement') ||
                                                Gate::check('manage holiday'))
                                            <li id="hr-admin" class="dash-item dash-hasmenu ">
                                                <a  class="dash-link {{ Request::segment(1) == 'loan' ||
                                                Request::segment(1) == 'holiday-calender' ||
                                                Request::segment(1) == 'employee-advance' ||
                                                Request::segment(1) == 'advance-tax-collection' ||
                                                Request::segment(1) == 'employee-transfer' ||
                                                Request::segment(1) == 'holiday' ||
                                                Request::segment(1) == 'policies' ||
                                                Request::segment(1) == 'award' ||
                                                Request::segment(1) == 'transfer' ||
                                                Request::segment(1) == 'resignation' ||
                                                Request::segment(1) == 'travel' ||
                                                Request::segment(1) == 'promotion' ||
                                                Request::segment(1) == 'complaint' ||
                                                Request::segment(1) == 'warning' ||
                                                Request::segment(1) == 'termination' ||
                                                Request::segment(1) == 'appraisal' ||
                                                Request::segment(1) == 'announcement' ||
                                                Request::segment(1) == 'emp-concession-order' ||
                                                Request::segment(1) == 'emp-leaves' ||
                                                Request::segment(1) == 'emp-eobi-allocation' ||
                                                Request::segment(1) == 'health-insurance-plan' ||
                                                Request::segment(1) == 'competencies' ||
                                                Request::segment(1) == 'sops'
                                                    ? 'active dash-trigger'
                                                    : '' }}"
                                                    href="#hr">{{ __('HR Admin Setup') }}</a>
                                                <ul id="hr" class="dash-submenu">
                                                    <li class="dash-item">
                                                        <a class="dash-link  {{ Request::segment(1) == 'loan' ? 'active dash-trigger' : '' }}"
                                                            {{-- {{ request()->is('loan*') ? 'active' : '' }} --}}
                                                            href="{{ route('loan.index') }}">{{ __('Set Loan') }}</a>
                                                    </li>
                                                    <li class="dash-item">
                                                        <a class="dash-link  {{ Request::segment(1) == 'employee-advance' ? 'active dash-trigger' : '' }}"
                                                            href="{{ route('employee-advance.index') }}">{{ __('Set Advance') }}</a>
                                                    </li>
 													 <li class="dash-item">
                                                        <a class="dash-link  {{ Request::segment(1) == 'advance-tax-collection' ? 'active dash-trigger' : '' }}"
                                                            href="{{ route('advance-tax-collection.index') }}">{{ __('Advance Tax Collection') }}</a>
                                                    </li>
                                                    @can('manage appraisal')
                                                        <li class="dash-item">
                                                            <a class="dash-link  {{ request()->is('appraisal*') ? 'active' : '' }}"
                                                                href="{{ route('appraisal.index') }}">{{ __('Appraisal') }}</a>
                                                        </li>
                                                    @endcan
                                                    @can('manage appraisal')
                                                        <li class="dash-item">
                                                            <a class="dash-link  {{ Request::segment(1) == 'health-insurance-plan' ? 'active dash-trigger' : '' }}"
                                                                href="{{ route('health-insurance-plan.index') }}">{{ __('Insurance Plan Setup') }}</a>
                                                        </li>
                                                    @endcan
                                                    @can('manage resignation')
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::segment(1) == 'resignation' ? 'active dash-trigger' : '' }}"
                                                                href="{{ route('resignation.index') }}">{{ __('Resignation') }}</a>
                                                        </li>
                                                    @endcan
                                                    @can('manage resignation')
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::segment(1) == 'emp-leaves' ? 'active dash-trigger' : '' }}"
                                                                href="{{ route('emp-leaves.index') }}">{{ __('Leave Allocation') }}</a>
                                                        </li>
                                                    @endcan
                                                    @can('manage resignation')
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::segment(1) == 'emp-eobi-allocation' ? 'active dash-trigger' : '' }}"
                                                                href="{{ route('emp-eobi-allocation.index') }}">{{ __('Emp. EOBI Allocation') }}</a>
                                                        </li>
                                                    @endcan
                                                    @can('manage termination')
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::segment(1) == 'termination' ? 'active dash-trigger' : '' }}"
                                                                href="{{ route('termination.index') }}">{{ __('Termination') }}</a>
                                                        </li>
                                                    @endcan
                                                    <li class="dash-item ">
                                                        <a class="dash-link {{ Request::segment(1) == 'employee-transfer' ? 'active dash-trigger' : '' }}"
                                                            href="{{ route('employee-transfer.index') }}">{{ __('Emp. Transfer') }}</a>
                                                    </li>
                                                    <li class="dash-item ">
                                                        <a class="dash-link  {{ Request::segment(1) == 'emp-concession-order' ? 'active dash-trigger' : '' }}"
                                                            href="{{ route('emp-concession-order.index') }}">{{ __('Concession Order') }}</a>
                                                    </li>
                                                    {{-- @can('manage award')
                                                    <li class="dash-item">
                                                        <a class="dash-link  {{ request()->is('award*') ? 'active' : '' }}"
                                                            href="{{ route('award.index') }}">{{ __('Award') }}</a>
                                                    </li>
                                                    @endcan
                                                    @can('manage transfer')
                                                    <li class="dash-item ">
                                                        <a class="dash-link  {{ request()->is('transfer*') ? 'active' : '' }}"
                                                            href="{{ route('transfer.index') }}">{{ __('Transfer') }}</a>
                                                    </li>
                                                    @endcan
                                                    @can('manage travel')
                                                    <li class="dash-item">
                                                        <a class="dash-link  {{ request()->is('travel*') ? 'active' : '' }}"
                                                            href="{{ route('travel.index') }}">{{ __('Trip') }}</a>
                                                    </li>
                                                    @endcan
                                                    @can('manage promotion')
                                                    <li class="dash-item">
                                                        <a class="dash-link  {{ request()->is('promotion*') ? 'active' : '' }}"
                                                            href="{{ route('promotion.index') }}">{{ __('Promotion') }}</a>
                                                    </li>
                                                    @endcan
                                                    @can('manage complaint')
                                                    <li class="dash-item ">
                                                        <a class="dash-link {{ request()->is('complaint*') ? 'active' : '' }}"
                                                            href="{{ route('complaint.index') }}">{{ __('Complaints') }}</a>
                                                    </li>
                                                    @endcan
                                                    @can('manage warning')
                                                    <li class="dash-item ">
                                                        <a class="dash-link {{ request()->is('warning*') ? 'active' : '' }}"
                                                            href="{{ route('warning.index') }}">{{ __('Warning') }}</a>
                                                    </li>
                                                    @endcan
                                                    @can('manage announcement')
                                                    <li class="dash-item ">
                                                        <a class="dash-link {{ request()->is('announcement*') ? 'active' : '' }}"
                                                            href="{{ route('announcement.index') }}">{{ __('Announcement') }}</a>
                                                    </li>
                                                    @endcan
                                                    @can('manage holiday')
                                                    <li class="dash-item ">
                                                        <a class="dash-link {{ request()->is('holiday*') || request()->is('holiday-calender') ? 'active' : '' }}"
                                                            href="{{ route('holiday.index') }}">{{ __('Holidays') }}</a>
                                                    </li>
                                                    @endcan --}}
                                                </ul>

                                                <!--------------------- HRM Employee Reports ----------------------------------->
                                        @endif
                                        <!--------------------- HRM Employee Reports ----------------------------------->
                                        {{--
                    @can('manage event')
                    <li class="dash-item ">
                        <a class="dash-link {{ request()->is('event*') ? 'active' : '' }}"
                            href="{{ route('event.index') }}">{{ __('Event Setup') }}</a>
                    </li>
                    @endcan
                    @can('manage meeting')
                    <li class="dash-item ">
                        <a class="dash-link {{ request()->is('meeting*') ? 'active' : '' }}"
                            href="{{ route('meeting.index') }}">{{ __('Meeting') }}</a>
                    </li>
                    @endcan
                     @can('manage assets')
                                        <li class="dash-item {{ request()->is('account-assets*') ? 'active' : '' }}">
                    <a class="dash-link"
                        href="{{ route('account-assets.index') }}">{{ __('Employees Asset Setup ') }}</a>
                    </li>
                    @endcan
                         @can('manage document')
                                            <li class="dash-item">
                                                <a class="dash-link  {{ request()->is('document-upload*') ? 'active' : '' }}"
                    href="{{ route('document-upload.index') }}">{{ __('Document Setup') }}</a>
                    </li>
                    @endcan
                    @can('manage company policy')
                    <li class="dash-item ">
                        <a class="dash-link {{ request()->is('company-policy*') ? 'active' : '' }}"
                            href="{{ route('company-policy.index') }}">{{ __('Company policy') }}</a>
                    </li>
                    @endcan
    --}}
                                        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'branch' || \Auth::user()->type == 'HR')
                                            <li class="dash-item ">
                                                <a class="dash-link {{ Request::segment(1) == 'leavetype' ||
                                                Request::segment(1) == 'document' ||
                                                Request::segment(1) == 'performanceType' ||
                                                Request::segment(1) == 'branch' ||
                                                Request::segment(1) == 'department' ||
                                                Request::segment(1) == 'designation' ||
                                                Request::segment(1) == 'job-stage' ||
                                                Request::segment(1) == 'performanceType' ||
                                                Request::segment(1) == 'job-category' ||
                                                Request::segment(1) == 'terminationtype' ||
                                                Request::segment(1) == 'awardtype' ||
                                                Request::segment(1) == 'trainingtype' ||
                                                Request::segment(1) == 'goaltype' ||
                                                Request::segment(1) == 'paysliptype' ||
                                                Request::segment(1) == 'allowanceoption' ||
                                                Request::segment(1) == 'loanoption' ||
                                                Request::segment(1) == 'deductionoption'
                                                    ? 'active dash-trigger'
                                                    : '' }}"
                                                    href="{{ route('department.index') }}">{{ __('HRM System Setup') }}</a>
                                            </li>
                                        @endcan



                                </ul>
                            </li>
                        @endif
                    @endif
                    <!--------------------- End HRM ----------------------------------->

                    <!--------------------- HRM Employee Reports ----------------------------------->

                    @if (Gate::check('view space') ||
                            Gate::check('view spacetype') ||
                            Gate::check('manage ismail') ||
                            Gate::check('manage vistor'))
                        <li class="dash-item dash-hasmenu">

                            <a href="#employee_reports"
                                class="dash-link {{ Request::segment(1) == 'emp-monthly-sec' ||
                                Request::segment(1) == 'emp-transfer-report' ||
                                Request::segment(1) == 'staff-child-report' ||
                                Request::segment(1) == 'emp-eobi-report' ||
                                Request::segment(1) == 'emp-pessi-report' ||
                                Request::segment(1) == 'periodwise-report' ||
                                Request::segment(1) == 'emp-employment-history' ||
                                Request::segment(1) == 'emp-monthly-reconsilation' ||
                                Request::segment(1) == 'emp-tax-ded-rpt' ||
                                Request::segment(1) == 'employee-qualification-report' ||
                                Request::segment(1) == 'employee-birthday' ||
                                Request::segment(1) == 'salary-comparison-report' ||
                                Request::segment(1) == 'employee-leave-report' ||
                                Request::segment(1) == 'employee-appraisal-report' ||
                                Request::segment(1) == 'employee-termination-report' ||
                                Request::segment(1) == 'employee-resignation-report' ||
                                Request::segment(1) == 'employee-promotion-report' ||
                                Request::segment(1) == 'employee-travel-report' ||
                                Request::segment(1) == 'employee-complaint-report' ||
                                Request::segment(1) == 'employee-warning-report' ||
                                Request::segment(1) == 'employee-award-report' ||
                                Request::segment(1) == 'employee-training-report' ||
                                Request::segment(1) == 'employee-job-report' ||
                                Request::segment(1) == 'employee-holiday-report' ||
                                Request::segment(1) == 'emp-retirement-report' ||
                                Request::segment(1) == 'emp-annual-srs' ||
                                Request::segment(1) == 'staff-turnover' ||
                                Request::segment(1) == 'emp-loan' ||
                                Request::segment(1) == 'single-emp-sec-report' ||
                                Request::segment(1) == 'emp-insurance-report' ||
                                Request::segment(1) == 'employee-withoutpayleave-report' ||
                                Request::segment(1) == 'employee-apprforms' ||
                                Request::segment(1) == 'emp-sec-report' ||
                                Request::segment(1) == 'empsecreport' ||
                                Request::segment(1) == 'reports-monthly-attendance' ||  Request::segment(1) == 'employee-profile-report'
                                    ? 'active dash-trigger'
                                    : '' }}"><span
                                    class="dash-micon">
                                </span>
                                <span class="dash-micon">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8.49979 19.1667C8.49979 19.625 8.1173 20 7.64981 20H4.24989C1.90395 20 0 18.1333 0 15.8333V4.16667C0 1.86667 1.90395 0 4.24989 0H11.0497C13.3957 0 15.2996 1.86667 15.2996 4.16667V5.83333C15.2996 6.29167 14.9171 6.66667 14.4496 6.66667C13.9822 6.66667 13.5997 6.29167 13.5997 5.83333V4.16667C13.5997 2.79167 12.4522 1.66667 11.0497 1.66667H4.24989C2.84743 1.66667 1.69996 2.79167 1.69996 4.16667V15.8333C1.69996 17.2083 2.84743 18.3333 4.24989 18.3333H7.64981C8.1173 18.3333 8.49979 18.7083 8.49979 19.1667ZM11.8997 5C11.8997 4.54167 11.5172 4.16667 11.0497 4.16667H4.24989C3.78241 4.16667 3.39991 4.54167 3.39991 5C3.39991 5.45833 3.78241 5.83333 4.24989 5.83333H11.0497C11.5172 5.83333 11.8997 5.45833 11.8997 5ZM4.24989 12.5C3.78241 12.5 3.39991 12.875 3.39991 13.3333C3.39991 13.7917 3.78241 14.1667 4.24989 14.1667H5.94985C6.41734 14.1667 6.79983 13.7917 6.79983 13.3333C6.79983 12.875 6.41734 12.5 5.94985 12.5H4.24989ZM8.49979 9.16667C8.49979 8.70833 8.1173 8.33333 7.64981 8.33333H4.24989C3.78241 8.33333 3.39991 8.70833 3.39991 9.16667C3.39991 9.625 3.78241 10 4.24989 10H7.64981C8.1173 10 8.49979 9.625 8.49979 9.16667ZM19.49 8.73333L15.7161 7.29167C14.9681 7.00833 14.1521 7.00833 13.4042 7.29167L9.63876 8.73333C9.32427 8.85 9.12027 9.15 9.12027 9.475C9.12027 9.8 9.32427 10.1 9.63876 10.2167L11.2962 10.85V11.8583C11.2962 13.625 12.7582 15.0583 14.5601 15.0583C16.3621 15.0583 17.8241 13.625 17.8241 11.8583V10.85L18.368 10.6417V12.6667C18.368 13.1083 18.7335 13.4667 19.184 13.4667C19.6345 13.4667 20 13.1083 20 12.6667V9.46667C20 8.93333 19.558 8.75 19.49 8.725V8.73333ZM13.7357 9.03333C14.0756 8.9 14.4581 8.9 14.7981 9.03333L16.5151 9.69167L14.7981 10.35C14.4581 10.4833 14.0756 10.4833 13.7357 10.35L12.0187 9.69167L13.7357 9.03333ZM15.8096 11.95C15.8096 12.7833 15.1211 13.4667 14.2626 13.4667C13.4042 13.4667 12.7157 12.7917 12.7157 11.95V11.5833L13.1662 11.7583C13.5232 11.8917 13.8887 11.9583 14.2626 11.9583C14.6366 11.9583 15.0021 11.8917 15.3591 11.7583L15.8096 11.5833V11.95ZM18.9035 18.7167C19.116 19.1083 18.9545 19.5917 18.555 19.8C18.436 19.8583 18.3085 19.8917 18.181 19.8917C17.8836 19.8917 17.6031 19.7333 17.4586 19.4583C16.8891 18.3917 15.7841 17.7333 14.5686 17.7333C13.3532 17.7333 12.2397 18.3917 11.6787 19.4583C11.4662 19.85 10.9817 20 10.5737 19.8C10.1742 19.5917 10.0212 19.1167 10.2252 18.7167C11.0752 17.125 12.7327 16.1333 14.5686 16.1333C16.4046 16.1333 18.062 17.125 18.912 18.7167H18.9035Z"
                                            fill="#474B4E" />
                                    </svg>

                                </span>
                                <span class="dash-mtext">{{ __('Employee Reports') }}</span>
                            </a>

                            <ul id="employee_reports" class="dash-submenu">
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'employee-apprforms' ? 'active' : '' }}"
                                            href="{{ route('AppraisalFrom') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Employee Appraisal Froms') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
  								@can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'employee-profile-report' ? 'active' : '' }}"
                                            href="{{ route('employee_profile_report') }}">{{ __('Employee Profile Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'employee-birthday' ? 'active' : '' }}"
                                            href="{{ route('empBirthdayRpt') }}">{{ __('Employee Birthday Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'emp-retirement-report' ? 'active' : '' }}"
                                            href="{{ route('empRetirementReport') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Employee Retirement Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'employee-qualification-report' ? 'active' : '' }}"
                                            href="{{ route('empEduRpt') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Employee Qualification Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'employee-leave-report' ? 'active' : '' }}"
                                            href="{{ route('empleaveReport') }}">{{ __('Employee Leave Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'emp-annual-srs' ? 'active' : '' }}"
                                            href="{{ route('empAnnualSrs') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Employee Annual SRS') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'staff-turnover' ? 'active' : '' }}"
                                            href="{{ route('staff.turnover') }}">{{ __('Staff TurnOver Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'emp-loan' ? 'active' : '' }}"
                                            href="{{ route('empLoan') }}">{{ __('Employee Loan Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'single-emp-sec-report' ? 'active' : '' }}"
                                            href="{{ route('singleEmpSecReport') }}">{{ __('Employee Security Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'emp-insurance-report' ? 'active' : '' }}"
                                            href="{{ route('empInsuranceReport') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Employee Insurance Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'employee-withoutpayleave-report' ? 'active' : '' }}"
                                            href="{{ route('empwithoutPayleave') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Employee W-O-P Leave Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'staff-child-report' ? 'active' : '' }}"
                                            href="{{ route('staffChildReport') }}">{{ __('Staff Child Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'emp-monthly-sec' ? 'active' : '' }}"
                                            href="{{ route('monthlysecurtiyded') }}">{{ __('Emp. Monthly Sec. Ded.') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'emp-eobi-report' ? 'active' : '' }}"
                                            href="{{ route('employeeEobiReport') }}">{{ __('EOBI Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'emp-pessi-report' ? 'active' : '' }}"
                                            href="{{ route('employeepessireport') }}">{{ __('Pessi Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'emp-tax-ded-rpt' ? 'active' : '' }}"
                                            href="{{ route('employeeTaxReport') }}">{{ __('Emp. Tax Ded. Rpt') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'salary-comparison-report' ? 'active' : '' }}"
                                            href="{{ route('salaryComparisonRpt') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Salary Comparison Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'periodwise-report' ? 'active' : '' }}"
                                            href="{{ route('periodwisepayroll') }}">{{ __('Period Wise Payroll') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'emp-sec-report' ? 'active' : '' }}"
                                            href="{{ route('empsecreport') }}">{{ __('Emp. Sec. Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'emp-transfer-report' ? 'active' : '' }}"
                                            href="{{ route('transferreport') }}">{{ __('Emp. Transfer Rpt.') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'emp-employment-history' ? 'active' : '' }}"
                                            href="{{ route('employmentHistory') }}">{{ __('Employment His.') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'emp-monthly-reconsilation' ? 'active' : '' }}"
                                            href="{{ route('employeeMonthlyReconsilation') }}">{{ __('Emp. Mon. Reconcilation') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif


                    <!--------------------- Start Account ----------------------------------->

                    @if (\Auth::user()->show_account() == 1)
                        @if (Gate::check('manage customer') ||
                                Gate::check('manage vender') ||
                                Gate::check('manage customer') ||
                                Gate::check('manage vender') ||
                                Gate::check('manage proposal') ||
                                Gate::check('manage bank account') ||
                                Gate::check('manage bank transfer') ||
                                Gate::check('manage invoice') ||
                                Gate::check('manage revenue') ||
                                Gate::check('manage credit note') ||
                                Gate::check('manage bill') ||
                                Gate::check('manage payment') ||
                                Gate::check('manage debit note') ||
                                Gate::check('manage chart of account') ||
                                Gate::check('manage journal entry') ||
                                Gate::check('manage head imprest') ||
                                Gate::check('balance sheet report') ||
                                Gate::check('ledger report') ||
                                Gate::check('trial balance report'))
                            <li class="dash-item dash-hasmenu">
                                <a href="#accounting"
                                    class="dash-link {{ Request::segment(1) == 'print-setting' ||
                                    Request::segment(1) == 'customer' ||
                                    // Request::segment(1) == 'vender' ||
                                    Request::segment(1) == 'proposal' ||
                                    Request::segment(1) == 'bank-account' ||
                                    Request::segment(1) == 'bank-transfer' ||
                                    Request::segment(1) == 'accounting-salary' ||
                                    Request::segment(1) == 'invoice' ||
                                    Request::segment(1) == 'revenue' ||
                                    Request::segment(1) == 'credit-note' ||
                                    Request::segment(1) == 'taxes' ||
                                    Request::segment(1) == 'product-category' ||
                                    Request::segment(1) == 'product-unit' ||
                                    Request::segment(1) == 'payment-method' ||
                                    Request::segment(1) == 'custom-field' ||
                                    Request::segment(1) == 'chart-of-account-type' ||
                                    (Request::segment(1) == 'transaction' &&
                                        Request::segment(2) != 'ledger' &&
                                        Request::segment(2) != 'balance-sheet' &&
                                        Request::segment(2) != 'trial-balance') ||
                                    Request::segment(1) == 'goal' ||
                                    Request::segment(1) == 'budget' ||
                                    Request::segment(1) == 'chart-of-account' ||
                                    Request::segment(1) == 'journal-entry' ||
                                    Request::segment(1) == 'bank-recipt-voucher' ||
                                    Request::segment(1) == 'bank-payment-voucher' ||
                                    Request::segment(1) == 'cash-recipt-voucher' ||
                                    Request::segment(1) == 'cash-payment-voucher' ||
                                    Request::segment(2) == 'ledger' ||
                                    Request::segment(2) == 'balance-sheet' ||
                                    Request::segment(2) == 'trial-balance' ||
                                    Request::segment(2) == 'profit-loss' ||
                                    Request::segment(1) == 'bill' ||
                                    Request::segment(1) == 'product-sub-category' ||
                                    Request::segment(1) == 'expense' ||
                                    Request::segment(1) == 'payment' ||
                                    Request::segment(1) == 'debit-note'
                                        ? ' active dash-trigger'
                                        : '' }}"><span
                                        class="dash-micon">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M4.5 6.75C1.93425 6.75 0 7.87875 0 9.375V15.375C0 16.8713 1.93425 18 4.5 18C7.06575 18 9 16.8713 9 15.375V9.375C9 7.87875 7.06575 6.75 4.5 6.75ZM7.5 12.375C7.5 12.7718 6.36075 13.5 4.5 13.5C2.63925 13.5 1.5 12.7718 1.5 12.375V11.364C2.2845 11.7638 3.32625 12 4.5 12C5.67375 12 6.7155 11.7638 7.5 11.364V12.375ZM4.5 8.25C6.36075 8.25 7.5 8.97825 7.5 9.375C7.5 9.77175 6.36075 10.5 4.5 10.5C2.63925 10.5 1.5 9.77175 1.5 9.375C1.5 8.97825 2.63925 8.25 4.5 8.25ZM4.5 16.5C2.63925 16.5 1.5 15.7718 1.5 15.375V14.364C2.2845 14.7638 3.32625 15 4.5 15C5.67375 15 6.7155 14.7638 7.5 14.364V15.375C7.5 15.7718 6.36075 16.5 4.5 16.5ZM18 3.75V14.25C18 16.3177 16.3177 18 14.25 18H10.5C10.0852 18 9.75 17.664 9.75 17.25C9.75 16.836 10.0852 16.5 10.5 16.5H14.25C15.4905 16.5 16.5 15.4905 16.5 14.25V3.75C16.5 2.5095 15.4905 1.5 14.25 1.5H6.75C5.5095 1.5 4.5 2.5095 4.5 3.75V4.5C4.5 4.914 4.16475 5.25 3.75 5.25C3.33525 5.25 3 4.914 3 4.5V3.75C3 1.68225 4.68225 0 6.75 0H14.25C16.3177 0 18 1.68225 18 3.75ZM9.75 7.5C9.33525 7.5 9 7.164 9 6.75C9 6.336 9.33525 6 9.75 6H13.5V4.5H7.5V4.875C7.5 5.289 7.16475 5.625 6.75 5.625C6.33525 5.625 6 5.289 6 4.875V4.5C6 3.67275 6.67275 3 7.5 3H13.5C14.3273 3 15 3.67275 15 4.5V6C15 6.82725 14.3273 7.5 13.5 7.5H9.75ZM10.5 13.5C10.5 13.086 10.8352 12.75 11.25 12.75H14.25C14.6648 12.75 15 13.086 15 13.5C15 13.914 14.6648 14.25 14.25 14.25H11.25C10.8352 14.25 10.5 13.914 10.5 13.5ZM10.5 10.5V9.75C10.5 9.336 10.8352 9 11.25 9C11.6648 9 12 9.336 12 9.75V10.5C12 10.914 11.6648 11.25 11.25 11.25C10.8352 11.25 10.5 10.914 10.5 10.5ZM15 10.5C15 10.914 14.6648 11.25 14.25 11.25C13.8352 11.25 13.5 10.914 13.5 10.5V9.75C13.5 9.336 13.8352 9 14.25 9C14.6648 9 15 9.336 15 9.75V10.5Z"
                                                fill="#474B4E" />
                                        </svg>
                                    </span>
                                    <span  class="dash-mtext">{{ __('Accounting System') }}
                                    </span>
                                </a>
                                <ul id="accounting" class="dash-submenu">

                                    @if (Gate::check('manage bank account') || Gate::check('manage bank transfer'))
                                        <li id="banking-id" class="dash-item dash-hasmenu ">
                                            <a  class="dash-link {{ Request::segment(1) == 'bank-account' || Request::segment(1) == 'bank-transfer' ? 'active dash-trigger' : '' }}"
                                                href="#banking">{{ __('Banking') }}</a>
                                            <ul id="banking" class="dash-submenu">
                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::segment(1) == 'bank-account' || Request::route()->getName() == 'bank-account.create' || Request::route()->getName() == 'bank-account.edit' ? ' active' : '' }}"
                                                        href="{{ route('bank-account.index') }}">{{ __('Account') }}</a>
                                                </li>
                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::route()->getName() == 'bank-transfer.index' || Request::route()->getName() == 'bank-transfer.create' || Request::route()->getName() == 'bank-transfer.edit' ? ' active' : '' }}"
                                                        href="{{ route('bank-transfer.index') }}">{{ __('Transfer') }}</a>
                                                </li>
                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::segment(1) == 'daily-closing' ? ' active' : '' }}"
                                                        href="{{ route('daily-closing.index') }}">{{ __('Daily Closing') }}</a>
                                                </li>
                                            </ul>
                                        </li>
                                    @endif
                                     <li id="accounting-hrm-id" class="dash-item dash-hasmenu ">
                                        <a class="dash-link {{ Request::segment(1) == 'accounting-salary' ? 'active dash-trigger' : '' }}"
                                            href="#accounting-hrm">{{ __('HRM') }}</a>
                                        <ul id="accounting-hrm" class="dash-submenu">
                                            <li class="dash-item ">
                                                <a class="dash-link {{ Request::segment(1) == 'accounting-salary' ? ' active' : '' }}"
                                                    href="{{ route('accounting.salary.index') }}">{{ __('Salary') }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                   @can('show account grn')
                                    <li id="accounts-inventory" class="dash-item dash-hasmenu ">
                                            <a class="dash-link {{ Request::segment(1) == 'accounts' && Request::segment(2) == 'grn' ? 'active dash-trigger' : '' }}"
                                                href="#acc_inventory">{{ __('Inventory') }}</a>
                                            <ul id="acc_inventory" class="dash-submenu">
                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::route()->getName() == 'grn.accounts_index' ? ' active' : '' }}"
                                                        href="{{ route('grn.accounts_index') }}">{{ __('GRN') }}</a>
                                                </li>
                                            </ul>
                                        </li>
                                    @endcan
                                    {{-- @if (Gate::check('manage customer') || Gate::check('manage proposal') || Gate::check('manage invoice') || Gate::check('manage revenue') || Gate::check('manage credit note'))
                                            <li class="dash-item dash-hasmenu ">
                                                <a class="dash-link {{ Request::segment(1) == 'customer' || Request::segment(1) == 'proposal' || Request::segment(1) == 'invoice' || Request::segment(1) == 'revenue' || Request::segment(1) == 'credit-note' ? 'active dash-trigger' : '' }}"
                        href="#sales">{{ __('Sales') }}</a>
                        <ul id="sales" class="dash-submenu">
                            @if (Gate::check('manage customer'))
                            <li class="dash-item">
                                <a class="dash-link  {{ Request::segment(1) == 'customer' ? 'active' : '' }}"
                                    href="{{ route('customer.index') }}">{{ __('Customer') }}</a>
                            </li>
                            @endif
                            @if (Gate::check('manage proposal'))
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::segment(1) == 'proposal' ? 'active' : '' }}"
                                    href="{{ route('proposal.index') }}">{{ __('Estimate') }}</a>
                            </li>
                            @endif
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::route()->getName() == 'invoice.index' || Request::route()->getName() == 'invoice.create' || Request::route()->getName() == 'invoice.edit' || Request::route()->getName() == 'invoice.show' ? ' active' : '' }}"
                                    href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a>
                            </li>
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::route()->getName() == 'revenue.index' || Request::route()->getName() == 'revenue.create' || Request::route()->getName() == 'revenue.edit' ? ' active' : '' }}"
                                    href="{{ route('revenue.index') }}">{{ __('Revenue') }}</a>
                            </li>
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::route()->getName() == 'credit.note' ? ' active' : '' }}"
                                    href="{{ route('credit.note') }}">{{ __('Credit Note') }}</a>
                            </li>
                        </ul>
                </li>
                @endif --}}
                                    {{-- @if (Gate::check('manage vender') || Gate::check('manage bill') || Gate::check('manage payment') || Gate::check('manage debit note'))
                <li class="dash-item dash-hasmenu ">
                                                <a class="dash-link {{ Request::segment(1) == 'bill' || Request::segment(1) == 'vender' || Request::segment(1) == 'vendor-advance' || Request::segment(1) == 'expense' || Request::segment(1) == 'payment' || Request::segment(1) == 'debit-note' ? 'active dash-trigger' : '' }}"
                href="#purchase">{{ __('Purchases') }}</a>
                <ul id="purchase" class="dash-submenu">
                    @if (Gate::check('manage vender'))
	                    <li class="dash-item ">
	                        <a class="dash-link {{ Request::segment(1) == 'vender' ? 'active' : '' }}"
	                            href="{{ route('vender.index') }}">{{ __('Vendor/Supplier') }}</a>
	                    </li>
						<li class="dash-item ">
							<a class="dash-link {{ Request::segment(1) == 'vendor-advance' ? 'active' : '' }}"
								href="{{ route('vendor-advance.index') }}">{{ __('Vendor Advance') }}</a>
						</li>
                    @endif
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::route()->getName() == 'bill.index' || Request::route()->getName() == 'bill.create' || Request::route()->getName() == 'bill.edit' || Request::route()->getName() == 'bill.show' ? ' active' : '' }}"
                            href="{{ route('bill.index') }}">{{ __('Bill') }}</a>
                    </li>
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::route()->getName() == 'expense.index' || Request::route()->getName() == 'expense.create' || Request::route()->getName() == 'expense.edit' || Request::route()->getName() == 'expense.show' ? ' active' : '' }}"
                            href="{{ route('expense.index') }}">{{ __('Expense') }}</a>
                    </li>
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::route()->getName() == 'payment.index' || Request::route()->getName() == 'payment.create' || Request::route()->getName() == 'payment.edit' ? ' active' : '' }}"
                            href="{{ route('payment.index') }}">{{ __('Payment') }}</a>
                    </li>
                    <li class="dash-item  ">
                        <a class="dash-link {{ Request::route()->getName() == 'debit.note' ? ' active' : '' }}"
                            href="{{ route('debit.note') }}">{{ __('Debit Note') }}</a>
                    </li>
                </ul>
                </li>
                @endif --}}
                                    @if (Gate::check('manage chart of account') ||
                                            Gate::check('manage journal entry') ||
                                            Gate::check('balance sheet report') ||
                                            Gate::check('ledger report') ||
                                            Gate::check('trial balance report'))
                                        <li id="accounts-id" class="dash-item dash-hasmenu ">
                                            <a class="dash-link {{ Request::segment(1) == 'chart-of-account' ||
                                            Request::segment(1) == 'journal-entry' ||
                                            Request::segment(1) == 'bank-recipt-voucher' ||
                                            Request::segment(1) == 'bank-payment-voucher' ||
                                            Request::segment(1) == 'cash-recipt-voucher' ||
                                            Request::segment(1) == 'cash-payment-voucher' ||
                                            Request::segment(2) == 'profit-loss' ||
                                            Request::segment(2) == 'ledger' ||
                                            Request::segment(2) == 'balance-sheet' ||
                                            Request::segment(2) == 'trial-balance'
                                                ? 'active dash-trigger'
                                                : '' }}"
                                                href="#double">{{ __('Accounts') }}</a>
                                            <ul id="double" class="dash-submenu">
                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::route()->getName() == 'chart-of-account.index' || Request::route()->getName() == 'chart-of-account.show' ? ' active' : '' }}"
                                                        href="{{ route('chart-of-account.index') }}">{{ __('Chart of Accounts') }}</a>
                                                </li>
                                                <li id="vouchers-id" class="dash-item dash-hasmenu ">
                                                    <a class="dash-link {{ Request::segment(1) == 'journal-entry' || Request::segment(1) == 'bank-recipt-voucher' || Request::segment(1) == 'bank-payment-voucher' || Request::segment(1) == 'cash-recipt-voucher' || Request::segment(1) == 'cash-payment-voucher' ? 'active dash-trigger' : '' }}"
                                                        href="#voucher">{{ __('Vouchers') }}</a>
                                                    <ul id="voucher" class="dash-submenu">
														<li class="dash-item ">
                                                            <a class="dash-link {{ Request::route()->getName() ==  'createVoucher' ? ' active' : '' }}"
                                                                href="{{ route('createVoucher') }}">{{ __('Add Voucher') }}</a>
                                                        </li>
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::route()->getName() == 'bank-recipt-voucher.edit' || Request::route()->getName() == 'bank-recipt-voucher.create' || Request::route()->getName() == 'bank-recipt-voucher.index' || Request::route()->getName() == 'bank-recipt-voucher.show' ? ' active' : '' }}"
                                                                href="{{ route('bank-recipt-voucher.index') }}">{{ __('BRV') }}</a>
                                                        </li>
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::route()->getName() == 'bank-payment-voucher.edit' || Request::route()->getName() == 'bank-payment-voucher.create' || Request::route()->getName() == 'bank-payment-voucher.index' || Request::route()->getName() == 'bank-payment-voucher.show' ? ' active' : '' }}"
                                                                href="{{ route('bank-payment-voucher.index') }}">{{ __('BPV') }}</a>
                                                        </li>
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::route()->getName() == 'cash-recipt-voucher.edit' || Request::route()->getName() == 'cash-recipt-voucher.create' || Request::route()->getName() == 'cash-recipt-voucher.index' || Request::route()->getName() == 'cash-recipt-voucher.show' ? ' active' : '' }}"
                                                                href="{{ route('cash-recipt-voucher.index') }}">{{ __('CRV') }}</a>
                                                        </li>
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::route()->getName() == 'cash-payment-voucher.edit' || Request::route()->getName() == 'cash-payment-voucher.create' || Request::route()->getName() == 'cash-payment-voucher.index' || Request::route()->getName() == 'cash-payment-voucher.show' ? ' active' : '' }}"
                                                                href="{{ route('cash-payment-voucher.index') }}">{{ __('CPV') }}</a>
                                                        </li>
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::route()->getName() == 'journal-entry.edit' || Request::route()->getName() == 'journal-entry.create' || Request::route()->getName() == 'journal-entry.index' || Request::route()->getName() == 'journal-entry.show' ? ' active' : '' }}"
                                                                href="{{ route('journal-entry.index') }}">{{ __('JV') }}</a>
                                                        </li>
                                                         @can('manage head imprest')
                                                            <li class="dash-item ">
                                                                <a class="dash-link {{ in_array(Request::route()->getName(), ['head-imprest-vouchers.index', 'head-imprest-vouchers.show']) ? ' active' : '' }}"
                                                                    href="{{ route('head-imprest-vouchers.index') }}">{{ __('Head Imprest Vouchers') }}</a>
                                                            </li>
                                                        @endcan
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::route()->getName() == 'report.head-imprest.cashflow' ? ' active' : '' }}"
                                                                href="{{ route('report.head-imprest.cashflow') }}">{{ __('Head Imprest Report') }}</a>
                                                        </li>
                                                        <li class="dash-item ">
                                                            <a class="dash-link {{ Request::route()->getName() == 'student-incomes.index' ? ' active' : '' }}"
                                                                href="{{ route('student-incomes.index') }}">{{ __('Student Incomes') }}</a>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::segment(2) == 'ledger' ? 'active' : '' }}"
                                                        href="{{ route('report.ledger', 0) }}">{{ __('Ledger Summary') }}</a>
                                                </li>
                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::route()->getName() == 'report.balance.sheet' ? ' active' : '' }}"
                                                        href="{{ route('report.balance.sheet') }}">{{ __('Balance Sheet') }}</a>
                                                </li>
                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::route()->getName() == 'report.profit.loss' ? ' active' : '' }}"
                                                        href="{{ route('report.profit.loss') }}">{{ __('Profit & Loss') }}</a>
                                                </li>

                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::route()->getName() == 'trial.balance' ? ' active' : '' }}"
                                                        href="{{ route('trial.balance') }}">{{ __('Trial Balance') }}</a>
                                                </li>
                                            </ul>
                                        </li>
                                    @endif
                                    {{-- @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'branch')
                                            <li class="dash-item ">
                                                <a class="dash-link {{ Request::segment(1) == 'budget' ? 'active' : '' }}"
                href="{{ route('budget.index') }}">{{ __('Budget Planner') }}</a>
                </li>
                @endif
                @if (Gate::check('manage goal'))
                <li class="dash-item">
                    <a class="dash-link {{ Request::segment(1) == 'goal' ? 'active' : '' }}"
                        href="{{ route('goal.index') }}">{{ __('Financial Goal') }}</a>
                </li>
                @endif --}}
                                    @if (Gate::check('manage constant tax') ||
                                            Gate::check('manage constant category') ||
                                            Gate::check('manage constant unit') ||
                                            Gate::check('manage constant payment method') ||
                                            Gate::check('manage constant custom field'))
                                        <li class="dash-item">
                                            <a class="dash-link  {{ Request::segment(1) == 'taxes' || Request::segment(1) == 'product-category' || Request::segment(1) == 'product-sub-category' || Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' || Request::segment(1) == 'chart-of-account-type' ? 'active dash-trigger' : '' }}"
                                                href="{{ route('taxes.index') }}">{{ __('Accounting Setup') }}</a>
                                        </li>
                                    @endif

                                    @if (Gate::check('manage print settings'))
                                        <li class="dash-item ">
                                            <a class="dash-link {{ Request::route()->getName() == 'print.setting' ? ' active' : '' }}"
                                                href="{{ route('print.setting') }}">{{ __('Print Settings') }}</a>
                                        </li>
                                    @endif

                                </ul>
                            </li>
                        @endif
                    @endif

                    <!--------------------- End Account ----------------------------------->
                    <li class="dash-item dash-hasmenu ">
                        <a href="{{ route('branches.index') }}"
                            class="dash-link {{ Request::segment(1) == 'branches' ? ' active' : '' }}">
                            <span class="dash-micon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M22.466 10.33C22.399 10.005 22.174 9.73598 21.867 9.60998C21.786 9.57598 19.837 8.78598 17.54 8.78598C17.001 8.78598 16.443 8.82998 15.873 8.91498C15.658 5.90298 15.269 3.92098 15.246 3.80798C15.183 3.48598 14.964 3.21598 14.662 3.08498C14.546 3.03498 11.777 1.85498 8.507 1.85498C5.237 1.85498 2.47 3.03398 2.354 3.08398C2.051 3.21398 1.833 3.48598 1.768 3.80898C1.737 3.96798 1 7.76098 1 12.83C1 16.862 1.469 20.102 1.67 21.309C1.75 21.791 2.168 22.144 2.656 22.144H21.643C22.138 22.144 22.558 21.782 22.632 21.292C22.8 20.173 23 18.397 23 16.295C23 12.92 22.488 10.434 22.466 10.33ZM3.514 20.145C3.299 18.646 3 15.995 3 12.831C3 9.04198 3.438 5.93798 3.636 4.73398C4.501 4.42798 6.404 3.85498 8.507 3.85498C10.61 3.85498 12.513 4.42998 13.38 4.73498C13.577 5.93198 14.014 9.01498 14.014 12.831C14.014 15.978 13.714 18.639 13.499 20.145H3.514ZM20.769 20.145H15.518C15.738 18.538 16.014 15.905 16.014 12.831C16.014 12.171 16.002 11.533 15.98 10.923C16.519 10.832 17.041 10.786 17.54 10.786C18.807 10.786 19.971 11.086 20.606 11.286C20.75 12.164 21 14.022 21 16.297C21 17.805 20.891 19.132 20.769 20.145Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M6.725 6.09998H5.728C5.175 6.09998 4.728 6.54798 4.728 7.09998C4.728 7.65198 5.175 8.09998 5.728 8.09998H6.725C7.278 8.09998 7.725 7.65198 7.725 7.09998C7.725 6.54798 7.278 6.09998 6.725 6.09998Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M11.286 6.09998H10.29C9.73701 6.09998 9.29001 6.54798 9.29001 7.09998C9.29001 7.65198 9.73701 8.09998 10.29 8.09998H11.286C11.839 8.09998 12.286 7.65198 12.286 7.09998C12.286 6.54798 11.839 6.09998 11.286 6.09998Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M6.725 9.67493H5.728C5.175 9.67493 4.728 10.1229 4.728 10.6749C4.728 11.2269 5.175 11.6749 5.728 11.6749H6.725C7.278 11.6749 7.725 11.2269 7.725 10.6749C7.725 10.1229 7.278 9.67493 6.725 9.67493Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M11.286 9.67493H10.29C9.73701 9.67493 9.29001 10.1229 9.29001 10.6749C9.29001 11.2269 9.73701 11.6749 10.29 11.6749H11.286C11.839 11.6749 12.286 11.2269 12.286 10.6749C12.286 10.1229 11.839 9.67493 11.286 9.67493Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M6.725 13.249H5.728C5.175 13.249 4.728 13.697 4.728 14.249C4.728 14.801 5.175 15.249 5.728 15.249H6.725C7.278 15.249 7.725 14.801 7.725 14.249C7.725 13.697 7.278 13.249 6.725 13.249Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M11.286 13.249H10.29C9.73701 13.249 9.29001 13.697 9.29001 14.249C9.29001 14.801 9.73701 15.249 10.29 15.249H11.286C11.839 15.249 12.286 14.801 12.286 14.249C12.286 13.697 11.839 13.249 11.286 13.249Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M6.725 16.824H5.728C5.175 16.824 4.728 17.272 4.728 17.824C4.728 18.376 5.175 18.824 5.728 18.824H6.725C7.278 18.824 7.725 18.376 7.725 17.824C7.725 17.272 7.278 16.824 6.725 16.824Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M11.286 16.824H10.29C9.73701 16.824 9.29001 17.272 9.29001 17.824C9.29001 18.376 9.73701 18.824 10.29 18.824H11.286C11.839 18.824 12.286 18.376 12.286 17.824C12.286 17.272 11.839 16.824 11.286 16.824Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M18.03 14.685C18.5817 14.685 19.029 14.2377 19.029 13.686C19.029 13.1343 18.5817 12.687 18.03 12.687C17.4783 12.687 17.031 13.1343 17.031 13.686C17.031 14.2377 17.4783 14.685 18.03 14.685Z"
                                        fill="#474B4E" />
                                    <path
                                        d="M18.03 18.2561C18.5817 18.2561 19.029 17.8088 19.029 17.2571C19.029 16.7053 18.5817 16.2581 18.03 16.2581C17.4783 16.2581 17.031 16.7053 17.031 17.2571C17.031 17.8088 17.4783 18.2561 18.03 18.2561Z"
                                        fill="#474B4E" />
                                </svg>


                            </span><span class="dash-mtext">{{ __('Branch Info') }}</span>
                        </a>

                    </li>

                    @if (Gate::check('view space') ||
                            Gate::check('view spacetype') ||
                            Gate::check('manage ismail') ||
                            Gate::check('manage vistor'))
                        <li class="dash-item dash-hasmenu">
                            <a href="#setup"
                                class="dash-link {{ Request::segment(1) == 'registerOption'  || Request::segment(1) == 'bulk-billing' || Request::segment(1) == 'session' || Request::segment(1) == 'classes' || Request::segment(1) == 'section' || Request::segment(1) == 'fee_head' || Request::segment(1) == 'class_wise_fee' || Request::segment(1) == 'studypack' || Request::segment(1) == 'concession_policy' ? ' active dash-trigger' : '' }}"><span
                                    class="dash-micon">

                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_494_11464)">
                                            <path
                                                d="M17.0833 10H10.8333V8.33333H12.5C14.3383 8.33333 15.8333 6.83833 15.8333 5V3.33333C15.8333 1.495 14.3383 0 12.5 0H7.5C5.66167 0 4.16667 1.495 4.16667 3.33333V5C4.16667 6.83833 5.66167 8.33333 7.5 8.33333H9.16667V10H0.833333C0.3725 10 0 10.3733 0 10.8333C0 11.2933 0.3725 11.6667 0.833333 11.6667H1.66667V19.1667C1.66667 19.6275 2.03917 20 2.5 20C2.96083 20 3.33333 19.6275 3.33333 19.1667V11.6667H12.5V17.0833C12.5 18.6917 13.8083 20 15.4167 20H17.0833C18.6917 20 20 18.6917 20 17.0833V12.9167C20 11.3083 18.6917 10 17.0833 10ZM5.83333 5V3.33333C5.83333 2.41417 6.58083 1.66667 7.5 1.66667H12.5C13.4192 1.66667 14.1667 2.41417 14.1667 3.33333V5C14.1667 5.91917 13.4192 6.66667 12.5 6.66667H7.5C6.58083 6.66667 5.83333 5.91917 5.83333 5ZM17.0833 11.6667C17.7725 11.6667 18.3333 12.2275 18.3333 12.9167V14.1667H14.1667V11.6667H17.0833ZM17.0833 18.3333H15.4167C14.7275 18.3333 14.1667 17.7725 14.1667 17.0833V15.8333H18.3333V17.0833C18.3333 17.7725 17.7725 18.3333 17.0833 18.3333Z"
                                                fill="#474B4E" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_494_11464">
                                                <rect width="20" height="20" fill="white" />
                                            </clipPath>
                                        </defs>
                                    </svg>

                                </span>

                                <span class="dash-mtext">{{ __('Setup') }}</span>
                            </a>
                            <ul id="setup" class="dash-submenu">
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'registerOption' ? 'active' : '' }}"
                                            href="{{ route('registerOption.index') }}">{{ __('Register Option') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'session' ? 'active' : '' }}"
                                            href="{{ route('session.index') }}">{{ __('Session Setup') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'classes' ? 'active' : '' }}"
                                            href="{{ route('classes.index') }}">{{ __('Class Setup') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'section' ? 'active' : '' }}"
                                            href="{{ route('section.index') }}">{{ __('Class & Section Setup') }}</a>
                                    </li>
                                @endcan
								@can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'bulk-billing' ? 'active' : '' }}"
                                            href="{{ route('bulk-billing.create') }}">{{ __('Bulk Billing') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'fee_head' ? 'active' : '' }}"
                                            href="{{ route('fee_head.index') }}">{{ __('Fee Heads Setup') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'class_wise_fee' ? 'active' : '' }}"
                                            href="{{ route('class_wise_fee.index') }}">{{ __('Fee Structure Setup') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'concession_policy' ? 'active' : '' }}"
                                            href="{{ route('concession_policy.index') }}">{{ __('Concession Policy Setup') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'studypack' ? 'active' : '' }}"
                                            href="{{ route('studypack.index') }}">{{ __('Study Packs Setup') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'tax-slab' ? 'active' : '' }}"
                                            href="{{ route('tax-slab.index') }}">{{ __('Tax Slab Setup') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif
                    {{-- Student Formation and Settelement --}}
                    @if (Gate::check('view space') ||
                            Gate::check('view spacetype') ||
                            Gate::check('manage ismail') ||
                            Gate::check('manage vistor'))
                        <li class="dash-item dash-hasmenu">
                            <a href="#Student_formation"
                                class="dash-link {{ Request::segment(1) == 'withdrawlstudent' || Request::segment(1) == 'remove-late-fee' || Request::segment(1) == 'clearanceCertificate' || Request::segment(1) == 'readmissionstudent' || Request::segment(1) == 'transferstudent' || Request::segment(1) == 'concession' || Request::segment(1) == 'student-promotion' ? ' active dash-trigger' : '' }}"><span
                                    class="dash-micon">
                                    <svg width="21" height="22" viewBox="0 0 21 22" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_494_11456)">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M3.86633 15.6411C5.15511 14.8544 6.97948 14.2602 9.0047 14.2602C9.38129 14.2602 9.75788 14.2854 10.1177 14.3188C10.3772 14.3523 10.645 14.2686 10.8542 14.1012C11.0634 13.9339 11.1973 13.6912 11.214 13.4317C11.2475 13.1723 11.1638 12.8962 10.9964 12.6953C10.8374 12.4861 10.5864 12.3522 10.3269 12.3271C9.89178 12.2769 9.45661 12.2601 9.0047 12.2601C6.61126 12.2601 4.42703 12.9547 2.82861 13.9339C2.02522 14.4193 1.33899 14.9967 0.845236 15.6243C0.359853 16.2436 0 16.9717 0 17.7667C0 18.612 0.410065 19.2815 1.00424 19.7585C1.55657 20.2104 2.30139 20.5116 3.08804 20.7125C4.66972 21.1309 6.77863 21.2648 9.0047 21.2648H9.69093C9.95873 21.2648 10.2098 21.1477 10.3939 20.9552C10.578 20.7627 10.6868 20.5033 10.6784 20.2439C10.6784 19.9761 10.5696 19.725 10.3772 19.5409C10.1847 19.3568 9.92525 19.248 9.66582 19.2563H9.0047C6.82047 19.2563 4.92078 19.1141 3.59853 18.771C2.94577 18.5952 2.50223 18.386 2.25117 18.1852C2.03359 18.0178 2.00011 17.8839 2.00011 17.7584C2.00011 17.5826 2.09217 17.273 2.41855 16.8629C2.73656 16.4612 3.22194 16.026 3.86633 15.6327V15.6411ZM20.202 16.5365C20.0179 16.3608 19.7752 16.2687 19.5241 16.2687H18.8546L18.7877 16.1014C18.6705 15.8168 18.5115 15.549 18.3107 15.3147L18.6454 14.7373L18.6956 14.6368C18.7961 14.3941 18.8044 14.1347 18.7124 13.892C18.6287 13.6493 18.4529 13.4569 18.227 13.3397C17.9927 13.2225 17.7332 13.1974 17.4905 13.2727C17.2395 13.3481 17.0386 13.5154 16.9131 13.733L16.57 14.3105L16.3524 14.277C16.0595 14.2435 15.7666 14.2519 15.4737 14.3105L15.1306 13.733L15.072 13.6326C14.9214 13.4234 14.6954 13.2811 14.436 13.2393C14.1849 13.1974 13.9255 13.256 13.7163 13.3899C13.4987 13.5322 13.348 13.7414 13.2895 13.9924C13.2309 14.2435 13.2644 14.5113 13.3983 14.7289L13.733 15.3147L13.5907 15.4821C13.4234 15.7248 13.2811 15.9842 13.1807 16.2604H12.394C12.143 16.3022 11.9086 16.4194 11.7413 16.6118C11.5823 16.8043 11.4902 17.0638 11.5069 17.3148C11.5237 17.5659 11.6325 17.8086 11.8166 17.9843C12.0007 18.1517 12.2517 18.2521 12.5028 18.2521H13.1723L13.2392 18.4195C13.3564 18.704 13.5154 18.9718 13.7163 19.1978L13.3815 19.7752L13.3313 19.884C13.2309 20.1183 13.2225 20.3778 13.3062 20.6204C13.3983 20.8631 13.5656 21.064 13.7999 21.1728C14.0343 21.2899 14.2937 21.315 14.5364 21.2314C14.7875 21.1644 14.9883 20.997 15.1138 20.7794L15.4569 20.202L15.6745 20.2355C15.9674 20.269 16.2603 20.2606 16.5532 20.202L16.8964 20.7794L16.9549 20.8799C17.1056 21.0807 17.3315 21.223 17.5826 21.2648C17.8337 21.315 18.1014 21.2648 18.3107 21.1142C18.5283 20.9719 18.6789 20.7627 18.7375 20.5033C18.7961 20.2522 18.7626 19.9928 18.6287 19.7668L18.2939 19.1894L18.4278 19.022C18.6036 18.7793 18.7458 18.5199 18.8463 18.2437H19.6329C19.884 18.2019 20.1183 18.0847 20.2857 17.8923C20.4447 17.6914 20.5367 17.4403 20.52 17.1893C20.5033 16.9382 20.3861 16.6955 20.202 16.5198V16.5365ZM16.729 17.9759C16.5365 18.1601 16.2771 18.2688 16.0176 18.2688C15.7582 18.2688 15.4988 18.1601 15.3063 17.9759C15.1222 17.7918 15.0134 17.5324 15.0134 17.2646C15.0134 16.9968 15.1222 16.7457 15.3063 16.5616C15.4904 16.3775 15.7582 16.2687 16.0176 16.2687C16.2771 16.2687 16.5365 16.3692 16.729 16.5616C16.9215 16.7541 17.0219 17.0052 17.0219 17.2646C17.0219 17.524 16.9131 17.7918 16.729 17.9759Z"
                                                fill="#474B4E" />
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M17.2478 2.40179L11.9003 0.309624C10.8458 -0.100441 9.67419 -0.100441 8.61974 0.309624L3.27216 2.40179C2.82025 2.56917 2.52734 3.00434 2.52734 3.47298C2.52734 3.94163 2.82025 4.3768 3.27216 4.55254L5.62375 5.4731V6.93762C5.62375 9.49006 7.69919 11.5739 10.26 11.5739C12.8208 11.5739 14.8962 9.49843 14.8962 6.93762V5.4731L15.6662 5.17183V8.10923C15.6662 8.75362 16.185 9.26411 16.821 9.26411C17.4571 9.26411 17.9759 8.74525 17.9759 8.10923V3.47298C17.9759 2.70307 17.3483 2.43527 17.2478 2.40179ZM12.5698 6.94599C12.5698 8.21803 11.532 9.26411 10.2516 9.26411C8.97123 9.26411 7.93351 8.21803 7.93351 6.94599V6.38528L8.61137 6.64471C9.1386 6.85393 9.69093 6.95436 10.2516 6.95436C10.8123 6.95436 11.3647 6.85393 11.9003 6.64471L12.5698 6.38528V6.94599ZM11.055 4.49396C10.5362 4.69481 9.9671 4.69481 9.45661 4.49396L6.87906 3.48135L9.45661 2.47711C9.9671 2.27626 10.5362 2.27626 11.055 2.47711L13.6326 3.48135L11.055 4.49396Z"
                                                fill="#474B4E" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_494_11456">
                                                <rect width="20.52" height="21.3067" fill="white" />
                                            </clipPath>
                                        </defs>
                                    </svg>


                                </span>

                                <span class="dash-mtext">{{ __('Student Formation') }}</span>
                            </a>
                            <ul id="Student_formation" class="dash-submenu">
 								@can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'student-import' ? 'active' : '' }}"
                                            href="{{ route('student.import') }}">{{ __('Student Import') }}</a>
                                    </li>
                                @endcan
                                @can('manage promotion')
                                    <li class="dash-item">
                                        <a class="dash-link  {{ request()->is('student-promotion*') ? 'active' : '' }}"
                                            href="{{ route('student-promotion.index') }}">{{ __('Student Promotion') }}</a>
                                    </li>
                                @endcan
								@can('manage promotion')
                                    <li class="dash-item">
                                        <a class="dash-link  {{ request()->is('section.bulkindex') ? 'active' : '' }}"
                                            href="{{ route('section.bulkindex') }}">{{ __('Bulk Section Update') }}</a>
                                    </li>
                                @endcan
                                @if(\Auth::user()->type == 'company')
                                <li class="dash-item">
                                        <a class="dash-link  {{ Request::segment(1) == 'remove-late-fee' ? 'active dash-trigger' : '' }}"
                                            href="{{ route('remove-late-fee.index') }}">{{ __('Remove Late Fee') }}</a>
                                </li>
                                @endif
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'transferstudent' ? 'active' : '' }}"
                                            href="{{ route('transferstudent.index') }}">{{ __('Student Transfer') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'bulk-transfer' ? 'active' : '' }}"
                                            href="{{ route('bulk-transfer.index') }}">{{ __('Bulk Student Transfer') }}</a>
                                    </li>
                                @endcan
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'concession.index' ? 'active' : '' }}"
                                        href="{{ route('concession.index', ['status' => 'Canceled']) }}">{{ __('Cancel Concession') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'concession.create' ? 'active' : '' }}"
                                        href="#" data-size="xl"
                                        data-url="{{ route('concession.create') }}"
                                        data-ajax-popup="true">{{ __('Concession Application') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'concession' ? 'active' : '' }}"
                                        href="{{ route('concession.index', ['status' => 'Canceled']) }}">{{ __('Concession List') }}</a>
                                </li>

                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'withdrawlstudent/create' ? 'active' : '' }}"
                                            href="#" data-size="lg"
                                            data-url="{{ route('withdrawlstudent.create') }}"
                                            data-ajax-popup="true">{{ __('Withdrawal Application') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'withdrawlstudent' ? 'active' : '' }}"
                                            href="{{ route('withdrawlstudent.index') }}">{{ __('Withdrawl List') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'readmissionstudent' ? 'active' : '' }}"
                                            href="{{ route('readmissionstudent.index') }}">{{ __('Student Readmission') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'clearanceCertificate' ? 'active' : '' }}"
                                            href="{{ route('clearance.index') }}">{{ __('Clearance Certificate') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif
                    <!--------------------- Student Account ----------------------------------->

                    @if (Gate::check('view space') ||
                            Gate::check('view spacetype') ||
                            Gate::check('manage ismail') ||
                            Gate::check('manage vistor'))
                        <li class="dash-item dash-hasmenu">
                            <a href="#student_accounts"
                                class="dash-link
                                {{ Request::segment(1) == 'registration' ||
                                Request::segment(1) == 'enrollment' ||
                                Request::segment(1) == 'space' ||
                                Request::segment(1) == 'account-assets' ||
                                Request::segment(1) == 'isvisitor' ||
                                Request::segment(1) == 'ismail' ||
                                Request::segment(1) == 'account-wise-fee' ||
                                Request::segment(1) == 'student_receipt' ||
                                Request::segment(1) == 'student-receipt' ||
                                Request::segment(1) == 'feereminderslip' ||
                                Request::route()->getName() == 'studypackreceipts.daily' ||
                                Request::segment(1) == 'registration-challan' ||
                                Request::segment(1) == 'admission-challan' ||
                                Request::segment(1) == 'regular-challan' ||
                                Request::segment(1) == 'withdraw-challan' ||
                                Request::segment(1) == 'advance-challan' ||
                                Request::segment(1) == 'transfer-challan' ||
                                Request::segment(1) == 'readmission-challan' ||
                                Request::segment(1) == 'studypackchallan'
                                    ? ' active dash-trigger'
                                    : '' }}"><span
                                    class="dash-micon">

                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8.49979 19.1667C8.49979 19.625 8.1173 20 7.64981 20H4.24989C1.90395 20 0 18.1333 0 15.8333V4.16667C0 1.86667 1.90395 0 4.24989 0H11.0497C13.3957 0 15.2996 1.86667 15.2996 4.16667V5.83333C15.2996 6.29167 14.9171 6.66667 14.4496 6.66667C13.9822 6.66667 13.5997 6.29167 13.5997 5.83333V4.16667C13.5997 2.79167 12.4522 1.66667 11.0497 1.66667H4.24989C2.84743 1.66667 1.69996 2.79167 1.69996 4.16667V15.8333C1.69996 17.2083 2.84743 18.3333 4.24989 18.3333H7.64981C8.1173 18.3333 8.49979 18.7083 8.49979 19.1667ZM11.8997 5C11.8997 4.54167 11.5172 4.16667 11.0497 4.16667H4.24989C3.78241 4.16667 3.39991 4.54167 3.39991 5C3.39991 5.45833 3.78241 5.83333 4.24989 5.83333H11.0497C11.5172 5.83333 11.8997 5.45833 11.8997 5ZM4.24989 12.5C3.78241 12.5 3.39991 12.875 3.39991 13.3333C3.39991 13.7917 3.78241 14.1667 4.24989 14.1667H5.94985C6.41734 14.1667 6.79983 13.7917 6.79983 13.3333C6.79983 12.875 6.41734 12.5 5.94985 12.5H4.24989ZM8.49979 9.16667C8.49979 8.70833 8.1173 8.33333 7.64981 8.33333H4.24989C3.78241 8.33333 3.39991 8.70833 3.39991 9.16667C3.39991 9.625 3.78241 10 4.24989 10H7.64981C8.1173 10 8.49979 9.625 8.49979 9.16667ZM19.49 8.73333L15.7161 7.29167C14.9681 7.00833 14.1521 7.00833 13.4042 7.29167L9.63876 8.73333C9.32427 8.85 9.12027 9.15 9.12027 9.475C9.12027 9.8 9.32427 10.1 9.63876 10.2167L11.2962 10.85V11.8583C11.2962 13.625 12.7582 15.0583 14.5601 15.0583C16.3621 15.0583 17.8241 13.625 17.8241 11.8583V10.85L18.368 10.6417V12.6667C18.368 13.1083 18.7335 13.4667 19.184 13.4667C19.6345 13.4667 20 13.1083 20 12.6667V9.46667C20 8.93333 19.558 8.75 19.49 8.725V8.73333ZM13.7357 9.03333C14.0756 8.9 14.4581 8.9 14.7981 9.03333L16.5151 9.69167L14.7981 10.35C14.4581 10.4833 14.0756 10.4833 13.7357 10.35L12.0187 9.69167L13.7357 9.03333ZM15.8096 11.95C15.8096 12.7833 15.1211 13.4667 14.2626 13.4667C13.4042 13.4667 12.7157 12.7917 12.7157 11.95V11.5833L13.1662 11.7583C13.5232 11.8917 13.8887 11.9583 14.2626 11.9583C14.6366 11.9583 15.0021 11.8917 15.3591 11.7583L15.8096 11.5833V11.95ZM18.9035 18.7167C19.116 19.1083 18.9545 19.5917 18.555 19.8C18.436 19.8583 18.3085 19.8917 18.181 19.8917C17.8836 19.8917 17.6031 19.7333 17.4586 19.4583C16.8891 18.3917 15.7841 17.7333 14.5686 17.7333C13.3532 17.7333 12.2397 18.3917 11.6787 19.4583C11.4662 19.85 10.9817 20 10.5737 19.8C10.1742 19.5917 10.0212 19.1167 10.2252 18.7167C11.0752 17.125 12.7327 16.1333 14.5686 16.1333C16.4046 16.1333 18.062 17.125 18.912 18.7167H18.9035Z"
                                            fill="#474B4E" />
                                    </svg>

                                </span>

                                <span class="dash-mtext">{{ __('Student Management') }}</span>
                            </a>

                            <ul id="student_accounts" class="dash-submenu">

                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'registration' ? 'active' : '' }}"
                                            href="{{ route('registration.index') }}">{{ __('Registration') }}</a>
                                    </li>
                                @endcan

                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'enrollment' ? 'active' : '' }}"
                                            href="{{ route('enrollment.index') }}">{{ __('Student Profile') }}</a>
                                    </li>
                                @endcan

                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'account-wise-fee' ? 'active' : '' }}"
                                            href="{{ route('account-wise-fee.index') }}">{{ __('Account Wise Update') }}</a>
                                    </li>
                                @endcan

                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'student_receipt' ? 'active' : '' }}"
                                            href="{{ route('student_receipt.index') }}">{{ __('Daily CMR Statement') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::route()->getName() == 'studypackreceipts.daily' || Request::segment(1) == 'studypackreceipts' ? 'active' : '' }}"
                                            href="{{ route('studypackreceipts.daily') }}">{{ __('StudyPack Payments') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'student-receipt' ? 'active' : '' }}"
                                            href="{{ route('student_receipt.list') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Period Wise CMR Statement') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'feereminderslip' ? 'active' : '' }}"
                                            href="{{ route('feereminderslip.index') }}">{{ __('Fee Reminder Slip') }}</a>
                                    </li>
                                @endcan
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'registration-challan' ? 'active' : '' }}"
                                        href="{{ route('registrationchallanlist') }}">{{ __('Registration challan list') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link  {{ Request::segment(1) == 'admission-challan' ? 'active' : '' }}"
                                        href="{{ route('admissionchallanlist') }}">{{ __('Admission challan list') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'regular-challan' ? 'active' : '' }}"
                                        href="{{ route('regularchallanlist') }}">{{ __('Regular challan list') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'advance-challan' ? 'active' : '' }}"
                                        href="{{ route('advancechallanlist') }}">{{ __('Advance challan list') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'withdraw-challan' ? 'active' : '' }}"
                                        href="{{ route('withdrawchallanlist') }}">{{ __('Withdrawal challan list') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'transfer-challan' ? 'active' : '' }}"
                                        href="{{ route('transferchallanlist') }}">{{ __('Transfer challan list') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'readmission-challan' ? 'active' : '' }}"
                                        href="{{ route('readmissionchallanlist') }}">{{ __('Re-Admission challan list') }}</a>
                                </li>
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'studypackchallan' ? 'active' : '' }}"
                                            href="{{ route('studypackchallan.index') }}">{{ __('StudyPack Challans List') }}</a>
                                    </li>
                                @endcan

                                <!-- {{--
                                @can('view spacetype')
                                    <li class="dash-item dash-hasmenu ">
                                        <a class="dash-link {{ Request::segment(1) == 'registration-challan' || Request::segment(1) == 'admission-challan' || Request::segment(1) == 'regular-challan' || Request::segment(1) == 'readmission-challan' || Request::segment(1) == 'transfer-challan' || Request::segment(1) == 'bank-account' ? ' active dash-trigger' : '' }}"
                                            href="#challans">{{ __('Challans') }}</a>
                                        <ul id="challans" class="dash-submenu">
                                            <li class="dash-item ">
                                                <a class="dash-link {{ Request::segment(1) == 'registration-challan' ? 'active' : '' }}"
                                                    href="{{ route('registrationchallanlist') }}">{{ __('Registration challan list') }}</a>
                                            </li>
                                            <li class="dash-item ">
                                                <a class="dash-link  {{ Request::segment(1) == 'admission-challan' ? 'active' : '' }}"
                                                    href="{{ route('admissionchallanlist') }}">{{ __('Admission challan list') }}</a>
                                            </li>
                                            <li class="dash-item ">
                                                <a class="dash-link {{ Request::segment(1) == 'regular-challan' ? 'active' : '' }}"
                                                    href="{{ route('regularchallanlist') }}">{{ __('Regular challan list') }}</a>
                                            </li>
                                            <li class="dash-item ">
                                                <a class="dash-link {{ Request::segment(1) == 'transfer-challan' ? 'active' : '' }}"
                                                    href="{{ route('transferchallanlist') }}">{{ __('Transfer challan list') }}</a>
                                            </li>
                                            <li class="dash-item ">
                                                <a class="dash-link {{ Request::segment(1) == 'readmission-challan' ? 'active' : '' }}"
                                                    href="{{ route('readmissionchallanlist') }}">{{ __('Re-Admission challan list') }}</a>
                                            </li>
                                            @can('view spacetype')
                                                <li class="dash-item ">
                                                    <a class="dash-link {{ Request::segment(1) == 'studypackchallan' ? 'active' : '' }}"
                                                        href="{{ route('studypackchallan.index') }}">{{ __('StudyPack Challans List') }}</a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcan --}} -->
                            </ul>
                        </li>
                    @endif

                    <!--------------------- End Workspace ----------------------------------->

                    <!--------------------- Student Reports ----------------------------------->

                    @if (Gate::check('view space') ||
                            Gate::check('view spacetype') ||
                            Gate::check('manage ismail') ||
                            Gate::check('manage vistor'))
                        <li class="dash-item dash-hasmenu">
                            <a href="#student_reports"
                                class="dash-link {{ Request::segment(1) == 'registrationdetailreport' ||  Request::segment(1) == 'track-registration' ||Request::segment(1) == 'studenttransferin' ||Request::segment(1) == 'student-single-account' ||Request::segment(1) == 'studenttransferout' ||Request::segment(1) == 'classwisefeereport' ||Request::segment(1) == 'admissionwithdrawalreport' ||Request::segment(1) == 'studentstrengthreport' ||Request::segment(1) == 'studentSecurityReport' ||Request::segment(1) == 'student-withdarawl-listing' ||Request::segment(1) == 'sibling-students' ||Request::segment(1) == 'studypackStudent' ||Request::segment(1) == 'student-strength' ||Request::segment(1) == 'sessionBranchWise' ||Request::segment(1) == 'sessionMonthWise' ||Request::segment(1) == 'profession-wise-listing' ||Request::segment(1) == 'student-wise-statistic-report' ||Request::segment(1) == 'profession-wise-listing' ||Request::segment(1) == 'student-data-analysis' ||Request::segment(1) == 'studypack-student' ||Request::segment(1) == 'sibling-students' ||Request::segment(1) == 'staff-child' ||Request::segment(1) == 'student-data-analysis' ||Request::segment(1) == 'studypack-student' ||Request::segment(1) == 'monthlystatistics' ||Request::segment(1) == 'fee-revision-report' ||Request::segment(1) == 'student-promotion-report' ||Request::segment(1) == 'tuition_fee' ||Request::segment(1) == 'spacetype' ||Request::segment(1) == 'sessionMonthBranchWise' ||Request::segment(1) == 'sessionWise' ||Request::segment(1) == 'feestructurelisting' ||Request::segment(1) == 'advancechallanreport' ||Request::segment(1) == 'withdrawl_notice' ||Request::segment(1) == 'student-wise-statistic-report' ||Request::segment(1) == 'period-wise-statistic-report' ||Request::segment(1) == 'student-fee-receipt-detail' ||Request::segment(1) == 'fee-receipt-summary' ||Request::segment(1) == 'studenttransferin' ||Request::segment(1) == 'studenttransferout' ||Request::segment(1) == 'classwisefeereport' ||Request::segment(1) == 'admissionwithdrawalreport' ||Request::segment(1) == 'studentstrengthreport' ||Request::segment(1) == 'studentSecurityReport' ||Request::segment(1) == 'student-withdarawl-listing' ||Request::segment(1) == 'student-defaulter' ||Request::segment(1) == 'admissionlisting' ||Request::segment(1) == 'student-defaulter-sm' ||Request::segment(1) == 'space' ||Request::segment(1) == 'account-assets' || Request::segment(1) == 'student-fee-detail' || Request::segment(1) == 'monthlyperchallanreport'|| Request::segment(1) ==  'student-sts-report'||Request::segment(1) == 'monthlychallanreport'? ' active dash-trigger': '' }}"><span
                                    class="dash-micon">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8.49979 19.1667C8.49979 19.625 8.1173 20 7.64981 20H4.24989C1.90395 20 0 18.1333 0 15.8333V4.16667C0 1.86667 1.90395 0 4.24989 0H11.0497C13.3957 0 15.2996 1.86667 15.2996 4.16667V5.83333C15.2996 6.29167 14.9171 6.66667 14.4496 6.66667C13.9822 6.66667 13.5997 6.29167 13.5997 5.83333V4.16667C13.5997 2.79167 12.4522 1.66667 11.0497 1.66667H4.24989C2.84743 1.66667 1.69996 2.79167 1.69996 4.16667V15.8333C1.69996 17.2083 2.84743 18.3333 4.24989 18.3333H7.64981C8.1173 18.3333 8.49979 18.7083 8.49979 19.1667ZM11.8997 5C11.8997 4.54167 11.5172 4.16667 11.0497 4.16667H4.24989C3.78241 4.16667 3.39991 4.54167 3.39991 5C3.39991 5.45833 3.78241 5.83333 4.24989 5.83333H11.0497C11.5172 5.83333 11.8997 5.45833 11.8997 5ZM4.24989 12.5C3.78241 12.5 3.39991 12.875 3.39991 13.3333C3.39991 13.7917 3.78241 14.1667 4.24989 14.1667H5.94985C6.41734 14.1667 6.79983 13.7917 6.79983 13.3333C6.79983 12.875 6.41734 12.5 5.94985 12.5H4.24989ZM8.49979 9.16667C8.49979 8.70833 8.1173 8.33333 7.64981 8.33333H4.24989C3.78241 8.33333 3.39991 8.70833 3.39991 9.16667C3.39991 9.625 3.78241 10 4.24989 10H7.64981C8.1173 10 8.49979 9.625 8.49979 9.16667ZM19.49 8.73333L15.7161 7.29167C14.9681 7.00833 14.1521 7.00833 13.4042 7.29167L9.63876 8.73333C9.32427 8.85 9.12027 9.15 9.12027 9.475C9.12027 9.8 9.32427 10.1 9.63876 10.2167L11.2962 10.85V11.8583C11.2962 13.625 12.7582 15.0583 14.5601 15.0583C16.3621 15.0583 17.8241 13.625 17.8241 11.8583V10.85L18.368 10.6417V12.6667C18.368 13.1083 18.7335 13.4667 19.184 13.4667C19.6345 13.4667 20 13.1083 20 12.6667V9.46667C20 8.93333 19.558 8.75 19.49 8.725V8.73333ZM13.7357 9.03333C14.0756 8.9 14.4581 8.9 14.7981 9.03333L16.5151 9.69167L14.7981 10.35C14.4581 10.4833 14.0756 10.4833 13.7357 10.35L12.0187 9.69167L13.7357 9.03333ZM15.8096 11.95C15.8096 12.7833 15.1211 13.4667 14.2626 13.4667C13.4042 13.4667 12.7157 12.7917 12.7157 11.95V11.5833L13.1662 11.7583C13.5232 11.8917 13.8887 11.9583 14.2626 11.9583C14.6366 11.9583 15.0021 11.8917 15.3591 11.7583L15.8096 11.5833V11.95ZM18.9035 18.7167C19.116 19.1083 18.9545 19.5917 18.555 19.8C18.436 19.8583 18.3085 19.8917 18.181 19.8917C17.8836 19.8917 17.6031 19.7333 17.4586 19.4583C16.8891 18.3917 15.7841 17.7333 14.5686 17.7333C13.3532 17.7333 12.2397 18.3917 11.6787 19.4583C11.4662 19.85 10.9817 20 10.5737 19.8C10.1742 19.5917 10.0212 19.1167 10.2252 18.7167C11.0752 17.125 12.7327 16.1333 14.5686 16.1333C16.4046 16.1333 18.062 17.125 18.912 18.7167H18.9035Z"
                                            fill="#474B4E" />
                                    </svg>

                                </span>

                                <span class="dash-mtext">{{ __('Student Reports') }}</span>
                            </a>

                            <ul id="student_reports" class="dash-submenu">

                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'registrationdetailreport' ? 'active' : '' }}"
                                            href="{{ route('registrationdetailreport') }}">{{ __('Registration Report') }}</a>
                                    </li>
                                @endcan
                                     @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'student-profile-report' ? 'active' : '' }}"
                                            href="{{ route('student_profile_report') }}">{{ __('Student Profile Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">

                                        <a class="dash-link {{ Request::segment(1) == 'admissionlisting' ? 'active' : '' }}"
                                            href="{{ route('admissionlisting') }}">{{ __('Admission Listing') }}</a>
                                    </li>
                                @endcan
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'monthlyperchallanreport' || Request::routeIs('prechallan.index') ? 'active' : '' }}"
                                        href="{{ route('monthlyprechallanreport') }}">{{ __('Monthly Pre-Challan') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'monthlychallanreport' ? 'active' : '' }}"
                                        href="{{ route('monthlychallanreport') }}">{{ __('Regular Challan Report') }}</a>
                                </li>
                                {{-- comparison regular vs pre challan --}}
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::routeIs('prechallan.comparison') ? 'active' : '' }}"
                                        href="{{ route('prechallan.comparison') }}">{{ __('Comparison Regular vs Pre challans') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'advancechallanreport' ? 'active' : '' }}"
                                        href="{{ route('advancechallanreport') }}">{{ __('Advance Challan Report') }}</a>
                                </li>
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'studentSecurityReport' ? 'active' : '' }}"
                                            href="{{ route('student_security_report') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Student Security Deposit Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan


                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'student-single-account' ? 'active' : '' }}"
                                            href="{{ route('student_single_account') }}">
                                            <div style="margin-top: -5px;">{{ __('Student Account Statement') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'fee-receipt-summary' ? 'active' : '' }}"
                                            href="{{ route('fee_receipt_summary') }}">{{ __('Fee Receipt Summary') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">

                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'student-fee-receipt-detail' ? 'active' : '' }}"
                                            href="{{ route('student_fee_receipt_detail') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Student Fee Receipt Detail') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                               <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'feestructurelisting' ? 'active' : '' }}"
                                        href="{{ route('feestructurelisting') }}">{{ __('Fee Structure Listing') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'fee-revision-report' ? 'active' : '' }}"
                                        href="{{ route('fee_revision_report') }}">{{ __('Fee Revision Report') }}</a>
                                </li>
								<li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'student-fee-detail' ? 'active' : '' }}"
                                        href="{{ route('student_fee_detail') }}">{{ __('Student Fee Detail') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'student-promotion-report' ? 'active' : '' }}"
                                        href="{{ route('student_promotion_report') }}">{{ __('Promotion Report') }}</a>
                                </li>
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'track-registration' ? 'active' : '' }}"
                                        href="{{ route('track_registration_report') }}">{{ __('Track Registration') }}</a>
                                </li>
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'student-defaulter' ? 'active' : '' }}"
                                            href="{{ route('student_defaulter') }}">{{ __('Student Defualter Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'studenttransferin' ? 'active' : '' }}"
                                            href="{{ route('transferin.index') }}">{{ __('Transfer In Report(STD)') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'studenttransferout' ? 'active' : '' }}"
                                            href="{{ route('transferout.index') }}">{{ __('Transfer Out Report(STD)') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'classwisefeereport' ? 'active' : '' }}"
                                            href="{{ route('classwisefeereport.index') }}">
                                            <div style="margin-top: -5px;">{{ __('Classwise Fee Structure Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'admissionwithdrawalreport' ? 'active' : '' }}"
                                            href="{{ route('admissionwithdrawal.index') }}">{{ __('Adm & W/D Register') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'student-withdarawl-listing' ? 'active' : '' }}"
                                            href="{{ route('student_withdarawl_listing') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Student Withdarawl Listing') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
@can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'student_sections_statistics' ? 'active' : '' }}"
                                            href="{{ route('student_sections_statistics') }}">{{ __('Student Statistics') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'sessionWise' ? 'active' : '' }}"
                                            href="{{ route('sessionWisereport') }}">{{ __('Statistics Session Wise') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'sessionBranchWise' ? 'active' : '' }}"
                                            href="{{ route('sessionBranchWise') }}">{{ __('Statistics Branch Wise') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'monthlystatistics' ? 'active' : '' }}"
                                            href="{{ route('monthlystatistics') }}">{{ __('Statistics Month Wise') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'student-wise-statistic-report' ? 'active' : '' }}"
                                            style="display: flex;"
                                            href="{{ route('student_wise_statistic_report') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Student Wise Statistics Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'period-wise-statistic-report' ? 'active' : '' }}"
                                            href="{{ route('period_wise_statistic_report') }}">
                                            <div style="margin-top: -5px;">{{ __('Period Wise Statistic Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'sessionMonthBranchWise' ? 'active' : '' }}"
                                            href="{{ route('sessionMonthBranchWise') }}">
                                            <div style="margin-top: -5px;">{{ __('Branch Session Wise Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'tuition_fee' ? 'active' : '' }}"
                                            href="{{ route('tuition_feereport') }}">{{ __('Tuition Fee Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'studentstrengthreport' ? 'active' : '' }}"
                                            href="{{ route('studentstrength.index') }}">{{ __('Student Strength') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a style="display: flex;"
                                            class="dash-link {{ Request::segment(1) == 'profession-wise-listing' ? 'active' : '' }}"
                                            href="{{ route('profession_wise_listing') }}">
                                            <div style="margin-top: -5px;">{{ __('Profession wise Listing Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'sibling-students' ? 'active' : '' }}"
                                            href="{{ route('sibling_students') }}">{{ __('Sibling student report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'staff-child' ? 'active' : '' }}"
                                            href="{{ route('staff_child') }}">{{ __('Staff Child List Report') }}</a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'student-data-analysis' ? 'active' : '' }}"
                                            style="display: flex;" href="{{ route('student_data_analysis') }}">
                                            <div style="margin-top: -5px;">
                                                {{ __('Monthly REG-ADM-WD Analysis Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
                                @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'studypack-student' ? 'active' : '' }}"
                                            style="display: flex;" href="{{ route('studypackStudent') }}">
                                            <div style="margin-top: -5px;">{{ __('Student StudyPack Report') }}
                                            </div>
                                        </a>
                                    </li>
                                @endcan
								 @can('view spacetype')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::segment(1) == 'student-sts-report' ? 'active' : '' }}"
                                            href="{{ route('student_sts_report') }}">{{ __('STS Report (Adm/WD/PO)') }}</a>
                                    </li>
                                @endcan


                            </ul>
                        </li>
                    @endif

                    <!--------------------- End Student Reports ----------------------------------->

                    <!--------------------- Start workspace ----------------------------------->

                    {{-- @if (Gate::check('view space') || Gate::check('view spacetype') || Gate::check('manage ismail') || Gate::check('manage vistor'))
                            <li class="dash-item dash-hasmenu">
                                <a href="#workspace"
                                    class="dash-link {{ Request::segment(1) == 'spacetype' || Request::segment(1) == 'space' || Request::segment(1) == 'account-assets' || Request::segment(1) == 'isvisitor' || Request::segment(1) == 'ismail' ? ' active dash-trigger' : '' }}"><span
                    class="dash-micon">

                    <svg fill="#ffffff" height="64px" width="64px" version="1.1" id="Capa_1"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="-38 -48 168.00 168.00" xml:space="preserve" stroke="#ffffff"
                        stroke-width="0.0006000000000000001">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g>
                                <g>
                                    <path
                                        d="M59,38h-4v-2h-4v2h-8v-4h9c0.6,0,1-0.4,1-1V15c0-0.6-0.4-1-1-1H26c-0.6,0-1,0.4-1,1v18c0,0.6,0.4,1,1,1h9v4H21v-3 c0-0.6-0.4-1-1-1h-1v-2h1c0.6,0,1-0.4,1-1v-4c0-0.6-0.4-1-1-1H7c-0.6,0-1,0.4-1,1v3H5c-0.6,0-1,0.4-1,1v4c0,0.6,0.4,1,1,1h1v2H1 c-0.6,0-1,0.4-1,1v5v16h2V45h2v15h2V45h48v15h2V45h2v15h2V44v-5C60,38.4,59.6,38,59,38z M51,16v11H27V16H51z M27,29h24v3h-9h-6h-9 V29z M37,34h4v4h-4V34z M8,28h11v2h-1H8V28z M6,32h1h10v2H7H6V32z M8,36h10h1v2H8V36z M55,43H5H2v-3h5h13h16h6h16v3H55z">
                                    </path>
                                    <path
                                        d="M6,18h14c0.6,0,1-0.4,1-1v-6c0-2.2-1.8-4-4-4h-3V0h-2v7H9c-2.2,0-4,1.8-4,4v6C5,17.6,5.4,18,6,18z M9,9h8c1.1,0,2,0.9,2,2 v5H7v-2h5v-2H7v-1C7,9.9,7.9,9,9,9z">
                                    </path>
                                </g>
                            </g>
                        </g>
                    </svg>
                </span>

                <span class="dash-mtext">{{ __('Workspace') }}</span>
                </a>

                <ul id="workspace" class="dash-submenu">

                    @can('view spacetype')
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::segment(1) == 'spacetype' ? 'active' : '' }}"
                            href="{{ route('spacetype.index') }}">{{ __('Spacetype') }}</a>
                    </li>
                    @endcan
                    @can('view space')
                    <li class="dash-item">
                        <a class="dash-link  {{ Request::segment(1) == 'space' ? 'active' : '' }}"
                            href="{{ route('space.index') }}">{{ __('Space') }}</a>
                    </li>
                    @endcan
                    @can('view chair')
                    <li class="dash-item {{ (Request::segment(1) == 'chair')?'active':''}}">
                        <a class="dash-link" href="{{ route('chair.index') }}">{{__('Chair')}}</a>
                    </li>
                    @endcan
                    @can('manage assets')
                    <li class="dash-item">
                        <a class="dash-link  {{ request()->is('account-assets*') ? 'active' : '' }}"
                            href="{{ route('account-assets.index') }}">{{ __('Asset Setup ') }}</a>
                    </li>
                    @endcan
                    @can('manage vistor')
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::segment(1) == 'isvisitor' ? ' active' : '' }}"
                            href="{{ route('isvisitor.index') }}">{{ __('Vistor') }}</a>
                    </li>
                    @endcan
                    @can('manage ismail')
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::segment(1) == 'ismail' ? ' active' : '' }}"
                            href="{{ route('ismail.index') }}">{{ __('Mail') }}</a>
                    </li>
                    @endcan

                </ul>
                </li>
                @endif --}}

                    <!--------------------- End Workspace ----------------------------------->

                    <!--------------------- Start CRM ----------------------------------->

                    {{-- @if (\Auth::user()->show_crm() == 1)
                            @if (Gate::check('manage lead') || Gate::check('manage deal') || Gate::check('manage form builder') || Gate::check('manage contract'))
                                <li class="dash-item dash-hasmenu ">
                                        <a href="#crm"
                                            class="dash-link {{ Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'pipelines' || Request::segment(1) == 'deals' || Request::segment(1) == 'leads' || Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response' || Request::segment(1) == 'contract' ? ' active dash-trigger' : '' }}"><span
                    class="dash-micon"><svg fill="#ffffff" xmlns="http://www.w3.org/2000/svg" width="57px" height="57px"
                        viewBox="-44.47 -44.47 200.38 200.38" enable-background="new 0 0 100 100" xml:space="preserve">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <ellipse cx="41.3" cy="42.3" rx="12.2" ry="13.5"></ellipse>
                            <path
                                d="M52.6,57.4c-3.1,2.8-7,4.5-11.3,4.5c-4.3,0-8.3-1.7-11.3-4.6c-5.5,2.5-11,5.7-11,10.7v2.1 c0,2.5,2,4.5,4.5,4.5h35.7c2.5,0,4.5-2,4.5-4.5v-2.1C63.6,63,58.2,59.9,52.6,57.4z">
                            </path>
                            <path
                                d="M68,47.4c-0.2-0.1-0.3-0.2-0.5-0.3c-0.4-0.2-0.9-0.2-1.3,0.1c-2.1,1.3-4.6,2.1-7.2,2.1c-0.3,0-0.7,0-1,0 c-0.5,1.3-1,2.6-1.7,3.7c0.4,0.2,0.9,0.3,1.4,0.6c5.7,2.5,9.7,5.6,12.5,9.8H75c2.2,0,4-1.8,4-4v-1.9C79,52.6,73.3,49.6,68,47.4z">
                            </path>
                            <path
                                d="M66.9,34.2c0-4.9-3.6-8.9-7.9-8.9c-2.2,0-4.1,1-5.6,2.5c3.5,3.6,5.7,8.7,5.7,14.4c0,0.3,0,0.5,0,0.8 C63.4,43,66.9,39.1,66.9,34.2z">
                            </path>
                        </g>
                    </svg></span><span class="label">{{ __('CRM System') }}</span>
                </a>
                <ul id="crm"
                    class="dash-submenu {{ Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'leads' || Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response' || Request::segment(1) == 'deals' || Request::segment(1) == 'pipelines' ? 'show' : '' }}">
                    @can('manage lead')
                    <li class="dash-item">
                        <a class="dash-link {{ Request::route()->getName() == 'leads.list' || Request::route()->getName() == 'leads.index' || Request::route()->getName() == 'leads.show' ? ' active' : '' }}"
                            href="{{ route('leads.index') }}">{{ __('Leads') }}</a>
                    </li>
                    @endcan
                    @can('manage deal')
                    <li class="dash-item">
                        <a class="dash-link {{ Request::route()->getName() == 'deals.list' || Request::route()->getName() == 'deals.index' || Request::route()->getName() == 'deals.show' ? ' active' : '' }}"
                            href="{{ route('deals.index') }}">{{ __('Deals') }}</a>
                    </li>
                    @endcan
                    @can('manage form builder')
                    <li class="dash-item">
                        <a class="dash-link  {{ Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response' ? 'active open' : '' }}"
                            href="{{ route('form_builder.index') }}">{{ __('Form Builder') }}</a>
                    </li>
                    @endcan
                    @can('manage contract')
                    <li class="dash-item ">
                        <a class="dash-link  {{ Request::route()->getName() == 'contract.index' || Request::route()->getName() == 'contract.show' ? 'active' : '' }}"
                            href="{{ route('contract.index') }}">{{ __('Contract') }}</a>
                    </li>
                    @endcan
                    @if (Gate::check('manage lead stage') || Gate::check('manage pipeline') || Gate::check('manage source') || Gate::check('manage label') || Gate::check('manage stage'))
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'pipelines' || Request::segment(1) == 'product-category' || Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' || Request::segment(1) == 'chart-of-account-type' ? 'active dash-trigger' : '' }}"
                            href="{{ route('pipelines.index') }}   ">{{ __('CRM System Setup') }}</a>

                    </li>
                    @endif
                </ul>
                </li>
                @endif
                @endif --}}

                    <!--------------------- End CRM ----------------------------------->


                    <!--------------------- Start Project ----------------------------------->

                    {{-- @if (\Auth::user()->show_project() == 1)
                            @if (Gate::check('manage project'))
                                <li class="dash-item dash-hasmenu ">
                                    <a href="#project"
                                        class="dash-link  {{ Request::segment(1) == 'project' ||
                                        Request::segment(1) == 'bugs-report' ||
                                        Request::segment(1) == 'bugstatus' ||
                                        Request::segment(1) == 'project-task-stages' ||
                                        Request::segment(1) == 'calendar' ||
                                        Request::segment(1) == 'timesheet-list' ||
                                        Request::segment(1) == 'taskboard' ||
                                        Request::segment(1) == 'timesheet-list' ||
                                        Request::segment(1) == 'taskboard' ||
                                        Request::segment(1) == 'project' ||
                                        Request::segment(1) == 'projects' ||
                                        Request::segment(1) == 'time-tracker' ||
                                        Request::segment(1) == 'project_report'
                                            ? 'active dash-trigger'
                                            : '' }}"><span class="dash-micon"><svg width="64px" height="64px"
                        viewBox="-370 -440 1500.00 1500.00" version="1.1" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" fill="#ffffff">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                        </g>
                        <g id="SVGRepo_iconCarrier">
                            <title>project-configuration</title>
                            <g id="Page-1" stroke-width="0.00512" fill="none" fill-rule="evenodd">
                                <g id="icon" fill="#ffffff" transform="translate(42.666667, 42.666667)">
                                    <path
                                        d="M277.333333,234.666667 L277.333,255.999667 L298.666667,256 L298.666667,298.666667 L277.333,298.666667 L277.333333,426.666667 L256,426.666667 L256,298.666667 L234.666667,298.666667 L234.666667,256 L256,255.999667 L256,234.666667 L277.333333,234.666667 Z M341.333333,234.666667 L341.333,341.332667 L362.666667,341.333333 L362.666667,384 L341.333,383.999667 L341.333333,426.666667 L320,426.666667 L320,383.999667 L298.666667,384 L298.666667,341.333333 L320,341.332667 L320,234.666667 L341.333333,234.666667 Z M405.333333,234.666667 L405.333,277.332667 L426.666667,277.333333 L426.666667,320 L405.333,319.999667 L405.333333,426.666667 L384,426.666667 L384,319.999667 L362.666667,320 L362.666667,277.333333 L384,277.332667 L384,234.666667 L405.333333,234.666667 Z M170.666667,7.10542736e-15 L341.333333,96 L341.333,213.333 L298.666,213.333 L298.666,138.747 L192,200.331 L192,323.018 L213.333,311.018 L213.333333,320 L234.666667,320 L234.666,348 L170.666667,384 L3.55271368e-14,288 L3.55271368e-14,96 L170.666667,7.10542736e-15 Z M42.666,139.913 L42.6666667,263.04 L149.333,323.022 L149.333,201.497 L42.666,139.913 Z M170.666667,48.96 L69.246,105.991 L169.656,163.963 L271.048,105.424 L170.666667,48.96 Z"
                                        id="Combined-Shape"> </path>
                                </g>
                            </g>
                        </g>
                    </svg></span><span class="label">{{ __('Project System') }}</span></a>
                <ul id="project" class="dash-submenu">
                    @can('manage project')
                    <li class="dash-item ">
                        <a class="dash-link {{ Request::segment(1) == 'project' || Request::route()->getName() == 'projects.list' || Request::route()->getName() == 'projects.list' || Request::route()->getName() == 'projects.index' || Request::route()->getName() == 'projects.show' || request()->is('projects/*') ? 'active' : '' }} "
                            href="{{ route('projects.index') }}">{{ __('Projects') }}</a>
                    </li>
                    @endcan
                    @can('manage project task')
                    <li class="dash-item ">
                        <a class="dash-link {{ request()->is('taskboard*') ? 'active' : '' }}"
                            href="{{ route('taskBoard.view', 'list') }}">{{ __('Tasks') }}</a>
                    </li>
                    @endcan
                    @can('manage timesheet')
                    <li class="dash-item">
                        <a class="dash-link  {{ request()->is('timesheet-list*') ? 'active' : '' }}"
                            href="{{ route('timesheet.list') }}">{{ __('Timesheet') }}</a>
                    </li>
                    @endcan
                    @can('manage bug report')
                    <li class="dash-item ">
                        <a class="dash-link {{ request()->is('bugs-report*') ? 'active' : '' }}"
                            href="{{ route('bugs.view', 'list') }}">{{ __('Bug') }}</a>
                    </li>
                    @endcan
                    @can('manage project task')
                    <li class="dash-item ">
                        <a class="dash-link {{ request()->is('calendar*') ? 'active' : '' }}"
                            href="{{ route('task.calendar', ['all']) }}">{{ __('Task Calendar') }}</a>
                    </li>
                    @endcan

                    @if (\Auth::user()->type != 'super admin')
                    <li class="dash-item ">
                        <a class="dash-link  {{ Request::segment(1) == 'time-tracker' ? 'active open' : '' }}"
                            href="{{ route('time.tracker') }}">{{ __('Tracker') }}</a>
                    </li>
                    @endif
                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'Employee')
                    <li class="dash-item  ">
                        <a class="dash-link {{ Request::route()->getName() == 'project_report.index' || Request::route()->getName() == 'project_report.show' ? 'active' : '' }}"
                            href="{{ route('project_report.index') }}">{{ __('Project Report') }}</a>
                    </li>
                    @endif

                    @if (Gate::check('manage project task stage') || Gate::check('manage bug status'))
                    <li class="dash-item dash-hasmenu ">
                        <a class="dash-link {{ Request::segment(1) == 'bugstatus' || Request::segment(1) == 'project-task-stages' ? 'active dash-trigger' : '' }}"
                            href="#system">{{ __('Project System Setup') }}</a>
                        <ul id="system" class="dash-submenu">
                            @can('manage project task stage')
                            <li class="dash-item  ">
                                <a class="dash-link {{ Request::route()->getName() == 'project-task-stages.index' ? 'active' : '' }}"
                                    href="{{ route('project-task-stages.index') }}">{{ __('Project Task Stages') }}</a>
                            </li>
                            @endcan
                            @can('manage bug status')
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::route()->getName() == 'bugstatus.index' ? 'active' : '' }}"
                                    href="{{ route('bugstatus.index') }}">{{ __('Bug Status') }}</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif
                </ul>
                </li>
                @endif
                @endif --}}

                    <!--------------------- End Project ----------------------------------->

                    {{-- @if (Gate::check('manage project task'))
                            <li class="dash-item dash-hasmenu ">
                                <a href="{{ route('booking.calendar', ['all']) }}"
                class="dash-link {{ Request::segment(1) == 'bookingcalendar' ? ' active' : '' }}">
                <span class="dash-micon">

                    <svg width="50px" height="50px" viewBox="-921.6 -921.6 2867.20 2867.20" class="icon" version="1.1"
                        xmlns="http://www.w3.org/2000/svg" fill="#ffffff" stroke="#ffffff">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M716 190.9v-67.8h-44v67.8H352v-67.8h-44v67.8H92v710h840v-710H716z m-580 44h172v69.2h44v-69.2h320v69.2h44v-69.2h172v151.3H136V234.9z m752 622H136V402.2h752v454.7z"
                                fill="#ffffff"></path>
                            <path d="M319 565.7m-33 0a33 33 0 1 0 66 0 33 33 0 1 0-66 0Z" fill="#fffffffffff"></path>
                            <path d="M510 565.7m-33 0a33 33 0 1 0 66 0 33 33 0 1 0-66 0Z" fill="#fffffffffff"></path>
                            <path d="M701.1 565.7m-33 0a33 33 0 1 0 66 0 33 33 0 1 0-66 0Z" fill="#fffffffffff"></path>
                            <path d="M319 693.4m-33 0a33 33 0 1 0 66 0 33 33 0 1 0-66 0Z" fill="#fffffffffff"></path>
                            <path d="M510 693.4m-33 0a33 33 0 1 0 66 0 33 33 0 1 0-66 0Z" fill="#fffffffffff"></path>
                            <path d="M701.1 693.4m-33 0a33 33 0 1 0 66 0 33 33 0 1 0-66 0Z" fill="#fffffffffff"></path>
                        </g>
                    </svg>
                </span><span class="label">{{ __('Booking Calender') }}</span>
                </a>
                </li>
                @endif
                @if (\Auth::user()->type == 'clientuser')
                <li class="dash-item dash-hasmenu ">
                    <a href="{{ route('booking.calendar', ['all']) }}"
                        class="dash-link {{ Request::segment(1) == 'bookingcalendar' ? ' active' : '' }}">
                        <span class="dash-micon"><svg width="50px" height="50px" viewBox="-20.64 -20.64 65.28 65.28"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path
                                        d="M3 9H21M7 3V5M17 3V5M6 13H8M6 17H8M11 13H13M11 17H13M16 13H18M16 17H18M6.2 21H17.8C18.9201 21 19.4802 21 19.908 20.782C20.2843 20.5903 20.5903 20.2843 20.782 19.908C21 19.4802 21 18.9201 21 17.8V8.2C21 7.07989 21 6.51984 20.782 6.09202C20.5903 5.71569 20.2843 5.40973 19.908 5.21799C19.4802 5 18.9201 5 17.8 5H6.2C5.0799 5 4.51984 5 4.09202 5.21799C3.71569 5.40973 3.40973 5.71569 3.21799 6.09202C3 6.51984 3 7.07989 3 8.2V17.8C3 18.9201 3 19.4802 3.21799 19.908C3.40973 20.2843 3.71569 20.5903 4.09202 20.782C4.51984 21 5.07989 21 6.2 21Z"
                                        stroke="#ffffff" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </g>
                            </svg></span><span class="label">{{ __('Booking Calender') }}</span>
                    </a>
                </li>
                @endif --}}

                    @if (
                        \Auth::user()->type != 'super admin' &&
                            (Gate::check('manage user') ||
                                Gate::check('manage role') ||
                                Gate::check('manage client') ||
                                Gate::check('view companybranch')))
                        <li class="dash-item dash-hasmenu ">

                            <a href="#user"
                                class="dash-link {{ Request::segment(1) == 'users' ||
                                Request::segment(1) == 'roles' ||
                                Request::segment(1) == 'clients' ||
                                Request::segment(1) == 'clientuser' ||
                                Request::segment(1) == 'userlogs'
                                    ? ' active dash-trigger'
                                    : '' }}"><span
                                    class="dash-micon"><svg width="21" height="21" viewBox="0 0 21 21"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M17.2967 12.7788C17.4277 12.5496 17.6399 12.3837 17.8901 12.3046C18.1402 12.2335 18.4094 12.2572 18.6426 12.3757C18.8759 12.4943 19.0556 12.6919 19.1451 12.9369C19.2347 13.1819 19.2272 13.4506 19.1243 13.6955L19.0711 13.7983L18.7278 14.391C18.9326 14.636 19.0991 14.9047 19.2216 15.1971L19.2893 15.3709H19.9767C20.2378 15.3709 20.4889 15.4657 20.6788 15.6475C20.8686 15.8293 20.9829 16.0743 20.9982 16.3351C21.0135 16.5958 20.9287 16.8488 20.7611 17.0542C20.5935 17.2518 20.3558 17.3782 20.0965 17.4098L19.9767 17.4177H19.2893C19.189 17.7022 19.0474 17.9709 18.8692 18.2159L18.7278 18.3898L19.0711 18.9825C19.2028 19.2117 19.2428 19.4804 19.1829 19.7333C19.123 19.9941 18.9676 20.2153 18.7489 20.3576C18.5301 20.4998 18.2645 20.5552 18.0066 20.5078C17.7487 20.4603 17.5182 20.3181 17.3623 20.1126L17.2967 20.0099L16.9535 19.4172C16.6534 19.4725 16.3491 19.4804 16.052 19.4488L15.8297 19.4172L15.4855 20.0099C15.3545 20.2391 15.1423 20.405 14.8921 20.4761C14.642 20.5551 14.3728 20.5315 14.1396 20.4129C13.9063 20.2944 13.7266 20.0889 13.6371 19.844C13.5476 19.599 13.555 19.3302 13.6579 19.0931L13.7111 18.9825L14.0544 18.3898C13.8498 18.1527 13.6832 17.884 13.5606 17.5916L13.4929 17.4177H12.8056C12.5445 17.4177 12.2933 17.315 12.1034 17.1412C11.9136 16.9594 11.7993 16.7144 11.784 16.4536C11.7687 16.1928 11.8535 15.932 12.0211 15.7344C12.1887 15.5369 12.4264 15.4105 12.6857 15.3788L12.8056 15.3709H13.494C13.5943 15.0864 13.7358 14.8177 13.914 14.5727L14.0544 14.3989L13.7111 13.7983C13.5794 13.577 13.5394 13.3082 13.5993 13.0475C13.6593 12.7946 13.8146 12.5734 14.0333 12.4311C14.2521 12.2889 14.5177 12.2335 14.7757 12.273C15.0335 12.3204 15.264 12.4627 15.42 12.676L15.4855 12.7788L15.8287 13.3715C16.1289 13.3162 16.4331 13.3082 16.7302 13.3399L16.9525 13.3715L17.2967 12.7788ZM9.21997 11.2693C9.67826 11.2693 10.1276 11.2931 10.5682 11.3405C10.8384 11.3721 11.0861 11.5065 11.2569 11.7198C11.4277 11.9253 11.5075 12.2019 11.4789 12.4706C11.4502 12.7393 11.3154 12.9842 11.1041 13.1581C10.8928 13.332 10.6222 13.411 10.352 13.3794C9.98252 13.3399 9.60516 13.3162 9.21997 13.3162C7.14754 13.3162 5.28303 13.9247 3.95844 14.7308C3.29563 15.1338 2.7957 15.5764 2.47303 15.9874C2.14111 16.4062 2.04888 16.7223 2.04888 16.904C2.04888 17.0305 2.08682 17.1649 2.31007 17.3387C2.56517 17.5442 3.00876 17.7576 3.68698 17.9393C5.03718 18.295 6.97747 18.4451 9.21997 18.4451L9.89408 18.4372C10.1658 18.4293 10.4278 18.54 10.6225 18.7296C10.8172 18.9193 10.9287 19.1722 10.9324 19.4488C10.936 19.7175 10.8316 19.9782 10.6421 20.1758C10.4526 20.3734 10.1934 20.484 9.92174 20.484L9.21997 20.4919C6.93654 20.4919 4.77899 20.3497 3.16247 19.923C2.35828 19.7096 1.60119 19.4014 1.02752 18.943C0.420036 18.453 0 17.7733 0 16.904C0 16.0979 0.36677 15.3473 0.864648 14.715C1.37066 14.0749 2.07038 13.4821 2.89101 12.9843C4.53313 11.9806 6.76647 11.2693 9.21997 11.2693ZM16.3911 15.3709C16.1194 15.3709 15.8588 15.4737 15.6667 15.6712C15.4746 15.8609 15.3667 16.1217 15.3667 16.3904C15.3667 16.667 15.4746 16.9278 15.6667 17.1174C15.8588 17.3071 16.1194 17.4177 16.3911 17.4177C16.6628 17.4177 16.9234 17.3071 17.1155 17.1174C17.3076 16.9278 17.4156 16.667 17.4156 16.3904C17.4156 16.1217 17.3076 15.8609 17.1155 15.6712C16.9234 15.4737 16.6628 15.3709 16.3911 15.3709ZM9.21997 0C10.5785 0 11.8813 0.545217 12.842 1.50146C13.8026 2.4656 14.3422 3.76963 14.3422 5.12101C14.3422 6.48029 13.8026 7.78423 12.842 8.74837C11.8813 9.70461 10.5785 10.2499 9.21997 10.2499C7.86148 10.2499 6.55862 9.70461 5.59804 8.74837C4.63745 7.78423 4.09777 6.48029 4.09777 5.12101C4.09777 3.76963 4.63745 2.4656 5.59804 1.50146C6.55862 0.545217 7.86148 0 9.21997 0ZM9.21997 2.05471C8.81638 2.05471 8.41673 2.13367 8.04388 2.28383C7.67102 2.44188 7.33223 2.66323 7.04678 2.94773C6.76141 3.24014 6.53507 3.57207 6.38057 3.95141C6.22615 4.32284 6.14665 4.71796 6.14665 5.12101C6.14665 5.52405 6.22615 5.92709 6.38057 6.29852C6.53507 6.66995 6.76141 7.00978 7.04678 7.29428C7.33223 7.58669 7.67102 7.80795 8.04388 7.966C8.41673 8.11615 8.81638 8.19521 9.21997 8.19521C10.0351 8.19521 10.8168 7.87119 11.3932 7.29428C11.9695 6.71738 12.2933 5.9429 12.2933 5.12101C12.2933 4.30702 11.9695 3.52464 11.3932 2.94773C10.8168 2.37083 10.0351 2.05471 9.21997 2.05471Z"
                                            fill="#474B4E" />
                                    </svg>
                                </span><span class="dash-mtext">{{ __('User Management') }}</span></a>
                            <ul id="user" class="dash-submenu">
                                @can('manage user')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit' || Request::route()->getName() == 'user.userlog' ? ' active' : '' }}"
                                            href="{{ route('users.index') }}">{{ __('User') }}</a>
                                    </li>
                                @endcan
                                @can('manage role')
                                    <li class="dash-item  ">
                                        <a class="dash-link {{ Request::route()->getName() == 'roles.index' || Request::route()->getName() == 'roles.create' || Request::route()->getName() == 'roles.edit' ? ' active' : '' }}"
                                            href="{{ route('roles.index') }}">{{ __('Role') }}</a>
                                    </li>
                                @endcan
                                {{-- @can('view companybranch')
                                    <li class="dash-item ">
                                        <a class="dash-link {{ Request::route()->getName() == 'branches.index' || Request::segment(1) == 'branches' || Request::route()->getName() == 'branches.edit' ? ' active' : '' }}"
                                            href="{{ route('branches.index') }}">{{ __('School') }}</a>
                                    </li>
                                @endcan --}}
                                {{-- @can('manage client')
                                        <li
                                            class="dash-item {{ Request::route()->getName() == 'clients.index' || Request::segment(1) == 'clients' || Request::route()->getName() == 'clients.edit' ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('clients.index') }}">{{ __('Client') }}</a>
                </li>
                @endcan --}}
                                {{-- @can('manage clientuser')
                <li class="dash-item ">
                    <a class="dash-link {{ Request::route()->getName() == 'clientuser.index' || Request::segment(1) == 'clientuser' || Request::route()->getName() == 'clientuser.edit' ? ' active' : '' }}"
                        href="{{ route('clientuser.index') }}">{{ __('Clientuser') }}</a>
                </li>
                @endcan --}}
                                {{--                              @can('manage user') --}}
                                {{--                                 <li class="dash-item {{ (Request::route()->getName() == 'users.index' || Request::segment(1) == 'users' || Request::route()->getName() == 'users.edit') ? ' active' : '' }}">
                --}}
                                {{--                                     <a class="dash-link" href="{{ route('user.userlog') }}">{{__('User Logs')}}</a>
                --}}
                                {{--                                 </li> --}}
                                {{--                             @endcan --}}
                            </ul>
                        </li>
                    @endif


                    @if (Gate::check('manage product & service') || Gate::check('manage product & service'))
                        <li class="dash-item dash-hasmenu ">
                            <a href="#products"
                                class="dash-link {{ Request::segment(1) == 'productservice' || Request::segment(1) == 'productstock' ? ' active dash-trigger' : '' }}">
                                <span class="dash-micon"><svg width="18" height="20" viewBox="0 0 18 20"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M10.9628 0.575582L15.473 3.18163C16.1268 3.55849 16.6697 4.10134 17.0473 4.75533C17.425 5.40856 17.624 6.15011 17.6246 6.90535V12.1144C17.6242 12.8689 17.4252 13.6105 17.0476 14.2645C16.6699 14.9184 16.1269 15.4613 15.473 15.8389L10.9628 18.4435C10.3088 18.8203 9.56722 19.019 8.81228 19.019C8.05733 19.019 7.31564 18.8203 6.66172 18.4435L2.15147 15.8374C1.49778 15.4605 0.954796 14.9177 0.577172 14.2637C0.199547 13.6105 0.000456804 12.8689 0 12.1144V6.90535C0 5.36821 0.820116 3.94907 2.15147 3.18012L6.66172 0.575582C7.31564 0.198719 8.05733 0 8.81228 0C9.56722 0 10.3088 0.198719 10.9628 0.575582ZM9.97583 2.28555C9.62204 2.08151 9.22073 1.97414 8.81228 1.97414C8.40382 1.97414 8.00251 2.08151 7.64864 2.28555L3.13839 4.89085C2.7849 5.09489 2.49126 5.38799 2.28684 5.74202C2.08242 6.09528 1.97453 6.49574 1.97385 6.90382V12.1152C1.97385 12.9451 2.41794 13.714 3.13839 14.1282L7.64864 16.7343C8.3691 17.1484 9.25538 17.1484 9.97583 16.7343L14.4861 14.1282C14.8396 13.9241 15.1333 13.631 15.3377 13.2778C15.5421 12.9245 15.65 12.5233 15.6507 12.1152V6.90382C15.6507 6.07396 15.2065 5.30502 14.4861 4.89085L9.97583 2.28555Z"
                                            fill="#474B4E" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M7.33014 9.3096L1.60297 6.44544L2.48528 4.68066L8.21246 7.54405C8.58948 7.73286 9.03356 7.73286 9.41058 7.54405L15.1378 4.68066L16.0211 6.44544L10.2939 9.3096C9.83373 9.53952 9.32637 9.65906 8.81201 9.65906C8.29765 9.65906 7.7903 9.53952 7.33014 9.3096Z"
                                            fill="#474B4E" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M9.7984 8.94275V17.9661H7.82455V8.94275H9.7984Z" fill="#474B4E" />
                                    </svg>
                                </span><span class="dash-mtext">{{ __('Products System') }}</span>
                            </a>
                            <ul id="products" class="dash-submenu">
                                @if (Gate::check('manage product & service'))
                                    <li class="dash-item ">
                                        <a href="{{ route('productservice.index') }}"
                                            class="dash-link {{ Request::segment(1) == 'productservice' ? 'active' : '' }}">{{ __('Product & Services') }}
                                        </a>
                                        {{-- <a href="{{ route('productservice.index') }}"
                                            class="label {{ Request::segment(1) == 'productservice' ? 'active' : '' }}">{{ __('Product & Services') }}
                                        </a> --}}
                                    </li>
                                @endif
                                @if (Gate::check('manage product & service'))
                                    <li class="dash-item ">
                                        <a href="{{ route('productstock.index') }}"
                                            class="dash-link {{ Request::segment(1) == 'productstock' ? 'active' : '' }}">{{ __('Product Stock') }}
                                        </a>
                                        {{-- <a href="{{ route('productstock.index') }}"
                                            class="label {{ Request::segment(1) == 'productstock' ? 'active' : '' }}">{{ __('Product Stock') }}
                                        </a> --}}
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif


                    {{-- @if (\Auth::user()->type != 'company')
                            @if (Gate::check('manage warehouse') || Gate::check('manage purchase') || Gate::check('manage pos') || Gate::check('manage print settings'))}} --}}
                    <li class="dash-item dash-hasmenu ">
                        <a class="{{ Request::segment(1) == 'store/' || Request::segment(1) == 'store' || Request::segment(1) == 'studypackreceipts' || Request::segment(1) == 'sales-by-customer' || Request::segment(1) == 'vender' || Request::segment(1) == 'purchase-vendor-summry' || Request::segment(1) == 'invoice' || Request::segment(1) == 'purchase-product-vendor-report' || Request::segment(1) == 'returnorder-report' || Request::segment(1) == 'invoice-product-report' || Request::segment(1) == 'purchase' || Request::segment(1) == 'returnorder' || Request::segment(1) == 'invoice-report' || Request::segment(1) == 'purchase-report' || Request::segment(1) == 'purchase-product-report' || Request::route()->getName() == 'pos.barcode' || Request::route()->getName() == 'pos.print'  || Request::segment(1) == 'vendor-advance' || Request::route()->getName() == 'pos.show' ? ' active dash-trigger' : 'dash-link' }}"
                            href="#pos"><span class="dash-micon "><svg width="18" height="18"
                                    viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M4.5 6.75C1.93425 6.75 0 7.87875 0 9.375V15.375C0 16.8713 1.93425 18 4.5 18C7.06575 18 9 16.8713 9 15.375V9.375C9 7.87875 7.06575 6.75 4.5 6.75ZM7.5 12.375C7.5 12.7718 6.36075 13.5 4.5 13.5C2.63925 13.5 1.5 12.7718 1.5 12.375V11.364C2.2845 11.7638 3.32625 12 4.5 12C5.67375 12 6.7155 11.7638 7.5 11.364V12.375ZM4.5 8.25C6.36075 8.25 7.5 8.97825 7.5 9.375C7.5 9.77175 6.36075 10.5 4.5 10.5C2.63925 10.5 1.5 9.77175 1.5 9.375C1.5 8.97825 2.63925 8.25 4.5 8.25ZM4.5 16.5C2.63925 16.5 1.5 15.7718 1.5 15.375V14.364C2.2845 14.7638 3.32625 15 4.5 15C5.67375 15 6.7155 14.7638 7.5 14.364V15.375C7.5 15.7718 6.36075 16.5 4.5 16.5ZM18 3.75V14.25C18 16.3177 16.3177 18 14.25 18H10.5C10.0852 18 9.75 17.664 9.75 17.25C9.75 16.836 10.0852 16.5 10.5 16.5H14.25C15.4905 16.5 16.5 15.4905 16.5 14.25V3.75C16.5 2.5095 15.4905 1.5 14.25 1.5H6.75C5.5095 1.5 4.5 2.5095 4.5 3.75V4.5C4.5 4.914 4.16475 5.25 3.75 5.25C3.33525 5.25 3 4.914 3 4.5V3.75C3 1.68225 4.68225 0 6.75 0H14.25C16.3177 0 18 1.68225 18 3.75ZM9.75 7.5C9.33525 7.5 9 7.164 9 6.75C9 6.336 9.33525 6 9.75 6H13.5V4.5H7.5V4.875C7.5 5.289 7.16475 5.625 6.75 5.625C6.33525 5.625 6 5.289 6 4.875V4.5C6 3.67275 6.67275 3 7.5 3H13.5C14.3273 3 15 3.67275 15 4.5V6C15 6.82725 14.3273 7.5 13.5 7.5H9.75ZM10.5 13.5C10.5 13.086 10.8352 12.75 11.25 12.75H14.25C14.6648 12.75 15 13.086 15 13.5C15 13.914 14.6648 14.25 14.25 14.25H11.25C10.8352 14.25 10.5 13.914 10.5 13.5ZM10.5 10.5V9.75C10.5 9.336 10.8352 9 11.25 9C11.6648 9 12 9.336 12 9.75V10.5C12 10.914 11.6648 11.25 11.25 11.25C10.8352 11.25 10.5 10.914 10.5 10.5ZM15 10.5C15 10.914 14.6648 11.25 14.25 11.25C13.8352 11.25 13.5 10.914 13.5 10.5V9.75C13.5 9.336 13.8352 9 14.25 9C14.6648 9 15 9.336 15 9.75V10.5Z"
                                        fill="#474B4E" />
                                </svg>
                            </span><span class="dash-mtext">{{ __('Store System') }}</span> </a>

                        <ul class="dash-submenu" id="pos">
                            <li class="dash-item">
                                <a class="dash-link {{ Request::segment(1) == 'studypackreceipts' ? ' active' : '' }}"
                                    href="{{ route('studypackreceipts') }}">{{ __('Studypack Receipts') }}</a>
                            </li>
                            @can('manage warehouse')
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'store' || Request::segment(1) == 'store.show' ? ' active' : '' }}"
                                        href="{{ route('store.index') }}">{{ __('Store') }}</a>
                                </li>
                            @endcan
                            @can('manage purchase')
                                <li class="dash-item">
                                    <a class="dash-link  {{ Request::segment(1) == 'purchase' || Request::route()->getName() == 'purchase.index' || Request::route()->getName() == 'purchase.create' || Request::route()->getName() == 'purchase.edit' || Request::route()->getName() == 'purchase.show' ? ' active' : '' }}"
                                        href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a>
                                </li>
                            @endcan
                            @can('manage grn')
								<li class="dash-item">
	                                <a class="dash-link {{ Request::segment(1) == 'grn' || in_array(Request::route()->getName(), ['grn.index', 'grn.create', 'grn.edit', 'grn.show']) ? ' active' : '' }}"
	                                    href="{{ route('grn.index') }}">{{ __('GRN') }}</a>
	                            </li>
                            @endcan
                            @if (Gate::check('manage vender'))
                                <li class="dash-item ">
                                    <a class="dash-link {{ Request::segment(1) == 'vender' ? 'active' : '' }}"
                                        href="{{ route('vender.index') }}">{{ __('Vendor/Supplier') }}</a>
                                </li>
								<li class="dash-item ">
									<a class="dash-link {{ Request::segment(1) == 'vendor-advance' ? 'active' : '' }}"
										href="{{ route('vendor-advance.index') }}">{{ __('Vendor Advance') }}</a>
								</li>
                            @endif
                            
							@can('manage demand order')
								<li class="dash-item">
                                    <a class="dash-link {{ Request::segment(1) == 'demand-order' || in_array(Request::route()->getName(), ['demand-order.index', 'demand-order.create', 'demand-order.edit', 'demand-order.show']) ? ' active' : '' }}"
                                        href="{{ route('demand-order.index') }}">{{ __('Demand Order') }}</a>
                                </li>
							@endcan
                            
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::segment(1) == 'invoice' || Request::route()->getName() == 'invoice.index' || Request::route()->getName() == 'invoice.create' || Request::route()->getName() == 'invoice.edit' || Request::route()->getName() == 'invoice.show' ? ' active' : '' }}"
                                    href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a>
                            </li>
                           @can('manage stock transfer order')
								<li class="dash-item">
                                    <a class="dash-link {{ Request::segment(1) == 'stock-transfer-order' || in_array(Request::route()->getName(), ['stock-transfer-order.index', 'stock-transfer-order.create', 'stock-transfer-order.edit', 'stock-transfer-order.show']) ? ' active' : '' }}"
                                        href="{{ route('stock-transfer-order.index') }}">{{ __('Stock Transfer Requisition') }}</a>
                                </li>
                            @endcan
                            @can('manage stock transfer note')
                                <li class="dash-item ">
                                    <a class="dash-link {{ in_array(Request::route()->getName(), ['stock-transfer-note.index', 'stock-transfer-note.create', 'stock-transfer-note.edit', 'stock-transfer-note.show']) ? ' active' : '' }}"
                                        href="{{ route('stock-transfer-note.index') }}">{{ __('Stock Transfer Note') }}</a>
                                </li>
							@endcan
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::segment(1) == 'invoice-report' ? ' active' : '' }}"
                                    href="{{ route('invoice_report') }}">{{ __('Invoice Balance Report ') }}</a>
                            </li>
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::segment(1) == 'invoice-product-report' ? ' active' : '' }}"
                                    href="{{ route('invoice_product_report') }}">{{ __('Invoice Products Report') }}</a>
                            </li>
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::segment(1) == 'purchase-report' ? ' active' : '' }}"
                                    href="{{ route('purchase.report') }}">{{ __('Purchase Report') }}</a>
                            </li>
                            <li class="dash-item ">
                                <a style="display: flex;"
                                    class="dash-link {{ Request::segment(1) == 'purchase-product-report' ? ' active' : '' }}"
                                    href="{{ route('purchaseproduct.report') }}">
                                    <div style="margin-top: -5px;">
                                        {{ __('Purchase Products Report') }}
                                    </div>
                                </a>
                            </li>
                            <li class="dash-item ">
                                <a style="display: flex;"
                                    class="dash-link {{ Request::segment(1) == 'purchase-product-vendor-report' ? ' active' : '' }}"
                                    href="{{ route('purchaseproductbyvendor.report') }}">
                                    <div style="margin-top: -5px;">
                                        {{ __('Purchase Products By Vendor Report') }}
                                    </div>
                                </a>
                            </li>
                            {{--  --}}
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::segment(1) == 'purchase-vendor-summry' ? ' active' : '' }}"
                                    style="display: flex;" href="{{ route('purchase_vendor_summary') }}">
                                    <div style="margin-top: -5px;">
                                        {{ __('Purchase Vendor Summary Report') }}
                                    </div>
                                </a>
                            </li>

                            <li class="dash-item ">
                                <a class="dash-link {{ Request::segment(1) == 'sales-by-customer' ? ' active' : '' }}"
                                    style="display: flex;" href="{{ route('sales_by_customer') }}">
                                    <div style="margin-top: -5px;">
                                        {{ __('Sales By Customer') }}
                                    </div>
                                </a>
                            </li>
                            {{--  --}}
                            <li class="dash-item ">
                                <a class="dash-link {{ Request::segment(1) == 'returnorder-report' ? ' active' : '' }}"
                                    href="{{ route('returnorder.reports') }}">{{ __('ReturnOrder Report') }}</a>
                            </li>
                            {{-- <li class="dash-item ">
                                <a class="dash-link {{ Request::segment(1) == 'returnorder-prduct-report' ? ' active' : '' }}"
                                    href="{{ route('returnorderproduct.report') }}">{{ __('ReturnOrder Products Report') }}</a>
                            </li> --}}
                            {{-- @can('manage pos')
                    <li class="dash-item {{ Request::route()->getName() == 'pos.index' ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('pos.index') }}">{{ __(' Add POS') }}</a>
                    </li>
                    <li
                        class="dash-item {{ Request::route()->getName() == 'pos.report' || Request::route()->getName() == 'pos.show' ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('pos.report') }}">{{ __('POS') }}</a>
                    </li>
                    @endcan --}}
                            {{-- @can('manage warehouse')
                    <li
                        class="dash-item {{ Request::route()->getName() == 'store-transfer.index' || Request::route()->getName() == 'store-transfer.show' ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('store-transfer.index') }}">{{ __('Transfer') }}</a>
                    </li>
                    @endcan --}}
                            {{-- @can('create barcode')
                    <li
                        class="dash-item {{ Request::route()->getName() == 'pos.barcode' || Request::route()->getName() == 'pos.print' ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('pos.barcode') }}">{{ __('Print Barcode') }}</a>
                    </li>
                    @endcan --}}
                            {{-- @can('manage pos')
                    <li class="dash-item {{ Request::route()->getName() == 'pos-print-setting' ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('pos.print.setting') }}">{{ __('Print Settings') }}</a>
                    </li>
                    @endcan --}}

                        </ul>
                    </li>
                    {{-- @endif
                @endif --}}


                    @if (\Auth::user()->type != 'super admin')
                        {{-- <li class="dash-item dash-hasmenu ">
                                <a href="{{ route('support.index') }}"
                class="dash-link {{ Request::segment(1) == 'support' ? 'active' : '' }}">
                <span class="dash-micon">
                    <svg fill="#ffffff" width="50px" height="50px" viewBox="-21.12 -21.12 66.24 66.24"
                        xmlns="http://www.w3.org/2000/svg" stroke="#ffffff" stroke-width="0.00024000000000000003">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M12 2C6.486 2 2 6.486 2 12v4.143C2 17.167 2.897 18 4 18h1a1 1 0 0 0 1-1v-5.143a1 1 0 0 0-1-1h-.908C4.648 6.987 7.978 4 12 4s7.352 2.987 7.908 6.857H19a1 1 0 0 0-1 1V18c0 1.103-.897 2-2 2h-2v-1h-4v3h6c2.206 0 4-1.794 4-4 1.103 0 2-.833 2-1.857V12c0-5.514-4.486-10-10-10z">
                            </path>
                        </g>
                    </svg>
                </span><span class="label">{{ __('Support System') }}</span>
                </a>
                </li> --}}
                        {{-- <li class="dash-item dash-hasmenu">
                                <a href="{{ route('zoom-meeting.index') }}"
                class="dash-link
                {{ Request::segment(1) == 'zoom-meeting' || Request::segment(1) == 'zoom-meeting-calender' ? 'active' : '' }}">
                <span class="dash-micon">
                    <svg fill="#ffffff" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" width="50px" height="50px"
                        viewBox="-407.15 -407.15 1266.69 1266.69" xml:space="preserve" stroke="#ffffff">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g>
                                <g id="Layer_8_38_">
                                    <path
                                        d="M441.677,43.643H10.687C4.785,43.643,0,48.427,0,54.329v297.425c0,5.898,4.785,10.676,10.687,10.676h162.069v25.631 c0,0.38,0.074,0.722,0.112,1.089h-23.257c-5.407,0-9.796,4.389-9.796,9.795c0,5.408,4.389,9.801,9.796,9.801h158.506 c5.406,0,9.795-4.389,9.795-9.801c0-5.406-4.389-9.795-9.795-9.795h-23.256c0.032-0.355,0.115-0.709,0.115-1.089V362.43H441.7 c5.898,0,10.688-4.782,10.688-10.676V54.329C452.37,48.427,447.589,43.643,441.677,43.643z M422.089,305.133 c0,5.903-4.784,10.687-10.683,10.687H40.96c-5.898,0-10.684-4.783-10.684-10.687V79.615c0-5.898,4.786-10.684,10.684-10.684 h370.446c5.898,0,10.683,4.785,10.683,10.684V305.133z M303.942,290.648H154.025c0-29.872,17.472-55.661,42.753-67.706 c-15.987-10.501-26.546-28.571-26.546-49.13c0-32.449,26.306-58.755,58.755-58.755c32.448,0,58.753,26.307,58.753,58.755 c0,20.553-10.562,38.629-26.545,49.13C286.475,234.987,303.942,260.781,303.942,290.648z">
                                    </path>
                                </g>
                            </g>
                        </g>
                    </svg>
                </span><span class="label">{{ __('Zoom Meeting') }}</span>
                </a>
                </li> --}}
                        {{-- <li class="dash-item dash-hasmenu">
                                <a href="{{ url('chats') }}"
                class="dash-link {{ Request::segment(1) == 'chats' ? 'active' : '' }}">
                <span class="dash-micon">
                    <svg width="50px" height="50px" viewBox="-21.6 -21.6 67.20 67.20" fill="none"
                        xmlns="http://www.w3.org/2000/svg" stroke="#ffffff" stroke-width="0.00024000000000000003">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M12 3.5C7.28986 3.5 3.5 7.1899 3.5 11.7071C3.5 14.0838 4.54603 16.2271 6.2249 17.7289C6.31701 17.8113 6.37534 17.9249 6.38864 18.0478L6.70243 20.9462C6.73215 21.2207 6.53369 21.4674 6.25915 21.4971C5.98462 21.5268 5.73796 21.3284 5.70824 21.0538L5.41479 18.3433C3.62078 16.6708 2.5 14.317 2.5 11.7071C2.5 6.60668 6.76902 2.5 12 2.5C15.0863 2.5 17.7762 3.83689 19.5165 6.06743C19.6775 6.27381 19.6521 6.56945 19.4582 6.74532L14.0482 11.6531C13.8719 11.8131 13.6073 11.8266 13.4156 11.6853L10.3489 9.42482L8.00341 13.3115L10.1084 11.6944C10.2829 11.5603 10.5246 11.5561 10.7037 11.6841L14.0166 14.0515L19.7983 7.67052C19.9076 7.5499 20.0688 7.49003 20.2303 7.51005C20.3919 7.53007 20.5335 7.62747 20.6101 7.77113C21.2947 9.056 21.5 10.2136 21.5 11.7071C21.5 16.8075 17.231 20.9141 12 20.9141C10.9256 20.9141 9.89175 20.7411 8.92713 20.4217C8.66499 20.3349 8.52284 20.052 8.60964 19.7899C8.69644 19.5278 8.97931 19.3856 9.24146 19.4724C10.1057 19.7585 11.0334 19.9141 12 19.9141C16.7101 19.9141 20.5 16.2242 20.5 11.7071C20.5 10.607 20.3821 9.75722 20.0355 8.898L14.4587 15.0529C14.2883 15.241 14.004 15.2715 13.7975 15.124L10.4238 12.7131L6.50993 15.7197C6.31928 15.8662 6.05139 15.8562 5.87214 15.696C5.69289 15.5358 5.65303 15.2707 5.77725 15.0649L9.77609 8.43863C9.84874 8.31823 9.96873 8.234 10.1067 8.20657C10.2446 8.17915 10.3877 8.21106 10.5008 8.29449L13.6795 10.6375L18.4328 6.32538C16.8861 4.55847 14.6156 3.5 12 3.5Z"
                                fill="#ffffff"></path>
                        </g>
                    </svg>
                </span><span class="label">{{ __('Messenger') }}</span>
                </a>
                </li> --}}
                    @endif

                    {{-- @if (\Auth::user()->type == 'company')
                            <li class="dash-item dash-hasmenu ">
                                <a href="{{ route('notification-templates.index') }}"
                class="dash-link {{ Request::segment(1) == 'notification-templates' ? 'active' : '' }}">
                <span class="dash-micon">
                    <svg fill="#ffffff" width="50px" height="50px" viewBox="-21.6 -21.6 67.20 67.20"
                        xmlns="http://www.w3.org/2000/svg" stroke="#ffffff" stroke-width="0.00024000000000000003">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M16 2H8C7.46957 2 6.96086 2.21071 6.58579 2.58579C6.21071 2.96086 6 3.46957 6 4H15V9H20V20H6C6 20.5304 6.21071 21.0391 6.58579 21.4142C6.96086 21.7893 7.46957 22 8 22H20C20.5304 22 21.0391 21.7893 21.4142 21.4142C21.7893 21.0391 22 20.5304 22 20V8L16 2Z">
                            </path>
                            <path
                                d="M11.3245 14.4883L12.6906 15.822V16.4942H2V15.822L3.3553 14.4883V11.1597C3.28833 10.2186 3.55162 9.28363 4.09982 8.51576C4.64802 7.74789 5.44681 7.1952 6.35864 6.95288V6.4975C6.35864 6.23295 6.46373 5.97923 6.6508 5.79216C6.83787 5.60509 7.09159 5.5 7.35614 5.5C7.62069 5.5 7.87441 5.60509 8.06148 5.79216C8.24855 5.97923 8.35364 6.23295 8.35364 6.4975V6.95288C9.25835 7.20335 10.0485 7.75916 10.59 8.52597C11.1315 9.29278 11.391 10.2233 11.3245 11.1597V14.4883Z">
                            </path>
                            <path
                                d="M8.26662 18.1094C8.01652 18.3595 7.67731 18.5 7.32361 18.5C6.96992 18.5 6.63071 18.3595 6.3806 18.1094C6.1305 17.8593 5.99 17.5201 5.99 17.1664H8.65722C8.65722 17.5201 8.51672 17.8593 8.26662 18.1094Z">
                            </path>
                        </g>
                    </svg> </span><span class="label">{{ __('Notification Template') }}</span>
                </a>
                </li>
                @endif --}}



                    {{-- @if (\Auth::user()->type != 'super admin')
                            @if (Gate::check('manage company plan') || Gate::check('manage order') || Gate::check('manage company settings'))
                                <li class="dash-item dash-hasmenu ">
                                    <a href="#settings"
                                        class="dash-link {{ Request::segment(1) == 'settings' ||
                                        Request::segment(1) == 'plans' ||
                                        Request::segment(1) == 'stripe' ||
                                        Request::segment(1) == 'order'
                                            ? ' active dash-trigger'
                                            : '' }}">
                <span class="dash-micon">
                    <svg fill="#ffffff" height="50px" width="50px" version="1.1" id="Capa_1"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="-397.52 -397.52 1192.56 1192.56" xml:space="preserve" stroke="#ffffff">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                        </g>
                        <g id="SVGRepo_iconCarrier">
                            <g>
                                <g id="Layer_5_36_">
                                    <g>
                                        <path
                                            d="M126.32,156.454c-37.993,0-68.901,30.911-68.901,68.905c0,37.991,30.909,68.9,68.901,68.9s68.9-30.909,68.9-68.9 C195.22,187.365,164.311,156.454,126.32,156.454z M126.32,279.641c-29.932,0-54.283-24.351-54.283-54.281 c0-29.934,24.352-54.286,54.283-54.286s54.282,24.353,54.282,54.286C180.602,255.29,156.251,279.641,126.32,279.641z">
                                        </path>
                                        <path
                                            d="M241.133,193.697l-9.568-2.638c-1.085-0.299-2.955-2.038-3.333-3.102l-2.717-6.683l-0.152-0.346 c-0.483-1.028-0.382-3.607,0.179-4.597l4.819-8.491c3.36-5.921,2.264-14.015-2.549-18.824l-23.776-23.779 c-2.852-2.848-6.952-4.482-11.248-4.482c-2.723,0-5.341,0.669-7.57,1.935l-8.038,4.561c-0.324,0.184-1.251,0.458-2.478,0.458 c-1.061,0-1.766-0.207-1.991-0.316l-8.275-3.484l-0.348-0.136c-1.068-0.385-2.818-2.276-3.121-3.375l-2.719-9.851 c-1.81-6.563-8.307-11.511-15.113-11.511h-33.629c-6.807,0-13.303,4.949-15.11,11.508l-2.723,9.855 c-0.303,1.101-2.06,3.003-3.132,3.393l-8.905,3.768l-0.378,0.173c-0.223,0.11-0.926,0.318-1.988,0.318 c-1.202,0.001-2.109-0.267-2.429-0.448l-7.565-4.295c-2.231-1.266-4.851-1.936-7.575-1.936c-4.3,0-8.4,1.636-11.247,4.484 l-23.782,23.778c-4.812,4.813-5.906,12.904-2.546,18.822l4.736,8.343c0.565,0.998,0.677,3.584,0.198,4.613 c-1.323,2.844-4.967,8.298-6.713,9.848l-8.841,2.438C4.946,195.509,0,202.006,0,208.812v33.626c0,6.803,4.946,13.3,11.506,15.112 l9.568,2.641c1.088,0.3,2.96,2.038,3.338,3.101l2.945,7.17l0.149,0.338c0.484,1.024,0.39,3.586-0.169,4.568l-4.362,7.68 c-3.356,5.916-2.261,14.006,2.55,18.822l23.78,23.777c2.85,2.85,6.95,4.484,11.248,4.484l0,0c2.723,0,5.342-0.669,7.576-1.936 l7.361-4.177c0.327-0.186,1.26-0.461,2.492-0.461c1.062,0,1.769,0.206,1.995,0.315l8.39,3.522l0.357,0.139 c1.065,0.382,2.81,2.264,3.112,3.358l2.56,9.276c1.808,6.561,8.305,11.511,15.111,11.511h33.629 c6.806,0,13.303-4.948,15.113-11.511l2.558-9.279c0.3-1.087,2.038-2.957,3.099-3.335l7.735-3.188l0.355-0.158 c0.225-0.107,0.931-0.311,1.99-0.311c1.259,0,2.214,0.282,2.548,0.472l7.823,4.443c2.232,1.267,4.851,1.936,7.576,1.936 c4.3,0,8.4-1.636,11.248-4.485l23.778-23.777c4.814-4.812,5.91-12.904,2.549-18.825l-4.441-7.82 c-0.556-0.979-0.647-3.525-0.163-4.541l3.188-7.659l0.134-0.347c0.379-1.064,2.253-2.805,3.343-3.105l9.57-2.64 c6.559-1.812,11.505-8.309,11.505-15.112v-33.623C252.641,202.006,247.695,195.508,241.133,193.697z M237.247,243.459 l-9.568,2.64c-5.615,1.549-11.11,6.61-13.151,12.086l-2.914,7.023c-2.439,5.314-2.139,12.778,0.738,17.851l4.422,7.782 c0.124,0.31,0.021,1.075-0.152,1.31L192.875,315.9c-0.096,0.073-0.467,0.233-0.944,0.233c-0.22,0-0.366-0.046-0.357-0.03 l-7.824-4.443c-2.702-1.534-6.17-2.379-9.766-2.379c-2.072,0-5.137,0.288-8.082,1.641l-7.098,2.934 c-5.479,2.037-10.544,7.533-12.093,13.151l-2.544,9.234c-0.13,0.305-0.73,0.766-1.066,0.82l-33.553,0.002 c-0.331-0.045-0.946-0.513-1.064-0.78l-2.56-9.276c-1.546-5.609-6.598-11.106-12.064-13.157l-7.725-3.232 c-2.97-1.383-6.063-1.678-8.155-1.678c-3.572,0-7.02,0.841-9.707,2.366l-7.32,4.155c-0.036,0.015-0.178,0.053-0.402,0.053 c-0.478,0-0.85-0.161-0.913-0.204l-23.747-23.741c-0.204-0.268-0.309-1.036-0.206-1.304l4.36-7.676 c2.873-5.058,3.185-12.52,0.766-17.839l-2.701-6.555c-2.037-5.48-7.535-10.548-13.153-12.097l-9.521-2.625 c-0.309-0.132-0.778-0.748-0.822-1.035l-0.002-33.581c0.045-0.333,0.514-0.949,0.777-1.067l9.563-2.637 c8.015-2.207,15.287-17.422,15.357-17.572c2.473-5.313,2.164-12.878-0.737-17.994l-4.718-8.307 c-0.124-0.312-0.021-1.076,0.15-1.309l23.749-23.748c0.096-0.073,0.467-0.232,0.943-0.232c0.222,0,0.363,0.041,0.359,0.03 l7.562,4.292c2.674,1.52,6.101,2.357,9.649,2.357c2.116,0,5.241-0.303,8.236-1.722l8.238-3.494 c5.445-2.071,10.479-7.573,12.021-13.166l2.709-9.813c0.131-0.308,0.746-0.776,1.032-0.819l33.584-0.002 c0.333,0.045,0.948,0.514,1.066,0.781l2.719,9.85c1.545,5.604,6.591,11.105,12.048,13.164l7.61,3.193 c2.975,1.39,6.073,1.686,8.17,1.686c3.568,0,7.012-0.84,9.694-2.363l7.995-4.538c0.036-0.015,0.176-0.051,0.396-0.051 c0.48,0,0.853,0.161,0.914,0.202l23.744,23.744c0.203,0.267,0.306,1.032,0.201,1.304l-4.819,8.493 c-2.868,5.056-3.189,12.511-0.79,17.823l2.489,6.102c2.034,5.487,7.535,10.562,13.154,12.11l9.523,2.623 c0.309,0.132,0.777,0.748,0.82,1.036l0.002,33.581C237.98,242.726,237.511,243.342,237.247,243.459z">
                                        </path>
                                        <path
                                            d="M393.377,112.81l-6.573-1.953c-2.321-0.688-4.846-3.132-5.611-5.428l-1.713-4.439c-0.983-2.211-0.778-5.725,0.459-7.805 l3.443-5.806c1.236-2.08,0.875-5.212-0.8-6.958L366.48,63.675c-1.679-1.746-4.794-2.232-6.922-1.076l-5.609,3.038 c-2.13,1.154-5.636,1.198-7.793,0.097l-5.418-2.399c-2.262-0.866-4.599-3.496-5.199-5.843l-1.745-6.844 c-0.598-2.345-3.066-4.304-5.487-4.352l-23.232-0.457c-2.42-0.048-4.965,1.814-5.654,4.133l-2.013,6.77 c-0.691,2.321-3.129,4.861-5.42,5.645l-5.954,2.389c-2.19,1.027-5.692,0.856-7.772-0.38l-5.166-3.07 c-2.083-1.237-5.215-0.876-6.96,0.805l-16.751,16.1c-1.742,1.676-2.226,4.793-1.073,6.921l3.159,5.831 c1.153,2.13,1.23,5.645,0.169,7.813c-1.061,2.167-5.21,8.66-7.557,9.256l-6.643,1.693c-2.345,0.599-4.305,3.07-4.353,5.49 l-0.456,23.228c-0.047,2.422,1.814,4.965,4.134,5.655l6.573,1.954c2.322,0.688,4.849,3.132,5.616,5.43l1.852,4.759 c0.992,2.211,0.795,5.721-0.444,7.802l-3.113,5.241c-1.238,2.084-0.875,5.215,0.803,6.961l16.104,16.746 c1.678,1.747,4.793,2.232,6.924,1.078l5.14-2.785c2.128-1.155,5.638-1.197,7.796-0.101l5.501,2.428 c2.261,0.864,4.605,3.488,5.2,5.837l1.642,6.442c0.598,2.348,3.067,4.307,5.488,4.354l23.231,0.455 c2.422,0.049,4.964-1.811,5.654-4.133l1.894-6.373c0.687-2.323,3.131-4.851,5.43-5.617l5.146-2.013 c2.207-0.997,5.719-0.802,7.798,0.436l5.342,3.172c2.082,1.238,5.215,0.876,6.958-0.804l16.751-16.1 c1.744-1.68,2.229-4.794,1.074-6.921l-2.962-5.467c-1.152-2.129-1.21-5.644-0.123-7.808l2.192-5.01 c0.86-2.266,3.482-4.609,5.829-5.206l6.645-1.693c2.343-0.599,4.305-3.066,4.352-5.488l0.457-23.229 C397.557,116.047,395.695,113.5,393.377,112.81z M314.236,170.826c-23.495-0.462-42.171-19.886-41.709-43.381 c0.462-23.5,19.886-42.176,43.381-41.715c23.497,0.463,42.172,19.889,41.71,43.387 C357.156,152.614,337.733,171.288,314.236,170.826z">
                                        </path>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </svg>
                </span><span class="dash-mtext">{{ __('Settings') }}</span>
                </a>
                <ul id="settings" class="dash-submenu">
                    @if (Gate::check('manage company settings'))
                    <li class="dash-item dash-hasmenu ">
                        <a href="{{ route('settings') }}"
                            class="dash-link {{ Request::segment(1) == 'settings' ? ' active' : '' }}">{{ __('System Settings') }}</a>
                    </li>
                    @endif
                    @if (Gate::check('manage company plan'))
                    <li class="dash-item">
                        <a href="{{ route('plans.index') }}"
                            class="label {{ Request::route()->getName() == 'plans.index' || Request::route()->getName() == 'stripe' ? ' active' : '' }}">{{ __('Setup Subscription Plan') }}</a>
                    </li>
                    @endif

                    @if (Gate::check('manage order') && Auth::user()->type == 'company')
                    <li class="dash-item ">
                        <a href="{{ route('order.index') }}"
                            class="label {{ Request::segment(1) == 'order' ? 'active' : '' }}">{{ __('Order') }}</a>
                    </li>
                    @endif
                </ul>
                </li>
                @endif
                @endif --}}





                    @if (\Auth::user()->type == 'client')
                        <ul class="dash-navbar">
                            @if (Gate::check('manage client dashboard'))
                                <li
                                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'dashboard' ? ' active' : '' }}">
                                    <a href="{{ route('client.dashboard.view') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-home"></i></span><span
                                            class="label">{{ __('Dashboard') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Gate::check('manage deal'))
                                <li
                                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'deals' ? ' active' : '' }}">
                                    <a href="{{ route('deals.index') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-rocket"></i></span><span
                                            class="label">{{ __('Deals') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Gate::check('manage contract'))
                                <li
                                    class="dash-item dash-hasmenu {{ Request::route()->getName() == 'contract.index' || Request::route()->getName() == 'contract.show' ? 'active' : '' }}">
                                    <a href="{{ route('contract.index') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-rocket"></i></span><span
                                            class="label">{{ __('Contract') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Gate::check('manage project'))
                                <li
                                    class="dash-item dash-hasmenu  {{ Request::segment(1) == 'projects' ? ' active' : '' }}">
                                    <a href="{{ route('projects.index') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-share"></i></span><span
                                            class="label">{{ __('Project') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Gate::check('manage project'))
                                <li
                                    class="dash-item  {{ Request::route()->getName() == 'project_report.index' || Request::route()->getName() == 'project_report.show' ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('project_report.index') }}">
                                        <span class="dash-micon"><i class="ti ti-chart-line"></i></span><span
                                            class="label">{{ __('Project Report') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if (Gate::check('manage project task'))
                                <li
                                    class="dash-item dash-hasmenu  {{ Request::segment(1) == 'taskboard' ? ' active' : '' }}">
                                    <a href="{{ route('taskBoard.view', 'list') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-list-check"></i></span><span
                                            class="label">{{ __('Tasks') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if (Gate::check('manage bug report'))
                                <li
                                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'bugs-report' ? ' active' : '' }}">
                                    <a href="{{ route('bugs.view', 'list') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-bug"></i></span><span
                                            class="label">{{ __('Bugs') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if (Gate::check('manage timesheet'))
                                <li
                                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'timesheet-list' ? ' active' : '' }}">
                                    <a href="{{ route('timesheet.list') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-clock"></i></span><span
                                            class="label">{{ __('Timesheet') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if (Gate::check('manage project task'))
                                <li
                                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'calendar' ? ' active' : '' }}">
                                    <a href="{{ route('task.calendar', ['all']) }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-calendar"></i></span><span
                                            class="label">{{ __('Task Calender') }}</span>
                                    </a>
                                </li>
                            @endif


                            <li class="dash-item dash-hasmenu">
                                <a href="{{ route('support.index') }}"
                                    class="dash-link {{ Request::segment(1) == 'support' ? 'active' : '' }}">
                                    <span class="dash-micon"><i class="ti ti-headphones"></i></span><span
                                        class="label">{{ __('Support') }}</span>
                                </a>
                            </li>
                        </ul>
                    @endif
                    @if (\Auth::user()->type == 'super admin')
                        <ul class="dash-navbar">
                            @if (Gate::check('manage super admin dashboard'))
                                <li
                                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'dashboard' ? ' active' : '' }}">
                                    <a href="{{ route('client.dashboard.view') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-home"></i></span><span
                                            class="label">{{ __('Dashboard') }}</span>
                                    </a>
                                </li>
                            @endif


                            @can('manage user')
                                <li
                                    class="dash-item dash-hasmenu {{ Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit' ? ' active' : '' }}">
                                    <a href="{{ route('users.index') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-users"></i></span><span
                                            class="label">{{ __('User') }}</span>
                                    </a>
                                </li>
                            @endcan

                            @if (Gate::check('manage plan'))
                                <li
                                    class="dash-item dash-hasmenu  {{ Request::segment(1) == 'plans' ? 'active' : '' }}">
                                    <a href="{{ route('plans.index') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-trophy"></i></span><span
                                            class="label">{{ __('Plan') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (\Auth::user()->type == 'super admin')
                                <li
                                    class="dash-item dash-hasmenu {{ request()->is('plan_request*') ? 'active' : '' }}">
                                    <a href="{{ route('plan_request.index') }}" class="dash-link">
                                        <span class="dash-micon"><i
                                                class="ti ti-arrow-up-right-circle"></i></span><span
                                            class="label">{{ __('Plan Request') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Gate::check('manage coupon'))
                                <li
                                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'coupons' ? 'active' : '' }}">
                                    <a href="{{ route('coupons.index') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-gift"></i></span><span
                                            class="label">{{ __('Coupon') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Gate::check('manage order'))
                                <li
                                    class="dash-item dash-hasmenu  {{ Request::segment(1) == 'orders' ? 'active' : '' }}">
                                    <a href="{{ route('order.index') }}" class="dash-link">
                                        <span class="dash-micon"><i
                                                class="ti ti-shopping-cart-plus"></i></span><span
                                            class="label">{{ __('Order') }}</span>
                                    </a>
                                </li>
                            @endif
                            <li
                                class="dash-item dash-hasmenu {{ Request::segment(1) == 'email_template' || Request::route()->getName() == 'manage.email.language' ? ' active dash-trigger' : 'collapsed' }}">
                                <a href="{{ route('manage.email.language', [$emailTemplate->id, \Auth::user()->lang]) }}"
                                    class="dash-link">
                                    <span class="dash-micon"><i class="ti ti-template"></i></span>
                                    <span class="label">{{ __('Email Template') }}</span>
                                </a>
                            </li>

                            @if (\Auth::user()->type == 'super admin')
                                @include('landingpage::menu.landingpage')
                            @endif

                            @if (Gate::check('manage system settings'))
                                <li
                                    class="dash-item dash-hasmenu {{ Request::route()->getName() == 'systems.index' ? ' active' : '' }}">
                                    <a href="{{ route('systems.index') }}" class="dash-link">
                                        <span class="dash-micon"><i class="ti ti-settings"></i></span><span
                                            class="label">{{ __('Settings') }}</span>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    @endif


                </ul>
            @endif

        </div>

        <!-- Menu End -->

        <!-- Mobile Buttons Start -->
        <div class="mobile-buttons-container">
            <!-- Menu Button Start -->
            <a href="#" id="mobileMenuButton" class="menu-button">
                <i data-acorn-icon="menu"></i>
            </a>
            <!-- Menu Button End -->
        </div>
        <!-- Mobile Buttons End -->
    </div>
    <div class="nav-shadow"></div>
</div>
{{-- </ul>
    </div>
    </div> --}}
</div>
<script>
window.addEventListener('load', function() {
    setTimeout(function() {
        document.querySelectorAll('#menu .dash-item.dash-hasmenu > .dash-link').forEach(function(link) {
            link.onclick = function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var parentLi = this.closest('.dash-item.dash-hasmenu');
                var subMenu = parentLi.querySelector(':scope > .dash-submenu');
                if (!subMenu) return;

                var isOpen = subMenu.style.display === 'block';

                // Only close siblings at the SAME level, not all submenus
                var siblings = parentLi.parentElement.querySelectorAll(':scope > .dash-item.dash-hasmenu > .dash-submenu');
                siblings.forEach(function(s) {
                    if (s !== subMenu) {
                        s.style.display = 'none';
                        s.classList.remove('show', 'open');
                    }
                });

                if (!isOpen) {
                    subMenu.style.display = 'block';
                    subMenu.classList.add('show', 'open');
                } else {
                    subMenu.style.display = 'none';
                    subMenu.classList.remove('show', 'open');
                }

                return false;
            };
        });
    }, 500);
});
</script>
</nav>
{{-- new theam code --}}

{{-- <script>
// // Wait for the DOM to be fully loaded
// document.addEventListener("DOMContentLoaded", function() {
//     // Get all elements with the class 'dash-link'
//     var dashLinks = document.querySelectorAll('.dash-link');

//     // Loop through each dash link
//     dashLinks.forEach(function(link) {
//         // Add click event listener
//         link.addEventListener('click', function(event) {
//             // Prevent the default action of the link
//             event.preventDefault();

//             // Toggle the active class for the clicked link
//             link.classList.toggle('active');

//             // Check if the link has a submenu
//             if (link.nextElementSibling && link.nextElementSibling.classList.contains(
//                     'dash-submenu')) {
//                 // Toggle the 'show' class for the submenu
//                 link.nextElementSibling.classList.toggle('show');
//             } else {
//                 // If the link doesn't have a submenu, check if its parent has one
//                 var parentSubmenu = link.closest('.dash-submenu');
//                 if (parentSubmenu) {
//                     // Toggle the 'show' class for the parent submenu
//                     parentSubmenu.classList.toggle('show');
//                 }
//             }
//         });

//         // Add click event listener to the SVG icon
//         var svgIcon = link.querySelector('.dash-micon');
//         if (svgIcon) {
//             svgIcon.addEventListener('click', function(event) {
//                 // Prevent the default action of the SVG icon
//                 event.preventDefault();

//                 // Find the parent menu item
//                 var parentMenuItem = link.closest('.dash-item');

//                 // Toggle the 'show' class for the parent menu item
//                 if (parentMenuItem) {
//                     parentMenuItem.classList.toggle('show');
//                 }
//             });
//         }
//     });
// });

var menuContainer = document.querySelector(".menu-container");

menuContainer.addEventListener("click", function(event) {
    // Check if the clicked element is an li
    //   console.log("event:", event);
    if (event.target.tagName === "A" && event.target.classList.contains("dash-link")) {
        console.log("dash:", event.target.classList.contains("dash-link"));
        // Find the parent list item
        var listItem = event.target.closest("li");
        console.log("Sub LI:", listItem);

        // Check if the parent list item exists and contains a sub unordered list
        if (listItem && listItem.querySelector("ul")) {
            // Find the immediate child unordered list
            var subUL = listItem.querySelector("ul");

            // Delay the execution of toggling the "show" class by 1 second
            setTimeout(function() {

                if (subUL.classList.contains("show") && subUL.classList.contains("open")) {
                    subUL.classList.remove("open");
                    subUL.classList.remove("show");
                    event.target.setAttribute("aria-expanded", "false");
                } else {
                    subUL.classList.add("show");
                    subUL.classList.add("open");
                    event.target.setAttribute("aria-expanded", "true");
                }
            }, 500); // 1000 milliseconds = 1 second
        }
    }
});
</script> --}}


{{-- //
<script>
    //     // Get the menu container
    //     var menuContainer = document.querySelector(".menu-container");

    //     // Add click event listener to the menu container
    //     menuContainer.addEventListener("click", function(event) {
    //         // Find if we clicked on a dash-link or any of its children
    //         let dashLink = event.target;
    //         while (dashLink && dashLink !== menuContainer) {
    //             if (dashLink.classList && dashLink.classList.contains("dash-link")) {
    //                 break;
    //             }
    //             dashLink = dashLink.parentElement;
    //         }

    //         // If we found a dash-link
    //         if (dashLink && dashLink.classList.contains("dash-link")) {
    //             // Find the parent list item
    //             var listItem = dashLink.closest("li");

    //             // Check if the parent list item exists and contains a sub unordered list
    //             if (listItem && listItem.querySelector("ul")) {
    //                 // This is a parent menu item with submenu - prevent default navigation
    //                 event.preventDefault();

    //                 // Find the immediate child unordered list
    //                 var subUL = listItem.querySelector("ul");

    //                 // Direct toggle without conditions
    //                 if (subUL.style.display === "block") {
    //                     // Menu is open, close it
    //                     subUL.style.display = "none";
    //                     dashLink.setAttribute("aria-expanded", "false");
    //                     subUL.classList.remove("show");
    //                     subUL.classList.remove("open");
    //                 } else {
    //                     // Menu is closed, open it
    //                     // First close all other open menus at the same level
    //                     var siblings = listItem.parentElement.children;
    //                     for (var i = 0; i < siblings.length; i++) {
    //                         var sibling = siblings[i];
    //                         if (sibling !== listItem && sibling.querySelector("ul")) {
    //                             var siblingSubUL = sibling.querySelector("ul");
    //                             siblingSubUL.style.display = "none";
    //                             siblingSubUL.classList.remove("show", "open");
    //                             if (sibling.querySelector(".dash-link")) {
    //                                 sibling.querySelector(".dash-link").setAttribute("aria-expanded", "false");
    //                             }
    //                         }
    //                     }

    //                     // Then open this menu
    //                     subUL.style.display = "block";
    //                     dashLink.setAttribute("aria-expanded", "true");
    //                     subUL.classList.add("show");
    //                     subUL.classList.add("open");
    //                 }
    //             }
    //             // If it's a submenu item without its own submenu, let the default navigation happen
    //         }
    //     });
    // 
</script> --}}

<script>
    (function() {
        const TAG = '[menu-center-vh]';

        // --- pickers ---
        const getMenu = () =>
            document.getElementById('menu') ||
            document.querySelector('.menu.show') ||
            document.querySelector('.menu') ||
            document.querySelector('.menu-container ul');

        // Prefer the real current/leaf link over collapsible headers
        const getActiveDeep = (menu) => {
            const prefer = [
                'a[style*="color: rgb(248, 0, 0)"]',
                'li .active:not([data-bs-toggle])',
                'a.active:not([data-bs-toggle])',
                'li.active > a:not([data-bs-toggle])',
                'a.active',
                'li.active'
            ];
            for (const sel of prefer) {
                const el = menu.querySelector(sel);
                if (el) return el.closest('li, a') || el;
            }
            return null;
        };

        const getScrollable = (menu) => {
            // OverlayScrollbars-aware
            const osHost = menu.closest('.os-host');
            if (osHost) {
                const vp = osHost.querySelector('.os-viewport') || osHost.querySelector('.os-content');
                if (vp) return {
                    container: vp,
                    osHost
                };
            }
            // Generic ancestor with vertical scrolling
            let n = menu;
            while (n && n !== document.body) {
                const cs = getComputedStyle(n);
                if (n.scrollHeight > n.clientHeight + 1 && /(auto|scroll|overlay)/.test(cs.overflowY)) {
                    return {
                        container: n,
                        osHost: null
                    };
                }
                n = n.parentElement;
            }
            // Page scroller fallback
            return {
                container: document.scrollingElement || document.documentElement,
                osHost: null
            };
        };

        // --- utils ---
        const currentScrollTop = (el) =>
            (el === document.scrollingElement || el === document.documentElement || el === document.body) ?
            (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0) :
            el.scrollTop;

        // Ensure parents are open so sizes are measurable
        const expandCollapsedAncestors = (el) => {
            let n = el && el.parentElement;
            while (n) {
                if (n.classList && n.classList.contains('collapse') && !n.classList.contains('show')) {
                    n.classList.add('show');
                }
                n = n.parentElement;
            }
        };

        // Compute scrollTop so the element’s TOP hits <vh>% of the viewport, centered by its own height
        const computeTargetForViewportVH = (container, target, vh = 50) => {
            const cRect = container.getBoundingClientRect();
            const tRect = target.getBoundingClientRect();
            const base = currentScrollTop(container);

            // element’s top in container scroll coordinates (independent of current scroll)
            const topInContainer = (tRect.top - cRect.top) + base;

            // Desired top position in viewport pixels (50vh by default, minus half element height to really center it)
            const targetViewportTop = (window.innerHeight * (vh / 100)) - (target.offsetHeight / 2);

            // Solve for new scrollTop: cRect.top + (topInContainer - S) = targetViewportTop  ->  S = topInContainer + cRect.top - targetViewportTop
            let want = topInContainer + cRect.top - targetViewportTop;

            // Clamp to scrollable range
            const max = Math.max(0, container.scrollHeight - container.clientHeight);
            if (want < 0) want = 0;
            if (want > max) want = max;

            return want;
        };

        const scrollToY = (container, y, osHost) => {
            // OverlayScrollbars (v2/v1) if available
            try {
                const os = window.OverlayScrollbarsGlobal?.OverlayScrollbars ?
                    window.OverlayScrollbarsGlobal.OverlayScrollbars(osHost) :
                    (window.OverlayScrollbars ? window.OverlayScrollbars(osHost) : null);
                if (os && typeof os.scroll === 'function') {
                    os.scroll({
                        y
                    }, 400);
                    return;
                }
            } catch (_) {}

            if (container === document.scrollingElement || container === document.documentElement ||
                container === document.body) {
                window.scrollTo({
                    top: y,
                    behavior: 'smooth'
                });
            } else if (container.scrollTo) {
                container.scrollTo({
                    top: y,
                    behavior: 'smooth'
                });
            } else {
                container.scrollTop = y;
            }
        };

        // --- main run (viewport-vh centering) ---
        const runVH = (vh = 50) => {
            const menu = getMenu();
            if (!menu) {
                console.warn(TAG, 'menu not found');
                return;
            }

            const picked = getActiveDeep(menu);
            if (!picked) {
                console.warn(TAG, 'no active/current item found');
                return;
            }

            expandCollapsedAncestors(picked);

            const {
                container,
                osHost
            } = getScrollable(menu);

            // Frame 1: READ/layout
            requestAnimationFrame(() => {
                const y = computeTargetForViewportVH(container, picked, vh);

                // (compact one-time debug)
                //console.groupCollapsed(TAG, `center at ${vh}vh`);
                //console.log('container:', container);
                //console.log('picked:', picked);
                //console.log('clientHeight:', container.clientHeight, 'scrollHeight:', container.scrollHeight);
                //console.log('targetScrollTop:', y);
                //console.groupEnd();

                // Frame 2: WRITE/scroll
                requestAnimationFrame(() => scrollToY(container, y, osHost));

                // Verify once; fallback if needed
                setTimeout(() => {
                    const after = currentScrollTop(container);
                    if (Math.abs(after - y) > 4) {
                        picked.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'nearest'
                        });
                    }
                }, 220);
            });
        };

        // Init & tiny API
        const init = () => {
            runVH(50); // default: center at 50vh
            setTimeout(() => runVH(50), 800); // one retry for late layout
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, {
                passive: true
            });
        } else {
            init();
        }

        // Manual controls for you in DevTools:
        window.menuCenterVH = {
            center(vh = 50) {
                runVH(vh);
            }, // e.g. menuCenterVH.center(40) for 40vh
            centerMid() {
                runVH(50);
            },
            centerTopThird() {
                runVH(33.3333);
            },
            centerBottomThird() {
                runVH(66.6667);
            }
        };

        // Optional: recenter on resize/orientation (passive, low-frequency)
        window.addEventListener('resize', () => runVH(50), {
            passive: true
        });
    })();
</script>

{{-- link text changing script--change selected page color --change selected page color--change selected page color--change selected page color--change selected page color --}}
<script>
    const activeLinks = document.querySelectorAll(
        'html[data-placement="vertical"] .nav-container .nav-content .menu-container .menu li a.active'
    );

    if (activeLinks.length > 0) {
        const lastActive = activeLinks[activeLinks.length - 1];
        lastActive.style.setProperty('background', 'none', 'important');
        lastActive.style.setProperty('box-shadow', 'none', 'important');
        lastActive.style.setProperty('color', '#F80000', 'important');
    }
</script>


{{-- <script>
    var menuContainer = document.querySelector(".menu-container");

    // Add click event listener to the menu container
    menuContainer.addEventListener("click", function(event) {
        // Check if the clicked element is an `a` tag with class `dash-link`
        if (event.target.tagName === "A" && event.target.classList.contains("dash-link")) {
            // Find the parent list item
            var listItem = event.target.closest("li");

            // Check if the parent list item exists and contains a sub unordered list
            if (listItem && listItem.querySelector("ul")) {
                // Find the immediate child unordered list
                var subUL = listItem.querySelector("ul");

                // Close any open submenus at the same level
                var openMenus = listItem.parentElement.querySelectorAll("ul.show");
                openMenus.forEach(function(openMenu) {
                    if (openMenu !== subUL) {
                        openMenu.classList.remove("show", "open");
                        openMenu.previousElementSibling.setAttribute("aria-expanded", "false");
                    }
                });

                // Delay the execution of toggling the "show" class by 500ms
                setTimeout(function() {
                    if (subUL.classList.contains("show") && subUL.classList.contains("open")) {
                        subUL.classList.remove("open", "show");
                        event.target.setAttribute("aria-expanded", "false");
                    } else {
                        subUL.classList.add("show", "open");
                        event.target.setAttribute("aria-expanded", "true");
                    }
                }, 500); // 500 milliseconds
            }
        }
    });

    const removeExtraSpaceFromSideMenu = () => {
        const spaceMenuBottom = document.getElementsByClassName("ps__rail-y")[0];
        const logo = document.getElementsByClassName("logo")[0];
        const userContainer = document.getElementsByClassName("user-container")[0];

        if (spaceMenuBottom && logo && userContainer) {
            const logoH = logo.getBoundingClientRect().height;
            const userContainerH = userContainer.getBoundingClientRect().height;
            spaceMenuBottom.style.height = `${logoH + userContainerH + 200}px`;
        }

        return spaceMenuBottom;
    };

    // Wait for .ps__rail-y to appear, then observe
    // const sideBarSpaceInterval = setInterval(() => {
    //     removeExtraSpaceFromSideMenu();
    // }, 200);
</script> --}}

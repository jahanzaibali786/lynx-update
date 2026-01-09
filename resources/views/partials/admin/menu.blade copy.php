

    <!-- Favicon Tags Start -->
    {{-- <link rel="apple-touch-icon-precomposed" sizes="57x57" href="img/favicon/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/favicon/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/favicon/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="img/favicon/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="img/favicon/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="img/favicon/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="img/favicon/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="img/favicon/apple-touch-icon-152x152.png" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-196x196.png" sizes="196x196" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-128.png" sizes="128x128" />
    <meta name="application-name" content="&nbsp;" />
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <meta name="msapplication-TileImage" content="img/favicon/mstile-144x144.png" />
    <meta name="msapplication-square70x70logo" content="img/favicon/mstile-70x70.png" />
    <meta name="msapplication-square150x150logo" content="img/favicon/mstile-150x150.png" />
    <meta name="msapplication-wide310x150logo" content="img/favicon/mstile-310x150.png" />
    <meta name="msapplication-square310x310logo" content="img/favicon/mstile-310x310.png" /> --}}
    <!-- Favicon Tags End -->
    <!-- Font Tags Start -->
    {{-- <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet" /> --}}
    {{-- <link rel="stylesheet" href="{{ asset('style.css')}}" />
    <!-- Font Tags End -->
    <!-- Vendor Styles Start -->
    <link rel="stylesheet" href="{{ asset('bootstrap.min.css')}}" />
    <link rel="stylesheet" href="{{ asset('OverlayScrollbars.min.css')}}" />

    <link rel="stylesheet" href="{{ asset('glide.core.min.css')}}" />

    <!-- Vendor Styles End -->
    <!-- Template Base Styles Start -->
    <link rel="stylesheet" href="{{ asset('styles.css')}}" />
    <!-- Template Base Styles End -->

    <link rel="stylesheet" href="{{ asset('main.css')}}" />
    <script src="{{ asset('loader.js')}}"></script>   --}}



  


@php
    use App\Models\Utility;
      //  $logo=asset(Storage::url('uploads/logo/'));
        $logo=\App\Models\Utility::get_file('uploads/logo/');
        $company_logo=Utility::getValByName('company_logo_dark');
        $company_logos=Utility::getValByName('company_logo_light');
        $logo_light = \App\Models\Utility::getValByName('company_logo_light');
        $logo_dark = \App\Models\Utility::getValByName('company_logo_dark');
        $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';
        $company_small_logo=Utility::getValByName('company_small_logo');
        $setting = \App\Models\Utility::colorset();
        $mode_setting = \App\Models\Utility::mode_layout();
        $emailTemplate     = \App\Models\EmailTemplate::first();
        $lang= Auth::user()->lang;


@endphp  


<div id="nav" class="nav-container d-flex">
    <div class="nav-content d-flex">
      <!-- Logo Start -->
      <div class="logo position-relative">
        <a href="Dashboards.Patient.html">
          <!-- Logo can be added directly -->
          <!-- <img src="img/logo/logo-white.svg" alt="logo" /> -->

          <!-- Or added via css to provide different ones for different color themes -->
          <div class="img"></div>
        </a>
      </div>
      <!-- Logo End -->

      <!-- User Menu Start -->
      <div class="user-container d-flex">
        <a href="#" class="d-flex user position-relative" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <img class="profile" alt="profile" src="img/profile/profile-1.webp" />
          <div class="name">Alicia Owens</div>
        </a>
      </div>
      <!-- User Menu End -->

      @php
    $users=\Auth::user();
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
    $languages=\App\Models\Utility::languages();
    $settings = Utility::settings();
    $lang = isset($users->lang)?$users->lang:'en';
    if ($lang == null) {
        $lang = 'en';
    }
    $LangName = \App\Models\Language::where('code',$lang)->first();

    $setting = \App\Models\Utility::colorset();
    $mode_setting = \App\Models\Utility::mode_layout();
// dd($settings);
    $unseenCounter=App\Models\ChMessage::where('to_id', Auth::user()->id)->where('seen', 0)->count();
@endphp

      <!-- Icons Menu Start -->
      <ul class="list-unstyled list-inline text-center menu-icons">
        <li class="list-inline-item">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();" class="dropdown-item">
                <i data-acorn-icon="logout" data-acorn-size="18"></i>
            </a>
        </li>
        
       


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
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
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
        

          <li class="list-inline-item">
            <a class="dash-head-link arrow-none me-0" href="{{ url('chats') }}" aria-haspopup="false" aria-expanded="false">
             <i class="ti ti-brand-hipchat"></i>
            </a>
          </li>
        <li class="list-inline-item">
          <a href="#" id="pinButton" class="pin-button">
            <i data-acorn-icon="lock-on" class="unpin" data-acorn-size="18"></i>
            <i data-acorn-icon="lock-off" class="pin" data-acorn-size="18"></i>
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
                        url: "{{ route('save.setting')}}",
                        data: combinedData,
                        success: function (response) {
                       
                            console.log(response);
                            location.reload(); 
                        },
                        error: function (error) {
              
                            console.log(error);
                        }
                    });
          
                };
        </script>



<li class="list-inline-item" id="settingsForm1" style="margin-top: 6px;">
    {{ Form::model($settings, ['route' => 'save.setting', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'settingsForm']) }}
    <a href="#" id="colorButton" class="hao">
        <i data-acorn-icon="light-on" class="light hao1 " data-hao = "light" data-acorn-size="18" style="color: white"></i>
        <i data-acorn-icon="light-off" class="dark hao1" data-hao ="dark" data-acorn-size="18" style="color: white"></i>
      </a>
    {{ Form::close() }}

  </li>
        
      

        <li class="dropdowni" onmouseleave="hideDropdown()">
            <a href="#"> <i data-acorn-icon="bell" data-acorn-size="18"></i></a>
            <div class="dropdown-contente dropdown-menu dropdown-menu-end wide notification-dropdown scroll-out">
                <div class="scroll">
                    <ul class="list-unstyled border-last-none">
                      <li class="mb-3 pb-3 border-bottom border-separator-light d-flex">
                        <img src="img/profile/profile-1.webp" class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                        <div class="align-self-center">
                          <a href="#">Joisse Kaycee just sent a new comment!</a>
                        </div>
                      </li>
                      <li class="mb-3 pb-3 border-bottom border-separator-light d-flex">
                        <img src="img/profile/profile-2.webp" class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                        <div class="align-self-center">
                          <a href="#">New order received! It is total $147,20.</a>
                        </div>
                      </li>
                      <li class="mb-3 pb-3 border-bottom border-separator-light d-flex">
                        <img src="img/profile/profile-3.webp" class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                        <div class="align-self-center">
                          <a href="#">3 items just added to wish list by a user!</a>
                        </div>
                      </li>
                      <li class="pb-3 pb-3 border-bottom border-separator-light d-flex">
                        <img src="img/profile/profile-6.webp" class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                        <div class="align-self-center">
                          <a href="#">Kirby Peters just sent a new message!</a>
                        </div>
                      </li>
                    </ul>
                  </div>
            </div>
        </li>

      </ul>
      <!-- Icons Menu End -->


      <!-- Menu Start -->
      <div class="menu-container flex-grow-1">
        <ul id="menu" class="menu">
            

            <li>
                <a href="{{route('profile')}}" class="dropdown-item">
                    <svg width="64px" height="64px" viewBox="-24 -24 72.00 72.00" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill-rule="evenodd" clip-rule="evenodd" d="M16.5 7.063C16.5 10.258 14.57 13 12 13c-2.572 0-4.5-2.742-4.5-5.938C7.5 3.868 9.16 2 12 2s4.5 1.867 4.5 5.063zM4.102 20.142C4.487 20.6 6.145 22 12 22c5.855 0 7.512-1.4 7.898-1.857a.416.416 0 0 0 .09-.317C19.9 18.944 19.106 15 12 15s-7.9 3.944-7.989 4.826a.416.416 0 0 0 .091.317z" fill="#ffffff"></path></g></svg><span class="label">{{__('Profile')}}</span>
                </a>

            </li>
    
          @if( Gate::check('show hrm dashboard') || Gate::check('show project dashboard') || Gate::check('show account dashboard') || Gate::check('show crm dashboard') || Gate::check('show pos dashboard'))
          <li class="dash-item dash-hasmenu
                  {{ ( Request::segment(1) == null ||Request::segment(1) == 'account-dashboard' || Request::segment(1) == 'income report'
                     || Request::segment(1) == 'report' || Request::segment(1) == 'reports-monthly-cashflow' || Request::segment(1) == 'reports-quarterly-cashflow' || Request::segment(1) == 'reports-payroll' || Request::segment(1) == 'reports-leave'
                     || Request::segment(1) == 'reports-monthly-attendance' || Request::segment(1) == 'reports-lead' || Request::segment(1) == 'reports-deal'
                     || Request::segment(1) == 'pos-dashboard'|| Request::segment(1) == 'reports-warehouse' || Request::segment(1) == 'reports-daily-purchase'
                     || Request::segment(1) == 'reports-monthly-purchase' || Request::segment(1) == 'reports-daily-pos' ||Request::segment(1) == 'reports-monthly-pos' ||Request::segment(1) == 'reports-pos-vs-purchase') ?'active dash-trigger':''}}">
                  <a href="#tent" class="dash-link ">

                      <span class="dash-micon">
                        <svg fill="#ffffff" width="64px" height="64px" viewBox="-24 -24 72.00 72.00" id="dashboard-alt-3" data-name="Line Color" xmlns="http://www.w3.org/2000/svg" class="icon line-color"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><line id="secondary" x1="17" y1="7" x2="13.16" y2="12.37" style="fill: none; stroke: #100773; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></line><path id="secondary-2" data-name="secondary" d="M10,14a2,2,0,0,1,4,0" style="fill: none; stroke: #100773; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><path id="primary" d="M20,17H4a1,1,0,0,1-1-1V4A1,1,0,0,1,4,3H20a1,1,0,0,1,1,1V16A1,1,0,0,1,20,17Zm-7,0H11l-1,4h4ZM8,21h8" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></g></svg>
                      </span>
                      <span class="label">{{__('Dashboard')}}</span>
                      </a>
                      <ul id="tent" class="dash-submenu">
                          @if(\Auth::user()->show_account() == 1 && Gate::check('show account dashboard'))
                                  <li class="dash-item dash-hasmenu {{ ( Request::segment(1) == null   || Request::segment(1) == 'account-dashboard'|| Request::segment(1) == 'report' || Request::segment(1) == 'reports-monthly-cashflow' || Request::segment(1) == 'reports-quarterly-cashflow') ? ' active dash-trigger' : ''}}">
                                      <a class="dash-link" href="#tree">{{__('Accounting ')}}</a>
                                      <ul id="tree" class="dash-submenu">
                                          @can('show account dashboard')
                                              <li class="dash-item {{ ( Request::segment(1) == null || Request::segment(1) == 'account-dashboard') ? ' active' : '' }}">
                                                  <a class="dash-link" href="{{route('dashboard')}}">{{__(' Overview')}}</a>
                                              </li>
                                          @endcan
                                          @if(Gate::check('income report') || Gate::check('expense report') || Gate::check('income vs expense report') ||
                                               Gate::check('tax report')  || Gate::check('loss & profit report') || Gate::check('invoice report') ||
                                               Gate::check('bill report') || Gate::check('stock report') || Gate::check('invoice report') ||
                                               Gate::check('manage transaction')||  Gate::check('statement report'))
                                              <li class="dash-item dash-hasmenu {{(Request::segment(1) == 'report' || Request::segment(1) == 'reports-monthly-cashflow' || Request::segment(1) == 'reports-quarterly-cashflow')? 'active dash-trigger ' :''}}">
                                                  <a class="dash-link" href="#crs">{{__('Reports')}}</a>
                                                  <ul id="crs" class="dash-submenu">
                                                      @can('statement report')
                                                          <li class="dash-item {{ (Request::route()->getName() == 'report.account.statement') ? ' active' : '' }}">
                                                              <a class="dash-link" href="{{route('report.account.statement')}}">{{__('Account Statement')}}</a>
                                                          </li>
                                                      @endcan
                                                      @can('invoice report')
                                                              <li class="dash-item {{ (Request::route()->getName() == 'report.invoice.summary' ) ? ' active' : '' }}">
                                                                  <a class="dash-link" href="{{route('report.invoice.summary')}}">{{__('Invoice Summary')}}</a>
                                                              </li>

                                                          @endcan
                                                          <li class="dash-item {{ (Request::route()->getName() == 'report.sales' ) ? ' active' : '' }}">
                                                              <a class="dash-link" href="{{route('report.sales')}}">{{__('Sales Report')}}</a>
                                                          </li>
                                                          <li class="dash-item {{ (Request::route()->getName() == 'report.receivables' ) ? ' active' : '' }}">
                                                              <a class="dash-link" href="{{route('report.receivables')}}">{{__('Receivables')}}</a>
                                                          </li>
                                                          @can('bill report')
                                                              <li class="dash-item {{ (Request::route()->getName() == 'report.bill.summary' ) ? ' active' : '' }}">
                                                                  <a class="dash-link" href="{{route('report.bill.summary')}}">{{__('Bill Summary')}}</a>
                                                              </li>
                                                          @endcan
                                                          @can('stock report')
                                                              <li class="dash-item {{ (Request::route()->getName() == 'report.product.stock.report' ) ? ' active' : '' }}">
                                                                  <a href="{{route('report.product.stock.report')}}" class="dash-link">{{ __('Product Stock') }}</a>
                                                              </li>
                                                          @endcan

                                                          @can('loss & profit report')
                                                              <li class="dash-item {{ request()->is('reports-monthly-cashflow') || request()->is('reports-quarterly-cashflow') ? 'active' : '' }}">
                                                                  <a class="dash-link" href="{{route('report.monthly.cashflow')}}">{{__('Cash Flow')}}</a>
                                                              </li>
                                                          @endcan
                                                          @can('manage transaction')
                                                              <li class="dash-item {{ (Request::route()->getName() == 'transaction.index' || Request::route()->getName() == 'transfer.create' || Request::route()->getName() == 'transaction.edit') ? ' active' : '' }}">
                                                                  <a class="dash-link" href="{{ route('transaction.index') }}">{{__('Transaction')}}</a>
                                                              </li>
                                                          @endcan
                                                          @can('income report')
                                                              <li class="dash-item {{ (Request::route()->getName() == 'report.income.summary' ) ? ' active' : '' }}">
                                                                  <a class="dash-link" href="{{route('report.income.summary')}}">{{__('Income Summary')}}</a>
                                                              </li>
                                                          @endcan
                                                          @can('expense report')
                                                              <li class="dash-item {{ (Request::route()->getName() == 'report.expense.summary' ) ? ' active' : '' }}">
                                                                  <a class="dash-link" href="{{route('report.expense.summary')}}">{{__('Expense Summary')}}</a>
                                                              </li>
                                                          @endcan
                                                          @can('income vs expense report')
                                                              <li class="dash-item {{ (Request::route()->getName() == 'report.income.vs.expense.summary' ) ? ' active' : '' }}">
                                                                  <a class="dash-link" href="{{route('report.income.vs.expense.summary')}}">{{__('Income VS Expense')}}</a>
                                                              </li>
                                                          @endcan
                                                          @can('tax report')
                                                              <li class="dash-item {{ (Request::route()->getName() == 'report.tax.summary' ) ? ' active' : '' }}">
                                                                  <a class="dash-link" href="{{route('report.tax.summary')}}">{{__('Tax Summary')}}</a>
                                                              </li>
                                                          @endcan
                                                  </ul>
                                              </li>
                                          @endif
                                      </ul>
                                  </li>
                              @endif

                          @if(\Auth::user()->show_hrm() == 1)
                                  @can('show hrm dashboard')
                                      <li class="dash-item dash-hasmenu {{ ( Request::segment(1) == 'hrm-dashboard'   || Request::segment(1) == 'reports-payroll') ? ' active dash-trigger' : ''}}">
                                          <a class="dash-link" href="#game">{{__('HRM ')}}</a>
                                          <ul id="game" class="dash-submenu">
                                              <li class="dash-item {{ (\Request::route()->getName()=='hrm.dashboard') ? ' active' : '' }}">
                                                  <a class="dash-link" href="{{route('hrm.dashboard')}}">{{__(' Overview')}}</a>
                                              </li>
                                              @can('manage report')
                                                  <li class="dash-item dash-hasmenu
                                                      {{ (Request::segment(1) == 'reports-monthly-attendance' || Request::segment(1) == 'reports-leave'
                                                      || Request::segment(1) == 'reports-payroll') ? 'active dash-trigger' : ''}}"
                                                      href="#hr-report" data-toggle="collapse" role="button"
                                                      aria-expanded="{{(Request::segment(1) == 'reports-monthly-attendance' || Request::segment(1) == 'reports-leave' || Request::segment(1) == 'reports-payroll') ? 'true' : 'false'}}">
                                                      <a class="dash-link" href="#kant">{{__('Reports')}}</a>
                                                      <ul id="kant" class="dash-submenu">
                                                          <li class="dash-item {{ request()->is('reports-payroll') ? 'active' : '' }}">
                                                              <a class="dash-link" href="{{ route('report.payroll') }}">{{__('Payroll')}}</a>
                                                          </li>
                                                          <li class="dash-item {{ request()->is('reports-leave') ? 'active' : '' }}">
                                                              <a class="dash-link" href="{{ route('report.leave') }}">{{__('Leave')}}</a>
                                                          </li>
                                                          <li class="dash-item {{ request()->is('reports-monthly-attendance') ? 'active' : '' }}">
                                                              <a class="dash-link" href="{{ route('report.monthly.attendance') }}">{{ __('Monthly Attendance') }}</a>
                                                          </li>
                                                      </ul>
                                                  </li>
                                              @endcan
                                          </ul>
                                      </li>
                                  @endcan
                              @endif

                              @if(\Auth::user()->show_crm() == 1)
                                  @can('show crm dashboard')
                                      <li class="dash-item dash-hasmenu {{ ( Request::segment(1) == 'crm-dashboard' || Request::segment(1) == 'reports-lead' || Request::segment(1) == 'reports-deal') ? ' active dash-trigger' : ''}}">
                                          <a class="dash-link" href="#trm">{{__('CRM')}}</a>
                                           <ul id="trm" class="dash-submenu">
                                               <li class="dash-item {{ (\Request::route()->getName()=='crm.dashboard') ? ' active' : '' }}">
                                                  <a class="dash-link" href="{{route('crm.dashboard')}}">{{__(' Overview')}}</a>
                                              </li>
                                               <li class="dash-item dash-hasmenu {{ ( Request::segment(1) == 'reports-lead' || Request::segment(1) == 'reports-deal') ? 'active dash-trigger' : ''}}"
                                                      href="#crm-report" data-toggle="collapse" role="button"
                                                      aria-expanded="{{( Request::segment(1) == 'reports-lead' || Request::segment(1) == 'reports-deal') ? 'true' : 'false'}}">
                                                  <a class="dash-link" href="#repo">{{__('Reports')}}</a>
                                                  <ul id="repo" class="dash-submenu">
                                                      <li class="dash-item {{ request()->is('reports-lead') ? 'active' : '' }}">
                                                          <a class="dash-link" href="{{ route('report.lead') }}">{{__('Lead')}}</a>
                                                      </li>
                                                      <li class="dash-item {{ request()->is('reports-deal') ? 'active' : '' }}">
                                                          <a class="dash-link" href="{{ route('report.deal') }}">{{__('Deal')}}</a>
                                                      </li>
                                                  </ul>
                                              </li>
                                           </ul>
                                      </li>
                                  @endcan
                              @endif

                              @if(\Auth::user()->show_project() == 1)
                                  @can('show project dashboard')
                                      <li class="dash-item {{ (Request::route()->getName() == 'project.dashboard') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('project.dashboard')}}">{{__('Project ')}}</a>
                                      </li>
                                  @endcan
                              @endif

                              @if(\Auth::user()->show_pos() == 1)
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
                                                          <a class="dash-link" href="{{ route('report.warehouse') }}">{{__('Warehouse Report')}}</a>
                                                      </li>
                                                      <li class="dash-item {{ request()->is('reports-daily-purchase') || request()->is('reports-monthly-purchase') ? 'active' : '' }}">
                                                          <a class="dash-link" href="{{ route('report.daily.purchase') }}">{{__('Purchase Daily/Monthly Report')}}</a>
                                                      </li>
                                                      <li class="dash-item {{ request()->is('reports-daily-pos') || request()->is('reports-monthly-pos') ? 'active' : '' }}">
                                                          <a class="dash-link" href="{{ route('report.daily.pos') }}">{{__('POS Daily/Monthly Report')}}</a>
                                                      </li>
                                                      <li class="dash-item {{ request()->is('reports-pos-vs-purchase') ? 'active' : '' }}">
                                                          <a class="dash-link" href="{{ route('report.pos.vs.purchase') }}">{{__('Pos VS Purchase Report')}}</a>
                                                      </li>
                                                  </ul>
                                              </li>
                                          </ul>
                                      </li>
                                  @endcan
                              @endif

                      </ul>
          </li>
        @endif



         
          @if(\Auth::user()->show_hrm() == 1)
          @if( Gate::check('manage employee') || Gate::check('manage setsalary'))
              <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'holiday-calender'
                  || Request::segment(1) == 'leavetype' || Request::segment(1) == 'leave' ||
                  Request::segment(1) == 'attendanceemployee' || Request::segment(1) == 'document-upload' || Request::segment(1) == 'document' || Request::segment(1) == 'performanceType'  ||
                  Request::segment(1) == 'branch' || Request::segment(1) == 'department' || Request::segment(1) == 'designation' || Request::segment(1) == 'employee'
                  || Request::segment(1) == 'leave_requests' || Request::segment(1) == 'holidays' || Request::segment(1) == 'policies' || Request::segment(1) == 'leave_calender'
                  || Request::segment(1) == 'award' || Request::segment(1) == 'transfer' || Request::segment(1) == 'resignation' || Request::segment(1) == 'training' || Request::segment(1) == 'travel' ||
                  Request::segment(1) == 'promotion' || Request::segment(1) == 'complaint' || Request::segment(1) == 'warning'
                  || Request::segment(1) == 'termination' || Request::segment(1) == 'announcement' || Request::segment(1) == 'job' || Request::segment(1) == 'job-application' ||
                  Request::segment(1) == 'candidates-job-applications' || Request::segment(1) == 'job-onboard' || Request::segment(1) == 'custom-question'
                  || Request::segment(1) == 'interview-schedule' || Request::segment(1) == 'career' || Request::segment(1) == 'holiday' || Request::segment(1) == 'setsalary' ||
                  Request::segment(1) == 'payslip' || Request::segment(1) == 'paysliptype' || Request::segment(1) == 'company-policy' || Request::segment(1) == 'job-stage'
                  || Request::segment(1) == 'job-category' || Request::segment(1) == 'terminationtype' || Request::segment(1) == 'awardtype' || Request::segment(1) == 'trainingtype' ||
                  Request::segment(1) == 'goaltype' || Request::segment(1) == 'paysliptype' || Request::segment(1) == 'allowanceoption' || Request::segment(1) == 'competencies' || Request::segment(1) == 'loanoption'
                  || Request::segment(1) == 'deductionoption')?'active dash-trigger':''}}">
                  <a href="#rm" class="dash-link ">
                      <span class="dash-micon">
                        <svg height="64px" width="64px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-512 -512 1536.00 1536.00" xml:space="preserve" fill="#100773" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <polygon style="fill:#FFFFFF;" points="29.984,218.088 128.232,218.088 128.232,12 383.768,12 383.768,218.088 482.016,218.088 256,493.104 "></polygon> <path style="fill:#100773;" d="M371.768,24v182.088v24h24h60.848L256,474.2L55.376,230.088h60.856h24v-24V24H371.768 M395.768,0 H116.232v206.088H4.592L256,512l251.408-305.912h-111.64V0L395.768,0z"></path> <circle cx="256" cy="108.76" r="20.44"></circle> <path d="M278.816,137.376h-45.64c-16.088,0-29.192,13.096-29.192,29.192v57.24c0,5.864,4.752,10.616,10.616,10.616 s10.616-4.752,10.616-10.616v-51.336h6.368v48.4c0,0.024-0.008,0.048-0.008,0.064v108.216c0,5.864,4.752,10.616,10.616,10.616 s10.616-4.752,10.616-10.616V242.08h6.368v87.064c0,5.864,4.752,10.616,10.616,10.616c5.864,0,10.616-4.752,10.616-10.616V242.08 v-21.16v-48.464h6.376v51.336c0,5.864,4.752,10.616,10.616,10.616c5.864,0,10.616-4.752,10.616-10.616V166.56 C308.008,150.472,294.912,137.376,278.816,137.376z"></path> </g></svg>
                      </span>
                      <span class="label">
                          {{__('HRM System')}}
                      </span>
              
                  </a>
                  <ul id="rm" class="dash-submenu">
                      <li class="dash-item  {{ (Request::segment(1) == 'employee' ? 'active dash-trigger' : '')}}   ">
                          @if(\Auth::user()->type =='Employee')
                              @php
                                  $employee=App\Models\Employee::where('user_id',\Auth::user()->id)->first();
                              @endphp
                              <a class="dash-link" href="{{route('employee.show',\Illuminate\Support\Facades\Crypt::encrypt($employee->id))}}">{{__('Employee')}}</a>
                          @else
                              <a href="{{route('employee.index')}}" class="dash-link">
                                  {{ __('Employee Setup') }}
                              </a>
                          @endif
                      </li>

                      @if( Gate::check('manage set salary') || Gate::check('manage pay slip'))
                          <li class="dash-item dash-hasmenu  {{ (Request::segment(1) == 'setsalary' || Request::segment(1) == 'payslip') ? 'active dash-trigger' : ''}}">
                          <a class="dash-link" href="#payroll">{{__('Payroll Setup')}}</a>
                          <ul id="payroll" class="dash-submenu">
                              @can('manage set salary')
                                  <li class="dash-item {{ (request()->is('setsalary*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{ route('setsalary.index') }}">{{__('Set salary')}}</a>
                                  </li>
                              @endcan
                              @can('manage pay slip')
                                  <li class="dash-item {{ (request()->is('payslip*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('payslip.index')}}">{{__('Payslip')}}</a>
                                  </li>
                              @endcan
                          </ul>
                      </li>
                      @endif

                      @if( Gate::check('manage leave') || Gate::check('manage attendance'))
                          <li class="dash-item dash-hasmenu  {{ (Request::segment(1) == 'leave' || Request::segment(1) == 'attendanceemployee') ? 'active dash-trigger' :''}}">
                          <a class="dash-link" href="#leave">{{__('Leave Management Setup')}}<span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                          <ul id="leave" class="dash-submenu">
                              @can('manage leave')
                                  <li class="dash-item {{ (Request::route()->getName() == 'leave.index') ?'active' :''}}">
                                      <a class="dash-link" href="{{route('leave.index')}}">{{__('Manage Leave')}}</a>
                                  </li>
                              @endcan
                              @can('manage attendance')
                                  <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'attendanceemployee') ? 'active dash-trigger' : ''}}" href="#navbar-attendance" data-toggle="collapse" role="button" aria-expanded="{{ (Request::segment(1) == 'attendanceemployee') ? 'true' : 'false'}}">
                                      <a class="dash-link" href="#tender">{{__('Attendance')}}</a>
                                      <ul id="tender" class="dash-submenu">
                                          <li class="dash-item {{ (Request::route()->getName() == 'attendanceemployee.index' ? 'active' : '')}}">
                                              <a class="dash-link" href="{{route('attendanceemployee.index')}}">{{__('Mark Attendance')}}</a>
                                          </li>
                                          @can('create attendance')
                                              <li class="dash-item {{ (Request::route()->getName() == 'attendanceemployee.bulkattendance' ? 'active' : '')}}">
                                                  <a class="dash-link" href="{{ route('attendanceemployee.bulkattendance') }}">{{__('Bulk Attendance')}}</a>
                                              </li>
                                          @endcan
                                      </ul>
                                  </li>
                              @endcan
                          </ul>
                      </li>
                      @endif

                      @if( Gate::check('manage indicator') || Gate::check('manage appraisal') || Gate::check('manage goal tracking'))
                          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'indicator' || Request::segment(1) == 'appraisal' || Request::segment(1) == 'goaltracking') ? 'active dash-trigger' : ''}}" href="#navbar-performance" data-toggle="collapse" role="button" aria-expanded="{{ (Request::segment(1) == 'indicator' || Request::segment(1) == 'appraisal' || Request::segment(1) == 'goaltracking') ? 'true' : 'false'}}">
                          <a class="dash-link" href="#performance">{{__('Performance Setup')}}</a>
                          <ul id="performance" class="dash-submenu {{ (Request::segment(1) == 'indicator' || Request::segment(1) == 'appraisal' || Request::segment(1) == 'goaltracking') ? 'show' : 'collapse'}}">
                              @can('manage indicator')
                                  <li class="dash-item {{ (request()->is('indicator*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('indicator.index')}}">{{__('Indicator')}}</a>
                                  </li>
                              @endcan
                              @can('manage appraisal')
                                  <li class="dash-item {{ (request()->is('appraisal*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('appraisal.index')}}">{{__('Appraisal')}}</a>
                                  </li>
                              @endcan
                              @can('manage goal tracking')
                                  <li class="dash-item  {{ (request()->is('goaltracking*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('goaltracking.index')}}">{{__('Goal Tracking')}}</a>
                                  </li>
                              @endcan
                          </ul>
                      </li>
                      @endif

                      @if( Gate::check('manage training') || Gate::check('manage trainer') || Gate::check('show training'))
                          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'trainer' || Request::segment(1) == 'training') ? 'active dash-trigger' : ''}}" href="#navbar-training" data-toggle="collapse" role="button" aria-expanded="{{ (Request::segment(1) == 'trainer' || Request::segment(1) == 'training') ? 'true' : 'false'}}">
                          <a class="dash-link" href="#training">{{__('Training Setup')}}</a>
                          <ul id="training" class="dash-submenu">
                              @can('manage training')
                                  <li class="dash-item {{ (request()->is('training*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('training.index')}}">{{__('Training List')}}</a>
                                  </li>
                              @endcan
                              @can('manage trainer')
                                  <li class="dash-item {{ (request()->is('trainer*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('trainer.index')}}">{{__('Trainer')}}</a>
                                  </li>
                              @endcan

                          </ul>
                      </li>
                      @endif

                      @if( Gate::check('manage job') || Gate::check('create job') || Gate::check('manage job application') || Gate::check('manage custom question') || Gate::check('show interview schedule') || Gate::check('show career'))
                          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'job' || Request::segment(1) == 'job-application' || Request::segment(1) == 'candidates-job-applications' || Request::segment(1) == 'job-onboard' || Request::segment(1) == 'custom-question' || Request::segment(1) == 'interview-schedule' || Request::segment(1) == 'career') ? 'active dash-trigger' : ''}}    ">
                          <a class="dash-link" href="#setup">{{__('Recruitment Setup')}}</a>
                          <ul id="setup" class="dash-submenu">
                              @can('manage job')
                                  <li class="dash-item {{ (Request::route()->getName() == 'job.index' || Request::route()->getName() == 'job.create' || Request::route()->getName() == 'job.edit' || Request::route()->getName() == 'job.show'   ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('job.index')}}">{{__('Jobs')}}</a>
                                  </li>
                              @endcan
                              @can('create job')
                                  <li class="dash-item {{ ( Request::route()->getName() == 'job.create' ? 'active' : '')}} ">
                                      <a class="dash-link" href="{{route('job.create')}}">{{__('Job Create')}}</a>
                                  </li>
                              @endcan
                              @can('manage job application')
                                  <li class="dash-item {{ (request()->is('job-application*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('job-application.index')}}">{{__('Job Application')}}</a>
                                  </li>
                              @endcan
                              @can('manage job application')
                                  <li class="dash-item {{ (request()->is('candidates-job-applications') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('job.application.candidate')}}">{{__('Job Candidate')}}</a>
                                  </li>
                              @endcan
                              @can('manage job application')
                                  <li class="dash-item {{ (request()->is('job-onboard*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('job.on.board')}}">{{__('Job On-boarding')}}</a>
                                  </li>
                              @endcan
                              @can('manage custom question')
                                  <li class="dash-item  {{ (request()->is('custom-question*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('custom-question.index')}}">{{__('Custom Question')}}</a>
                                  </li>
                              @endcan
                              @can('show interview schedule')
                                  <li class="dash-item {{ (request()->is('interview-schedule*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('interview-schedule.index')}}">{{__('Interview Schedule')}}</a>
                                  </li>
                              @endcan
                              @can('show career')
                                  <li class="dash-item {{ (request()->is('career*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('career',[\Auth::user()->creatorId(),$lang])}}">{{__('Career')}}</a>
                                  </li>
                              @endcan
                          </ul>
                      </li>
                      @endif

                      @if( Gate::check('manage award') || Gate::check('manage transfer') || Gate::check('manage resignation') || Gate::check('manage travel') || Gate::check('manage promotion') || Gate::check('manage complaint') || Gate::check('manage warning') || Gate::check('manage termination') || Gate::check('manage announcement') || Gate::check('manage holiday') )
                          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'holiday-calender' || Request::segment(1) == 'holiday' || Request::segment(1) == 'policies' || Request::segment(1) == 'award' || Request::segment(1) == 'transfer' || Request::segment(1) == 'resignation' || Request::segment(1) == 'travel' || Request::segment(1) == 'promotion' || Request::segment(1) == 'complaint' || Request::segment(1) == 'warning' || Request::segment(1) == 'termination' || Request::segment(1) == 'announcement' || Request::segment(1) == 'competencies' ) ? 'active dash-trigger' : ''}}">
                          <a class="dash-link" href="#hr">{{__('HR Admin Setup')}}</a>
                          <ul id="hr" class="dash-submenu">
                              @can('manage award')
                                  <li class="dash-item {{ (request()->is('award*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('award.index')}}">{{__('Award')}}</a>
                                  </li>
                              @endcan
                              @can('manage transfer')
                                  <li class="dash-item  {{ (request()->is('transfer*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('transfer.index')}}">{{__('Transfer')}}</a>
                                  </li>
                              @endcan
                              @can('manage resignation')
                                  <li class="dash-item {{ (request()->is('resignation*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('resignation.index')}}">{{__('Resignation')}}</a>
                                  </li>
                              @endcan
                              @can('manage travel')
                                  <li class="dash-item {{ (request()->is('travel*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('travel.index')}}">{{__('Trip')}}</a>
                                  </li>
                              @endcan
                              @can('manage promotion')
                                  <li class="dash-item {{ (request()->is('promotion*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('promotion.index')}}">{{__('Promotion')}}</a>
                                  </li>
                              @endcan
                              @can('manage complaint')
                                  <li class="dash-item {{ (request()->is('complaint*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('complaint.index')}}">{{__('Complaints')}}</a>
                                  </li>
                              @endcan
                              @can('manage warning')
                                  <li class="dash-item {{ (request()->is('warning*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('warning.index')}}">{{__('Warning')}}</a>
                                  </li>
                              @endcan
                              @can('manage termination')
                                  <li class="dash-item {{ (request()->is('termination*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('termination.index')}}">{{__('Termination')}}</a>
                                  </li>
                              @endcan
                              @can('manage announcement')
                                  <li class="dash-item {{ (request()->is('announcement*') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('announcement.index')}}">{{__('Announcement')}}</a>
                                  </li>
                              @endcan
                              @can('manage holiday')
                                  <li class="dash-item {{ (request()->is('holiday*') || request()->is('holiday-calender') ? 'active' : '')}}">
                                      <a class="dash-link" href="{{route('holiday.index')}}">{{__('Holidays')}}</a>
                                  </li>
                              @endcan
                          </ul>
                      </li>
                      @endif

                      @can('manage event')
                          <li class="dash-item {{ (request()->is('event*') ? 'active' : '')}}">
                              <a class="dash-link" href="{{route('event.index')}}">{{__('Event Setup')}}</a>
                          </li>
                      @endcan
                      @can('manage meeting')
                          <li class="dash-item {{ (request()->is('meeting*') ? 'active' : '')}}">
                              <a class="dash-link" href="{{route('meeting.index')}}">{{__('Meeting')}}</a>
                          </li>
                      @endcan
                      @can('manage assets')
                          <li class="dash-item {{ (request()->is('account-assets*') ? 'active' : '')}}">
                              <a class="dash-link" href="{{route('account-assets.index')}}">{{__('Employees Asset Setup ')}}</a>
                          </li>
                      @endcan
                      @can('manage document')
                          <li class="dash-item {{ (request()->is('document-upload*') ? 'active' : '')}}">
                              <a class="dash-link" href="{{route('document-upload.index')}}">{{__('Document Setup')}}</a>
                          </li>
                      @endcan
                      @can('manage company policy')
                          <li class="dash-item {{ (request()->is('company-policy*') ? 'active' : '')}}">
                              <a class="dash-link" href="{{route('company-policy.index')}}">{{__('Company policy')}}</a>
                          </li>
                      @endcan

                      @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'HR')
                      <li class="dash-item {{ (Request::segment(1) == 'leavetype' || Request::segment(1) == 'document' || Request::segment(1) == 'performanceType'
                                              || Request::segment(1) == 'branch' || Request::segment(1) == 'department'
                                              || Request::segment(1) == 'designation' || Request::segment(1) == 'job-stage'
                                              || Request::segment(1) == 'performanceType'  || Request::segment(1) == 'job-category'
                                              || Request::segment(1) == 'terminationtype' || Request::segment(1) == 'awardtype'
                                              || Request::segment(1) == 'trainingtype' || Request::segment(1) == 'goaltype'
                                              || Request::segment(1) == 'paysliptype' || Request::segment(1) == 'allowanceoption'
                                              || Request::segment(1) == 'loanoption' || Request::segment(1) == 'deductionoption') ? 'active dash-trigger' : ''}}">
                          <a class="dash-link" href="{{route('branch.index')}}">{{__('HRM System Setup')}}</a>
                      </li>
                      @endcan


                  </ul>
              </li>
          @endif
      @endif






          @if(\Auth::user()->show_account() == 1)
          @if( Gate::check('manage customer') || Gate::check('manage vender') || Gate::check('manage customer') || Gate::check('manage vender') ||
               Gate::check('manage proposal') ||  Gate::check('manage bank account') ||  Gate::check('manage bank transfer') ||  Gate::check('manage invoice')
               ||  Gate::check('manage revenue') ||  Gate::check('manage credit note') ||  Gate::check('manage bill')  ||  Gate::check('manage payment') ||
                Gate::check('manage debit note') || Gate::check('manage chart of account') ||  Gate::check('manage journal entry') ||   Gate::check('balance sheet report')
                || Gate::check('ledger report') ||  Gate::check('trial balance report')  )
                  <li class="dash-item dash-hasmenu
                           {{ (Request::route()->getName() == 'print-setting' || Request::segment(1) == 'customer' || Request::segment(1) == 'vender' || Request::segment(1) == 'proposal' || Request::segment(1) == 'bank-account'
                              || Request::segment(1) == 'bank-transfer' || Request::segment(1) == 'invoice' || Request::segment(1) == 'revenue'
                              || Request::segment(1) == 'credit-note' || Request::segment(1) == 'taxes' || Request::segment(1) == 'product-category'
                              || Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field'
                              || Request::segment(1) == 'chart-of-account-type' || ( Request::segment(1) == 'transaction') &&  Request::segment(2) != 'ledger'
                               &&  Request::segment(2) != 'balance-sheet' &&  Request::segment(2) != 'trial-balance' || Request::segment(1) == 'goal'
                               || Request::segment(1) == 'budget'|| Request::segment(1) == 'chart-of-account' || Request::segment(1) == 'journal-entry'
                               || Request::segment(2) == 'ledger' ||  Request::segment(2) == 'balance-sheet' ||  Request::segment(2) == 'trial-balance' ||Request::segment(2) == 'profit-loss'
                               ||Request::segment(1) == 'bill' ||Request::segment(1) == 'expense' || Request::segment(1) == 'payment' || Request::segment(1) == 'debit-note')?' active dash-trigger':''}}">
                              <a href="#accounting" class="dash-link"><span class="dash-micon"><svg fill="#ffffff" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-24 -24 72.00 72.00" xml:space="preserve" width="64px" height="64px" stroke="#ffffff" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <style type="text/css"> .st0{fill:none;} </style> <g id="surface1"> <path d="M10.5,2C9.8,2,9.3,2.4,9.1,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h7c0,0.6,0.4,1,1,1h7c0.6,0,1-0.4,1-1V11 c0-0.6-0.4-1-1-1h-2V5c0-1.1-0.9-2-2-2h-4.1C11.7,2.4,11.2,2,10.5,2z M10.5,3C10.8,3,11,3.2,11,3.5S10.8,4,10.5,4S10,3.8,10,3.5 S10.2,3,10.5,3z M5.5,5H8v1h5V5h2.5C15.8,5,16,5.2,16,5.5V10h-3c-0.6,0-1,0.4-1,1v8H5.5C5.2,19,5,18.8,5,18.5v-13 C5,5.2,5.2,5,5.5,5z M10.6,8.4h-4v2h4V8.4z M10.6,11.4h-4v2h4V11.4z M10.6,14.5h-4v2h4V14.5z M14,12h5v2h-5V12z M14,15h2v2h-2V15z M17,15h2v2h-2V15z M14,18h2v2h-2V18z M17,18h2v2h-2V18z"></path> </g> <rect class="st0" width="24" height="24"></rect> </g></svg></span><span class="label">{{__('Accounting System ')}}
                                  </span>
                              </a>
                          <ul id="accounting" class="dash-submenu">

                          @if( Gate::check('manage bank account') ||  Gate::check('manage bank transfer'))
                              <li class="dash-item dash-hasmenu {{(Request::segment(1) == 'bank-account' || Request::segment(1) == 'bank-transfer')? 'active dash-trigger' :''}}">
                                  <a class="dash-link" href="#banking">{{__('Banking')}}</a>
                                  <ul id="banking" class="dash-submenu">
                                      <li class="dash-item {{ (Request::route()->getName() == 'bank-account.index' || Request::route()->getName() == 'bank-account.create' || Request::route()->getName() == 'bank-account.edit') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{ route('bank-account.index') }}">{{__('Account')}}</a>
                                      </li>
                                      <li class="dash-item {{ (Request::route()->getName() == 'bank-transfer.index' || Request::route()->getName() == 'bank-transfer.create' || Request::route()->getName() == 'bank-transfer.edit') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('bank-transfer.index')}}">{{__('Transfer')}}</a>
                                      </li>
                                  </ul>
                              </li>
                          @endif
                          @if( Gate::check('manage customer') ||  Gate::check('manage proposal') ||  Gate::check('manage invoice') ||   Gate::check('manage revenue') ||  Gate::check('manage credit note'))
                              <li class="dash-item dash-hasmenu {{(Request::segment(1) == 'customer' || Request::segment(1) == 'proposal' || Request::segment(1) == 'invoice' || Request::segment(1) == 'revenue' || Request::segment(1) == 'credit-note')? 'active dash-trigger' :''}}">
                                  <a class="dash-link" href="#sales">{{__('Sales')}}</a>
                                  <ul id="sales" class="dash-submenu">
                                      @if(Gate::check('manage customer'))
                                          <li class="dash-item {{ (Request::segment(1) == 'customer')?'active':''}}">
                                              <a class="dash-link" href="{{route('customer.index')}}">{{__('Customer')}}</a>
                                          </li>
                                      @endif
                                          @if(Gate::check('manage proposal'))
                                              <li class="dash-item {{ (Request::segment(1) == 'proposal')?'active':''}}">
                                                  <a class="dash-link" href="{{ route('proposal.index') }}">{{__('Estimate')}}</a>
                                              </li>
                                          @endif
                                      <li class="dash-item {{ (Request::route()->getName() == 'invoice.index' || Request::route()->getName() == 'invoice.create' || Request::route()->getName() == 'invoice.edit' || Request::route()->getName() == 'invoice.show') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{ route('invoice.index') }}">{{__('Invoice')}}</a>
                                      </li>
                                      <li class="dash-item {{ (Request::route()->getName() == 'revenue.index' || Request::route()->getName() == 'revenue.create' || Request::route()->getName() == 'revenue.edit') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('revenue.index')}}">{{__('Revenue')}}</a>
                                      </li>
                                      <li class="dash-item {{ (Request::route()->getName() == 'credit.note' ) ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('credit.note')}}">{{__('Credit Note')}}</a>
                                      </li>
                                  </ul>
                              </li>
                          @endif
                          @if( Gate::check('manage vender') || Gate::check('manage bill')  ||  Gate::check('manage payment') ||  Gate::check('manage debit note'))
                              <li class="dash-item dash-hasmenu {{(Request::segment(1) == 'bill' || Request::segment(1) == 'vender' || Request::segment(1) == 'expense' || Request::segment(1) == 'payment' || Request::segment(1) == 'debit-note')? 'active dash-trigger' :''}}">
                                  <a class="dash-link" href="#purchase">{{__('Purchases')}}</a>
                                  <ul id="purchase" class="dash-submenu">
                                      @if(Gate::check('manage vender'))
                                          <li class="dash-item {{ (Request::segment(1) == 'vender')?'active':''}}">
                                              <a class="dash-link" href="{{ route('vender.index') }}">{{__('Suppiler')}}</a>
                                          </li>
                                      @endif
                                      <li class="dash-item {{ (Request::route()->getName() == 'bill.index' || Request::route()->getName() == 'bill.create' || Request::route()->getName() == 'bill.edit' || Request::route()->getName() == 'bill.show') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{ route('bill.index') }}">{{__('Bill')}}</a>
                                      </li>
                                      <li class="dash-item {{ (Request::route()->getName() == 'expense.index' || Request::route()->getName() == 'expense.create' || Request::route()->getName() == 'expense.edit' || Request::route()->getName() == 'expense.show') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{ route('expense.index') }}">{{__('Expense')}}</a>
                                      </li>
                                      <li class="dash-item {{ (Request::route()->getName() == 'payment.index' || Request::route()->getName() == 'payment.create' || Request::route()->getName() == 'payment.edit') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('payment.index')}}">{{__('Payment')}}</a>
                                      </li>
                                      <li class="dash-item  {{ (Request::route()->getName() == 'debit.note' ) ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('debit.note')}}">{{__('Debit Note')}}</a>
                                      </li>
                                  </ul>
                              </li>
                          @endif
                          @if( Gate::check('manage chart of account') ||  Gate::check('manage journal entry') ||  Gate::check('balance sheet report')
                          ||  Gate::check('ledger report') ||  Gate::check('trial balance report'))
                              <li class="dash-item dash-hasmenu {{(Request::segment(1) == 'chart-of-account' || Request::segment(1) == 'journal-entry' || Request::segment(2) == 'profit-loss' 
                                  || Request::segment(2) == 'ledger' ||  Request::segment(2) == 'balance-sheet' ||  Request::segment(2) == 'trial-balance')? 'active dash-trigger' :''}}">
                                  <a class="dash-link" href="#double">{{__('Double Entry')}}</a>
                                  <ul id="double" class="dash-submenu">
                                      <li class="dash-item {{ (Request::route()->getName() == 'chart-of-account.index' || Request::route()->getName() == 'chart-of-account.show') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{ route('chart-of-account.index') }}">{{__('Chart of Accounts')}}</a>
                                      </li>
                                      <li class="dash-item {{ (Request::route()->getName() == 'journal-entry.edit' || Request::route()->getName() == 'journal-entry.create'
                                              || Request::route()->getName() == 'journal-entry.index' || Request::route()->getName() == 'journal-entry.show') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{ route('journal-entry.index') }}">{{__('Journal Account')}}</a>
                                      </li>
                                      <li class="dash-item {{ (Request::route()->getName() == 'report.ledger') ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('report.ledger',0)}}">{{__('Ledger Summary')}}</a>
                                      </li>
                                      <li class="dash-item {{ (Request::route()->getName() == 'report.balance.sheet' ) ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('report.balance.sheet')}}">{{__('Balance Sheet')}}</a>
                                      </li>
                                      <li class="dash-item {{ (Request::route()->getName() == 'report.profit.loss' ) ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('report.profit.loss')}}">{{__('Profit & Loss')}}</a>
                                      </li>

                                      <li class="dash-item {{ (Request::route()->getName() == 'trial.balance' ) ? ' active' : '' }}">
                                          <a class="dash-link" href="{{route('trial.balance')}}">{{__('Trial Balance')}}</a>
                                      </li>
                                  </ul>
                              </li>
                          @endif
                          @if(\Auth::user()->type =='company')
                              <li class="dash-item {{ (Request::segment(1) == 'budget')?'active':''}}">
                                  <a class="dash-link" href="{{ route('budget.index') }}">{{__('Budget Planner')}}</a>
                              </li>
                          @endif
                          @if(Gate::check('manage goal'))
                              <li class="dash-item {{ (Request::segment(1) == 'goal')?'active':''}}">
                                  <a class="dash-link" href="{{ route('goal.index') }}">{{__('Financial Goal')}}</a>
                              </li>
                          @endif
                          @if(Gate::check('manage constant tax') || Gate::check('manage constant category') ||Gate::check('manage constant unit') ||Gate::check('manage constant payment method') ||Gate::check('manage constant custom field') )
                              <li class="dash-item {{(Request::segment(1) == 'taxes' || Request::segment(1) == 'product-category' || Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' || Request::segment(1) == 'chart-of-account-type')? 'active dash-trigger' :''}}">
                                  <a class="dash-link" href="{{ route('taxes.index') }}">{{__('Accounting Setup')}}</a>
                              </li>
                          @endif

                          @if(Gate::check('manage print settings'))
                              <li class="dash-item {{ (Request::route()->getName() == 'print-setting') ? ' active' : '' }}">
                                  <a class="dash-link" href="{{ route('print.setting') }}">{{__('Print Settings')}}</a>
                              </li>
                          @endif

                      </ul>
                  </li>
              @endif
          @endif


          
          @if( Gate::check('view space') || Gate::check('view spacetype') )
          <li class="dash-item dash-hasmenu
                  {{ (Request::route()->getName() == 'spacetype' || Request::segment(1) == 'customer'  || Request::segment(1) == 'debit-note')?' active dash-trigger':''}}">
                      <a href="#workspace" class="dash-link"><span class="dash-micon"><i class="ti ti-box"></i></span><span class="label">{{__('Workspace')}}
                          </span>
                      </a>
                  <ul id="workspace" class="dash-submenu">

                  @can('view spacetype')
                      <li class="dash-item {{ (Request::segment(1) == 'spacetype')?'active':''}}">
                          <a class="dash-link" href="{{ route('spacetype.index') }}">{{__('Spacetype')}}</a>
                      </li>
                  @endcan
                  @can('view space')
                      <li class="dash-item {{ (Request::segment(1) == 'space')?'active':''}}">
                          <a class="dash-link" href="{{ route('space.index') }}">{{__('Space')}}</a>
                      </li>
                  @endcan
                  @can('view chair')
                      <li class="dash-item {{ (Request::segment(1) == 'chair')?'active':''}}">
                          <a class="dash-link" href="{{ route('chair.index') }}">{{__('Chair')}}</a>
                      </li>
                  @endcan
              
              </ul>
          </li>
      @endif

          @if(\Auth::user()->show_crm() == 1)
          @if( Gate::check('manage lead') || Gate::check('manage deal') || Gate::check('manage form builder') || Gate::check('manage contract'))
              <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'pipelines' || Request::segment(1) == 'deals' || Request::segment(1) == 'leads'  || Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response' || Request::segment(1) == 'contract')?' active dash-trigger':''}}">
                  <a href="#crm" class="dash-link"
                  ><span class="dash-micon"><svg fill="#ffffff" xmlns="http://www.w3.org/2000/svg" width="64px" height="64px" viewBox="-100 -100 300.00 300.00" enable-background="new 0 0 100 100" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <ellipse cx="41.3" cy="42.3" rx="12.2" ry="13.5"></ellipse> <path d="M52.6,57.4c-3.1,2.8-7,4.5-11.3,4.5c-4.3,0-8.3-1.7-11.3-4.6c-5.5,2.5-11,5.7-11,10.7v2.1 c0,2.5,2,4.5,4.5,4.5h35.7c2.5,0,4.5-2,4.5-4.5v-2.1C63.6,63,58.2,59.9,52.6,57.4z"></path> <path d="M68,47.4c-0.2-0.1-0.3-0.2-0.5-0.3c-0.4-0.2-0.9-0.2-1.3,0.1c-2.1,1.3-4.6,2.1-7.2,2.1c-0.3,0-0.7,0-1,0 c-0.5,1.3-1,2.6-1.7,3.7c0.4,0.2,0.9,0.3,1.4,0.6c5.7,2.5,9.7,5.6,12.5,9.8H75c2.2,0,4-1.8,4-4v-1.9C79,52.6,73.3,49.6,68,47.4z"></path> <path d="M66.9,34.2c0-4.9-3.6-8.9-7.9-8.9c-2.2,0-4.1,1-5.6,2.5c3.5,3.6,5.7,8.7,5.7,14.4c0,0.3,0,0.5,0,0.8 C63.4,43,66.9,39.1,66.9,34.2z"></path> </g></svg></span
                      ><span class="label">{{__('CRM System')}}</span
                      ></a>
                  <ul id="crm" class="dash-submenu {{ (Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'leads'  || Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response' || Request::segment(1) == 'deals' || Request::segment(1) == 'pipelines')?'show':''}}">
                      @can('manage lead')
                          <li class="dash-item {{ (Request::route()->getName() == 'leads.list' || Request::route()->getName() == 'leads.index' || Request::route()->getName() == 'leads.show') ? ' active' : '' }}">
                              <a class="dash-link" href="{{ route('leads.index') }}">{{__('Leads')}}</a>
                          </li>
                      @endcan
                      @can('manage deal')
                          <li class="dash-item {{ (Request::route()->getName() == 'deals.list' || Request::route()->getName() == 'deals.index' || Request::route()->getName() == 'deals.show') ? ' active' : '' }}">
                              <a class="dash-link" href="{{route('deals.index')}}">{{__('Deals')}}</a>
                          </li>
                      @endcan
                      @can('manage form builder')
                          <li class="dash-item {{ (Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response')?'active open':''}}">
                              <a class="dash-link" href="{{route('form_builder.index')}}">{{__('Form Builder')}}</a>
                          </li>
                      @endcan
                      @can('manage contract')
                          <li class="dash-item  {{ (Request::route()->getName() == 'contract.index' || Request::route()->getName() == 'contract.show')?'active':''}}">
                              <a class="dash-link" href="{{route('contract.index')}}">{{__('Contract')}}</a>
                          </li>
                      @endif
                      @if(Gate::check('manage lead stage') || Gate::check('manage pipeline') ||Gate::check('manage source') ||Gate::check('manage label') || Gate::check('manage stage'))
                          <li class="dash-item  {{(Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'pipelines' || Request::segment(1) == 'product-category' || Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' || Request::segment(1) == 'chart-of-account-type')? 'active dash-trigger' :''}}">
                              <a class="dash-link" href="{{ route('pipelines.index') }}   ">{{__('CRM System Setup')}}</a>

                          </li>
                      @endif
                  </ul>
              </li>
          @endif
      @endif

          


          
          @if(\Auth::user()->show_project() == 1)
          @if( Gate::check('manage project'))
              <li class="dash-item dash-hasmenu
                              {{ ( Request::segment(1) == 'project' || Request::segment(1) == 'bugs-report' || Request::segment(1) == 'bugstatus' ||
                                   Request::segment(1) == 'project-task-stages' || Request::segment(1) == 'calendar' || Request::segment(1) == 'timesheet-list' ||
                                   Request::segment(1) == 'taskboard' || Request::segment(1) == 'timesheet-list' || Request::segment(1) == 'taskboard' ||
                                   Request::segment(1) == 'project' || Request::segment(1) == 'projects' || Request::segment(1) == 'project_report') ? 'active dash-trigger' : ''}}">
                  <a href="#project" class="dash-link"><span class="dash-micon"><svg width="64px" height="64px" viewBox="-512 -512 1536.00 1536.00" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>project-configuration</title> <g id="Page-1" stroke-width="0.00512" fill="none" fill-rule="evenodd"> <g id="icon" fill="#ffffff" transform="translate(42.666667, 42.666667)"> <path d="M277.333333,234.666667 L277.333,255.999667 L298.666667,256 L298.666667,298.666667 L277.333,298.666667 L277.333333,426.666667 L256,426.666667 L256,298.666667 L234.666667,298.666667 L234.666667,256 L256,255.999667 L256,234.666667 L277.333333,234.666667 Z M341.333333,234.666667 L341.333,341.332667 L362.666667,341.333333 L362.666667,384 L341.333,383.999667 L341.333333,426.666667 L320,426.666667 L320,383.999667 L298.666667,384 L298.666667,341.333333 L320,341.332667 L320,234.666667 L341.333333,234.666667 Z M405.333333,234.666667 L405.333,277.332667 L426.666667,277.333333 L426.666667,320 L405.333,319.999667 L405.333333,426.666667 L384,426.666667 L384,319.999667 L362.666667,320 L362.666667,277.333333 L384,277.332667 L384,234.666667 L405.333333,234.666667 Z M170.666667,7.10542736e-15 L341.333333,96 L341.333,213.333 L298.666,213.333 L298.666,138.747 L192,200.331 L192,323.018 L213.333,311.018 L213.333333,320 L234.666667,320 L234.666,348 L170.666667,384 L3.55271368e-14,288 L3.55271368e-14,96 L170.666667,7.10542736e-15 Z M42.666,139.913 L42.6666667,263.04 L149.333,323.022 L149.333,201.497 L42.666,139.913 Z M170.666667,48.96 L69.246,105.991 L169.656,163.963 L271.048,105.424 L170.666667,48.96 Z" id="Combined-Shape"> </path> </g> </g> </g></svg></span><span class="label">{{__('Project System')}}</span></a>
                  <ul  id="project" class="dash-submenu">
                      @can('manage project')
                          <li class="dash-item  {{Request::segment(1) == 'project' || Request::route()->getName() == 'projects.list' || Request::route()->getName() == 'projects.list' ||Request::route()->getName() == 'projects.index' || Request::route()->getName() == 'projects.show' || request()->is('projects/*') ? 'active' : ''}}">
                              <a class="dash-link" href="{{route('projects.index')}}">{{__('Projects')}}</a>
                          </li>
                      @endcan
                      @can('manage project task')
                          <li class="dash-item {{ (request()->is('taskboard*') ? 'active' : '')}}">
                              <a class="dash-link" href="{{ route('taskBoard.view', 'list') }}">{{__('Tasks')}}</a>
                          </li>
                      @endcan
                      @can('manage timesheet')
                          <li class="dash-item {{ (request()->is('timesheet-list*') ? 'active' : '')}}">
                              <a class="dash-link" href="{{route('timesheet.list')}}">{{__('Timesheet')}}</a>
                          </li>
                      @endcan
                      @can('manage bug report')
                          <li class="dash-item {{ (request()->is('bugs-report*') ? 'active' : '')}}">
                              <a class="dash-link" href="{{route('bugs.view','list')}}">{{__('Bug')}}</a>
                          </li>
                      @endcan
                      @can('manage project task')
                          <li class="dash-item {{ (request()->is('calendar*') ? 'active' : '')}}">
                              <a class="dash-link" href="{{ route('task.calendar',['all']) }}">{{__('Task Calendar')}}</a>
                          </li>
                      @endcan
                      
                      @if(\Auth::user()->type!='super admin')
                          <li class="dash-item  {{ (Request::segment(1) == 'time-tracker')?'active open':''}}">
                              <a class="dash-link" href="{{ route('time.tracker') }}">{{__('Tracker')}}</a>
                          </li>
                      @endif
                      @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'Employee')
                           <li class="dash-item  {{(Request::route()->getName() == 'project_report.index' || Request::route()->getName() == 'project_report.show') ? 'active' : ''}}">
                               <a class="dash-link" href="{{route('project_report.index') }}">{{__('Project Report')}}</a>
                           </li>
                      @endif

                      @if(Gate::check('manage project task stage') || Gate::check('manage bug status'))
                          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'bugstatus' || Request::segment(1) == 'project-task-stages') ? 'active dash-trigger' : ''}}">
                              <a class="dash-link" href="#system">{{__('Project System Setup')}}</a>
                              <ul id="system" class="dash-submenu">
                                  @can('manage project task stage')
                                      <li class="dash-item  {{ (Request::route()->getName() == 'project-task-stages.index') ? 'active' : '' }}">
                                          <a class="dash-link" href="{{route('project-task-stages.index')}}">{{__('Project Task Stages')}}</a>
                                      </li>
                                  @endcan
                                  @can('manage bug status')
                                      <li class="dash-item {{ (Request::route()->getName() == 'bugstatus.index') ? 'active' : '' }}">
                                          <a class="dash-link" href="{{route('bugstatus.index')}}">{{__('Bug Status')}}</a>
                                      </li>
                                  @endcan
                              </ul>
                          </li>
                      @endif
                  </ul>
              </li>
          @endif
      @endif

          

          @if(Gate::check('manage project task'))
          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'bookingcalendar') ? ' active' : '' }}">
              <a href="{{ route('booking.calendar',['all']) }}" class="dash-link">
                  <span class="dash-micon"><svg width="64px" height="64px" viewBox="-20.64 -20.64 65.28 65.28" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M3 9H21M7 3V5M17 3V5M6 13H8M6 17H8M11 13H13M11 17H13M16 13H18M16 17H18M6.2 21H17.8C18.9201 21 19.4802 21 19.908 20.782C20.2843 20.5903 20.5903 20.2843 20.782 19.908C21 19.4802 21 18.9201 21 17.8V8.2C21 7.07989 21 6.51984 20.782 6.09202C20.5903 5.71569 20.2843 5.40973 19.908 5.21799C19.4802 5 18.9201 5 17.8 5H6.2C5.0799 5 4.51984 5 4.09202 5.21799C3.71569 5.40973 3.40973 5.71569 3.21799 6.09202C3 6.51984 3 7.07989 3 8.2V17.8C3 18.9201 3 19.4802 3.21799 19.908C3.40973 20.2843 3.71569 20.5903 4.09202 20.782C4.51984 21 5.07989 21 6.2 21Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg></span><span class="label">{{__('Booking Calender')}}</span>
              </a>
          </li>
      @endif
           
          @if(\Auth::user()->type!='super admin' && ( Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage client') || Gate::check('view companybranch')))
          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'users' || Request::segment(1) == 'roles'
              || Request::segment(1) == 'clients' || Request::segment(1) == 'clientuser' || Request::segment(1) == 'branches'  || Request::segment(1) == 'userlogs')?' active dash-trigger':''}}">

              <a href="#user" class="dash-link "
              ><span class="dash-micon"><svg width="64px" height="64px" viewBox="-24 -24 72.00 72.00" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <circle cx="10" cy="6" r="4" stroke="#ffffff" stroke-width="1.5"></circle> <path d="M19 2C19 2 21 3.2 21 6C21 8.8 19 10 19 10" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> <path d="M17 4C17 4 18 4.6 18 6C18 7.4 17 8 17 8" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> <path d="M13 20.6151C12.0907 20.8619 11.0736 21 10 21C6.13401 21 3 19.2091 3 17C3 14.7909 6.13401 13 10 13C13.866 13 17 14.7909 17 17C17 17.3453 16.9234 17.6804 16.7795 18" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> </g></svg></span
                  ><span class="label">{{__('User Management')}}</span
                  ></a>
              <ul id="user" class="dash-submenu">
                  @can('manage user')
                      <li class="dash-item {{ (Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit' || Request::route()->getName() == 'user.userlog') ? ' active' : '' }}">
                          <a class="dash-link" href="{{ route('users.index') }}">{{__('User')}}</a>
                      </li>
                  @endcan
                  @can('manage role')
                      <li class="dash-item {{ (Request::route()->getName() == 'roles.index' || Request::route()->getName() == 'roles.create' || Request::route()->getName() == 'roles.edit') ? ' active' : '' }} ">
                          <a class="dash-link" href="{{route('roles.index')}}">{{__('Role')}}</a>
                      </li>
                  @endcan
                  @can('view companybranch')
                      <li class="dash-item {{ (Request::route()->getName() == 'branches.index' || Request::segment(1) == 'branches' || Request::route()->getName() == 'branches.edit') ? ' active' : '' }}">
                          <a class="dash-link" href="{{ route('branches.index') }}">{{__('Branch')}}</a>
                      </li>
                  @endcan
                  @can('manage client')
                      <li class="dash-item {{ (Request::route()->getName() == 'clients.index' || Request::segment(1) == 'clients' || Request::route()->getName() == 'clients.edit') ? ' active' : '' }}">
                          <a class="dash-link" href="{{ route('clients.index') }}">{{__('Client')}}</a>
                      </li>
                  @endcan
                  @can('manage clientuser')
                      <li class="dash-item {{ (Request::route()->getName() == 'clientuser.index' || Request::segment(1) == 'clientuser' || Request::route()->getName() == 'clientuser.edit') ? ' active' : '' }}">
                          <a class="dash-link" href="{{ route('clientuser.index') }}">{{__('Clientuser')}}</a>
                      </li>
                  @endcan
{{--                                    @can('manage user')--}}
{{--                                        <li class="dash-item {{ (Request::route()->getName() == 'users.index' || Request::segment(1) == 'users' || Request::route()->getName() == 'users.edit') ? ' active' : '' }}">--}}
{{--                                            <a class="dash-link" href="{{ route('user.userlog') }}">{{__('User Logs')}}</a>--}}
{{--                                        </li>--}}
{{--                                    @endcan--}}
              </ul>
          </li>
      @endif


          @if( Gate::check('manage product & service') || Gate::check('manage product & service'))
          <li class="dash-item dash-hasmenu">
              <a href="#products" class="dash-link ">
                  <span class="dash-micon"><svg width="64px" height="64px" viewBox="-343.04 -343.04 1198.08 1198.08" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>product-catalog</title> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="icon" fill="#ffffff" transform="translate(42.666667, 41.600000)"> <path d="M85.334,107.733 L85.335,150.399 L42.6666667,150.4 L42.6666667,342.4 L175.702784,342.4 L192,350.539 L192,250.91 L202.665434,256.831437 L213.331989,262.740708 L223.998544,256.831437 L234.666,250.909 L234.666,350.539 L250.963883,342.4 L384,342.4 L384,150.4 L341.332,150.399 L341.331,107.733 L426.666667,107.733333 L426.666667,385.066667 L261.013333,385.066667 L213.333333,408.918058 L165.632,385.066667 L3.55271368e-14,385.066667 L3.55271368e-14,107.733333 L85.334,107.733 Z M362.666667,278.4 L362.666667,310.4 L256,310.4 L256,278.4 L362.666667,278.4 Z M170.666667,278.4 L170.666667,310.4 L64,310.4 L64,278.4 L170.666667,278.4 Z M362.666667,214.4 L362.666667,246.4 L256,246.4 L256,239.065 L300.43,214.399 L362.666667,214.4 Z M126.237,214.399 L170.666,239.065 L170.666667,246.4 L64,246.4 L64,214.4 L126.237,214.399 Z M213.333333,7.10542736e-15 L320,59.2604278 L320,177.780929 L213.333333,237.041357 L106.666667,177.780929 L106.666667,59.2604278 L213.333333,7.10542736e-15 Z M170.666667,107.370667 L170.666667,188.928 L192,200.789333 L192,119.232 L170.666667,107.370667 Z M128,83.6693333 L128,165.226723 L149.333333,177.088 L149.333333,95.5306667 L128,83.6693333 Z M256.768,48.5333333 L182.037333,89.28 L202.346667,100.565333 L276.373333,59.4133333 L256.768,48.5333333 Z M213.333333,24.4053901 L139.306667,65.536 L159.957333,77.0133333 L234.688,36.2666667 L213.333333,24.4053901 Z" id="Path-2"> </path> </g> </g> </g></svg></span><span class="label">{{__('Products System')}}</span>
              </a>
              <ul id="products" class="dash-submenu">
                  @if(Gate::check('manage product & service'))
                      <li class="dash-item {{ (Request::segment(1) == 'productservice')?'active':''}}">
                          <a href="{{ route('productservice.index') }}" class="label">{{__('Product Information ')}}
                          </a>
                      </li>
                  @endif
                  @if(Gate::check('manage product & service'))
                      <li class="dash-item {{ (Request::segment(1) == 'productstock')?'active':''}}">
                          <a href="{{ route('productstock.index') }}" class="label">{{__('Product Stock')}}
                          </a>
                      </li>
                 @endif
              </ul>
          </li>
      @endif


          @if(\Auth::user()->show_pos() == 1)
          @if( Gate::check('manage warehouse') ||  Gate::check('manage purchase')  || Gate::check('manage pos') || Gate::check('manage print settings'))
          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'warehouse' || Request::segment(1) == 'purchase' || Request::route()->getName() == 'pos.barcode' || Request::route()->getName() == 'pos.print' || Request::route()->getName() == 'pos.show')?' active dash-trigger':''}}">
              <a href="#pos"><span class="dash-micon"><svg fill="#ffffff" width="64px" height="64px" viewBox="-24 -24 72.00 72.00" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="m1.44 24c-.795 0-1.44-.645-1.44-1.44v-16.904c0-.795.645-1.44 1.44-1.44h1.705.001c.054 0 .106.013.151.036l-.002-.001v-4.082-.001c0-.093.075-.168.168-.168h.001 6.018.001c.093 0 .168.075.168.168v.001 4.058c.024-.006.052-.01.08-.01h1.515c.795 0 1.44.645 1.44 1.44v4.031c.042-.021.092-.033.145-.033h.001.756c.186 0 .337.151.337.337v8.772c0 .186-.151.337-.337.337h-.756-.001c-.053 0-.102-.012-.147-.034l.002.001v3.492c0 .795-.645 1.44-1.44 1.44zm7.324-3.231v1.134c0 .093.076.169.169.169h1.334c.093 0 .169-.076.169-.169v-1.134c0-.093-.076-.168-.169-.168h-1.336c-.093 0-.168.075-.169.168zm-3.156 0v1.134c0 .093.076.169.169.169h1.333c.093 0 .169-.076.169-.169v-1.134c0-.093-.076-.168-.169-.168h-1.334c-.093 0-.168.075-.169.168zm-3.156 0v1.134.001c0 .093.075.168.168.168h1.334c.093 0 .169-.076.169-.169v-1.134c0-.093-.076-.168-.169-.168h-1.334c-.093 0-.168.075-.168.168zm6.311-2.571v1.134c0 .093.076.169.169.169h1.334c.093 0 .169-.076.169-.169v-1.134-.001c0-.093-.075-.168-.168-.168h-.001-1.335-.001c-.093 0-.168.075-.168.168v.001zm-3.156 0v1.134c0 .093.076.169.169.169h1.334c.093 0 .169-.076.169-.169v-1.134-.001c0-.093-.075-.168-.168-.168h-.001-1.334-.001c-.093 0-.168.075-.168.168zm-3.156 0v1.134.001c0 .093.075.168.168.168h1.334c.093 0 .169-.076.169-.169v-1.134-.001c0-.093-.075-.168-.168-.168h-.001-1.334c-.093 0-.168.075-.168.168zm6.311-2.572v1.134.001c0 .093.075.168.168.168h.001 1.334.001c.093 0 .168-.075.168-.168v-.001-1.134c0-.093-.076-.168-.169-.168h-1.334c-.093 0-.168.075-.169.168zm-3.156 0v1.134.001c0 .093.075.168.168.168h.001 1.334.001c.093 0 .168-.075.168-.168v-.001-1.134c0-.093-.076-.168-.169-.168h-1.334c-.093 0-.168.075-.169.168zm-3.156 0v1.134.001c0 .093.075.168.168.168h1.335.001c.093 0 .168-.075.168-.168v-.001-1.134c0-.093-.076-.168-.169-.168h-1.334c-.093 0-.168.075-.168.168zm-.21-6.713v4.911.001c0 .093.075.168.168.168h.001 7.76.001c.093 0 .168-.075.168-.168v-.001-4.911c0-.093-.076-.169-.169-.169h-7.76c-.093 0-.169.076-.169.169zm-.504-2.682v1.189.001c0 .279.226.505.505.505h.001 8.398c.279 0 .505-.226.505-.505v-.001-1.189c0-.279-.226-.505-.505-.505h-.99v1.01h.488v.178h-7.392v-.178h.32.001c.084 0 .164-.021.233-.057l-.003.001v-.898c-.067-.035-.146-.056-.231-.056 0 0 0 0-.001 0h-.826c-.278 0-.504.226-.504.505z"></path></g></svg></span><span class="label">{{__('POS System')}}</span> </a>
    
                <ul id="pos" >
              
                 @can('manage warehouse')
                    <li class="dash-item {{ (Request::route()->getName() == 'warehouse.index' || Request::route()->getName() == 'warehouse.show') ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('warehouse.index') }}">{{__('Warehouse')}}</a>
                    </li>
                @endcan
                @can('manage purchase')
                    <li class="dash-item {{ (Request::route()->getName() == 'purchase.index' || Request::route()->getName() == 'purchase.create' || Request::route()->getName() == 'purchase.edit' || Request::route()->getName() == 'purchase.show') ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('purchase.index') }}">{{__('Purchase')}}</a>
                    </li>
                @endcan
                @can('manage pos')
                <li class="dash-item {{ (Request::route()->getName() == 'pos.index' ) ? ' active' : '' }}">
                    <a class="dash-link" href="{{ route('pos.index') }}">{{__(' Add POS')}}</a>
                </li>
                <li class="dash-item {{ (Request::route()->getName() == 'pos.report' || Request::route()->getName() == 'pos.show') ? ' active' : '' }}">
                    <a class="dash-link" href="{{ route('pos.report') }}">{{__('POS')}}</a>
                </li>
            @endcan
            @can('manage warehouse')
                <li class="dash-item {{ (Request::route()->getName() == 'warehouse-transfer.index' || Request::route()->getName() == 'warehouse-transfer.show') ? ' active' : '' }}">
                    <a class="dash-link" href="{{ route('warehouse-transfer.index') }}">{{__('Transfer')}}</a>
                </li>
            @endcan
            @can('create barcode')
                <li class="dash-item {{ (Request::route()->getName() == 'pos.barcode'  || Request::route()->getName() == 'pos.print') ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('pos.barcode') }}">{{__('Print Barcode')}}</a>
                    </li>
            @endcan
            @can('manage pos')
                <li class="dash-item {{ (Request::route()->getName() == 'pos-print-setting') ? ' active' : '' }}">
                        <a class="dash-link" href="{{ route('pos.print.setting') }}">{{__('Print Settings')}}</a>
                    </li>
            @endcan
    
                </ul>
              </li>
          @endif
          @endif

          


    




          @if(\Auth::user()->type!='super admin')
          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'support')?'active':''}}">
              <a href="{{route('support.index')}}" class="dash-link">
                  <span class="dash-micon"><svg fill="#ffffff" width="64px" height="64px" viewBox="-32 -32 96.00 96.00" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 0c-8.836 0-16 7.163-16 16s7.163 16 16 16 16-7.163 16-16-7.163-16-16-16zM20 2.593c4.507 1.343 8.052 4.9 9.397 9.407h-6.486c-0.701-1.204-1.706-2.209-2.91-2.908zM21.989 16.006c0 3.311-2.681 5.994-5.989 5.994s-5.989-2.683-5.989-5.994 2.681-5.994 5.989-5.994c3.307 0 5.989 2.684 5.989 5.994zM14 2.154c0.653-0.094 1.32-0.144 2-0.144s1.346 0.051 2 0.144v6.119c-0.64-0.165-1.308-0.262-2-0.262s-1.36 0.097-2 0.262v-6.119zM12 2.593v6.499c-1.205 0.7-2.21 1.704-2.91 2.908h-6.487c1.345-4.507 4.89-8.063 9.397-9.407zM2.010 16.005c0-0.682 0.058-1.349 0.152-2.005h6.106c-0.166 0.641-0.257 1.312-0.257 2.005 0 0.69 0.091 1.357 0.255 1.995h-6.105c-0.093-0.652-0.151-1.317-0.151-1.995zM12 29.416c-4.511-1.344-8.056-4.906-9.4-9.416h6.483c0.701 1.208 1.708 2.217 2.916 2.919v6.498zM18 29.855c-0.654 0.093-1.321 0.145-2 0.145s-1.347-0.052-2-0.145v-6.118c0.64 0.166 1.308 0.262 2 0.262s1.36-0.097 2-0.262v6.118zM20 29.416v-6.498c1.208-0.701 2.216-1.71 2.916-2.919h6.483c-1.343 4.511-4.89 8.073-9.399 9.416zM23.735 18c0.164-0.637 0.255-1.305 0.255-1.995 0-0.694-0.091-1.364-0.258-2.005h6.107c0.094 0.656 0.152 1.323 0.152 2.005 0 0.678-0.058 1.343-0.151 1.995h-6.105z"></path> </g></svg></span><span class="label">{{__('Support System')}}</span>
              </a>
          </li>
          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'zoom-meeting' || Request::segment(1) == 'zoom-meeting-calender')?'active':''}}">
              <a href="{{route('zoom-meeting.index')}}" class="dash-link">
                  <span class="dash-micon"><svg fill="#ffffff" width="64px" height="64px" viewBox="-394.24 -394.24 1300.48 1300.48" data-name="Layer 1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff" stroke-width="7.168000000000001"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M464.73,377H446V115.4A30.11,30.11,0,0,0,415.9,85.33H96.1A30.1,30.1,0,0,0,66,115.4V377H47.28A10.39,10.39,0,0,0,36.9,387.41V392a34.72,34.72,0,0,0,34.68,34.69H440.43A34.72,34.72,0,0,0,475.1,392v-4.57A10.39,10.39,0,0,0,464.73,377ZM75.09,115.4a21,21,0,0,1,21-21H415.9a21,21,0,0,1,21,21V377H75.09ZM466,392a25.64,25.64,0,0,1-25.61,25.62H71.58A25.65,25.65,0,0,1,46,392v-4.57a1.32,1.32,0,0,1,1.32-1.31H464.73a1.31,1.31,0,0,1,1.31,1.31Z"></path><path d="M398.36,126.36H113.64a4.53,4.53,0,0,0-4.53,4.53V339.31a4.53,4.53,0,0,0,4.53,4.53H398.36a4.53,4.53,0,0,0,4.54-4.53V130.89A4.53,4.53,0,0,0,398.36,126.36Zm-4.53,104.82H362v-8.7a36.27,36.27,0,0,0-21.38-33,21.77,21.77,0,1,0-29.69,0,36.27,36.27,0,0,0-21.39,33v8.7H260.53V135.42h133.3Zm-95.18,0v-8.7a27.16,27.16,0,0,1,54.32,0v8.7Zm14.47-57.62a12.7,12.7,0,1,1,12.69,12.7A12.71,12.71,0,0,1,313.12,173.56Zm-61.65-38.14v95.76H222.42v-8.7a36.27,36.27,0,0,0-21.38-33,21.77,21.77,0,1,0-29.7,0,36.27,36.27,0,0,0-21.38,33v8.7H118.17V135.42ZM159,231.18v-8.7a27.16,27.16,0,1,1,54.32,0v8.7Zm14.46-57.62a12.7,12.7,0,1,1,12.7,12.7A12.72,12.72,0,0,1,173.49,173.56Zm-55.32,66.69h133.3v94.53H222.42v-8.7a36.26,36.26,0,0,0-21.38-33,21.77,21.77,0,1,0-29.7,0,36.26,36.26,0,0,0-21.38,33v8.7H118.17Zm55.32,36.9a12.7,12.7,0,1,1,12.7,12.7A12.72,12.72,0,0,1,173.49,277.15Zm39.86,57.63H159v-8.7a27.16,27.16,0,0,1,54.32,0Zm99.77-57.63a12.7,12.7,0,1,1,12.69,12.7A12.71,12.71,0,0,1,313.12,277.15ZM353,334.78H298.65v-8.7a27.16,27.16,0,0,1,54.32,0Zm9.07,0v-8.7a36.26,36.26,0,0,0-21.38-33,21.77,21.77,0,1,0-29.69,0,36.25,36.25,0,0,0-21.39,33v8.7H260.53V240.25h133.3v94.53Z"></path><path d="M160.41,355.29H113.64a4.53,4.53,0,0,0,0,9.06h46.77a4.53,4.53,0,0,0,0-9.06Z"></path></g></svg></span><span class="label">{{__('Zoom Meeting')}}</span>
              </a>
          </li>
          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'chats')?'active':''}}">
              <a href="{{ url('chats') }}" class="dash-link">
                  <span class="dash-micon"><svg fill="#ffffff" width="64px" height="64px" viewBox="-24 -24 72.00 72.00" id="messenger" data-name="Flat Line" xmlns="http://www.w3.org/2000/svg" class="icon flat-line"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path id="secondary" d="M20.88,13.46A9,9,0,0,1,7.88,20L3,21l1-4.88a9,9,0,1,1,16.88-2.66Z" style="fill: #100773; stroke-width: 2;"></path><polyline id="primary" points="16 11 13 13 11 11 8 13" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></polyline><path id="primary-2" data-name="primary" d="M20.88,13.46A9,9,0,0,1,7.88,20L3,21l1-4.88a9,9,0,1,1,16.88-2.66Z" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></g></svg></span><span class="label">{{__('Messenger')}}</span>
              </a>
          </li>
      @endif

      @if(\Auth::user()->type =='company')
          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'notification_templates')?'active':''}}">
              <a href="{{route('notification-templates.index')}}" class="dash-link">
                  <span class="dash-micon"><svg width="64px" height="64px" viewBox="-21.36 -21.36 66.72 66.72" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M9.75005 4.75C9.33584 4.75 9.00005 5.08579 9.00005 5.5C9.00005 5.91421 9.33584 6.25 9.75005 6.25V4.75ZM12.6751 6.25C13.0893 6.25 13.4251 5.91421 13.4251 5.5C13.4251 5.08579 13.0893 4.75 12.6751 4.75V6.25ZM9.48794 17.0191C9.48249 16.605 9.14232 16.2736 8.72814 16.2791C8.31396 16.2845 7.98262 16.6247 7.98807 17.0389L9.48794 17.0191ZM11.2126 19.5L11.2228 18.7501C11.216 18.75 11.2092 18.75 11.2023 18.7501L11.2126 19.5ZM14.437 17.0389C14.4425 16.6247 14.1111 16.2845 13.697 16.2791C13.2828 16.2736 12.9426 16.605 12.9372 17.0191L14.437 17.0389ZM8.73703 17.779C9.15124 17.779 9.48703 17.4432 9.48703 17.029C9.48703 16.6148 9.15124 16.279 8.73703 16.279V17.779ZM7.63723 17.029L7.631 17.779H7.63723V17.029ZM5.85005 15.168L6.60005 15.1732V15.168H5.85005ZM6.57058 12.676L5.82897 12.5641L5.82888 12.5647L6.57058 12.676ZM6.76558 11.276L7.50406 11.4069C7.50578 11.3972 7.50731 11.3875 7.50865 11.3777L6.76558 11.276ZM11.2126 7.147L11.2433 6.39763C11.2228 6.39679 11.2022 6.39679 11.1817 6.39763L11.2126 7.147ZM15.6595 11.273L14.9165 11.3751C14.9177 11.3836 14.919 11.392 14.9204 11.4004L15.6595 11.273ZM15.8545 12.673L16.5962 12.5617L16.596 12.5605L15.8545 12.673ZM16.5751 15.165L15.825 15.165L15.8251 15.1706L16.5751 15.165ZM14.7879 17.027L14.7879 17.777L14.7941 17.777L14.7879 17.027ZM13.6871 16.277C13.2729 16.277 12.9371 16.6128 12.9371 17.027C12.9371 17.4412 13.2729 17.777 13.6871 17.777V16.277ZM8.73703 16.277C8.32281 16.277 7.98703 16.6128 7.98703 17.027C7.98703 17.4412 8.32281 17.777 8.73703 17.777V16.277ZM13.6871 17.777C14.1013 17.777 14.4371 17.4412 14.4371 17.027C14.4371 16.6128 14.1013 16.277 13.6871 16.277V17.777ZM9.75005 6.25H12.6751V4.75H9.75005V6.25ZM7.98807 17.0389C8.01146 18.8183 9.4421 20.2742 11.2228 20.2499L11.2023 18.7501C10.2859 18.7625 9.50091 18.006 9.48794 17.0191L7.98807 17.0389ZM11.2023 20.2499C12.983 20.2742 14.4136 18.8183 14.437 17.0389L12.9372 17.0191C12.9242 18.006 12.1392 18.7625 11.2228 18.7501L11.2023 20.2499ZM8.73703 16.279H7.63723V17.779H8.73703V16.279ZM7.64345 16.279C7.08063 16.2744 6.59573 15.7972 6.60003 15.1732L5.10007 15.1628C5.09031 16.5785 6.20512 17.7671 7.631 17.779L7.64345 16.279ZM6.60005 15.168C6.60005 14.891 6.69326 14.6047 6.85708 14.1992C7.00452 13.8342 7.23096 13.3291 7.31227 12.7873L5.82888 12.5647C5.78052 12.8869 5.6467 13.1908 5.46629 13.6373C5.30227 14.0433 5.10005 14.571 5.10005 15.168H6.60005ZM7.31219 12.7879C7.40448 12.1759 7.4489 11.7181 7.50406 11.4069L6.02709 11.1451C5.97305 11.4499 5.90047 12.0901 5.82897 12.5641L7.31219 12.7879ZM7.50865 11.3777C7.77569 9.42625 9.35923 7.97389 11.2434 7.89637L11.1817 6.39763C8.54483 6.50613 6.38434 8.53009 6.0225 11.1743L7.50865 11.3777ZM11.1818 7.89637C13.0651 7.97369 14.6484 9.42473 14.9165 11.3751L16.4025 11.1709C16.0392 8.52798 13.8791 6.50584 11.2433 6.39763L11.1818 7.89637ZM14.9204 11.4004C14.9759 11.7223 15.0202 12.1736 15.113 12.7855L16.596 12.5605C16.525 12.0924 16.4504 11.4457 16.3986 11.1456L14.9204 11.4004ZM15.1128 12.7843C15.1941 13.3261 15.4206 13.8312 15.568 14.1962C15.7318 14.6017 15.8251 14.888 15.8251 15.165H17.3251C17.3251 14.568 17.1228 14.0403 16.9588 13.6343C16.7784 13.1878 16.6446 12.8839 16.5962 12.5617L15.1128 12.7843ZM15.8251 15.1706C15.8297 15.7949 15.3447 16.2724 14.7817 16.277L14.7941 17.777C16.2205 17.7651 17.3355 16.5756 17.325 15.1594L15.8251 15.1706ZM14.7879 16.277H13.6871V17.777H14.7879V16.277ZM8.73703 17.777H13.6871V16.277H8.73703V17.777Z" fill="#ffffff"></path> </g></svg></span><span class="label">{{__('Notification Template')}}</span>
              </a>
          </li>
      @endif

      


      @if((\Auth::user()->type != 'super admin'))
      @if( Gate::check('manage company plan') || Gate::check('manage order') || Gate::check('manage company settings'))
          <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'settings' || Request::segment(1) == 'plans'
          || Request::segment(1) == 'stripe'  || Request::segment(1) == 'order')?' active dash-trigger':''}}">
              <a href="#settings" class="dash-link">
                  <span class="dash-micon"><svg width="64px" height="64px" viewBox="-24 -24 72.00 72.00" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M11 3H13C13.5523 3 14 3.44772 14 4V4.56879C14 4.99659 14.2871 5.36825 14.6822 5.53228C15.0775 5.69638 15.5377 5.63384 15.8403 5.33123L16.2426 4.92891C16.6331 4.53838 17.2663 4.53838 17.6568 4.92891L19.071 6.34312C19.4616 6.73365 19.4615 7.36681 19.071 7.75734L18.6688 8.1596C18.3661 8.46223 18.3036 8.92247 18.4677 9.31774C18.6317 9.71287 19.0034 10 19.4313 10L20 10C20.5523 10 21 10.4477 21 11V13C21 13.5523 20.5523 14 20 14H19.4312C19.0034 14 18.6318 14.2871 18.4677 14.6822C18.3036 15.0775 18.3661 15.5377 18.6688 15.8403L19.071 16.2426C19.4616 16.6331 19.4616 17.2663 19.071 17.6568L17.6568 19.071C17.2663 19.4616 16.6331 19.4616 16.2426 19.071L15.8403 18.6688C15.5377 18.3661 15.0775 18.3036 14.6822 18.4677C14.2871 18.6318 14 19.0034 14 19.4312V20C14 20.5523 13.5523 21 13 21H11C10.4477 21 10 20.5523 10 20V19.4313C10 19.0034 9.71287 18.6317 9.31774 18.4677C8.92247 18.3036 8.46223 18.3661 8.1596 18.6688L7.75732 19.071C7.36679 19.4616 6.73363 19.4616 6.34311 19.071L4.92889 17.6568C4.53837 17.2663 4.53837 16.6331 4.92889 16.2426L5.33123 15.8403C5.63384 15.5377 5.69638 15.0775 5.53228 14.6822C5.36825 14.2871 4.99659 14 4.56879 14H4C3.44772 14 3 13.5523 3 13V11C3 10.4477 3.44772 10 4 10L4.56877 10C4.99658 10 5.36825 9.71288 5.53229 9.31776C5.6964 8.9225 5.63386 8.46229 5.33123 8.15966L4.92891 7.75734C4.53838 7.36681 4.53838 6.73365 4.92891 6.34313L6.34312 4.92891C6.73365 4.53839 7.36681 4.53839 7.75734 4.92891L8.15966 5.33123C8.46228 5.63386 8.9225 5.6964 9.31776 5.53229C9.71288 5.36825 10 4.99658 10 4.56876V4C10 3.44772 10.4477 3 11 3Z" stroke="#ffffff" stroke-width="1.5"></path> <path d="M14 12C14 13.1046 13.1046 14 12 14C10.8954 14 10 13.1046 10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12Z" stroke="#ffffff" stroke-width="1.5"></path> </g></svg></span><span class="label">{{__('Settings')}}</span>
              </a>
              <ul id="settings" class="dash-submenu">
                  @if(Gate::check('manage company settings'))
                      <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'settings') ? ' active' : '' }}">
                          <a href="{{ route('settings') }}" class="label">{{__('System Settings')}}</a>
                      </li>
                  @endif
                  @if(Gate::check('manage company plan'))
                      <li class="dash-item{{ (Request::route()->getName() == 'plans.index' || Request::route()->getName() == 'stripe') ? ' active' : '' }}">
                          <a href="{{ route('plans.index') }}" class="label">{{__('Setup Subscription Plan')}}</a>
                      </li>
                  @endif

                  @if(Gate::check('manage order') && Auth::user()->type == 'company')
                      <li class="dash-item {{ (Request::segment(1) == 'order')? 'active' : ''}}">
                          <a href="{{ route('order.index') }}" class="label">{{__('Order')}}</a>
                      </li>
                  @endif
              </ul>
          </li>
      @endif
    @endif





    @if((\Auth::user()->type == 'client'))
    <ul class="dash-navbar">
        @if(Gate::check('manage client dashboard'))
            <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'dashboard') ? ' active' : '' }}">
                <a href="{{ route('client.dashboard.view') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-home"></i></span><span class="label">{{__('Dashboard')}}</span>
                </a>
            </li>
        @endif
        @if(Gate::check('manage deal'))
            <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'deals') ? ' active' : '' }}">
                <a href="{{ route('deals.index') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-rocket"></i></span><span class="label">{{__('Deals')}}</span>
                </a>
            </li>
        @endif
        @if(Gate::check('manage contract'))
                <li class="dash-item dash-hasmenu {{ (Request::route()->getName() == 'contract.index' || Request::route()->getName() == 'contract.show')?'active':''}}">
                    <a href="{{ route('contract.index') }}" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-rocket"></i></span><span class="label">{{__('Contract')}}</span>
                    </a>
                </li>
            @endif
        @if(Gate::check('manage project'))
            <li class="dash-item dash-hasmenu  {{ (Request::segment(1) == 'projects') ? ' active' : '' }}">
                <a href="{{ route('projects.index') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-share"></i></span><span class="label">{{__('Project')}}</span>
                </a>
            </li>
        @endif
            @if(Gate::check('manage project'))

                <li class="dash-item  {{(Request::route()->getName() == 'project_report.index' || Request::route()->getName() == 'project_report.show') ? 'active' : ''}}">
                    <a class="dash-link" href="{{route('project_report.index') }}">
                        <span class="dash-micon"><i class="ti ti-chart-line"></i></span><span class="label">{{__('Project Report')}}</span>
                    </a>
                </li>
            @endif

        @if(Gate::check('manage project task'))
            <li class="dash-item dash-hasmenu  {{ (Request::segment(1) == 'taskboard') ? ' active' : '' }}">
                <a href="{{ route('taskBoard.view', 'list') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-list-check"></i></span><span class="label">{{__('Tasks')}}</span>
                </a>
            </li>
        @endif

        @if(Gate::check('manage bug report'))
            <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'bugs-report') ? ' active' : '' }}">
                <a href="{{ route('bugs.view','list') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-bug"></i></span><span class="label">{{__('Bugs')}}</span>
                </a>
            </li>
        @endif

        @if(Gate::check('manage timesheet'))
            <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'timesheet-list') ? ' active' : '' }}">
                <a href="{{ route('timesheet.list') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-clock"></i></span><span class="label">{{__('Timesheet')}}</span>
                </a>
            </li>
        @endif

        @if(Gate::check('manage project task'))
            <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'calendar') ? ' active' : '' }}">
                <a href="{{ route('task.calendar',['all']) }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-calendar"></i></span><span class="label">{{__('Task Calender')}}</span>
                </a>
            </li>
        @endif
     

            <li class="dash-item dash-hasmenu">
                <a href="{{route('support.index')}}" class="dash-link {{ (Request::segment(1) == 'support')?'active':''}}">
                    <span class="dash-micon"><i class="ti ti-headphones"></i></span><span class="label">{{__('Support')}}</span>
                </a>
            </li>
    </ul>
@endif
@if((\Auth::user()->type == 'super admin'))
    <ul class="dash-navbar">
        @if(Gate::check('manage super admin dashboard'))
            <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'dashboard') ? ' active' : '' }}">
                <a href="{{ route('client.dashboard.view') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-home"></i></span><span class="label">{{__('Dashboard')}}</span>
                </a>
            </li>
        @endif


        @can('manage user')
            <li class="dash-item dash-hasmenu {{ (Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit') ? ' active' : '' }}">
                <a href="{{ route('users.index') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-users"></i></span><span class="label">{{__('User')}}</span>
                </a>
            </li>
        @endcan

        @if(Gate::check('manage plan'))
            <li class="dash-item dash-hasmenu  {{ (Request::segment(1) == 'plans')?'active':''}}">
                <a href="{{ route('plans.index') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-trophy"></i></span><span class="label">{{__('Plan')}}</span>
                </a>
            </li>

        @endif
        @if(\Auth::user()->type=='super admin')
            <li class="dash-item dash-hasmenu {{ request()->is('plan_request*') ? 'active' : '' }}">
                <a href="{{ route('plan_request.index') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-arrow-up-right-circle"></i></span><span class="label">{{__('Plan Request')}}</span>
                </a>
            </li>
        @endif
        @if(Gate::check('manage coupon'))
            <li class="dash-item dash-hasmenu {{ (Request::segment(1) == 'coupons')?'active':''}}">
                <a href="{{ route('coupons.index') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-gift"></i></span><span class="label">{{__('Coupon')}}</span>
                </a>
            </li>
        @endif
        @if(Gate::check('manage order'))
            <li class="dash-item dash-hasmenu  {{ (Request::segment(1) == 'orders')?'active':''}}">
                <a href="{{ route('order.index') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-shopping-cart-plus"></i></span><span class="label">{{__('Order')}}</span>
                </a>
            </li>
        @endif
            <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'email_template' || Request::route()->getName() == 'manage.email.language' ? ' active dash-trigger' : 'collapsed' }}">
                <a href="{{ route('manage.email.language',[$emailTemplate ->id,\Auth::user()->lang]) }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-template"></i></span>
                    <span class="label">{{ __('Email Template') }}</span>
                </a>
            </li>

            @if(\Auth::user()->type=='super admin')
                @include('landingpage::menu.landingpage')
            @endif

        @if(Gate::check('manage system settings'))
            <li class="dash-item dash-hasmenu {{ (Request::route()->getName() == 'systems.index') ? ' active' : '' }}">
                <a href="{{ route('systems.index') }}" class="dash-link">
                    <span class="dash-micon"><i class="ti ti-settings"></i></span><span class="label">{{__('Settings')}}</span>
                </a>
            </li>
        @endif

    </ul>
@endif




 

     

        </ul>
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


{{-- 
    <!-- Vendor Scripts Start -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>

    <script src="{{ asset('jquery-3.5.1.min.js')}}"></script>
    <script src="{{ asset('bootstrap.bundle.min.js')}}"></script>
    <script src="{{ asset('OverlayScrollbars.min.js')}}"></script>
    <script src="{{ asset('autoComplete.min.js')}}"></script>
    <script src="{{ asset('clamp.min.js')}}"></script>
    <script src="{{ asset('acorn-icons.js')}}"></script>
    <script src="{{ asset('acorn-icons-interface.js')}}"></script>
    <script src="{{ asset('acorn-icons-medical.js')}}"></script>

    <script src="{{ asset('glide.min.js')}}"></script>

    <!-- Vendor Scripts End -->

    <!-- Template Base Scripts Start -->
    <script src="{{ asset('helpers.js')}}"></script>
    <script src="{{ asset('globals.js')}}"></script>
    <script src="{{ asset('nav.js')}}"></script>
    <script src="{{ asset('search.js')}}"></script>
    <script src="{{ asset('settings.js')}}"></script>
    <!-- Template Base Scripts End -->
    <!-- Page Specific Scripts Start -->

    <script src="{{ asset('glide.custom.js')}}"></script>

    <script src="{{ asset('dashboards.patient.js')}}"></script>

    <script src="{{ asset('common.js')}}"></script>
    <script src="{{ asset('scripts.js')}}"></script> --}}

    
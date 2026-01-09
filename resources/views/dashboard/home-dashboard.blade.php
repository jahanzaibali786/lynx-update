@php
    use App\Models\Utility;
    //$logo=asset(Storage::url('uploads/logo/'));
    $logo=\App\Models\Utility::get_file('uploads/logo');
    $company_favicon=Utility::getValByName('company_favicon');
    $setting = \App\Models\Utility::colorset();
    $company_logo = \App\Models\Utility::GetLogo();
    $mode_setting = \App\Models\Utility::mode_layout();
    $color = (!empty($setting['color'])) ? $setting['color'] : 'theme-3';
    $SITE_RTL = Utility::getValByName('SITE_RTL');
    $lang = \App::getLocale('lang');
    if($lang == 'ar' || $lang == 'he')
    {
        $SITE_RTL= 'on';
    }
    $getseo= App\Models\Utility::getSeoSetting();
    $metatitle =  isset($getseo['meta_title']) ? $getseo['meta_title'] :'';
    $metsdesc= isset($getseo['meta_desc'])?$getseo['meta_desc']:'';
    $meta_image = \App\Models\Utility::get_file('uploads/meta/');
    $meta_logo = isset($getseo['meta_image'])?$getseo['meta_image']:'';
    $get_cookie = \App\Models\Utility::getCookieSetting();


@endphp
    <!DOCTYPE html>
{{-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{$SITE_RTL == 'on' ? 'rtl' : '' }}"> --}}
<html lang="' . str_replace('_', '-', app()->getLocale()) . '" dir="' . ($SITE_RTL == 'on' ? 'rtl' : '') . '" data-footer="true" data-placement="vertical" data-behaviour="pinned" data-layout="fluid" data-radius="rounded" data-color="light-teal" data-navcolor="default" data-show="true" data-dimension="desktop" >

<meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
<head>
    <title>{{(Utility::getValByName('title_text')) ? Utility::getValByName('title_text') : config('app.name', 'ERPGO')}} - @yield('page-title')</title>

    <meta name="title" content="{{$metatitle}}">
    <meta name="description" content="{{$metsdesc}}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{$metatitle}}">
    <meta property="og:description" content="{{$metsdesc}}">
    <meta property="og:image" content="{{$meta_image.$meta_logo}}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{$metatitle}}">
    <meta property="twitter:description" content="{{$metsdesc}}">
    <meta property="twitter:image" content="{{$meta_image.$meta_logo}}">


    <script src="{{ asset('js/html5shiv.js') }}"></script>

{{--    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>--}}

<!-- Meta -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="url" content="{{ url('').'/'.config('chatify.path') }}" data-user="{{ Auth::user()->id }}">
    <link rel="icon" href="{{$logo.'/'.(isset($company_favicon) && !empty($company_favicon)?$company_favicon:'favicon.png')}}" type="image" sizes="16x16">

    <!-- Favicon icon -->
{{--    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon"/>--}}
<!-- Calendar-->
    <!-- <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}"> -->

    <!-- <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

    <!--bootstrap switch-->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">


    {{-- acron codes css links Start --}}

    {{-- <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet" /> --}}
    <!-- Font Tags Start -->
    <link rel="stylesheet" href="{{ asset('public/acron/style.css')}}" />
    <!-- Font Tags End -->
    <!-- Vendor Styles Start -->
    <link rel="stylesheet" href="{{ asset('public/acron/bootstrap.min.css')}}" />
    <link rel="stylesheet" href="{{ asset('public/acron/OverlayScrollbars.min.css')}}" />

    <link rel="stylesheet" href="{{ asset('public/acron/glide.core.min.css')}}" />

    <link rel="stylesheet" href="{{ asset('public/acron/fullcalendar.min.css')}}" />
    <!-- Vendor Styles End -->
    <!-- Template Base Styles Start -->
    <link rel="stylesheet" href="{{ asset('public/acron/styles.css')}}" />
    <!-- Template Base Styles End -->

    <link rel="stylesheet" href="{{ asset('public/acron/main.css')}}" />
    {{-- <script src="{{ asset('acron/loader.js')}}"></script>  --}}


    <!-- Vendor Scripts Start -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>

    <script src="{{ asset('public/acron/jquery-3.5.1.min.js')}}"></script>
    <script src="{{ asset('public/acron/bootstrap.bundle.min.js')}}"></script>
    <script src="{{ asset('public/acron/OverlayScrollbars.min.js')}}"></script>
    <script src="{{ asset('public/acron/autoComplete.min.js')}}"></script>
    <script src="{{ asset('public/acron/clamp.min.js')}}"></script>
    <script src="{{ asset('public/acron/acorn-icons.js')}}"></script>
    <script src="{{ asset('public/acron/acorn-icons-interface.js')}}"></script>
    <script src="{{ asset('public/acron/acorn-icons-medical.js')}}"></script>

    <script src="{{ asset('public/acron/glide.min.js')}}"></script>

    <!-- Vendor Scripts End -->

    <!-- Template Base Scripts Start -->
    <script src="{{ asset('public/acron/helpers.js')}}"></script>
    <script src="{{ asset('public/acron/globals.js')}}"></script>
    <script src="{{ asset('public/acron/nav.js')}}"></script>
    <script src="{{ asset('public/acron/search.js')}}"></script>
    <script src="{{ asset('public/acron/settings.js')}}"></script>
    <!-- Template Base Scripts End -->
    <!-- Page Specific Scripts Start -->

    <script src="{{ asset('public/acron/glide.custom.js')}}"></script>

    <script src="{{ asset('public/acron/dashboards.patient.js')}}"></script>

    <script src="{{ asset('public/acron/common.js')}}"></script>
    <script src="{{ asset('public/acron/scripts.js')}}"></script>

    {{-- acron codes css links End--}}

    <!-- vendor css -->
    @if ($SITE_RTL == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">

    @endif


    @if($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style">
    @else
         <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style">
    @endif


    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}" >

    @if ($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('css/custom-dark.css') }}">
    @endif
    <style>
        .hover-border-primary :hover{
            background-color: var(--primary);
            color: white;
            border-radius: 10px;
        }
    </style>

    @stack('css-page')
</head>
{{-- <body class="{{ $color }}" class="rtl" data-bs-padding="21px"> --}}
<body class="{{ $color }}" class="rtl" data-bs-padding="21px" class="img js-fullheight"  style="background-image: url({{ asset(Storage::url('uploads/logo/bg.JPG')) }});">

<!-- [ Pre-loader ] start -->
<div class="loader-bg">
    <div class="loader-track">
        <div class="loader-fill"></div>
    </div>
</div>

{{-- @include('partials.admin.menu') --}}
<!-- [ navigation menu ] end -->
<!-- [ Header ] start -->
@include('partials.admin.header')

<!-- Modal -->
<div class="modal notification-modal fade"
     id="notification-modal"
     tabindex="-1"
     role="dialog"
     aria-hidden="true"
>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <button
                    type="button"
                    class="btn-close float-end"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
                <h6 class="mt-2">
                    <i data-feather="monitor" class="me-2"></i>Desktop settings
                </h6>
                <hr/>
                <div class="form-check form-switch">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="pcsetting1"
                        checked
                    />
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting1"
                    >Allow desktop notification</label
                    >
                </div>
                <p class="text-muted ms-5">
                    you get lettest content at a time when data will updated
                </p>
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="pcsetting2"/>
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting2"
                    >Store Cookie</label
                    >
                </div>
                <h6 class="mb-0 mt-5">
                    <i data-feather="save" class="me-2"></i>Application settings
                </h6>
                <hr/>
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="pcsetting3"/>
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting3"
                    >Backup Storage</label
                    >
                </div>
                <p class="text-muted mb-4 ms-5">
                    Automaticaly take backup as par schedule
                </p>
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="pcsetting4"/>
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting4"
                    >Allow guest to print file</label
                    >
                </div>
                <h6 class="mb-0 mt-5">
                    <i data-feather="cpu" class="me-2"></i>System settings
                </h6>
                <hr/>
                <div class="form-check form-switch">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="pcsetting5"
                        checked
                    />
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting5"
                    >View other user chat</label
                    >
                </div>
                <p class="text-muted ms-5">Allow to show public user message</p>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-light-danger btn-sm"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>
                <button type="button" class="btn btn-light-primary btn-sm">
                    Save changes
                </button>
            </div>
        </div>
    </div>
</div>
<!-- [ Header ] end -->

<!-- [ Main Content ] start -->

<div class="dash-container m-3 card" style=" justify-content: center; background: transparent !important; border:none;">
<div style=" justify-content: center; align-items: center; display: flex;"><h1><font face="Edwardian Script ITC" style="font-size: 50pt" color="#FFFFFF">
	<span style="font-weight: 400">The Lynx School </span>
	</font></h1></div>
    <div class="dash-content ">

    <div class="row">
        <div class="col-xxl-12">
            <div class="row" style="justify-content: center; align-items: center;">
                    <div class="col-md-6">
                        <div class=" text-center">
                                    <div class="row" >
                                        <div class="col-lg-3 col-3">
                                            <a href="{{ route('dashboard') }}">
                                            <div class="card mb-5 hover-border-primary">
                                                <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                                    <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                        {{__('ACCOUNT')}}
                                                        <br/>
                                                        {{__('MODULE')}}
                                                    </div>
                                                </div>
                                              </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-3">
                                            <div class="card mb-5 hover-border-primary">
                                                <a href="{{ route('hrm.dashboard') }}">
                                                    <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                                
                                                    <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                        {{__('STUDENT')}}
                                                        <br />
                                                        {{__('MODULE')}}
                                                    </div>
                                                    
                                                    </div>
                                                </a>
                                              </div>
                                        </div>
                                        <div class="col-lg-3 col-3">
                                            <div class="card mb-5 hover-border-primary">
                                                <a href="{{ route('hrm.dashboard') }}">
                                                <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                            
                                                  <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                      {{__('HRM')}}
                                                    <br />
                                                    {{__('MODULE')}}
                                                  </div>
                                                 
                                                </div>
                                                </a>
                                              </div>
                                        </div>
                                        <div class="col-lg-3 col-3">
                                            <div class="card mb-5 hover-border-primary">
                                                <a href="{{ route('dashboard') }}">
                                                <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                               
                                                <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                      {{__('INVENTORY')}}
                                                    <br />
                                                        {{__('MODULE')}}
                                                  </div>
                                                 
                                                </div>
                                              </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-3">
                                            <div class="card mb-5 hover-border-primary">
                                                <a href="{{ route('data.import.form') }}">
                                                <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                               
                                                <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                      {{__('Imports')}}                                                  
                                                  </div>
                                                 
                                                </div>
                                              </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-3">
                                            <div class="card mb-5 hover-border-primary">
                                                <a href="{{ route('data.import.hrform') }}">
                                                <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                               
                                                <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                      {{__('HR Imports')}}                                                  
                                                  </div>
                                                </div>
                                              </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                        {{-- </div>
                    </div> --}}
            </div>
        </div>
    </div>

    <!-- [ Main Content ] end -->
    </div>
</div>

<div class="modal fade" id="commonModal" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="commonModalOver" tabindex="-1" role="dialog" aria-labelledby="commonModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commonModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
    <div id="liveToast" class="toast text-white fade" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body text-white"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
        </div>
    </div>
</div>
{{-- @include('partials.admin.footer') --}}
@include('Chatify::layouts.footerLinks')


<script>
 document.addEventListener('DOMContentLoaded', () => {
    const htmlElement = document.documentElement;
    const logo = document.getElementById('main_logo_0123');

    function updateLogoVisibility() {
        const menuAnimateValue = htmlElement.getAttribute('data-menu-animate');
        const dataBehaviour = htmlElement.getAttribute('data-behaviour');

        if (menuAnimateValue === 'show' || dataBehaviour === 'pinned') {
            logo.style.display = 'block';
        } else if (menuAnimateValue === 'hidden' || dataBehaviour === 'unpinned') {
            logo.style.display = 'none';
        }
    }

    if (window.matchMedia("(min-width: 769px)").matches) {
        // Run the script for larger screens
        updateLogoVisibility();
        console.log('Running script for large screens');

        // Attach MutationObserver for larger screens
        const observer = new MutationObserver(updateLogoVisibility);
        observer.observe(htmlElement, { 
            attributes: true, 
            attributeFilter: ['data-menu-animate', 'data-behaviour'] 
        });

    } else {
        
    }
});


//     document.addEventListener('DOMContentLoaded', () => {
//     // Function to update logo visibility
//     function updateLogoVisibility() {
//         const htmlElement = document.documentElement; // Select the <html> element
//         const menuAnimateValue = htmlElement.getAttribute('data-menu-animate');
//         const dataBehaviour = htmlElement.getAttribute('data-behaviour');

//         // Get the logo element
//         const logo = document.getElementById('main_logo_0123');

//         if (logo) { // Check if the logo element exists
//             if (menuAnimateValue === 'show' || dataBehaviour === 'pinned') {
//                 console.log('show or pinned');
//                 logo.style.display = 'block'; // Show the logo
//             } else if (menuAnimateValue === 'hidden' || dataBehaviour === 'unpinned') {
//                 console.log('hidden or unpinned');
//                 logo.style.display = 'none'; // Hide the logo
//             }
//         }
//     }

//     // Initial call to set the correct state
//     updateLogoVisibility();

//     // Run the update function every 1 seconds
//     setInterval(updateLogoVisibility, 1000);
// });

</script>

</body>
</html>

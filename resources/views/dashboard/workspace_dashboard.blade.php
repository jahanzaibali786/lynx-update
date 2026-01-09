@extends('layouts.admin')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@push('css-page')
    <style>
        .glide__slide {
            width:200px !important;
        }
    </style>
@endpush
@push('script-page')
    <script></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clientuser.dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Workspace') }}</li>
@endsection
@php
    $setting = \App\Models\Utility::settings();
@endphp
{{-- @php
    $stat = 'Welcome';
    date_default_timezone_set('Asia/Karachi'); // Set the timezone to Pakistan
    $currentTime = date('H:i'); // Get the current time in 24-hour format
    if ($currentTime >= '05:00' && $currentTime < '11:30') {
        $stat = 'Good Morning';
    } elseif ($currentTime >= '11:30' && $currentTime < '17:30') {
        $stat = 'Good Afternoon';
    } elseif ($currentTime >= '17:30' && $currentTime < '20:00') {
        $stat = 'Good Evening';
    } else {
        $stat = 'Good Night';
    }
@endphp --}}
@section('content')
    <div class=" row">
        {{-- <!-- Title and Top Buttons Start -->
        <div class="page-title-container">
            <div class="row">
                <!-- Title Start -->
                <div class="col-12 col-md-7">
                    <span class="align-middle text-muted d-inline-block lh-1 pb-2 pt-2 text-small">Home</span>
                    <h1 class="mb-0 pb-0 display-6" id="title">{{ $stat }}, {{ $user->name }}</h1>
                </div>
                <!-- Title End -->
            </div>
        </div>
        <!-- Title and Top Buttons End --> --}}

        <div class="row">
            <div class="col-xl-12">
                <!-- Stats Start -->
                <h2 class="small-title">Stats</h2>
                <div class="row gx-2">
                    <div class="col-12 p-0">
                        <div class="glide glide-small" id="statsCarousel">
                            <div class="glide__track" data-glide-el="track">
                                <div class="glide__slides">
                                    <div class="glide__slide">
                                        <div class="card mb-5 hover-border-primary">
                                            <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                            <div class="account-dashboard-svg">
                                            <svg fill="#100773" height="64px" width="64px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-251.08 -251.08 994.47 994.47" xml:space="preserve" stroke="#100773" stroke-width="0.00492308"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <polygon points="201.519,290.813 201.519,191.659 181.827,191.659 181.827,290.813 82.673,290.813 82.673,310.505 181.827,310.505 181.827,409.649 201.519,409.649 201.519,310.505 300.673,310.505 300.673,290.813 "></polygon> </g> </g> <g> <g> <path d="M109.077,0.053v108.971H0v383.231h383.346V383.293h108.962V0.053H109.077z M363.654,383.293v89.269H19.692V128.716h89.385 h254.577V383.293z M472.615,363.601h-89.269V109.024H128.769V19.745h343.846V363.601z"></path> </g> </g> </g></svg>
                                                            </div>
                                                <a href="{{ route('space.index') }}">
                                                    <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                        Total
                                                        <br />
                                                        Space
                                                    </div>
                                                    <div class="display-6 text-primary">{{ $space }}</div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="glide__slide">
                                        <div class="card mb-5 hover-border-primary">
                                            <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                            <div class="account-dashboard-svg">
                                                        <svg width="64px" height="64px" viewBox="-522.24 -522.24 2068.48 2068.48" fill="#100773" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M944.8 1012c-5.6 0-9.6-4.8-9.6-10.4V637.6c0-5.6 4.8-10.4 9.6-10.4h28c5.6 0 10.4 4.8 10.4 10.4v364c0 5.6-4.8 10.4-10.4 10.4h-28z m-140 0c-5.6 0-10.4-4.8-10.4-10.4v-280c0-5.6 4.8-9.6 10.4-9.6h28c5.6 0 10.4 4.8 10.4 9.6v280c0 5.6-4.8 10.4-10.4 10.4h-28z m-140 0c-5.6 0-10.4-4.8-10.4-10.4v-280c0-5.6 4.8-9.6 10.4-9.6h28c5.6 0 10.4 4.8 10.4 9.6v280c0 5.6-4.8 10.4-10.4 10.4h-28z m-602.4 0c-28.8 0-52-23.2-52-52V680c0-28.8 23.2-52 52-52h280c28.8 0 52 23.2 52 52v280c0 28.8-23.2 52-52 52h-280z m28-336c-17.6 0-32 14.4-32 32v224c0 17.6 14.4 32 32 32h224c17.6 0 32-14.4 32-32v-224c0-17.6-14.4-32-32-32h-224z m406.4 224c-5.6 0-9.6-4.8-9.6-9.6V637.6c0-5.6 4.8-10.4 9.6-10.4h28c5.6 0 10.4 4.8 10.4 10.4v252c0 5.6-4.8 9.6-10.4 9.6h-28zM20.8 536c-5.6 0-10.4-4.8-10.4-9.6v-28c0-5.6 4.8-9.6 10.4-9.6h980c5.6 0 9.6 4.8 9.6 9.6v28c0 5.6-4.8 9.6-9.6 9.6H20.8z m657.6-140c-28.8 0-52-23.2-52-52V64c0-28.8 23.2-52 52-52h280c28.8 0 52 23.2 52 52v280c0 28.8-23.2 52-52 52h-280z m28-336c-17.6 0-32 14.4-32 32v224c0 17.6 14.4 32 32 32h224c17.6 0 32-14.4 32-32v-224c0-17.6-14.4-32-32-32h-224z m-209.6 336c-5.6 0-9.6-4.8-9.6-10.4V21.6c0-5.6 4.8-10.4 9.6-10.4h28c5.6 0 10.4 4.8 10.4 10.4v364c0 5.6-4.8 10.4-10.4 10.4h-28z m-434.4 0c-28.8 0-52-23.2-52-52V64c0-28.8 23.2-52 52-52h280c28.8 0 52 23.2 52 52v280c0 28.8-23.2 52-52 52h-280z m28-336c-17.6 0-32 14.4-32 32v224c0 17.6 14.4 32 32 32h224c17.6 0 32-14.4 32-32v-224c0-17.6-14.4-32-32-32h-224z" fill=""></path></g></svg>
        </div>
                                                        <a href="{{ route('space_details',['type' => 'used']) }}">
                                                    <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                        Used
                                                        <br />
                                                        space
                                                    </div>
                                                    <div class="display-6 text-primary">{{ $use_space }}</div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="glide__slide">
                                        <div class="card mb-5 hover-border-primary">
                                            <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                            <div class="account-dashboard-svg">
                                            <svg fill="#100773" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="-240.26 -240.26 942.56 942.56" xml:space="preserve" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M457.83,158.441c-0.021-0.028-0.033-0.058-0.057-0.087l-50.184-62.48c-0.564-0.701-1.201-1.305-1.879-1.845 c-2.16-2.562-5.355-4.225-8.967-4.225H65.292c-3.615,0-6.804,1.661-8.965,4.225c-0.678,0.54-1.316,1.138-1.885,1.845l-50.178,62.48 c-0.023,0.029-0.034,0.059-0.057,0.087C1.655,160.602,0,163.787,0,167.39v193.07c0,6.5,5.27,11.771,11.77,11.771h438.496 c6.5,0,11.77-5.271,11.77-11.771V167.39C462.037,163.787,460.381,160.602,457.83,158.441z M408.516,134.615l16.873,21.005h-16.873 V134.615z M384.975,113.345v42.274H296.84c-2.514,0-4.955,0.805-6.979,2.293l-58.837,43.299l-58.849-43.305 c-2.023-1.482-4.466-2.287-6.978-2.287H77.061v-42.274H384.975z M53.523,155.62H36.65l16.873-21.005V155.62z M438.498,348.69H23.54 V179.16h137.796l62.711,46.148c4.15,3.046,9.805,3.052,13.954-0.005l62.698-46.144h137.799V348.69L438.498,348.69z"></path> </g> </g></svg>
        </div>

                                                <a href="{{ route('space_details',['type' => 'vacant']) }}">
                                                    <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                        Vacant
                                                        <br />
                                                        Space
                                                    </div>
                                                </a>
                                                <div class="display-6 text-primary">{{ $free_space }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="glide__slide">
                                        <div class="card mb-5 hover-border-primary">
                                            <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                            <div class="account-dashboard-svg">
       <svg fill="#100773" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-243.04 -243.04 982.08 982.08" xml:space="preserve" width="64px" height="64px" stroke="#100773" stroke-width="0.004960000000000001"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M187.872,51.752c10.184,8.144,16.832,19.496,19.152,32.04l-28.056-11.224l-5.936,14.856L216,104.616l42.968-17.184 l-5.936-14.856l-29.688,11.872c-2.328-17.712-11.288-33.84-25.472-45.184C185.584,29.416,170.144,24,154.392,24H152v16h2.392 C166.52,40,178.416,44.168,187.872,51.752z"></path> <path d="M308.128,444.248c-10.184-8.144-16.832-19.496-19.152-32.04l28.056,11.224l5.936-14.856L280,391.384l-42.968,17.184 l5.936,14.856l29.688-11.872c2.328,17.712,11.288,33.84,25.472,45.184c12.288,9.848,27.728,15.264,43.48,15.264H344v-16h-2.392 C329.48,456,317.584,451.832,308.128,444.248z"></path> <path d="M424,352c-11.504,0-22.344,2.776-32,7.6V217.544l16.568,41.424l14.856-5.936l-11.872-29.688 c17.712-2.328,33.84-11.288,45.184-25.472c9.848-12.288,15.264-27.728,15.264-43.48V152h-16v2.392 c0,12.136-4.168,24.024-11.752,33.488c-8.144,10.184-19.496,16.832-32.04,19.152l11.224-28.056l-14.856-5.936L392,214.456V136.4 c9.656,4.816,20.496,7.6,32,7.6c39.704,0,72-32.296,72-72S463.704,0,424,0c-39.704,0-72,32.296-72,72 c0,27.872,15.944,52.04,39.16,64H104.84C128.056,124.04,144,99.872,144,72c0-39.704-32.296-72-72-72S0,32.296,0,72 s32.296,72,72,72c11.504,0,22.344-2.776,32-7.6v142.056l-16.568-41.424l-14.856,5.936l11.872,29.688 c-17.712,2.328-33.84,11.288-45.184,25.472C29.416,310.416,24,325.856,24,341.608V344h16v-2.392 c0-12.136,4.168-24.024,11.752-33.488c8.144-10.184,19.496-16.832,32.04-19.152l-11.224,28.056l14.856,5.936L104,281.544V359.6 c-9.656-4.824-20.496-7.6-32-7.6c-39.704,0-72,32.296-72,72c0,39.704,32.296,72,72,72s72-32.296,72-72 c0-27.872-15.944-52.04-39.16-64h286.328C367.944,371.96,352,396.128,352,424c0,39.704,32.296,72,72,72c39.704,0,72-32.296,72-72 C496,384.296,463.704,352,424,352z M96,122.52c-7.288,3.472-15.408,5.48-24,5.48s-16.712-2.008-24-5.48V112 c0-13.232,10.768-24,24-24s24,10.768,24,24V122.52z M56,56c0-8.824,7.176-16,16-16s16,7.176,16,16s-7.176,16-16,16 S56,64.824,56,56z M96,474.52c-7.288,3.472-15.408,5.48-24,5.48s-16.712-2.008-24-5.48V464c0-13.232,10.768-24,24-24 s24,10.768,24,24V474.52z M56,408c0-8.824,7.176-16,16-16s16,7.176,16,16c0,8.824-7.176,16-16,16S56,416.824,56,408z M128,424 c0,15.256-6.152,29.088-16.08,39.2c-0.272-13.448-7.144-25.312-17.568-32.36C100.288,425.032,104,416.952,104,408 c0-17.648-14.352-32-32-32s-32,14.352-32,32c0,8.952,3.712,17.032,9.648,22.84c-10.424,7.056-17.296,18.912-17.568,32.36 C22.152,453.088,16,439.256,16,424c0-30.872,25.128-56,56-56S128,393.128,128,424z M94.352,78.84 C100.288,73.032,104,64.952,104,56c0-17.648-14.352-32-32-32S40,38.352,40,56c0,8.952,3.712,17.032,9.648,22.84 C39.224,85.896,32.352,97.752,32.08,111.2C22.152,101.088,16,87.256,16,72c0-30.872,25.128-56,56-56s56,25.128,56,56 c0,15.256-6.152,29.088-16.08,39.2C111.648,97.752,104.776,85.888,94.352,78.84z M376,344H120V216h256V344z M376,200H120v-48h256 V200z M448,122.52c-7.288,3.472-15.408,5.48-24,5.48c-8.592,0-16.712-2.008-24-5.48V112c0-13.232,10.768-24,24-24 s24,10.768,24,24V122.52z M408,56c0-8.824,7.176-16,16-16c8.824,0,16,7.176,16,16s-7.176,16-16,16C415.176,72,408,64.824,408,56z M384.08,111.2C374.152,101.088,368,87.256,368,72c0-30.872,25.128-56,56-56s56,25.128,56,56c0,15.256-6.152,29.088-16.08,39.2 c-0.272-13.448-7.144-25.312-17.568-32.36C452.288,73.032,456,64.952,456,56c0-17.648-14.352-32-32-32s-32,14.352-32,32 c0,8.952,3.712,17.032,9.648,22.84C391.216,85.896,384.344,97.752,384.08,111.2z M448,474.52c-7.288,3.472-15.408,5.48-24,5.48 c-8.592,0-16.712-2.008-24-5.48V464c0-13.232,10.768-24,24-24s24,10.768,24,24V474.52z M408,408c0-8.824,7.176-16,16-16 c8.824,0,16,7.176,16,16c0,8.824-7.176,16-16,16C415.176,424,408,416.824,408,408z M463.92,463.2 c-0.272-13.448-7.144-25.312-17.568-32.36C452.288,425.032,456,416.952,456,408c0-17.648-14.352-32-32-32s-32,14.352-32,32 c0,8.952,3.712,17.032,9.648,22.84c-10.432,7.056-17.304,18.912-17.568,32.36C374.152,453.088,368,439.256,368,424 c0-30.872,25.128-56,56-56s56,25.128,56,56C480,439.256,473.848,453.088,463.92,463.2z"></path> <rect x="136" y="168" width="16" height="16"></rect> <rect x="168" y="168" width="16" height="16"></rect> <rect x="200" y="168" width="16" height="16"></rect> <path d="M264,232H136v96h128V232z M248,312h-96v-64h96V312z"></path> <rect x="280" y="232" width="80" height="16"></rect> <rect x="280" y="264" width="80" height="16"></rect> <rect x="280" y="296" width="80" height="16"></rect> </g> </g> </g> </g></svg>
                                        </div>  


                                                <a href="{{ route('space.index') }}">
                                                    <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                        Total
                                                        <br />
                                                        Visiter
                                                    </div>
                                                    <div class="display-6 text-primary">{{ $visit }}</div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="glide__slide">
                                        <div class="card mb-5 hover-border-primary">
                                            <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                            <div class="account-dashboard-svg">
                                            <svg width="64px" height="64px" viewBox="-12.5 -12.5 50.00 50.00" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M12.2802 3.39001H4.99023C3.92937 3.39001 2.91195 3.81138 2.1618 4.56152C1.41166 5.31167 0.990234 6.32915 0.990234 7.39001V17.39C0.990234 18.4509 1.41166 19.4682 2.1618 20.2184C2.91195 20.9685 3.92937 21.39 4.99023 21.39H16.9902C18.0511 21.39 19.0685 20.9685 19.8187 20.2184C20.5688 19.4682 20.9902 18.4509 20.9902 17.39V12.1" stroke="#100773" stroke-width="0.425" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M15.6191 11.51C14.353 12.6619 12.7196 13.328 11.009 13.39C7.79903 13.39 4.59906 10.72 1.78906 5.39001" stroke="#100773" stroke-width="0.425" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M19.0098 1.39001L20.4198 3.66002L23.0098 4.30002L21.2898 6.34001L21.4798 9.00002L19.0098 8.00002L16.5398 9.00002L16.7198 6.34001L15.0098 4.30002L17.5998 3.66002L19.0098 1.39001Z" stroke="#100773" stroke-width="0.425" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
        </div>
                                                <a href="{{ route('space.index') }}">
                                                    <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                        Total
                                                        <br />
                                                        Mail
                                                    </div>
                                                    <div class="display-6 text-primary">{{ $mail }}</div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Stats End -->

            <div class="row">
                <!-- Booking Start -->
                <div class="col-xl-12 mb-5">
                    <div class="d-flex justify-content-between">
                        <h2 class="small-title">Bookings</h2>
                        <button class="btn btn-icon btn-icon-end btn-xs btn-background-alternate p-0 text-small"
                            type="button">
                            <a href="#" data-size="md" data-url="{{ route('bookings.create') }}"
                                data-ajax-popup="true" data-bs-toggle="{{ __('Create Booking') }}" id="createBookingLink"><span
                                    class="align-bottom">Add New Booking</span></a>
                            <i data-acorn-icon="chevron-right" class="align-middle" data-acorn-size="12"></i>
                        </button>
                    </div>
                    <div class="card h-xl-100-card hover-border-primary">
                        <div class="card-header border-0 pb-0 ">
                            <div class="row">
                                <div class="col-lg-12">
                                    {{-- <a href="#" data-size="md" data-url="{{ route('bookings.create') }}" style="float: right; margin:1%" data-ajax-popup="true"  data-bs-title="{{__('Booking')}}" data-bs-toggle="{{__('Create Booking')}}"  id="createBookingLink" class="btn btn-sm btn-primary">
                                            Create
                                        </a> --}}
                                    <select class="form-control" name="space_id" id="space_id"
                                        style="float: right;width: 150px;" onchange="get_data()">\
                                        @foreach ($spaces as $space)
                                            <option value="{{ $space->id }}">{{ $space->name }}</option>
                                        @endforeach
                                    </select>

                                    <input type="hidden" id="task_calendar" value="{{ url('/') }}">
                                </div>

                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <div id='calendar' class='calendar'></div>
                        </div>
                    </div>
                </div>
                <!-- Booking End -->
                <div class="col-xl-6 mb-5">
                    <div class="d-flex justify-content-between">
                        <h2 class="small-title"></h2>
                        <button class="btn btn-icon btn-icon-end btn-xs btn-background-alternate p-0 text-small"
                            type="button">
                            <a href="#" data-size="md" data-url="{{ route('isvisitor.create') }}" data-ajax-popup="true"  data-bs-toggle="{{ __('Create Visitor') }}"><span class="align-bottom">Add New Visitor</span></a>
                            <i data-acorn-icon="chevron-right" class="align-middle" data-acorn-size="12"></i>
                        </button>
                    </div>

                    <div class="card hover-border-primary">
                        <div class="card-header">
                            <h5>Your Vistors</h5>
                        </div>
                        <div class="card-body" style="min-height: 250px;">
                            <div class="table-responsive">
                                @if (count($visitors) > 0)
                                    <table class="table align-items-center">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Time') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @foreach ($visitors as $visitor)
                                                <tr>
                                                    <td>{{ $visitor->name }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($visitor->date_time) }}</td>
                                                    <td>{{ \Auth::user()->timeFormat($visitor->date_time) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="p-2 text-primary">
                                        {{ __('No visitors yet.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-xl-6 mb-5">
                    <div class="d-flex justify-content-between">
                        <h2 class="small-title"></h2>
                        <button class="btn btn-icon btn-icon-end btn-xs btn-background-alternate p-0 text-small" type="button">
                            <a href="#" data-size="md" data-url="{{ route('ismail.create') }}" data-ajax-popup="true" data-bs-toggle="{{ __('Create Mail') }}""  >Add New Mail</span></a>
                            <i data-acorn-icon="chevron-right" class="align-middle" data-acorn-size="12"></i>
                        </button>
                       
                    </div>
        
                    <div class="card hover-border-primary">
                        <div class="card-header">
                            <h5>Your Mails</h5>
                        </div>
                        <div class="card-body" style="min-height: 250px;">
                            <div class="table-responsive">
                                @if (count($mails) > 0)
                                    <table class="table align-items-center">
                                        <thead>
                                            <tr>
                                                <th>{{__('Name')}}</th>
                                                <th>{{__('Date')}}</th>
                                                <th>{{__('Price')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @foreach ($mails as $mail)
                                                <tr>
                                                    <td>
                                                        {{ (!empty($mail->name)) ? $mail->name : '-' }}
                                                    </td>
                                                    <td>
                                                        {{ (!empty($mail->date)) ? $mail->date : '-' }}
                                                    </td>
                                                    <td>
                                                        {{ (!empty($mail->price)) ? $mail->price : '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="p-2 text-primary">
                                        {{ __('No visitors yet.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
        
                </div>
            </div>
        </div>
    </div>


    </div>
@endsection


@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $("#createBookingLink").on("click", function() {
                var space_id = $('#space_id :selected').val();
                $("#createBookingLink").attr("data-url", "{{ route('bookings.create') }}/" + space_id);
            });

            get_data();
        });

        function get_data() {
            var calender_type = 'local_calender';
            var space_id = $('#space_id :selected').val();
            $('#calendar').removeClass('local_calender');
            $('#calendar').removeClass('goggle_calender');
            if (calender_type == undefined) {
                $('#calendar').addClass('local_calender');
            }
            $('#calendar').addClass(calender_type);
            $.ajax({
                url: $("#task_calendar").val() + "/bookingcalendar/get_task_data",

                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'calender_type': calender_type,
                    'space_id': space_id
                },
                success: function(data) {
                    // console.log(data);
                    (function() {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {

                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },

                            themeSystem: 'bootstrap',
                            slotDuration: '00:15:00',
                            slotMinTime: '07:00:00', // Set your desired start time here
                            slotMaxTime: '23:00:00', // Set your desired end time here
                            allDaySlot: true,
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            initialView: 'dayGridMonth',
                            dayMaxEvents: true,
                            height: 'auto',
                            events: data,
                        });

                        calendar.render();
                    })();
                }
            });
        }
    </script>
@endpush

@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clientuser.dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('HRM') }}</li>
@endsection
@php
    $setting = \App\Models\Utility::settings();
@endphp
@php
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
@endphp
@section('content')
    <div class=" row">
        <!-- Title and Top Buttons Start -->
        <div class="page-title-container">
            <div class="row">
                <!-- Title Start -->
                <div class="col-12 col-md-7">
                    {{-- <span class="align-middle text-muted d-inline-block lh-1 pb-2 pt-2 text-small">Home</span> --}}
                    <h1 class="mb-0 pb-0 display-6" id="title">{{ $stat }}, {{ $user->name }}</h1>
                </div>
                <!-- Title End -->
            </div>
        </div>
        <!-- Title and Top Buttons End -->

        <div class="row">
            {{-- <div class="col-xl-4 mb-5">
        <!-- About Start -->
        <h2 class="small-title">About</h2>
        <div class="card h-100-card">
          <div class="card-body">
            <div class="d-flex align-items-center flex-column mb-4">
              <div class="d-flex align-items-center flex-column">
                <div class="sw-13 position-relative mb-3">
                  <img src="img/profile/profile-1.webp" class="img-fluid rounded-xl" alt="thumb" />
                </div>
                <div class="h5 mb-0">{{$user->name}}</div>
              </div>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <div class="text-center">
                <p class="text-small text-muted mb-1">BLOOD</p>
                <p>A+</p>
              </div>
              <div class="text-center">
                <p class="text-small text-muted mb-1">AGE</p>
                <p>27</p>
              </div>
              <div class="text-center">
                <p class="text-small text-muted mb-1">WEIGHT</p>
                <p>64</p>
              </div>
             
            </div>
            <div class="mb-5">
              <p class="text-small text-muted mb-2">DIOGNOSTICS</p>
              <div class="row g-0 mb-2">
                <div class="col-auto">
                  <div class="sw-3 me-1">
                    <i data-acorn-icon="lungs" class="text-primary" data-acorn-size="17"></i>
                  </div>
                </div>
                <div class="col text-alternate">Allergic Rhinitis</div>
              </div>
              <div class="row g-0 mb-2">
                <div class="col-auto">
                  <div class="sw-3 me-1">
                    <i data-acorn-icon="brain" class="text-primary" data-acorn-size="17"></i>
                  </div>
                </div>
                <div class="col text-alternate">Diabetic Ketoacidosis</div>
              </div>
              <div class="row g-0 mb-2">
                <div class="col-auto">
                  <div class="sw-3 me-1">
                    <i data-acorn-icon="stomach" class="text-primary" data-acorn-size="17"></i>
                  </div>
                </div>
                <div class="col text-alternate">Cervical Spondylosis</div>
              </div>
            </div>

            <div class="mb-5">
              <p class="text-small text-muted mb-2">CONTACT</p>
              <div class="row g-0 mb-2">
                <div class="col-auto">
                  <div class="sw-3 me-1">
                    <i data-acorn-icon="phone" class="text-primary" data-acorn-size="17"></i>
                  </div>
                </div>
                <div class="col text-alternate">+6443884455</div>
              </div>
              <div class="row g-0 mb-2">
                <div class="col-auto">
                  <div class="sw-3 me-1">
                    <i data-acorn-icon="email" class="text-primary" data-acorn-size="17"></i>
                  </div>
                </div>
                <div class="col text-alternate">aliciaowens@gmail.com</div>
              </div>
              <div class="row g-0 mb-2">
                <div class="col-auto">
                  <div class="sw-3 me-1">
                    <i data-acorn-icon="pin" class="text-primary" data-acorn-size="17"></i>
                  </div>
                </div>
                <div class="col text-alternate">Wellington New Zealand</div>
              </div>
            </div>

            <div>
              <p class="text-small text-muted mb-2">NOTES</p>
              <div class="row g-0">
                <div class="col-auto">
                  <div class="sw-3 me-1">
                    <i data-acorn-icon="warning-hexagon" class="text-primary" data-acorn-size="17"></i>
                  </div>
                </div>
                <div class="col text-alternate align-middle">Penicillin Allergy</div>
              </div>
            </div>
          </div>
        </div>
        <!-- About End -->
      </div> --}}
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
                                            <span class="position-absolute e-3 t-4 z-index-1">
                                                <i data-acorn-icon="check" class="text-primary" data-acorn-size="14"></i>
                                            </span>
                                            <div
                                                class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                                <div
                                                    class="sw-8 sh-8 rounded-xl d-flex justify-content-center align-items-center border border-primary">
                                                    <i data-acorn-icon="blood" class="text-primary"></i>
                                                </div>
                                                <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                    Meeting 
                                                    <br />
                                                    Hours
                                                </div>
                                                <div class="display-6 text-primary">{{$hours}}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="glide__slide">
                                        <div class="card mb-5 hover-border-primary">
                                            <span class="position-absolute e-3 t-4 z-index-1">
                                                <i data-acorn-icon="check" class="text-primary" data-acorn-size="14"></i>
                                            </span>
                                            <div
                                                class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                                <div
                                                    class="sw-8 sh-8 rounded-xl d-flex justify-content-center align-items-center border border-primary">
                                                    <i data-acorn-icon="heart" class="text-primary"></i>
                                                </div>
                                                <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                    Employees
                                                    <br />
                                                    
                                                </div>
                                                <div class="display-6 text-primary">{{$employees}}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="glide__slide">
                                        <div class="card mb-5 hover-border-primary">
                                            <span class="position-absolute e-3 t-4 z-index-1">
                                                <i data-acorn-icon="chevron-bottom" class="text-primary"
                                                    data-acorn-size="14"></i>
                                            </span>
                                            <div
                                                class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                                <div
                                                    class="sw-8 sh-8 rounded-xl d-flex justify-content-center align-items-center border border-primary">
                                                    <i data-acorn-icon="laboratory" class="text-primary"></i>
                                                </div>
                                                <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                    Mails
                                                    <br />
                                                    This Month
                                                </div>
                                                <div class="display-6 text-primary">{{$mail}}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="glide__slide">
                                        <div class="card mb-5 hover-border-primary">
                                            <span class="position-absolute e-3 t-4 z-index-1">
                                                <i data-acorn-icon="chevron-top" class="text-primary"
                                                    data-acorn-size="14"></i>
                                            </span>
                                            <div
                                                class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                                <div
                                                    class="sw-8 sh-8 rounded-xl d-flex justify-content-center align-items-center border border-primary">
                                                    <i data-acorn-icon="weight" class="text-primary"></i>
                                                </div>
                                                <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                    Visiter
                                                    <br />
                                                    This Month
                                                </div>
                                                <div class="display-6 text-primary">{{$visit}}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="glide__slide">
                                        <div class="card mb-5 hover-border-primary">
                                            <span class="position-absolute e-3 t-4 z-index-1">
                                                <i data-acorn-icon="chevron-top" class="text-primary"
                                                    data-acorn-size="14"></i>
                                            </span>
                                            <div
                                                class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                                <div
                                                    class="sw-8 sh-8 rounded-xl d-flex justify-content-center align-items-center border border-primary">
                                                    <i data-acorn-icon="weight" class="text-primary"></i>
                                                </div>
                                                <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                                    Body Mass
                                                    <br />
                                                    Index
                                                </div>
                                                <div class="display-6 text-primary">29.4</div>
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
                    <!-- Appointments Start -->
                    <div class="col-xl-6 mb-5">
                        <div class="d-flex justify-content-between">
                            <h2 class="small-title">Bookings</h2>
                            <button class="btn btn-icon btn-icon-end btn-xs btn-background-alternate p-0 text-small"
                                type="button">
                                <a href="{{ route('booking.calendar', ['all']) }}"><span class="align-bottom">Add  New</span></a>
                                <i data-acorn-icon="chevron-right" class="align-middle" data-acorn-size="12"></i>
                            </button>
                        </div>
                        <div class="card h-xl-100-card hover-border-primary" >
                            <div class="card-header border-0 pb-0 d-flex justify-content-center">
                                <div class="glide-tab-container">
                                    <div class="glide glide-tab" id="appointmentsCarousel">
                                        <div id="calendar" class="compact h-100"></div>
                                        <div class="glide__track" data-glide-el="track">
                                            <div class="glide__slides nav nav-pills" role="tablist">
                                                @for ($i = 0; $i < 15; $i++)
                                                    <div class="glide__slide @if ($i == 0) active @endif"
                                                        data-bs-toggle="tab" data-bs-target="#day{{ $i + 1 }}"
                                                        role="tab" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                                                        <button
                                                            class="btn btn-foreground hover-outline px-1 py-3 rounded-xl sw-4"
                                                            type="button">
                                                            <div class="text-alternate mb-2">
                                                                {{ substr(\Carbon\Carbon::now()->addDays($i)->format('l'),0,2) }}
                                                            </div>
                                                            <div class="text-primary">
                                                                {{ \Carbon\Carbon::now()->addDays($i)->format('j') }}</div>
                                                        </button>
                                                    </div>
                                                @endfor

                                            </div>
                                        </div>
                                        <div class="glide__arrows" data-glide-el="controls">
                                            <button class="btn btn-icon btn-icon-only btn-link left-arrow btn-sm mt-3"
                                                data-glide-dir="<">
                                                <i data-acorn-icon="chevron-left"></i>
                                            </button>
                                            <button class="btn btn-icon btn-icon-only btn-link right-arrow btn-sm mt-3"
                                                data-glide-dir=">">
                                                <i data-acorn-icon="chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-3">
                                <div class="tab-content">
                                    @for ($i = 0; $i < 12; $i++)
                                        {{-- <div class="glide__slide @if ($i == 0) active @endif" data-bs-toggle="tab" data-bs-target="#day{{ $i + 1 }}" role="tab" aria-selected="{{ $i === 0 ? 'true' : 'false' }}"> --}}
                                        <div class="tab-pane fade @if ($i == 0) active @endif show mb-n3"
                                            id="day{{ $i + 1 }}" role="tabpanel">
                                            <div class="mb-4 text-primary text-center">
                                                {{ \Carbon\Carbon::now()->addDays($i)->format('d F Y, l') }}</div>
                                                @php
                                                   $bookings = \App\Models\Booking::with('space')->where('owned_by', \Auth::user()->ownedId())->whereDate('start_date',\Carbon\Carbon::now()->addDays($i)->format('Y-m-d'))->get();
                                                @endphp
                                            @if (count($bookings)>0)
                                                @for ($j = 0; $j < count($bookings); $j++)
                                                    <div class="row g-0 mb-3">
                                                        <div class="col-auto">
                                                            <div
                                                                class="sw-5 d-inline-block d-flex align-items-center pt-1">
                                                                <i data-acorn-icon="calendar"></i>
                                                                {{-- <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="acorn-icons acorn-icons-notebook-1 undefined"><path d="M3 5.5C3 4.09554 3 3.39331 3.33706 2.88886C3.48298 2.67048 3.67048 2.48298 3.88886 2.33706C4.39331 2 5.09554 2 6.5 2H13.5C14.9045 2 15.6067 2 16.1111 2.33706C16.3295 2.48298 16.517 2.67048 16.6629 2.88886C17 3.39331 17 4.09554 17 5.5V14.5C17 15.9045 17 16.6067 16.6629 17.1111C16.517 17.3295 16.3295 17.517 16.1111 17.6629C15.6067 18 14.9045 18 13.5 18H6.5C5.09554 18 4.39331 18 3.88886 17.6629C3.67048 17.517 3.48298 17.3295 3.33706 17.1111C3 16.6067 3 15.9045 3 14.5V5.5Z"></path><path d="M8 6H12M8 10H12M8 14H12M2 8H4M2 12H4"></path></svg> --}}
                                                            </div>
                                                        </div>
                                                        <div class="col">
                                                            <div
                                                                class="card-body d-flex flex-column ps-0 pt-0 pb-0 h-100 justify-content-center">
                                                                <div class="d-flex flex-column">
                                                                    <div class="text-body">{{$bookings[$j]->space->name}}</div>
                                                                    <div class="text-muted">{{ \Auth::user()->dateFormat($bookings[$j]->start_date) }} {{ \Auth::user()->timeFormat($bookings[$j]->start_date) }}</div>
                                                                    <div class="text-muted">{{ \Auth::user()->dateFormat($bookings[$j]->end_date) }} {{ \Auth::user()->timeFormat($bookings[$j]->end_date) }}</div>
                                                                    <div class="text-muted">{{$bookings[$j]->total_min}} mints</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            @else
                                                <div class="tab-pane" id="dayNone" role="tabpanel">

                                                    <div class="text-center">
                                                        <img src="img/illustration/icon-appointment.webp"
                                                            class="theme-filter" alt="Bookings" />
                                                        <p>No Bookings for the day!</p>
                                                        <button class="btn btn-icon btn-icon-start btn-primary"
                                                            type="button">
                                                            <a href="{{ route('booking.calendar', ['all']) }}"><i
                                                                    data-acorn-icon="calendar"></i></a>
                                                            <span>New Booking</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endfor

                                </div>

                            </div>
                        </div>
                    </div>
                
                    <!-- Appointments End -->

                    <!-- Your Doctors Start -->
                    <div class="col-xl-6 mb-5">
                      <div class="d-flex justify-content-between">
                        <h2 class="small-title"></h2>
                        <button class="btn btn-icon btn-icon-end btn-xs btn-background-alternate p-0 text-small"
                            type="button">
                            <a href="{{ route('isvisitor.index') }}"><span class="align-bottom">Add New</span></a>
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

                      
                        {{-- <h2 class="small-title">Your Vistors</h2>
                        <div class="card">
                            <div class="card-body mb-n3 border-last-none">
                                <div class="mb-3 pb-3 border-bottom border-separator-light">
                                    <div class="row g-0 sh-6">
                                        <div class="col-auto">
                                            <a href="Doctors.Detail.html">
                                                <img src="img/profile/profile-14.webp" class="card-img rounded-xl sh-6 sw-6"
                                                    alt="thumb" />
                                            </a>
                                        </div>
                                        <div class="col">
                                            <div
                                                class="card-body d-flex flex-row pt-0 pb-0 ps-3 pe-0 h-100 align-items-center justify-content-between">
                                                <div class="d-flex flex-column">
                                                    <a href="Doctors.Detail.html" class="body-link">Karter Kidd, M.D.</a>
                                                    <div class="text-small text-muted">Neurologist</div>
                                                </div>
                                                <div class="d-flex">
                                                    <button class="btn btn-outline-secondary btn-sm ms-1"
                                                        type="button">Schedule</button>
                                                    <button
                                                        class="btn btn-sm btn-icon btn-icon-only btn-outline-secondary ms-1"
                                                        type="button">
                                                        <i data-acorn-icon="more-vertical"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom border-separator-light">
                                    <div class="row g-0 sh-6">
                                        <div class="col-auto">
                                            <a href="Doctors.Detail.html">
                                                <img src="img/profile/profile-12.webp" class="card-img rounded-xl sh-6 sw-6"
                                                    alt="thumb" />
                                            </a>
                                        </div>
                                        <div class="col">
                                            <div
                                                class="card-body d-flex flex-row pt-0 pb-0 ps-3 pe-0 h-100 align-items-center justify-content-between">
                                                <div class="d-flex flex-column">
                                                    <a href="Doctors.Detail.html" class="body-link">Carmelo Avril,
                                                        M.B.B.S.</a>
                                                    <div class="text-small text-muted">Rheumatologist</div>
                                                </div>
                                                <div class="d-flex">
                                                    <button class="btn btn-outline-secondary btn-sm ms-1"
                                                        type="button">Schedule</button>
                                                    <button
                                                        class="btn btn-sm btn-icon btn-icon-only btn-outline-secondary ms-1"
                                                        type="button">
                                                        <i data-acorn-icon="more-vertical"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom border-separator-light">
                                    <div class="row g-0 sh-6">
                                        <div class="col-auto">
                                            <a href="Doctors.Detail.html">
                                                <img src="img/profile/profile-13.webp" class="card-img rounded-xl sh-6 sw-6"
                                                    alt="thumb" />
                                            </a>
                                        </div>
                                        <div class="col">
                                            <div
                                                class="card-body d-flex flex-row pt-0 pb-0 ps-3 pe-0 h-100 align-items-center justify-content-between">
                                                <div class="d-flex flex-column">
                                                    <a href="Doctors.Detail.html" class="body-link">Wiebe Rodolfo, M.D.</a>
                                                    <div class="text-small text-muted">Psychiatrist</div>
                                                </div>
                                                <div class="d-flex">
                                                    <button class="btn btn-outline-secondary btn-sm ms-1"
                                                        type="button">Schedule</button>
                                                    <button
                                                        class="btn btn-sm btn-icon btn-icon-only btn-outline-secondary ms-1"
                                                        type="button">
                                                        <i data-acorn-icon="more-vertical"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom border-separator-light">
                                    <div class="row g-0 sh-6">
                                        <div class="col-auto">
                                            <a href="Doctors.Detail.html">
                                                <img src="img/profile/profile-15.webp" class="card-img rounded-xl sh-6 sw-6"
                                                    alt="thumb" />
                                            </a>
                                        </div>
                                        <div class="col">
                                            <div
                                                class="card-body d-flex flex-row pt-0 pb-0 ps-3 pe-0 h-100 align-items-center justify-content-between">
                                                <div class="d-flex flex-column">
                                                    <a href="Doctors.Detail.html" class="body-link">Alma Holder, D.M.S.</a>
                                                    <div class="text-small text-muted">Ophthalmologist</div>
                                                </div>
                                                <div class="d-flex">
                                                    <button class="btn btn-outline-secondary btn-sm ms-1"
                                                        type="button">Schedule</button>
                                                    <button
                                                        class="btn btn-sm btn-icon btn-icon-only btn-outline-secondary ms-1"
                                                        type="button">
                                                        <i data-acorn-icon="more-vertical"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 border-bottom border-separator-light">
                                    <div class="row g-0 sh-6">
                                        <div class="col-auto">
                                            <a href="Doctors.Detail.html">
                                                <img src="img/profile/profile-16.webp" class="card-img rounded-xl sh-6 sw-6"
                                                    alt="thumb" />
                                            </a>
                                        </div>
                                        <div class="col">
                                            <div
                                                class="card-body d-flex flex-row pt-0 pb-0 ps-3 pe-0 h-100 align-items-center justify-content-between">
                                                <div class="d-flex flex-column">
                                                    <a href="Doctors.Detail.html" class="body-link">Isaac Mckee, D.O.</a>
                                                    <div class="text-small text-muted">Neurologist</div>
                                                </div>
                                                <div class="d-flex">
                                                    <button class="btn btn-outline-secondary btn-sm ms-1"
                                                        type="button">Schedule</button>
                                                    <button
                                                        class="btn btn-sm btn-icon btn-icon-only btn-outline-secondary ms-1"
                                                        type="button">
                                                        <i data-acorn-icon="more-vertical"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                    <!-- Your Doctors End -->
                </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-6">
                <div class="card hover-border-primary">
                    <div class="card-header">

                        <h5>{{ __('Announcement List') }}</h5>
                    </div>
                    <div class="card-body" style="min-height: 250px;">
                        <div class="table-responsive">
                            @if (count($announcements) > 0)
                                <table class="table align-items-center">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>

                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($announcements as $announcement)
                                            <tr>
                                                <td>{{ $announcement->title }}</td>
                                                <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                                <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="p-2 text-primary">
                                    {{ __('No accouncement present yet.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card hover-border-primary">
                    <div class="card-header">
                        <h5>{{ __('Meeting schedule') }}</h5>
                    </div>
                    <div class="card-body" style="min-height: 250px;">
                        <div class="table-responsive">
                            @if (count($meetings) > 0)
                                <table class="table align-items-center">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Time') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($meetings as $meeting)
                                            <tr>
                                                <td>{{ $meeting->title }}</td>
                                                <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                                <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="p-2 text-primary">
                                    {{ __('No meeting scheduled yet.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="row">
        <!-- Results Start -->
        <div class="col-xl-6 mb-5">
            <h2 class="small-title">Results</h2>
            <div class="card mb-2 sh-11 sh-md-8">
                <div class="card-body pt-0 pb-0 h-100">
                    <div class="row g-0 h-100 align-content-center">
                        <div class="col-11 col-md-7 d-flex align-items-center mb-1 mb-md-0 order-1 order-md-1">
                            <a href="Results.Detail.html" class="body-link text-truncate">
                                <i data-acorn-icon="file-text" class="sw-2 me-2 text-alternate" data-acorn-size="17"></i>
                                <span class="align-middle">blood-analysis.pdf</span>
                            </a>
                        </div>
                        <div
                            class="col-12 col-md-3 d-flex align-items-center justify-content-md-center text-muted order-3 order-md-2">
                            12.11.2021</div>
                        <div
                            class="col-1 col-md-2 d-flex align-items-center text-muted text-medium justify-content-end order-2 order-md-3">
                            <button class="btn btn-icon btn-icon-only btn-link btn-sm p-1" type="button">
                                <i data-acorn-icon="arrow-bottom"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-2 sh-11 sh-md-8">
                <div class="card-body pt-0 pb-0 h-100">
                    <div class="row g-0 h-100 align-content-center">
                        <div class="col-11 col-md-7 d-flex align-items-center mb-1 mb-md-0 order-1 order-md-1">
                            <a href="Results.Detail.html" class="body-link text-truncate">
                                <i data-acorn-icon="image" class="sw-2 me-2 text-alternate" data-acorn-size="17"></i>
                                <span class="align-middle">hearth-imaging.pdf</span>
                            </a>
                        </div>
                        <div
                            class="col-12 col-md-3 d-flex align-items-center justify-content-md-center text-muted order-3 order-md-2">
                            05.11.2021</div>
                        <div
                            class="col-1 col-md-2 d-flex align-items-center text-muted text-medium justify-content-end order-2 order-md-3">
                            <button class="btn btn-icon btn-icon-only btn-link btn-sm p-1" type="button">
                                <i data-acorn-icon="arrow-bottom"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-2 sh-11 sh-md-8">
                <div class="card-body pt-0 pb-0 h-100">
                    <div class="row g-0 h-100 align-content-center">
                        <div class="col-11 col-md-7 d-flex align-items-center mb-1 mb-md-0 order-1 order-md-1">
                            <a href="Results.Detail.html" class="body-link text-truncate">
                                <i data-acorn-icon="file-text" class="sw-2 me-2 text-alternate" data-acorn-size="17"></i>
                                <span class="align-middle">blood-analysis.pdf</span>
                            </a>
                        </div>
                        <div
                            class="col-12 col-md-3 d-flex align-items-center justify-content-md-center text-muted order-3 order-md-2">
                            02.11.2021</div>
                        <div
                            class="col-1 col-md-2 d-flex align-items-center text-muted text-medium justify-content-end order-2 order-md-3">
                            <button class="btn btn-icon btn-icon-only btn-link btn-sm p-1" type="button">
                                <i data-acorn-icon="arrow-bottom"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card sh-11 sh-md-8">
                <div class="card-body pt-0 pb-0 h-100">
                    <div class="row g-0 h-100 align-content-center">
                        <div class="col-11 col-md-7 d-flex align-items-center mb-1 mb-md-0 order-1 order-md-1">
                            <a href="Results.Detail.html" class="body-link text-truncate">
                                <i data-acorn-icon="file-text" class="sw-2 me-2 text-alternate" data-acorn-size="17"></i>
                                <span class="align-middle">allergy-test.pdf</span>
                            </a>
                        </div>
                        <div
                            class="col-12 col-md-3 d-flex align-items-center justify-content-md-center text-muted order-3 order-md-2">
                            02.11.2021</div>
                        <div
                            class="col-1 col-md-2 d-flex align-items-center text-muted text-medium justify-content-end order-2 order-md-3">
                            <button class="btn btn-icon btn-icon-only btn-link btn-sm p-1" type="button">
                                <i data-acorn-icon="arrow-bottom"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Results End -->

        <!-- Check Up Start -->
        <div class="col-xl-6 mb-5">
            <h2 class="small-title">Check Up</h2>
            <div class="card w-100 sh-100 h-xl-100-card hover-img-scale-up position-relative">
                <img src="img/banner/cta-doctor.webp" class="card-img h-100 scale position-absolute" alt="card image" />
                <div class="card-img-overlay d-flex flex-column justify-content-between bg-transparent">
                    <div>
                        <div class="cta-2 mb-0 text-black w-75 w-sm-50">Check Yourself Up</div>
                        <div class="cta-2 mb-3 text-black w-75 w-sm-50">30% Off</div>
                        <div class="w-50 text-black mb-3">
                            Liquorice caramels chupa chups bonbon. Jelly-o candy sugar chocolate cake caramels apple pie
                            lollipop jujubes.
                        </div>
                    </div>
                    <div>
                        <a href="Appointments.New.html"
                            class="btn btn-icon btn-icon-start btn-primary mt-3 stretched-link">
                            <i data-acorn-icon="chevron-right"></i>
                            <span>View</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Check Up End -->
    </div> --}}
    </div>
@endsection

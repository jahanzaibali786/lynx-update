@extends('layouts.admin')

@section('page-title')
    {{__('Task Calendar')}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">
@endpush

@php
    $setting = \App\Models\Utility::settings();
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Task Calendar')}}</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <h5>{{ __('Calendar') }}</h5>
                  
                        </div>                        
                        <div class="col-lg-6">
                            <a href="#" data-size="md" data-url="{{ route('bookings.create') }}" style="float: right; margin:1%" data-ajax-popup="true"  data-bs-title="{{__('Booking')}}" data-bs-toggle="{{__('Create Booking')}}"  id="createBookingLink" class="btn btn-sm btn-primary">
                                Create
                            </a>
                            <select class="form-control" name="space_id" id="space_id" style="float: right;width: 150px;" onchange="get_data()">\
                                @foreach ($spaces as $space)
                                <option value="{{$space->id}}">{{$space->name}}</option>
                                @endforeach
                            </select>
                          
                            <input type="hidden" id="task_calendar" value="{{url('/')}}">
                        </div>
                        {{-- <div class="col-lg-6">
                            @if (isset($setting['google_calendar_enable']) && $setting['google_calendar_enable'] == 'on')
                                <select class="form-control" name="calender_type" id="calender_type" style="float: right;width: 150px;" onchange="get_data()">
                                    <option value="goggle_calender">{{__('Google Calender')}}</option>
                                    <option value="local_calender" selected="true">{{__('Local Calender')}}</option>
                                </select>
                            @endif
                            <input type="hidden" id="task_calendar" value="{{url('/')}}">
                        </div> --}}
                    </div>
                </div>
                <div class="card-body">
                    <div id='calendar' class='calendar'></div>
                </div>
            </div>
        </div>

    </div>

@endsection


@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>

    <script type="text/javascript">

        $(document).ready(function()
        {
            $("#createBookingLink").on("click", function() {
                var space_id = $('#space_id :selected').val();
                $("#createBookingLink").attr("data-url", "{{ route('bookings.create') }}/" + space_id);
            });

            get_data();
        });
        
        function get_data()
        {
            var calender_type='local_calender';
            var space_id=$('#space_id :selected').val();
            $('#calendar').removeClass('local_calender');
            $('#calendar').removeClass('goggle_calender');
            if(calender_type==undefined){
                $('#calendar').addClass('local_calender');
            }
            $('#calendar').addClass(calender_type);
            $.ajax({
                url: $("#task_calendar").val()+"/bookingcalendar/get_task_data" ,

                method:"POST",
                data: {"_token": "{{ csrf_token() }}",'calender_type':calender_type,'space_id':space_id},
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
                            allDaySlot:true,
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

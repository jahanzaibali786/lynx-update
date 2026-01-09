@extends('layouts.admin')
@section('page-title')
    {{__('Dashboard')}}
@endsection
@push('script-page')
    <script>
        (function () {
            var options = {
                chart: {
                    height: 180,
                    type: 'area',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [{
                    name: 'Refferal',
                    data:{!! json_encode(array_values($home_data['task_overview'])) !!}
                },],
                xaxis: {
                    categories:{!! json_encode(array_keys($home_data['task_overview'])) !!},
                },
                colors: ['#100773'],
                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                // markers: {
                //     size: 4,
                //     colors: ['#100773', '#FF3A6E',],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // }
            };
            var chart = new ApexCharts(document.querySelector("#task_overview"), options);
            chart.render();
        })();

        (function () {
            var options = {
                chart: {
                    height: 300,
                    type: 'bar',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                colors: ["#100773"],
                dataLabels: {
                    enabled: true,
                    offsetX: -6,
                    style: {
                        fontSize: '12px',
                        colors: ['#fff']
                    }
                },
                stroke: {
                    show: true,
                    width: 1,
                    colors: ['#fff']
                },
                grid: {
                    strokeDashArray: 4,
                },
                series: [{
                    data: {!! json_encode(array_values($home_data['timesheet_logged'])) !!}
                }],
                xaxis: {
                    categories: {!! json_encode(array_keys($home_data['timesheet_logged'])) !!},
                },
            };
            var chart = new ApexCharts(document.querySelector("#timesheet_logged"), options);
            chart.render();
        })();

    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}" class="text-primary">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item text-primary">{{__('Project')}}</li>
@endsection
@section('content')
    <div class="row">

        <div class="col-lg-4 col-md-6">
            <div class="card hover-border-primary">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">

                                <svg width="64px" height="64px" viewBox="-3.12 -3.12 30.24 30.24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M9 7.00013H15V12.0001H9V7.00013ZM10.5 8.50013V10.5001H13.5V8.50013H10.5Z" fill="#100773"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M18 4.00024H6V6.00016H4V7.50016H6V11.0112H4V12.5112H6V16.0223H4V17.5223H6V20.0002H18V4.00024ZM7.5 5.50024V18.5002H16.5V5.50024H7.5Z" fill="#100773"></path> </g></svg>
                                <div class="ms-3">
                                    <small class="text-primary">{{__('Total')}}</small>
                                    <h6 class="m-0">{{__('Proj')}}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $home_data['total_project']['total'] }}</h4>
                            <small class="text-primary"><span class="text-success">{{ $home_data['total_project']['percentage'] }}%</span> {{__('completd')}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card hover-border-primary">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <svg width="64px" height="64px" viewBox="-40.96 -40.96 593.92 593.92" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" stroke="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>tasks-done</title> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Combined-Shape" fill="#100773" transform="translate(70.530593, 46.125620)"> <path d="M185.469407,39.207713 L356.136074,39.207713 L356.136074,81.8743797 L185.469407,81.8743797 L185.469407,39.207713 Z M185.469407,188.541046 L356.136074,188.541046 L356.136074,231.207713 L185.469407,231.207713 L185.469407,188.541046 Z M14.8027404,295.207713 L121.469407,295.207713 L121.469407,401.87438 L14.8027404,401.87438 L14.8027404,295.207713 Z M46.8027404,327.207713 L46.8027404,369.87438 L89.4694071,369.87438 L89.4694071,327.207713 L46.8027404,327.207713 Z M185.469407,337.87438 L356.136074,337.87438 L356.136074,380.541046 L185.469407,380.541046 L185.469407,337.87438 Z M119.285384,-7.10542736e-15 L144.649352,19.5107443 L68.6167605,118.353113 L1.42108547e-14,58.3134476 L21.0721475,34.2309934 L64.0400737,71.8050464 L119.285384,-7.10542736e-15 Z M119.285384,149.333333 L144.649352,168.844078 L68.6167605,267.686446 L1.42108547e-14,207.646781 L21.0721475,183.564327 L64.0400737,221.13838 L119.285384,149.333333 Z"> </path> </g> </g> </g></svg>
                                <div class="ms-3">
                                    <small class="text-primary">{{__('Total')}}</small>
                                    <h6 class="m-0">{{__('Tasks')}}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $home_data['total_task']['total'] }}</h4>
                            <small class="text-primary"><span class="text-success">{{ $home_data['total_task']['percentage'] }}%</span> {{__('completd')}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card hover-border-primary" >
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">

                                <svg width="64px" height="64px" viewBox="-5.76 -5.76 59.52 59.52" id="a" xmlns="http://www.w3.org/2000/svg" fill="#100773" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style>.p{fill:none;stroke:#100773;stroke-linecap:round;stroke-linejoin:round;}</style> </defs> <g id="b"> <path id="c" class="p" d="M16.5169,14.3442l7.7047-4.801,10.2741,8.6883v12.5665l-5.9671,4.836v-11.8175l-12.0117-9.4722Z"></path> <path id="d" class="p" d="M26.0581,9.2578l5.8416-3.6121,10.4601,7.293-6.4328,4.9258"></path> <path id="e" class="p" d="M36.2041,28.6126l6.2959-5.1397"></path> <path id="f" class="p" d="M36.2041,25.9523l6.2959-5.1397"></path> <path id="g" class="p" d="M36.2041,23.292l6.2959-5.1397"></path> <path id="h" class="p" d="M36.2041,20.6317l6.2959-5.1397"></path> <path id="i" class="p" d="M35.3139,14.172l2.7236-2.077-1.865-1.2474-1.4987,1.1314"></path> <path id="j" class="p" d="M5.5,31.9538l13.5429,10.4006,7.4233-5.9106"></path> <path id="k" class="p" d="M5.5,29.2851l13.5429,10.4006,7.4233-5.9106"></path> <path id="l" class="p" d="M5.6039,26.6164l13.5429,10.4006,7.4233-5.9106"></path> <path id="m" class="p" d="M5.5892,23.9478l13.5429,10.4006,7.4233-5.9106"></path> <path id="n" class="p" d="M20.2345,23.7501c-.226,1.0274-1.6933,1.5535-3.2773,1.1753h0c-1.5841-.3783-2.685-1.5178-2.459-2.5451,.226-1.0274,1.6933-1.5535,3.2773-1.1753s2.685,1.5177,2.459,2.5451Z"></path> <path id="o" class="p" d="M15.0514,15.826l-9.2955,5.5946,13.3311,10.1174,7.6392-6.0147"></path> </g> </g></svg>
                                <div class="ms-3">
                                    <small class="text-primary">{{__('Total')}}</small>
                                    <h6 class="m-0">{{__('Expe')}}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $home_data['total_expense']['total'] }}</h4>
                            <small class="text-primary"><span class="text-success">{{ $home_data['total_expense']['percentage'] }}%</span> {{__('expense')}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card hover-border-primary">
                <div class="card-header">

                    <h5>{{__('Project Status')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row ">
                        @foreach($home_data['project_status'] as $status => $val)
                            <div class="col-md-6 col-sm-6 mb-5">
                                <div class="align-items-start">

                                    <div class="ms-2">
                                        <p class="text-primary text-sm mb-0">{{__(\App\Models\Project::$project_status[$status])}}</p>
                                        <h3 class="mb-0 text-{{ \App\Models\Project::$status_color[$status] }}">{{ $val['total'] }}%</h3>
                                        <div class="progress mb-0">
                                            <div class="progress-bar bg-{{ \App\Models\Project::$status_color[$status] }}" style="width: {{$val['percentage']}}%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card hover-border-primary">
                <div class="card-header">
                    <h5>{{__('Tasks Overview')}} <span class="float-end"> <small class="text-primary">{{__('Total Completed task in last 7 days')}}</small></span></h5>

                </div>
                <div class="card-body">
                    <div id="task_overview"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card hover-border-primary">
                <div class="card-header">
                    <h5>{{__('Top Due Projects')}}</h5>
                </div>
                <div class="card-body project_table">
                    <div class="table-responsive ">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th>{{__('Name')}}</th>
                                <th>{{__('End Date')}}</th>
                                <th >{{__('Status')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($home_data['due_project']->count() > 0)
                                @foreach($home_data['due_project'] as $due_project)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{asset(Storage::url('/'.$due_project->project_image ))}}"
                                                     class="wid-40 rounded-circle me-3" >
                                                <div>
                                                    <h6 class="mb-0">{{ $due_project->project_name }}</h6>
                                                    <p class="mb-0"><span class="text-success">{{ \Auth::user()->priceFormat($due_project->budget) }}</p>

                                                </div>
                                            </div>
                                        </td>
                                        <td >{{  Utility::getDateFormated($due_project->end_date) }}</td>
                                        <td class="">
                                            <span class=" status_badge p-2 px-3 rounded badge bg-{{\App\Models\Project::$status_color[$due_project->status]}}">{{ __(\App\Models\Project::$project_status[$due_project->status]) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="py-5">
                                    <td class="text-center mb-0" colspan="3">{{__('No Due Projects Found.')}}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card hover-border-primary">
                <div class="card-header">
                    <h5>{{__('Timesheet Logged Hours')}} <span>  <small class="float-end text-primary flo">{{__('Last 7 days')}}</small></span></h5>
                </div>
                <div class="card-body project_table">
                    <div id="timesheet_logged"></div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card hover-border-primary">
                <div class="card-header">
                    <h5>{{__('Top Due Tasks')}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                            @foreach($home_data['due_tasks'] as $due_task)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <small class="text-primary">{{__('Task')}}:</small>
                                                <h6 class="m-0"><a href="{{ route('projects.tasks.index',$due_task->project->id) }}" class="name mb-0 h6 text-sm">{{ $due_task->name }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-primary">{{__('Project')}}:</small>
                                        <h6 class="m-0 h6 text-sm">{{$due_task->project->project_name}}</h6>
                                    </td>
                                    <td>

                                        <small class="text-primary">{{__('Stage')}}:</small>
                                        <div class="d-flex align-items-center h6 text-sm mt-2">
                                            <span class="full-circle bg-{{ \App\Models\ProjectTask::$priority_color[$due_task->priority] }}"></span>
                                            <span class="ms-1">{{ \App\Models\ProjectTask::$priority[$due_task->priority] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-primary">{{__('Completion')}}:</small>
                                        <h6 class="m-0 h6 text-sm">{{ $due_task->taskProgress()['percentage'] }}</h6>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

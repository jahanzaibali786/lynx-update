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
                            <div class="account-dashboard-svg">
                            <svg fill="#100773" width="64px" height="64px" viewBox="-4 -4 16.00 16.00" xmlns="http://www.w3.org/2000/svg" stroke="#100773" stroke-width="0.00008"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M0 0v7h1v-7h-1zm7 0v7h1v-7h-1zm-5 1v1h2v-1h-2zm1 2v1h2v-1h-2zm1 2v1h2v-1h-2z"></path> </g></svg>
    </div>
                                <div class="ms-3">
                                    <small class="text-primary">{{__('Total')}}</small>
                                    <h6 class="m-0">{{__('Projects')}}</h6>
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
                                <div class="account-dashboard-svg">
<svg fill="#100773" height="64px" width="64px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-271.36 -271.36 1054.72 1054.72" xml:space="preserve" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g transform="translate(1 1)"> <g> <g> <path d="M395.8,309.462V58.733c0-5.12-3.413-8.533-8.533-8.533H293.4v-8.533c0-5.12-3.413-8.533-8.533-8.533h-26.453 C254.147,13.507,237.08-1,216.6-1c-20.48,0-37.547,14.507-41.813,34.133h-26.453c-5.12,0-8.533,3.413-8.533,8.533V50.2H45.933 c-5.12,0-8.533,3.413-8.533,8.533V485.4c0,5.12,3.413,8.533,8.533,8.533h267.849C329.987,504.705,349.393,511,370.2,511 c56.32,0,102.4-46.08,102.4-102.4C472.6,361.111,439.838,320.904,395.8,309.462z M378.733,67.267V306.2c-2.56,0-5.973,0-8.533,0 c-2.873,0-5.718,0.126-8.533,0.361V92.867c0-5.12-3.413-8.533-8.533-8.533H293.4V67.267H378.733z M333.316,313.116 c-34.241,12.834-58.734,43.499-64.298,79.747c-0.04,0.257-0.076,0.516-0.114,0.774c-0.152,1.044-0.292,2.091-0.413,3.144 c-0.088,0.756-0.168,1.515-0.239,2.276c-0.04,0.434-0.082,0.867-0.117,1.302c-0.094,1.164-0.167,2.333-0.221,3.507 c-0.013,0.284-0.022,0.569-0.033,0.854c-0.049,1.288-0.081,2.58-0.081,3.88c0,10.578,1.342,20.487,4.611,30.32 c0.08,0.255,0.165,0.506,0.247,0.76c0.243,0.756,0.492,1.509,0.753,2.258c0.092,0.266,0.187,0.531,0.282,0.796H88.6V101.4h51.2 v8.533c0,5.12,3.413,8.533,8.533,8.533h136.533c5.12,0,8.533-3.413,8.533-8.533V101.4h51.2v208.062 C340.747,310.463,336.982,311.688,333.316,313.116z M156.867,50.2h25.6c5.12,0,8.533-3.413,8.533-8.533 c0-14.507,11.093-25.6,25.6-25.6c14.507,0,25.6,11.093,25.6,25.6c0,5.12,3.413,8.533,8.533,8.533h25.6v8.533v34.133v8.533 H156.867v-8.533V58.733V50.2z M54.467,67.267H139.8v17.067H80.067c-5.12,0-8.533,3.413-8.533,8.533v358.4 c0,5.12,3.413,8.533,8.533,8.533h201.549c3.558,6.115,7.731,11.831,12.431,17.067H54.467V67.267z M370.2,493.933 c-17.987,0-34.71-5.656-48.509-15.255c-0.045-0.035-0.085-0.071-0.131-0.105c-11.323-7.968-20.373-18.413-26.655-30.214 c-0.154-0.471-0.364-0.928-0.651-1.359c-4.836-9.672-7.992-19.904-9.02-30.695c-0.086-0.954-0.166-1.91-0.22-2.872 c-0.022-0.365-0.035-0.733-0.052-1.1c-0.054-1.239-0.095-2.481-0.095-3.733c0-1.39,0.039-2.771,0.106-4.145 c0.018-0.379,0.052-0.755,0.075-1.134c0.063-1.02,0.135-2.037,0.234-3.047c0.035-0.363,0.08-0.723,0.12-1.084 c0.119-1.074,0.251-2.144,0.411-3.206c0.035-0.236,0.073-0.471,0.11-0.706c4.646-29.31,24.349-53.775,50.871-65.154 c5.69-2.348,11.725-4.097,18.047-5.151c0.717-0.143,1.381-0.365,1.998-0.645c4.357-0.693,8.818-1.062,13.362-1.062 c0.759,0,1.51,0.038,2.264,0.058c4.365,0.199,8.73,0.921,13.096,1.649c0.964,0.321,1.929,0.4,2.847,0.283 c38.26,8.402,67.126,42.657,67.126,83.344C455.533,455.533,417.133,493.933,370.2,493.933z"></path> <path d="M131.267,169.667h119.467c4.267,0,8.533-3.413,8.533-8.533s-3.413-8.533-8.533-8.533H131.267 c-5.12,0-8.533,3.413-8.533,8.533S126.147,169.667,131.267,169.667z"></path> <path d="M284.867,169.667h25.6c4.267,0,8.533-3.413,8.533-8.533s-3.413-8.533-8.533-8.533h-25.6c-5.12,0-8.533,3.413-8.533,8.533 S279.747,169.667,284.867,169.667z"></path> <path d="M310.467,186.733h-85.333c-5.12,0-8.533,3.413-8.533,8.533s3.413,8.533,8.533,8.533h85.333 c4.267,0,8.533-3.413,8.533-8.533S315.587,186.733,310.467,186.733z"></path> <path d="M310.467,220.867h-34.133c-5.12,0-8.533,3.413-8.533,8.533s3.413,8.533,8.533,8.533h34.133 c4.267,0,8.533-3.413,8.533-8.533S315.587,220.867,310.467,220.867z"></path> <path d="M131.267,203.8H191c4.267,0,8.533-3.413,8.533-8.533s-3.413-8.533-8.533-8.533h-59.733c-5.12,0-8.533,3.413-8.533,8.533 S126.147,203.8,131.267,203.8z"></path> <path d="M131.267,237.933h25.6c4.267,0,8.533-3.413,8.533-8.533s-3.413-8.533-8.533-8.533h-25.6c-5.12,0-8.533,3.413-8.533,8.533 S126.147,237.933,131.267,237.933z"></path> <path d="M131.267,272.067h68.267c4.267,0,8.533-3.413,8.533-8.533S204.653,255,199.533,255h-68.267 c-5.12,0-8.533,3.413-8.533,8.533S126.147,272.067,131.267,272.067z"></path> <path d="M182.467,229.4c0,5.12,3.413,8.533,8.533,8.533h51.2c4.267,0,8.533-3.413,8.533-8.533s-3.413-8.533-8.533-8.533H191 C185.88,220.867,182.467,224.28,182.467,229.4z"></path> <path d="M310.467,255h-8.533c-5.12,0-8.533,3.413-8.533,8.533s3.413,8.533,8.533,8.533h8.533c4.267,0,8.533-3.413,8.533-8.533 S315.587,255,310.467,255z"></path> <path d="M267.8,272.067c4.267,0,8.533-3.413,8.533-8.533S272.92,255,267.8,255h-34.133c-5.12,0-8.533,3.413-8.533,8.533 s3.413,8.533,8.533,8.533H267.8z"></path> <path d="M139.8,289.133h-8.533c-5.12,0-8.533,3.413-8.533,8.533s3.413,8.533,8.533,8.533h8.533c4.267,0,8.533-3.413,8.533-8.533 S144.92,289.133,139.8,289.133z"></path> <path d="M310.467,289.133H242.2c-5.12,0-8.533,3.413-8.533,8.533s3.413,8.533,8.533,8.533h68.267 c4.267,0,8.533-3.413,8.533-8.533S315.587,289.133,310.467,289.133z"></path> <path d="M208.067,289.133h-34.133c-5.12,0-8.533,3.413-8.533,8.533s3.413,8.533,8.533,8.533h34.133 c4.267,0,8.533-3.413,8.533-8.533S213.187,289.133,208.067,289.133z"></path> <path d="M417.133,366.787c-4.267-2.56-9.387-1.707-11.947,2.56l-44.373,66.56l-17.92-23.893 c-2.56-3.413-8.533-4.267-11.947-1.707c-3.413,2.56-4.267,8.533-1.707,11.947l23.853,31.804c0.544,1.409,1.498,2.67,2.868,3.644 c1.612,1.534,3.655,2.1,5.706,2.1c0.266,0,0.533-0.027,0.8-0.065c0.118-0.016,0.235-0.037,0.353-0.06 c0.179-0.036,0.359-0.08,0.538-0.13c0.095-0.027,0.19-0.048,0.284-0.079c1.049-0.326,2.097-0.849,3.146-1.373 c1.31-0.983,2.239-2.469,2.746-4.12l50.16-75.24C422.253,374.467,421.4,369.347,417.133,366.787z"></path> </g> </g> </g> </g></svg>
    </div>
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
                                <div class="account-dashboard-svg">
                            <svg width="64px" height="64px" viewBox="-24.96 -24.96 97.92 97.92" id="a" xmlns="http://www.w3.org/2000/svg" fill="#100773" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><defs><style>.f{fill:none;stroke:#100773;stroke-linecap:round;stroke-linejoin:round;}</style></defs><rect id="b" class="f" x="6.9608" y="13.065" width="7.5178" height="21.87"></rect><rect id="c" class="f" x="20.7926" y="18.5325" width="7.5178" height="16.4025"></rect><rect id="d" class="f" x="34.994" y="27.4172" width="7.5178" height="7.5178"></rect><path id="e" class="f" d="m4.5,34.9237l14.2938-11.4905,12.4763,4.4976,12.2299-9.4265"></path></g></svg>
    </div>
                            <div class="ms-3">
                                    <small class="text-primary">{{__('Total')}}</small>
                                    <h6 class="m-0">{{__('Expense')}}</h6>
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
                        <table class=" table-hover mb-0">
                            <thead>
                            <tr class="table_heads">
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

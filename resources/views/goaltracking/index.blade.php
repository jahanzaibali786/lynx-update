@extends('layouts.admin')
@section('page-title')
    {{__('Manage Goal Tracking')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Goal Tracking')}}</li>
@endsection
@push('css-page')
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush
@push('script-page')
    <script src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script>
        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();
            $("fieldset[id^='demo'] .stars").click(function () {
                alert($(this).val());
                $(this).attr("checked");
            });
        });

    </script>
@endpush

@section('action-btn')
    <div class="float-end">
    @can('create goal tracking')
       <a href="#" data-size="lg" data-url="{{ route('goaltracking.create') }}" data-ajax-popup="true"  data-bs-title="{{__('Create')}}" data-bs-toggle="{{__('Create New Goal Tracking')}}" class="btn btn-sm btn-primary">
            Create
        </a>
        @endcan
    </div>
@endsection

@section('content')
    @if(\Auth::user()->type == 'company')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['goaltracking.index'], 'method' => 'GET', 'id' => 'goaltracking_submit']) }}
                        <div class="row d-flex justify-content-end ">
        
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                                </div>                               
                            </div>

                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('goaltracking_submit').submit(); return false;"
                                     data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('goaltracking.index') }}" class="btn btn-sm btn-danger" 
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

                    <table class="">
                            <thead>
                            <tr class="table_heads">
                                <th>{{__('Goal Type')}}</th>
                                <th>{{__('Subject')}}</th>
                                <th>{{__('Branch')}}</th>
                                <th>{{__('Target Achievement')}}</th>
                                <th>{{__('Start Date')}}</th>
                                <th>{{__('End Date')}}</th>
                                <th>{{__('Rating')}}</th>
                                <th width="20%">{{__('Progress')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody class="font-style">

                            @foreach ($goalTrackings as $goalTracking)

                                <tr>
                                    <td>{{ !empty($goalTracking->goalType)?$goalTracking->goalType->name:'' }}</td>
                                    <td>{{$goalTracking->subject}}</td>
                                    <td>{{ !empty($goalTracking->branches)?$goalTracking->branches->name:'' }}</td>
                                    <td>{{$goalTracking->target_achievement}}</td>
                                    <td>{{\Auth::user()->dateFormat($goalTracking->start_date)}}</td>
                                    <td>{{\Auth::user()->dateFormat($goalTracking->end_date)}}</td>
                                    <td>
                                        @for($i=1; $i<=5; $i++)
                                            @if($goalTracking->rating < $i)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="text-warning fas fa-star"></i>
                                            @endif
                                        @endfor
                                    </td>
                                    <td>
                                        <div class="progress-wrapper">
                                            <span class="progress-percentage"><small class="font-weight-bold"></small>{{$goalTracking->progress}}%</span>
                                            <div class="progress progress-xs mt-2 w-100">
                                                <div class="progress-bar bg-{{Utility::getProgressColor($goalTracking->progress)}}" role="progressbar" aria-valuenow="{{$goalTracking->progress}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$goalTracking->progress}}%;"></div>
                                            </div>
                                        </div>

                                    </td>
                                    @if( Gate::check('edit goal tracking') ||Gate::check('delete goal tracking'))
                                        <td>
                                            @can('edit goal tracking')
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" data-url="{{ route('goaltracking.edit',$goalTracking->id) }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="{{__('Edit Goal Tracking')}}" class="mx-3 btn btn-sm align-items-center "  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                <i class="ti ti-pencil text-white"></i></a>
                                            </div>
                                                @endcan
                                            @can('delete goal tracking')
                                            <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['goaltracking.destroy', $goalTracking->id],'id'=>'delete-form-'.$goalTracking->id]) !!}
                                                   <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm-yes="document.getElementById('delete-form-{{$goalTracking->id}}').submit();">
                                                   <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                {!! Form::close() !!}
                                            </div>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
@endsection




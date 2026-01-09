@extends('layouts.admin')
@php
   // $profile=asset(Storage::url('uploads/avatar/'));
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
@endphp
@section('page-title')
    @if ($type == 'used')
        {{__('Used Space')}}
    @else
        {{__('Vacant Space')}}
    @endif
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    @if ($type == 'used')
        <li class="breadcrumb-item">{{__('Used Space')}}</li>
    @else
        <li class="breadcrumb-item">{{__('Vacant Space')}}</li>
    @endif
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
            <div class="card-body table-border-style">
                        <div class="table-responsive">
                        <table class="table datatable">
                                <thead>
                                <tr>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Type')}}</th>
                                    <th>{{__('Capacity')}}</th>
                                    @if($type == 'used')
                                    <th>{{__('Company')}}</th>
                                    @endif  
                                    <th>{{__('Meeting')}}</th>
                                    <th>{{__('Window')}}</th>
                                    <th style="max-width:300px">{{__('Description')}}</th>
    
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($spaces as $space)
                                    <tr>
                                    
                                        <td>
                                            {{ (!empty($space->name)) ? $space->name : '-' }}
                                        </td>
                                        <td>
                                            {{ (!empty($space->type)) ? $space->type->name : '-' }}
                                        </td>
                                        <td>
                                            {{ (!empty($space->capacity)) ? $space->capacity : '-' }}
                                        </td>
                                        @if($type == 'used')
                                            @php
                                                $comp= App\Models\Roomassign::with('company')->where('status','assign')->where('space_id',$space->id)->groupBy('company_id')->get();
                                            @endphp
                                            <td>
                                                @if (!empty($comp))
                                                @foreach (@$comp as $comps)
                                                @if (!empty($comps->company))
                                                    {{ $comps->company->name }} , 
                                                @else - @endif
                                                @endforeach
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            {{ (!empty($space->meeting)) ? $space->meeting : '-' }}
                                        </td>
                                        <td>
                                            {{ (!empty($space->window)) ? $space->window : '-' }}
                                        </td>
                                        <td style="max-width:300px !important; overflow-y: auto;">
                                            {{ (!empty($space->description)) ? $space->description : '-' }}
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
    </div>
@endsection

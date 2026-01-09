@extends('layouts.admin')
@section('page-title')
    {{__('Manage Employee Salary')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Employee Salary')}}</li>
@endsection
@section('content')
@if(\Auth::user()->type == 'company')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['setsalary.index'], 'method' => 'GET', 'id' => 'setsalary_submit']) }}
                    <div class="row d-flex justify-content-end ">
    
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                            </div>                               
                        </div>

                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('setsalary_submit').submit(); return false;"
                                 data-bs-title="{{ __('apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('setsalary.index') }}" class="btn btn-sm btn-danger" 
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
                                <th>{{__('Employee Id')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Payroll Type') }}</th>
                                <th>{{__('Salary') }}</th>
                                <th>{{__('Net Salary') }}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td class="Id">
                                        <a href="{{route('setsalary.show',$employee->id)}}" class="btn btn-outline-primary"  data-bs-title="{{__('View')}}">
                                            {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
                                        </a>
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->salary_type() }}</td>
                                    <td>{{  \Auth::user()->priceFormat($employee->salary) }}</td>
                                    <td>{{  !empty($employee->get_net_salary()) ?\Auth::user()->priceFormat($employee->get_net_salary()):'' }}</td>
                                    <td>
                                    <div class="action-btn bg-success ms-2">
                                        <a href="{{route('setsalary.show',$employee->id)}}" class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Set Salary')}}" data-bs-title="{{__('View')}}">
                                            <i class="ti ti-eye text-white"></i>
                                        </a>
                                    </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
@endsection



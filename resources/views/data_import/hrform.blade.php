@extends('layouts.admin')
@section('page-title')
    {{__('Manage Imports')}}
@endsection
@section('content')

<div class="max-w-3xl mx-auto my-8 bg-white rounded-lg shadow p-6">
    <h1 class="text-xl font-bold text-gray-800 mb-6">{{__('Import HR Data via Excel')}}</h1>
    @if(Session::has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded" role="alert">
            <p>{{ Session::get('success') }}</p>
        </div>
    @endif
    <form action="{{ route('data.hrimport') }}" method="POST" enctype="multipart/form-data" style="width:100%;">
        @csrf
        <div class="mb-4 w-full">
            <label for="data_type" class="block text-sm font-medium text-gray-700 mb-1">{{__('Data Type')}}</label>
            <select style="width:100%;" name="data_type" id="data_type" required class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="" selected disabled>{{__('Select Data Type')}}</option>
                <option value="employees">{{__('Employees')}}</option>
                <option value="emp_scales_create">{{__('EmpScale')}}</option>
                <option value="emp_leaves">{{__('Leaves')}}</option>
                <option value="employee_scale">{{__('Employee Scale Attach')}}</option>
                <option value="emp_scale">{{__('Scales Department wise')}}</option>
                <option value="emp_salary">{{__('Monthly Salary')}}</option>
                <option value="emp_security">{{__('Security')}}</option>
            </select>
        </div>

        <div class="mb-6 w-full">
            <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-1">{{__('Excel File')}}</label>
            <input style="width:100%;" type="file" id="excel_file" name="excel_file" accept=".csv" required class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            <p class="mt-1 text-xs text-gray-500">{{__('Accepted formats: .csv')}}</p>
        </div>

        <button style="background-color: #14a3f0 !important; color: #fff;" type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
            {{__('Import Data')}}
        </button>
    </form>
</div>
@endsection

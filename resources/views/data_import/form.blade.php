@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Imports') }}
@endsection
@section('content')
    <div class="max-w-3xl mx-auto my-8 bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-6">{{ __('Import Data via Excel') }}</h1>

        @if (Session::has('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded" role="alert">
                <p>{{ Session::get('success') }}</p>
            </div>
        @endif
        @if (Session::has('counter'))
            <div id="counter-box" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded"
                role="alert">
                <p id="counter-value">{{ Session::get('counter') }}</p>
            </div>
        @else
            <div id="counter-box" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded hidden"
                role="alert">
                <p id="counter-value"></p>
            </div>
        @endif
        <form action="{{ route('data.import') }}" method="POST" enctype="multipart/form-data" style="width:100%;">
            @csrf
            <div class="mb-4 w-full">
                <label for="data_type" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Data Type') }}</label>
                <select style="width:100%;" name="data_type" id="data_type" required
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="" selected disabled>{{ __('Select Data Type') }}</option>
                    <option value="section">{{ __('Sections') }}</option>
                    <option value="class">{{ __('Classes') }}</option>
                    <option value="enrollment_status">{{ __('Enrollmentstatus') }}</option>
                    <option value="registration">{{ __('Registration') }}</option>
                    <option value="registration2">{{ __('Registration 2') }}</option>
                    <option value="enrollment">{{ __('Enrollment') }}</option>
                    <option value="enrollment2">{{ __('Enrollment 2') }}</option>
                    <option value="student_detail">{{ __('Student Detail') }}</option>
                    <option value="student_detail2">{{ __('Student Detail 2') }}</option>
                    <option value="transfer">{{ __('Transfer') }}</option>
                    <option value="withdraw">{{ __('Withdrawal') }}</option>
                    <option value="challan_concession">{{ __('Challan Concession') }}</option>
                    <option value="regular_challan">{{ __('Regular Challan') }}</option>
                    {{-- <option value="securtiy">{{__('Security')}}</option> --}}
                    <option value="receipts">{{ __('Receipts') }}</option>
                    <option value="concession">{{ __('Concession policy') }}</option>
                    <option value="concession_list">{{ __('Concession List') }}</option>
                </select>
            </div>

            <div class="mb-6 w-full">
                <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Excel File') }}</label>
                <input style="width:100%;" type="file" id="excel_file" name="excel_file" accept=".xlsx,.csv,.xls"
                    required
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('Accepted formats: .csv, .xlsx, .xls') }}</p>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                {{ __('Import Data') }}
            </button>
        </form>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelector('form').addEventListener('submit', function(e) {
                    console.log('form submitted');
                    let counter = {{ Session::get('counter', 0) }};
                    let counterBox = document.getElementById('counter-box');
                    let counterValue = document.getElementById('counter-value');
                    counterBox.classList.remove('hidden');
                    counterValue.textContent = counter;
                    let interval = setInterval(function() {
                        counter++;
                        counterValue.textContent = counter;
                    }, 1000);
                });
            });
        </script>
    </div>
@endsection

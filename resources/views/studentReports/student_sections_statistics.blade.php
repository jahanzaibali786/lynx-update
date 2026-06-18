@extends('layouts.admin')
@section('page-title')
    {{ __('Student Statistics') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <!-- Monthly Statistics Changed to Branch Month Wise -->
    <li class="breadcrumb-item">{{ __('Student Statistics') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
    </div>
@endsection
@section('content')
    <style>
        thead tr th:first-child,
        thead tr th:last-child {
            border-radius: 0px !important;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['student_sections_statistics'], 'method' => 'GET', 'id' => 'monthlystatistics']) }}
                        <div class="row d-flex justify-content-end ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <!-- <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                    {{ Form::month('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div> -->
                            <div
                                class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                                <!-- Search Button -->
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('monthlystatistics').submit(); return false;"
                                    data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <!-- Actions Dropdown -->
                                    <div class="dropdown d-inline-block mx-1">
                                            <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                                id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Export
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                                <li>
                                                    <button class="dropdown-item" type="submit" name="export" value="excel">
                                                        <i class="ti ti-file me-2"></i>Excel
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="submit" name="export" value="pdf">
                                                        <i class="ti ti-download me-2"></i>Pdf
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container card maximumHeightNew">
        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School
                </b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Student statistics Report</p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">
                {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
        </div>
        <table class="">

            <thead class="table_heads sticky-headerNew">

                {{-- FIRST ROW --}}
                <tr style="background-color: #100773; color: white;">
                    <th rowspan="2">Sr</th>
                    <th rowspan="2">B.Sr#</th>
                    <th rowspan="2">CLASS</th>

                    <th style="text-align: center;" colspan="{{ $sections->count() }}">
                        SECTIONS
                    </th>

                    <th rowspan="2">TOTAL</th>
                </tr>

                {{-- SECOND ROW (ONLY SECTIONS) --}}
                <tr style="background-color: #100773; color: white;">

                    @foreach ($sections as $section)
                        <th>{{ $section->name }}</th>
                    @endforeach

                </tr>

            </thead>
            @foreach ($report as $branchData)
                {{-- BRANCH NAME --}}
                <tr style="background:#eee; font-weight:bold;">
                    <td colspan="{{ 4 + $sections->count() }}">
                        {{ $branchData['branch'] }}
                    </td>
                </tr>

                {{-- CLASS ROWS --}}
                @foreach ($branchData['rows'] as $row)
                    <tr>
                        <td>{{ $loop->parent->iteration }}</td>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row['class'] }}</td>

                        @foreach ($sections as $section)
                            <td>{{ $row['sections'][$section->id] ?? 0 }}</td>
                        @endforeach

                        <td><b>{{ $row['total'] }}</b></td>
                    </tr>
                @endforeach

                {{-- BRANCH TOTAL --}}
                <tr style="font-weight:bold;">
                    <td colspan="3" style="background:#b4b4b4;">Branch Total</td>

                    @foreach ($sections as $section)
                        <td style="background:#b4b4b4;">
                            {{ $branchData['totals'][$section->id] }}
                        </td>
                    @endforeach

                    <td style="background:#b4b4b4;">
                        {{ $branchData['totals']['total'] }}
                    </td>
                </tr>
            @endforeach

            {{-- GRAND TOTAL --}}
            <tr style="font-weight:bold;">
                <td colspan="3" style="background:#cfcfcf;">GRAND TOTAL</td>

                @foreach ($sections as $section)
                    <td style="background:#cfcfcf;">
                        {{ $grandTotals[$section->id] }}
                    </td>
                @endforeach

                <td style="background:#adadad;">
                    {{ $grandTotals['total'] }}
                </td>
            </tr>
        </table>

    </div>
@endsection

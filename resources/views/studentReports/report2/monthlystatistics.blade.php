@extends('layouts.admin')
@section('page-title')
    {{ __('Session Wise Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Session Wise Report') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['monthlystatistics'], 'method' => 'GET', 'id' => 'monthlystatistics']) }}
                        <div class="row d-flex justify-content-end ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                    {{ Form::month('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('monthlystatistics').submit(); return false;"
                                     title="" title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export"
                                    value="excel"><span class="btn-inner--icon">Pdf / Print</span></button>
                                <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="print"
                                    value="pdf"><span class="btn-inner--icon"><i
                                            class="ti ti-print">Print</i></span></button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container card">
        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School 
                    </b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Monthly statistics Report</p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">
                {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
        </div>
        <table class="datatable">
            @php $currentBranch = null; @endphp
            @foreach ($report as $row)
                @if ($currentBranch != $row['branch'])
                    <tr style="background: gray">
                        <td colspan="8">{{ $row['branch'] }}</td>
                        @php $currentBranch = $row['branch']; @endphp
                    </tr>
                    <tr>
                        <th>CLASS</th>
                        <th>SECTION</th>
                        <th>OPENING</th>
                        <th>NEW ADMISSIONS</th>
                        <th>TRANSFER IN</th>
                        <th>withdrawals</th>
                        <th>TRANSFER OUT</th>
                        <th>CLOSING BALANCE</th>
                    </tr>
                @endif
                <tr>
                    <td>{{ $row['class'] }}</td>
                    <td>{{ $row['section'] }}</td>
                    <td>{{ $row['opening'] }}</td>
                    <td>{{ $row['new_admissions'] }}</td>
                    <td>{{ $row['transfer_in'] }}</td>
                    <td>{{ $row['withdrawals'] }}</td>
                    <td>{{ $row['transfer_out'] }}</td>
                    <td>{{ $row['closing_balance'] }}</td>
                </tr>
            @endforeach
        </table>

    </div>
@endsection

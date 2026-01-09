@extends('layouts.admin')
@section('page-title')
    {{ __('Branch Month Wise Report') }}
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
    <li class="breadcrumb-item">{{ __('Branch Month Wise Report') }}</li>
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
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                    {{ Form::month('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <!-- Search Button -->
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('monthlystatistics').submit(); return false;"
                                     data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <!-- Actions Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" 
                                id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Export
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                            <li>
                                <form method="post" style="display: inline;">
                                    <button class="dropdown-item" type="submit" name="export" value="excel">
                                        <i class="ti ti-file me-2"></i>Excel
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form method="get" style="display: inline;">
                                    @csrf
                                    @method('GET')
                                    <input type="hidden" name="export" value="pdf">
                                    <button class="dropdown-item" type="submit" name="print" value="pdf">
                                        <i class="ti ti-download me-2"></i>Pdf
                                    </button>
                                </form>
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
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Branch Month Wise statistics Report</p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">
                {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
        </div>
        <table class="">
            @php $currentBranch = null;  $sr = 1; $bsr = 1;
            $opening = 0; $new_admissions = 0; $transfer_in = 0; $withdrawals = 0; $transfer_out = 0; $closing_balance = 0;
            $grandTotal = [
                'opening' => 0,
                'new_admissions' => 0,
                'transfer_in' => 0,
                'withdrawals' => 0,
                'transfer_out' => 0,
                'closing_balance' => 0,
            ];  @endphp
            <thead class="table_heads sticky-headerNew">
                <tr class="table_heads" style="background-color: #100773; color: white;">
                    <th>Sr</th>
                    <th>B.Sr#</th>
                    <th>CLASS</th>
                    <th>SECTION</th>
                    <th>OPENING</th>
                    <th>NEW ADMISSIONS</th>
                    <th>TRANSFER IN</th>
                    <th>withdrawals</th>
                    <th>TRANSFER OUT</th>
                    <th>CLOSING BALANCE</th>
                </tr>
                </thead>
            @foreach ($report as $row)
                @if ($currentBranch != $row['branch'])
                @if ($loop->iteration != 1)
                <tr class="branch-total trNew" style="font-weight: bold;">
                    <td colspan="4" style="text-align: center; background-color:#bcbcbc;">Branch Total:</td>
                    <td colspan="1" style="background-color:#bcbcbc;">{{ $opening }}</td>
                    <td colspan="1" style="background-color:#bcbcbc;">{{ $new_admissions }}</td>
                    <td colspan="1" style="background-color:#bcbcbc;">{{ $transfer_in }}</td>
                    <td colspan="1" style="background-color:#bcbcbc;">{{ $withdrawals }}</td>
                    <td colspan="1" style="background-color:#bcbcbc;">{{ $transfer_out }}</td>
                    <td colspan="1" style="background-color:#bcbcbc;">{{ $closing_balance }}</td>
                </tr>
                @endif
                <tr class="branch-header trNew">
                    <td colspan="10" style="font-weight: bold; background-color:#bcbcbc;">{{ $row['branch'] }}</td>
                    @php $currentBranch = $row['branch'];
                    $bsr = 1;
                    $opening = 0; $new_admissions = 0; $transfer_in = 0; $withdrawals = 0; $transfer_out = 0; $closing_balance = 0;
                    @endphp

                </tr>
                @endif
                @php $opening += $row['opening']; $new_admissions += $row['new_admissions']; $transfer_in += $row['transfer_in']; $withdrawals += $row['withdrawals']; $transfer_out += $row['transfer_out']; $closing_balance += $row['closing_balance'];
                $grandTotal['opening'] += $row['opening']; $grandTotal['new_admissions'] += $row['new_admissions']; $grandTotal['transfer_in'] += $row['transfer_in']; $grandTotal['withdrawals'] += $row['withdrawals']; $grandTotal['transfer_out'] += $row['transfer_out']; $grandTotal['closing_balance'] += $row['closing_balance'];
                @endphp
                <tr class="trNew">
                    <td>{{ $sr++ }}</td>
                    <td>{{ $bsr++ }}</td>
                    <td>{{ $row['class'] }}</td>
                    <td>{{ $row['section'] }}</td>
                    <td>{{ $row['opening'] }}</td>
                    <td>{{ $row['new_admissions'] }}</td>
                    <td>{{ $row['transfer_in'] }}</td>
                    <td>{{ $row['withdrawals'] }}</td>
                    <td>{{ $row['transfer_out'] }}</td>
                    <td>{{ $row['closing_balance'] }}</td>
                </tr>
                @if ($loop->last)
                    <tr class="branch-total trNew" style="font-weight: bold;">
                        <td colspan="4" style="text-align: center; background-color:#bcbcbc;">Branch Total:</td>
                        <td style="background-color:#bcbcbc;">{{ $opening }}</td>
                        <td style="background-color:#bcbcbc;">{{ $new_admissions }}</td>
                        <td style="background-color:#bcbcbc;">{{ $transfer_in }}</td>
                        <td style="background-color:#bcbcbc;">{{ $withdrawals }}</td>
                        <td style="background-color:#bcbcbc;">{{ $transfer_out }}</td>
                        <td style="background-color:#bcbcbc;">{{ $closing_balance }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="branch-total trNew" style="font-weight: bold;">
                <td colspan="4" style="text-align: center; background-color:#bcbcbc;">Grand Total:</td>
                <td colspan="1" style="background-color:#bcbcbc;">{{ $grandTotal['opening'] }}</td>
                <td colspan="1" style="background-color:#bcbcbc;">{{ $grandTotal['new_admissions'] }}</td>
                <td colspan="1" style="background-color:#bcbcbc;">{{ $grandTotal['transfer_in'] }}</td>
                <td colspan="1" style="background-color:#bcbcbc;">{{ $grandTotal['withdrawals'] }}</td>
                <td colspan="1" style="background-color:#bcbcbc;">{{ $grandTotal['transfer_out'] }}</td>
                <td colspan="1" style="background-color:#bcbcbc;">{{ $grandTotal['closing_balance'] }}</td>
            </tr>
        </table>

    </div>
@endsection

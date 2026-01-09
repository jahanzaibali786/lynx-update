@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Transfer In Reports') }}
@endsection
@push('script-page')
    <script>
        function generatePDF() {
            var form = document.getElementById('transferin_submit');
            var formData = new FormData(form);
            formData.append('print', 'pdf');
            var queryString = new URLSearchParams(formData).toString();

            window.location.href = "{{ route('transferin.report') }}?" + queryString;
        }

        function exportExcel() {
            var form = document.getElementById('transferin_submit');
            var formData = new FormData(form);
            formData.append('export', 'excel');
            var queryString = new URLSearchParams(formData).toString();
            
            window.location.href = "{{ route('transferin.report') }}?" + queryString;
        }
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Transfer IN Report') }}</li>
@endsection

@section('content')
    {{-- <div class="my-3">
    <div class="row">
        <div class="col-12 d-flex justify-content-end gap-4">
            <button class="btn btn-outline-primary" onclick="generatePDF()">Download PDF</button>
            <button class="btn btn-outline-success" onclick="printPDF()">Print PDF</button>
        </div>
    </div>
</div> --}}

    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['transferin.index'], 'method' => 'GET', 'id' => 'transferin_submit']) }}
                        <div class="row align-items-center justify-content-end ">
                            <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                                <div class="row d-flex justify-content-end ">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('date_from', __('From Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('date_from', isset($_GET['date_from']) ? $_GET['date_from'] : '', ['class' => 'form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('date_to', __('To Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('date_to', isset($_GET['date_to']) ? $_GET['date_to'] : '', ['class' => 'form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select custom-select']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                                            {{ Form::select(
                                                'type',
                                                ['' => 'Select Type', 'inter city' => 'Inter City', 'inter branch' => 'Inter banch'],
                                                isset($_GET['type']) ? $_GET['type'] : '',
                                                ['class' => 'form-control select'],
                                            ) }}

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-2 align-items-center">
                                <!-- Search Button -->
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('transferin_submit').submit(); return false;"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('admissionwithdrawal.index') }}"
                                    class="btn mx-1 btn-sm btn-outline-danger" data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="exportActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="exportActionsDropdown">
                                        <!-- Export Excel Option -->
                                        <li>
                                            <a href="#" class="dropdown-item" onclick="exportExcel(); return false;"
                                                data-bs-title="Export">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </a>
                                        </li>
                                        <!-- Print PDF Option -->
                                        <li>
                                            <a href="#" class="dropdown-item" onclick="generatePDF(); return false;"
                                                data-bs-title="Print">
                                                <i class="ti ti-download me-2"></i>Pdf
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content" id="report-content">
        <div class="card mt-2 p-4">
            <div style="width: 100%; text-align: center;">
                <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b>
                </p>
                <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b></b></p>
                <p style="text-align:center; font-weight:600; font-size:1rem;">
                    {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
            </div>
            {{-- <p style="text-align:center; font-weight:900; font-size:1rem;">Transfer Out Report</p> --}}
            <div class="" style="width: 100%; display: flex; justify-content: space-between;">
                <p><b>Period From: </b>{{ date('d M Y', strtotime($request->input('date_from'))) }}</p>
                <p style="text-align:center; font-weight:900; font-size:1rem;">Transfer IN Report</p>
                <p><b>Period To: </b>{{ date('d M Y', strtotime($request->input('date_to'))) }}</p>
            </div>
            <table class="datatable maximumHeightNew">
                <thead class="sticky-headerNew">
                    <tr class="table_heads">
                        <th colspan="9"></th>
                        <th colspan="2">{{ __('Transfer OUT') }}</th>
                        <th colspan="2">{{ __('Transfer IN') }}</th>
                        <th></th>
                        <th></th>
                    </tr>

                    <tr class="table_heads report_table">
                        <th>{{ __('Sr No.') }}</th>
                        <th>{{ __('B Sr No.') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Transfer Date') }}</th>
                        <th>{{ __('Reg No') }}</th>
                        <th>{{ __('Roll No') }}</th>
                        <th>{{ __('Reg. Catg.') }}</th>
                        <th>{{ __('Student Name') }}</th>
                        <th>{{ __('Father Name') }}</th>
                        <th>{{ __('Branch') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Branch') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Transfer Fee') }}</th>
                        <th>{{ __('Reason') }}</th>
                    </tr>
                </thead>
                <tbody>
                @php 
                    $totalTransferFee = 0; 
                    $previousBranchName = ''; 
                    $branchTransferFee = 0;
                    $branchSr = 1;
                @endphp
                @foreach ($studenttransfer as $transfer)
                    @php
                        $transfer_fee = $transfer->transfer_fee ?? 0;
                        $totalTransferFee += $transfer_fee;
                    @endphp
                    
                    {{-- Check if we need to show branch total for previous branch --}}
                    @if(!$loop->first && $transfer->branchfrom->name != $previousBranchName)
                        {{-- Branch Total Row --}}
                        <tr class="total-row trNew" style="background-color: #B8B8B8;">
                            <td colspan="13" style="background-color: #B8B8B8; font-size: 10px; font-weight: bold; text-align: right; padding-right: 10px;">BRANCH TOTAL</td>
                            <td style="background-color: #B8B8B8; font-size: 10px; text-align: left; font-weight: bold;">{{ number_format($branchTransferFee, 0) }}</td>
                            <td style="background-color: #B8B8B8; font-size: 8px; text-align: center;"></td>
                        </tr>
                        @php 
                            $branchTransferFee = 0;
                            $branchSr = 1;
                        @endphp
                    @endif
                    
                    {{-- Branch Header Row --}}
                    @if ($loop->first || $transfer->branchfrom->name != $previousBranchName)
                        <tr class="trNew">
                            <td colspan="15" style="background-color: #808080; font-size: 10px; font-weight: bold; text-align: left; padding-left: 10px;">{{ $transfer->branchfrom->name }}</td>
                        </tr>
                    @endif
                    
                    {{-- Data Row --}}
                    <tr class="trNew">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $branchSr }}</td>
                        <td>{{ !empty($transfer->transfer_type) ? $transfer->transfer_type : '-' }}</td>
                        <td>{{ !empty($transfer->transfer_date) ? @$transfer->transfer_date : '-' }}</td>
                        <td>{{ !empty($transfer->enrollment) ? @$transfer->enrollment->regId : '-' }}</td>
                        <td>{{ !empty($transfer->enrollment) ? @$transfer->enrollment->enrollId : '-' }}</td>
                        <td>{{ $transfer->student->registeroption->name ?? '' }}</td>
                        <td>{{ !empty($transfer->student) ? @$transfer->student->stdname : '-' }}</td>
                        <td>{{ !empty($transfer->student) ? @$transfer->student->fathername : '-' }}</td>
                        <td>{{ !empty($transfer->branchfrom) ? @$transfer->branchfrom->name : '-' }}</td>
                        <td>{{ !empty($transfer->classfrom) ? @$transfer->classfrom->name : '-' }}</td>
                        <td>{{ !empty($transfer->branchto) ? @$transfer->branchto->name : '-' }}</td>
                        <td>{{ !empty($transfer->classto) ? @$transfer->classto->name : '-' }}</td>
                        <td>{{ number_format($transfer_fee, 0) }}</td>
                        <td>{{ !empty($transfer->status) ? $transfer->status : '-' }}</td>
                    </tr>
                    
                    @php 
                        $previousBranchName = $transfer->branchfrom->name; 
                        $branchTransferFee += $transfer_fee;
                        $branchSr++;
                    @endphp
                @endforeach
                
                {{-- Last Branch Total Row --}}
                @if(!empty($previousBranchName))
                    <tr class="total-row trNew" style="background-color: #B8B8B8;">
                        <td colspan="13" style="background-color: #B8B8B8; font-size: 10px; font-weight: bold; text-align: right; padding-right: 10px;">BRANCH TOTAL</td>
                        <td style="background-color: #B8B8B8; font-size: 10px; text-align: left; font-weight: bold;">{{ number_format($branchTransferFee, 0) }}</td>
                        <td style="background-color: #B8B8B8; font-size: 8px; text-align: center;"></td>
                    </tr>
                @endif
                
                {{-- Grand Total Row --}}
                <tr class="total-row trNew" style="background-color: #808080;">
                    <td colspan="13" style="background-color: #808080; font-size: 10px; font-weight: bold; text-align: right; padding-right: 10px;">GRAND TOTAL</td>
                    <td style="background-color: #808080; font-size: 10px; text-align: left; font-weight: bold;">{{ number_format($totalTransferFee, 0) }}</td>
                    <td style="background-color: #808080; font-size: 8px; text-align: center;"></td>
                </tr>
                </tbody>
            </table>
        </div>

    </div>
@endsection

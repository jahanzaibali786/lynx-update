@extends('layouts.admin')

@section('page-title')
    {{ __('Student Withdraw Listing') }}
@endsection

@push('script-page')
    <script>

        function branchcustomer(id) {
            var customer = $('#customerselect').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {
                    
                    if (result.status == 'success') {
                        var $classSelect = $('#class_select');
                        // Remove previous custom select wrapper and instance
                        if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                            $classSelect[0].customSelectInstance.destroy();
                            delete $classSelect[0].customSelectInstance;
                        }
                        if ($classSelect.next('.custom-select-wrapper').length) {
                            $classSelect.next('.custom-select-wrapper').remove();
                        }
                        $classSelect.removeClass('custom-select');

                        // Clear and append new options
                        $classSelect.empty();
                        $classSelect.append($('<option>', {
                            value: 'all',
                            text: 'All Class'
                        }));
                        for (var j = 0; j < result.class.length; j++) {
                            var cls = result.class[j];
                            $classSelect.append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }

                        // Re-add class and re-init
                        $classSelect.addClass('custom-select');
                        $classSelect.show();
                        // Directly create new CustomSelect instance for this select only
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            window.CustomSelect.create($classSelect[0]);
                        }

                        // Session select update (unchanged)
                        $('#sessionselect').empty();
                        $('#sessionselect').append($('<option>', {
                            value: 'all',
                            text: 'All Session'
                        }));
                        for (var i = 0; i < result.session.length; i++) {
                            var session = result.session[i];
                            $('#sessionselect').append($('<option>', {
                                value: session.id,
                                text: session.title
                            }));
                        }
                    }
                    if (result.status == 'error') {}

                }
            });
        }

        function generatePDF() {
            var form = document.getElementById('admissionwithdrawal_submit');
            var formData = new FormData(form);
            formData.append('export', 'pdf'); // Ensure print=pdf is sent
            var queryString = new URLSearchParams(formData).toString();
            window.location.href = "{{ route('student_withdarawl_listing.report') }}?" + queryString;
        }

        function exportToExcel() {
            var form = document.getElementById('admissionwithdrawal_submit');
            var formData = new FormData(form);
            formData.append('export', 'excel'); // Ensure export=excel is sent
            var queryString = new URLSearchParams(formData).toString();
            window.location.href = "{{ route('student_withdarawl_listing.report') }}?" + queryString;
        }
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Withdraw Listing') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['student_withdarawl_listing'], 'method' => 'GET', 'id' => 'admissionwithdrawal_submit']) }}
                        <div class="row align-items-center justify-content-end ">
                            <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                                <div class="row d-flex justify-content-end ">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('date_from', __('From Withdraw Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('date_from', isset($_GET['date_from']) ? $_GET['date_from'] : \Carbon\Carbon::today()->format('d-m-Y'), ['class' => 'form-control ']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('date_to', __('To Withdraw Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('date_to', isset($_GET['date_to']) ? $_GET['date_to'] : \Carbon\Carbon::today()->format('d-m-Y'), ['class' => 'form-control ']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                            {{ Form::select('class', $class, request()->get('class'), ['class' => 'form-control select custom-select', 'id' => 'class_select']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-2 align-items-center">
                                <!-- Search Button -->
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('admissionwithdrawal_submit').submit(); return false;"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_withdarawl_listing') }}"
                                    class="btn mx-1 btn-sm btn-outline-danger" data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <!-- Actions Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <button class="dropdown-item" type="button"
                                                onclick="exportToExcel(); return false;" data-bs-title="Export"
                                                name="export" value="excel">
                                                <i class="ti ti-file me-2"></i>
                                                <span class="btn-inner--icon">Excel</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="button"
                                                onclick="generatePDF(); return false;" data-bs-title="Print" name="print"
                                                value="pdf">
                                                <i class="ti ti-download me-2"></i>
                                                <span class="btn-inner--icon">PDF</span>
                                            </button>
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
        <div class="card p-4">
            <div style="width: 100%; text-align: center;">
                <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b>
                </p>
                <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> </b></p>
                <p style="text-align:center; font-weight:600; font-size:1rem;">
                    {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
            </div>
            <div class="" style="width: 100%; display: flex; justify-content: space-between;">
                <p><b>Period From: </b>{{ date('d M Y', strtotime($request->input('date_from'))) }}</p>
                <p style="text-align:center; font-weight:900; font-size:1rem;">Student Withdraw Listing</p>
                <p><b>Period To: </b>{{ date('d M Y', strtotime($request->input('date_to'))) }}</p>
            </div>
            <!-- all_data -->
            <table class="datatable maximumHeightNew">
                <thead class="sticky-headerNew">
                <tr class="table_heads report_table">
                    <th>Sr no.</th>
                    <th>Br.Sr#</th>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Father Name</th>
                    <th>Adm Date</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Withdrawal Date</th>
                    <th>Reason of Leaving</th>
                    <th>WO#</th>
                    <th>Security Deposit</th>
                    <th>Net Receivable</th>
                    <th>Net Paid</th>
                    <th>Net Payable</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    @php 
                        $branchSr = 1; 
                        $currentBranch = null;
                        $branchTotals = [
                            'security_deposit' => 0,
                            'receivable' => 0,
                            'paid' => 0,
                            'payable' => 0
                        ];
                        $grandTotals = [
                            'security_deposit' => 0,
                            'receivable' => 0,
                            'paid' => 0,
                            'payable' => 0
                        ];
                    @endphp
                    
                    @foreach ($all_data as $data)
                        @php
                            $dataBranch = $data->student->branches->name ?? $data->branch->name ?? 'Unknown Branch';
                        @endphp
                        
                        @if($currentBranch !== $dataBranch)
                            @if($currentBranch !== null)
                                {{-- Branch total row --}}
                                <tr class="trNew" style="background:#e9e7e7; font-weight:bold;">
                                    <td colspan="12" style="text-align:left; padding-right:10px;">Branch Total</td>
                                    <td style="text-align:left;">{{ number_format($branchTotals['security_deposit'], 2) }}</td>
                                    <td style="text-align:left;">{{ number_format($branchTotals['receivable'], 2) }}</td>
                                    <td style="text-align:left;">{{ number_format($branchTotals['paid'], 2) }}</td>
                                    <td style="text-align:left;">{{ number_format($branchTotals['payable'], 2) }}</td>
                                    <td></td>
                                </tr>
                                @php
                                    $branchTotals = ['security_deposit' => 0, 'receivable' => 0, 'paid' => 0, 'payable' => 0];
                                @endphp
                            @endif
                            
                            {{-- Branch header row --}}
                            <tr class="trNew" style="background:#dcd3d3; font-weight:bold;">
                                <td colspan="17" style="text-align:left; padding-left:10px;">{{ $dataBranch }}</td>
                            </tr>
                            
                            @php 
                                $currentBranch = $dataBranch;
                                $branchSr = 1;
                            @endphp
                        @endif
                        
                        <tr class="trNew">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $branchSr++ }}</td>
                            <td>{{ !empty($data->student) ? $data->student->roll_no : '-' }}</td>
                            <td>{{ !empty($data->student) ? $data->student->stdname : '-' }}</td>
                            <td>{{ !empty($data->student->class) ? $data->student->class->name : '-' }}
                            </td>
                            <td>{{ !empty($data->student) ? $data->student->fathername : '-' }}
                            </td>
                            <td>{{ !empty($data->student->enrollment) ? \Carbon\Carbon::parse($data->student->enrollment->adm_date)->format('d-M-Y') : '-' }}
                            </td>
                            <td>{{ !empty($data->student) ? $data->student->address : '-' }}</td>
                            <td>{!! !empty($data->student) ? str_replace(',', '<br>', $data->student->fatherphone) : '-' !!}
                            </td>
                            <td style="width:50px;">
                                {{ !empty($data) ? $data->withdraw_date : '-' }}
                                </td>
                                <td>{{ !empty($data) ? $data->reason : '-' }}</td>
                                <td>{{ !empty($data) ? $data->wo_no : '-' }}</td>
                                <td>{{ !empty($data) ? number_format($data->security_deposit, 2) : '-' }}</td>
                                <td>{{ !empty($data) ? number_format($data->receivable, 2) : '-' }}</td>
                                <td>{{ !empty($data) ? number_format($data->paid, 2) : '-' }}</td>
                                <td>{{ !empty($data) ? number_format($data->payable, 2) : '-' }}</td>


                            
                            <td></td>
                        </tr>
                        
                        @php
                            $branchTotals['security_deposit'] += $data->security_deposit ?? 0;
                            $branchTotals['receivable'] += $data->receivable ?? 0;
                            $branchTotals['paid'] += $data->paid ?? 0;
                            $branchTotals['payable'] += $data->payable ?? 0;
                            
                            $grandTotals['security_deposit'] += $data->security_deposit ?? 0;
                            $grandTotals['receivable'] += $data->receivable ?? 0;
                            $grandTotals['paid'] += $data->paid ?? 0;
                            $grandTotals['payable'] += $data->payable ?? 0;
                        @endphp
                    @endforeach
                    
                    {{-- Last branch total --}}
                    @if($currentBranch !== null)
                        <tr class="trNew" style="background:#e9e7e7; font-weight:bold;">
                            <td colspan="12" style="text-align:left; padding-right:10px;">Branch Total</td>
                            <td style="text-align:left;">{{ number_format($branchTotals['security_deposit'], 2) }}</td>
                            <td style="text-align:left;">{{ number_format($branchTotals['receivable'], 2) }}</td>
                            <td style="text-align:left;">{{ number_format($branchTotals['paid'], 2) }}</td>
                            <td style="text-align:left;">{{ number_format($branchTotals['payable'], 2) }}</td>
                            <td></td>
                        </tr>
                    @endif
                    
                    {{-- Grand total row --}}
                    <tr class="trNew" style="background:#d4d4d4; font-weight:bold;">
                        <td colspan="12" style="text-align:left; padding-right:10px;">Grand Total</td>
                        <td style="text-align:left;">{{ number_format($grandTotals['security_deposit'], 2) }}</td>
                        <td style="text-align:left;">{{ number_format($grandTotals['receivable'], 2) }}</td>
                        <td style="text-align:left;">{{ number_format($grandTotals['paid'], 2) }}</td>
                        <td style="text-align:left;">{{ number_format($grandTotals['payable'], 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>

            </table>
        </div>
    </div>
@endsection

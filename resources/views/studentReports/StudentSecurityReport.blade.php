@extends('layouts.admin')
@section('page-title')
    {{ __('Student Security Report') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        // function generatePDF() {
        //     console.log('generating');
        //     const element = document.getElementById('registrationcont');
        //     const opt = {
        //         filename: 'student_security_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700, 1000],
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).save();
        // }

        // function printPDF() {
        //     console.log('printing');
        //     const element = document.getElementById('registrationcont');
        //     const opt = {
        //         filename: 'student_security_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700, 1000],
        //             orientation: 'portrait'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).output('bloburl').then(function (pdf) {
        //         window.open(pdf);
        //     });
        // }

        function generatePDF() {
            var form = document.getElementById('student_security_report');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('student_security_report.report') }}?" + queryString,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], {
                        type: 'application/pdf'
                    });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

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
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Security Report') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
    </div>
@endsection

@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => 'student_security_report', 'method' => 'GET', 'id' => 'student_security_report']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_from', __('From'), ['class' => 'form-label']) }}
                        {{ Form::date('date_from', request('date_from'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_to', __('To'), ['class' => 'form-label']) }}
                        {{ Form::date('date_to', request('date_to'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', @$classes, request('class'), ['class' => 'form-control select custom-select', 'id' => 'class_select']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('status', __('Student Status'), ['class' => 'form-label']) }}
                        {{ Form::select('status', ['all' => 'All', 'active' => 'Active', 'withdraw' => 'Withdraw'], request()->get('status', 'all'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <!-- Search Button -->
                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                        onclick="document.getElementById('student_security_report').submit(); return false;"
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
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <div id="registrationcont">
        <div class="card p-4">
            <div class="" style="margin: 0 auto; padding: 30px;">
                <div style="width: 100%; text-align: center;">
                    <p style="font-family: Edwardian Script ITC; font-size: 3.5rem; text-align: center;"><b>The Lynx School</b></p>
                    <h4><b>{{ @$branches[request('branch')] }}</b></h4>
                    <h4><b>Student Security Report</b></h4>
                </div>
            </div>
            <table class="maximumHeightNew">
                <thead class="sticky-headerNew">
                    <tr class="table_heads" style="font-weight:400; font-size:0.8rem;">
                        <th>{{ __('Sr No.') }}</th>
                        <th>{{ __('B Sr No.') }}</th>
                        <th>{{ __('Roll#') }}</th>
                        <th>{{ __('Student Name') }}</th>
                        <th>{{ __('Father Name') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Monthly Fee') }}</th>
                        <th>{{ __('Date of Admission') }}</th>
                        <th>{{ __('Security Deposit') }}</th>
                        <th>{{ __('Admission Challan#') }}</th>
                        <th>{{ __('Withdrawal') }}</th>
                        <th>{{ __('Adjustment') }}</th>
                        <th>{{ __('Refund') }}</th>
                        <th>{{ __('D/O/Payment') }}</th>
                        <th>{{ __('Balance') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        if (!isset($records) || (!is_array($records) && !is_object($records))) {
                            $records = [];
                        }
                        $totalMonthlyFee = 0;
                        $totalDeposit = 0;
                        $totalPaid = 0;
                        $totalAdjustment = 0;
                        $totalRefund = 0;
                        $totalBalance = 0;
                        $branchSr = 1;
                    @endphp
                    @foreach (($records ?? []) as $data)
                        @php
                            $stats = ($journals ?? collect())->get($data->challan_id, ['deposit' => 0, 'paid' => 0]);
                            $securityDeposit = $stats['deposit'];
                            $securityPaid = $stats['paid'];
                            $monthlyFee = floatval(@$data->challan->student->monthly_fee ?? 0);
                            $adjustment = 0; // Add your adjustment logic here
                            $refund = 0; // Add your refund logic here
                            $balance = $securityDeposit - $securityPaid;
                            
                            $totalMonthlyFee += $monthlyFee;
                            $totalDeposit += $securityDeposit;
                            $totalPaid += $securityPaid;
                            $totalAdjustment += $adjustment;
                            $totalRefund += $refund;
                            $totalBalance += $balance;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $branchSr++ }}</td>
                            <td>{{ @$data->challan->student->roll_no ?? '-' }}</td>
                            <td>{{ @$data->challan->student->stdname ?? '-' }}</td>
                            <td>{{ @$data->challan->student->fathername ?? '-' }}</td>
                            <td>{{ @$data->challan->student->class->name ?? '-' }}</td>
                            <td>{{ number_format($monthlyFee, 0) }}</td>
                            <td>{{ @$data->challan->enrollstudent->created_at ? \Carbon\Carbon::parse(@$data->challan->enrollstudent->created_at)->format('d-M-y') : '-' }}</td>
                            <td>{{ number_format($securityDeposit, 0) }}</td>
                            <td>{{ @$data->challan->challanNo ?? '-' }}</td>
                            <td>{{ @$data->challan->student->withdrawal && @$data->challan->student->withdrawal->withdraw_date ? \Carbon\Carbon::parse(@$data->challan->student->withdrawal->withdraw_date)->format('d-M-y') : '-' }}</td>
                            <td>{{ number_format($adjustment, 0) }}</td>
                            <td>{{ number_format($refund, 0) }}</td>
                            <td>{{ @$data->payment_date ? \Carbon\Carbon::parse(@$data->payment_date)->format('d-M-y') : '-' }}</td>
                            <td>{{ number_format($balance, 0) }}</td>
                            <td>{{ @$data->challan->student->withdrawal ? 'Withdrawal' : 'Active' }}</td>
                        </tr>
                    @endforeach
                    {{-- total --}}
                    <tr>
                        <td colspan="6" style="text-align: left; font-weight: bold; background-color: #dcdcdc;">Total</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalMonthlyFee, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalDeposit, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalAdjustment, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalRefund, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalBalance, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                    </tr>
                    {{-- grand total --}}
                    <tr>
                        <td colspan="6" style="text-align: left; font-weight: bold; background-color: #dcdcdc;">Grand Total</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalMonthlyFee, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalDeposit, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalAdjustment, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalRefund, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">{{ number_format($totalBalance, 0) }}</td>
                        <td style="font-weight: bold; background-color: #dcdcdc;">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

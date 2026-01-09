@extends('layouts.admin')

@section('page-title')
    {{ __('Student Single Account') }}
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
                    console.log(result);
                    if (result.status == 'success') {
                        $('#sessionselect').empty();
                        $('#sessionselect').append($('<option>', {
                            value: '',
                            text: 'Select Session'
                        }));
                        for (var i = 0; i < result.session.length; i++) {
                            var session = result.session[i];
                            $('#sessionselect').append($('<option>', {
                                value: session.id,
                                text: session.title
                            }));
                        }
                        $('#class_select').empty();
                        $('#class_select').append($('<option>', {
                            value: '',
                            text: 'Select Class'
                        }));

                        for (var j = 0; j < result.class.length; j++) {
                            var cls = result.class[j];
                            $('#class_select').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }
                    if (result.status == 'error') {}

                }
            });
        }

        function classStudents(classId, status) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('class.withdrawstudents') }}",
                type: "POST",
                data: {
                    class_id: classId,
                    status: status
                },
                dataType: 'json',
                success: function(result) {
                    console.log(result);
                    if (result.status == 'success') {
                        $('#student_select').empty();
                        $('#student_select').append($('<option>', {
                            value: 'all',
                            text: 'All Students'
                        }));
                        for (var id in result.students) {
                            if (result.students.hasOwnProperty(id)) {
                                $('#student_select').append($('<option>', {
                                    value: id,
                                    text: result.students[id]
                                }));
                            }
                        }
                        $('#student_select').val('all');
                    }
                }
            });
        }

        $(document).on('change', '#class_select, #status_select', function() {
            var classId = $('#class_select').val();
            var status = $('#status_select').val();
            if (classId && status) {
                classStudents(classId, status);
            }
        });

        $(document).on('change', '#class_select', function() {
            var classId = $(this).val();
            if (classId) {
                classStudents(classId);
            }
        });
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['student_single_account_details'], 'method' => 'GET', 'id' => 'student_single_account_details']) }}
                        <div class="row d-flex justify-content-start ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('from_date', $from_date, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('to_date', $to_date, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, $selected_branch, ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('status', __('Student Status'), ['class' => 'form-label']) }}
                                    {{ Form::select('status', ['active' => 'Active', 'withdraw' => 'Withdraw'], request()->get('status', 'active'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, $selected_class, ['class' => 'form-control select', 'id' => 'class_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                                    {{ Form::select('student', $student, $selected_student, ['class' => 'form-control select', 'id' => 'student_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('student_single_account_details').submit(); return false;"
                                     data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_single_account_details') }}"
                                    class="btn mx-1 btn-sm btn-outline-danger" 
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span
                                        class="btn-inner--icon">Print</span>
                                </a>
                            </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="challan-list mt-4">
            @foreach ($challans as $challan_date => $challanGroup)
                <div class="card">
                    @foreach ($challanGroup as $challan)
                        <table class="mb-4">
                            <thead>
                                <tr>
                                    <th>Challan No</th>
                                    <th>Challan Date</th>
                                    <th>Student ID</th>
                                    <th>Class ID</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Issued Date</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $challan->challanNo }}</td>
                                    <td>{{ \Carbon\Carbon::parse($challan->challan_date)->format('d-M-Y') }}</td>
                                    <td>{{ $challan->student_id }}</td>
                                    <td>{{ $challan->class_id }}</td>
                                    <td>{{ number_format($challan->total_amount, 2) }}</td>
                                    <td>{{ $challan->status }}</td>
                                    <td>{{ \Carbon\Carbon::parse($challan->issue_date)->format('d-M-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($challan->due_date)->format('d-M-Y') }}</td>
                                </tr>
                                    <tr>
                                    <td colspan="8">
                                        @if ($challan->receipts->isNotEmpty())
                                            @php
                                                $receiptsByDate = $challan->receipts->groupBy(function ($receipt) {
                                                    return \Carbon\Carbon::parse($receipt->recipt_date)->format('d-M-Y');
                                                });
                                            @endphp
                                            @foreach ($receiptsByDate as $receiptDate => $receipts)
                                                @php
                                                    $dayTotal = $receipts->sum('recipt_amount');
                                                @endphp
                                                <ul class="list-group mb-4">
                                                    <li class="list-group-item"><strong>Receipts for {{ $receiptDate }}</strong></li>
                                                    @foreach ($receipts as $receipt)
                                                        <li class="list-group-item">
                                                            <div class="d-flex justify-content-between">
                                                                <strong>Receipt Date:</strong>
                                                                {{ \Carbon\Carbon::parse($receipt->recipt_date)->format('d-M-Y') }}
                                                                <strong>Amount:</strong>
                                                                {{ number_format($receipt->recipt_amount, 2) }}
                                                                <strong>Late Amount:</strong>
                                                                {{ number_format($receipt->late_amount, 2) }}
                                                                <strong>Arrears:</strong>
                                                                {{ number_format($receipt->arrears, 2) }}
                                                                <strong>Receive Type:</strong>
                                                                {{ $receipt->receive_type }}
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endforeach
                                        @else
                                            <p style="text-align: center; font-size: 1rem;">No receipts available for this challan.</p>
                                        @endif
                                    </td>
                                </tr>
                                    <tr>
                                    <td colspan="8">
                                        <ul class="list-group">
                                            <li class="list-group-item"><strong>Voucher Details</strong></li>
                                            @foreach ($challan->vouchers as $voucher)
                                                @php
                                                    $voucherDebit = 0;
                                                    $voucherCredit = 0;
                                                    foreach ($voucher->accounts as $account) {
                                                        $voucherDebit += $account->debit;
                                                        $voucherCredit += $account->credit;
                                                    }
                                                    $voucherBalance = $voucherDebit - $voucherCredit; 
                                                @endphp
    
                                                <li class="list-group-item">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Voucher No:</strong> {{ $voucher->voucher_no }}
                                                        <strong>Debit:</strong> {{ number_format($voucherDebit, 2) }}
                                                        <strong>Credit:</strong> {{ number_format($voucherCredit, 2) }}
                                                        <strong>Balance:</strong> {{ number_format($voucherBalance, 2) }}
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>    
@endsection

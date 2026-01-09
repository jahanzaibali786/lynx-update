@extends('layouts.admin')

@section('page-title')
    {{ __('Student Single Account') }}
@endsection

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function generatePDF() {
            const element = document.getElementById('report-content');
            const opt = {
                filename: 'student_single_report.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: 'a4',
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).save();
        }

        function printPDF() {
            const element = document.getElementById('report-content');
            const opt = {
                filename: 'student_single_report.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: 'a4',
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
                window.open(pdf);
            });
        }
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Single Account') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['student_single_account'], 'method' => 'GET', 'id' => 'student_single_account']) }}
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
                                    {{ Form::select('status', ['active' => 'Active', 'withdraw' => 'Withdraw'], request()->get('status', 'active'), ['class' => 'form-control select', 'id' => 'status_select']) }}
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
                                    {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}<span
                                        style="color: red"> *</span>
                                    {{ Form::select('student', $students, $selected_student, ['class' => 'form-control select', 'id' => 'student_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="validateForm(); return false;" 
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_single_account') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
    <div class="content" id="report-content">
        <div class="card p-4 table-responsive">
            <p style="text-align: center; font-weight: 900; font-size: 1rem;">Student Single Account</p>
            <div class="d-flex justify-content-between">
                <p><b>Period From :</b> {{ @$from_date }}</p>
                <p><b>Period To :</b> {{ @$to_date }}</p>
            </div>
            <div class="d-flex justify-content-between" style="flex-direction:column;">
                <div class="d-flex justify-content-between">
                    <p><b>Student Name: {{ @$std->stdname }}</b></p>
                    <p><b>Class: {{ @$std->class->name }}</b></p>
                    <p><b>Section: {{ @$std->enrollment->section->name }}</b></p>
                    <p><b>Roll No: {{ @$std->enrollment->enrollId }}</b></p>
                </div>
            </div>
            <table style="font-size:0.8rem;">
                <tr class="table_heads report_table">
                    <th>sr#</th>
                    <th>Class</th>
                    <th>Student</th>
                    <th>Billing Month</th>
                    <th>Challan No</th>
                    <th>Challan Type</th>
                    <th>Challan Amount</th>
                    <th>Payment Date</th>
                    <th>Late Amount</th>
                    <th>Receipt</th>
                    <th>Arrears</th>
                    <th>Receipt Mode</th>
                    <th>T.HEAD</th>
                    <th>T.Amount</th>
                    <th>Receipt Ref.</th>
                </tr>
                <tbody>
                    @php
                        $headtotal = 0;
                        $dailyTotal = 0;
                        $currentDate = null;
                    @endphp
                    @foreach (@$receipts as $receipt)
                        @php
                            // Check if we are processing a new date
                            $receiptDate = \Carbon\Carbon::parse(@$receipt->recipt_date)->format('d-M-Y');
                            if ($currentDate !== $receiptDate && $currentDate !== null) {
                                // Display the total for the previous day
                                echo '<tr>
                            <td colspan="13" class="text-right"><strong>Total for ' .
                                    $currentDate .
                                    ':</strong></td>
                            <td><strong>' .
                                    $dailyTotal .
                                    '</strong></td>
                            <td></td>
                        </tr>';
                                // Reset daily total for the new date
                                $dailyTotal = 0;
                            }
                            $currentDate = $receiptDate;
                        @endphp

                        @foreach (@$receipt->voucher as $voucher)
                            <tr>
                                @php
                                    $headtotal += $voucher->credit;
                                    $dailyTotal += $voucher->credit;
                                @endphp
                                <td>{{ @$loop->parent->iteration }}</td>
                                <td>{{ @$receipt->challan->class->name }}</td>
                                <td>{{ @$receipt->challan->student->stdname }}</td>
                                <td>{{ \Carbon\Carbon::parse(@$receipt->challan->fee_month)->format('M-y') }}</td>
                                <td>{{ @$receipt->challan->challanNo }}</td>
                                <td>{{ @$receipt->challan->challan_type }}</td>
                                <td>{{ @$receipt->challan_amount }}</td>
                                <td>{{ \Carbon\Carbon::parse(@$receipt->recipt_date)->format('d-M-Y') }}</td>
                                <td>{{ @$receipt->late_amount }}</td>
                                <td>{{ @$receipt->recipt_amount }}</td>
                                <td>{{ @$receipt->arrears }}</td>
                                <td>{{ @$receipt->receive_type }}</td>
                                <td>{{ @$voucher->heads->fee_head }}</td>
                                <td>{{ @$voucher->credit }}</td>
                                <td>{{ @$receipt->referance }}</td>
                            </tr>
                        @endforeach
                    @endforeach

                    @if ($currentDate !== null)
                        <tr>
                            <td colspan="13" class="text-right"><strong>Total for {{ $currentDate }}:</strong></td>
                            <td><strong>{{ $dailyTotal }}</strong></td>
                            <td></td>
                        </tr>
                    @endif
                    @if($headtotal != 0)
                    <tr style="background-color:grey; color:white;">
                        <td colspan="11" class="text-right"></td>
                        <td colspan="2"><strong>Total Amount</strong></td>
                        <td>{{ $dailyTotal }}</td>
                        <td></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

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

        function validateForm() {
            var studentSelect = document.getElementById('student_select');

            if (studentSelect.value === '' || studentSelect.value === null) {
                alert('Please select a student before applying the filter.');
                return false;
            }
            document.getElementById('student_single_account').submit();
        }
    </script>
@endsection

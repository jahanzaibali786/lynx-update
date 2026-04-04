@extends('layouts.admin')
@section('page-title')
    {{ __('Period Wise CMR Statement') }}
@endsection
@push('script-page')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('breadcrumb')
    <style>
        th,
        td {
            padding: 8px 4px !important;
        }

        .font_less {
            font-size: 11px;
        }
    </style>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Period Wise CMR Statement') }}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['student_receipt.list'], 'method' => 'GET', 'id' => 'student_receipt_list']) }}
                        <div class="row d-flex justify-content-end ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('from_date', isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('to_date', isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('challan_no', __('Challan No #'), ['class' => 'form-label']) }}
                                    {{ Form::text('challan_no', isset($_GET['challan_no']) ? $_GET['challan_no'] : '', ['class' => 'form-control ', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('default_bank', __('Bank'), ['class' => 'form-label']) }}
                                    {{ Form::select('default_bank', $accounts, null, ['class' => 'form-control select', 'id' => 'default_bank', 'required' => 'required']) }}
                                </div>
                            </div>

                        </div>
                        <div class="row d-flex justify-content-end mt-2">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, @$studentDetail->branch ? @$studentDetail->branch->id : '', ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, @$studentDetail->class ? @$studentDetail->class->id : '', ['class' => 'form-control select', 'id' => 'class_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                                    {{ Form::select('student', [], @$studentDetail->StudentRegistration->id ? @$studentDetail->StudentRegistration->id : '', ['class' => 'form-control select', 'id' => 'student_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('student_receipt_list').submit(); return false;"
                                     data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_receipt.list') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                     data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <table class="datatable">
            <thead class="table_heads">
                <tr>
                    <th>Rpt No.</th>
                    <th>Rpt Date</th>
                    <th>Challan No.</th>
                    <th>Rpt Amt</th>
                    <th>Challan Amt</th>
                    <th>Late Amt</th>
                    <th>Arrears</th>
                    <th>Total Fee</th>
                    <th>Rem. Fee</th>
                    <th>Type</th>
                    <th>Bank Account</th>
                    <th>D Status</th>
                    <th>Reference</th>
                    <!-- <th>Action</th> -->
                </tr>
            </thead>
            <tbody>
                @foreach ($recipts as $recipt)
                    <tr style="  border-radius: 10px !important;">
                        <td>
                            <input type="text" value="{{ @$recipt->id }}" disabled
                                style="width:50px; font-size: 11px;">
                        </td>
                        <td>
                            <input type="text" value="{{ date('d/m/Y', strtotime($recipt->recipt_date)) }}" disabled
                                class="font_less" style="width:63px; font-size: 11px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->challan->challanNo }}" disabled style="width:60px; ">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->recipt_amount }}" disabled
                                style="width:60px; font-size: 13px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->challan_amount }}" disabled
                                style="width:65px; font-size: 13px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->late_amount }}" disabled
                                style="width:50px; font-size: 13px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->arrears }}" disabled
                                style="width:50px; font-size: 13px;">
                        </td>
                        <td>
                            <input type="text"
                                value="{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears }}" disabled
                                style="width:60px; font-size: 12px;">
                        </td>
                        <td>
                            <input type="text"
                                value="{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears - @$recipt->recipt_amount }}"
                                disabled style="width:65px; font-size: 12px;">
                        </td>
                        <td>
                            <input type="text" value="RV" disabled style="width:50px; font-size: 13px;">
                        </td>
                        <td>
                            {{ Form::select('default_bank', $accounts, @$recipt->bank_id, ['style' => 'width:100px; font-size: 12px;', 'disabled' => 'disabled']) }}
                        </td>
                        <td>
                            <select class="input">
                                <option value=""></option>
                                <option selected="selected" value="DD">DD</option>
                                <option value="OL">OL</option>
                                <option value="CHQ">CHQ</option>
                                <option value="CD">CD</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->referance }}" style="width:80px; font-size: 11px;">
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
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

        function classStudents(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('class.students') }}",
                type: "POST",
                data: {
                    class_id: id
                },
                dataType: 'json',
                success: function(result) {
                    console.log(result);
                    if (result.status == 'success') {
                        $('#student_select').empty();
                        $('#student_select').append($('<option>', {
                            value: '',
                            text: 'Select Student'
                        }));
                        for (var id in result.students) {
                            if (result.students.hasOwnProperty(id)) {
                                $('#student_select').append($('<option>', {
                                    value: id,
                                    text: result.students[id]
                                }));
                            }
                        }
                        $('#student_select').val('');
                    }
                }
            });
        }
        $(document).on('change', '#class_select', function() {
            var classId = $(this).val();
            if (classId) {
                classStudents(classId);
            }
        });
    </script>
@endsection

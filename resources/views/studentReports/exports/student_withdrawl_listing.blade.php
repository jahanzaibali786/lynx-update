@extends('layouts.admin')

@section('page-title')
    {{ __('Student Withdraw Listing') }}
@endsection

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        // function generatePDF() {
        //     const element = document.getElementById('report-content');
        //     const opt = {
        //         filename: 'student_withdraw_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: 'a4',
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).save();
        // }

        // function printPDF() {
        //     const element = document.getElementById('report-content');
        //     const opt = {
        //         filename: 'student_withdraw_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: 'a4',
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
        //         window.open(pdf);
        //     });
        // }

        function generatePDF() {
        var form = document.getElementById('admissionwithdrawal_submit');
        var formData = new FormData(form);
        var queryString = new URLSearchParams(formData).toString();

        $.ajax({
            url: "{{ route('student_withdarawl_listing.report') }}?" + queryString,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                const base64Pdf = response.base64Pdf;
                const byteCharacters = atob(base64Pdf);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);
                window.open(blobUrl, '_blank');
            },
            error: function (xhr) {
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
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                            {{ Form::select('class', $class, request()->get('class'), ['class' => 'form-control select', 'id' => 'class_select']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('admissionwithdrawal_submit').submit(); return false;"
                                     data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_withdarawl_listing') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                     data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                {{-- <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span
                                        class="btn-inner--icon">Print</span>
                                </a> --}}
                                <a href="#" onclick="generatePDF(); return false;"
                                    class="btn mx-1 btn-sm btn-outline-success"  data-bs-title="Print">
                                    <span class="btn-inner--icon">Print
                                    </span>
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
            <table class="datatable">
                <thead>
                <tr class="table_heads report_table">
                    <th>Sr no.</th>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Adm Date</th>
                    <th>Father Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Withdrawal Date</th>
                    <th>Net Payable / Receivable</th>
                    <th>Reason for Leaving</th>
                </tr>
                </thead>
                <tbody>
                    @foreach ($all_data as $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ !empty($data->student) ? $data->student->roll_no : '-' }}</td>
                            <td>{{ !empty($data->student) ? $data->student->stdname : '-' }}</td>
                            <td>{{ !empty($data->student->class) ? $data->student->class->name : '-' }}
                            </td>
                            <td>{{ !empty($data->student->enrollment) ? \Carbon\Carbon::parse($data->student->enrollment->adm_date)->format('d-M-Y') : '-' }}
                            </td>
                            <td>{{ !empty($data->student) ? $data->student->fathername : '-' }}
                            </td>
                            <td>{{ !empty($data->student) ? $data->student->address : '-' }}</td>
                            <td>{!! !empty($data->student) ? str_replace(',', '<br>', $data->student->fatherphone) : '-' !!}
                            </td>
                            <td style="width:50px;">
                                {{ !empty($data) ? $data->withdraw_date : '-' }}
                            <td>
                                @php
                                    $arrears = 0;
                                    $total = 0;
                                    $challans = \App\Models\Challans::select(
                                        \DB::raw('(total_amount - (paid_amount + concession_amount)) as total'),
                                    )
                                        ->where('student_id', @$data->student_id)
                                        ->where('status', '!=', 'Paid')
                                        ->get();
                                    foreach ($challans as $challan) {
                                        $arrears += $challan->total;
                                    }
                                @endphp
                                {{ $arrears }}

                            </td>
                            <td>{{ !empty($data) ? $data->reason : '-' }}</td>
                        </tr>
                    @endforeach

                </tbody>

            </table>
        </div>
    </div>
@endsection

@extends('layouts.admin')
@section('page-title')
    {{ __('Student Fee Receipt Detail') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>

    <script>
        // function generatePDF() {
        //     console.log('generating');
        //     const element = document.getElementById('studentfeereceipt');
        //     const opt = {
        //         filename: 'studentfeereceipt.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700, 950],
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).save();
        // }

        // function printPDF() {
        //     console.log('printing');
        //     const element = document.getElementById('studentfeereceipt');
        //     const opt = {
        //         filename: 'studentfeereceipt.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700, 950],
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
        //         window.open(pdf);
        //     });
        // }

        function generatePDF() {
            var form = document.getElementById('student_receipt_list');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('student_fee_receipt_detail.report') }}?" + queryString,
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

        function exportToExcel() {
            var form = document.getElementById('student_receipt_list');
            var formData = new FormData(form);
            formData.append('export', 'excel');
            var queryString = new URLSearchParams(formData).toString();

            window.location.href = "{{ route('student_fee_receipt_detail.report') }}?" + queryString;
        }

        function exportToPDF() {
            var form = document.getElementById('student_receipt_list');
            var formData = new FormData(form);
            formData.append('export', 'pdf');
            var queryString = new URLSearchParams(formData).toString();

            window.location.href = "{{ route('student_fee_receipt_detail.report') }}?" + queryString;
        }

        function exportToLibreOfficePDF() {
            var form = document.getElementById('student_receipt_list');
            var formData = new FormData(form);
            formData.append('export', 'libreoffice_pdf');
            var queryString = new URLSearchParams(formData).toString();

            window.location.href = "{{ route('student_fee_receipt_detail.report') }}?" + queryString;
        }
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Fee Receipt Detail') }}</li>
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
                        {{ Form::open(['route' => ['student_fee_receipt_detail'], 'method' => 'GET', 'id' => 'student_receipt_list']) }}
                        <div class="row d-flex justify-content-end " style="width: 100%">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('from_date', request()->get('from_date') ?? date('Y-m-d'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('to_date', request()->get('to_date') ?? date('Y-m-d'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('default_bank', __('Bank'), ['class' => 'form-label']) }}
                                    {{ Form::select('default_bank', $accounts, request()->get('default_bank'), ['class' => 'form-control select', 'id' => 'default_bank']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('head', __('T.Heads'), ['class' => 'form-label']) }}
                                    {{ Form::select('head', $heads, request()->get('head'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex justify-content-end mt-2">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('vouchers', __('Voucher'), ['class' => 'form-label']) }}
                                    {{ Form::select('voucher', $vouchers, request()->get('voucher'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, request()->get('class'), ['class' => 'form-control select', 'id' => 'class_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('student', __('Student'), ['class' => 'form-label']) }}
                                    {{ Form::select('student', $students, request()->get('student'), ['class' => 'form-control select', 'id' => 'student_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('student_receipt_list').submit(); return false;"
                                    title="search" data-bs-original-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                {{-- <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span class="btn-inner--icon">Print</span>
                            </a> --}}
                                <a href="#" onclick="generatePDF(); return false;"
                                    class="btn mx-1 btn-sm btn-outline-success" title="print"
                                    data-bs-original-title="Print">
                                    <span class="btn-inner--icon">Print
                                    </span>
                                </a>
                                {{-- Excel Export button --}}
                                <a href="#" onclick="exportToExcel(); return false;"
                                    class="btn mx-1 btn-sm btn-outline-success" title="Export to Excel"
                                    data-bs-original-title="Export to Excel">
                                    {{-- <i class="fas fa-file-excel"></i> --}} Export 
                                </a>
                                {{-- LibreOffice PDF Export button --}}
                                {{-- <a href="#" onclick="exportToLibreOfficePDF(); return false;"
                                    class="btn mx-1 btn-sm btn-outline-warning" title="Export Excel as PDF"
                                    data-bs-original-title="Export Excel as PDF">
                                    <i class="fas fa-file-pdf"></i> Excel PDF
                                </a> --}}
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-2 p-4" id="studentfeereceipt">
        <div class="mt-4" style="margin: 0 auto; padding: 30px;">
            <div style="width: 100%; text-align: center;">
                <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School </b></p>
            </div>
            <div style="width: 100%; text-align: center;">
                <p style="font-size:1rem; text-align: center; font-weight: 800;">Fee Receipt Detail</p>
            </div>
            <div style="width: 100%; text-align: center;">
                <p style="font-size:1rem; text-align: center; font-weight: 800;">
                    {{ @$brnches_name->name ?? 'All Branches' }}</p>
            </div>
            <div class="" style="width:100%">
                <p style="width: 34%; float:left;"><b>From Date: </b>{{ request()->get('from_date') ?? date('Y-M-d') }}</p>
                <p style="width: 34%; float:left;"></p>
                <p style="width: 30%; float:left; padding-left:100px;"><b>To Date:
                    </b>{{ request()->get('to_date') ?? date('Y-M-d') }}</p>
            </div>
            <div class="" style="width: 100%;">
                <table class="datatable">
                    <thead>
                        <tr class="table_heads" style="font-size:0.8rem;">
                            <th>{{ __('Sr No.') }}</th>
                            <th>{{ __('BSr No.') }}</th>
                            <th>{{ __('Rpt. Date') }}</th>
                            <th>{{ __('Ch Type') }}</th>
                            <th>{{ __('Roll No') }}</th>
                            <th>{{ __('Student') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Challan No') }}</th>
                            <th>{{ __('Billing Period') }}</th>
                            <th>{{ __('Bank') }}</th>
                            <th>{{ __('T.Head') }}</th>
                            <th>{{ __('Ref.') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Over Receipt.') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($recipts as $receipt)
                            @foreach ($receipt->voucher as $voucher)
                                {{-- @dd($recipts,$receipt->challan->challan_type) --}}
                                <tr style="font-size:0.7rem;">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $receipt->recipt_date)->format('d-M-Y') }}
                                    </td>
                                    <td>{{ $receipt->challan?->challan_type }}</td>
                                    <td>{{ @$receipt->challan?->enrollstudent->id }}</td>
                                    <td>{{ @$receipt->challan?->student->stdname }}</td>
                                    <td>{{ @$receipt->challan?->class->name }}</td>
                                    <td>{{ @$receipt->challan?->challanNo }}</td>
                                    <td>
                                        {{ $receipt->challan?->fee_month ? \Carbon\Carbon::parse($receipt->challan->fee_month)->format('F Y') : '' }}
                                    </td>
                                    <td>{{ $receipt->bank?->bank_name }}</td>
                                    <td>
                                        {{ @$voucher->heads?->fee_head ? $voucher->heads?->fee_head : '' }}
                                    </td>
                                    <td>{{ $receipt->referance }}</td>
                                    <td>
                                        {{ $voucher->credit }}
                                    </td>
                                    <td>
                                        0.0
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
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

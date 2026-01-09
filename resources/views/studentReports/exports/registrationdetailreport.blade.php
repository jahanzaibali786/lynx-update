@extends('layouts.admin')
@section('page-title')
    {{ __('Student Registration Detail Rpt.') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    {{-- <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script> --}}
    <script>
        $(document).on('change', '#branch', function() {
            var branch = $(this).val();
            $.ajax({
                url: '{{ route('branch.class') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#class_select').empty();
                    $('#class_select').append(
                        '<option value="all" selected >{{ __('All Class') }}</option>');
                    for (let index = 0; index < data.length; index++) {
                        $('#class_select').append('<option value="' + data[index]['id'] + '">' + data[
                            index]['name'] + '</option>');
                    }
                    var s = `{{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}
                        <select id="class_students" name="student_id" class="form-control select" required="required">
                            <option value="" selected disabled>{{ __('Select Student') }}</option> </select>`;
                    $('.std_data').empty().html(s);
                }
            });
        });
    </script>
    <script>
        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var style = `
                <style>
                    table {
                        border-collapse: collapse;
                        width: 100%;
                        border: 1px solid gray !important;
                    }
                    th, td {
                        border: 1px solid gray !important;
                        padding: 5px !important;
                    }
                    th {
                        background-color: #f2f2f2 !important;
                    }
                         .serc{
                            display: none;
                        }
                            #hed{
                           float: right !important;
                           padding-bottom: 30px;
                        }
                </style>
            `;

            // Create a temporary wrapper div
            var wrapper = document.createElement('div');
            wrapper.innerHTML = style + element.innerHTML;
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(wrapper).save();
        }

        function printDiv() {
            var element = document.getElementById('printableArea');

            var style = `
                <style>
                    table {
                        border-collapse: collapse;
                        width: 100%;
                        border: 1px solid gray !important;
                    }
                    th, td {
                        border: 1px solid gray !important;
                        padding: 5px !important;
                    }
                    th {
                        background-color: #f2f2f2 !important;
                    }
                        .serc{
                            display: none;
                        }
                        #hed{
                            justify-content: end;
                            float: right !important;
                            padding-bottom: 30px;
                        }
                </style>
            `;

            // Create a temporary wrapper div
            var wrapper = document.createElement('div');
            wrapper.innerHTML = style + element.innerHTML;

            var opt = {
                margin: 0.3,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };

            html2pdf().set(opt).from(wrapper).outputPdf('bloburl').then(function(pdfUrl) {
                window.open(pdfUrl, '_blank');
            });
        }

        function submitWithPrintValue() {
            const form = document.getElementById('registrationdetailreport');

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'print';
            input.value = 'pdf';

            form.appendChild(input);
            form.target = '_blank';
            form.submit();
            form.removeChild(input);
            form.target = '';
        }
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Registration Detail Rpt.') }}</li>
@endsection
@section('action-btn')
    <style>
        .fake-header {
            margin-bottom: -10px;
            background-color: var(--primary-darker) !important;
            color: #fff !important;
            font-weight: bolder;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        .fake-header td {
            background-color: #100773 !important;
        }
    </style>
    <div class="float-end">
    </div>
@endsection
@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => 'registrationdetailreport', 'method' => 'GET', 'id' => 'registrationdetailreport']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select', 'id' => 'branch']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', $classes, request('class'), ['class' => 'form-control select', 'id' => 'class_select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('register', __('Registration Option'), ['class' => 'form-label']) }}
                        {{ Form::select('register', $registerOption, request('register'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                        {{ Form::select('status', $status, request('status'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_from', __('From'), ['class' => 'form-label  mt-1']) }}
                        {{ Form::date('date_from', isset($_GET['date_from']) ? $_GET['date_from'] : @$request->date_from, ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_to', __('To'), ['class' => 'form-label mt-1']) }}
                        {{ Form::date('date_to', isset($_GET['date_to']) ? $_GET['date_to'] : @$request->date_to, ['class' => 'form-control']) }}
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex">
                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary mt-2"
                        onclick="document.getElementById('registrationdetailreport').submit(); return false;"
                        data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                    <button class="btn mx-1 btn-sm btn-outline-success mt-2" type="submit" name="print" value="pdf"
                        data-bs-title="Print"><span class="btn-inner--icon">Print</span></button>
                    <button class="btn mx-1 btn-sm btn-outline-success mt-2" type="submit" name="export" value="excel"
                        data-bs-title="Export"><span class="btn-inner--icon">Export</span></button>
                    <button class="btn mx-1 btn-sm btn-outline-success mt-2" type="submit" name="export" value="pdf"
                        data-bs-title="Export"><span class="btn-inner--icon">PDF</span></button>
                    {{-- <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export" value="excel"  title="Export" ><span class="btn-inner--icon">Pdf / Print</span></button> --}}
                    {{-- <a href="#" class="btn mx-1 btn-sm btn-outline-success mt-2"
    
                                        onclick="generatePDF(); return false;"
                                         data-bs-title="{{ __('Print Report') }}">
                                            <span class="btn-inner--icon">Print</span>
                                        </a> --}}

                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
    </div>
    {{-- @dd('sadas') --}}


    <div id="printableArea">
        <div class="card mt-2 p-2">
            <div class="mt-1"
                style="margin: 0 auto; padding: 10px; width:100%; display:flex; justify-content: center; align-items: center; flex-direction: column;">
                <div style="width: 100%; text-align: center;">
                    <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx
                            School</b></p>
                </div>
                <div style="width: 100%; text-align: center;">
                    <p style="font-size:1rem; text-align: center; font-weight: 800;">
                        {{ request()->get('status') == 'Registered' ? 'Not Admitted' : '' }} Registration Report
                    </p>
                </div>
                <div class="" style="width: 100%; display: flex; justify-content: space-between;">
                    <p><b>Period From:
                        </b>{{ date('d M Y', strtotime(isset($_GET['date_from']) ? $_GET['date_from'] : @$request->date_from)) }}
                    </p>
                    <p><b>Branch: </b>{{ @$branches[request('branch')] ?? 'All Branches' }}</p>
                    <p><b>Period To: </b>{{ date('d M Y', strtotime($request->input('date_to'))) }}</p>
                </div>

                <div class="table-responsive " style="width: 100%;">
                    <table class="datatable">

                        <thead class="table_heads">
                            <tr>
                                <th>{{ __('Sr No.') }}</th>
                                <th>{{ __('B Sr No.') }}</th>
                                <th>{{ __('Reg. #') }}</th>
                                <th>{{ __('Reg. Date') }}</th>
                                <th>{{ __('Student Name') }}</th>
                                <th>{{ __('Father Name') }}</th>
                                <th>{{ __('Class') }}</th>
                                <th>{{ __('Phone No') }}</th>
                                <th>{{ __('Registration Status') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Adm. Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $globalIndex = 1; @endphp
                            @foreach ($studentData as $branchId => $students)
                                {{-- Branch name row --}}
                                <tr class="branch-header" style="background-color:#bcbcbc;">
                                    <td colspan="11" style="font-weight: bold;">
                                        {{ @$branches[$branchId] ?? 'Branch Not Specified' }}
                                    </td>
                                </tr>
                                @if ($loop->iteration != 1)
                                    <tr class="fake-header" style="font-weight:600; font-size:0.9rem;">
                                        <td>{{ __('Sr No.') }}</td>
                                        <td>{{ __('B Sr No.') }}</td>
                                        <td>{{ __('Reg. #') }}</td>
                                        <td>{{ __('Reg. Date') }}</td>
                                        <td>{{ __('Student Name') }}</td>
                                        <td>{{ __('Father Name') }}</td>
                                        <td>{{ __('Class') }}</td>
                                        <td>{{ __('Phone No') }}</td>
                                        <td>{{ __('Registration Status') }}</td>
                                        <td>{{ __('Amount') }}</td>
                                        <td>{{ __('Adm. Status') }}</td>
                                    </tr>
                                @endif
                                @foreach ($students as $index => $student)
                                    <tr>
                                        <td>{{ $globalIndex }}</td>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->id }}</td>
                                        <td>{{ date('d M Y', strtotime($student->regdate)) }}</td>
                                        <td>{{ @$student->stdname }}</td>
                                        <td>{{ @$student->fathername }}</td>
                                        <td>{{ @$student->class->name }}</td>
                                        <td>{{ @$student->fatherphone }}</td>
                                        <td>{{ @$student->registeroption->name }}</td>
                                        <td>{{ @$challans[$student->id]->paid_amount }}</td>
                                        <td>{{ @$student->student_status == 'Enrolled' ? 'Yes' : 'No' }}</td>
                                    </tr>
                                    @php $globalIndex++; @endphp
                                @endforeach

                                <tr class="branch-total" style="font-weight: bold;">
                                    <td colspan="9" style="text-align: right;">Branch Total:</td>
                                    <td colspan="2">{{ $branchTotals[$branchId] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #555; color: white;">
                                <td colspan="9" style="text-align: right; font-weight: bold;">Grand Total:</td>
                                <td colspan="2" style="font-weight: bold;">{{ $grandTotal }}</td>
                            </tr>
                        </tfoot>

                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

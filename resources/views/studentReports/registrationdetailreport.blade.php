@extends('layouts.admin')
@section('page-title')
    {{ __('Student Registration Detail Rpt.') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        $(document).on('change', '#class_select', function() {
            var class_id = $(this).val();

            $.ajax({
                url: '{{ route('class.student_head') }}',
                type: 'POST',
                data: {
                    "class_id": class_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var s = `{{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}
                            <select id="class_students" name="student_id" class="form-control select" required="required">
                                <option value="" selected disabled>{{ __('Select Student') }}</option>`;

                    for (let index = 0; index < data.student.length; index++) {
                        s +=
                        `<option value="${ data.student[index]['roll_no']}">${ data.student[index]['roll_no']} - ${data.student[index]['stdname']} s/d/o ${data.student[index]['fathername']} </option>`;
                    }
                    s += `</select>`;
                    $('.std_data').empty().html(s);
                    if (data.length != 0) {
                        $('#class_students').addClass('js-searchBox');
                        JsSearchBox();
                        updateWidths();
                    }
                }
            });
        });

        $(document).on('change', '#branch', function() {
            let branch = $(this).val();
            $.ajax({
                url: "{{ route('branch.class') }}",
                type: "POST",
                data: {
                    branch_id: branch,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(result) {
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
                    for (var j = 0; j < result.length; j++) {
                        var cls = result[j];
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

                    $('#student_select').html('<option value="">Select Student</option>');
                }
            });
        });
    </script>
    <!-- <script>
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
    </script> -->
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
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select custom-select', 'id' => 'branch']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', $classes, request('class'), ['class' => 'form-control select custom-select', 'id' => 'class_select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('register', __('Registration Option'), ['class' => 'form-label']) }}
                        {{ Form::select('register', $registerOption, request('register'), ['class' => 'form-control select custom-select']) }}
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
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <!-- Search Button -->
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('registrationdetailreport').submit(); return false;"
                        data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                
                    <!-- Actions Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" 
                                id="reportActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Export
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="reportActionsDropdown">
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
        </div>
        {{ Form::close() }}
    </div>
    </div>

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

                <div class="table-responsive maximumHeightNew mt-2" style="width: 100%;">
                    <table class="datatable">

                        <thead class="table_heads sticky-headerNew">
                            <tr class="table_heads">
                                <th>{{ __('Sr No.') }}</th>
                                <th>{{ __('B Sr No.') }}</th>
                                <th>{{ __('Reg. #') }}</th>
                                <th>{{ __('Reg. Date') }}</th>
                                <th>{{ __('Student Name') }}</th>
                                <th>{{ __('Father Name') }}</th>
                                <th>{{ __('Session') }}</th>
                                <th>{{ __('Class') }}</th>
                                <th>{{ __('DOB') }}</th>
                                <th>{{ __('Gender') }}</th>
                                <th>{{ __('Phone No') }}</th>
                                <th>{{ __('Registration Status') }}</th>
                                {{-- <th>{{ __('Amount') }}</th> --}}
                                {{-- <th>{{ __('Adm. Status') }}</th> --}}
                                <th>{{ __('Registration Option') }}</th>
                                <th>{{ __('Reg. Fee') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $globalIndex = 1; @endphp
                            @foreach ($studentData as $branchId => $students)
                                {{-- Branch name row --}}
                                <tr class="branch-header trNew" style="background-color:#bcbcbc;">
                                    <td colspan="14" style="font-weight: bold;">
                                        {{ @$branches[$branchId] ?? 'Branch Not Specified' }}
                                    </td>
                                </tr>
                             <!--   @if ($loop->iteration != 1)
                                    <tr class="fake-header trNew" style="font-weight:600; font-size:0.9rem;">
                                        <td>{{ __('Sr No.') }}</td>
                                        <td>{{ __('B Sr No.') }}</td>
                                        <td>{{ __('Reg. #') }}</td>
                                        <td>{{ __('Reg. Date') }}</td>
                                        <td>{{ __('Student Name') }}</td>
                                        <td>{{ __('Father Name') }}</td>
                                        <td>{{ __('Session') }}</td>
                                        <td>{{ __('Class') }}</td>
                                        <td>{{ __('DOB') }}</td>
                                        <td>{{ __('Gender') }}</td>
                                        <td>{{ __('Phone No') }}</td>
                                        <td>{{ __('Registration Status') }}</td>
                                        {{-- <td>{{ __('Amount') }}</td> --}}
                                        {{-- <td>{{ __('Adm. Status') }}</td> --}}
                                        <td>{{ __('Registration Option') }}</td>
                                        <th>{{ __('Reg. Fee') }}</th>
                                    </tr>
                                @endif -->
                                @foreach ($students as $index => $student)
                                    <tr class="trNew">
                                        <td>{{ $globalIndex }}</td>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->reg_no }}</td>
                                        <td>{{ date('d M Y', strtotime($student->regdate)) }}</td>
                                        <td>{{ @$student->stdname }}</td>
                                        <td>{{ @$student->fathername }}</td>
                                        <td>{{ @$student->session->year }}</td>
                                        <td>{{ @$student->class->name }}</td>
                                        <td>{{ @$student->dob }}</td>
                                        <td>{{ strtoupper(@$student->gender) }}</td>
                                        <td>{{ @$student->fatherphone }}</td>
                                        {{-- <td>{{ @$challans[$student->id]->paid_amount }}</td> --}}
                                        <td>
                                        @if($student->roll_no != null)
                            {{ 'Enrolled' }}
                        @else
                            {{ 'Not Enrolled' }}
                        @endif
                                        </td>
                                        <td>{{ @$student->registeroption->name }}</td>
                                        @php
                                            $student_registrations = \App\Models\StudentRegistration::where('id', $student->id)->first();
                                        @endphp
                                        <td>{{ @$student_registrations->registrationfee ?? '0' }}</td>
                                    </tr>
                                    @php $globalIndex++; @endphp
                                @endforeach

                                <tr class="branch-total trNew" style="font-weight: bold;">
                                    <td colspan="13" style="text-align: right;">Branch Total:</td>
                                    <td colspan="1">{{ $branchTotals[$branchId] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="trNew" style="background-color: #555; color: white;">
                                <td colspan="13" style="text-align: right; font-weight: bold;">Grand Total:</td>
                                <td colspan="1" style="font-weight: bold;">{{ $grandTotal }}</td>
                            </tr>
                        </tfoot>

                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

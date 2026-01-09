@extends('layouts.admin')
@section('page-title')
    {{ __('Admission Listing.') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>

    <script>
        // function generatePDF() {
        //     console.log('generating');
        //     const element = document.getElementById('registrationcont');
        //     const opt = {
        //         filename: 'admission_report.pdf',
        //         html2canvas: { scale: 1 },
        //         jsPDF: { unit: 'pt', format: [700, 1000], orientation: 'landscape' }
        //     };
        //     html2pdf().from(element).set(opt).save();
        // }

        // function printPDF() {
        //     console.log('printing');
        //     const element = document.getElementById('registrationcont');
        //     const opt = {
        //         filename: 'admission_report.pdf',
        //         html2canvas: { scale: 1 },
        //         jsPDF: { unit: 'pt', format: [700, 1000], orientation: 'portrait' }
        //     };
        //     html2pdf().from(element).set(opt).output('bloburl').then(function (pdf) {
        //         window.open(pdf);
        //     });
        // }
        function generatePDF() {
            var form = document.getElementById('admissionlisting');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('admissionlisting.report') }}?" + queryString,
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

        $(document).on('change', '#class_select', function() {
            var classId = $(this).val();
            if (classId) {
                classStudents(classId);
            }
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Admission Listing') }}</li>
@endsection

@section('action-btn')
    <style>
        .branch-totla-row td {
            background: #48494b4f !important;
        }
    </style>
    <div class="float-end">
    </div>
@endsection

@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => 'admissionlisting', 'method' => 'GET', 'id' => 'admissionlisting']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', @$classes, isset($_GET['class']), ['class' => 'form-control select', 'id' => 'class_select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                        {{ Form::select('student', @$student, isset($_GET['student']), ['class' => 'form-control select', 'id' => 'student_select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_from', __('From'), ['class' => 'form-label']) }}
                        {{ Form::date('date_from', request('date_from') ?? '', ['class' => 'form-control']) }}
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_to', __('To'), ['class' => 'form-label']) }}
                        {{ Form::date('date_to', request('date_to') ?? '', ['class' => 'form-control']) }}
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex">
                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                        onclick="document.getElementById('admissionlisting').submit(); return false;"
                        data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                    <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="print" data-bs-title="print"
                        value="pdf"><span class="btn-inner--icon">Print
                        </span></button>
                        <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export" data-bs-title="Export"
                        value="excel"><span class="btn-inner--icon">Export</span></button>
                    {{-- <a href="#" onclick="generatePDF(); return false;" class="btn mx-1 btn-sm btn-outline-success"
                                     title="" title="Print">
    
                                    <span class="btn-inner--icon">Print
                                    </span>
                                </a> --}}
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
    <div id="registrationcont" class="card">
        <div class="mt-2 p-4" style="width: 100%; max-width: 100%; overflow-x: auto;">
            <div class="mt-4" style="margin: 0 auto; padding: 30px;">
                <div style="width: 100%; text-align: center;">
                    <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;">
                        <b>The Lynx School </b>
                    </p>
                </div>
                <div style="width: 100%; display: flex; justify-content: space-between;">
                    <p><b>Period From:
                        </b>{{ request('date_from') ? date('d M Y', strtotime(request('date_from'))) : '01 Jul 2023' }}</p>
                    <p><b>Branch: </b>{{ $branches[request('branch')] ?? 'All Branches' }}</p>
                    <p><b>Period To:
                        </b>{{ request('date_to') ? date('d M Y', strtotime(request('date_to'))) : '30 Jun 2024' }}</p>
                </div>

                <div style="width: 100%;">
                    <span style="font-size:1rem; font-weight:600; padding:10px; width:100%;">
                        {{ @$branches[request('branch')] ?? 'All Branches' }}
                    </span>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="datatable" style="width: 100%;">
                            <thead>
                                <tr class="table_heads" style="font-weight:400; font-size:0.8rem;">
                                    <th>{{ __('Sr No.') }}</th>
                                    <th>{{ __('B Sr No.') }}</th>
                                    <th>{{ __('Reg No #') }}</th>
                                    <th>{{ __('Roll No #') }}</th>
                                    <th>{{ __('Challan No #') }}</th>
                                    <th>{{ __('Admission Date') }}</th>
                                    <th>{{ __('Class') }}</th>
                                    <th>{{ __('Student Name') }}</th>
                                    @foreach ($heads as $head)
                                        <th>{{ $head->fee_head ?? '-' }}</th>
                                    @endforeach
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Adm. Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $mainloop = 0; @endphp
                                @foreach ($studentData as $branchId => $students)
                                    @foreach ($students as $index => $student)
                                        @php
                                            $studentRegNo = @$student->StudentRegistration->reg_no;
                                            // Get pre-calculated challan data instead of querying database
                                            $challanData = $studentChallanData[$studentRegNo] ?? [
                                                'challan_no' => '',
                                                'heads' => [],
                                                'total' => 0,
                                            ];
                                            $challanId = $challanData['challan_id'] ?? '';
                                        @endphp
                                        <tr>
                                            <td>{{ $mainloop++}}</td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->id }}</td>
                                            <td>{{ $student->enrollId ?? '' }}</td>
                                            {{-- challan view button on clicking on challan no and to this route: href="{{ route('installmentview', $challan->id) }}" --}}
                                            <td><a href="{{ route('installmentview', $challanId) }}">{{ $challanData['challan_no'] }}</a></td>
                                            <td>{{ date('d M Y', strtotime($student->created_at ?? '')) }}</td>
                                            <td>{{ @$student->class->name }}</td>
                                            <td>{{ @$student->StudentRegistration->stdname ?? '' }}</td>
                                            @foreach ($heads as $head)
                                                <td>
                                                    @php
                                                        $amount = '';
                                                        foreach ($challanData['heads'] as $challhead) {
                                                            if ($challhead['head_id'] == $head->id) {
                                                                $amount = $challhead['amount'];
                                                                break;
                                                            }
                                                        }
                                                        echo $amount;
                                                    @endphp
                                                </td>
                                            @endforeach
                                            <td>{{ $challanData['total'] }}</td>
                                            <td>{{ @$student->StudentRegistration->student_status == 'Enrolled' ? 'Yes' : 'No' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="branch-totla-row" style="background-color:#48494b4f !important; ">
                                        <td colspan="14"><b>Branch Total</b></td>
                                        <td><strong>{{ $branchTotals[$branchId] ?? '0' }}</strong></td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            {{-- <tbody>
                                    @foreach ($students as $index => $student)
                                        @php
                                            $challanHeads = [];
                                            $challan = App\Models\Challans::where(
                                                'student_id',
                                                @$student->StudentRegistration->reg_no,
                                            )
                                                ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])
                                                ->with('heads', 'heads.feehead')
                                                ->first();
                                            if ($challan) {
                                                foreach ($challan->heads as $head) {
                                                    $challanHeads[] = [
                                                        'name' => $head->feehead->fee_head,
                                                        'amount' => $head->price,
                                                        'head_id' => $head->head_id,
                                                    ];
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->owned_by }}</td>
                                            <td>{{ $student->id }}</td>
                                            <td>{{ $student->enrollId ?? '' }}</td>
                                            <td>{{ $challan->challanNo ?? '' }}</td>
                                            <td>{{ date('d M Y', strtotime($student->created_at ?? '')) }}</td>
                                            <td>{{ @$student->class->name }}</td>
                                            <td>{{ @$student->StudentRegistration->stdname ?? '' }}</td>
                                            @foreach ($heads as $head)
                                                <td>
                                                    @foreach ($challanHeads as $challhead)
                                                        @if ($challhead['head_id'] == $head->id)
                                                            {{ $challhead['amount'] }}
                                                            @break
                                                        @endif
                                                    @endforeach
                                                </td>
                                            @endforeach
                                            <td>{{ $student->total_amount ?? '0' }}</td>
                                            <td>{{ @$student->StudentRegistration->student_status == 'Enrolled' ? 'Yes' : 'No' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody> --}}
                                <br>
                            <tr class="branch-totla-row" style="background-color:#48494b4f !important; border-top: 5px solid #fff;">
                                <td colspan="14"><b>Grand Total</b></td>
                                <td><strong>{{ $grandTotal ?? '0' }}</strong></td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

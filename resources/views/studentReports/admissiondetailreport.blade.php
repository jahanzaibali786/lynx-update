@extends('layouts.admin')
@section('page-title')
    {{ __('Admission Listing.') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>

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

        function classStudents(id) {
            $.ajax({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                url: "{{ route('class.students') }}",
                type: "POST",
                data: {
                    class_id: id
                },
                dataType: "json",
                success: function(result) {
                    // --- populate students ---
                    if (result.status === "success") {
                        const $student = $("#student_select");
                        $student.empty();
                        $student.append($("<option>", {
                            value: "all",
                            text: "All Students"
                        }));
                        for (var sid in result.students) {
                            if (result.students.hasOwnProperty(sid)) {
                                $student.append($("<option>", {
                                    value: sid,
                                    text: result.students[sid]
                                }));
                            }
                        }
                        $student.val("all");
                    }

                    // --- also populate sections for this class ---
                    $.ajax({
                        url: "{{ route('class.section') }}",
                        type: "POST",
                        dataType: "json",
                        data: {
                            class_id: id,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            const $section = $("#section_select");
                            $section.empty();
                            $section.append($("<option>", {
                                value: "all",
                                text: "All Sections"
                            })); // or "All Session" if you prefer
                            for (let i = 0; i < data.length; i++) {
                                $section.append($("<option>", {
                                    value: data[i].id,
                                    text: data[i].name
                                }));
                            }
                            $section.val("all");
                        }
                    });
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
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', @$classes, isset($_GET['class']), ['class' => 'form-control select custom-select', 'id' => 'class_select']) }}
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
                        {{ Form::label('sections', __('Section'), ['class' => 'form-label']) }}
                        {{ Form::select('sections', $sections, request()->get('sections', ''), ['class' => 'form-control select', 'id' => 'section_select']) }}
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

                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <!-- Search Button -->
                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                        onclick="document.getElementById('admissionlisting').submit(); return false;"
                        data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                    <!-- Actions Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" id="actionDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Export
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                            <li>
                                <button class="dropdown-item" type="submit" name="export" value="excel">
                                    <i class="ti ti-file me-2"></i>Excel
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item" type="submit" name="print" value="pdf">
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
    <div id="registrationcont" class="card">
        <div class="p-4" style="width: 100%; max-width: 100%; overflow-x: auto;">
            <div class="" style="margin: 0 auto; padding: 30px;">
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
                    <div class="table-responsive maximumHeightNew" style="overflow-x: auto;">
                        <table class="datatable maximumHeightNew" style="width: 100%;">
                            <thead class="sticky-headerNew">
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
                                            <td>{{ $mainloop++ }}</td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->id }}</td>
                                            <td>{{ $student->enrollId ?? '' }}</td>
                                            {{-- challan view button on clicking on challan no and to this route: href="{{ route('installmentview', $challan->id) }}" --}}
                                            <td><a
                                                    href="{{ route('installmentview', $challanId) }}">{{ $challanData['challan_no'] }}</a>
                                            </td>
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
                                        <td colspan="8"><b>Branch Total</b></td>
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
                            <tr class="branch-totla-row"
                                style="background-color:#48494b4f !important; border-top: 5px solid #fff;">
                                <td colspan="8"><b>Grand Total</b></td>
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

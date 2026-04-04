@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Challans') }}
@endsection
@php
    // Determine the route based on the challan type
    $challanType = $type;
    $route = '';

    if ($challanType == 'admission') {
        $route = 'admissionchallanlist';
    } elseif ($challanType == 'registration') {
        $route = 'registrationchallanlist';
    } elseif ($challanType == 'regular') {
        $route = 'regularchallanlist';
    } elseif ($challanType == 'advance') {
        $route = 'advancechallanlist';
    } elseif ($challanType == 'readmission') {
        $route = 'readmissionchallanlist';
    } elseif ($challanType == 'withdrawal') {
        $route = 'withdrawchallanlist';
    } elseif ($challanType == 'transfer') {
        $route = 'transferchallanlist';
    } else {
        $route = ''; // fallback route
    }
@endphp
@push('script-page')
<script>
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

    $(document).on('change', '#class_select', function() {
        let classId = $(this).val();
        $.ajax({
            url: "{{ route('class.student_head') }}",
            type: "POST",
            data: {
                class_id: classId,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
                var $studentSelect = $('#student_select');
                // Remove previous custom select wrapper and instance
                if ($studentSelect[0] && $studentSelect[0].customSelectInstance) {
                    $studentSelect[0].customSelectInstance.destroy();
                    delete $studentSelect[0].customSelectInstance;
                }
                if ($studentSelect.next('.custom-select-wrapper').length) {
                    $studentSelect.next('.custom-select-wrapper').remove();
                }
                $studentSelect.removeClass('custom-select');

                // Clear and append new options
                $studentSelect.empty();
                $studentSelect.append($('<option>', {
                    value: '',
                    text: 'Select Student'
                }));
                
                for (var j = 0; j < data.student.length; j++) {
                    var std = data.student[j];
                    $studentSelect.append($('<option>', {
                        value: std.roll_no,
                        text: std.roll_no + ' - ' + std.stdname + ' s/d/o ' + std.fathername
                    }));
                }

                // Re-add class and re-init
                $studentSelect.addClass('custom-select');
                $studentSelect.show();
                // Directly create new CustomSelect instance for this select only
                if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                    window.CustomSelect.create($studentSelect[0]);
                }
            }
        });
    });
</script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __($breadcrumb) }}</li>
@endsection
@push('script-page')
    <script></script>
@endpush
@section('action-btn')
    @if ($challanType == 'advance')
        <div class="float-end">
            <a href="#" data-size="lg" data-url="{{ route('createChallan') . '?type=advance' }}" data-ajax-popup="true"
                data-bs-title="{{ __('Create') }}" data-bs-toggle="{{ __('Create Advance Challan') }}"
                class="btn btn-sm btn-primary">
                Create
            </a>
        </div>
    @elseif($challanType == 'regular')
        <div class="float-end">
            <a href="#" data-size="lg" data-url="{{ route('createChallan') . '?type=regular' }}"
                data-ajax-popup="true" data-bs-title="{{ __('Create') }}"
                data-bs-toggle="{{ __('Create Regular Challan') }}" class="btn btn-sm btn-primary">
                Create
            </a>
        </div>
    @endif

    {{-- <div class="float-end">
    {{-- @can('create session')
    <a href="{{ route('registration.create') }}" data-bs-title="{{__('Create')}}" class="btn btn-sm btn-primary">
Create
</a>
{{-- @endcan
</div> --}}
@endsection
@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{-- @dd($type) --}}

                        {{-- @dd($route) --}}
                        {{ Form::open(['route' => $route, 'method' => 'GET', 'id' => 'admission_challan_form']) }}
                        {{-- {{ Form::hidden('type', @$challan_list->first()->challan_type) }} --}}
                        <div class="row d-flex" style="width: 100%;">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                                    {{ Form::select('session', $session, isset($_GET['session']) ? $_GET['session'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            @if (\Auth::user()->type == 'company')
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, isset($_GET['class']) ? $_GET['class'] : '', ['class' => 'form-control select custom-select', 'id' => 'class_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box std_data">
                                    {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                                    {{ Form::select('student', @$students, isset($_GET['student']) ? $_GET['student'] : 'all', ['class' => 'form-control select custom-select', 'id' => 'student_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            @if ($challanType == 'admission')
                                {{-- //to and from  --}}
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mt-2">
                                    <div class="btn-box">
                                        {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('from_date', isset($_GET['from_date']) ? $_GET['from_date'] : now()->startOfMonth(), ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mt-2">
                                    <div class="btn-box">
                                        {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('to_date', isset($_GET['to_date']) ? $_GET['to_date'] : now()->format('Y-m-d'), ['class' => 'form-control']) }}
                                    </div>
                                </div>
                            @else
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mt-2">
                                    <div class="btn-box">
                                        {{ Form::label('challan_date', __('Challan Month'), ['class' => 'form-label']) }}<span
                                            style="color: red">&nbsp;(for the month)</span>
                                        {!! Form::month('challan_date', isset($_GET['challan_date']) ? $_GET['challan_date'] : date('Y-m'), [
                                            'class' => 'form-control',
                                            'id' => 'challan_date',
                                        ]) !!}
                                    </div>
                                </div>
                            @endif
                            <div
                                class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mt-5 d-flex justify-content-start gap-2">
                                <button type="submit" class="btn mx-1 btn-sm btn-outline-primary"
                                    data-bs-title="Search"><span class="btn-inner--icon">Search</span></button>
                                {{-- @if (strtolower(@$challan_list->first()->challan_type) == 'regular') --}}
                                @if(\Auth::user()->type == 'super admin' || \Auth::user()->type == 'company')
                                @if ($challanType == 'regular')
                                    <button type="button" id="generateChallanButton"
                                        class="btn mx-1 btn-sm btn-outline-primary ml-2"
                                        data-bs-title="{{ __('Generate Bulk Challans') }}">
                                        <span class="btn-inner--icon">Generate Bulk Challan</span>
                                    </button>
                                @endif
                                @endif
                                {{-- @endif --}}
                                <button id="printButton" class="btn mx-1 btn-sm btn-outline-success ml-2"
                                    title="Download Challan in PDF" onclick="openPrintModal(event)" disabled>Download
                                    Challan</button>
                                {{-- //rollback --}}
                                @if(\Auth::user()->type == 'super admin' || \Auth::user()->type == 'company')
                                <button type="button" id="rollbackButton" class="btn mx-1 btn-sm btn-outline-danger ml-2"
                                    data-bs-title="{{ __('Rollback Challans') }}">
                                    <span class="btn-inner--icon">Rollback Challan</span>
                                </button>
                                @endif
                            </div>
                            {{ Form::close() }}

                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="printModalLabel">Download Options</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Select Download options:</p>
                        <button class="btn btn-primary" onclick="printSeparatePDF()">Separate PDF</button>
                        <button class="btn btn-primary" onclick="printSinglePDF()">Single PDF</button>
                    </div>
                </div>
            </div>
        </div>
        <table class="">
            <thead class="table_heads">
                <tr>
                    <th><input id="checkAll" type="checkbox"></th>
                    <th>{{ __('#') }}</th>
                    <th>{{ __('Challan No.') }}</th>
                    <th>{{ __('Roll No.') }}</th>
                    <th>{{ __('Student Name') }}</th>
                    <th>{{ __('Challan Type') }}</th>
                    @if (strtolower($challanType) == 'advance')
                        <th>{{ __('Challan Months') }}</th>
                    @else
                        <th>{{ __('Challan Month') }}</th>
                    @endif 
                    <th>{{ __('Total Amount') }}</th>
                    <th>{{ __('Receivable Amount') }}</th>
                    <th>{{ __('Rem Amount') }}</th>
                    <th>{{ __('status') }}</th>
                    <th>{{ __('Issue Date') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th></th>
                    <th width="250px;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($challan_list as $challan)
                
                   
                    <tr>
                        <td><input type="checkbox" name="checked[]" value="{{$challan->id}}"></td>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $challan->challanNo }}</td>
                        {{-- @php
            $st_id = $challan->student_id;
            $studentData = App\Models\StudentRegistration::with('enrollment.class', 'enrollment.section')
            ->where('id', $st_id)
            ->first();
            @endphp --}}

                        <td>{{ $challan->rollno }}</td>
                        <td class="student-name">{{ @$challan->student->stdname }}</td>
                        <td>{{ $challan->challan_type }}</td>
                        @if (strtolower($challan->challan_type) == 'advance' && $challan->other_months != null)
                            <td>
                                {{ collect(explode(',', $challan->other_months))->map(fn($date) => \Carbon\Carbon::parse($date)->format('F, Y'))->implode(', ') }}
                            </td>
                        @else
                            <td>{{ \Carbon\Carbon::parse($challan->fee_month)->format('F,Y') }}</td>
                        @endif                        
                        <td>{{ $challan->total_amount }}</td>
                        <td>{{ $challan->total_amount - $challan->concession_amount }}</td>
                        <td>{{ $challan->total_amount - ($challan->paid_amount + $challan->concession_amount) }}</td>
                        <td>{{ $challan->status }}</td>
                        <td>{{ $challan->issue_date }}</td>
                        <td>{{ $challan->due_date }}</td>
                        <td class="challan-id"><input name="challan-id" value="{{ $challan->id }}" hidden></td>
                        <td>
                            <div class="action-btn ms-2">
                                <a href="{{ route('challan.show', $challan->id) }}"
                                    class="mx-1 btn btn-sm align-items-center btn-outline-primary"
                                    data-bs-title="{{ __('View Challan') }}" data-bs-toggle="tooltip"
                                    data-bs-title="{{ __('Show') }}"><span class="btn-inner--icon"><i
                                            class="ti ti-eye "></i></span></a>
                                @if (strtolower($challan->status) == 'issued' && \Auth::user()->type == 'super admin' || \Auth::user()->type == 'company')
                                    <a href="{{ route('challan.edit', $challan->id) }}"
                                        class="mx-1 btn btn-sm align-items-center btn-outline-primary"
                                        data-bs-toggle="tooltip" data-bs-title="{{ __('Edit Challan') }}"
                                        data-bs-title="{{ __('Edit') }}"><span class="btn-inner--icon"><i
                                                class="ti ti-pencil "></i></span></a>
                                @endif
                            </div>
                        </td>
                        <input type="text" name="challan_id" value="{{ $challan->id }}" hidden>
                        {{-- <td class="student-id"><input name="student-id" value="{{ $challan->student_id }}" ></td> --}}
                        {{-- <td>
                <div class="action-btn bg-primary ms-2">
                    <a href="#!" data-url="{{route('challan.challanedit',$challan->id)}}" data-ajax-popup="true"
            class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}"
            data-bs-title="{{__('Edit')}}"><span class="ti ti-pencil text-white"></i></a>
            </div>
            </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const generateChallanBtn = document.getElementById('generateChallanButton');
            if (generateChallanBtn) {
                generateChallanBtn.addEventListener('click', function() {
                    const form = document.getElementById('admission_challan_form');
                    const formData = new FormData(form);
                    const responseMessage = document.getElementById('responseMessage');
                    const fieldValue = formData.get('student');
                    if (!fieldValue || fieldValue.trim() === "all") {
                        const fieldbranch = formData.get('branches');
                        if (!fieldbranch || fieldbranch.trim() === "") {
                            show_toastr('error', 'The Branch field required .', 'error');
                            return;
                        }
                        const fieldsession = formData.get('session');
                        if (!fieldsession || fieldsession.trim() === "") {
                            show_toastr('error', 'The Session field required .', 'error');
                            return;
                        }
                        const fieldclass = formData.get('class');
                        if (!fieldclass || fieldclass.trim() === "") {
                            show_toastr('error', 'The Class field required .', 'error');
                            return;
                        }
                        const fielddate = formData.get('challan_date');
                        if (!fielddate || fielddate.trim() === "") {
                            show_toastr('error', 'The Challan Date field required .', 'error');
                            return;
                        }
                    } else {
                        const fieldsession = formData.get('session');
                        if (!fieldsession || fieldsession.trim() === "") {
                            show_toastr('error', 'The Session field required .', 'error');
                            return;
                        }
                        const fielddate = formData.get('challan_date');
                        if (!fielddate || fielddate.trim() === "") {
                            show_toastr('error', 'The Challan Date field required .', 'error');
                            return;
                        }
                    }
                    fetch('{{ route('bulkchallan') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show_toastr('success', data.message, 'error');
                                // location.reload();
                                $('#admission_challan_form').submit();
                            } else {
                                show_toastr('error', data.message, 'error');
                                // alert(data.message);
                                location.reload();
                                // responseMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            responseMessage.innerHTML =
                                '<div class="alert alert-danger">An error occurred while generating bulk challans.</div>';
                        });
                });
            }
        </script>
        <script>
            var Printbtn = document.getElementById('printButton');
            var checkAllCheckbox = document.getElementById('checkAll');
            var rowCheckboxes = document.querySelectorAll('input[name="checked[]"]');
            var rollBackBtn = document.getElementById('rollbackButton');

            checkAllCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = checkAllCheckbox.checked;
                });
                updatePrintButtonState();
            });

            rowCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if (!this.checked) {
                        checkAllCheckbox.checked = false;
                    } else if (Array.from(rowCheckboxes).every(cb => cb.checked)) {
                        checkAllCheckbox.checked = true;
                    }
                    updatePrintButtonState();
                });
            });

            function updatePrintButtonState() {
                var anyChecked = Array.from(rowCheckboxes).some(function(checkbox) {
                    return checkbox.checked;
                });
                Printbtn.disabled = !anyChecked;
            }

            updatePrintButtonState();

            rollBackBtn.addEventListener('click', function() {
                var checkedRows = [];
                var checkboxes = document.getElementsByName("checked[]");
                checkboxes.forEach(function(checkbox) {
                    if (checkbox.checked) {
                        var rowData = [];
                        var row = checkbox.closest("tr");
                        var cells = row.querySelectorAll("td");
                        var studentName = '';
                        var studentId = '';

                        cells.forEach(function(cell) {
                            var cellContent;
                            var std_id = cell.querySelector(".student-id");
                            var input = cell.querySelector("input");
                            var div = cell.querySelector("div");
                            var label = cell.querySelector("label");
                            if (input && input.tagName.toLowerCase() === "input") {
                                cellContent = input.value;
                            } else if (div && div.tagName.toLowerCase() === "div") {
                                cellContent = "";
                            } else if (label && label.tagName.toLowerCase() === "label") {
                                cellContent = label.textContent.trim();
                            } else {
                                cellContent = cell.textContent.trim();
                            }
                            rowData.push(cellContent);

                            // Fetch student name and ID
                            if (cell.classList.contains('student-name')) {
                                studentName = cellContent;
                            }
                            if (cell.classList.contains('student-id')) {
                                studentId = cellContent;
                            }
                        });

                        rowData.push({
                            studentName: studentName,
                            studentId: studentId
                        });
                        checkedRows.push(rowData);
                    }
                });

                if (checkedRows.length == 0) {
                    show_toastr('error', 'Please select at least one challan to rollback.', 'error');
                    return;
                }
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to rollback the selected challans?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, rollback!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{ route('challan.rollback') }}",
                            type: "POST",
                            data: {
                                rows: checkedRows
                            },
                            success: function(result) {
                                if (result.success) {
                                    Swal.fire({
                                        title: 'Rollback Result',
                                        text: result.message,
                                        icon: 'success',
                                        customClass: {
                                            popup: 'text-start white-space-pre-wrap'
                                        }
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', result.message, 'error');
                                }
                            }
                        });
                    }
                });

            });
        </script>

        <script>
            function openPrintModal(e) {
                e.preventDefault();
                $('#printModal').modal('show');
            }

            function printSeparatePDF() {
                console.log('Printing separate PDFs');
                getCheckedRowData('separate');
                $('#printModal').modal('hide');
            }

            function printSinglePDF() {
                console.log('Printing single PDF');
                getCheckedRowData('single');
                $('#printModal').modal('hide');
            }

            function getCheckedRowData(printType) {
                var Printbtn = document.getElementById('printButton');
                Printbtn.textContent = 'Processing...';
                Printbtn.disabled = true;

                // Get only the selected challan IDs
                var checkedRowsData = [];
                var checkboxes = document.getElementsByName("checked[]");
                
                checkboxes.forEach(function(checkbox) {
                    if (checkbox.checked) {
                        checkedRowsData.push(checkbox.value);
                    }
                });
                
                if (checkedRowsData.length === 0) {
                    alert('Please select at least one challan to print');
                    Printbtn.textContent = 'Download Challan';
                    Printbtn.disabled = false;
                    return;
                }
                
                // Show loading overlay
                showLoadingOverlay(checkedRowsData.length, 0, printType);
                
                // Store all PDFs for separate mode
                var allPdfs = [];
                var batchSize = 50;
                var batchIndex = 0;
                var totalChallans = checkedRowsData.length;
                
                function processBatch() {
                    var startIdx = batchIndex * batchSize;
                    var endIdx = Math.min(startIdx + batchSize, totalChallans);
                    var currentBatchIds = checkedRowsData.slice(startIdx, endIdx);
                    
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    $.ajax({
                        url: '{{ route('printchallans') }}',
                        method: 'POST',
                        data: {
                            rowsdata: currentBatchIds,
                            printType: printType,
                            batchSize: batchSize,
                            batchIndex: batchIndex
                        },
                        success: function(response) {
                            if (response.error) {
                                hideLoadingOverlay();
                                alert(response.error);
                                Printbtn.textContent = 'Download Challan';
                                Printbtn.disabled = false;
                                return;
                            }
                            
                            if (response.pdfs) {
                                allPdfs = allPdfs.concat(response.pdfs);
                            }
                            
                            var processedCount = response.processedCount || endIdx;
                            var message = (printType === 'single') 
                                ? 'Processing ' + processedCount + '/' + totalChallans + ' challengans...' 
                                : 'Downloading ' + processedCount + '/' + totalChallans + ' challengans...';
                            updateLoadingMessage(message);
                            
                            if (response.hasMoreBatches) {
                                batchIndex++;
                                processBatch();
                            } else {
                                hideLoadingOverlay();
                                
                                if (printType === 'separate') {
                                    allPdfs.forEach(function(pdfBase64, index) {
                                        var byteCharacters = atob(pdfBase64);
                                        var byteNumbers = new Array(byteCharacters.length);
                                        for (var i = 0; i < byteCharacters.length; i++) {
                                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                                        }
                                        var byteArray = new Uint8Array(byteNumbers);
                                        var blob = new Blob([byteArray], { type: 'application/pdf' });
                                        var url = window.URL.createObjectURL(blob);
                                        var filename = 'challan_' + (index + 1) + '.pdf';
                                        var a = document.createElement('a');
                                        a.href = url;
                                        a.download = filename;
                                        document.body.appendChild(a);
                                        a.click();
                                        document.body.removeChild(a);
                                        window.URL.revokeObjectURL(url);
                                    });
                                } else {
                                    if (allPdfs.length > 0) {
                                        var byteCharacters = atob(allPdfs[0]);
                                        var byteNumbers = new Array(byteCharacters.length);
                                        for (var i = 0; i < byteCharacters.length; i++) {
                                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                                        }
                                        var byteArray = new Uint8Array(byteNumbers);
                                        var blob = new Blob([byteArray], { type: 'application/pdf' });
                                        var url = window.URL.createObjectURL(blob);
                                        var filename = 'bulk_challan.pdf';
                                        var a = document.createElement('a');
                                        a.href = url;
                                        a.download = filename;
                                        document.body.appendChild(a);
                                        a.click();
                                        document.body.removeChild(a);
                                        window.URL.revokeObjectURL(url);
                                    }
                                }
                                
                                Printbtn.textContent = 'Download Challan';
                                Printbtn.disabled = false;
                            }
                        },
                        error: function(xhr, status, error) {
                            hideLoadingOverlay();
                            console.error(xhr.responseText);
                            alert('Failed to fetch PDF content: ' + error);
                            Printbtn.textContent = 'Download Challan';
                            Printbtn.disabled = false;
                        }
                    });
                }
                
                processBatch();
            }
            
            // Loading overlay functions
            function showLoadingOverlay(count, startCount, printType) {
                hideLoadingOverlay();
                
                var overlay = document.createElement('div');
                overlay.id = 'pdfLoadingOverlay';
                overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:9999;display:flex;flex-direction:column;justify-content:center;align-items:center;color:white;';
                
                var spinner = document.createElement('div');
                spinner.style.cssText = 'width:60px;height:60px;border:6px solid #f3f3f3;border-top:6px solid #3498db;border-radius:50%;animation:spin 1s linear infinite;';
                spinner.id = 'pdfLoadingSpinner';
                
                var message = document.createElement('p');
                message.style.cssText = 'margin-top:25px;font-size:20px;font-weight:500;';
                message.id = 'pdfLoadingMessage';
                
                var progressContainer = document.createElement('div');
                progressContainer.style.cssText = 'width:300px;height:8px;background:#444;border-radius:4px;margin-top:15px;overflow:hidden;';
                
                var progressBar = document.createElement('div');
                progressBar.id = 'pdfProgressBar';
                progressBar.style.cssText = 'height:100%;background:linear-gradient(90deg, #3498db, #2ecc71);width:0%;transition:width 0.3s ease;';
                
                progressContainer.appendChild(progressBar);
                
                var percentText = document.createElement('p');
                percentText.style.cssText = 'margin-top:10px;font-size:14px;color:#aaa;';
                percentText.id = 'pdfPercentText';
                percentText.textContent = '0%';
                
                overlay.appendChild(spinner);
                overlay.appendChild(message);
                overlay.appendChild(progressContainer);
                overlay.appendChild(percentText);
                document.body.appendChild(overlay);
                
                var style = document.createElement('style');
                style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
                document.head.appendChild(style);
                
                // Set initial message based on print type
                var initialMessage = (printType === 'single') 
                    ? 'Processing 0/' + count + ' challengans...' 
                    : 'Downloading 0/' + count + ' challengans...';
                updateLoadingMessage(initialMessage);
            }
            
            function updateLoadingMessage(message) {
                var messageEl = document.getElementById('pdfLoadingMessage');
                var percentEl = document.getElementById('pdfPercentText');
                var progressEl = document.getElementById('pdfProgressBar');
                
                if (messageEl) messageEl.textContent = message;
                
                var match = message.match(/(\d+)\/(\d+)/);
                if (match && percentEl && progressEl) {
                    var current = parseInt(match[1]);
                    var total = parseInt(match[2]);
                    var percent = Math.round((current / total) * 100);
                    percentEl.textContent = percent + '%';
                    progressEl.style.width = percent + '%';
                }
            }
            
            function hideLoadingOverlay() {
                var overlay = document.getElementById('pdfLoadingOverlay');
                if (overlay) overlay.remove();
            }
        </script>
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



            $(document).on('change', '#class_select', function() {
                var classId = $(this).val();
                if (classId) {
                    classStudents(classId);
                }
            });

            $(document).ready(function() {
                $('#branch_from').trigger('branchcustomer');
                // JsSearchBox();
            });
        </script>
    @endsection

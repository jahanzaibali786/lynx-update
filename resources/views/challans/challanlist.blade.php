@extends('layouts.admin')
@section('page-title')
{{__('Manage Challans')}}
@endsection
@push('script-page')

@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('All Challans')}}</li>
@endsection
@section('action-btn')
{{--<div class="float-end">
    {{-- @can('create session')
    <a href="{{ route('registration.create') }}" data-bs-title="{{__('Create')}}" class="btn btn-sm btn-primary">
Create
</a>
{{-- @endcan 
</div>--}}
@endsection
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
{{-- @if (\Auth::user()->type == 'company') --}}
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body filter_change">
                <!-- {{ Form::open(['route' => ['class_wise_fee.index'], 'method' => 'GET', 'id' => 'class_wise_fee_submit']) }} -->
                {{ Form::open(['route' => ['bulkchallan'], 'method' => 'POST', 'id' => '']) }}
                    <div class="row d-flex justify-content-end ">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, '', ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                                {{ Form::select('session', $session, '', ['class' => 'form-control select', 'id' => 'sessionselect', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                {{ Form::select('class', $class, '', ['class' => 'form-control select', 'id' => 'class_select', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                                {{ Form::select('student', [], 'all', ['class' => 'form-control select', 'id' => 'student_select', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mt-2" style="float:left; position: relative; left:-57%;">
                            <div class="btn-box">
                                {{ Form::label('challan_date', __('Challan Date'), ['class' => 'form-label']) }}<span style="color: red">&nbsp;(for the month)</span>
                                {!! Form::date('challan_date', null, ['class' => 'form-control', 'id' => 'challan_date']) !!}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <button type="submit" class="btn mx-1 btn-sm btn-outline-primary"  data-bs-title="{{ __('Generate Bulk Challans') }}">
                                <span class="btn-inner--icon">Generate Bulk Challan</span>
                            </button>
                        </div>
                    </div>
                    {{ Form::close() }}

                </div>
            </div>
        </div>
    </div>
</div>
{{-- @endif --}}

<div class="card p-3" style="display: flex; justify-content:end; align-items:end;">
    <!-- <button id="printButton" class="btn mx-1 btn-sm btn-outline-success" onclick="getCheckedRowData()">Print Challan</button> -->
    <button id="printButton" class="btn mx-1 btn-sm btn-outline-success" onclick="openPrintModal()" disabled>Download
        Challan</button>
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
</div>
<table class="datatable">
    <thead>
    <tr class="table_heads">
        <th>{{__('Challan No.')}}</th>
        <th>{{__('Student Name')}}</th>
        <th>{{__('Challan Type')}}</th>
        <th>{{__('Challan Month')}}</th>
        <th>{{__('Total Amount')}}</th>
        <th>{{__('Remaining Amount')}}</th>
        <th>{{__('status')}}</th>
        <th>{{__('Issue Date')}}</th>
        <th>{{__('Due Date')}}</th>
        <th>{{__('Action')}}</th>
        <th><input id="checkAll" type="checkbox"></th>
    </tr>
    </thead>
    <tbody>
        @foreach($challans as $challan)
        <tr>
            <td>{{ $challan->challanNo }}</td>
            {{--@php
            $st_id = $challan->student_id;
            $studentData = App\Models\StudentRegistration::with('enrollment.class', 'enrollment.section')
            ->where('id', $st_id)
            ->first();
            @endphp --}}
            {{-- @php
            $st_id = $challan->student_id;
            $studentData = App\Models\StudentRegistration::with('class')->where('id', $st_id)->first();
            @endphp --}}
            <td class="student-name">{{ @$challan->student->stdname }}</td>
            <td>{{ $challan->challan_type }}</td>
            <td>{{ \Carbon\Carbon::parse($challan->fee_month)->format('F,Y') }}</td>
            <td>{{ $challan->total_amount }}</td>
            <td>{{ ($challan->total_amount)-($challan->paid_amount + $challan->concession_amount)  }}</td>
            <td>{{ $challan->status }}</td>
            <td>{{ $challan->issue_date }}</td>
            <td>{{ $challan->due_date }}</td>
            <td>
                <div class="action-btn ms-2">
                    <a href="#!" data-size="lg" data-url="{{route('challan.pay',$challan->id)}}" data-ajax-popup="true"
                        class="mx-1 btn mx-1 btn-sm btn-outline-primary" 
                        data-bs-title="{{__('Pay Challan')}}" data-bs-title="{{__('Pay Challan')}}"><span
                            class="btn-inner--icon"><i class="ti ti-pencil "></i></span></a>
                    <a href="{{route('challan.show',$challan->id)}}" class="mx-3 btn btn-sm align-items-center" data-bs-title="{{__('View')}}"
                        data-bs-title="{{__('View')}}"><i class="ti ti-eye text-white"></i></a>
                </div>

            </td>
            <td><input type="checkbox" name="checked[]"></td>
            <td class=""><input type="text" name="challan_id" value="{{$challan->id}}" hidden></td>
            <td class="student-id" style="display: none;" >{{ $challan->student_id }}</td>
            {{--<td>
                <div class="action-btn bg-primary ms-2">
                    <a href="#!" data-url="{{route('challan.challanedit',$challan->id)}}" data-ajax-popup="true"
            class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}"
            data-bs-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
            </div>
            </td>--}}
        </tr>
        @endforeach
    </tbody>
</table>
<script>
var Printbtn = document.getElementById('printButton');
var checkAllCheckbox = document.getElementById('checkAll');
var rowCheckboxes = document.querySelectorAll('input[name="checked[]"]');

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
</script>

<script>
function openPrintModal() {
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
    Printbtn.textContent = 'Downloading...';
    Printbtn.disabled = true;

    var checkedRowsData = [];
    var className = '{{ @$studentData->class->name }}';
    var challanMonth = '{{ \Carbon\Carbon::now()->format('M') }}';
    var checkboxes = document.getElementsByName("checked[]");
    
    checkboxes.forEach(function(checkbox) {
        if (checkbox.checked) {
            var rowData = [];
            var row = checkbox.closest("tr");
            var cells = row.querySelectorAll("td");
            var studentName = '';
            var studentId = '';
            var challanNo = '';

            cells.forEach(function(cell) {
                var cellContent;
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
                
                // Fetch student name, ID, and challan number
                if (cell.classList.contains('student-name')) {
                    studentName = cellContent;
                }
                if (cell.classList.contains('student-id')) {
                    studentId = cellContent;
                }
                if (cell.cellIndex === 0) {
                    challanNo = cellContent;
                }
            });
            
            rowData.push({ studentName: studentName, studentId: studentId, challanNo: challanNo });
            checkedRowsData.push(rowData);
        }
    });
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
            rowsdata: checkedRowsData,
            printType: printType
        },
        success: function(response) {
            if (response.pdfs && response.pdfs.length > 0) {
                if (printType === 'separate') {
                    response.pdfs.forEach(function(pdfBase64, index) {
                        var byteCharacters = atob(pdfBase64);
                        var byteNumbers = new Array(byteCharacters.length);
                        for (var i = 0; i < byteCharacters.length; i++) {
                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                        }
                        var byteArray = new Uint8Array(byteNumbers);
                        var blob = new Blob([byteArray], { type: 'application/pdf' });
                        var url = window.URL.createObjectURL(blob);
                        var rowMeta = checkedRowsData[index][checkedRowsData[index].length - 1];
                        var studentName = rowMeta.studentName;
                        var studentId = rowMeta.studentId;
                        var challanNo = rowMeta.challanNo;
                        var challanMonth = '{{ \Carbon\Carbon::now()->format('F-Y') }}';
                        var filename = `${studentId}_${studentName}_${challanMonth}_challan.pdf`;
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    });
                } else {
                    var byteCharacters = atob(response.pdfs[0]);
                    var byteNumbers = new Array(byteCharacters.length);
                    for (var i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    var byteArray = new Uint8Array(byteNumbers);
                    var blob = new Blob([byteArray], { type: 'application/pdf' });
                    var url = window.URL.createObjectURL(blob);
                    var filename = `${challanMonth}_challan.pdf`;
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            } else {
                alert('Failed to generate PDFs');
            }
            Printbtn.textContent = 'Download Challan';
            Printbtn.disabled = false;
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            alert('Failed to fetch PDF content');
            Printbtn.textContent = 'Download Challan';
            Printbtn.disabled = false;
        }
    });
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
@endsection
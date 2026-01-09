@extends('layouts.admin')
@section('page-title')
{{__('Clearance Certificate')}}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
function generatePDF() {
    console.log('generating');
    const element = document.getElementById('clearancecer');
    const opt = {
        filename: 'clearance-certificate.pdf',
        html2canvas: {
            scale: 1
        },
        jsPDF: {
            unit: 'pt',
            format: [700, 900],
            orientation: 'portrait'
        }
    };
    html2pdf().from(element).set(opt).save();
}

function printPDF() {
    console.log('printing');
    const element = document.getElementById('clearancecer');
    const opt = {
        filename: 'clearance-certificate.pdf',
        html2canvas: {
            scale: 1
        },
        jsPDF: {
            unit: 'pt',
            format: [700, 900],
            orientation: 'portrait'
        }
    };
    html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
        window.open(pdf);
    });
}
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Clearance Certificate')}}</li>
@endsection
@section('action-btn')
<div class="float-end">
    {{-- @can('create session') --}}
    {{-- @endcan --}}
</div>
@endsection
@section('content')
@if (\Auth::user()->type == 'company')
    <div class="row">
    <div class="col-sm-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body filter_change">
            {{ Form::open(['route' => 'clearance.index', 'method' => 'GET', 'id' => '']) }}
            <div class="row d-flex">
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                        {{ Form::date('date', @$date ? $date : '', ['class' => 'form-control','required' => 'required']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branches', $branches, @$studentDetail->branch ? @$studentDetail->branch->id : '' , ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', $class, isset($_GET['class']) ? $_GET['class'] : '', ['class' => 'form-control select', 'onchange' => 'classcustomer(this.value)', 'id' => 'class_select', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                        {{ Form::select('student', $students, isset($_GET['student']) ? $_GET['student'] : '' , ['class' => 'form-control select custom-select', 'id' => 'student_select', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 gap-2 d-flex justify-content-end">
                <button  data-bs-title="Apply" type="submit" id="" class="btn mx-1 btn-sm btn-outline-primary ml-2"><span class="btn-inner--icon">Search</span></button>
                <a  data-bs-title="Print" class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span class="btn-inner--icon">Print</span></a>
                </div>
            </div>
            {{ Form::close() }}
        </div>
        </div>
    </div>
    </div>
</div>
@endif
<div class="card mt-2 p-4" id="clearancecer">
    <div style="width: 8.5in; height: 8in; margin: 0 auto; padding: 30px; border: 2px solid black;">
        <div style="width: 100%;">
            <div style="float: left; width: 33.33%; text-align: center;">
                <p></p>
            </div>
            <div style="float: left; width: 33.33%; text-align: center;">
                <p style="font-family:Curlz MT; font-size:2rem; text-align: center; font-weight: 800;">The Lynx School
                </p>
                <p style="text-transform: uppercase;">Pwd branch islamabad</p>
            </div>
            <div style="float: left; width: 33.33%; text-align: center;">
                <div class="logo">
                    <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;"
                        alt="logo">
                </div>
            </div>
        </div><br><br><br>
        <div class="" style="width:100%;">
            <p
                style="text-transform:uppercase; letter-spacing:0.7rem; width:100%; float: left; text-align:center; background-color:grey; padding:2px;">
                <b>Studentclearancecertificate</b></p>
        </div>
        <div class="" style="width:100%; float:right; text-align:right;">
            <p style=" width:100%; float: left;">Date: <span
                    style="border-bottom:1px solid black; width:30%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ \Carbon\Carbon::parse($date)->format('F-M-Y') ? \Carbon\Carbon::parse($date)->format('d-M-Y') : \Carbon\Carbon::now()->format('F-M-Y')  }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </p>
        </div>
        <div class="" style="width:100%;">
            <p style=" width:100%; float: left; font-size:0.9rem; line-height:1.5rem;">This is to certify that <span
                    style="border-bottom:1px solid black; width:30%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{@$studentDetail->StudentRegistration->stdname ? @$studentDetail->StudentRegistration->stdname :'---------'}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>Roll No. <span
                    style="border-bottom:1px solid black; width:30%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{@$studentDetail->id ? $studentDetail->id : '--------'}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                of class <span
                    style="border-bottom:1px solid black; width:30%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{@$studentDetail->class ? $studentDetail->class->name : '----------'}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                Section <span
                    style="border-bottom:1px solid black; width:30%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{@$studentDetail->section ? $studentDetail->section->name : '----------'}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>has
                cleared the following dues</p>
        </div>
        <div class="" style="width: 100%; float:left;">
            <ol style="width:100%;">
                <li>Libraray&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <span
                        style="border-bottom:1px solid black; width:50%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </li>
                <li> Class and School
                    Collection&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <span
                        style="border-bottom:1px solid black; width:50%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </li>
                <li>Laboratory&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span
                        style="border-bottom:1px solid black; width:50%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </li>
            </ol>
        </div>
        <div class="" style="width:100%; float:left;">
            <p>Tution fee paid up to <span
                    style="border-bottom:1px solid black; width:50%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>Security
                deposit Rs.<span
                    style="border-bottom:1px solid black; width:30%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;00.00&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

            </p>
        </div>
        <div class="" style="width:100%; float:left;">
            <p>Paid Date<span
                    style="border-bottom:1px solid black; width:50%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;20-Mar-2019&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>attended the school till<span
                    style="border-bottom:1px solid black; width:30%; text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </p>
        </div><br><br><br><br><br><br>
        <div class="" style="width: 8.5in; margin: 0 auto; padding: 30px; position:relative; top:200px;">
            <div style="width: 100%;">
                <div style="float: left; width: 47%; margin-right:3%;" class="">
                    <p style="border-top:1px solid black; text-align:center;">Office Assitant
</p>
                </div>
                <div style="float: left; width: 47%; margin-right:3%;">
                    <p style="border-top:1px solid black; text-align:center;">Head of the Institution
</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Branch -> Class (safe CustomSelect re-init, no JS search)
    function branchcustomer(branch) {
        $.ajax({
            url: "{{ route('branch.class') }}",
            type: "POST",
            data: {
                branch_id: branch,
                _token: "{{ csrf_token() }}"
            },
            dataType: "json",
            success: function(result) {
                // class select
                var $classSelect = $('#class_select');
                if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                    $classSelect[0].customSelectInstance.destroy();
                    delete $classSelect[0].customSelectInstance;
                }
                if ($classSelect.next('.custom-select-wrapper').length) {
                    $classSelect.next('.custom-select-wrapper').remove();
                }
                $classSelect.removeClass('custom-select');

                $classSelect.empty();
                $classSelect.append($('<option>', { value: 'all', text: 'All Class' }));
                for (var j = 0; j < result.length; j++) {
                    var cls = result[j];
                    $classSelect.append($('<option>', { value: cls.id, text: cls.name }));
                }

                $classSelect.addClass('custom-select').show();
                if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                    window.CustomSelect.create($classSelect[0]);
                }

                // reset students
                var $studentSelect = $('#student_select');
                if ($studentSelect[0] && $studentSelect[0].customSelectInstance) {
                    $studentSelect[0].customSelectInstance.destroy();
                    delete $studentSelect[0].customSelectInstance;
                }
                if ($studentSelect.next('.custom-select-wrapper').length) {
                    $studentSelect.next('.custom-select-wrapper').remove();
                }
                $studentSelect.removeClass('custom-select')
                              .empty()
                              .append($('<option>', { value: '', text: 'Select Student' }))
                              .addClass('custom-select')
                              .show();
                if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                    window.CustomSelect.create($studentSelect[0]);
                }
            }
        });
    }

    // Class -> Students (safe CustomSelect re-init, no JS search)
    function classStudents(classId) {
        $.ajax({
            url: "{{ route('class.student_head') }}",
            type: "POST",
            data: {
                class_id: classId,
                _token: "{{ csrf_token() }}"
            },
            dataType: "json",
            success: function(data) {
                var $studentSelect = $('#student_select');

                if ($studentSelect[0] && $studentSelect[0].customSelectInstance) {
                    $studentSelect[0].customSelectInstance.destroy();
                    delete $studentSelect[0].customSelectInstance;
                }
                if ($studentSelect.next('.custom-select-wrapper').length) {
                    $studentSelect.next('.custom-select-wrapper').remove();
                }
                $studentSelect.removeClass('custom-select');

                $studentSelect.empty();
                $studentSelect.append($('<option>', { value: '', text: 'Select Student' }));

                for (var i = 0; i < data.student.length; i++) {
                    var std = data.student[i];
                    $studentSelect.append($('<option>', {
                        value: std.roll_no,
                        text: std.roll_no + ' - ' + std.stdname + ' s/d/o ' + std.fathername
                    }));
                }

                $studentSelect.addClass('custom-select').show();
                if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                    window.CustomSelect.create($studentSelect[0]);
                }
            }
        });
    }

    // wire up class change
    $(document).on('change', '#class_select', function () {
        var classId = $(this).val();
        if (classId) {
            classStudents(classId);
        } else {
            $('#student_select').html('<option value="">{{ __("Select Student") }}</option>');
        }
    });
</script>

@endsection
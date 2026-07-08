@extends('layouts.admin')
@section('page-title')
    {{ __('Clearance Certificate') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Clearance Certificate') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}
        {{-- @endcan --}}
    </div>
@endsection
@section('content')

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
                                        {{ Form::date('date', @$date ? $date : now(), ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, @$studentDetail->branch ? @$studentDetail->branch->id : '', ['class' => 'form-control select', 'onchange' => 														'branchcustomer(this.value)']) }}
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
                                        {{ Form::select('student', $students, isset($_GET['student']) ? $_GET['student'] : '', ['class' => 'form-control select custom-select', 'id' => 'student_select', 'required' => 'required']) }}
                                    </div>
                                </div>
                                <div
                                    class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 gap-2 d-flex justify-content-end">
                                    <button data-bs-title="Apply" type="submit" id=""
                                        class="btn mx-1 btn-sm btn-outline-primary ml-2"><span
                                            class="btn-inner--icon">Search</span></button>
                                    <a href="{{ route('clearance.index', array_merge(request()->all(), ['print' => 1])) }}"
                                        target="_blank" class="btn mx-1 btn-sm btn-outline-success">
                                        Print
                                    </a>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="card mt-2 p-4">
        <div style="width:8.5in; margin:0 auto; font-family: DejaVu Sans, sans-serif; font-size:12px;">

            <div style="border:1px solid #000; padding:25px;">

                <!-- HEADER -->
                <div style="text-align:center; position:relative;">

                    <div style="font-size:26px; font-weight:bold; font-family: Edwardian Script ITC, cursive;">
                        The Lynx School
                    </div>

                    <div style="font-size:12px; margin-top:5px;">
                        {{isset(request()->branches) ? $branches[request()->branches] : ''}}
                    </div>

                    <img src="{{ asset('assets/images/lynx2.jpg') }}"
                        style="width:70px; position:absolute; right:0; top:0;">
                </div>

                <!-- TITLE -->
                <div style="margin-top:15px; background:#7d7d7d; text-align:center; letter-spacing:6px; padding:6px;">
                    STUDENT CLEARANCE CERTIFICATE
                </div>

                <!-- DATE -->
                <div style="margin-top:12px; text-align:right;">
                    Date:
                    <span style="display:inline-block; border-bottom:1px solid #000; width:140px; text-align:center;">
                        {{ $date ? \Carbon\Carbon::parse($date)->format('d-M-Y') : '' }}
                    </span>
                </div>

                <!-- STUDENT INFO -->
                <div style="margin-top:18px; line-height:24px;">
                    This is to certify that

                    <span style="display:inline-block; border-bottom:1px solid #000; width:200px; text-align:center;">
                        {{ @$studentDetail->StudentRegistration->stdname ?? '' }}
                    </span>

                    Roll No.

                    <span style="display:inline-block; border-bottom:1px solid #000; width:100px; text-align:center;">
                        {{ @$studentDetail->enrollId ?? '' }}
                    </span>

                    of class

                    <span style="display:inline-block; border-bottom:1px solid #000; width:150px; text-align:center;">
                        {{ @$studentDetail->class->name ?? '' }}
                    </span>
                </div>

                <!-- SECTION -->
                <div style="margin-top:8px;">
                    Section

                    <span style="display:inline-block; border-bottom:1px solid #000; width:60px; text-align:center;">
                        {{ @$studentDetail->section->name ?? '' }}
                    </span>

                    has cleared the following dues
                </div>

                <!-- LIST (ALIGNED PERFECTLY) -->
                <div style="margin-top:20px;">

                    <div style="margin-bottom:10px;">
                        <span style="display:inline-block; width:220px;">1. Library</span>
                        <span style="display:inline-block; border-bottom:1px solid #000; width:300px;"></span>
                    </div>

                    <div style="margin-bottom:10px;">
                        <span style="display:inline-block; width:220px;">2. Class and School Collection</span>
                        <span style="display:inline-block; border-bottom:1px solid #000; width:300px;"></span>
                    </div>

                    <div>
                        <span style="display:inline-block; width:220px;">3. Laboratory</span>
                        <span style="display:inline-block; border-bottom:1px solid #000; width:300px;"></span>
                    </div>

                </div>

                <!-- TUITION -->
                <div style="margin-top:25px;">
                    Tuition fee paid up to

                    <span style="display:inline-block; border-bottom:1px solid #000; width:220px; text-align:center;">
                        {{ @$lastchallan ? \Carbon\Carbon::parse($lastchallan->fee_month)->format('F Y') : '' }}
                    </span>

                    &nbsp;&nbsp; Security deposit Rs.

                    <span style="display:inline-block; border-bottom:1px solid #000; width:100px; text-align:center;">
                        {{ $security }}
                    </span>
                </div>

                <!-- PAID DATE -->
                <div style="margin-top:18px;">
                    Paid Date

                    <span style="display:inline-block; border-bottom:1px solid #000; width:200px; text-align:center;">
                        {{ @$lastchallan ? \Carbon\Carbon::parse($lastchallan->paid_date)->format('d-M-Y') : '' }}
                    </span>

                    attended the school till

                    <span style="display:inline-block; border-bottom:1px solid #000; width:200px;"></span>
                </div>

                <!-- SIGNATURES -->
                <div style="margin-top:120px;">

                    <div style="width:48%; display:inline-block; text-align:center;">
                        <div style="border-top:1px solid #000; width:260px; margin:0 auto;"></div>
                        Office Assistant
                    </div>

                    <div style="width:48%; display:inline-block; text-align:center;">
                        <div style="border-top:1px solid #000; width:260px; margin:0 auto;"></div>
                        Head of the Institution
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
                        .append($('<option>', {
                            value: '',
                            text: 'Select Student'
                        }))
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
                url: "{{ route('class.student_headwithdrawl') }}",
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
                    $studentSelect.append($('<option>', {
                        value: '',
                        text: 'Select Student'
                    }));

                    for (var i = 0; i < data.student.length; i++) {
                        var std = data.student[i];
                        $studentSelect.append($('<option>', {
                            value: std.id,
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
        $(document).on('change', '#class_select', function() {
            var classId = $(this).val();
            if (classId) {
                classStudents(classId);
            } else {
                $('#student_select').html('<option value="">{{ __('Select Student') }}</option>');
            }
        });
    </script>
@endsection

@extends('layouts.admin')
@section('page-title')
{{__('Withdrawl Notice')}}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>

<script>
function generatePDF() {
    console.log('generating');
    const element = document.getElementById('WithdrawlNotice');
    const opt = {
        filename: 'Withdrawl Notice.pdf',
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
    const element = document.getElementById('WithdrawlNotice');
    const opt = {
        filename: 'Withdrawl Notice.pdf',
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
<li class="breadcrumb-item">{{__('Withdrawl Notice')}}</li>
@endsection
@section('action-btn')
<div class="float-end">
</div>
@endsection
@section('content')
<div class="card mt-5">
    <div class="card-body filter_change">
        {{ Form::open(['route' => 'withdarawl_notice', 'method' => 'GET', 'id' => 'withdrawlnotice']) }}
        <div class="row d-flex justify-content-start">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                    {{ Form::select('class', @$class, isset($_GET['class']), ['class' => 'form-control select', 'id' => 'class_select', 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                    {{ Form::select('students', @$students, isset($_GET['students']), ['class' => 'form-control select', 'id' => 'student_select', 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-4">
                <a href="#" class="btn btn-sm btn-primary"
                    onclick="document.getElementById('withdrawlnotice').submit(); return false;"
                     title="" title="Search">
                    <span class="btn-inner--icon">Search</span>
                </a>
                <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span class="btn-inner--icon"><i
                            class="fas fa-print"></i></span>
                </a>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>
<div class="card mt-5 p-4">
    <div style="margin: 0 auto; padding: 30px;" id="WithdrawlNotice">
        <div style="width: 100%;">
            <div style="float: left; width: 33.33%; text-align: center;">
                <div class="logo">
                    <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;"
                        alt="logo">
                </div>
            </div>
            <div style="float: left; width: 33.33%; text-align: center;">
                <p style="font-family:Curlz MT; font-size:2rem; text-align: center; font-weight: 800;">The Lynx School
                </p>
            </div>
            <div style="float: left; width: 33.33%; text-align: center;"></div>
        </div><br><br><br>
        <div class="d-flex justify-content-end">Date: {{date('d M Y')}}</div><br>
        <div class="d-flex justify-content-start"><b>Branch :</b>
            {{ $branches[request()->get('branches')] ?? '-----------' }}
        </div><br>
        <div class="d-flex justify-content-start"><b>Subject:</b> <b class="w-100 d-flex justify-content-center"
                style="text-decoration: underline;"> WITHDRAWAL NOTICE </b></div><br>
        <div class="">

            <b>Dear Parents,</b><br>
            It is with great regret to inform you that we are no longer able to provide our services to your child<span
                style="text-decoration:underline;">&nbsp;&nbsp;&nbsp;&nbsp;
                @if(isset($_GET['students']))
                {{ isset($students[$_GET['students']]) ? $students[$_GET['students']] : '' }}
                @else
                {{ '--------------------' }}
                @endif
                &nbsp;&nbsp;&nbsp;&nbsp;</span>due to
            the unpaid fee for the month's <span>
                @php
                $feeMonths = [];
                $totalAmount = 0;
                @endphp

                @foreach(@$groupedChallans as $studentChallans)
                @foreach(@$studentChallans as $challan)
                @foreach(@$challan as $chall)
                @if(isset($chall->fee_month))
                @php
                $feeMonths[] = $chall->fee_month;
                @endphp
                @endif
                @if(isset($chall->total))
                @php
                $totalAmount += $chall->total;
                @endphp
                @endif
                @endforeach
                @endforeach
                @endforeach
                @php
                $feeMonths = array_unique($feeMonths);
                sort($feeMonths);
                @endphp

                <span style="text-decoration:underline;">
                    @if($feeMonths)
                    @foreach($feeMonths as $feeMonth)
                    ({{ date('M y', strtotime($feeMonth ?? '')) }})
                    @endforeach
                    @else
                    -------------------
                    @endif
                </span> of accumulating to a total of Rs.<span
                    style="text-decoration:underline;">&nbsp;&nbsp;&nbsp;{{ $totalAmount }}&nbsp;&nbsp;&nbsp;</span>

                If you would like to further avail the facilities at The Lynx School, I would request that you clear
                all
                pending arrears in the bank within
                <span style="text-decoration:underline;">&nbsp;&nbsp;30&nbsp;&nbsp;</span>day(s) and provide the paid
                challan copy to the school office immediately. <br><br> <b> I request that you do not send your child to
                    school until all outstanding accounts have been deemed clear.</b><br>
                <br>Yours sincerely,<br><br><br><br><br>
                <br> Headmistress <br>
                <hr style="height:2px; color:black;">
                <div class="d-flex justify-content-center"><b style="text-decoration: underline;"> WITHDRAWAL NOTICE
                    </b>
                </div>
                <br>
                <div class="d-flex justify-content-start"><b style="text-decoration: underline;">Office Copy </b></div>
                <br>
                <div class="d-flex justify-content-start">Date: {{date('d M Y')}}</div><br>
                <div>
                    <div class="d-flex justify-content-start">
                        @php
                        $selectedStudentId = $_GET['students'] ?? null;
                        $studentDetails = null;
                        if ($selectedStudentId) {
                        $studentDetails = App\Models\StudentRegistration::with('class', 'branches',
                        'enrollment')->find($selectedStudentId);
                        }
                        @endphp
                        <div class="">

                            Name:
                            <span
                                style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{@$studentDetails->stdname}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </div>
                        <div class="">

                            Class:
                            <span
                                style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{@$studentDetails->class->name}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </div>
                        <div class="">

                            Roll #
                            <span
                                style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{@$studentDetails->enrollment->enrollId}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </div>
                    </div><br>
                    <div class="d-flex justify-content-start">
                        <div class="">
                            Billing Month:
                            <span
                                style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                @foreach($feeMonths as $feeMonth)
                                ({{ date('M y', strtotime($feeMonth ?? '')) }})
                                @endforeach
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </div>
                        <div class="">
                            Amount
                            <span
                                style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$totalAmount}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </div>
                    </div><br>
                    Withdrawal notice copy should be file in student file
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
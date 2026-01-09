@extends('layouts.admin')
@section('page-title')
{{__('Student Defaulter SM')}}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>

<script>
function generatePDF() {
    console.log('generating');
    const element = document.getElementById('studentdefaulter');
    const opt = {
        filename: 'studentdefaulter-report(sw).pdf',
        html2canvas: {
            scale: 1
        },
        jsPDF: {
            unit: 'pt',
            format: [700, 900],
            orientation: 'landscape'
        }
    };
    html2pdf().from(element).set(opt).save();
}

function printPDF() {
    console.log('printing');
    const element = document.getElementById('studentdefaulter');
    const opt = {
        filename: 'studentdefaulter-report(sw).pdf',
        html2canvas: {
            scale: 1
        },
        jsPDF: {
            unit: 'pt',
            format: [700, 900],
            orientation: 'landscape'
        }
    };
    html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
        window.open(pdf);
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
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Student Defaulter SM')}}</li>
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
                    {{ Form::open(['route' => ['student_defaulter_sm'], 'method' => 'GET', 'id' => 'student-defaulter']) }}
                    <div class="row d-flex justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                {{ Form::date('from_date', request()->get('from_date') ?? date('Y-m-01'), ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                {{ Form::date('to_date', request()->get('to_date') ?? date('Y-m-d'), ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                {{ Form::select('class', $class, request()->get('class'), ['class' => 'form-control select', 'id' => 'class_select']) }}
                            </div>
                        </div>
                        <div
                            class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex gap-4 justify-content-end">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                onclick="document.getElementById('student-defaulter').submit(); return false;"
                                 title="" title="Search">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span
                                    class="btn-inner--icon">Print</span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card mt-2 p-4" id="studentdefaulter">
    <div class="mt-4" style="margin: 0 auto; padding: 30px;">
        <div style="width: 100%; text-align: center;">
        <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School  </b></p>
        <p style="font-size:1rem;"><b>Defaulter Students (Student Wise)</b></p>
        </div>
        <div class="d-flex" style="display:flex; justify-content:space-between; width:100%">
            <p><b>Period From: </b>{{ request()->get('from_date') ?? date('Y-m-01') }}</p>
            <p><b>Branch: </b>{{ $branches[request()->get('branches')] ?? 'All Branches' }}</p>
            <p><b>Period To: </b>{{ request()->get('to_date') ?? date('Y-m-d') }}</p>
        </div>
        <div style="width: 100%;">
            <table class="table">
                <thead>
                    <tr class="table_heads">
                        <th>{{__('Sr No.')}}</th>
                        <th>{{__('B Sr No.')}}</th>
                        <th>{{__('Student Name')}}</th>
                        <th>{{__('Roll No')}}</th>
                        <th>{{__('Class')}}</th>
                        <th>{{__('Phone No')}}</th>
                        @foreach ($months as $month)
                        <th>{{ $month }}</th>
                        @endforeach
                        <th>{{__('Total Amount')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(@$groupedChallans as $studentChallans)
                    @foreach(@$studentChallans as $challan)
                    @foreach(@$challan as $chall)
                    <tr>
                        <td>{{ $loop->parent->iteration }}</td>
                        <td>{{ @$chall->student->branch }}</td>
                        <td>{{ @$chall->student->stdname }}</td>
                        <td>{{ @$chall->enrollstudent->enrollId }}</td>
                        <td>{{ @$chall->class->name }}</td>
                        <td>{{ @$chall->student->fatherphone }}</td>
                        @php $total= 0; @endphp
                        @foreach ($months as $month)
                        @php
                        $month = \Carbon\Carbon::parse($chall->fee_month)->format('Y-m');
                        $monthData = $chall->firstWhere('fee_month', $month);
                        $total += @$monthData->total_amount;
                        @endphp
                        <td>{{ @$monthData ? @$monthData->total_amount : '' }}</td>

                        @endforeach
                        <td>{{ $total }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
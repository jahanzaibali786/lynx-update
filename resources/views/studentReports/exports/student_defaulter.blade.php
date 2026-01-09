@extends('layouts.admin')
@section('page-title')
{{__('Student Defaulter')}}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>

<script>
    $(document).on('change', '#branch', function () {
            var branch = $(this).val();
            $.ajax({
                url: '{{ route('branch.class') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#class_select').empty();
                    $('#class_select').append('<option value="all" selected >{{ __('All Class') }}</option>');
                    for (let index = 0; index < data.length; index++) {
                        $('#class_select').append('<option value="' + data[index]['id'] + '">' + data[index]['name'] + '</option>');
                    }
                    var s = `{{ Form::label('student_id', __('Student'),['class'=>'form-label']) }}
                            <select id="class_students" name="student_id" class="form-control select" required="required">
                                <option value="" selected disabled>{{ __('Select Student') }}</option> </select>`;
                    $('.std_data').empty().html(s);
                }
            });
        });
// function generatePDF() {
//     console.log('generating');
//     const element = document.getElementById('studentdefaulter');
//     const opt = {
//         filename: 'studentdefaulter-report.pdf',
//         html2canvas: {
//             scale: 1
//         },
//         jsPDF: {
//             unit: 'pt',
//             format: [700, 900],
//             orientation: 'portrait'
//         }
//     };
//     html2pdf().from(element).set(opt).save();
// }

// function printPDF() {
//     console.log('printing');
//     const element = document.getElementById('studentdefaulter');
//     const opt = {
//         filename: 'studentdefaulter-report.pdf',
//         html2canvas: {
//             scale: 1
//         },
//         jsPDF: {
//             unit: 'pt',
//             format: [700, 900],
//             orientation: 'portrait'
//         }
//     };
//     html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
//         window.open(pdf);
//     });
// }

function generatePDF() {
        var form = document.getElementById('student-defaulter');
        var formData = new FormData(form);
        var queryString = new URLSearchParams(formData).toString();

        $.ajax({
            url: "{{ route('student-defaultert.report') }}?" + queryString,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                const base64Pdf = response.base64Pdf;
                const byteCharacters = atob(base64Pdf);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);
                window.open(blobUrl, '_blank');
            },
            error: function (xhr) {
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
    document.getElementById("fromDate").addEventListener("change", function() {
        const fromDate = new Date(this.value);

        if (fromDate) {
            const minDate = new Date(fromDate);
            minDate.setFullYear(fromDate.getFullYear());

            const maxDate = new Date(fromDate);
            maxDate.setFullYear(fromDate.getFullYear() + 1); // 1 year after

            // Format dates to YYYY-MM-DD for the `input` field
            const minDateString = minDate.toISOString().split("T")[0];
            const maxDateString = maxDate.toISOString().split("T")[0];

            document.getElementById("toDate").value = minDateString;

            // Set the min and max attributes for the toDate input
            const toDateInput = document.getElementById("toDate");
            toDateInput.min = minDateString;
            toDateInput.max = maxDateString;
        }
    });
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Student Defaulter')}}</li>
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
                    {{ Form::open(['route' => ['student_defaulter'], 'method' => 'GET', 'id' => 'student-defaulter']) }}
                    <div class="row d-flex justify-content-end" style="width: 100%">
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('date_from', __('From Date'), ['class' => 'form-label']) }}
                                {{ Form::date('date_from', isset($_GET['date_from'])?$_GET['date_from']:'', ['class' => 'form-control','id' => 'fromDate']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('date_to', __('To Date'), ['class' => 'form-label']) }}
                                {{ Form::date('date_to', isset($_GET['date_to'])?$_GET['date_to']:'', ['class' => 'form-control','id' => 'toDate']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'id' => 'branch']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                {{ Form::select('class', @$class, isset($_GET['class']) ? $_GET['class'] : '' , ['class' => 'form-control select', 'id' => 'class_select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex gap-2">
                            <a href="#" class="btn btn-sm btn-outline-primary"
                                onclick="document.getElementById('student-defaulter').submit(); return false;"
                                 data-bs-title="Search">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            {{-- <a class="btn btn-sm btn-outline-success" onclick="generatePDF()"><span class="btn-inner--icon">Print</span>
                            </a> --}}
                            {{-- <a href="#" onclick="generatePDF(); return false;" class="btn btn-sm btn-outline-success"
                                     title="" title="Print">
                                    <span class="btn-inner--icon">Print
                                    </span>
                                </a> --}}
                            <button class="btn btn-sm btn-outline-success" type="submit" name="print" value="pdf"  data-bs-title="Print" ><span class="btn-inner--icon">PDF / Print</span></button>
                            <button class="btn btn-sm btn-outline-success" type="submit" name="export" value="excel"  data-bs-title="Export" ><span class="btn-inner--icon">Export</span></button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card mt-2 p-4" id="studentdefaulter">
    <div class="mt-4">
        <div style="width: 100%; text-align: center;">
        <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School</b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Student Defaulter Report</p>
        </div>
        <div class="d-flex" style="display:flex; justify-content:space-between; width:100%">
            <p><b>Period From: </b>{{ request()->get('date_from') ?? date('Y-m-d') }}</p>
            <p><b>Branch: </b>{{ $branches[request()->get('branches')] ?? 'All Branches' }}</p>
            <p><b>Period To: </b>{{ request()->get('date_to') ?? date('Y-m-d') }}</p>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr class="">
                        <th rowspan="2">{{__('Sr No.')}}</th>
                        <th rowspan="2">{{__('B Sr No.')}}</th>
                        <th rowspan="2">{{__('Roll No')}}</th>
                        <th rowspan="2">{{__('Student Name')}}</th>
                        <th rowspan="2">{{__('Reg Type')}}</th>
                        <th rowspan="2">{{__('Class')}}</th>
                        <th rowspan="2">{{__('Phone No')}}</th>
                        <th rowspan="2">{{__('Arrears')}}</th>
                        @php
                            // Step 1: Group months by year and count the months for each year
                            $yearMonthCounts = [];
                            foreach ($monthsArray as $monthYear) {
                                [$month, $year] = explode('-', $monthYear);
                                if (!isset($yearMonthCounts[$year])) {
                                    $yearMonthCounts[$year] = 0;
                                }
                                $yearMonthCounts[$year]++;
                                $yearMonths[] = $month;
                            }
                            $i=1;
                                $grandMonthlyTotals = array_fill(0, count($monthsArray), 0); // Initialize grand total for each month
                                $grandTotal = 0; // Initialize overall grand total
                        @endphp
                        @foreach(@$yearMonthCounts as $ak => $year)
                            <th colspan="{{$year}}" style="text-align: center;">{{ $ak }}</th>
                        @endforeach
                        <th rowspan="2">{{__('Total Amount')}}</th>
                    </tr>
                    <tr style="background-color: #100773; color:#fff !important;">
                        @foreach(@$yearMonths as $month)
                            <th colspan="" style="background-color: #100773; color:#fff !important; border-radius:0px !important;">{{ date('M', mktime(0, 0, 0, (int)$month, 1)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach(@$reportData as $data)
                    @if($data['challans']->count() < 1)
                        @continue
                    @endif
                    <tr style="background:  #a9a9a9;">
                        <td colspan="{{count($monthsArray) + 9}}" >{{  $data['branch'] }}</td>
                    </tr>
                    @php
                        $branchMonthlyTotals = array_fill(0, count($monthsArray), 0); // Initialize branch total for each month
                        $branchTotal = 0; // Initialize branch overall total
                        $brsr = 1;
                    @endphp
                    @foreach(@$data['challans'] as $index => $challan)
                    @foreach(@$challan as $chall)
                    @php
                        $studentTotal = 0; // Initialize student total
                    @endphp
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $brsr++ }}</td>
                        <td>{{ @$chall->student->roll_no }}</td>
                        <td>{{ @$chall->student->stdname }}</td>
                        <td>{{ @$chall->student->registeroption->name }}</td>
                        <td>{{ @$chall->class->name }}</td>
                        <td>{{ @$chall->student->fatherphone }}</td>
                        <td>0</td>
                        @foreach ($monthsArray as $l => $monthYear)
                            @php
                                [$month, $year] = explode('-', $monthYear);
                                $formattedDate = date('Y-m-01', strtotime("$year-$month-01"));
                                $specificdata = collect($challan)->firstWhere('fee_month', $formattedDate);
                                $price = $specificdata ? $specificdata->total_amount - ($specificdata->paid_amount + $specificdata->concession_amount) : 0;
                                $studentTotal += $price;
                                // dd($branchMonthlyTotals[$loop->parent->iteration]);
                                $branchMonthlyTotals[$l] += $price;
                                $grandMonthlyTotals[$l] += $price;
                            @endphp
                        <td>{{ $price }}</td>
                        @endforeach
                        <td>{{ $studentTotal }}</td>
                    </tr>
                    @php
                        $branchTotal += $studentTotal; // Add student total to branch total
                    @endphp
                    {{-- @dd($branchMonthlyTotals,$grandMonthlyTotals) --}}
                    @break
                    @endforeach
                     <!-- Branch Total Row -->
                    @endforeach
                    <tr style="background: #dcdcdc; font-weight: bold;">
                        <td colspan="8">Branch Total</td>
                        @foreach ($branchMonthlyTotals as $monthlyTotal)
                            <td>{{ $monthlyTotal }}</td>
                        @endforeach
                        <td>{{ $branchTotal }}</td>
                    </tr>

                    @php
                        $grandTotal += $branchTotal; // Add branch total to grand total
                    @endphp
                    @endforeach
                    <tr style="background: #cccccc; font-weight: bold;">
                        <td colspan="8">Grand Total</td>
                        @foreach ($grandMonthlyTotals as $grandMonthlyTotal)
                            <td>{{ $grandMonthlyTotal }}</td>
                        @endforeach
                        <td>{{ $grandTotal }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

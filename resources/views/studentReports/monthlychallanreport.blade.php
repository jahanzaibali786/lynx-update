@extends('layouts.admin')
@section('page-title')
    {{ __('Regular Challan Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
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
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Regular Challan Report') }}</li>
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
                        {{ Form::open(['route' => ['monthlychallanreport'], 'method' => 'GET', 'id' => 'monthlychallanreport']) }}
                        <div class="row d-flex justify-content-start " style="width: 100%">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                                    {{ Form::select('session', $sessions, isset($_GET['session']) ? $_GET['session'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select custom-select', 'id' => 'branch']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', @$class, isset($_GET['class']) ? $_GET['class'] : '', ['class' => 'form-control select custom-select', 'id' => 'class_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box std_data">
                                    {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                                    {{ Form::select('student', @$students, isset($_GET['student']) ? $_GET['student'] : '', ['class' => 'form-control select custom-select', 'id' => 'student_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date', __('Billing Month'), ['class' => 'form-label']) }}
                                    {{ Form::month('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                                <!-- Search Button -->
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('monthlychallanreport').submit(); return false;"
                                     data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <!-- Actions Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" 
                                            id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <form method="post" style="display: inline;">
                                                <button class="dropdown-item" type="submit" name="export" value="excel">
                                                    <i class="ti ti-file me-2"></i>Excel
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="get" style="display: inline;">
                                                @csrf
                                                @method('GET')
                                                <input type="hidden" name="export" value="pdf">
                                                <button class="dropdown-item" type="submit" name="print" value="pdf">
                                                    <i class="ti ti-download me-2"></i>Pdf
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container card" style="display:flex;justify-content:center; align-items: center;">

        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School</b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Regular Challan Report</p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">
                {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
        </div>
        <div class=" table-responsive maximumHeightNew" style="width:100%;">
            {{-- <table border="1"> --}}
            @php
                $i = 1;
                $grandTotal = [
                    'previousUnpaid' => 0,
                    'totalAmount' => 0,
                    'concessionAmount' => 0,
                    'monthly_fee' => 0,
                ];
                $grandHeadTotals = [];
            @endphp
            <table class="datatable maximumHeightNew">
                <thead class="sticky-headerNew">
                <tr class="table_heads">
                    <th colspan="8"></th>
                    <th>Monthly Fee</th>
                    @foreach (@$heads as $head)
                        <th colspan="3" style="text-align: center">{{ @$head->fee_head }}</th>
                    @endforeach
                    <th colspan="2" style="text-align:center;">Current Month Bill</th>
                    <th colspan="2" style="text-align:center;">Discount</th>
                </tr>
                <tr class="table_heads">
                    <th>Sr.</th>
                    <th>B Sr.</th>
                    <th>Bill No</th>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Reg type</th>
                    <th>Class</th>
                    <th>Billing Month</th>
                    <th>Monthly Fee</th>
                    @foreach (@$heads as $head)
                        <th>Amount</th>
                        <th>Disc</th>
                        <th>Ch_Amt</th>
                    @endforeach
                    <th>Arrears</th>
                    <th>Net Receivable</th>
                    <th>Discount</th>
                    <th>Category</th>
                </tr>
                </thead>
                @foreach ($report as $a => $row)
                    <tr style="background: gray">
                        <td colspan="{{ $heads->count() * 3 + 13 }}">{{ $branches[$a] }}</td>
                    </tr>
                    @php
                        $branchTotal = [
                            'previousUnpaid' => 0,
                            'totalAmount' => 0,
                            'concessionAmount' => 0,
                            'monthly_fee' => 0,
                        ];
                        $branchHeadTotals = [];
                    @endphp
                    @foreach ($row as $index => $data)
                    {{-- @dd($data) --}}
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $data->challanNo }}</td>
                            <td>{{ @$data->student->roll_no }}</td>
                            <td>{{ @$data->student->stdname }}</td>
                            <td>{{ @$data->student->registeroption->name }}</td>
                            <td>{{ @$data->class->name }}</td>
                            <td>{{ date('M-Y', strtotime($data->fee_month)) }}</td>
                             @php
                                $monthly_fee = 0;
                                foreach ($heads as $head) {
                                    $specificHead = collect($data->heads)->firstWhere('head_id', $head->id);
                                    $monthly_fee += $specificHead && isset($specificHead->price) ? floatval($specificHead->price) : 0;
                                }
                                $branchTotal['monthly_fee'] += $monthly_fee;
                                $grandTotal['monthly_fee'] = isset($grandTotal['monthly_fee']) ? $grandTotal['monthly_fee'] + $monthly_fee : $monthly_fee;
                            @endphp
                            <td>{{ $monthly_fee }}</td>
                            @foreach (@$heads as $head)
                                {{-- @dd($head,$data->heads,$heads); --}}
                                @php
                                    $specificHead = collect($data->heads)->firstWhere('head_id', $head->id);

                                    $price =
                                        $specificHead && isset($specificHead->price)
                                            ? floatval($specificHead->price)
                                            : 0;
                                    $concession =
                                        $specificHead && isset($specificHead->concession)
                                            ? floatval($specificHead->concession)
                                            : 0;
                                    $netAmount = $price - $concession;

                                    // Initialize branch totals if not set
                                    if (!isset($branchHeadTotals[$head->id])) {
                                        $branchHeadTotals[$head->id] = [
                                            'price' => 0,
                                            'concession' => 0,
                                            'netAmount' => 0,
                                        ];
                                    }
                                    $branchHeadTotals[$head->id]['price'] += $price;
                                    $branchHeadTotals[$head->id]['concession'] += $concession;
                                    $branchHeadTotals[$head->id]['netAmount'] += $netAmount;

                                    // Initialize grand totals if not set
                                    if (!isset($grandHeadTotals[$head->id])) {
                                        $grandHeadTotals[$head->id] = [
                                            'price' => 0,
                                            'concession' => 0,
                                            'netAmount' => 0,
                                        ];
                                    }
                                    $grandHeadTotals[$head->id]['price'] += $price;
                                    $grandHeadTotals[$head->id]['concession'] += $concession;
                                    $grandHeadTotals[$head->id]['netAmount'] += $netAmount;
                                @endphp


                                <td style="text-align:center;">{{ $price }}</td>
                                <td style="text-align:center;">{{ $concession }}</td>
                                <td style="text-align:center;">{{ $netAmount }}</td>
                            @endforeach
                            @php
                                $i++;
                                $previousUnpaidChallans = App\Models\Challans::where('student_id', $data->student_id)
                                    ->where('status', '!=', 'Paid')
                                    ->wheredate('fee_month', '<', date('Y-m-d', strtotime($data->fee_month)))
                                    ->where('id', '!=', $data->id)
                                    ->sum(DB::raw('total_amount - concession_amount'));
                                $branchTotal['previousUnpaid'] += $previousUnpaidChallans ?? 0;
                                $branchTotal['totalAmount'] += $data->total_amount - $data->concession_amount;
                                $branchTotal['concessionAmount'] += $data->concession_amount;

                                // Update grand totals
                                $grandTotal['previousUnpaid'] += $previousUnpaidChallans ?? 0;
                                $grandTotal['totalAmount'] += $data->total_amount - $data->concession_amount;
                                $grandTotal['concessionAmount'] += $data->concession_amount;
                            @endphp
                            <td style="text-align:center;">{{ $previousUnpaidChallans ?? 0 }}</td>
                            <td style="text-align:center;">{{ $data->total_amount - $data->concession_amount }}</td>
                            <td style="text-align:center;">{{ $data->concession_amount }}</td>
                            <td>{{ @$data->concession->name ?? '' }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background-color: #dcdcdc;">
                        <td colspan="8">Branch Total</td>
                        <td style="text-align:center;">{{ $branchTotal['monthly_fee'] ?? 0 }}</td>
                        @foreach ($heads as $head)
                            <td style="text-align:center;">{{ $branchHeadTotals[$head->id]['price'] ?? 0 }}</td>
                            <td style="text-align:center;">{{ $branchHeadTotals[$head->id]['concession'] ?? 0 }}</td>
                            <td style="text-align:center;">{{ $branchHeadTotals[$head->id]['netAmount'] ?? 0 }}</td>
                        @endforeach
                        <td style="text-align:center;">{{ $branchTotal['previousUnpaid'] }}</td>
                        <td style="text-align:center;">{{ $branchTotal['totalAmount'] }}</td>
                        <td style="text-align:center;">{{ $branchTotal['concessionAmount'] }}</td>
                        <td colspan="1"></td>
                    </tr>
                @endforeach
                <tr style="font-weight: bold; background-color: #a9a9a9;">
                    <td colspan="8">Grand Total</td>
                    <td style="text-align:center;">{{ $grandTotal['monthly_fee'] ?? 0 }}</td>
                    @foreach ($heads as $head)
                        <td style="text-align:center;">{{ $grandHeadTotals[$head->id]['price'] ?? 0 }}</td>
                        <td style="text-align:center;">{{ $grandHeadTotals[$head->id]['concession'] ?? 0 }}</td>
                        <td style="text-align:center;">{{ $grandHeadTotals[$head->id]['netAmount'] ?? 0 }}</td>
                    @endforeach
                    <td style="text-align:center;">{{ $grandTotal['previousUnpaid'] }}</td>
                    <td style="text-align:center;">{{ $grandTotal['totalAmount'] }}</td>
                    <td style="text-align:center;">{{ $grandTotal['concessionAmount'] }}</td>
                    <td colspan="1"></td>
                </tr>
            </table>
        </div>
    </div>
@endsection

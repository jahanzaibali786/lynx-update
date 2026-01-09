@extends('layouts.admin')
@section('page-title')
    {{ __('Student Defaulter') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <script>
        $(document).on('change', '#branch', function() {
            var branch = $(this).val();
            $.ajax({
                url: '{{ route('branch.class') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#class_select').empty();
                    $('#class_select').append(
                        '<option value="all" selected >{{ __('All Class') }}</option>');
                    for (let index = 0; index < data.length; index++) {
                        $('#class_select').append('<option value="' + data[index]['id'] + '">' + data[
                            index]['name'] + '</option>');
                    }
                    var s = `{{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}
                            <select id="class_students" name="student_id" class="form-control select" required="required">
                                <option value="" selected disabled>{{ __('Select Student') }}</option> </select>`;
                    $('.std_data').empty().html(s);
                }
            });
        });

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
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Defaulter Report') }}</li>
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
                        <div class="row d-flex justify-content-start" style="width: 100%">
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date_from', __('From Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('date_from', isset($_GET['date_from']) ? $_GET['date_from'] : '', ['class' => 'form-control', 'id' => 'fromDate']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date_to', __('To Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('date_to', isset($_GET['date_to']) ? $_GET['date_to'] : '', ['class' => 'form-control', 'id' => 'toDate']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', [
                                        'class' => 'form-control custom-select',
                                        'id' => 'branch',
                                        'onchange' => 'branchcustomer(this.value)'
                                    ]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', @$class, isset($_GET['class']) ? $_GET['class'] : '', [
                                        'class' => 'form-control custom-select',
                                        'id' => 'class_select'
                                    ]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                                <!-- Search Button -->
                                <a href="#" class="btn btn-sm btn-outline-primary"
                                   onclick="document.getElementById('student-defaulter').submit(); return false;"
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
            </div>
        </div>
    </div>
    <div class="card p-4" id="studentdefaulter">
        <div class="">
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
            <div class="table-responsive maximumHeightNew">
                <table class="table">
                    <thead class="table_heads sticky-headerNew">
                        <tr class="table_heads">
                            <th rowspan="2">{{ __('Sr.No.') }}</th>
                            <th rowspan="2">{{ __('B Sr No.') }}</th>
                            <th rowspan="2">{{ __('Roll#.') }}</th>
                            <th rowspan="2">{{ __('Student') }}</th>
                            <th rowspan="2">{{ __('Reg.') }}</th>
                            <th rowspan="2">{{ __('Class') }}</th>
                            <th rowspan="2">{{ __('Phone No') }}</th>
                            <th rowspan="2">{{ __('Monthly Fee') }}</th>
                            <th rowspan="2">{{ __('Arrears') }}</th>
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
                                $i = 1;
                                $grandMonthlyFeeTotal = 0;
                                $grandArrearsTotal = 0;
                                $grandMonthlyTotals = array_fill(0, count($monthsArray), 0); // Initialize grand total for each month
                                $grandTotal = 0; // Initialize overall grand total
                            @endphp
                            @foreach (@$yearMonthCounts as $ak => $year)
                                <th colspan="{{ $year }}" style="text-align: center;">{{ $ak }}</th>
                            @endforeach
                            <th rowspan="2">{{ __('Total') }}</th>
                        </tr>
                        <tr class="tr" style="background-color: #100773; color:#fff !important;">
                            @foreach (@$yearMonths as $month)
                                <th colspan=""
                                    style="background-color: #100773; color:#fff !important; border-radius:0px !important;">
                                    {{ date('M', mktime(0, 0, 0, (int) $month, 1)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (@$reportData as $data)
                            @if ($data['challans']->count() < 1)
                                @continue
                            @endif
                            <tr class="trNew" style="background:  #a9a9a9;">
                                <td colspan="{{ count($monthsArray) + 10 }}">{{ $data['branch'] }}</td>
                            </tr>
                            @php
                                $branchMonthlyFeeTotal = 0;
                                $branchArrearsTotal = 0;
                                $branchMonthlyTotals = array_fill(0, count($monthsArray), 0); // Initialize branch total for each month
                                $branchTotal = 0; // Initialize branch overall total
                                $brsr = 1;
                            @endphp
                            @foreach (@$data['challans'] as $index => $challan)
                                @foreach (@$challan as $chall)
                                    @php
                                        $studentTotal = 0; // Initialize student total
                                        $branchMonthlyFeeTotal += $chall->monthly_fee;
                                        $branchArrearsTotal += $chall->arrears;
                                    @endphp
                                    <tr class="trNew">
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $brsr++ }}</td>
                                        <td>{{ @$chall->student->roll_no }}</td>
                                        <td>{{ @$chall->student->stdname }}</td>
                                        <td>{{ @$chall->student->registeroption->name }}</td>
                                        <td>{{ @$chall->class->name }}</td>
                                        <td>{{ @$chall->student->fatherphone }}</td>
                                        <td>{{ @$chall->monthly_fee ?? 0 }}</td>
                                        <td>{{ @$chall->arrears ?? 0 }}</td>
                                        @foreach ($monthsArray as $l => $monthYear)
                                            @php
                                                [$month, $year] = explode('-', $monthYear);
                                                $formattedDate = date('Y-m-01', strtotime("$year-$month-01"));
                                                $specificdata = collect($challan)->firstWhere(
                                                    'fee_month',
                                                    $formattedDate,
                                                );
                                                $price = $specificdata
                                                    ? $specificdata->total_amount -
                                                        ($specificdata->paid_amount + $specificdata->concession_amount)
                                                    : 0;
                                                $studentTotal += $price;
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
                                @break
                            @endforeach
                        @endforeach
                        <!-- Branch Total Row -->
                        <tr class="trNew" style="background: #dcdcdc; font-weight: bold;">
                            <td colspan="7">Branch Total</td>
                            <td>{{ $branchMonthlyFeeTotal }}</td>
                            <td>{{ $branchArrearsTotal }}</td>
                            @foreach ($branchMonthlyTotals as $monthlyTotal)
                                <td>{{ $monthlyTotal }}</td>
                            @endforeach
                            <td>{{ $branchTotal }}</td>
                        </tr>

                        @php
                            $grandMonthlyFeeTotal += $branchMonthlyFeeTotal;
                            $grandArrearsTotal += $branchArrearsTotal;
                            $grandTotal += $branchTotal; // Add branch total to grand total
                        @endphp
                    @endforeach
                    <tr class="trNew" style="background: #cccccc; font-weight: bold;">
                        <td colspan="7">Grand Total</td>
                        <td>{{ $grandMonthlyFeeTotal }}</td>
                        <td>{{ $grandArrearsTotal }}</td>
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

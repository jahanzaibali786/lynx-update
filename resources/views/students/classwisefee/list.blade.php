@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Fee Structure') }}
@endsection

@push('script-page')
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
                        $('#class_select').empty().append($('<option>', {
                            value: '',
                            text: 'Select Class'
                        }));
                        result.class.forEach(function(cls) {
                            $('#class_select').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        });
                    } else if (result.status == 'error') {
                        $('#class_select').empty().append($('<option>', {
                            value: '',
                            text: 'No Classes Available'
                        }));
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    $('#class_select').empty().append($('<option>', {
                        value: '',
                        text: 'Error fetching classes'
                    }));
                }
            });
        }

        function printReport() {
            var form = document.getElementById('class_wise_fee_list');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('fee_structure.report') }}?" + queryString,
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


        $(document).ready(function() {
            if ($(".sec").length > 0) {
                $($(".sec")).each(function(index, element) {
                    var id = $(element).attr('id');
                    var multipleCancelButton = new Choices(
                        '#' + id, {
                            removeItemButton: true,
                        }
                    );
                });
            }
        });
        $(document).ready(function() {
            if ($(".session").length > 0) {
                $($(".session")).each(function(index, element) {
                    var id = $(element).attr('id');
                    var multipleCancelButton = new Choices(
                        '#' + id, {
                            removeItemButton: true,
                        }
                    );
                });
            }
        });
    </script>
    <script>
        function exportToExcel() {
            var form = document.getElementById('class_wise_fee_list');
            var formData = new FormData(form);

            // Convert form data to URL search params
            var params = new URLSearchParams();
            for (var pair of formData.entries()) {
                // Handle array inputs (like branches[] or session[])
                if (pair[0].endsWith('[]')) {
                    formData.getAll(pair[0]).forEach(val => params.append(pair[0], val));
                } else {
                    params.append(pair[0], pair[1]);
                }
            }

            // Add export parameter
            params.append('export', 'excel');

            // Redirect to the route with all parameters
            window.location.href = "{{ route('feestructurelisting.export') }}?" + params.toString();
        }
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Fee Structure') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <!-- Optional buttons can go here -->
    </div>
@endsection

@section('content')
    @if (\Auth::user()->type == 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['feestructurelisting'], 'method' => 'GET', 'id' => 'class_wise_fee_list']) }}
                            <div class="row d-flex justify-content-end">
                                <!-- Session Select -->
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                                        {{-- {{ Form::select('session', $session, request()->get('session'), ['class' => 'form-control select', 'id' => 'sessionselect', 'required' => true]) }} --}}
                                        <select name="session[]" class="form-control select session" id="session"
                                            multiple="multiple">
                                            <option value="" disabled>Select Session...</option>
                                            @foreach ($session as $key => $sess)
                                                <option
                                                    value="{{ $key }}"{{ in_array($key, old('session', request()->get('session', []))) ? 'selected' : '' }}>
                                                    {{ $sess }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!-- Branch Select -->
                                {{-- @dd($branches) --}}
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{-- <select name="branches[]" class="form-control select sec" id="section" multiple="multiple">
                                            <option value="" disabled>Select Branches...</option>
                                            @foreach ($branches as $key => $branche)
                                                <option value="{{$key}}">{{$branche}}</option>
                                            @endforeach
                                        </select> --}}
                                        <select name="branches[]" class="form-control select sec" id="section"
                                            multiple="multiple">
                                            <option value="" disabled>Select Branches...</option>
                                            @foreach ($branches as $key => $branch)
                                                <option value="{{ $key }}"
                                                    {{ in_array($key, old('branches', request()->get('branches', []))) ? 'selected' : '' }}>
                                                    {{ $branch }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!-- Action Buttons -->
                                <div class="mt-4 d-flex justify-content-end gap-2 align-items-center">
                                    <!-- Search Button -->
                                    <button type="submit" class="btn mx-1 btn-sm btn-outline-primary"
                                        data-bs-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </button>
                                    <a href="{{ route('class_wise_fee.index') }}"
                                        class="btn mx-1 btn-sm btn-outline-danger" data-bs-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon">Clear</span>
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
                                                    <a href="#" class="dropdown-item"
                                                        onclick="exportToExcel(); return false;"
                                                        data-bs-title="{{ __('Export Report') }}">
                                                        <i class="ti ti-file me-2"></i>Excel
                                                    </a>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="get" action="{{ route('fee_structure.report') }}" target="_blank" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="print" value="pdf">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="ti ti-download me-2"></i>Pdf
                                                    </button>
                                                </form>
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
    @endif

    {{-- @if ($class_wise_fee && count($class_wise_fee) > 0)
        @php $i = 1; @endphp
        @foreach ($class_wise_fee as $branchId => $students)
            <div style="width: 100%; margin-top: 20px;">
                <span style="font-size:1rem; font-weight:600; padding:10px;">
                    {{ $branches[$branchId] ?? 'All Branches' }} 
                </span>
                <table style="width:100%; font-size:0.9rem;">
                    <thead >
                        <tr class="table_heads" style="background-color:grey; font-size:0.6rem;">
                            <th style="width:5%;">{{ __('Sr No') }}</th>
                            <th style="width:10%;">{{ __('Class') }}</th>
                            @foreach ($heads as $head)
                                <th style="width:10%;">{{ $head->fee_head }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students->groupBy('class.name') as $className => $groupedStudents)
                            <tr class="tr" style="font-size:0.8rem;">
                                <td>{{ $i }}</td>
                                <td>{{ $className }}</td>
                                @foreach ($heads as $head)
                                    @php
                                        $amount = optional($groupedStudents->where('feehead.fee_head', $head->fee_head)->first())->amount ?? 'N/A';
                                    @endphp
                                    <td>{{ $amount }}</td>
                                @endforeach
                            </tr>
                            @php $i++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif --}}
    {{-- @dd($class_wise_fee) --}}
    @if ($class_wise_fee && count($class_wise_fee) > 0)
        @php $i = 1; @endphp
        @foreach ($class_wise_fee as $branchId => $students)
            <div style="width: 100%; margin-top: 20px;">
                {{-- <span style="font-size:1rem; font-weight:600; padding:10px;">
                {{ $branches[$branchId] ?? 'All Branches' }} 
            </span> --}}
                {{-- @dd($students) --}}
                <table style="width:100%; font-size:0.9rem;">
                    <thead>
                        <tr class="table_heads" style="background-color:grey; font-size:0.6rem;">
                            <th style="width:5%;">{{ __('Sr No') }}</th>
                            <th style="width:10%;">{{ __('Class') }}</th>
                            @foreach ($heads as $head)
                                <th style="width:10%;">{{ $head->fee_head }}</th>
                            @endforeach
                        </tr>
                        <tr class="tr" style="font-size:2rem; font-weight:600; padding:10px; background: #dcdcdc;">
                            <td colspan="{{ count($heads) + 2 }}">
                                <strong>{{ $branches[$branchId] ?? 'All Branches' }}</strong> </td>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $session => $value)
                            {{-- <tr  style="font-size:0.8rem;">
                        <td colspan="{{count($heads) + 2}}" > <strong>{{ @$value[0]->session->year }}</strong> </td>
                        
                    </tr> --}}
                            @foreach ($value->groupBy('class.name') as $className => $groupedStudents)
                                <tr class="tr" style="font-size:0.8rem;">
                                    <td>{{ $i }}</td>
                                    <td>{{ $className }}</td>
                                    @foreach ($heads as $head)
                                        @php
                                            $amount =
                                                optional(
                                                    $groupedStudents
                                                        ->where('feehead.fee_head', $head->fee_head)
                                                        ->first(),
                                                )->amount ?? '0';
                                        @endphp
                                        <td>{{ $amount }}</td>
                                    @endforeach
                                </tr>
                                @php $i++; @endphp
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

@endsection

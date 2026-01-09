<!-- resources/views/studentReports/student_WISE_statistic_report.blade.php -->

@extends('layouts.admin')
@section('page-title')
    {{ __('Student Wise Statistic Report') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        function generatePDF() {
            var form = document.getElementById('student_wise_statistic_report');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('student_wise_statistic.report') }}?" + queryString,
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
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Wise Statistic Report') }}</li>
@endsection

@section('action-btn')
    <div class="float-end"></div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['student_wise_statistic_report'], 'method' => 'GET', 'id' => 'student_wise_statistic_report']) }}
                        <div class="row d-flex justify-content-start">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('date', $Date, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select', 'id' => 'branch_select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, request()->get('class'), ['class' => 'form-control select custom-select', 'id' => 'class_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('student_wise_statistic_report').submit(); return false;"
                                     data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="#" onclick="generatePDF(); return false;"
                                    class="btn mx-1 btn-sm btn-outline-success"  data-bs-title="Print">
                                    <span class="btn-inner--icon">Print
                                    </span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card p-4">
        <div style="width: 100%; text-align: center;">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b>
            </p>
            <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> </b></p>
            <p style="text-align:center; font-weight:600; font-size:1rem;">
                {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
                <p style="text-align:center; font-weight:900; font-size:1rem;">Student Wise Statistic Report</p>
        </div>
        <div class="" style="width: 100%; display: flex; justify-content: space-between;">
            <p>
                <b>Period From: </b>{{ date('d M Y', strtotime($request->input('date'))) }}
            </p>
            <p>
                {{-- <b>Period To: </b>{{ date('d M Y', strtotime($request->input('date_to'))) }} --}}
            </p>
        </div>
        <table class="datatable maximumHeightNew">
            <thead class="sticky-headerNew table_heads report_table">
                <tr>
                    <th>Branch</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Student</th>
                    <th>Section Change Date</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $prevBranch = null;
                    $prevClass = null;
                    $prevSection = null;
                @endphp
                @forelse ($all_data as $data)
                    <tr>
                        <td>
                            @if ($data->student->branches->name !== $prevBranch)
                                {{ $prevBranch = $data->student->branches->name }}
                            @else
                                //
                            @endif
                        </td>
                        <td>
                            @if ($data->student->class->name !== $prevClass)
                                {{ $prevClass = $data->student->class->name }}
                            @else
                                //
                            @endif
                        </td>
                        <td>
                            @if ($data->sectionto->name !== $prevSection)
                                {{ $prevSection = $data->sectionto->name }}
                            @else
                                //
                            @endif
                        </td>
                        <td>{{ $data->student->stdname }}</td>
                        <td>{{ $data->transfer_date }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

<!-- resources/views/studentReports/student_WISE_statistic_report.blade.php -->

@extends('layouts.admin')
@section('page-title')
    {{ __('Student Wise Statistic Report') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function generatePDF() {
            const element = document.getElementById('studentfeereceipt');
            const opt = {
                filename: 'student_wise_statistic_report.pdf',
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
            const element = document.getElementById('studentfeereceipt');
            const opt = {
                filename: 'student_wise_statistic_report.pdf',
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
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('date', $Date, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, request()->get('class'), ['class' => 'form-control select', 'id' => 'class_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('student_wise_statistic_report').submit(); return false;"
                                     data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                {{-- <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span
                                    class="btn-inner--icon">Print</span>
                            </a> --}}
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
        </div>
        <div class="" style="width: 100%; display: flex; justify-content: space-between;">
            <p>
                <b>Period From: </b>{{ date('d M Y', strtotime($request->input('date'))) }}
            </p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">Student Wise Statistic Report</p>
            <p>
                {{-- <b>Period To: </b>{{ date('d M Y', strtotime($request->input('date_to'))) }} --}}
            </p>
        </div>
        <table class="datatable">
            <thead class="table_heads report_table">
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

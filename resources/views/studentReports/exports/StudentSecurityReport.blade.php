@extends('layouts.admin')
@section('page-title')
    {{ __('Student Security Report') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>

    <script>
        // function generatePDF() {
        //     console.log('generating');
        //     const element = document.getElementById('registrationcont');
        //     const opt = {
        //         filename: 'student_security_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700, 1000],
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).save();
        // }

        // function printPDF() {
        //     console.log('printing');
        //     const element = document.getElementById('registrationcont');
        //     const opt = {
        //         filename: 'student_security_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700, 1000],
        //             orientation: 'portrait'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).output('bloburl').then(function (pdf) {
        //         window.open(pdf);
        //     });
        // }

        function generatePDF() {
            var form = document.getElementById('student_security_report');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('student_security_report.report') }}?" + queryString,
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
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Security Report') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
    </div>
@endsection

@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => 'student_security_report', 'method' => 'GET', 'id' => 'student_security_report']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_from', __('From'), ['class' => 'form-label']) }}
                        {{ Form::date('date_from', request('date_from'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_to', __('To'), ['class' => 'form-label']) }}
                        {{ Form::date('date_to', request('date_to'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', @$classes, request('class'), ['class' => 'form-control select', 'id' => 'class_select', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('status', __('Student Status'), ['class' => 'form-label']) }}
                        {{ Form::select('status', ['all' => 'All', 'active' => 'Active', 'withdraw' => 'Withdraw'], request()->get('status', 'all'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-2">
                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                        onclick="document.getElementById('student_security_report').submit(); return false;"
                        data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                    {{-- <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span class="btn-inner--icon"><i
                            class="fas fa-print"></i></span></a> --}}
                    <a href="#" onclick="generatePDF(); return false;" class="btn mx-1 btn-sm btn-outline-success"
                        data-bs-title="Print">
                        <span class="btn-inner--icon">Print
                        </span>
                    </a>
                    {{-- export button --}}
                    <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export" value="excel"  title="Export" data-bs-title="Export">
                        <span class="btn-inner--icon">Export</span>
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <div id="registrationcont">
        <div class="card mt-2 p-4">
            <div class="mt-4" style="margin: 0 auto; padding: 30px;">
                <div style="width: 100%; text-align: center;">
                    <p style="font-family: Edwardian Script ITC; font-size: 3.5rem; text-align: center;"><b>The Lynx School</b></p>
                    <h4><b>{{ @$branches[request('branch')] }}</b></h4>
                    <h4><b>Student Security Report</b></h4>
                </div>
            </div>
            <table class="">
                <thead>
                    <tr class="table_heads" style="font-weight:400; font-size:0.8rem;">
                        <th>{{ __('Sr No.') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Father Name') }}</th>
                        <th>{{ __('Roll No #') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Admission Date') }}</th>
                        <th>{{ __('WithDrawal Date') }}</th>
                        <th>{{ __('Security deposit') }}</th>
                        <th>{{ __('Security adjusted') }}</th>
                        <th>{{ __('Balance') }}</th>
                        <th>{{ __('Security Paid') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        if (!isset($records) || (!is_array($records) && !is_object($records))) {
                            $records = [];
                        }
                    @endphp
                    @foreach (($records ?? []) as $data)
                        @php
                            $stats = ($journals ?? collect())->get($data->challan_id, ['deposit' => 0, 'paid' => 0]);
                            $securityDeposit = $stats['deposit'];
                            $securityPaid = $stats['paid'];
                            $balance = $securityDeposit - $securityPaid;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ @@$data->challan->student->stdname ?? '-' }}</td>
                            <td>{{ @$data->challan->student->fathername }}</td>
                            <td>{{ @$data->challan->enrollstudent->enrollId}}</td>
                            <td>{{ @$data->challan->student->class->name }}</td>
                            <td>{{ \Carbon\Carbon::parse(@$data->challan->enrollstudent->created_at)->format('d-M-Y') }}</td>
                            <td>{{\Carbon\Carbon::parse(@$data->challan->student->withdrawal->withdraw_date)->format('d-M-Y')}}</td>
                            <td>{{ number_format($securityDeposit, 2) }}</td>
                             <td></td>
                            <td>{{ number_format($balance, 2) }}</td>
                            <td>{{ number_format($securityPaid, 2) }}</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
@endsection

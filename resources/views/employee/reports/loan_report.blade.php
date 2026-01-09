@extends('layouts.admin')
@section('page-title')
    {{ __('Emp. Loan Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Loan Report') }}</li>
@endsection
{{-- @push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    <script>
        function printDompdfPDF() {
            var form = document.getElementById('empLoan');
            var formData = new FormData(form);
            var params = new URLSearchParams(formData);
            params.set('print', 'pdf');
            var url = form.action + '?' + params.toString();
            window.open(url, '_blank');
        }
    </script>
@endpush --}}
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{ Form::open(['route' => ['empLoan'], 'method' => 'GET', 'id' => 'empLoan']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control month-btn']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : now()->format('Y-m-d'), ['class' => 'form-control month-btn']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                onclick="document.getElementById('empLoan').submit(); return false;"
                                data-bs-title="{{ __('Apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('empLoan') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                data-bs-title="{{ __('Reset') }}">
                                <span class="btn-inner--icon">Clear</span>
                            </a>
                            <!-- Actions Dropdown -->
                            <div class="dropdown d-inline-block mx-1">
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
                                        <button class="dropdown-item" type="submit" name="export" value="pdf">
                                            <i class="ti ti-download me-2"></i>Pdf
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content" id="report-content">
        <div class="card p-4">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The
                    Lynx School</b></p>
            <p style="font-size: 1.5rem; text-align: center; margin-top:-20px"><b>Employee Loan
                    Report</b></p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">
                @isset($_GET['branches'])
                    {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
                @endisset
            </p>

            @if (!$loans->isEmpty())
                <table class="datatable">
                    <thead class="">
                        <tr class="table_heads">
                            <th>{{ __('Sr. No.') }}</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Loan Amount') }}</th>
                            <th>{{ __('Deduction start Date') }}</th>
                            <th>{{ __('End Date') }}</th>
                            <th>{{ __('Received Amount') }}</th>
                            <th>{{ __('charge amnt/mon') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td style='width:100px;'>
                                    <span>{{ !empty($loan->employee->name) ? $loan->employee->name : '' }}</span>
                                </td>
                                <td>{{ $loan->title }}</td>
                                <td>{{ @$loan->amount }}</td>
                                <td>{{ @$loan->from_pay_month }}</td>
                                <td>{{ @$loan->loan_ended }}</td>
                                <td>{{ @$loan->received_amount }}</td>
                                <td>{{ @$loan->per_month_amount }}</td>
                                <td>
                                    @if ($loan->status == 0)
                                        <span>{{ $loan->status == 0 ? 'Pending' : ($loan->status == 2 ? 'Rejected' : 'Approved') }}</span>
                                    @else
                                        <span>{{ $loan->status == 1 ? 'Approved' : ($loan->status == 2 ? 'Rejected' : 'Approved') }}</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="mt-2 text-center">
                    No Loan Data Found!
                </div>
            @endif
        </div>
    @endsection

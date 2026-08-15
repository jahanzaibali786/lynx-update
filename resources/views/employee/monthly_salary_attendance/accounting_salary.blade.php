@extends('layouts.admin')

@section('page-title')
    {{ __('Accounting Salary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Accounting Salary') }}</li>
@endsection

@push('css-page')
    <style>
        .accounting-salary-badges .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 30px;
            font-size: 12.5px;
            line-height: 1.2;
            padding: 7px 12px;
            font-weight: 600;
            border-radius: 999px;
            letter-spacing: 0;
        }

        .accounting-salary-table th,
        .accounting-salary-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .report-download-loader {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1099;
            pointer-events: none;
        }

        .report-download-loader.d-none {
            display: none !important;
        }

        .report-download-loader-box {
            min-width: 160px;
            padding: 12px 14px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.16);
            text-align: center;
        }

        .accounting-voucher-btn,
        .accounting-voucher-btn:hover,
        .accounting-voucher-btn:focus,
        .accounting-voucher-btn:active {
            color: #fff !important;
        }
    </style>
@endpush

@section('action-btn')
    <div class="float-end d-flex align-items-center gap-2">
        <select id="accountingSheetType" class="form-select" style="width: 170px;">
            <option value="" selected disabled>{{ __('Print Sheet') }}</option>
            <option value="salary">{{ __('Salary Sheet') }}</option>
            <option value="deduction">{{ __('Deduction Sheet') }}</option>
            <option value="paymode">{{ __('Paymode Sheet') }}</option>
            <option value="gross">{{ __('Gross Sheet') }}</option>
            <option value="advance">{{ __('Advance Sheet') }}</option>
            <option value="slip">{{ __('Salary Slip') }}</option>
        </select>
        <select id="accountingSheetAction" class="form-select" style="width: 170px;">
            <option value="" selected disabled>{{ __('Select Mode') }}</option>
            <option value="preview">{{ __('Print / Preview PDF') }}</option>
            <option value="excel">{{ __('Excel Sheet') }}</option>
        </select>
    </div>
@endsection

@section('content')
    @php
        $totalAmount = $salaries->sum('net_pay');
        $pendingApprovalCount = $salaries->filter(fn($salary) => trim(strtolower($salary->status ?? '')) === 'fwd_to_account')->count();
        $accountApprovedCount = $salaries->filter(fn($salary) => trim(strtolower($salary->status ?? '')) === 'account_approved')->count();
        $partialPaidCount = $salaries->filter(fn($salary) => trim(strtolower($salary->status ?? '')) === 'partial_paid')->count();
        $paidCount = $salaries->filter(fn($salary) => trim(strtolower($salary->status ?? '')) === 'paid')->count();
        $groupedSalaries = $salaries->groupBy(fn($salary) => optional(optional($salary->employee)->userbranch)->name ?: __('No Branch'));
        $emptyColspan = 12;
    @endphp
    <div id="accountingReportDownloadLoader" class="report-download-loader d-none" aria-live="polite" aria-busy="true">
        <div class="report-download-loader-box">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 fw-semibold" data-report-loader-text>{{ __('Preparing report...') }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['accounting.salary.index'], 'method' => 'GET', 'id' => 'accounting_salary_filter']) }}
                    <div class="row d-flex justify-content-start">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branchesList, request('branches'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                {{ Form::select('department_id', $departments, request('department_id', 'all'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                {{ Form::select('designation_id', $designations, request('designation_id', 'all'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {!! Form::label('paymode', __('Paymode'), ['class' => 'form-label']) !!}
                                {{ Form::select(
                                    'paymode',
                                    [
                                        '' => 'Select All',
                                        'Bank Deposit HBL' => 'Bank Deposit HBL',
                                        'Bank Deposit AF' => 'Bank Deposit AF',
                                        'Demand Draft' => 'Demand Draft',
                                        'Cheque' => 'Cheque',
                                        'Bank' => 'Bank Deposite',
                                        'Cash' => 'Cash',
                                    ],
                                    request('paymode'),
                                    ['class' => 'form-control select'],
                                ) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {{ Form::label('date', __('Month'), ['class' => 'form-label']) }}
                                {{ Form::input('month', 'date', request('date') ? date('Y-m', strtotime(request('date'))) : date('Y-m', strtotime($date)), ['class' => 'form-control', 'id' => 'date']) }}
                            </div>
                        </div>
                        <div class="col-xl-1 col-lg-1 col-md-6 col-sm-12 d-flex align-items-end justify-content-end gap-2 mt-3 mt-lg-0">
                            <a href="#" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('accounting_salary_filter').submit(); return false;">
                                {{ __('Search') }}
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-wrap align-items-center gap-2 accounting-salary-badges">
                    <span class="badge bg-secondary">{{ __('Total Rows') }} <strong>{{ $salaries->count() }}</strong></span>
                    <span class="badge bg-dark">{{ __('Pending Accounts Approval') }} <strong>{{ $pendingApprovalCount }}</strong></span>
                    <span class="badge bg-primary">{{ __('Account Approved') }} <strong>{{ $accountApprovedCount }}</strong></span>
                    <span class="badge bg-warning text-dark">{{ __('Partial Paid') }} <strong>{{ $partialPaidCount }}</strong></span>
                    <span class="badge bg-success">{{ __('Paid') }} <strong>{{ $paidCount }}</strong></span>
                    <span class="badge bg-success">{{ __('Total Amount') }} <strong>{{ number_format($totalAmount, 2) }}</strong></span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="#" id="accounting-approve-salary-btn" class="btn btn-sm btn-outline-primary accounting-salary-action-btn">
                        {{ __('Account Manager Approve') }}
                    </a>
                    <a href="#" id="accounting-return-salary-btn" class="btn btn-sm btn-outline-warning accounting-salary-action-btn">
                        {{ __('Return to HR Admin') }}
                    </a>
                    <a href="#" id="accounting-pay-salary-btn" class="btn btn-sm btn-outline-success accounting-salary-action-btn">
                        {{ __('Pay Salary') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered accounting-salary-table">
                    <thead class="table_heads">
                        <tr>
                            <th><input type="checkbox" id="accounting-salary-check-all"></th>
                            <th>{{ __('Sr.') }}</th>
                            <th>{{ __('Emp No') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Department') }}</th>
                            <th>{{ __('Designation') }}</th>
                            <th>{{ __('Salary Month') }}</th>
                            <th>{{ __('Paid Date') }}</th>
                            <th>{{ __('Paymode') }}</th>
                            <th>{{ __('Account No') }}</th>
                            <th class="text-end">{{ __('Net Pay') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groupedSalaries as $branchName => $branchSalaries)
                            @php
                                $branchKey = 'branch-' . md5($branchName);
                                $branchSelectableCount = $branchSalaries->filter(fn($salary) => trim(strtolower($salary->status ?? '')) !== 'paid')->count();
                            @endphp
                            <tr class="table-secondary">
                                <td>
                                    <input type="checkbox" class="accounting-branch-checkbox" data-branch="{{ $branchKey }}" {{ $branchSelectableCount === 0 ? 'disabled' : '' }}>
                                </td>
                                <td colspan="9" class="fw-bold">
                                    {{ $branchName }} <span class="badge bg-light text-dark ms-2">{{ $branchSalaries->count() }}</span>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($branchSalaries->sum('net_pay'), 2) }}</td>
                                <td></td>
                            </tr>
                            @foreach ($branchSalaries as $salary)
                                @php
                                    $salaryStatus = trim(strtolower($salary->status ?? ''));
                                    $isPaidSalary = $salaryStatus === 'paid';
                                    $paymentJournal = optional($salary->salaryPayment)->journal;
                                    $paymentJournals = $salary->salaryPayments
                                        ->map(function ($payment) {
                                            return $payment->journal;
                                        })
                                        ->filter()
                                        ->unique('id')
                                        ->values();
                                @endphp
                                <tr data-branch="{{ $branchKey }}">
                                    <td><input type="checkbox" class="accounting-salary-checkbox" data-branch="{{ $branchKey }}" data-employee-id="{{ $salary->employee_id }}" value="{{ $salary->id }}" {{ $isPaidSalary ? 'disabled' : '' }}></td>
                                    <td>{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                                    <td class="font-style">
                                        <a href="#"
                                            data-size="xl"
                                            data-url="{{ route('emp-month-sal-attendance.show', $salary->id) }}"
                                            data-ajax-popup="true"
                                            data-bs-title="{{ __('Monthly Salary Detail') }}"
                                            class="mx-1 btn mx-1 btn-sm btn-outline-primary">
                                            <span class="btn-inner--icon">{{ optional($salary->employee)->employee_id }}</span>
                                        </a>
                                    </td>
                                    <td>{{ optional($salary->employee)->name }}</td>
                                    <td>{{ optional(optional($salary->employee)->department)->name }}</td>
                                    <td>{{ optional(optional($salary->employee)->designation)->name }}</td>
                                    <td>{{ date('M-Y', strtotime($salary->salary_date)) }}</td>
                                    <td>{{ !empty($salary->paid_date) ? date('d-M-Y', strtotime($salary->paid_date)) : '-' }}</td>
                                    <td>{{ $salary->paymode }}</td>
                                    <td>{{ $salary->account_number }}</td>
                                    <td class="text-end">
                                        {{ number_format($salary->net_pay, 2) }}
                                        @if (!empty($salary->remaining_pay_amount) && $salaryStatus === 'partial_paid')
                                            <div class="small text-warning">{{ __('Remaining:') }} {{ number_format($salary->remaining_pay_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($salaryStatus === 'paid')
                                            <span class="badge bg-success">{{ __('Paid') }}</span>
                                            @if ($paymentJournals->count() === 1)
                                                <div class="mt-1">
                                                    @php
                                                        $voucherRoute = $paymentJournals->first()->voucher_type === 'CPV' ? 'cash-payment-voucher.show' : 'bank-payment-voucher.show';
                                                    @endphp
                                                    <a href="{{ route($voucherRoute, $paymentJournals->first()->id) }}" target="_blank" class="btn btn-sm btn-outline-success accounting-voucher-btn">
                                                        {{ $paymentJournals->first()->getVoucherNumber() }}
                                                    </a>
                                                </div>
                                            @elseif ($paymentJournals->count() > 1)
                                                <div class="dropdown mt-1">
                                                    <button class="btn btn-sm btn-success dropdown-toggle accounting-voucher-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        {{ __('View Vouchers') }}
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        @foreach ($paymentJournals as $voucherJournal)
                                                            @php
                                                                $voucherRoute = $voucherJournal->voucher_type === 'CPV' ? 'cash-payment-voucher.show' : 'bank-payment-voucher.show';
                                                            @endphp
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route($voucherRoute, $voucherJournal->id) }}" target="_blank">
                                                                    {{ $voucherJournal->getVoucherNumber() }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @elseif ($salaryStatus === 'partial_paid')
                                            <span class="badge bg-warning text-dark">{{ __('Partial Paid') }}</span>
                                            @if ($paymentJournals->count() === 1)
                                                <div class="mt-1">
                                                    @php
                                                        $voucherRoute = $paymentJournals->first()->voucher_type === 'CPV' ? 'cash-payment-voucher.show' : 'bank-payment-voucher.show';
                                                    @endphp
                                                    <a href="{{ route($voucherRoute, $paymentJournals->first()->id) }}" target="_blank" class="btn btn-sm btn-outline-warning accounting-voucher-btn">
                                                        {{ $paymentJournals->first()->getVoucherNumber() }}
                                                    </a>
                                                </div>
                                            @elseif ($paymentJournals->count() > 1)
                                                <div class="dropdown mt-1">
                                                    <button class="btn btn-sm btn-warning dropdown-toggle accounting-voucher-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        {{ __('View Vouchers') }}
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        @foreach ($paymentJournals as $voucherJournal)
                                                            @php
                                                                $voucherRoute = $voucherJournal->voucher_type === 'CPV' ? 'cash-payment-voucher.show' : 'bank-payment-voucher.show';
                                                            @endphp
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route($voucherRoute, $voucherJournal->id) }}" target="_blank">
                                                                    {{ $voucherJournal->getVoucherNumber() }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @elseif ($salaryStatus === 'account_approved')
                                            <span class="badge bg-primary">{{ __('Account Approved') }}</span>
                                        @else
                                            <span class="badge bg-dark">{{ __('Pending Accounts Approval') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="{{ $emptyColspan }}" class="text-center">{{ __('No accounting salary found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).on('change', '#accounting-salary-check-all', function() {
            $('.accounting-salary-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
            $('.accounting-branch-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
        });

        $(document).on('change', '.accounting-branch-checkbox', function() {
            var branch = $(this).data('branch');
            $('.accounting-salary-checkbox[data-branch="' + branch + '"]:not(:disabled)').prop('checked', $(this).prop('checked'));
            $('#accounting-salary-check-all').prop(
                'checked',
                $('.accounting-salary-checkbox:not(:disabled)').length > 0 &&
                $('.accounting-salary-checkbox:not(:disabled)').length === $('.accounting-salary-checkbox:not(:disabled):checked').length
            );
        });

        $(document).on('change', '.accounting-salary-checkbox', function() {
            var branch = $(this).data('branch');
            var branchBoxes = $('.accounting-salary-checkbox[data-branch="' + branch + '"]:not(:disabled)');
            $('.accounting-branch-checkbox[data-branch="' + branch + '"]').prop(
                'checked',
                branchBoxes.length > 0 && branchBoxes.length === branchBoxes.filter(':checked').length
            );
            $('#accounting-salary-check-all').prop(
                'checked',
                $('.accounting-salary-checkbox:not(:disabled)').length > 0 &&
                $('.accounting-salary-checkbox:not(:disabled)').length === $('.accounting-salary-checkbox:not(:disabled):checked').length
            );
        });

        let accountingReportDownloadLoaderTimer = null;

        function showAccountingReportDownloadLoader(message = '{{ __('Preparing report...') }}') {
            const loader = document.getElementById('accountingReportDownloadLoader');
            if (!loader) {
                return;
            }

            const text = loader.querySelector('[data-report-loader-text]');
            if (text) {
                text.textContent = message;
            }

            loader.classList.remove('d-none');
            clearTimeout(accountingReportDownloadLoaderTimer);
            accountingReportDownloadLoaderTimer = setTimeout(hideAccountingReportDownloadLoader, 15000);
        }

        function hideAccountingReportDownloadLoader() {
            const loader = document.getElementById('accountingReportDownloadLoader');
            if (loader) {
                loader.classList.add('d-none');
            }

            clearTimeout(accountingReportDownloadLoaderTimer);
            accountingReportDownloadLoaderTimer = null;
        }

        window.addEventListener('pageshow', hideAccountingReportDownloadLoader);
        window.addEventListener('focus', function() {
            setTimeout(hideAccountingReportDownloadLoader, 700);
        });

        function getAccountingSalaryIds() {
            return $('.accounting-salary-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
        }

        function submitAccountingSalaryAction(button, url, processingText) {
            var ids = getAccountingSalaryIds();
            if (!ids.length) {
                show_toastr('error', '{{ __('Please select at least one salary.') }}', 'error');
                return;
            }

            var btn = $(button);
            if (btn.prop('disabled')) {
                return;
            }

            $('.accounting-salary-action-btn').prop('disabled', true).addClass('disabled');
            btn.data('old-text', btn.text()).text(processingText);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    salary_ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        show_toastr('success', response.message, 'success');
                        window.location.reload();
                    } else {
                        show_toastr('error', response.message || '{{ __('Something went wrong.') }}', 'error');
                        $('.accounting-salary-action-btn').prop('disabled', false).removeClass('disabled');
                        btn.text(btn.data('old-text'));
                    }
                },
                error: function(xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '{{ __('Something went wrong.') }}';
                    show_toastr('error', message, 'error');
                    $('.accounting-salary-action-btn').prop('disabled', false).removeClass('disabled');
                    btn.text(btn.data('old-text'));
                }
            });
        }

        function openAccountingBase64Pdf(url) {
            $.ajax({
                url: url,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (!response.base64Pdf) {
                        show_toastr('error', '{{ __('Unable to prepare report preview.') }}', 'error');
                        hideAccountingReportDownloadLoader();
                        return;
                    }

                    const byteCharacters = atob(response.base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], {
                        type: 'application/pdf'
                    });
                    window.open(URL.createObjectURL(blob), '_blank');
                    hideAccountingReportDownloadLoader();
                },
                error: function(xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '{{ __('Something went wrong.') }}';
                    show_toastr('error', message, 'error');
                    hideAccountingReportDownloadLoader();
                }
            });
        }

        $(document).on('click', '#accounting-approve-salary-btn', function(e) {
            e.preventDefault();
            submitAccountingSalaryAction(this, "{{ route('accounting.salary.approve') }}", '{{ __('Approving...') }}');
        });

        $(document).on('click', '#accounting-return-salary-btn', function(e) {
            e.preventDefault();
            submitAccountingSalaryAction(this, "{{ route('accounting.salary.return') }}", '{{ __('Returning...') }}');
        });

        $(document).on('change', '#accountingSheetAction', function() {
            const action = this.value;
            const sheet = document.getElementById('accountingSheetType').value;
            if (!sheet || !action) {
                return;
            }

            showAccountingReportDownloadLoader(action === 'excel' ? '{{ __('Preparing Excel report...') }}' : '{{ __('Preparing report preview...') }}');
            const form = document.getElementById('accounting_salary_filter');
            const fd = new FormData(form);
            const selectedIds = getAccountingSalaryIds();
            selectedIds.forEach(function(id) {
                const employeeId = $('.accounting-salary-checkbox[value="' + id + '"]').data('employee-id');
                if (employeeId) {
                    fd.append('employee_ids[]', employeeId);
                }
            });

            const qs = new URLSearchParams(fd).toString();
            const excelRoutes = {
                salary: "{{ route('export_salary_sheet') }}",
                deduction: "{{ route('export_deduction_sheet') }}",
                paymode: "{{ route('export_paymode_sheet') }}",
                gross: "{{ route('export_gross_sheet') }}",
                advance: "{{ route('export_advance_sheet') }}",
            };
            const previewRoutes = {
                salary: "{{ route('salary_sheet') }}",
                deduction: "{{ route('deduction_sheet') }}",
                paymode: "{{ route('paymode_sheet') }}",
                gross: "{{ route('gross_sheet') }}",
                advance: "{{ route('advance_sheet') }}",
                slip: "{{ route('salary_slip') }}",
            };

            if (action === 'excel') {
                if (sheet === 'slip') {
                    fd.append('export_type', 'excel');
                    window.location.href = "{{ route('salary_slip') }}?" + new URLSearchParams(fd).toString();
                } else {
                    window.location.href = excelRoutes[sheet] + '?' + qs;
                }
            } else {
                if (sheet === 'slip') {
                    fd.append('export_type', 'pdf');
                    window.open("{{ route('salary_slip') }}?" + new URLSearchParams(fd).toString(), '_blank');
                } else if (['deduction', 'paymode', 'gross', 'advance'].includes(sheet)) {
                    openAccountingBase64Pdf(previewRoutes[sheet] + '?' + qs);
                } else {
                    window.open(previewRoutes[sheet] + '?' + qs, '_blank');
                }
            }

            this.selectedIndex = 0;
        });

        $(document).on('click', '#accounting-pay-salary-btn', function(e) {
            e.preventDefault();
            var ids = getAccountingSalaryIds();

            if (!ids.length) {
                show_toastr('error', '{{ __('Please select at least one salary.') }}', 'error');
                return;
            }

            $.ajax({
                url: "{{ route('accounting.salary.pay.modal') }}",
                type: 'GET',
                data: { salary_ids: ids },
                success: function(response) {
                    $('#commonModal .modal-title').html('{{ __('Pay Salary') }}');
                    $('#commonModal .modal-dialog').removeClass('modal-xl modal-xl modal-xl modal-fullscreen').addClass('modal-xl');
                    $('#commonModal .body').html(response);
                    $('#commonModal').modal('show');
                },
                error: function(xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '{{ __('Something went wrong.') }}';
                    show_toastr('error', message, 'error');
                }
            });
        });

        $(document).on('submit', '#accounting-salary-pay-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('[type="submit"]');
            if (btn.prop('disabled')) {
                return;
            }

            btn.prop('disabled', true).data('old-text', btn.text()).text('{{ __('Processing...') }}');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        show_toastr('success', response.message, 'success');
                        $('#commonModal').modal('hide');
                        window.location.reload();
                    } else {
                        show_toastr('error', response.message || '{{ __('Something went wrong.') }}', 'error');
                        btn.prop('disabled', false).text(btn.data('old-text'));
                    }
                },
                error: function(xhr) {
                    var message = '{{ __('Something went wrong.') }}';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                    }
                    show_toastr('error', message, 'error');
                    btn.prop('disabled', false).text(btn.data('old-text'));
                }
            });
        });
    </script>
@endpush

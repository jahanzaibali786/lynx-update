{{ Form::open(['route' => 'accounting.salary.pay', 'method' => 'POST', 'id' => 'accounting-salary-pay-form']) }}
<style>
    #accounting-salary-pay-form .accounting-pay-modal-body {
        padding-top: 14px;
        padding-bottom: 14px;
    }

    #accounting-salary-pay-form .accounting-pay-table-wrap {
        max-height: 260px;
        overflow-y: auto;
        overflow-x: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    #accounting-salary-pay-form .accounting-pay-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
    }

    #accounting-salary-pay-form .accounting-pay-table th,
    #accounting-salary-pay-form .accounting-pay-table td {
        padding: 7px 9px;
        white-space: normal;
        word-break: break-word;
        vertical-align: middle;
    }

    #accounting-salary-pay-form .accounting-pay-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #accounting-salary-pay-form .accounting-pay-table .col-sr {
        width: 44px;
    }

    #accounting-salary-pay-form .accounting-pay-table .col-emp-no {
        width: 74px;
    }

    #accounting-salary-pay-form .accounting-pay-table .col-month {
        width: 118px;
    }

    #accounting-salary-pay-form .accounting-pay-table .col-net {
        width: 112px;
    }

    #accounting-salary-pay-form .accounting-pay-table .col-paid,
    #accounting-salary-pay-form .accounting-pay-table .col-remaining,
    #accounting-salary-pay-form .accounting-pay-table .col-payment {
        width: 120px;
    }

    #accounting-salary-pay-form .accounting-pay-table .col-payment {
        width: 170px;
    }

    #accounting-salary-pay-form .payment-amount-input {
        min-width: 150px;
    }

    #accounting-salary-pay-form .accounting-grand-total {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 14px;
        margin-top: 8px;
        padding: 8px 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #f8fafc;
        font-weight: 700;
    }

    #accounting-salary-pay-form .accounting-grand-total .amount {
        min-width: 140px;
        text-align: right;
        color: #0f172a;
    }

    #accounting-salary-pay-form .form-group {
        margin-bottom: 10px;
    }

    #accounting-salary-pay-form .modal-footer {
        padding-top: 10px;
        padding-bottom: 10px;
    }

    #accounting-salary-pay-form .account-balance-note {
        margin-top: 6px;
        font-size: 12px;
        color: #475569;
    }

    #accounting-salary-pay-form .account-balance-note strong {
        color: #0f172a;
    }

    #accounting-salary-pay-form .payment-amount-input.text-danger {
        border-color: #dc2626;
        color: #dc2626;
    }
</style>
<div class="modal-body accounting-pay-modal-body">
    @foreach ($salaryIds as $salaryId)
        <input type="hidden" name="salary_ids[]" value="{{ $salaryId }}">
    @endforeach

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('bank_id', __('Bank Account'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('bank_id', $bankAccounts, null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'accounting_salary_bank_id']) }}
                <div class="account-balance-note" id="accountingSalaryBankBalance">{{ __('Available Balance:') }} <strong>-</strong></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('payment_date', __('Payment Date'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::date('payment_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('reference', __('Reference No'), ['class' => 'form-label']) }}
                {{ Form::text('reference', null, ['class' => 'form-control', 'placeholder' => __('Reference No')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Remarks'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Remarks')]) }}
            </div>
        </div>
    </div>

    <div class="accounting-pay-table-wrap mt-2">
        <table class="table table-sm table-bordered accounting-pay-table">
            <thead class="table_heads">
                <tr>
                    <th class="col-sr">{{ __('Sr.') }}</th>
                    <th class="col-emp-no">{{ __('Emp No') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th class="col-month">{{ __('Salary Month') }}</th>
                    <th class="text-end col-paid">{{ __('Paid') }}</th>
                    <th class="text-end col-remaining">{{ __('Remaining') }}</th>
                    <th class="col-payment">{{ __('Pay Now') }}</th>
                    <th class="text-end col-net">{{ __('Net Pay') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salaries as $salary)
                    <tr>
                        <td class="col-sr">{{ $loop->iteration }}</td>
                        <td class="col-emp-no">{{ optional($salary->employee)->employee_id }}</td>
                        <td>{{ optional($salary->employee)->name }}</td>
                        <td class="col-month">{{ date('M-Y', strtotime($salary->salary_date)) }}</td>
                        <td class="text-end col-paid">{{ number_format($salary->total_paid_amount ?? 0, 2) }}</td>
                        <td class="text-end col-remaining" data-remaining="{{ $salary->remaining_pay_amount ?? 0 }}">{{ number_format($salary->remaining_pay_amount ?? 0, 2) }}</td>
                        <td class="col-payment">
                            <input
                                type="text"
                                name="payment_amounts[{{ $salary->id }}]"
                                class="form-control payment-amount-input"
                                inputmode="decimal"
                                value="{{ number_format($salary->remaining_pay_amount ?? 0, 2, '.', '') }}"
                                data-salary-id="{{ $salary->id }}"
                                data-remaining="{{ number_format($salary->remaining_pay_amount ?? 0, 2, '.', '') }}"
                                required
                            >
                        </td>
                        <td class="text-end col-net">{{ number_format($salary->net_pay, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="accounting-grand-total">
        <span>{{ __('Payment Total') }}</span>
        <span class="amount" id="accountingSalaryPaymentTotal">{{ number_format($totalAmount, 2) }}</span>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-sm btn-primary">{{ __('Pay Salary') }}</button>
</div>
<script>
    (function () {
        var bankBalances = @json($bankBalances);
        var $bankSelect = $('#accounting_salary_bank_id');
        var $balanceNote = $('#accountingSalaryBankBalance');
        var $paymentInputs = $('.payment-amount-input');
        var $paymentTotal = $('#accountingSalaryPaymentTotal');

        function getSelectedBankBalance() {
            var selectedId = $bankSelect.val();
            return selectedId && bankBalances[selectedId] ? bankBalances[selectedId] : null;
        }

        function getPaymentTotal() {
            var total = 0;

            $paymentInputs.each(function () {
                total += parseFloat($(this).val()) || 0;
            });

            return total;
        }

        function updateSelectedBankBalance() {
            var selectedBank = getSelectedBankBalance();
            var total = getPaymentTotal();

            if (!selectedBank) {
                $balanceNote.html('{{ __('Available Balance:') }} <strong>-</strong>');
                return;
            }

            var isShort = parseFloat(selectedBank.balance || 0) < total;
            $balanceNote.html(
                selectedBank.type + ' {{ __('Balance:') }} <strong' + (isShort ? ' class="text-danger"' : '') + '>' + selectedBank.formatted_balance + '</strong>'
            );
        }

        function updatePaymentTotals() {
            var total = getPaymentTotal();
            $paymentTotal.text(total.toFixed(2));
            updateSelectedBankBalance();
        }

        function validateRowAmount(input) {
            var $input = $(input);
            var remaining = parseFloat($input.data('remaining')) || 0;
            var amount = parseFloat($input.val()) || 0;
            var invalid = amount <= 0 || amount > remaining;

            $input.toggleClass('text-danger', invalid);
        }

        function sanitizePaymentAmount(value, remaining) {
            value = String(value || '').replace(/[^0-9.]/g, '');

            var parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
                parts = value.split('.');
            }

            if (parts[1] !== undefined) {
                parts[1] = parts[1].slice(0, 2);
                value = parts[0] + '.' + parts[1];
            }

            var numericValue = parseFloat(value);
            if (!isNaN(numericValue) && numericValue > remaining) {
                value = remaining.toFixed(2);
            }

            return value;
        }

        $(document).off('change.accountingSalaryBankBalance', '#accounting_salary_bank_id')
            .on('change.accountingSalaryBankBalance', '#accounting_salary_bank_id', updateSelectedBankBalance);
        $(document).off('keypress.accountingSalaryPayment', '.payment-amount-input')
            .on('keypress.accountingSalaryPayment', '.payment-amount-input', function (e) {
                var charCode = typeof e.which === 'number' ? e.which : e.keyCode;
                var char = String.fromCharCode(charCode);

                if (charCode === 0 || charCode === 8 || charCode === 13) {
                    return;
                }

                if (!/[0-9.]/.test(char)) {
                    e.preventDefault();
                    return;
                }

                if (char === '.' && $(this).val().indexOf('.') !== -1) {
                    e.preventDefault();
                }
            });
        $(document).off('input.accountingSalaryPayment', '.payment-amount-input')
            .on('input.accountingSalaryPayment', '.payment-amount-input', function () {
                var remaining = parseFloat($(this).data('remaining')) || 0;
                $(this).val(sanitizePaymentAmount($(this).val(), remaining));
                validateRowAmount(this);
                updatePaymentTotals();
            });
        $(document).off('blur.accountingSalaryPayment', '.payment-amount-input')
            .on('blur.accountingSalaryPayment', '.payment-amount-input', function () {
                var remaining = parseFloat($(this).data('remaining')) || 0;
                var value = parseFloat($(this).val());

                if (!isNaN(value) && value > 0) {
                    $(this).val(Math.min(value, remaining).toFixed(2));
                }

                validateRowAmount(this);
                updatePaymentTotals();
            });

        $paymentInputs.each(function () {
            var remaining = parseFloat($(this).data('remaining')) || 0;
            $(this).val(sanitizePaymentAmount($(this).val(), remaining));
            validateRowAmount(this);
        });
        updatePaymentTotals();
        updateSelectedBankBalance();
    })();
</script>
{{ Form::close() }}

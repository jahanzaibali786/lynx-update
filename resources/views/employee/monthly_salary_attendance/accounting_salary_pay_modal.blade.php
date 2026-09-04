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
</style>
<div class="modal-body accounting-pay-modal-body">
    @foreach ($salaryIds as $salaryId)
        <input type="hidden" name="salary_ids[]" value="{{ $salaryId }}">
    @endforeach

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('bank_id', __('Bank Account'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('bank_id', $bankAccounts, null, ['class' => 'form-control select', 'required' => 'required']) }}
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
                        <td class="text-end col-net">{{ number_format($salary->net_pay, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="accounting-grand-total">
        <span>{{ __('Grand Total') }}</span>
        <span class="amount">{{ number_format($totalAmount, 2) }}</span>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-sm btn-primary">{{ __('Pay Salary') }}</button>
</div>
{{ Form::close() }}

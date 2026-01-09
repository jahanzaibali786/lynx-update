<style>
    table,
    tr,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size: 0.6rem;
    }
</style>
<div class=" p-4">
    <p style="text-align:center; font-weight:900; font-size:1rem;">
    <table class="">
        <thead>
            <tr class="table_heads" style="font-size:0.8rem;">
                <th>{{ __('Sr No.') }}</th>
                <th>{{ __('Branch') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Pay Scale') }}</th>
                <th>{{ __('Desg.') }}</th>
                @foreach ($employee as $emp)
                    @foreach ($emp->employee_monthly_salaries ?? [] as $salary)
                        @foreach ($salary->salaryheads ?? [] as $salhead)
                            <th>{{ $salhead->SalaryHead->head ?? '' }}</th>
                        @endforeach
                    @endforeach
                @endforeach
                <th>{{ __('Earned Basic') }}</th>
                <th>{{ __('Conv') }}</th>
                <th>{{ __('Sal') }}</th>
                <th>{{ __('others.') }}</th>
                <th>{{ __('stop_sal') }}</th>
                <th>{{ __('Gross') }}</th>
                <th>{{ __('Emp.sec') }}</th>
                <th>{{ __('Adv.Tax') }}</th>
                <th>{{ __('EOBI') }}</th>
                <th>{{ __('Loan E.s') }}</th>
                <th>{{ __('Others') }}</th>
                <th>{{ __('Stop_sal') }}</th>
                <th>{{ __('PESSI') }}</th>
                <th>{{ __('Loan Adj.') }}</th>
                <th>{{ __('Net') }}</th>
                <th>{{ __('PESSI Comp') }}</th>
                <th>{{ __('EOBI Comp') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Cost to Comp.') }}</th>
                <th>{{ __('OP') }}</th>
                <th>{{ __('LVS') }}</th>
                <th>{{ __('OP') }}</th>
                <th>{{ __('LVS') }}</th>
                <th>{{ __('Bal') }}</th>
                <th>{{ __('Working Days') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employee as $emp)
                @foreach (@$emp->employee_monthly_salaries ?? [] as $salary)
                    @php
                        $payscale = $emp->employee_payscale_details->last();
                    @endphp
                    <tr style="font-size:0.7rem;">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Auth::user()->getBranch($emp->branch_id)->name }}</td>
                        <td>{!! \Carbon\Carbon::parse(@$salary->salary_date)->format('d-F-Y') !!}</td>
                        <td>{{ @$salary->scale_no }}</td>
                        <td>{!! @$emp->designation->name !!}</td>
                        @foreach (@$salary->salaryheads ?? [] as $salhead)
                            <td>{{ $salhead->head_value }}</td>
                        @endforeach
                        <td>{{ !empty(@$salary->basics) ? $salary->basics : '0' }}</td>
                        <td>{{ !empty(@$salary->conv) ? @$salary->conv : '0' }}</td>
                        <td>{{ !empty(@$salary->sal_all) ? @$salary->sal_all : '0' }}</td>
                        <td>{{ !empty(@$salary->other) ? @$salary->other : '0' }}</td>
                        <td>{{ !empty(@$salary->stop_sal) ? @$salary->stop_sal : '0' }}</td>
                        <td>{{ !empty(@$salary->gross) ? @$salary->gross : '0' }}</td>
                        <td>{{ !empty(@$salary->emp_sec) ? @$salary->emp_sec : '0' }}</td>
                        <td>{{ !empty(@$salary->it) ? @$salary->it : '0' }}</td>
                        <td>{{ !empty(@$payscale->eobi) ? @$payscale->eobi : '0' }}</td>
                        <td>{{ !empty(@$salary->loan) ? @$salary->loan : '0' }}</td>
                        <td>{{ !empty(@$salary->other) ? @$salary->other : '0' }}</td>
                        <td>{{ !empty(@$salary->stop_sal) ? @$salary->stop_sal : '0' }}</td>
                        <td>{{ !empty(@$payscale->pessi) ? @$payscale->pessi : '0' }}</td>
                        <td>{{ !empty(@$salary->loan_adj) ? @$salary->loan_adj : '0' }}</td>
                        @php
                            $total_deduction =
                                @$salary->gross -
                                (@$salary->emp_sec +
                                    @$salary->it +
                                    @$payscale->eobi +
                                    @$salary->loan +
                                    @$salary->other +
                                    @$salary->stop_sal +
                                    @$salary->loan_adj +
                                    @$payscale->pessi);
                        @endphp
                        <td>{{ $total_deduction }}</td>
                        <td>{{ !empty(@$payscale->eobi_employer) ? @$payscale->eobi_employer : '0' }}</td>
                        <td>{{ !empty(@$payscale->pessi_employer) ? @$payscale->pessi_employer : '0' }}
                        </td>
                        @php
                            $total_sum = @$salary->loan_adj + @$payscale->eobi_employer + @$payscale->pessi_employer;
                            $total_addition = @$total_deduction + @$total_sum;
                        @endphp
                        <td>{{ !empty(@$total_sum) ? @$total_sum : '0' }}</td>
                        <td>{{ !empty(@$total_addition) ? @$total_addition : '0' }}</td>
                        <td>{{ !empty(@$salary->op) ? @$salary->op : '0' }}</td>
                        <td>{{ !empty(@$salary->lvs) ? @$salary->lvs : '0' }}</td>
                        <td>{{ !empty(@$salary->op) ? @$salary->op : '0' }}</td>
                        <td>{{ !empty(@$salary->lvs) ? @$salary->lvs : '0' }}</td>
                        <td>{{ !empty(@$salary->balance) ? @$salary->balance : '0' }}</td>
                        <td>{{ !empty(@$salary->balance) ? @$salary->balance : '0' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

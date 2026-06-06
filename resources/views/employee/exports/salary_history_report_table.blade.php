<table class="datatable">
    <thead>
        <tr>
            {{-- <td style="width: 75px;" rowspan="3"></td>
            <td rowspan="3" style="text-align: center;">
                <img src="{{ public_path('assets/images/lynx2.jpg') }}" alt="School Logo" width="75px" height="75px" style="width: 50px; height: 50px;">
            </td> --}}
            <td colspan="31"
                style="text-align: center; font-family: 'Edwardian Script ITC'; font-weight: 800; font-size: 35rem;">
                The Lynx School
            </td>
        </tr>
        <tr>
            <td colspan="31" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span>{{ __('Salary History Report') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="31" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span>{{ \Carbon\Carbon::now()->format('F Y') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="31" style="text-align: center;">
            </td>
        </tr>
        <tr
            style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; ">
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray;">
                {{ __('Sr No.') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">
                {{ __('Branch') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 130px; background-color:gray;">
                {{ __('Date') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 120px; background-color:gray;">
                {{ __('Pay Scale') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">
                {{ __('Designation') }}</th>
            @foreach ($employee as $emp)
                @foreach ($emp->employee_monthly_salaries ?? [] as $salary)
                    @foreach ($salary->salaryheads ?? [] as $salhead)
                        <th
                            style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 130px; background-color:gray;">
                            {{ $salhead->SalaryHead->head ?? '' }}</th>
                    @endforeach
                @endforeach
            @endforeach
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 110px; background-color:gray;">
                {{ __('Earned Basic') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('Other') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('Salary') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('Other Allowance') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 90px; background-color:gray;">
                {{ __('Drns & Misc') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('stop_sal') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray;">
                {{ __('Gross') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('Emp.sec') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray;">
                {{ __('Adv.Tax') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('EOBI') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('Loan E.s') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('Other Allowance') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray;">
                {{ __('Stop_sal') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('PESSI') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray;">
                {{ __('Loan Adj.') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('Net') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray;">
                {{ __('PESSI Comp') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray;">
                {{ __('EOBI Comp') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 70px; background-color:gray;">
                {{ __('Total') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray;">
                {{ __('Cost to Comp.') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray;">
                {{ __('OP') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray;">
                {{ __('LVS') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray;">
                {{ __('OP') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray;">
                {{ __('LVS') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray;">
                {{ __('Bal') }}</th>
            <th
                style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 120px; background-color:gray;">
                {{ __('Working Days') }}</th>
        </tr>

    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @foreach ($employee as $emp)
            @foreach (@$emp->employee_monthly_salaries ?? [] as $salary)
                @php
                    $payscale = $emp->employee_payscale_details->last();
                @endphp
                <tr style="border: 2px solid black; border-collapse: collapse;">
                    <td style="border: 2px solid black; border-collapse: collapse;">{{ $loop->iteration }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ \Auth::user()->getBranch($emp->branch_id)->name }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">{!! \Carbon\Carbon::parse(@$salary->salary_date)->format('d-F-Y') !!}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">{{ @$salary->scale_no }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">{!! @$emp->designation->name !!}</td>
                    @foreach (@$salary->salaryheads ?? [] as $salhead)
                        <td style="border: 2px solid black; border-collapse: collapse;">{{ $salhead->head_value }}</td>
                    @endforeach
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->basics) ? $salary->basics : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->conv) ? @$salary->conv : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->sal_all) ? @$salary->sal_all : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->other) ? @$salary->other : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ (@$salary->drns ?? 0) + (@$salary->misc ?? 0) }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->stop_sal) ? @$salary->stop_sal : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->gross) ? @$salary->gross : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->emp_sec) ? @$salary->emp_sec : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->it) ? @$salary->it : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$payscale->eobi) ? @$payscale->eobi : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->loan) ? @$salary->loan : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->other) ? @$salary->other : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->stop_sal) ? @$salary->stop_sal : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$payscale->pessi) ? @$payscale->pessi : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->loan_adj) ? @$salary->loan_adj : '0' }}</td>
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
                    <td style="border: 2px solid black; border-collapse: collapse;">{{ $total_deduction }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$payscale->eobi_employer) ? @$payscale->eobi_employer : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$payscale->pessi_employer) ? @$payscale->pessi_employer : '0' }}</td>
                    @php
                        $total_sum = @$salary->loan_adj + @$payscale->eobi_employer + @$payscale->pessi_employer;
                        $total_addition = @$total_deduction + @$total_sum;
                    @endphp
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$total_sum) ? @$total_sum : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$total_addition) ? @$total_addition : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->op) ? @$salary->op : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->lvs) ? @$salary->lvs : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->op) ? @$salary->op : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->lvs) ? @$salary->lvs : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->balance) ? @$salary->balance : '0' }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(@$salary->balance) ? @$salary->balance : '0' }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

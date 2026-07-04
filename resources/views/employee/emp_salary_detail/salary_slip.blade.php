<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    @php
        $gross = 0;
    @endphp
    @foreach ($datas as $key => $data)
        @php
            $payscale = $data->employee->employee_payscale_details->last();
        @endphp
        <div style="width: 100%; position: relative; bottom: 30px; display: table; line-height:1rem;">
            <div style="display: table-cell; width: 10%; text-align: center; vertical-align: middle;"></div>
            <div style="display: table-cell; width: 65%; text-align: center; vertical-align: middle;">
                <h4 style="font-size: 1.9rem; font-weight: 800; margin: 0;">The Lynx School</h4>
                <p style="font-size: 1.3rem;">{!! \Auth::user()->getBranch($requestdata['branches'])
                    ? \Auth::user()->getBranch($requestdata['branches'])->name
                    : 'Main Branch' !!}</p>
                <h4 style="font-size: 1.7rem; font-weight: 800; margin: 0;">Salary Slip</h4>
            </div>
            <div style="display: table-cell; width: 25%; text-align: center; vertical-align: middle;">
                <div class="logo">
                    <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;"
                        alt="logo">
                </div>
            </div>
        </div>
        <table class="datatable">
            <thead>
                <tr>
                    <th colspan="2" style="width:500px;"></th>
                    <th colspan="1" style="width:100px;"></th>
                    <th style="width:200px;"></th>
                    <th style="width:50px;">CL</th>
                    <th style="width:50px;">AL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $empleaves = $data->employee->employee_monthly_salaries_attend->first();
                    $leav_cas =
                        (!empty($empleaves->total_casual) ? @$empleaves->total_casual : '0') -
                        (!empty($empleaves->bal_casual) ? @$empleaves->bal_casual : '0');

                    $leav_anul =
                        (!empty($empleaves->total_annual) ? @$empleaves->total_annual : '0') -
                        (!empty($empleaves->bal_annual) ? @$empleaves->bal_annual : '0');
                @endphp
                <tr>
                    <td style=""><b>Name</b></td>
                    <td style="padding-left:50px;">{{ !empty($data->employee->name) ? @$data->employee->name : '' }}
                    </td>
                    <td></td>
                    <td style="padding-left:30px;"><b>Opening Balance</b></td>
                    <td style="padding-left:30px;">
                        {{ !empty($empleaves->total_casual) ? @$empleaves->total_casual : '0' }}
                    </td>
                    <td style="padding-left:30px;">
                        {{ !empty($empleaves->total_annual) ? @$empleaves->total_annual : '0' }}
                    </td>
                </tr>
                <tr>
                    <td style=""><b>Employee Id #</b></td>
                    <td style="padding-left:50px;">{{ !empty($data->employee->id) ? @$data->employee->id : '' }}</td>
                    <td></td>
                    <td style="padding-left:30px;"><b>Leaves</b></td>
                    <td style="padding-left:30px;">{{ !empty($leav_cas) ? @$leav_cas : '0' }}</td>
                    <td style="padding-left:30px;">{{ !empty($leav_anul) ? @$leav_anul : '0' }}</td>
                </tr>
                <tr>
                    <td style=""><b>Designation</b></td>
                    <td style="padding-left:50px;">
                        {{ !empty($data->employee->designation->name) ? @$data->employee->designation->name : '' }}</td>
                    <td></td>
                    <td style="padding-left:30px;"><b>Balance</b></td>
                    <td style="padding-left:30px;">{{ !empty($empleaves->bal_casual) ? @$empleaves->bal_casual : '0' }}
                    </td>
                    <td style="padding-left:30px;">{{ !empty($empleaves->bal_annual) ? @$empleaves->bal_annual : '0' }}
                    </td>
                </tr>
                <tr>
                    <td style=""><b>Salary Month</b></td>
                    <td style="padding-left:50px;">{{ \Carbon\Carbon::parse($requestdata['date'])->format('F-Y') }}
                    </td>
                    <td></td>
                    <td style="padding-left:30px;"><b>Working Days</b></td>
                    <td style="padding-left:30px;">{{ !empty($data->sal_days) ? @$data->sal_days : '' }}</td>
                    <td style="padding-left:30px;"></td>
                </tr>
            </tbody>
        </table>
        <br>
        <div style="width: 100%; position: relative; display: table; line-height:1rem;">
            @php
                $salaryHeadsCount = count($salaryHeads);
                $deductions = [
                    'Employee Security' => !empty($data->emp_sec) ? $data->emp_sec : '0',
                    'E.O.B.I' => !empty($data->eobi) ? $data->eobi : '0',
                    'P.E.S.S.I' => !empty($data->pessi) ? $data->pessi : '0',
                    'Income Tax' => !empty($data->it) ? $data->it : '0',
                    'Advance' => !empty($payscale->advance) ? $payscale->advance : '0',
                    'Training Course' => !empty($payscale->training) ? $payscale->training : '0',
                    'Others' => !empty($payscale->other) ? $payscale->other : '0',
                    'Loan Emp Security' => !empty($data->loan_emp_sec) ? $data->loan_emp_sec : '0',
                    'Other Deduction' => !empty($data->dedu) ? $data->dedu : '0',
                ];
                $deductionsCount = count($deductions);
                $maxRows = max($salaryHeadsCount, $deductionsCount);
            @endphp

            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th colspan="2"
                                style="border: 1px solid black; padding: 8px; background-color: #f2f2f2;">
                                Receipt
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salaryHeads as $head)
                            @php
                                $found = false;
                            @endphp
                            @foreach ($data->salary_heads as $emp_sal_head)
                                @if ($head->id == $emp_sal_head->head_id)
                                    @php
                                        $found = true;
                                    @endphp
                                    @if($head->head == 'Initial Basic')
                                        <td style="border: 1px solid black; padding: 8px;">{{ @$head->head }}</td>
                                        <td style="border: 1px solid black; padding: 8px;">Rs.
                                            {{ !empty($data->basics) ? @$data->basics : '' }}
                                        </td>
                                    @else
                                    <tr>
                                        <td style="border: 1px solid black; padding: 8px;">{{ @$head->head }}</td>
                                        <td style="border: 1px solid black; padding: 8px;">Rs.
                                            {{ !empty($emp_sal_head->head_value) ? $emp_sal_head->head_value : '' }}
                                        </td>
                                    </tr>
                                    @endif
                                @endif
                            @endforeach
                            @if (!$found)
                                <tr>
                                    <td style="border: 1px solid black; padding: 8px;">{{ @$head->head }}</td>
                                    <td style="border: 1px solid black; padding: 8px;">0</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr>
                            <td style="border: 1px solid black; padding: 8px;">Other</td>
                            <td style="border: 1px solid black; padding: 8px;">Rs.
                                {{ !empty($data->other1) ? @$data->other1 : '0' }}</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 8px;">Salary ({{ \Carbon\Carbon::parse($data->salary_date)->format('M, y') }})</td>
                            <td style="border: 1px solid black; padding: 8px;">Rs.
                                {{ !empty($data->stop_sal) ? @$data->stop_sal : '0' }}</td>
                        </tr>

                        @for ($i = $salaryHeadsCount + 3; $i < $maxRows + 1; $i++)
                            <tr style="height:100px;">
                                <td style="border: 1px solid black; padding: 16px 8px;"></td>
                                <td style="border: 1px solid black; padding: 16px 8px;"></td>
                            </tr>
                        @endfor
                        <tr>
                            <td style="border: 1px solid black; padding: 8px;"><b>Gross Pay</b></td>
                            <td style="border: 1px solid black; padding: 8px;">Rs.
                                {{ (float) ($data->gross ?? 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @php
                $net_deduction =
                    (!empty($data->emp_sec) ? @$data->emp_sec : '0') +
                    (!empty($data->it) ? @$data->it : '0') +
                    (!empty($payscale->advance) ? @$payscale->advance : '0') +
                    (!empty($data->eobi) ? @$data->eobi : '0') +
                    (!empty($data->pessi) ? @$data->pessi : '0') +
                    (!empty($data->dedu) ? @$data->dedu : '0') +
                    (!empty($data->loan_emp_sec) ? @$data->loan_emp_sec : '0');
                $total_payable = (((float) ($data->gross ?? 0)) + ((float) ($data->stop_sal ?? 0))) - $net_deduction;
            @endphp
            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th colspan="2"
                                style="border: 1px solid black; padding: 8px; background-color: #f2f2f2;">
                                Deduction
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deductions as $key => $value)
                            <tr>
                                <td style="border: 1px solid black; padding: 8px;">{{ $key }}</td>
                                <td style="border: 1px solid black; padding: 8px;">Rs. {{ $value }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="border: 1px solid black; padding: 8px;"><b>Net Payable</b></td>
                            <td style="border: 1px solid black; padding: 8px;">Rs. {{ $total_payable }}</td>
                        </tr>
                        @for ($i = $deductionsCount + 1; $i < $maxRows + 1; $i++)
                            <tr>
                                <td style="border: 1px solid black; padding: 8px;"></td>
                                <td style="border: 1px solid black; padding: 8px;"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        <div style="width: 100%; display: table; margin-top:5%; line-height:1.5rem;">
            <div style="display: table-cell; width: 25%; text-align: left; ">Paid Through cheq#</div>
            <div style="display: table-cell; width: 25%; text-align: left; "></div>
            <div style="display: table-cell; width: 25%; text-align: left; ">Net Salary Paid</div>
            <div style="display: table-cell; width: 25%; text-align: left; ">Rs
                {{ !empty($total_payable) ? @$total_payable : '0' }}</div>
        </div>
        <div style="width: 100%; display: table; position:relative;line-height:1.5rem;">
            <div style="display: table-cell; width: 25%; text-align: left; ">Pay Mode</div>
            <div style="display: table-cell; width: 25%; text-align: left; "></div>
            <div style="display: table-cell; width: 25%; text-align: left; ">Paid Date</div>
            <div style="display: table-cell; width: 25%; text-align: left; ">
                {{ \Carbon\Carbon::parse($data->paid_date)->format('d-F-Y') }}</div>
        </div>
        <div style="width: 100%; display: table; position:relative; line-height:1.5rem;">
            <div style="display: table-cell; width: 25%; text-align: left; ">Date of Joining</div>
            <div style="display: table-cell; width: 25%; text-align: left; ">
                {{ !empty($data->employee->company_doj) ? @$data->employee->company_doj : '' }}</div>
            <div style="display: table-cell; width: 25%; text-align: left; ">Bank A/C No</div>
            <div style="display: table-cell; width: 25%; text-align: left; ">
                {{ !empty($payscale->account_number) ? @$payscale->account_number : '' }}</div>
        </div>
        <div style="width: 100%; display: table; position:relative; line-height:1.5rem;">
            <div style="display: table-cell; width: 25%; text-align: left; ">Scale</div>
            <div style="display: table-cell; width: 25%; text-align: left; ">
                {{ !empty($data->scale_no) ? @$data->scale_no : '' }}</div>
            <div style="display: table-cell; width: 25%; text-align: left; "></div>
            <div style="display: table-cell; width: 25%; text-align: left; "></div>
        </div>
        <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
    @endforeach
</body>

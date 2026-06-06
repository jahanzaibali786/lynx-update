<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div style="width: 100%; position: relative; bottom: 30px; display: table;">
        {{-- <div style="display: table-cell; width: 25%; text-align: center; vertical-align: middle;">
            <div class="logo">
                <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
            </div>
        </div> --}}
        <div style="display: table-cell; width: 65%; text-align: center; vertical-align: middle;">
            <h4 style="font-size: 1.9rem; font-weight: 800; margin: 0;">The Lynx School</h4>
            <h5 style="font-size: 1.5rem; font-weight: 800;">{!!\Auth::user()->getBranch($requestdata['branches'])
                ?\Auth::user()->getBranch($requestdata['branches'])->name : 'Main Branch' !!}</h5>
            <h4 style="font-size: 1.7rem; font-weight: 800; margin: 0;">Payroll Register for the month of
                {{ \Carbon\Carbon::parse($requestdata['date'])->format('F-Y') }}</h4>
        </div>
        <div style="display: table-cell; width: 10%; text-align: center; vertical-align: middle;">
        </div>
    </div>
    <div>
        <table style="border: 1px solid #000; border-collapse: collapse; width: 100% !important;">
            <thead>
                <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    <th style="border: 1px solid #000; " colspan="5">Employee Detail</th>
                    <th style="border: 1px solid #000; " colspan="8">Allowances</th>
                    <th style="border: 1px solid #000; "></th>
                    <th style="border: 1px solid #000; "></th>
                    <th style="border: 1px solid #000; " colspan="9">Deduction</th>
                    <th style="border: 1px solid #000; "></th>
                    <th style="border: 1px solid #000; " colspan="4">Cost To School</th>
                    <th style="border: 1px solid #000; " colspan="3">CL</th>
                    <th style="border: 1px solid #000; " colspan="3">AL</th>
                    <th style="border: 1px solid #000; "></th>
                </tr>
                <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    {{-- <th style="border: 1px solid #000;">Departments.</th> --}}
                    <th style="border: 1px solid #000;">Emp no.</th>
                    <th style="border: 1px solid #000;">Scale</th>
                    <th style="border: 1px solid #000;">Name</th>
                    <th style="border: 1px solid #000;">Designation</th>
                    <th style="border: 1px solid #000;">DOJ</th>

                    <th style="border: 1px solid #000;">Earned Basic</th>
                    @php
                        $head_totals = [];
                    @endphp
                    @foreach (@$salaryHeads as $head)
                        <th style="border: 1px solid #000;">{{@$head->head}}</th>
                        @php
                            $head_totals[$head->id] = 0;
                        @endphp
                    @endforeach

                    <th style="border: 1px solid #000;">Other Allowance</th>
                    <th style="border: 1px solid #000;">Other</th>
                    <th style="border: 1px solid #000;">Drns & Misc</th>
                    <th style="border: 1px solid #000;">Stop Salary</th>

                    <th style="border: 1px solid #000;">Gross Pay</th>

                    <th style="border: 1px solid #000;">E.s</th>
                    <th style="border: 1px solid #000;">I.Tax</th>
                    <th style="border: 1px solid #000;">Salary Adv.</th>
                    <th style="border: 1px solid #000;">EOBI Emp.</th>
                    <th style="border: 1px solid #000;">Loan Emp Sec</th>
                    <th style="border: 1px solid #000;">Stop Salary</th>
                    <th style="border: 1px solid #000;">PESSI</th>
                    <th style="border: 1px solid #000;">Other Deduction</th>
                    <th style="border: 1px solid #000;">Loan</th>

                    <th style="border: 1px solid #000;">Net</th>

                    <th style="border: 1px solid #000;">PESSI Comp.</th>
                    <th style="border: 1px solid #000;">EOBI Comp.</th>
                    <th style="border: 1px solid #000;">Total</th>
                    <th style="border: 1px solid #000;">Cost to Comp.</th>

                    <th style="border: 1px solid #000;">OP</th>
                    <th style="border: 1px solid #000;">LVs</th>
                    <th style="border: 1px solid #000;">Bal</th>

                    <th style="border: 1px solid #000;">OP</th>
                    <th style="border: 1px solid #000;">LVs</th>
                    <th style="border: 1px solid #000;">Bal</th>

                    <th style="border: 1px solid #000;">Total Working Days</th>
                </tr>
            </thead>
            <tbody>
                @php
                $gross = 0;
                $total_basics = 0;
                $total_other = 0;
                $total_conv_other = 0;
                $total_other_misc = 0;
                $total_stop_sal = 0;
                $total_gross = 0;
                $total_emp_sec = 0;
                $total_it = 0;
                $total_advance = 0;
                $total_eobi = 0;
                $total_loan_emp_sec = 0;
                $total_stop_sal_deductions = 0;
                $total_pessi = 0;
                $total_other_deduction = 0;
                $total_loan = 0;
                $total_net_pay = 0;
                $total_pessi_employer = 0;
                $total_eobi_employer = 0;
                $total_total_cost = 0;
                $total_cost_to_comp = 0;
                $currentDepartmentId = null; // To track the current department
                @endphp

                    @foreach($datas as $key => $data)
                    @php
                        $payscale = $data->employee->employee_payscale_details->last();
                        $conv_other = $data->conv ?? 0;
                        $other_misc = ($data->drns ?? 0) + ($data->misc ?? 0);
                        $total_basics += $data->basics;
                        $total_other += $data->other;
                        $total_conv_other += $conv_other;
                        $total_other_misc += $other_misc;
                        $total_stop_sal += $data->stop_sal;
                        $total_gross += !empty($data->gross) ? @$data->gross : '0';
                        $total_emp_sec += !empty($data->emp_sec) ? @$data->emp_sec : '0';
                        $total_it += !empty($data->it) ? @$data->it : '0';
                        $total_advance += !empty($data->sal_advance) ? @$data->sal_advance : '0';
                        $total_pessi += '0';
                        $total_loan_emp_sec += !empty($data->emp_sec_loan) ? @$data->emp_sec_loan : '0';
                        $total_eobi += !empty($data->eobi) ? @$data->eobi : '0';
                        $total_loan += !empty($data->loan) ? @$data->loan : '0';
                        $total_net_pay += !empty($data->net_pay) ? @$data->net_pay : '0';
                        $total_stop_sal_deductions += !empty($data->stop_sal) ? @$data->stop_sal : '0';
                        $total_other_deduction += !empty($data->dedu) ? @$data->dedu : '0';
                        $total_pessi_employer += !empty($data->pessi_employer) ? @$data->pessi_employer : '0';
                        $total_eobi_employer += !empty($data->eobi_employer) ? @$data->eobi_employer : '0';
                        $total_total_cost += !empty($total_cost) ? @$total_cost : '0';
                    @endphp
                    <tr>
                        @if ($currentDepartmentId != $data->department_id)
                            @php
                                $currentDepartmentId = $data->department_id;
                            @endphp
                            <tr>
                                <th colspan="35" style="border: 1px solid #000; text-align:left;">
                                    {{ !empty($data->employee->department->name) ? $data->employee->department->name : 'No Department Name' }}
                                </th>
                            </tr>
                        @endif
                    </tr>
                    <tr style="border: 1px solid #000; font-size:0.7rem;">
                        {{-- Display the department name only when the department ID changes --}}

                    <td style="border: 1px solid #000;">{{!empty($data->employee->id) ? @$data->employee->id : ''}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->scale_no) ? @$data->scale_no : ''}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->employee->name) ? @$data->employee->name : ''}}
                    </td>
                    <td style="border: 1px solid #000;">
                        {{!empty($data->employee->designation->name) ? @$data->employee->designation->name : ''}}</td>
                    <td style="border: 1px solid #000;">
                        {{!empty($data->employee->company_doj) ? @$data->employee->company_doj : ''}}</td>

                        <td style="border: 1px solid #000;">
                        {{!empty($data->basics) ? @$data->basics : ''}}</td>


                         @foreach ($salaryHeads as $head)
                            @php
                                // Search for the matching salary head within the employee salary heads
                                $emp_sal_head = $data->salary_heads->firstWhere('head_id', $head->id);
                                $head_value = !empty($emp_sal_head) ? $emp_sal_head->head_value : 0;

                                // Accumulate the total for this head
                                $head_totals[$head->id] += $head_value;
                            @endphp
                            @if($head->head == 'Initial Basic')
                            <td style="border: 1px solid #000;">{{!empty($data->basics) ? @$data->basics : ''}}</td>
                            @else
                            <td style="border: 1px solid #000;">
                                {{ !empty($emp_sal_head) ? $emp_sal_head->head_value : 0 }}
                            </td>
                            @endif
                        @endforeach


                    <td style="border: 1px solid #000;">
                        {{!empty($data->other) ? @$data->other : '0'}}</td>
                    <td style="border: 1px solid #000;">{{ $conv_other }}</td>
                    <td style="border: 1px solid #000;">{{ $other_misc }}</td>
                    <td style="border: 1px solid #000;">
                        {{!empty($data->stop_sal) ? @$data->stop_sal : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->gross) ? @$data->gross : '0'}}</td>

                    <td style="border: 1px solid #000;">{{!empty($data->emp_sec) ? @$data->emp_sec : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->it) ? @$data->it : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->sal_advance) ? @$data->sal_advance : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->eobi) ? @$data->eobi : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->emp_sec_loan) ? @$data->emp_sec_loan : '0'}}
                    </td>
                    {{-- @php
                    $net_deduction = (!empty($data->emp_sec) ? @$data->emp_sec : '0') + (!empty($data->it) ? @$data->it : '0')+(!empty($data->pessi) ? @$data->pessi : '0') + (!empty($data->sal_advance) ? @$data->sal_advance : '0') + (!empty($data->eobi) ? @$data->eobi : '0') + (!empty($data->emp_sec_loan) ? @$data->emp_sec_loan : '0')+ (!empty($data->stop_sal) ? @$data->stop_sal : '0');
                    @endphp --}}

                    <td style="border: 1px solid #000;">{{!empty($data->stop_sal) ? @$data->stop_sal : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->pessi) ? @$data->pessi : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->dedu) ? @$data->dedu : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->loan) ? @$data->loan : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->net_pay) ? @$data->net_pay : '0'}}</td>

                    <td style="border: 1px solid #000;">{{!empty($data->pessi_employer) ? @$data->pessi_employer : '0'}}
                    </td>
                    <td style="border: 1px solid #000;">{{!empty($data->eobi_employer) ? @$data->eobi_employer : '0'}}
                    </td>
                    @php
                     $total_cost = (!empty($data->pessi_employer) ? @$data->pessi_employer : '0') + (!empty($data->eobi_employer) ? @$data->eobi_employer : '0');
                     $cost_to_comp = (!empty($total_cost) ? @$total_cost : '0') + (!empty($data->gross) ? @$data->gross : '0');
                     $total_cost_to_comp += $cost_to_comp;
                    @endphp

                    <td style="border: 1px solid #000;">{{!empty($total_cost) ? @$total_cost : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($cost_to_comp) ? @$cost_to_comp : '0'}}</td>
                    @php
                    $empleaves = $data->employee->employee_monthly_salaries_attend->first();
                    $leav_cas = (!empty($empleaves->total_casual) ? @$empleaves->total_casual :
                    '0')-(!empty($empleaves->bal_casual) ? @$empleaves->bal_casual : '0');

                    $leav_anul = (!empty($empleaves->total_annual) ? @$empleaves->total_annual :
                    '0')-(!empty($empleaves->bal_annual) ? @$empleaves->bal_annual : '0');
                    @endphp
                    <td style="border: 1px solid #000;">
                        {{!empty($empleaves->total_casual) ? @$empleaves->total_casual : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($leav_cas) ? @$leav_cas : '0'}}</td>
                    <td style="border: 1px solid #000;">
                        {{!empty($empleaves->bal_casual) ? @$empleaves->bal_casual : '0'}}</td>

                    <td style="border: 1px solid #000;">
                        {{!empty($empleaves->total_annual) ? @$empleaves->total_annual : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($leav_anul) ? @$leav_anul : '0'}}</td>
                    <td style="border: 1px solid #000;">
                        {{!empty($empleaves->bal_annual) ? @$empleaves->bal_annual : '0'}}</td>

                    <td style="border: 1px solid #000;">{{!empty($data->sal_days) ? @$data->sal_days : ''}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border: 1px solid #000; font-weight: bold;  background-color:gray; font-size:0.9rem;">
                    <td colspan="5" style="text-align: center;">Grand Total:</td>
                    <td style="border: 1px solid #000;">{{ $total_basics }}</td>
                    @foreach ($salaryHeads as $head)
                        <td style="border: 1px solid #000;">
                            {{ $head_totals[$head->id] }}
                        </td>
                    @endforeach
                    <!-- Add other totals for salary heads here if needed -->
                    <td style="border: 1px solid #000;">{{ $total_other }}</td>
                    <td style="border: 1px solid #000;">{{ $total_conv_other }}</td>
                    <td style="border: 1px solid #000;">{{ $total_other_misc }}</td>
                    <td style="border: 1px solid #000;">{{ $total_stop_sal }}</td>
                    <td style="border: 1px solid #000;">{{ $total_gross }}</td>
                    <td style="border: 1px solid #000;">{{ $total_emp_sec }}</td>
                    <td style="border: 1px solid #000;">{{ $total_it }}</td>
                    <td style="border: 1px solid #000;">{{ $total_advance }}</td>
                    <td style="border: 1px solid #000;">{{ $total_eobi }}</td>
                    <td style="border: 1px solid #000;">{{ $total_loan_emp_sec }}</td>
                    <td style="border: 1px solid #000;">{{ $total_stop_sal_deductions }}</td>
                    <td style="border: 1px solid #000;">{{ $total_pessi }}</td>
                    <td style="border: 1px solid #000;">{{ $total_other_deduction }}</td>
                    <td style="border: 1px solid #000;">{{ $total_loan }}</td>
                    <td style="border: 1px solid #000;">{{ $total_net_pay }}</td>
                    <td style="border: 1px solid #000;">{{ $total_pessi_employer }}</td>
                    <td style="border: 1px solid #000;">{{ $total_eobi_employer }}</td>
                    <td style="border: 1px solid #000;">{{ $total_total_cost }}</td>
                    <td style="border: 1px solid #000;">{{ $total_cost_to_comp }}</td>
                    <td style="border: 1px solid #000;"  colspan="7" ></td>
                </tr>
            </tfoot>
        </table>



    </div>

</body>

</html>

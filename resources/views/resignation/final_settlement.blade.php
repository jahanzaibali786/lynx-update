<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <br>
    <div style="font-size: 0.8rem; line-height:1.2rem;">
        <div style="font-size: 0.8rem; position:relative; left:250px; font-weight:800;">Salary Certificate / Final
            Settlement</div>
        <table style="width: 100%; border-collapse: collapse; margin-top:50px;">
            @php
                $gross = 0;
                $earnedgross = 0;
                $total_adj = 0;
                $total_ded = 0;
            @endphp
            <tr>
                <td style="width:25%;"><b>Branch</b></td>
                <td style="width:25%;">{!! \Auth::user()->getBranch($employee->branch_id)->name !!}</td>
                <td style="width:25%;"><b>Name</b></td>
                <td style="width:25%;">{!! @$employee->name !!}</td>
            </tr>
            <tr>
                <td style="width:25%;"><b>Employee No</b></td>
                <td style="width:25%;">{!! \Auth::user()->employeeIdFormat($employee->employee_id) !!}</td>
                <td style="width:25%;"><b>Designation</b></td>
                <td style="width:25%;">{!! @$EmployeefinalSettlement->designation->name !!}</td>
            </tr>
            <tr>
                <td style="width:25%;"><b>Joining Date</b></td>
                <td style="width:25%;">{!! \Carbon\Carbon::parse(@$employee->company_doj)->format('d-F-Y') !!}</td>
                <td style="width:25%;"><b>Resignation Date</b></td>
                <td style="width:25%;">{!! \Carbon\Carbon::parse(@$EmployeefinalSettlement->resignation->resignation_date)->format('d-F-Y') !!}
                </td>
            </tr>
            @php
                    $gross = 0;
                    $earnedgross = 0;
                    $company_doj = \Carbon\Carbon::parse($EmployeefinalSettlement->employee->company_doj);
                    $resign_date = \Carbon\Carbon::parse($EmployeefinalSettlement->employee->resignation->last_attendance_date);
                    $years = $resign_date->diffInYears($company_doj);
                    $months = $resign_date->diffInMonths($company_doj) % 12;
                    if ($years > 0) {
                        $service_tenure = $years . 'years -' . $months . 'months';
                    } else {
                        $service_tenure = $months . ' Months';
                    }
                    $payscale = @$EmployeefinalSettlement->employee->employee_payscale_details->last();
                    $existingslaryforthismonth = \App\Models\EmployeeMonthlySalary::where(
                        'employee_id',
                        $EmployeefinalSettlement->employee->id,
                    )
                        ->whereMonth('salary_date', $resign_date->month)
                        ->whereYear('salary_date', $resign_date->year)
                        ->first();

                    if ($existingslaryforthismonth) {
                        $total_days = 0;
                    } else {
                        $total_days = min((int) $resign_date->day, 30);
                    }
                    $total_days_in_month = 30;
                @endphp
            <tr>
                <td style="width:25%;"><b>Last Date of Attendance</b></td>
                <td style="width:25%;">{!! \Carbon\Carbon::parse(@$EmployeefinalSettlement->resignation->last_attendance_date)->format('d-F-Y') !!}</td>
                <td style="width:25%;"><b>Last Pay Scale</b></td>
                <td style="width:25%;">{!! @$lastPayscaleDetail->scale->scale_no !!}</td>
            </tr>
            <tr>
                <td style="width:25%;"><b>Notice Period</b></td>
                <td style="width:25%;">{{ !empty(@$EmployeefinalSettlement->resignation->notice_date) ? 'Yes' : 'No' }}
                </td>
                <td style="width:25%;"><b>Service Tenure</b></td>
                <td style="width:25%;">{!! @$EmployeefinalSettlement->tenure !!}</td>
            </tr>
            <tr>
                <td style="width:25%;"><b>Clearnce Certificate Date</b></td>
                <td style="width:25%;">{{ \Carbon\Carbon::parse(now())->format('d-F-Y') }}</td>
                <td style="width:25%;"><b>Status</b></td>
                <td style="width:25%;">{!! @$employee->category == 'Regular' ? 'Permanent' : 'Adhoc' !!}</td>
            </tr>
            <tr>
                <td style="width:25%;"><b>Basic Salary</b></td>
                <td style="width:25%;">{!! @$EmployeefinalSettlement->basic_sal !!}</td>
                <td style="width:25%;"><b>Working Days</b></td>
                <td style="width:25%;">{!! $total_days !!}</td>
            </tr>
        </table><br>
        <hr>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th style="width:40%; text-align:left;"></th>
                    <th style="width:5%; text-align:left;"></th>
                    <th style="width:25%; text-align:left;">As Per Anexture 'R'</th>
                    <th style="width:5%; text-align:left;"><b>{!! $total_days !!}</b></th>
                    <th style="width:25%; text-align:left;">Salary for the month {!! \Carbon\Carbon::parse(@$EmployeefinalSettlement->resignation->resignation_date)->format('F-Y') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($EmployeefinalSettlement->finalsettlementHeads as $salhead)
                    @php
                        $gross += $salhead->head_value;
                        $earnedValue = ($salhead->head_value * $total_days) / $total_days_in_month;
                        $earnedgross += $earnedValue;
                        $accounts = \App\Models\JournalEntry::with('accounts')->where('category', 'Final Settlement')->where('voucher_type','BPV')->orwhere('voucher_type','CPV')->where('reference_id', $EmployeefinalSettlement->id)->get();
                        $total_paid = 0;
                        foreach ($accounts as $account) {
                            $total_paid += $account->accounts->sum('debit');
                        }
                    @endphp
                    <tr>
                        <td>{!! @$salhead->salaryHead->head !!}</td>
                        <td><b>Rs.</b></td>
                        <td>{!! $salhead->head_value !!}</td>
                        <td><b>Rs.</b></td>
                        <td>{{ number_format($earnedValue, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td>Gross (A)</td>
                    <td><b>RS.</b></td>
                    <td><b>{{ $gross }}</b></td>
                    <td><b>RS.</b></td>
                    <td><b>{{ number_format($earnedgross, 2) }}</b></td>
                </tr>
                <tr>
                    <td>Less 8% GPE + EOBI</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>0</td>
                </tr>
                <tr style="background-color:gray;">
                    <td><b>Net Payable</b></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><b>{{ number_format($earnedgross, 2) }}</b></td>
                </tr>
                <tr>
                    <td>Less PESSI</td>
                    <td></td>
                    <td>0</td>
                    <td></td>
                    <td>{{ number_format($earnedgross, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <hr>
        <table style="width:100%;">
            <thead>
                <tr style="background-color:gray;">
                    <th style="width:45%; text-align:left;">ADD</th>
                    <th style="width:5%;text-align:left;"></th>
                    <th style="width:45%;"></th>
                    <th style="width:5%; text-align:left;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($EmployeefinalSettlement->final_set_adj_ded as $adj)
                    @if ($adj->type == 'adjustment')
                        @php
                            $total_adj += $adj->value;
                        @endphp
                        <tr>
                            <td>{{ $adj->key }}</td>
                            <td><b>RS.</b></td>
                            <td style=" text-align:center;">{{ number_format($adj->value, 2) }}</td>
                            <td></td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td>Gross Amount Payable</td>
                    <td><b>RS.</b></td>
                    <td style=" text-align:center;">{{ number_format($total_adj + $earnedgross, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <table style="width:100%;">
            <thead>
                <tr style="background-color:gray;">
                    <th style="width:45%; text-align:left;">Deduction</th>
                    <th style="width:5%;text-align:left;"></th>
                    <th style="width:45%;"></th>
                    <th style="width:5%; text-align:left;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($EmployeefinalSettlement->final_set_adj_ded as $ded)
                    @if ($ded->type == 'deduction')
                        @if (preg_match_all('/(\d+)\s+(casual|Days Leaves)/i', $ded->key, $matches))
                            @php
                                $casual = (int) ($matches[1][0] ?? 0); // First match: casual
                                $annual = (int) ($matches[1][1] ?? 0); // Second match: Days Leaves (annual)
                                $totalLeaves = $casual + $annual;

                                $perdaysal = (float) ($gross / $total_days_in_month);
                                $lvsded = $perdaysal * $totalLeaves;
                                $percentageaddlvs = ($lvsded * 10) / 100;
                                $total_ded += $lvsded + $percentageaddlvs;
                            @endphp
                        @else
                            @php
                                $total_ded += $ded->value;
                            @endphp
                        @endif
                        <tr>
                            <td>{{ $ded->key }}</td>
                            <td><b>RS.</b></td>
                            <td style=" text-align:center;">{{ number_format($ded->value, 2) }}</td>
                            <td></td>
                        </tr>
                    @endif
                @endforeach
                @if($total_paid > 0)
                        <tr>
                            <td>Amount Paid</td>
                            <td><b>RS.</b></td>
                            <td style=" text-align:center;">{{ number_format($total_paid, 2) }}</td>
                            <td></td>
                        </tr>
                @endif
                <tr style="background-color:gray;">
                    <td>Net Amount Payable (Recoverable)</td>
                    <td><b>RS.</b></td>
                    <td style=" text-align:center;">
                        {{ number_format($total_adj + $earnedgross - $total_ded - $total_paid , 2) }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align:right;"><b>Date</b></td>
                    <td></td>
                    <td style=" text-align:center;"><b>{{ \Carbon\Carbon::parse(now())->format('d-F-Y') }}</b></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <br><br>
        <style>
            .stamp th {
                border-bottom: 1px solid black;
            }
        </style>
        <table style="width:100%;">
            <thead>
                <tr class="stamp">
                    <th style="width:15%; margin:0px 5px !important;"></th>
                    <th style="width:23%; margin:0px 5px !important;">Mohsin Fiaz</th>
                    <th style="width:23%; margin:0px 5px !important;">Muhammad Sajjad</th>
                    <th style="width:23%;">Shahid Mehmood</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Prepared By</td>
                    <td>Checked by (Manager Finance)</td>
                    <td>Authorized by (Director Admin)</td>
                    <td>Approved by (Director Finance)</td>
                </tr>
            </tbody>
        </table><br>
        <p>I <span style="width: 150px; border-bottom:1px solid black;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{!! @$employee->name !!}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>do
            hereby confirm that i have received my entire dues from <i>The Lynx School (Pvt) Ltd </i>and i have no claim
            on the school.</p>
        <table style="width:100%;">
            <tbody>
                <tr>
                    <td style="width:25%;"></td>
                    <td style="width:25%;"></td>
                    <td style="width:25%;"></td>
                    <td style="border-bottom:1px solid black; width:25%;">{!! @$employee->name !!}</td>
                </tr>
                <br>
                <tr style="">
                    <td style="width:25%;"></td>
                    <td style="width:25%;"></td>
                    <td style="width:25%;"></td>
                    <td style="text-align:right; width:25%;">Date: -------------------------------</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>

</html>

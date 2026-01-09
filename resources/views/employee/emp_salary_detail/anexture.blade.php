<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Profile - Annexure A</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 9px; margin: 20px; line-height: 1.2;">
    
    <!-- Main Container Table -->
    <table style="width: 100%; border-collapse: collapse; margin-top: -20px;">
        <tr>
            <td>
                <!-- Header Table -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                    <tr>
                        <td style="width: 70%; text-align: center; vertical-align: top;">
                        </td>
                        <td style="width: 15%; text-align: right; vertical-align: top;">
                            <div style="font-weight: bold; font-size: 10px;">ANNEXURE "A"</div>
                        </td>
                    </tr>
                </table>

                <!-- Employee Profile Section -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                    <tr>
                        <td style="background-color: #d3d3d3; padding: 6px; text-align: center; font-weight: bold; font-size: 11px; border: 1px solid #d3d3d3;">
                            EMPLOYEE PROFILE
                        </td>
                    </tr>
                </table>

                <!-- Employee Details Table -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                    <tr>
                        <td style="font-weight: bold; width: 120px; padding: 3px 8px; font-size: 9px;">Name</td>
                        <td style="width: 60px;"></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ $employee->name ?? '-' }}</td>
                        <td style="width: 15%; vertical-align: top;" rowspan="7">
                            <!-- Employee Photo -->
                            @if(isset($employee->user) && !empty($employee->user->profile))
                                <img id="profileImage" src="{{ Storage::url('emp_profile_images/' . $employee->profile_img) }}" style="width: 80px; height: 90px; border: 1px solid #000; object-fit: cover; margin-right: 10px;" />
                            @else
                                <div id="profileImage" src="{{ Storage::url('emp_profile_images/avatar_1751294680.png') }}" style="width: 70px; height: 90px; border: 1px solid #000; background-color: #f0f0f0; margin-right: 10px;"></div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">CNIC</td>
                        <td></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ $employee->cnic ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">DOB</td>
                        <td></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ !empty($employee->dob) ? \Carbon\Carbon::parse($employee->dob)->format('d-M-Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Designation</td>
                        <td></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{!empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '-'}}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Region</td>
                        <td></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ $employee->area ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Branch</td>
                        <td></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{!empty(\Auth::user()->getBranch($employee->branch_id)) ? \Auth::user()->getBranch($employee->branch_id)->name : '-'}}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Employee No</td>
                        <td></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ $employee->employee_id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Joining Date</td>
                        <td></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ !empty($employee->company_doj) ? \Carbon\Carbon::parse($employee->company_doj)->format('d-M-Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Effective From</td>
                        <td></td>
                        <td style="padding: 3px 8px; font-size: 9px;">
    {{ !empty($lastPayscaleDetail->scale) && !empty($lastPayscaleDetail->scale->effect_from) ? \Carbon\Carbon::parse($lastPayscaleDetail->scale->effect_from)->format('d-M-Y') : '-' }}
</td>

                    </tr>
                </table>

                <!-- Salary Detail Section Header -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                    <tr>
                        <td style="background-color: #d3d3d3; padding: 6px; text-align: center; font-weight: bold; font-size: 11px; border: 1px solid #d3d3d3;">
                            SALARY DETAIL
                        </td>
                    </tr>
                </table>

                <!-- Salary Details Table -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                    <tr>
                        <td style="font-weight: bold; width: 180px; padding: 3px 8px; font-size: 9px;">Pay Scale</td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ $lastPayscaleDetail->scale->scale_no ?? '-' }}</td>
                        @if($employee->status == 'Adhoc')
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Status</td>
                        <td style="padding: 3px 8px; font-size: 9px;">Visiting</td>
                        @else
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Status</td>
                        <td style="padding: 3px 8px; font-size: 9px;">Regular</td>
                        @endif
                    </tr>
                    @php
                        $heads = $lastPayscaleDetail->scale->employeeScaleHeads ?? collect();
                        $basic = $heads->where('head', function_exists('getSalaryHeadId') ? getSalaryHeadId('Initial Basic') : 1)->first();
                        $house = $heads->where('head', function_exists('getSalaryHeadId') ? getSalaryHeadId('House Rent') : 2)->first();
                        $medical = $heads->where('head', function_exists('getSalaryHeadId') ? getSalaryHeadId('Medical Allowance') : 3)->first();
                        $others = $heads->whereNotIn('head', [($basic->head ?? 1), ($house->head ?? 2), ($medical->head ?? 3)]);
                        $gross = $heads->sum('head_value');
                    @endphp
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Basic Salary</td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ number_format($basic->head_value ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">House Rent <span style="font-weight: normal;">(40% of Basic)</span></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ number_format($house->head_value ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Medical Allowance <span style="font-weight: normal;">(10% of Basic)</span></td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ number_format($medical->head_value ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 3px 8px; font-size: 9px;">Others</td>
                        <td style="padding: 3px 8px; font-size: 9px;">{{ number_format($others->sum('head_value'), 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 5px 8px; font-size: 9px;">GROSS SALARY (PM)</td>
                        <td style="padding: 5px 8px; font-size: 9px;">{{!empty($lastPayscaleDetail) ? $lastPayscaleDetail->net + $lastPayscaleDetail->emp_sec : '0' }}</td>
                    </tr>
                </table>

                <div style="width: 100%; border-bottom: 4px solid #d3d3d3; margin: 20px 0;"></div>

                <!-- Contribution Tables Container -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tr>
                        <td style="width: 48%; vertical-align: top; padding-right: 2%;">
                            <!-- Employer's Contribution Table -->
                            <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
                                <tr>
                                    <td colspan="2" style="background-color: #d3d3d3; padding: 6px; text-align: center; font-weight: bold; font-size: 11px; border-top: 1px solid #d3d3d3; border-left: 1px solid #d3d3d3; border-right: 1px solid #d3d3d3;">
                                        Employer's Contribution
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 2px; text-align: center; font-weight: bold; font-size: 11px; border: 1px solid #ffffff; border-bottom: 1px solid #000;">
                                    
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">E.O.B.I <span style="font-weight: normal;">(5% of Minimum Wages)</span></td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($lastPayscaleDetail->eobi_employer ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">P.E.S.S.I <span style="font-weight: normal;">(7% of Minimum Wages)</span></td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($lastPayscaleDetail->pessi_employer ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Child Concession</td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($lastPayscaleDetail->chaild_concession ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Special Allowance</td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($lastPayscaleDetail->misc ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Fuel Allowance</td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">-</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Others</td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">-</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Total Employer Contribution</td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format(($lastPayscaleDetail->eobi_employer ?? 0) + ($lastPayscaleDetail->pessi_employer ?? 0) + ($lastPayscaleDetail->chaild_concession ?? 0) + ($lastPayscaleDetail->misc ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Cost to Company</td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($gross + ($lastPayscaleDetail->eobi_employer ?? 0) + ($lastPayscaleDetail->pessi_employer ?? 0) + ($lastPayscaleDetail->chaild_concession ?? 0) + ($lastPayscaleDetail->misc ?? 0), 2) }}</td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 48%; vertical-align: top; padding-left: 2%;">
                            <!-- Employee's Contribution Table -->
                            <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
                                <tr>
                                    <td colspan="2" style="background-color: #d3d3d3; padding: 6px; text-align: center; font-weight: bold; font-size: 11px; border-top: 1px solid #d3d3d3; border-left: 1px solid #d3d3d3; border-right: 1px solid #d3d3d3;">
                                        Employee's Contribution
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 2px; text-align: center; font-weight: bold; font-size: 11px; border: 1px solid #ffffff; border-bottom: 1px solid #000;">
                                    
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Employee Security <span style="font-weight: normal;">(6% of Earned Basic)</span></td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($lastPayscaleDetail->emp_sec ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">E.O.B.I <span style="font-weight: normal;">(1% of Minimum Wages)</span></td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($lastPayscaleDetail->eobi ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">P.E.S.S.I</td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($lastPayscaleDetail->pessi ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Income Tax <span style="font-weight: normal;">(As per FBR Slab)</span></td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($lastPayscaleDetail->itax ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;"></td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">-</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;"></td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">-</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Total Deduction</td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format(($lastPayscaleDetail->emp_sec ?? 0) + ($lastPayscaleDetail->eobi ?? 0) + ($lastPayscaleDetail->pessi ?? 0) + ($lastPayscaleDetail->itax ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 6px; border: 1px solid #000; font-size: 9px; font-weight: bold;">Take Home Salary</td>
                                    <td style="padding: 4px 6px; border: 1px solid #000; text-align: right; font-size: 9px;">{{ number_format($lastPayscaleDetail->net ?? 0, 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <div style="width: 100%; border-bottom: 4px solid #d3d3d3; margin: 20px 0;"></div>

                <!-- Signature Section -->
                <table style="width: 100%; border-collapse: collapse; margin-top: 30px; padding-top: 80px;">
                    <tr>
                        <td style="width: 33.33%; text-align: center; vertical-align: bottom; padding: 20px 5px;">
                            {{-- <div style="border-bottom: 1px solid #000; height: 30px; margin-bottom: 5px;"></div> --}}
                            <div style="font-size: 9px;">{{ $employee->name ?? '-' }}</div>
                            <div style="font-size: 10px; font-weight: bold;">Employee</div>
                        </td>
                        <td style="width: 33.33%; text-align: center; vertical-align: bottom; padding: 20px 5px;">
                            {{-- <div style="border-bottom: 1px solid #000; height: 30px; margin-bottom: 5px;"></div> --}}
                            <div style="font-size: 9px;">MRS ZERMINA SHEHZAD</div>
                            <div style="font-size: 10px; font-weight: bold;">Executive Director Academics</div>
                        </td>
                        <td style="width: 33.33%; text-align: center; vertical-align: bottom; padding: 20px 5px;">
                            {{-- <div style="border-bottom: 1px solid #000; height: 30px; margin-bottom: 5px;"></div> --}}
                            <div style="font-size: 9px;">MRS MISBAH KHURSHID</div>
                            <div style="font-size: 10px; font-weight: bold;">Managing Director</div>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Transfer Order</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            padding: 15px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 35px;
            font-weight: bold;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .document-title {
            background: #000;
            color: #fff;
            padding: 8px 20px;
            display: inline-block;
            font-weight: bold;
            font-size: 14px;
        }
        
        .separator {
            border-bottom: 1px solid #000;
            margin: 15px 0;
        }
        
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .form-table td {
            padding: 8px 0;
            vertical-align: baseline;
        }
        
        .form-table td.label {
            font-weight: bold;
            width: 140px;
            padding-right: 10px;
        }
        
        .form-table td.input {
            width: 200px;
            padding-right: 30px;
        }
        
        .form-table td.input.wide {
            width: 400px;
        }
        
        .form-table td.input.full {
            width: 500px;
        }
        
        .form-table input {
            border: none;
            border-bottom: 1px solid #000;
            background: transparent;
            font-size: 12px;
            padding: 2px 0;
            width: 100%;
            outline: none;
        }
        
        .terms-box {
            border: 1px solid #000;
            padding: 12px;
            margin: 20px 0;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        
        .signatures td {
            text-align: center;
            width: 33.33%;
            padding: 0 20px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 8px;
        }
        
        .signature-title {
            font-size: 11px;
            font-weight: bold;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .container {
                border: 2px solid #000;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">THE LYNX SCHOOL</div>
            <div class="document-title">Employee Transfer Order</div>
        </div>
        

        <!-- Form Fields -->
        <table class="form-table" style="padding-top: 50px;">
            <!-- Row 1: Date and Employee ID -->
            <tr>
                <td class="label">Date:</td>
                <td class="input">
                    <input type="text" value="{{ $transfer->transfer_date }}">
                </td>
                <td class="label">Employee ID:</td>
                <td class="input">
                    <input type="text" value="{{ $transfer->employee->employee_id ?? '' }}">
                </td>
            </tr>
            
            <!-- Row 2: Name (full width) -->
            <tr>
                <td class="label">Name:</td>
                <td class="input full" colspan="3">
                    <input type="text" value="{{ strtoupper($transfer->employee->name ?? '') }}">
                </td>
            </tr>
            
            <!-- Row 3: Department and Designation -->
            <tr>
                <td class="label">Department:</td>
                <td class="input">
                    <input type="text" value="{{ strtoupper(optional($transfer->department_from)->name ?? '') }}">
                </td>
                <td class="label">Designation:</td>
                <td class="input">
                    <input type="text" value="{{ strtoupper(optional($transfer->employee->designation)->name ?? '') }}">
                </td>
            </tr>
            
            <!-- Row 4: Current Branch and Employee Grade -->
            <tr>
                <td class="label">Current Branch:</td>
                <td class="input">
                    <input type="text" value="{{ strtoupper(optional($transfer->branch_from)->name ?? '') }}">
                </td>
                <td class="label">Employee Grade:</td>
                <td class="input">
                    @php
                        $lastPayscale = optional($transfer->employee->employee_payscale_details)->last();
                        $grade = ($lastPayscale && $lastPayscale->scale) ? $lastPayscale->scale->scale_no : 'N/A';
                    @endphp
                    <input type="text" value="{{ $grade }}">
                </td>
            </tr>
            
            <!-- Row 5: Branch where Transfer is Required (full width) -->
            <tr>
                <td class="label">Branch where Transfer is Required:</td>
                <td class="input full" colspan="3">
                    <input type="text" value="{{ strtoupper(optional($transfer->branch_to)->name ?? '') }}">
                </td>
            </tr>
            
            <!-- Row 6: New Department -->
            <tr>
                <td class="label">New Department:</td>
                <td class="input wide" colspan="3">
                    <input type="text" value="{{ strtoupper(optional($transfer->department_to)->name ?? '') }}">
                </td>
            </tr>
            
            <!-- Row 7: New Designation -->
            <tr>
                <td class="label">New Designation:</td>
                <td class="input wide" colspan="3">
                    <input type="text" value="{{ strtoupper(optional(App\Models\Designation::find($transfer->designation_to_id))->name ?? '') }}">
                </td>
            </tr>
            
            <!-- Row 8: Transfer Date and Reporting Date -->
            <tr>
                <td class="label">Transfer Date:</td>
                <td class="input">
                    <input type="text" value="{{ $transfer->transfer_date }}">
                </td>
                <td class="label">Reporting Date:</td>
                <td class="input">
                    <input type="text" value="{{ $transfer->transfer_date }}">
                </td>
            </tr>
            
            <!-- Row 9: New Address (full width) -->
            <tr>
                <td class="label">New Address:</td>
                <td class="input full" colspan="3">
                    <input type="text" value="{{ $transfer->employee->address ?? '' }}">
                </td>
            </tr>
            
            <!-- Row 10: Phone No and Email -->
            <tr>
                <td class="label">Phone No:</td>
                <td class="input">
                    <input type="text" value="{{ $transfer->employee->phone ?? '' }}">
                </td>
                <td class="label">Email:</td>
                <td class="input">
                    <input type="text" value="{{ $transfer->employee->email ?? '' }}">
                </td>
            </tr>
            
            <!-- Row 11: Reason for Transfer -->
            <tr>
                <td class="label">Reason for Transfer:</td>
                <td class="input wide" colspan="3">
                    <input type="text" value="{{ $transfer->transfer_reason ?? '' }}">
                </td>
            </tr>
        </table>

        <!-- Terms and Conditions -->
        <div class="terms-box">
            I understand that this transfer is subject to completion of all handover procedures, clearance of all company property and assets, and reporting to the new location by the specified date. All employment terms and conditions remain as per the original contract unless specifically modified by this transfer order.
        </div>

        <!-- Signatures -->
        <table class="signatures">
            <tr>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-title">HR Manager</div>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-title">Department Head</div>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-title">General Manager</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Concession Order</title>

</head>

<body > --}}
    <div style="width: 100%; position:relative; bottom: 30px;">
        <div style="float: left; width: 33.33%; text-align: center;">
            {{-- <div class="logo">
                <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
            </div> --}}
        </div>
        <div style="float: left; width: 33.33%; text-align: center;">
            <p style="font-family: Edwardian Script ITC; font-size: 2rem; font-weight: 800;">The Lynx School</p>
            <p style="font-size: 1.5rem; font-weight: 400; text-align: center; margin-top: -25px !important; margin-bottom: 0;">
                Withdrawal Certificate
            </p>
        </div>
        <div style="float: left; width: 33.33%; text-align: center;"></div>
    </div>
    <div style="margin-top: 40px; padding-top: 100px;">
        <table style="width:100%;">
            <tbody>
                <tr>
                    <td colspan="4"> Date: <span style="border-bottom: 1px solid black;  text-align: center; ">
                        {{ $withdrawal ? \Carbon\Carbon::parse($withdrawal->document_date)->format('Y-m-d') : now()->format('Y-m-d') }}
                    </span> </td>
                </tr>
                <tr>
                    <td style="width:50px !important;">Name: </td>
                    <td style="border-bottom: 1px solid black;  text-align: center; width:350px !important; ">{{ $student->stdname ?? '-' }}</td>
                    <td style="width:80px !important; padding-left: 20px;">Roll No:</td>
                    <td style="border-bottom: 1px solid black;  text-align: center;">{{ $student->roll_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="width:50px !important;">Class :</td>
                    <td style="border-bottom: 1px solid black;  text-align: center; width:350px !important;">{{ $class->name ?? '-' }}</td>
                    <td style="width:80px !important; padding-left: 20px;">Section:</td>
                    <td style="border-bottom: 1px solid black;  text-align: center;">{{ $enrollment && $enrollment->section ? $enrollment->section->name : '-' }}</td>
                </tr>
            </tbody>
        </table>
        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-size: 1rem; padding-bottom: 10px;">
                    <span style="margin-right: 10px;">Branch from where withdrawing: </span>
                    <span
                        style="border-bottom: 1px solid black; display: inline-block; width: 440px; padding-right: 10px;">
                        &nbsp;{{ $branch->name ?? '-' }}
                    </span>
                </td>
            </tr>
        </table>

        {{-- <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-size: 1rem; padding-bottom: 10px;">
                    <span style="margin-right: 10px;">New Address: </span>
                    <span
                        style="border-bottom: 1px solid black; display: inline-block; width: 580px; padding-right: 10px;">
                        &nbsp;{{ $student->address ?? '-' }}
                    </span>
                </td>
            </tr>
        </table> --}}

        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-size: 1rem; padding-bottom: 10px;">
                    <span style="margin-right: 10px;"> Phone No: </span>
                    <span
                        style="border-bottom: 1px solid black; display: inline-block; width: 600px; padding-right: 10px;">
                        &nbsp;{{ $student->fatherphone ?? '-' }}
                    </span>
                </td>
            </tr>
        </table>

        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-size: 1rem; padding-bottom: 10px;">
                    <span style="margin-right: 10px;">Reason for Withdrawal: </span>
                    <span
                        style="border-bottom: 1px solid black; display: inline-block; width: 445px; padding-right: 10px;">
                        &nbsp;{{ $withdrawal->reason ?? '-' }}
                    </span>
                </td>
            </tr>
        </table>

        <p>Fee Paid Up to: &nbsp;&nbsp;&nbsp;{{ $lastPaidChallan ? \Carbon\Carbon::parse($lastPaidChallan->fee_month)->format('F-Y') : '-' }}</p>
        <hr>
        <p>I understand that withdrawal is subject to the availability of a seat at the prospective branch, clearance of all my dues, and my son/daughter’s joining the new institution by the stipulated date.</p>
        <table style="width:100%;">
            <tr>
                <td>
                    <p><span style="border-bottom: 1px solid black; padding-right: 100px;">
                            &nbsp;
                        </span></p>
                    <p>Head Of School</p>
                </td>
                <td>
                    <p><span style="border-bottom: 1px solid black; padding-right: 100px;">
                        &nbsp;
                    </span></p>
                    <p>Accountant</p>
                </td>
                <td>
                    <p><span style="border-bottom: 1px solid black; padding-right: 100px;">
                        &nbsp;
                    </span></p>
                    <p>School Stamp</p>
                </td>
            </tr>
        </table>
        <p>Student Record Copy</p>
        <p>Parent Copy</p>
        <p>Branch Copy</p>
    </div>
{{-- </body>

</html> --}}

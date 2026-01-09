{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Concession Order</title>

</head>

<body > --}}
    <div style="margin-top: 40px;">
        {{-- <p style="font-size: 1rem; font-weight: 600; text-align: center;">Transfer Order</p> --}}
        <table style="width:100%;">
            <tbody>
                <tr>
                    <td colspan="4"> Date: <span style="border-bottom: 1px solid black;  text-align: center; ">
                        <?php echo now()->format('Y-m-d'); ?>
                    </span> </td>
                </tr>
                <tr>
                    <td style="width:50px !important;">Name: </td>
                    <td style="border-bottom: 1px solid black;  text-align: center; width:350px !important; ">{{ @$transfer_order->student->stdname }}</td>
                    <td style="width:80px !important; padding-left: 20px;">Roll No:</td>
                    <td style="border-bottom: 1px solid black;  text-align: center;">{{ @$transfer_order->student->roll_no }}</td>
                </tr>
                <tr>
                    <td style="width:50px !important;">Class :</td>
                    <td style="border-bottom: 1px solid black;  text-align: center; width:350px !important; mar">{{ @$transfer_order->classto->name }}</td>
                    <td style="width:80px !important; padding-left: 20px;">Section:</td>
                    <td style="border-bottom: 1px solid black;  text-align: center;">{{ @$transfer_order->sectionto->name }}</td>
                </tr>
            </tbody>
        </table>
        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-size: 1rem; padding-bottom: 10px;">
                    <span style="margin-right: 10px;">Branch where Transfer is Required: </span>
                    <span
                        style="border-bottom: 1px solid black; display: inline-block; width: 440px; padding-right: 10px;">
                        &nbsp;{{ @$transfer_order->branchto->name }}
                    </span>
                </td>
            </tr>
        </table>

        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-size: 1rem; padding-bottom: 10px;">
                    <span style="margin-right: 10px;">New Address: </span>
                    <span
                        style="border-bottom: 1px solid black; display: inline-block; width: 580px; padding-right: 10px;">
                        &nbsp;
                    </span>
                </td>
            </tr>
        </table>

        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-size: 1rem; padding-bottom: 10px;">
                    <span style="margin-right: 10px;"> Phone No: </span>
                    <span
                        style="border-bottom: 1px solid black; display: inline-block; width: 600px; padding-right: 10px;">
                        &nbsp;
                    </span>
                </td>
            </tr>
        </table>

        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-size: 1rem; padding-bottom: 10px;">
                    <span style="margin-right: 10px;">Reason for Transferring the Child: </span>
                    <span
                        style="border-bottom: 1px solid black; display: inline-block; width: 445px; padding-right: 10px;">
                        &nbsp;{{ @$transfer_order->reason }}
                    </span>
                </td>
            </tr>
        </table>

        <p>Fee Paid Up to &nbsp;&nbsp;&nbsp;@if(@$fee_paid[0]['fee_month']) {{ date('F-Y', strtotime(@$fee_paid[0]['fee_month'])) }} @endif</p>
        <hr>
        <p>I understand that transfer is subject to availability of a seat at the prospective branch, clearence of all
            my dues and my son/daughther joinning by the due date.</p>
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

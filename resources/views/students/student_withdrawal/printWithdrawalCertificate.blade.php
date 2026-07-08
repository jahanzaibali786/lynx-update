{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Concession Order</title>

</head>

<body > --}}


<div style="width: 100%; display: table; margin-bottom: 15px;">

    <!-- LEFT SIDE -->
    <div style="display: table-cell; width: 70%; vertical-align: top; text-align: left;">

        <img src="{{ public_path('assets/images/lynxheadertext.jpg') }}" style="width: 320px; height: auto;"
            alt="">

        <p
            style="
            font-size: 22px;
            font-weight: 500;
            margin: 8px 0 0 0;
            letter-spacing: 0.5px;
        ">
            WITHDRAWAL ORDER
        </p>

    </div>

    <!-- RIGHT SIDE -->
    <div style="display: table-cell; width: 30%; vertical-align: top; text-align: right;">

        <img src="{{ public_path('assets/images/lynx2.jpg') }}" style="width: 85px; height: auto;" alt="logo">

        <p style="
            margin: 8px 0 0 0;
            font-size: 13px;
        ">
            <strong>Order No:</strong> {{ $withdrawal->id ?? '-' }}
        </p>

    </div>

</div>
<div style="margin-top: 20px; padding-top: 11px;">
    <table style="width:100%;">
        <tbody>
            <tr>
                <td colspan="4" style="text-align:left; font-weight: bold;">
                    Application Date:
                    <span style="border-bottom: 1px solid black; text-align: center;">
                        {{ $withdrawal->apply_date ? \Carbon\Carbon::parse($withdrawal->apply_date)->format('d-M Y') : '-' }}
                    </span>
                </td>
            </tr>
            <br><br>
            <tr>
                <td style="width:50px !important;">Name: </td>
                <td style="border-bottom: 1px solid black;  text-align: center; width:350px !important; ">
                    {{ $student->stdname ?? '-' }}</td>
                <td style="width:80px !important; padding-left: 20px;">Roll No:</td>
                <td style="border-bottom: 1px solid black;  text-align: center;">{{ $student->roll_no ?? '-' }}</td>
            </tr>
            <tr>
                <td style="width:50px !important;">Class :</td>
                <td style="border-bottom: 1px solid black;  text-align: center; width:350px !important;">
                    {{ $class->name ?? '-' }}</td>
                <td style="width:80px !important; padding-left: 20px;">Section:</td>
                <td style="border-bottom: 1px solid black; text-align: center;">
                    {{ $sectionName ?? optional(optional($enrollment)->section)->name ?? '-' }}
                </td>
            </tr>
        </tbody>
    </table>
    <table width="100%" style="border-collapse: collapse; margin-top: 15px;">
        <tr>
            <td style="font-size: 1rem; padding-bottom: 10px;">
                <span style="margin-right: 10px;">Branch from where withdrawn: </span>
                <span style="border-bottom: 1px solid black; display: inline-block; width: 470px; padding-right: 10px;">
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
            @php
                $rawPhone = preg_replace('/\D/', '', $student->fatherphone ?? '');

                $numbers = [];
                $i = 0;

                while ($i < strlen($rawPhone) && count($numbers) < 2) {
                    // Number starts with 03 → take 11 digits
                    if (substr($rawPhone, $i, 2) === '03') {
                        $num = substr($rawPhone, $i, 11);

                        if (strlen($num) === 11) {
                            $numbers[] = $num;
                        }

                        $i += 11;
                    }

                    // Number starts with 3 → take 10 digits
                    elseif (substr($rawPhone, $i, 1) === '3') {
                        $num = substr($rawPhone, $i, 10);

                        if (strlen($num) === 10) {
                            $numbers[] = $num;
                        }

                        $i += 10;
                    } else {
                        $i++;
                    }
                }

                $formattedPhone = count($numbers) ? implode(' / ', $numbers) : '-';
            @endphp


            <td style="font-size: 1rem; padding-bottom: 10px;">
                <span style="margin-right: 10px;">Phone No:</span>

                <span style="border-bottom: 1px solid black; display: inline-block; width: 605px; padding-right: 10px;">
                    &nbsp;{{ $formattedPhone }}
                </span>
            </td>
        </tr>
    </table>

    <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
        <tr>
            <td style="font-size: 1rem; padding-bottom: 10px;">
                <span style="margin-right: 10px;">Reason for Withdrawal: </span>
                <span style="border-bottom: 1px solid black; display: inline-block; width: 515px; padding-right: 10px;">
                    &nbsp;{{ $withdrawal->reason == 'Other' ? 'Other / ' . ucfirst(strtolower($withdrawal->other_reason ?? '-')) : ucfirst(strtolower($withdrawal->reason ?? '-')) }}
                </span>
            </td>
        </tr>
    </table>
    <p>Fee Paid Up to: &nbsp;&nbsp;&nbsp;<span
            style="border-bottom: 1px solid black; display: inline-block; width: calc(100% - 120px);">
            &nbsp;{{ $lastPaidChallan ? \Carbon\Carbon::parse($lastPaidChallan->fee_month)->format('F-Y') : '-' }}
        </span></p>



    <table style="width:100%;">
        <tr>
            <td style="text-align: left;"><br>
                <p><span style="border-bottom: 1px solid black; padding-right: 100px;">
                        &nbsp;
                    </span></p>
                <p>Headmistress</p>
            </td>
            <td style="text-align: center;"><br>
                <p><span style="border-bottom: 1px solid black; padding-right: 100px;">
                        &nbsp;
                    </span></p>
                <p>Accountant</p>
            </td>
            <td style="text-align: right;"><br>
                <p><span style="border-bottom: 1px solid black; padding-right: 100px;">
                        &nbsp;
                    </span></p>
                <p>School Stamp</p>
            </td>
        </tr>
    </table>

    <h4 style="text-align: center; margin-top: 20px;">ACKNOWLEDGMENT</h4>
    <p>I understand that I willfully withdraw my child from The Lynx School with effective from
        {{ $withdrawal->withdraw_date ? \Carbon\Carbon::parse($withdrawal->withdraw_date)->format('d-M Y') : '-' }}</p>
    <h5>Reactivation Terms:</h5>
    <ol style="margin-left: 20px; justify-content: center;">
        <li>
            If I revert my decision and reactivate my child within 30 days of the withdrawal, I will not pay any extra
            charges in terms of Re-Admission.
        </li>
        <li>
            If I decide to reactivate my child after 30 days but within 90 days from the withdrawal date, I will pay the
            Re-Admission fee and outstanding dues till reactivation month as per the school fee structure in effect at
            that
            time.
        </li>
        <li>
            If I wish to reactivate my child's education with The Lynx School after 90 days from the date of withdrawal,
            the school will treat it as a new admission, and it will be subject to the new fee structure applicable at
            that
            time.
        </li>
    </ol>

    <p>
        I have fully read, understood, and voluntarily agreed to the above terms.
    </p>
</div>

<div style="margin-top: 10px;">
    <p>Parent / Legal Guardian</p><br>
    <table style="width:100%; text-align:center; margin-top: 10px;">
        <tr>
            <td>
                Signature: ________________
            </td>
            <td>
                Name: ______________________
            </td>

            <td>
                Date: __________________
            </td>
        </tr>
    </table>
    {{-- </body>

</html> --}}

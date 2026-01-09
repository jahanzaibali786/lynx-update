<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Concession Order</title>
</head>

<body>
    <div style="line-height:1.2rem;">
        <p style="font-size: 1rem; font-weight: 600; text-align: center;">{{ $student->branches->name }}</p>

        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-size: 1rem;">C.ORD#____Lynx. Accounts</td>
                <td style="text-align: right; font-size: 1rem;">Date:<span
                        style="border-bottom: 1px solid black; padding-right: 100px;">&nbsp;<?php echo now()->format('Y-m-d'); ?>
                    </span></td>
            </tr>
        </table>

        <p>Mr./Mrs.</p>
        <p>Address <span
                style="border-bottom: 1px solid black; padding-right: 200px;">&nbsp;{{ $student->address }}</span></p>
        <p>Contact# <span
                style="border-bottom: 1px solid black; padding-right: 150px;">&nbsp;{{ $student->fathercell ? $student->fathercell : $student->fatherphone }}</span>
        </p>

        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td>Subject:</td>
                <td style="text-align: center; font-size: 1.5rem; font-weight: 800; margin-left:-50px;">CONCESSION ORDER
                </td>
                <td></td>
            </tr>
        </table>

        <p>Dear Sir/Madam,</p>
        <p style="line-height:1.4rem;">This is to inform you that the school administration granted concession to your
            child
            <span style="border-bottom: 1px solid black; padding-right: 100px;">&nbsp;{{ @$student->stdname }}</span>
            Reg# <span
                style="border-bottom: 1px solid black; padding-right: 50px;">&nbsp;{{ @$student->reg_no }}</span>
            Class <span style="border-bottom: 1px solid black; padding-right: 70px;">&nbsp;{{ @$class->name }}</span>
            Session <span
                style="border-bottom: 1px solid black; padding-right: 70px;">&nbsp;{{ @$student->session->year }}</span>
            for the following fee heads.
        </p>

        <table width="100%" style="border-collapse: collapse; text-align: center; margin-top: 20px;">
            <thead>
                <tr>
                    <th></th>
                    <th>FEE HEAD</th>
                    <th>DISCOUNT</th>
                    <th>ACTUAL</th>
                    <th>PAYABLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $dta)
                    @php
                        $head = \App\Models\FeeHead::find($dta->head_id);
                        $classheads = \App\Models\ClassWiseFee::where('class_id', $student->class_id)
                            ->where('head_id', $head->id)
                            ->first();

                        $actualAmount = @$classheads->amount;
                        $discountPercentage = @$dta->percentage;
                        $discountAmount = ($discountPercentage / 100) * $actualAmount;
                        $payableAmount = $actualAmount - $discountAmount;
                    @endphp
                    <tr>
                        <td>=></td>
                        <td>{{ $head->fee_head }}</td>
                        <td>{{ $discountPercentage }}%</td>
                        <td>{{ $actualAmount }}</td>
                        <td>{{ number_format($payableAmount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 20px;">Best regards,</p>
        <br>
        <p>__________________</p>
        <p>Head of Institute</p>
        <p style="font-size: 1rem; font-weight: 600; text-align: center;">ACKNOWLEDGMENT</p>
        <p>I hereby certify that I have thoroughly read the policies and procedures of the institution. I further
            certify that I will pay the monthly tuition and any other applicable fees until the monthly challan
            deadline, if payment is not received, the school has the right to withdraw my kid in line with school rules
            or to impose a late fee. I am also aware that there will be an annual fee that must be paid in January of
            each year, as well as an increase in school charges at the beginning of each new session.</p><br><br><br>
        <table style="width:100%;">
            <tr>
                <td>
                    <p><span
                            style="border-bottom: 1px solid black; padding-right: 100px;">&nbsp;{{ $student->fathername }}</span>
                    </p>
                    <p>Name of Parent</p>
                </td>
                <td>
                    <p>__________________</p>
                    <p>Signature</p>
                </td>
                <td>
                    <p><span style="border-bottom: 1px solid black; padding-right: 100px;">&nbsp;<?php echo now()->format('Y-m-d'); ?>
                        </span></p>
                    <p>Date</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>

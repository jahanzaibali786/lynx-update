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
                <td style="font-size: 1rem;">C.ORD# Lynx. Accounts</td>
                <td style="text-align: right; font-size: 1rem;">w.e.f:<span
                        style="border-bottom: 1px solid black; padding-right: 100px;">&nbsp;<?php echo now()->format('Y-m-d'); ?>
                    </span></td>
            </tr>
        </table>

        <p>Mr./Mrs. <span
                style="border-bottom: 1px solid black; padding-right: 100px;">&nbsp;{{ $student->fathername }}</span>
        </p>
        <p>Address <span
                style="border-bottom: 1px solid black; padding-right: 200px;">&nbsp;{{ $student->address }}</span></p>
        <p>Contact# <span
                style="border-bottom: 1px solid black; padding-right: 150px;">&nbsp;{{ $student->fathercell ? $student->fathercell : $student->fatherphone }}</span>
        </p>

        <p>Subject:</p>
        <p style="text-align: center; font-size: 1.5rem; font-weight: 800; margin: -50px 0 0 0;">CONCESSION ORDER</p>
        <p>Dear Sir/Madam,</p>
        <p style="line-height:1.4rem;">This is to inform you that the school administration granted concession to your
            child
            <span style="border-bottom: 1px solid black; padding-right: 100px;">&nbsp;{{ @$student->stdname }}</span>
            Reg# <span style="border-bottom: 1px solid black; padding-right: 50px;">&nbsp;{{ @$student->reg_no }}</span>
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
        <p>I confirm that I have carefully reviewed the institution's policies and procedures. I further attest that I
            will pay the monthly tuition and any other costs due by the monthly challan date; if I don't, the school has
            the authority to withdraw my child in accordance with school policies or charge a late fee. In addition, I
            am aware that there will be an annual fee that needs to be paid in January of every year, along with an
            increase in school fee at the start of every new session. In addition, I acknowledge that I am responsible
            for paying all other charges, such as study materials, on a yearly basis.</p><br><br><br>
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

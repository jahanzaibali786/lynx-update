<style>
    .remarks-lines {
        margin-top: 10px;
    }

    .remarks-line {
        border-bottom: 1px solid #000;
        height: 35px;
        margin-bottom: 8px;
    }
</style>
<div style="width:100%; font-family: Arial, sans-serif; font-size:12px;">
    <div style="width:650px; margin:0 auto; padding:30px 0;">

        <!-- Header -->
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <!-- Left spacer (same width as logo) -->
                <td style="width:80px;"></td>

                <!-- Centered content -->
                <td style="text-align:center; vertical-align:middle;">
                    <p style="font-family:'Edwardian Script ITC'; font-size:30px; margin:0;">
                        <img src="{{ public_path('assets/images/lynxheadertext.png') }}" style="max-height: 50px;"
                            alt="logo">

                    </p>
                    <p style="margin:4px 0 0; font-size:12px;">
                        {{ @$adm_order->branches->name }}
                    </p>
                    <p style="margin:10px 0 0; font-size:18px;">
                        <strong>ADMISSION ORDER</strong>
                    </p>
                </td>

                <!-- Logo on right -->
                <td style="width:80px; text-align:right; vertical-align:top;">
                    <img src="{{ public_path('assets/images/lynx2.jpg') }}" style="width:80px;">
                </td>
            </tr>
        </table>

        <br><br>

        <!-- Main Fields -->
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:170px; padding:6px 0;">Student Name :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;">&nbsp;{{ $adm_order->stdname }}</td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Father's Name :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;">&nbsp;{{ $adm_order->fathername }}</td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Date of Birth :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;">
                    {{ \Carbon\Carbon::parse($adm_order->dob)->format('d-M-Y') }}</td>
            </tr>


            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Date of Admission :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;">
                    {{ $adm_order->enrollment ? date('d-M-Y', strtotime($adm_order->enrollment->adm_date)) : '' }} </td>

            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0; vertical-align:bottom;">
                    Class to which Admitted :
                </td>

                <td style="vertical-align:bottom; padding:0;">
                    <table style="width:100%; border-collapse:collapse; border-spacing:0;">
                        <tr>
                            <td style="border-bottom:1px solid #000; padding:6px 0; width:60%;">
                                {{ $adm_order->class->name }}
                            </td>

                            {{-- <td style="padding:0; white-space:nowrap; vertical-align:bottom;">
                                Section:
                            </td>

                            <td style="border-bottom:1px solid #000; padding:6px 0; vertical-align:bottom;">
                                {{ @$adm_order->enrollment->section->name }}
                            </td> --}}
                            <td style="padding:0; white-space:nowrap; vertical-align:bottom;">
                                Section:
                            </td>

                            <td style="width:40%; padding:0 0 0 4px; vertical-align:bottom;">
                                <div style="border-bottom:1px solid #000; width:100%; padding-bottom:2px;">
                                    {{ @$adm_order->enrollment->section->name }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Permanent Address :</td>&nbsp;
                <td style="border-bottom:1px solid #000; padding:6px 0;">{{ $adm_order->address }}</td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0; vertical-align:bottom;">
                    Landline No :
                </td>

                <td style="vertical-align:bottom; padding:0;">
                    <table style="width:100%; border-collapse:collapse; border-spacing:0;">
                        <tr>
                            <td style="border-bottom:1px solid #000; padding:6px 0; width:60%;">&nbsp;
                                {{ $adm_order->fatherphone }}
                            </td>

                            {{-- <td style="padding:0; white-space:nowrap; vertical-align:bottom;">
                                Section:
                            </td>

                            <td style="border-bottom:1px solid #000; padding:6px 0; vertical-align:bottom;">
                                {{ @$adm_order->enrollment->section->name }}
                            </td> --}}
                            <td style="padding:0; white-space:nowrap; vertical-align:bottom;">
                                Mobile No :
                            </td>

                            <td style="width:40%; padding:0 0 0 4px; vertical-align:bottom;">
                                <div style="border-bottom:1px solid #000; width:100%; padding-bottom:2px;">&nbsp;
                                    {{ @$adm_order->fathercell }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <br>

        <!-- Roll No: single full-width line -->
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="white-space:nowrap; padding-right:6px; vertical-align:bottom;">Entered in Admission Register
                    and Allotted Roll No :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0; vertical-align:bottom; width:100%;">&nbsp;&nbsp;
                    {{ @$adm_order->enrollment->enrollId }}</td>
            </tr>
        </table>

        <br><br>

        <!-- Remarks (DOMPDF aligned like fields) -->
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:170px; padding:10px 0; position: relative; top: 10px; vertical-align:bottom;">
                    Remarks:
                </td>
                <td style="border-bottom:1px solid #000; padding:-10px 0;">
                    {{ !empty($adm_order->fee_exempt_jun_jul) ? 'Jun-july exempt' : '' }}
                </td>
            </tr>

            <!-- match spacing like other fields -->
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>

            <tr>
                <td></td>
                <td style="border-bottom:1px solid #000; padding:15px 0;"></td>
            </tr>

            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>

            <tr>
                <td></td>
                <td style="border-bottom:1px solid #000; padding:15px 0;"></td>
            </tr>
        </table>

        <br><br>





        <br><br>
        <br><br>
        <br><br>
        <!-- Footer -->
        <table style="width:100%; text-align:center;">
            <tr>
                <td style="width:33%; vertical-align:bottom;">
                    <div style="height:25px; width:150px; margin:0 auto; border-bottom:1px solid #000;"></div>
                    <p style="margin-top:5px;">School Stamp</p>
                </td>

                <td style="width:33%; vertical-align:bottom;">
                    <div
                        style="height:25px; width:150px; margin:0 auto; border-bottom:1px solid #000; line-height:25px;">
                        {{ date('d-M-Y') }}
                    </div>
                    <p style="margin-top:5px;">Print Date</p>
                </td>

                <td style="width:33%; vertical-align:bottom;">
                    <div
                        style="height:25px; width:150px; margin:0 auto; border-bottom:1px solid #000; line-height:25px;">
                        {{ @$adm_order->branch_name->headmaster_name->name ?? '' }}
                    </div>
                    <p style="margin-top:5px;">Head of institute</p>
                </td>
            </tr>
        </table>
        <br><br>
        <br><br>
        <br><br>
        <p style="position:absolute; bottom:10px;;">Copies to : Parents / Personal file / Class Teacher.
        </p>

    </div>
</div>

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
                        The Lynx School
                    </p>
                    <p style="margin:4px 0 0; font-size:12px;">
                        {{ @$adm_order->branches->name }}
                    </p>
                    <p style="margin:10px 0 0; font-size:18px;">
                        Admission Order
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
                <td style="width:170px; padding:6px 0;">Name :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;">{{ $adm_order->stdname }}</td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Date Of Birth :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;">
                    {{ \Carbon\Carbon::parse($adm_order->dob)->format('d-M-Y') }}</td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Father's Name :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;">{{ $adm_order->fathername }}</td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Date of Admission :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;"></td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0; vertical-align:bottom;">Class to which Admitted :</td>
                <td style="vertical-align:bottom; padding:0;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="border-bottom:1px solid #000; padding:6px 0; width:60%;">
                                {{ $adm_order->class->name }}</td>
                            <td style="padding:6px 8px; white-space:nowrap; vertical-align:bottom;">Section :</td>
                            <td style="border-bottom:1px solid #ce0909313; padding:6px 0;">
                                {{ @$adm_order->enrollment->section->name }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Permanent Address :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;">{{ $adm_order->address }}</td>
            </tr>
            <tr>
                <td colspan="2" style="height:8px;"></td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Telephone Number :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0;">{{ $adm_order->fatherphone }}</td>
            </tr>
        </table>

        <br>

        <!-- Roll No: single full-width line -->
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="white-space:nowrap; padding-right:6px; vertical-align:bottom;">Entered in admission Register
                    and Allotted Roll No :</td>
                <td style="border-bottom:1px solid #000; padding:6px 0; vertical-align:bottom; width:100%;">
                    {{ @$adm_order->enrollment->enrollId }}</td>
            </tr>
        </table>

        <br><br>

        <p>Remarks:</p>

        <br><br><br>

        <p>Copies to : Parents / Personal file / Class Teacher / School File.</p>

        <br><br>

        <!-- Footer -->
        <table style="width:100%; text-align:center;">
            <tr>
                <td style="width:33%;">
                    <div style="border-top:1px solid #000; width:150px; margin:0 auto;"></div>
                    <p>School Stamp</p>
                </td>
                <td style="width:33%;">
                    <div style="border-bottom:1px solid #000; width:150px; margin:0 auto; padding:2px 0;">
                        {{ date('Y-m-d') }}</div>
                    <p>Date</p>
                </td>
                <td style="width:33%;">
                    <div style="border-bottom:1px solid #000; width:150px; margin:0 auto; padding:2px 0;">
                        {{ @$adm_order->branch_name->headmaster_name->name ?? '' }}</div>
                    <p>Head of institute</p>
                </td>
            </tr>
        </table>

    </div>
</div>
<div style="width:100%; font-family:Arial, sans-serif; font-size:13px;">
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
                    <br>
                    <p style="margin:4px 0 0; font-size:12px;">
                        {{ @$reg_recipt->branches->name }}
                    </p>

                </td>

                <!-- Logo on right -->
                <td style="width:80px; text-align:right; vertical-align:top;">
                    <img src="{{ public_path('assets/images/lynx2.jpg') }}" style="width:80px;">
                </td>
            </tr>
        </table>
           <br>
        <!-- Reg No and Date -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
            <tr>
                <td style="padding:4px 0; font-size:13px;">Registration No: <span>{{ @$reg_recipt->id }}</span></td>
                <td style="padding:4px 0; font-size:13px; text-align:right;">Date:
                    <span>{{ now()->format('d M Y') }}</span>
                </td>
            </tr>
        </table>
   
          <table style="width:90%; border-collapse:collapse; margin-bottom:10px;">
            <tr>
                 <td style="width:80px;"></td>
                <td style="text-align:center; vertical-align:middle;">
                    <p><strong>Registration Receipt</strong></p>
                </td>
            </tr>
            </table>
           

        <!-- Body -->
        <p style="font-size:13px; line-height:2.2; margin:0;">
            Received Rs <span>&nbsp;{{ @$reg_recipt->registrationfee }}&nbsp;&nbsp;</span> with thanks from Mr.
            &nbsp;&nbsp;&nbsp;<span
                style="border-bottom:1px solid #000; padding-bottom:2px;">&nbsp;{{ @$reg_recipt->fathername }}&nbsp;&nbsp;</span>
            for the
            registration of
            {{ @$reg_recipt->gender == 'male' ? 'his' : 'her' }}
            ward&nbsp;&nbsp;&nbsp;&nbsp;
            <span style="border-bottom:1px solid #000; padding-bottom:2px;">{{ @$reg_recipt->stdname }}</span> of class
            <span
                style="border-bottom:1px solid #000; padding-bottom:2px;">&nbsp;&nbsp;&nbsp;&nbsp;{{ @$reg_recipt->class->name }}&nbsp;&nbsp;&nbsp;&nbsp;</span>
            session
            <span
                style="border-bottom:1px solid #000; padding-bottom:2px;">&nbsp;{{ @$reg_recipt->session->year }}&nbsp;</span>.
        </p>

        <br><br><br><br><br>

        <!-- Signatures -->
        <table style="width:100%; border-collapse:collapse; text-align:center;">
            <tr>
                <td style="width:50%; text-align:left;">
                    <div style="border-bottom:1px solid #000; width:150px; margin:0;">
                        {{ @$reg_recipt->branch_name->headmaster_name->name ?? '' }}</div>
                    <p style="margin:4px 0 0; font-size:13px;">Headmistress</p>
                </td>
                <td style="width:50%; text-align:right;">
                    <div style="border-bottom:1px solid #000; width:150px; margin:0 0 0 auto;">&nbsp;</div>
                    <p style="margin:4px 0 0; font-size:13px; text-align:right;">Accountant</p>
                </td>
            </tr>
        </table>

    </div>
</div>
<br><br><br><br><br><br>
<div style="width:100%; font-family:Arial, sans-serif; font-size:13px;">
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
                    <br>
                    <p style="margin:4px 0 0; font-size:12px;">
                        {{ @$reg_recipt->branches->name }}
                    </p>

                </td>

                <!-- Logo on right -->
                <td style="width:80px; text-align:right; vertical-align:top;">
                    <img src="{{ public_path('assets/images/lynx2.jpg') }}" style="width:80px;">
                </td>
            </tr>
        </table>
           <br>
        <!-- Reg No and Date -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
            <tr>
                <td style="padding:4px 0; font-size:13px;">Registration No: <span>{{ @$reg_recipt->id }}</span></td>
                <td style="padding:4px 0; font-size:13px; text-align:right;">Date:
                    <span>{{ now()->format('d M Y') }}</span>
                </td>
            </tr>
        </table>
   
          <table style="width:90%; border-collapse:collapse; margin-bottom:10px;">
            <tr>
                 <td style="width:80px;"></td>
                <td style="text-align:center; vertical-align:middle;">
                    <p><strong>Registration Receipt</strong></p>
                </td>
            </tr>
            </table>
           

        <!-- Body -->
        <p style="font-size:13px; line-height:2.2; margin:0;">
            Received Rs <span>&nbsp;{{ @$reg_recipt->registrationfee }}&nbsp;&nbsp;</span> with thanks from Mr.
            &nbsp;&nbsp;&nbsp;<span
                style="border-bottom:1px solid #000; padding-bottom:2px;">&nbsp;{{ @$reg_recipt->fathername }}&nbsp;&nbsp;</span>
            for the
            registration of
            {{ @$reg_recipt->gender == 'male' ? 'his' : 'her' }}
            ward&nbsp;&nbsp;&nbsp;&nbsp;
            <span style="border-bottom:1px solid #000; padding-bottom:2px;">{{ @$reg_recipt->stdname }}</span> of class
            <span
                style="border-bottom:1px solid #000; padding-bottom:2px;">&nbsp;&nbsp;&nbsp;&nbsp;{{ @$reg_recipt->class->name }}&nbsp;&nbsp;&nbsp;&nbsp;</span>
            session
            <span
                style="border-bottom:1px solid #000; padding-bottom:2px;">&nbsp;{{ @$reg_recipt->session->year }}&nbsp;</span>.
        </p>

        <br><br><br><br><br>

        <!-- Signatures -->
        <table style="width:100%; border-collapse:collapse; text-align:center;">
            <tr>
                <td style="width:50%; text-align:left;">
                    <div style="border-bottom:1px solid #000; width:150px; margin:0;">
                        {{ @$reg_recipt->branch_name->headmaster_name->name ?? '' }}</div>
                    <p style="margin:4px 0 0; font-size:13px;">Headmistress</p>
                </td>
                <td style="width:50%; text-align:right;">
                    <div style="border-bottom:1px solid #000; width:150px; margin:0 0 0 auto;">&nbsp;</div>
                    <p style="margin:4px 0 0; font-size:13px; text-align:right;">Accountant</p>
                </td>
            </tr>
        </table>

    </div>
</div>
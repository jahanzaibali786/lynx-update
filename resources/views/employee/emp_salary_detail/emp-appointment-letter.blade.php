<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Appointment Letter</title>
</head>

<style>
    .dynamic-content {
        margin: 0;
        padding: 0;
        width: 100%;
        max-width: 100%;
        text-align: justify;
        line-height: 1.7;
        font-size: 14px;
        box-sizing: border-box;

        white-space: normal;
        word-break: keep-all;
        overflow-wrap: normal;
        word-wrap: normal;
    }

    /* reset children */
    .dynamic-content * {
        max-width: 100% !important;
        box-sizing: border-box !important;
        white-space: normal !important;
        word-break: keep-all !important;
        overflow-wrap: normal !important;
        word-wrap: normal !important;
    }

    /* paragraphs same as other content */
    .dynamic-content p,
    .dynamic-content div {
        margin: 0 0 8px 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    /* LISTS FIXED */
    .dynamic-content ol,
    .dynamic-content ul {
        margin: 0 0 8px 22px !important;
        /* same left alignment */
        padding: 0 !important;
        width: auto !important;
        /* prevent pushing away */
    }

    .dynamic-content li {
        margin: 0 0 8px 0 !important;
        padding: 0 !important;
        line-height: 1.6 !important;
        width: auto !important;
        display: list-item !important;
    }

    /* table/image */
    .dynamic-content table {
        width: 100% !important;
    }

    .dynamic-content img {
        max-width: 100% !important;
        height: auto !important;
    }

    /* SAME FONT FOR WHOLE FILE EXCEPT FOOTER */
    body :not(.footer):not(.footer *) {
        font-family: Arial, sans-serif !important;
        font-size: 14px !important;
        font-weight: normal;
        color: #000;
    }

    /* heading only bigger */
    h2 {
        font-size: 22px !important;
        font-weight: bold !important;
    }

    /* dynamic content */
    .dynamic-content {
        margin: 0;
        padding: 0;
        width: 100%;
        max-width: 100%;
        text-align: justify;
        line-height: 1.7;
        box-sizing: border-box;
        white-space: normal;
        word-break: keep-all;
        overflow-wrap: normal;
        word-wrap: normal;
    }

    .dynamic-content * {
        font-family: Arial, sans-serif !important;
        font-size: 14px !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        white-space: normal !important;
        word-break: keep-all !important;
        overflow-wrap: normal !important;
        word-wrap: normal !important;
    }

    .dynamic-content p,
    .dynamic-content div {
        margin: 0 0 8px 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    .dynamic-content ol,
    .dynamic-content ul {
        margin: 0 0 8px 18px !important;
        padding: 0 !important;
    }

    .dynamic-content li {
        margin: 0 0 8px 0 !important;
        line-height: 1.7 !important;
    }

    .dynamic-content img {
        max-width: 100% !important;
        height: auto !important;
    }

    .para-row {
        display: table;
        width: 100%;
        margin: 0 0 12px 0;
        line-height: 1.8;
    }

    .para-row .num {
        display: table-cell;
        width: 21px;
        vertical-align: top;
        text-align: left;
    }

    .para-row .text {
        display: table-cell;
        vertical-align: top;
        text-align: justify;
    }

    .para-row.no-num .num {
        visibility: hidden;
    }
    .duty-report-section{
    page-break-inside: avoid !important;
    break-inside: avoid !important;
}

.duty-report-section table{
    page-break-inside: avoid !important;
    break-inside: avoid !important;
}

.duty-report-section tr,
.duty-report-section td,
.duty-report-section div,
.duty-report-section p{
    page-break-inside: avoid !important;
    break-inside: avoid !important;
}

/* heading bar */
.report-heading{
    background:#d9d9d9;
    padding:10px;
    text-align:center;
    font-weight:bold !important;
}
</style>

<body style="font-family:Arial, sans-serif; font-size:14px; margin:0; padding:0;">

    <div style="width:100%; max-width:640px; margin:0 auto; padding:0 20px; box-sizing:border-box;">
        <div>
            <!-- Heading -->
            <h2
                style="text-transform:uppercase; text-align:center; margin-top:20px; margin-bottom:5px; position:relative; left:-18px;">
                Appointment Letter
            </h2>

            <hr
                style="width:260px; height:2px; background:#000; border:none; margin:0 auto 25px auto; position:relative; left:-18px;">
        </div>
        <!-- Main Content -->
        <div style="text-align:justify; line-height:1.8;">

            <p>
                &nbsp;&nbsp; &nbsp;&nbsp; Dear &nbsp;&nbsp; &nbsp;&nbsp;
                <span
                    style="display:inline-block; width:280px; border-bottom:1px solid #000; text-align:left; padding-left:0;">
                    {!! @$employee->salute !!} {!! @$employee->name !!}
                </span>
            </p>

            <p class="para-row no-num">
                <span class="num">0</span>
                We are pleased to appoint you as
                <span style="display:inline-block; width:200px; border-bottom:1px solid #000; text-align:center;">
                    {!! @$employee->designation->name !!}
                </span>
                in <b><i>The Lynx School</i></b> with effect from
                <span style="display:inline-block; width:120px; border-bottom:1px solid #000; text-align:center;">
                    20-03-2024
                </span>
                on the following terms and conditions.
            </p>

            <!-- USE THIS HTML STRUCTURE FOR NUMBERED PARAGRAPHS -->

            
            <p class="para-row">
                <span class="num">1</span>
                <span class="text">
                    You will be entitled to a basic salary of
                    <span style="display:inline-block; width:135px; border-bottom:1px solid #000; text-align:center;">
                        Rs {{ number_format(!empty($lastPayscaleDetail) ? $lastPayscaleDetail->resolved_basic_salary : 0, 2) }}
                    </span>
                    per month and the detail of other benefits applicable to your category of employee are given in
                    Annexure "A".
                </span>
            </p>

            <p class="para-row">
                <span class="num">2</span>
                <span class="text">
                    You will be on probation for the period of
                    <span style="display:inline-block; width:65px; border-bottom:1px solid #000; text-align:center;">
                        {!! @$employee->probation_period !!}
                    </span>
                    months from date of your joining. The probation period may be extended for such term as may be
                    considered appropriate by the Management. Upon satisfactory completion of your probation, your
                    services will be confirmed in writing with the organization.
                </span>
            </p>

            <!-- Dynamic Content -->
            <div class="dynamic-content">
                {!! preg_replace('/\x{00A0}/u', ' ', str_replace('&nbsp;', ' ', $appointmentletterdata->datacontent)) !!}
            </div>

            <!-- Signatures -->
            <table width="100%" style="margin-top:40px;">
                <tr>
                    <!-- LEFT SIDE -->
                    <td width="48%" valign="top" style="text-align:left;">
                        Yours faithfully,<br><br>

                        <div style="width:220px; text-align:center;">
                            <div style="height:20px;">
                                <b style="text-transform:uppercase; font-weight:bold;">
                                    {{ @$employee->master->headmaster_name->name }}
                                </b>
                            </div>

                            <div style="margin-top:8px;">
                                Head of Department
                            </div>
                        </div>
                    </td>

                    <!-- RIGHT SIDE -->
                    <td width="48%" valign="top" style="text-align:right; padding-right:10px;">
                        <br><br>

                        <div style="width:220px; text-align:center; margin-left:auto;">
                            <div style="height:20px; text-transform:uppercase; font-weight:bold;">
                                {!! @$employee->name !!}
                            </div>

                            <div style="margin-top:8px;">
                                Employee
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

        </div>

        <!-- Duty Joining Report -->
        <br><br>

        <div class="duty-report-section">

            <div class="report-heading">
                DUTY JOINING REPORT
            </div>

            <div style="margin-top:20px; line-height:2; font-size:14px;">

                <p>
                    I have read the term and conditions of this letter of appointment and confirm my acceptance.
                </p>

                <table width="100%" style="font-size:14px;">
                    <tr>
                        <td width="30%">Name :</td>
                        <td>{!! @$employee->name !!}</td>
                    </tr>

                    <tr>
                        <td>CNIC :</td>
                        <td>{!! @$employee->cnic !!}</td>
                    </tr>

                    <tr>
                        <td>Date of Joining :</td>
                        <td>{!! \Carbon\Carbon::parse(@$employee->company_doj)->format('d-F-Y') !!}</td>
                    </tr>

                    <tr>
                        <td>Address :</td>
                        <td>{!! @$employee->present_address !!}</td>
                    </tr>

                    <tr>
                        <td>Mobile No :</td>
                        <td>{!! @$employee->phone !!}</td>
                    </tr>

                    <tr>
                        <td>Home No :</td>
                        <td>{!! @$employee->phone !!}</td>
                    </tr>
                </table>

                <div style="text-align:right; margin-top:60px;">
                    <span style="display:inline-block; width:200px; border-bottom:1px solid #000;"></span><br>
                    Employee Signature / Date
                </div>

            </div>

        </div>

    </div>

</body>

</html>

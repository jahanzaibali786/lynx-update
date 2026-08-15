<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Appointment Letter</title>
</head>

<style>
    body,
    body table,
    body tr,
    body td,
    body div,
    body p,
    body span,
    body li,
    body ul,
    body ol {
        font-family: Arial, sans-serif !important;
        font-size: 14px !important;
        color: #000 !important;
        letter-spacing: 0 !important;
    }

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

    .dynamic-content > table.appointment-clause,
    .dynamic-content > table.appointment-indent {
        margin-left: 0 !important;
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

    .appointment-clause-number {
        width: 45px !important;
        min-width: 45px !important;
        max-width: 45px !important;
        vertical-align: top !important;
        text-align: left !important;
        padding: 0 5px 0 0 !important;
        margin: 0 !important;
        font-weight: 600 !important;
        white-space: nowrap !important;
    }

    .appointment-clause-text {
        width: auto !important;
        vertical-align: top !important;
        text-align: justify !important;
        padding: 0 !important;
        margin: 0 !important;
        line-height: 1.8 !important;
    }

    .appointment-clause-text p,
    .appointment-clause-text div {
        margin: 0 !important;
        width: 100% !important;
    }

    .appointment-clause-text ul,
    .appointment-clause-text ol {
        margin: 8px 0 8px 18px !important;
    }

    .dynamic-content h1,
    .dynamic-content h2,
    .dynamic-content h3,
    .dynamic-content h4,
    .dynamic-content h5,
    .dynamic-content h6 {
        font-family: Arial, sans-serif !important;
        color: #000 !important;
        margin: 0 0 12px 0 !important;
        line-height: 1.3 !important;
    }

    .dynamic-content h1 {
        font-size: 22px !important;
    }

    .dynamic-content h2 {
        font-size: 20px !important;
    }

    .dynamic-content h3 {
        font-size: 18px !important;
    }

    .dynamic-content strong,
    .dynamic-content b {
        font-weight: 700 !important;
    }

    .appointment-inline-field {
        display: inline-block !important;
        min-width: 50px !important;
        padding: 0 15px 1px 15px !important;
        border-bottom: 1px solid #000 !important;
        line-height: 1.2 !important;
        vertical-align: baseline !important;
    }

    .appointment-inline-field-bold {
        font-weight: 700 !important;
    }

    .dynamic-content u {
        text-decoration: underline !important;
    }

    .dynamic-content mark {
        padding: 0 2px !important;
    }

    .dynamic-content span[style*="background-color"],
    .dynamic-content mark,
    .dynamic-content .marker-yellow,
    .dynamic-content .marker-green,
    .dynamic-content .marker-pink,
    .dynamic-content .marker-blue {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
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
    page-break-before: always !important;
    break-before: page !important;
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

.appointment-letter-heading {
    text-align: center;
    font-family: Arial, sans-serif !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    text-decoration: underline;
    margin: 16px 0 18px 0;
}

.appointment-letter-meta {
    width: 100%;
    margin: 0 0 22px 0;
}

.appointment-letter-meta-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.appointment-letter-date {
    text-align: right;
    vertical-align: top;
    width: 36%;
}

.appointment-letter-date span {
    display: inline-block;
    padding: 0;
    font-weight: 700;
    text-decoration: underline;
}

.appointment-letter-dear {
    vertical-align: top;
    width: 64%;
    font-weight: 700;
    padding-right: 12px;
}

.appointment-letter-dear-label {
    display: inline-block;
    margin-bottom: 3px;
}

.appointment-letter-dear-line {
    display: inline-block;
    min-width: 180px;
    padding: 0 5px 1px 5px;
    border-bottom: 1px solid #000;
    position: relative;
    top: -2px;
    vertical-align: baseline;
}
</style>

<body style="font-family:Arial, sans-serif; font-size:14px; margin:0; padding:0; color:#000;">

    <div style="width:100%; margin:0 auto; padding:0 20px; box-sizing:border-box;">
        <div class="appointment-letter-heading">
            {{ __('Appointment Letter') }}{{ !empty($appointmentletterdata?->type) ? ' (' . strtoupper($appointmentletterdata->type) . ')' : '' }}
        </div>

        <div style="width:100%; max-width:640px; margin:0 auto;">
            <!-- Main Content -->
            <div style="font-family:Arial, sans-serif; font-size:14px; text-align:justify; line-height:1.8; color:#000;">

                <div class="appointment-letter-meta">
                    <table class="appointment-letter-meta-table">
                        <tr>
                            <td class="appointment-letter-dear">
                                <span class="appointment-letter-dear-label">{{ __('Dear    ') }}</span>
                                <span class="appointment-letter-dear-line">
                                    {{ strtoupper((string) ($employee->name ?? '')) }}
                                </span>
                            </td>
                            <td class="appointment-letter-date">
                                <span>
                                    {{ __('Dated:') }} {{ \Carbon\Carbon::parse($appointmentletterdata->date ?? $employee->company_doj ?? now())->format('d-M-Y') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Dynamic Content -->
                <div class="dynamic-content">
                    {!! $appointmentLetterContent ?? $appointmentletterdata->datacontent !!}
                </div>

                <!-- Signatures -->
                <table width="100%" style="margin-top:40px;">
                    <tr>
                        <!-- LEFT SIDE -->
                        <td width="48%" valign="top" style="text-align:left;">
                            Yours faithfully,<br><br><br><br><br><br>

                            <div style="width:220px; text-align:center;">
                                <div style="height:20px;">
                                    <b style="text-transform:uppercase; font-weight:bold;">
                                        {{ @$employee->master->headmaster_name->name }}
                                    </b>
                                </div>

                                <div style="margin-top:8px;">
                                   {{ @$employee->master->headmaster_designation->designation->name }}
                                </div>
                            </div>
                        </td>

                        <!-- RIGHT SIDE -->
                        <td width="48%" valign="top" style="text-align:right; padding-right:10px;">
                            <br><br><br><br><br><br>

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

                <div style="margin-top:20px; line-height:2; font-size:14px; font-family:Arial, sans-serif; color:#000;">

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
    </div>

</body>

</html>

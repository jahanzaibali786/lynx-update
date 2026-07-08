<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Clearance Certificate</title>
</head>

<body>

    <div style="margin:0 auto; font-family: DejaVu Sans, sans-serif; font-size:12px;">

        <div style="border:1px solid #000; padding:25px;">

            <!-- HEADER -->
            <div style="text-align:center; position:relative;">

                <div style="font-size:26px; font-weight:bold; font-family: Edwardian Script ITC, cursive;">
                    The Lynx School
                </div>

                 <div style="font-size:12px; margin-top:5px;">
                        {{isset(request()->branches) ? $branches[request()->branches] : ''}}
                    </div>

                <img src="{{ public_path('assets/images/lynx2.jpg') }}" style="width:70px; position:absolute; right:0; top:0;">
            </div>

            <!-- TITLE -->
            <div style="margin-top:15px; background:#7d7d7d; text-align:center; letter-spacing:6px; padding:6px;">
                STUDENT CLEARANCE CERTIFICATE
            </div>

            <!-- DATE -->
            <div style="margin-top:12px; text-align:right;">
                Date:
                <span style="display:inline-block; border-bottom:1px solid #000; width:140px; text-align:center;">
                    {{ $date ? \Carbon\Carbon::parse($date)->format('d-M-Y') : '' }}
                </span>
            </div>

            <!-- STUDENT INFO -->
            <div style="margin-top:18px; line-height:24px;">
                This is to certify that

                <span style="display:inline-block; border-bottom:1px solid #000; width:200px; text-align:center;">
                    {{ @$studentDetail->StudentRegistration->stdname ?? '' }}
                </span>

                Roll No.

                <span style="display:inline-block; border-bottom:1px solid #000; width:100px; text-align:center;">
                    {{ @$studentDetail->enrollId ?? '' }}
                </span>

                of class

                <span style="display:inline-block; border-bottom:1px solid #000; width:150px; text-align:center;">
                    {{ @$studentDetail->class->name ?? '' }}
                </span>
            </div>

            <!-- SECTION -->
            <div style="margin-top:8px;">
                Section

                <span style="display:inline-block; border-bottom:1px solid #000; width:60px; text-align:center;">
                    {{ @$studentDetail->section->name ?? '' }}
                </span>

                has cleared the following dues
            </div>

            <!-- LIST (ALIGNED PERFECTLY) -->
            <div style="margin-top:20px;">

                <div style="margin-bottom:10px;">
                    <span style="display:inline-block; width:220px;">1. Library</span>
                    <span style="display:inline-block; border-bottom:1px solid #000; width:300px;"></span>
                </div>

                <div style="margin-bottom:10px;">
                    <span style="display:inline-block; width:220px;">2. Class and School Collection</span>
                    <span style="display:inline-block; border-bottom:1px solid #000; width:300px;"></span>
                </div>

                <div>
                    <span style="display:inline-block; width:220px;">3. Laboratory</span>
                    <span style="display:inline-block; border-bottom:1px solid #000; width:300px;"></span>
                </div>

            </div>

            <!-- TUITION -->
            <div style="margin-top:25px;">
                Tuition fee paid up to

                <span style="display:inline-block; border-bottom:1px solid #000; width:220px; text-align:center;">
                    {{ @$lastchallan ? \Carbon\Carbon::parse($lastchallan->fee_month)->format('F Y') : '' }}
                </span>

                &nbsp;&nbsp; Security deposit Rs.

                <span style="display:inline-block; border-bottom:1px solid #000; width:100px; text-align:center;">
                    {{ $security }}
                </span>
            </div>

            <!-- PAID DATE -->
            <div style="margin-top:18px;">
                Paid Date

                <span style="display:inline-block; border-bottom:1px solid #000; width:200px; text-align:center;">
                    {{ @$lastchallan ? \Carbon\Carbon::parse($lastchallan->paid_date)->format('d-M-Y') : '' }}
                </span>

                attended the school till

                <span style="display:inline-block; border-bottom:1px solid #000; width:200px;"></span>
            </div>

            <!-- SIGNATURES -->
            <div style="margin-top:120px;">

                <div style="width:48%; display:inline-block; text-align:center;">
                    <div style="border-top:1px solid #000; width:260px; margin:0 auto;"></div>
                    Office Assistant
                </div>

                <div style="width:48%; display:inline-block; text-align:center;">
                    <div style="border-top:1px solid #000; width:260px; margin:0 auto;"></div>
                    Head of the Institution
                </div>

            </div>

        </div>
    </div>
</body>

</html>

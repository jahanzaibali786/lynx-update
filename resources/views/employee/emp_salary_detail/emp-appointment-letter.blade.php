<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <title>Document</title> -->
</head>

<body>

    {{-- @php
        $lastPayscaleDetail = $employee->employee_payscale_details->last();
    @endphp --}}
    <h2 style="text-transform:uppercase; position:relative; left:31%; top:20px;">Appointment Letter</h2>
    <hr style="width:300px; height:2px; background-color:black; margin-left:30%;">
    <div class="">
        <p>Dear <span style="margin-left:70px;">{!!@$employee->salute!!}. {!!@$employee->name!!}</span>
            <hr style="position:relative; top: -18px;; left:-150px; width:300px; background-color:black;">
        </p>
        <p style="margin-left:30px;">We are pleased to appoint you as<span
                style="margin-left:70px;">{!!@$employee->designation->name!!} </span>
            <hr style="position:relative; top: -18px; left:20px; width:250px; background-color:black;"><span
                style="position:relative; right:-72%; top:-45px;"> in <b><i>The lynx School</i></b> with</span>
        </p>

        <p style="margin-top: -30px; margin-left:25px;">effect from <span style="margin-left:25px;">20-03-2024</span>
            <hr style="position:relative; top: -18px; left:-27%; width:100px; background-color:black;"><span
                style="position:relative; right:-30%; top:-45px;">on the following terms and conditions</span>
        </p>
        <hr style="position:absolute; left:27px; width:5px; height:5px; border-radius:50px; background-color:black;">
        </hr>
        <p style="margin-top: -25px; margin-left:40px;"> You will be entitled to a basic salary of <span
                style="margin-left:5px;">Rs {!!(@$lastPayscaleDetail->net + @$lastPayscaleDetail->emp_sec)!!}</span>
            <hr style="position:relative; top: -10px; width:100px; background-color:black;"><span
                style="position:relative; right:-58%; top:-40px;"> per month and the detail of other benefits</span>
            <span style="position:relative; left:-32%; top:-20px;">applicable to your category of employee, are given in
                Annexure"A" .</span>
        </p>
        <hr style="position:absolute; left:27px; width:5px; height:5px; border-radius:50px; background-color:black;">
        </hr>
        <p style="margin-left:40px; margin-top:-15px;">You will be on probation for the period of <span
                style="margin-left:5px;">{!!@$employee->probation_period!!}</span>
            <hr style="position:relative; top: -15px; left:-3%; width:40px; background-color:black;"><span
                style="position:relative; right:-50%; top:-50px;">months from date of your joining . The
                probation</span>
        <p style="position:relative; left:6%; top:-45px;">period may be extended for such term as may be considered
            appropriate by the Management, Upon satisfactory completion of your probation, your services will be
            confirmed by written / understood <br> with the organization.</p>
        </p>
        <div style="width:665px !important; text-align:justify; margin-left:5px; margin-top:-60px;">
            {!!$appointmentletterdata->datacontent!!}</div>

        <p style="margin-left:40px;">Yours faithfully ,</p>
        <div style="position:relative; left:-155px; top:50px;">
            <p style="text-transform:uppercase; text-align:center;"><b>{{@$employee->master->headmaster_name->name}}</b> <br> <span
                    style="font-size:1.1rem;">Head of Department</span></p>
        </div>
        <div style="position:relative; left:200px; top:-30px;">
            <p style="text-transform:uppercase; text-align:center; margin-left:90px;">{!!@$employee->name!!}<span
            style="font-size:1.1rem; position:relative; top:50px; left:-140px;">Employee</span><br>
                <hr style="width:200px; background-color:black;">
            </p>
        </div>
    </div><br><br><br>
    <div style="background-color:rgb(190, 186, 186);">
        <p style="text-align:center; padding:10px; font-size:1rem; background-color:rgb(190, 186, 186);">DUTY JOINING REPORT</p>
    </div>
    <div>
        <p>I have read the term and conditions of this letter of appointment and confirm my acceptance.</p><br>

        <p>Name : <span style="margin-left:160px;">{!!@$employee->name!!}</span> </p>
        <p>CNIC : <span style="margin-left:160px;">{!!@$employee->cnic!!}</span> </p>
        <p>Date of Joining : <span
                style="margin-left:100px;">{!! \Carbon\Carbon::parse(@$employee->company_doj)->format('d-F-Y') !!}</span>
        </p>
        <p>Address : <span style="margin-left:150px;">{!!@$employee->present_address!!}</span> </p>
        <p>Mobile No : <span style="margin-left:135px;">{!!@$employee->phone!!}</span> </p>
        <p>Home No : <span style="margin-left:140px;">{!!@$employee->phone!!}</span> </p>
        <div style="position:relative; left:200px; top:-30px;">
            <p style="text-transform:uppercase; text-align:center;"><br>
                <hr style="width:200px; background-color:black;"><br> <span
                    style="font-size:1rem; position:relative; left:270px; top:-28px;">Employee Signature / Date </span>
            </p>
        </div>
    </div>
</body>

</html>
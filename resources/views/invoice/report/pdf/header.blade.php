{{-- <div style="width: 100%; position:relative; bottom: 30px;">
    <div style="float: left; width: 33.33%; text-align: center;">
        <div class="logo">
            <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
        </div>
    </div>
    @dd($concessions)
    @foreach ($concessions as $report)
    <div style="float: left; width: 33.33%; text-align: center; ">
        <p style="font-family:Edwardian Script ITC; font-size:2rem; text-align: center; font-weight: 800;">The Lynx
            School
            {{ @$brnches->name ?? 'All Branches'}}
        </p>
    </div>
    @endforeach
    <div style="float: left; width: 33.33%; text-align: center;">{{ @$brnches->name ?? 'All Branches'}}</div>
</div> --}}
<div style="width: 100%; position: relative; bottom: 30px; display: table;">
    <div style="display: table-cell; width: 20%; text-align: center; vertical-align: middle;">
        <div class="logo">
            <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
        </div>
    </div>
    <div style="display: table-cell; width: 5%; text-align: center; vertical-align: middle;">

    </div>
    <div style="display: table-cell; width: 60%; text-align: center; vertical-align: middle;">
        <h4 style="font-size: 2rem; font-weight: 800; margin: 0;">The Lynx
            School</h4>
        <p style="font-family: 'Edwardian Script ITC'; text-align: center; margin: 0;">
            @if(@$report_name){{ @$report_name  }} <br>@endif
            @if(@$store_rep){{$store_rep}}@else{{ @$brnches_name->name ?? 'All Branches' }}@endif
        </p>
    </div>
    <div style="display: table-cell; width: 15%; text-align: center; vertical-align: middle;">

    </div>
</div>
<div style="width: 100%; position: relative; bottom: 30px; display: table; margin: 10px 0;">
    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
        <p style="margin: 0; text-align:left;"><b>Period From:
            </b>{{ date('d M Y', strtotime($request->input('start_date'))) }}</p>
    </div>
    <div style="display: table-cell; width: 75%; text-align: center; vertical-align: middle;">
    </div>
    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
        <p style="margin: 0; text-align:right;"><b>Period To:
            </b>{{ date('d M Y', strtotime($request->input('end_date'))) }}</p>
    </div>
</div>
<div></div>

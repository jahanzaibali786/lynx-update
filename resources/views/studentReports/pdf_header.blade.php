<div style="width: 100%; position: relative; bottom: 80px; display: table; position:relative; left:-70px;">
    <div style="display: table-cell; width: 70%; text-align: left; vertical-align: left;">
        <div class="logo">
            <img src="{{ asset('assets/images/lynxheadertext.jpg') }}"
            style=" height: 75px; width:400px !important; max-height: 90px; width: auto; margin-left:80px; margin-top:20px;"
            alt="logo">
        </div>
    </div>
    <div style="display: table-cell; width: 30%; text-align: right; vertical-align: right;">
        <div class="logo">
            <img src="{{ asset('assets/images/graylynx.png') }}" style="max-width: 100px; max-height: 100px; margin-left:80px; filter: grayscale(100%) !important;"
                alt="logo">
        </div>
    </div>
</div>

<div style="width: 100%; position: relative; bottom: 60px; display: table; position:relative;">
    <div style="display: table-cell; width: 100%; text-align: center; vertical-align: left;">
        <p class="branchname" style="text-align: left; margin: 0; font-size:2rem !important; font-family:Arial, Helvetica, sans-serif !important;">
            @if (@$branch)
                {{ $branch }}
            @else
                {{'All Branches' }}
            @endif
        </p>
    </div>
</div>



<div style="width: 100%; position: relative; bottom: 60px; display: table; position:relative;">
    <div style="display: table-cell; width: 100%; text-align: center; vertical-align: left;">
        <p style="text-align: left;  font-size: 2.3rem; font-weight:bolder; font-family:Arial, Helvetica, sans-serif !important;">{{ @$report_name }}</p>
    </div>
</div>
@php
    $periods = request()->input('periods');
@endphp 
@if ($periods)
    <div style="display:flex; justify-content: space-between; align-items: center; font-size: 1rem;">
        <div class="flex-grow-1">
            <p style="text-align:left;"><b>Period From:
                </b>{{ date('d M Y', strtotime($request->input('date_from') ? $request->input('date_from') : now()->startOfYear())) }}
            </p>
        </div>
        <div class="flex-grow-1">
            <p style="text-align:center;"><b>
                </b>{{ @$report }}</p>
        </div>
        <div class="flex-grow-1">
            <p style="text-align:right;"><b>Period To:
                </b>{{ date('d M Y', strtotime($request->input('date_to') ? $request->input('date_to') : now())) }}</p>
        </div>
    </div>
@endif

{{-- <div style="width: 100%; position: relative; bottom: 30px; display: table; margin: 20px 0;" id="periodtext">
    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
        <p style="margin: 0; text-align:left;"><b>Period From:
            </b>{{ date('d M Y', strtotime($request->input('date_from'))) }}</p>
    </div>
    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
        <p style="margin: 0; text-align:center;"><b>
            </b>{{ @$report }}</p>
    </div>
    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
        <p style="margin: 0; text-align:right;"><b>Period To:
            </b>{{ date('d M Y', strtotime($request->input('date_to'))) }}</p>
    </div>

</div> --}}

<div style="width: 100%; position: relative; bottom: 30px; display: table;">
    <div style="display: table-cell; width: 20%; text-align: center; vertical-align: middle;">
        <div class="logo">
            <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
        </div>
    </div>
    <div style="display: table-cell; width: 5%; text-align: center; vertical-align: middle;">

    </div>
    <div style="display: table-cell; width: 60%; text-align: center; vertical-align: middle;">
        <img class="lynxtextimg" style="width: 70%;" src="{{asset('assets/images/lynxheadertext.png')}}" alt="The Lynx School"><br>
        <p  style="font-family: 'Edwardian Script ITC'; text-align: center; margin: 0; font-size: 1.5rem;">
            {{ @$report_name  }}</p>
            <p class="branchname">
            @isset($_GET['branches'])
                {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
            @endisset
            </p>
            {{-- {{ @$brnches_name->name ?? 'All Branches' }}</p> --}}
    </div>
    <div style="display: table-cell; width: 15%; text-align: center; vertical-align: middle;">

    </div>
</div>
@if(isset($request))
<div style="width: 100%; position: relative; bottom: 30px; display: table; margin: 20px 0;">
    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
        <p style="margin: 0; text-align:left;"><b>Period From:
            </b>{{ date('d M Y', strtotime($request->input('datefrom'))) }}</p>
    </div>
    <div style="display: table-cell; width: 75%; text-align: center; vertical-align: middle;">
    </div>
    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
        <p style="margin: 0; text-align:right;"><b>Period To:
            </b>{{ date('d M Y', strtotime($request->input('dateto'))) }}</p>
    </div>
</div>
@endif
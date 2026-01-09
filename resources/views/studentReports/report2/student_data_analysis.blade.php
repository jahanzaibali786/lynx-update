@extends('layouts.admin')
@section('page-title')
    {{ __('Student Data Analysis') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Data Analysis') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['student_data_analysis'], 'method' => 'GET', 'id' => 'student_data_analysis']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('years', __('Periods (years back)'), ['class' => 'form-label']) }}
                                    {{ Form::select(
                                        'years',
                                        [
                                            1 => 'Last One Year',
                                            2 => 'Last Two Years',
                                            3 => 'Last Three Years',
                                            4 => 'Last Four Years',
                                            5 => 'Last Five Years',
                                        ],
                                        request()->get('years', 2),
                                        ['class' => 'form-control select', 'id' => 'years'],
                                    ) }}
                                </div>
                            </div>
                            <div
                                class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-2 align-items-center">
                                <!-- Search Button -->
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('student_data_analysis').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_data_analysis') }}" class="btn btn-sm btn-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <!-- Actions Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                                <button class="dropdown-item" type="submit" name="export" value="excel">
                                                    <i class="ti ti-file me-2"></i>Excel
                                                </button>
                                        </li>
                                        <li>
                                                <button class="dropdown-item" type="submit" name="print" value="pdf">
                                                    <i class="ti ti-download me-2"></i>Pdf
                                                </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-2 p-4" style="width: 100%;">
        <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
        <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> </b></p>
        <p style="text-align:center; font-weight:900; font-size:1rem;">
            @isset($_GET['branches'])
                {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
            @endisset
        </p>
        <div class="table-responsive px-2">
            @php
                $lynxTotals = [];
                foreach ($periods as $i => $p) {
                    $lynxTotals[$i] = ['reg' => 0, 'adm' => 0, 'tf_in' => 0, 'tf_out' => 0, 'wd' => 0, 'gain' => 0];
                }
            @endphp

            @foreach ($branchesToLoop as $branchId => $branchName)
                <p
                    style="font-family: Arial, Helvetica, sans-serif; font-size: 1.2rem; font-weight:bold; margin-top: 10px;">
                    {{ $branchName }}
                </p>
                <table cellpadding="4" cellspacing="0" class="">
                    <thead class="table_heads">
                        <tr style="font-weight:bold;">
                            <th rowspan="2" style="width:15%;">CLASS</th>
                            @foreach ($periods as $p)
                                <th colspan="6">{{ $p['label'] }}</th>
                            @endforeach
                        </tr>
                        <tr style="font-weight:bold;">
                            @foreach ($periods as $_)
                                <th>REG</th>
                                <th>ADM</th>
                                <th>TF. IN</th>
                                <th>TF. OUT</th>
                                <th>WD</th>
                                <th>NET GAIN</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Rows per class --}}
                        @foreach ($classesByBranch[$branchId] as $classId => $classModel)
                            <tr>
                                <td style="font-weight:bold;">{{ $classModel->name }}</td>
                                @foreach ($periods as $i => $p)
                                    @php
                                        $c = $stats[$branchId][$classId][$i] ?? [
                                            'reg' => 0,
                                            'adm' => 0,
                                            'tf_in' => 0,
                                            'tf_out' => 0,
                                            'wd' => 0,
                                            'gain' => 0,
                                        ];
                                    @endphp
                                    <td style="text-align:center">{{ $c['reg'] }}</td>
                                    <td style="text-align:center">{{ $c['adm'] }}</td>
                                    <td style="text-align:center">{{ $c['tf_in'] }}</td>
                                    <td style="text-align:center">{{ $c['tf_out'] }}</td>
                                    <td style="text-align:center">{{ $c['wd'] }}</td>
                                    <td style="text-align:center">{{ $c['gain'] }}</td>
                                @endforeach
                            </tr>
                        @endforeach

                        {{-- Branch Total --}}
                        <tr style="background:#eee; font-weight:bold;">
                            <td>Branch Total</td>
                            @foreach ($periods as $i => $p)
                                @php
                                    $sumReg = collect($classesByBranch[$branchId]->keys())->sum(
                                        fn($cid) => $stats[$branchId][$cid][$i]['reg'] ?? 0,
                                    );
                                    $sumAdm = collect($classesByBranch[$branchId]->keys())->sum(
                                        fn($cid) => $stats[$branchId][$cid][$i]['adm'] ?? 0,
                                    );
                                    $sumTfIn = collect($classesByBranch[$branchId]->keys())->sum(
                                        fn($cid) => $stats[$branchId][$cid][$i]['tf_in'] ?? 0,
                                    );
                                    $sumTfOut = collect($classesByBranch[$branchId]->keys())->sum(
                                        fn($cid) => $stats[$branchId][$cid][$i]['tf_out'] ?? 0,
                                    );
                                    $sumWd = collect($classesByBranch[$branchId]->keys())->sum(
                                        fn($cid) => $stats[$branchId][$cid][$i]['wd'] ?? 0,
                                    );
                                    $sumGain = collect($classesByBranch[$branchId]->keys())->sum(
                                        fn($cid) => $stats[$branchId][$cid][$i]['gain'] ?? 0,
                                    );
                                    // accumulate into lynxTotals for this period
                                    $lynxTotals[$i]['reg'] += $sumReg;
                                    $lynxTotals[$i]['adm'] += $sumAdm;
                                    $lynxTotals[$i]['tf_in'] += $sumTfIn;
                                    $lynxTotals[$i]['tf_out'] += $sumTfOut;
                                    $lynxTotals[$i]['wd'] += $sumWd;
                                    $lynxTotals[$i]['gain'] += $sumGain;
                                @endphp

                                <td style="text-align:center">{{ $sumReg }}</td>
                                <td style="text-align:center">{{ $sumAdm }}</td>
                                <td style="text-align:center">{{ $sumTfIn }}</td>
                                <td style="text-align:center">{{ $sumTfOut }}</td>
                                <td style="text-align:center">{{ $sumWd }}</td>
                                <td style="text-align:center">{{ $sumGain }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            @endforeach
                <br>
            {{-- LYNX TOTAL --}}
            <table cellpadding="4" cellspacing="0" class="">
                <tbody>
                    <tr style="background:gray; font-weight:bold;">
                        <td style="background:gray; font-weight:bold;">Lynx Total</td>
                        @foreach ($periods as $i => $p)
                            <td style="text-align:center background:gray; font-weight:bold;">{{ $lynxTotals[$i]['reg'] }}</td>
                            <td style="text-align:center background:gray; font-weight:bold;">{{ $lynxTotals[$i]['adm'] }}</td>
                            <td style="text-align:center background:gray; font-weight:bold;">{{ $lynxTotals[$i]['tf_in'] }}</td>
                            <td style="text-align:center background:gray; font-weight:bold;">{{ $lynxTotals[$i]['tf_out'] }}</td>
                            <td style="text-align:center background:gray; font-weight:bold;">{{ $lynxTotals[$i]['wd'] }}</td>
                            <td style="text-align:center background:gray; font-weight:bold;">{{ $lynxTotals[$i]['gain'] }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
@endsection

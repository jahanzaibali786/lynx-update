@extends('layouts.admin')

@section('page-title')
    {{ __('Student Fee Detail Report') }}
@endsection

@push('script-page')
    <script>
        function togglePctInputs(show) {
            document.querySelectorAll('.pct-input').forEach(function(el) {
                el.disabled = !show;
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            togglePctInputs(true);
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Fee Detail Report') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['student_fee_detail'], 'method' => 'GET', 'id' => 'student_fee_detail_form']) }}
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request('branches', 'all'), ['class' => 'form-control select', 'id' => 'branch_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                                    {{ Form::select('session', $sessions, request('session', 'all'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $classes, request('class', 'all'), ['class' => 'form-control select', 'id' => 'class_select']) }}
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>{{ __('Fee Heads — Enter discount percentage to filter') }}</h6>
                            </div>
                            @foreach ($feeHeads as $head)
                                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mt-2">
                                    <div class="btn-box">
                                        {{ Form::label('pct_' . $head->id, $head->fee_head, ['class' => 'form-label']) }}
                                        {{ Form::number('pct_' . $head->id, request('pct_' . $head->id, ''), ['class' => 'form-control pct-input', 'step' => '0.01', 'min' => '0', 'max' => '100', 'placeholder' => '%']) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-3 align-items-end">
                            <div class="col-xl-12 d-flex justify-content-end gap-2">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('student_fee_detail_form').submit(); return false;">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_fee_detail') }}" class="btn mx-1 btn-sm btn-outline-danger">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <button type="submit" name="export" value="excel" class="btn mx-1 btn-sm btn-outline-success">
                                    <span class="btn-inner--icon">Excel</span>
                                </button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($groupedResults->isNotEmpty())
        <div class="content" id="report-content">
            <div class="card p-4 table-responsive">
                <div style="width: 100%; text-align: center;">
                    <p style="font-family:Edwardian Script ITC; font-size:3rem;"><b>The Lynx School</b></p>
                    <p style="text-align:center; font-weight:600; font-size:1rem;">{{ __('Student Fee Detail Report') }}</p>
                </div>
                <div style="width: 100%; display: flex; justify-content: space-between;">
                    <p><b>Branch:</b> {{ request('branches') && request('branches') !== 'all' ? ($branches[request('branches')] ?? 'Selected') : 'All Branches' }}</p>
                    <p><b>Class:</b> {{ request('class') && request('class') !== 'all' ? ($classes[request('class')] ?? 'Selected') : 'All Classes' }}</p>
                </div>

                <table class="datatable">
                    <thead class="table_heads">
                        <tr>
                            <th>{{ __('Sr#') }}</th>
                            <th>{{ __('Bsr#') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th>{{ __('Roll No') }}</th>
                            <th>{{ __('Student Name') }}</th>
                            <th>{{ __('Father Name') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Section') }}</th>
                            <th>{{ __('Fee Head') }}</th>
                            <th>{{ __('Disc %') }}</th>
                            <th>{{ __('Actual Fee') }}</th>
                            <th>{{ __('Discount') }}</th>
                            <th>{{ __('Payable') }}</th>
                            <th>{{ __('Policy') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sr = 1; @endphp
                        @foreach ($groupedResults as $branchId => $branchStudents)
                            @php
                                $branchName = $branches[$branchId] ?? 'Branch #' . $branchId;
                            @endphp
                            <tr style="background-color: #e0e0e0; font-weight: bold;">
                                <td colspan="14">{{ $branchName }}</td>
                            </tr>
                            @php $bsr = 1; @endphp
                            @foreach ($branchStudents as $row)
                                @php $student = $row['student']; @endphp
                                @foreach ($row['items'] as $idx => $item)
                                    <tr>
                                        @if ($idx === 0)
                                            <td rowspan="{{ count($row['items']) }}">{{ $sr++ }}</td>
                                            <td rowspan="{{ count($row['items']) }}">{{ $bsr++ }}</td>
                                            <td rowspan="{{ count($row['items']) }}" style="white-space: normal;">{{ $branchName }}</td>
                                            <td rowspan="{{ count($row['items']) }}">{{ $student->roll_no ?? optional($student->enrollment)->enrollId ?? '-' }}</td>
                                            <td rowspan="{{ count($row['items']) }}">{{ $student->stdname ?? '-' }}</td>
                                            <td rowspan="{{ count($row['items']) }}">{{ $student->fathername ?? '-' }}</td>
                                            <td rowspan="{{ count($row['items']) }}">{{ optional(optional($student->enrollment)->class)->name ?? optional($student->class)->name ?? '-' }}</td>
                                            <td rowspan="{{ count($row['items']) }}">{{ optional(optional($student->enrollment)->section)->name ?? '-' }}</td>
                                        @endif
                                        <td>{{ $item['head_name'] }}</td>
                                        <td>{{ $item['discount_pct'] }}%</td>
                                        <td>{{ number_format($item['actual_fee'], 2) }}</td>
                                        <td>{{ number_format($item['discount_amount'], 2) }}</td>
                                        <td>{{ number_format($item['payable'], 2) }}</td>
                                        @if ($idx === 0)
                                            <td rowspan="{{ count($row['items']) }}">{{ $row['policy_name'] ?? '-' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>

                <p class="mt-3"><strong>{{ __('Total Students:') }}</strong> {{ $totalStudents }}</p>
            </div>
        </div>
    @else
        @php
            $hasPctFilter = false;
            foreach ($feeHeads as $head) {
                if (is_numeric(request('pct_' . $head->id)) && request('pct_' . $head->id) > 0) {
                    $hasPctFilter = true;
                    break;
                }
            }
        @endphp
        @if ($hasPctFilter)
            <div class="content" id="report-content">
                <div class="card p-4 text-center">
                    <p>{{ __('No students found matching the selected criteria.') }}</p>
                </div>
            </div>
        @endif
    @endif
@endsection

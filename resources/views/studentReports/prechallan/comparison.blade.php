@extends('layouts.admin')
@section('page-title')
    {{ __('Pre-Challan vs Regular Challan Comparison') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Pre-Challan Comparison') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card mt-2">
                <div class="card-body">
                    {{ Form::open(['route' => 'prechallan.comparison', 'method' => 'GET', 'id' => 'comparison_form']) }}
                    <div class="row align-items-end g-2 justify-content-end">

                        <div class="col-auto">
                            {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                            {{ Form::select('branches', $branches, $selectedBranchId ?? null, ['class' => 'form-control', 'style' => 'min-width:180px;']) }}
                        </div>

                        <div class="col-auto">
                            {{ Form::label('date', __('Month'), ['class' => 'form-label']) }}
                            {{ Form::month('date', $selectedDate ?? now()->format('Y-m'), ['class' => 'form-control']) }}
                        </div>

                        <div class="col-auto mt-4">
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('comparison_form').submit(); return false;">
                                    <i class="ti ti-search" style="color:#fff !important;"></i> Search
                                </a>
                                <a href="{{ route('prechallan.comparison') }}" class="btn btn-sm btn-danger">
                                    <i class="ti ti-x" style="color:#fff !important;"></i> Clear
                                </a>
                                @if (!empty($report) && $report->count())
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle"
                                            data-bs-toggle="dropdown">Export</button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button name="export" value="excel" class="dropdown-item" type="submit">
                                                    Excel
                                                </button>
                                            </li>
                                            {{-- <li>
                                                <button name="export" value="pdf" class="dropdown-item" type="submit">
                                                    PDF
                                                </button>
                                            </li> --}}
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    @if (!empty($report) && $report->count())
        <div class="row mt-3">
            <div class="col-sm-12">

                {{-- Summary cards --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body py-3">
                                <p class="text-muted mb-1" style="font-size:12px;">Total Students</p>
                                <h5 class="mb-0">{{ $totalStudents }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body py-3">
                                <p class="text-muted mb-1" style="font-size:12px;">Regular Challan Total</p>
                                <h5 class="mb-0">{{ number_format($regChallanTotal, 0) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body py-3">
                                <p class="text-muted mb-1" style="font-size:12px;">Pre-Challan Total</p>
                                <h5 class="mb-0">{{ number_format($preChallanTotal, 0) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body py-3">
                                <p class="text-muted mb-1" style="font-size:12px;">Net Difference</p>
                                <h5
                                    class="mb-0 {{ $netDifferenceTotal == 0 ? 'text-primary' : ($netDifferenceTotal > 0 ? 'text-danger' : 'text-warning') }}">
                                    {{ number_format($netDifferenceTotal, 0) }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main table --}}
                <div class="card">
                    <div class="card-body p-0">
                        <table class="datatable mb-0" style="font-size:12px;">
                            <thead class="table_heads">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Br. Sr.</th>
                                    <th>Roll #</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>D/O/ADM</th>
                                    <th class="text-end">Regular Challan Net Payable</th>
                                    <th class="text-end">Pre-Challan Net Payable</th>
                                    <th class="text-end">Difference</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $globalSr = 1;
                                    $grandReg = 0;
                                    $grandPre = 0;
                                    $grandDiff = 0;
                                @endphp

                                @foreach ($report as $branchId => $rows)
                                    <tr style="background:#d4d4d4; font-weight:bold;">
                                        <td colspan="10" style="border:1px solid #999; padding:4px 8px;">
                                            {{ $branches[$branchId] ?? 'Branch #' . $branchId }}
                                        </td>
                                    </tr>

                                    @php
                                        $branchRegTotal = 0;
                                        $branchPreTotal = 0;
                                        $branchDiffTotal = 0;
                                    @endphp

                                    @foreach ($rows as $idx => $row)
                                        @php
                                            $diff = $row['difference'];
                                            $isOk = $diff == 0;
                                            $branchRegTotal += $row['regular_net'];
                                            $branchPreTotal += $row['pre_challan_net'];
                                            $branchDiffTotal += $diff;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $globalSr++ }}</td>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td>{{ $row['roll_no'] }}</td>
                                            <td>{{ $row['student_name'] }}</td>
                                            <td>{{ $row['class_name'] }}</td>
                                            <td class="text-center">{{ $row['adm_date'] }}</td>
                                            <td class="text-end">{{ number_format($row['regular_net'], 0) }}</td>
                                            <td class="text-end">{{ number_format($row['pre_challan_net'], 0) }}</td>
                                            <td class="text-end {{ $isOk ? 'text-primary' : 'text-danger' }}">
                                                {{ number_format($diff, 0) }}
                                            </td>
                                            <td class="{{ $isOk ? 'text-primary' : 'text-danger' }}">
                                                {{ $isOk ? 'OK' : 'Mismatch' }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Branch subtotal --}}
                                    <tr style="background:#878787; font-weight:bold; font-size:11px;">
                                        <td colspan="6" class="text-end">Branch Total</td>
                                        <td class="text-end">{{ number_format($branchRegTotal, 0) }}</td>
                                        <td class="text-end">{{ number_format($branchPreTotal, 0) }}</td>
                                        <td class="text-end {{ $branchDiffTotal == 0 ? 'text-primary' : 'text-danger' }}">
                                            {{ number_format($branchDiffTotal, 0) }}
                                        </td>
                                        <td></td>
                                    </tr>

                                    @php
                                        $grandReg += $branchRegTotal;
                                        $grandPre += $branchPreTotal;
                                        $grandDiff += $branchDiffTotal;
                                    @endphp
                                @endforeach

                                {{-- Grand total --}}
                                <tr style="background:#8B8B8B; color:#000; font-weight:bold; font-size:11px;">
                                    <td colspan="6" class="text-end">Grand Total</td>
                                    <td class="text-end">{{ number_format($grandReg, 0) }}</td>
                                    <td class="text-end">{{ number_format($grandPre, 0) }}</td>
                                    <td class="text-end">{{ number_format($grandDiff, 0) }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    @elseif(request()->has('date'))
        <div class="card">

            <div class="alert alert-info mt-3">
                No comparison data found for the selected filters. Either no pre-challan has been approved for this
                month/branch, or no regular challans were generated.
            </div>
        </div>
    @endif

    {{-- ===================== Export Modal ===================== --}}
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Export Comparison Report') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:13px;">
                        Exporting comparison for
                        <strong>{{ $branches[$selectedBranchId ?? ''] ?? 'All Branches' }}</strong>
                        &mdash;
                        <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedDate ?? now()->format('Y-m'))->format('F Y') }}</strong>
                    </p>
                    <div class="d-flex gap-3 mt-3 justify-content-center">
                        <a href="{{ route('prechallan.comparison', array_merge(request()->all(), ['export' => 'excel'])) }}"
                            class="btn btn-success btn-sm px-4 py-3 d-flex flex-column align-items-center"
                            style="min-width:110px;">
                            <i class="ti ti-file-spreadsheet"
                                style="font-size:28px; margin-bottom:6px; color:#fff !important;"></i>
                            <span>Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('prechallan.comparison', array_merge(request()->all(), ['export' => 'pdf'])) }}"
                            class="btn btn-danger btn-sm px-4 py-3 d-flex flex-column align-items-center"
                            style="min-width:110px;">
                            <i class="ti ti-file-type-pdf"
                                style="font-size:28px; margin-bottom:6px; color:#fff !important;"></i>
                            <span>PDF</span>
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{-- ========================================================= --}}

@endsection

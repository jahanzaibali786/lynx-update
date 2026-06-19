@extends('layouts.admin')
@section('page-title')
    {{ __('Student Statistical Summary (STS) Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        $(document).on('change', '#branch', function() {
            let branch = $(this).val();
            $.ajax({
                url: "{{ route('branch.class') }}",
                type: "POST",
                data: {
                    branch_id: branch,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(result) {
                    var $classSelect = $('#class_select');
                    if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                        $classSelect[0].customSelectInstance.destroy();
                        delete $classSelect[0].customSelectInstance;
                    }
                    if ($classSelect.next('.custom-select-wrapper').length) {
                        $classSelect.next('.custom-select-wrapper').remove();
                    }
                    $classSelect.removeClass('custom-select');
                    $classSelect.empty();
                    $classSelect.append($('<option>', { value: 'all', text: 'All Classes' }));
                    for (var j = 0; j < result.length; j++) {
                        var cls = result[j];
                        $classSelect.append($('<option>', { value: cls.id, text: cls.name }));
                    }
                    $classSelect.addClass('custom-select');
                    $classSelect.show();
                    if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                        window.CustomSelect.create($classSelect[0]);
                    }
                }
            });
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Statistical Summary') }}</li>
@endsection
@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => 'student_sts_report', 'method' => 'GET', 'id' => 'student_sts_report']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select custom-select', 'id' => 'branch']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                        {{ Form::date('start_date', $startDate->format('Y-m-d'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                        {{ Form::date('end_date', $endDate->format('Y-m-d'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('student_sts_report').submit(); return false;"
                        data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                id="reportActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Export
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="reportActionsDropdown">
                            <li>
                                <button class="dropdown-item" type="submit" name="export" value="excel">
                                    <i class="ti ti-file me-2"></i>Excel
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    @if (count($reportData) > 0)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table_heads">
                            <tr>
                                <th rowspan="2" class="text-center align-middle">{{ __('Branch') }}</th>
                                @foreach ($months as $month)
                                    <th colspan="3" class="text-center">
                                        {{ \Carbon\Carbon::parse($month . '-01')->format('M Y') }}
                                    </th>
                                @endforeach
                                <th colspan="4" class="text-center">{{ __('Total') }}</th>
                            </tr>
                            <tr>
                                @foreach ($months as $month)
                                    <th class="text-center">{{ __('Adm') }}</th>
                                    <th class="text-center">{{ __('WD') }}</th>
                                    <th class="text-center">{{ __('PO') }}</th>
                                @endforeach
                                <th class="text-center">{{ __('Adm') }}</th>
                                <th class="text-center">{{ __('WD') }}</th>
                                <th class="text-center">{{ __('PO') }}</th>
                                <th class="text-center">{{ __('Gains(+/-)') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData as $row)
                                <tr>
                                    <td>{{ $row['branch_name'] }}</td>
                                    @foreach ($months as $month)
                                        <td class="text-center">{{ $row['months'][$month]['adm'] }}</td>
                                        <td class="text-center">{{ $row['months'][$month]['wd'] }}</td>
                                        <td class="text-center">{{ $row['months'][$month]['po'] }}</td>
                                    @endforeach
                                    <td class="text-center fw-bold">{{ $row['total_adm'] }}</td>
                                    <td class="text-center fw-bold">{{ $row['total_wd'] }}</td>
                                    <td class="text-center fw-bold">{{ $row['total_po'] }}</td>
                                    <td class="text-center fw-bold {{ $row['gains'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $row['gains'] >= 0 ? '+' : '' }}{{ $row['gains'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td>{{ __('Total') }}</td>
                                @foreach ($months as $month)
                                    <td class="text-center">{{ $grandTotals[$month]['adm'] }}</td>
                                    <td class="text-center">{{ $grandTotals[$month]['wd'] }}</td>
                                    <td class="text-center">{{ $grandTotals[$month]['po'] }}</td>
                                @endforeach
                                <td class="text-center">{{ $grandTotals['total_adm'] }}</td>
                                <td class="text-center">{{ $grandTotals['total_wd'] }}</td>
                                <td class="text-center">{{ $grandTotals['total_po'] }}</td>
                                <td class="text-center {{ $grandTotals['gains'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $grandTotals['gains'] >= 0 ? '+' : '' }}{{ $grandTotals['gains'] }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($request->anyFilled(['branch', 'start_date', 'end_date']))
        <div class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-0">{{ __('No data found for the selected criteria.') }}</p>
            </div>
        </div>
    @endif
@endsection

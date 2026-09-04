@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Employee Scale') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Scale') }}</li>
@endsection
@push('script-page')
    <script>
        function printReport() {
            let form = document.getElementById('employee_scale_submit');
            let formData = new FormData(form);
            let queryString = new URLSearchParams(formData).toString();
            window.location.href = "{{ route('employee_scale.report') }}?" + queryString;
        }

        function bindEmployeeScaleAjax() {
            if (typeof ajaxModalForm === 'function') {
                ajaxModalForm({
                    formSelector: '.employee-scale-ajax-form',
                    submitText: '{{ __("Saving...") }}',
                    showToast: true,
                    onSuccess: function(response) {
                        if (!response || !response.success) {
                            return;
                        }

                        var $tbody = $('#employee-scale-table-body');
                        var rowHtml = response.row_html || '';
                        var rowId = response.row_id || null;

                        if (rowId && $('#employee-scale-row-' + rowId).length) {
                            $('#employee-scale-row-' + rowId).replaceWith(rowHtml);
                        } else if (rowHtml) {
                            var $empty = $('#employee-scale-empty-row');
                            if ($empty.length) {
                                $empty.remove();
                            }
                            $tbody.prepend(rowHtml);
                        }

                        if (typeof ajaxDeleteForm === 'function') {
                            ajaxDeleteForm({
                                selector: '.employee-scale-delete',
                                showToast: true,
                                onSuccess: function(deleteResponse, $button) {
                                    var deletedRowId = deleteResponse && deleteResponse.row_id ? deleteResponse.row_id : null;
                                    if (deletedRowId) {
                                        $('#employee-scale-row-' + deletedRowId).remove();
                                    } else {
                                        $button.closest('tr').remove();
                                    }
                                }
                            });
                        }
                    }
                });
            }

            if (typeof ajaxDeleteForm === 'function') {
                ajaxDeleteForm({
                    selector: '.employee-scale-delete',
                    showToast: true,
                    onSuccess: function(response, $button) {
                        if (response && response.row_id) {
                            $('#employee-scale-row-' + response.row_id).remove();
                        } else {
                            $button.closest('tr').remove();
                        }
                    }
                });
            }
        }

        $(document).ready(function() {
            bindEmployeeScaleAjax();
        });
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        @can('create employee scale')
            <a href="#" data-size="lg" data-url="{{ route('employee_scale.create') }}" data-ajax-popup="true"
                data-bs-title="{{ __('Create') }}" data-bs-toggle="{{ __('Create New Employee Scale') }}"
                class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['employee_scale.index'], 'method' => 'GET', 'id' => 'employee_scale_submit']) }}
                        <div class="row d-flex justify-content-start ">

                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('scale_no', __('Scale No'), ['class' => 'form-label']) }}
                                    {{ Form::text('scale_no', isset($_GET['scale_no']) ? $_GET['scale_no'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            @foreach ($heads as $account)
                                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('head_' . $account->id, __($account->head), ['class' => 'form-label']) }}
                                        {{ Form::text('head_' . $account->id, isset($_GET['head_' . $account->id]) ? $_GET['head_' . $account->id] : '', ['class' => 'form-control']) }}
                                    </div>
                                </div>
                            @endforeach
                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('effect_from', __('Effect From'), ['class' => 'form-label']) }}
                                    {{ Form::date('effect_from', isset($_GET['effect_from']) ? $_GET['effect_from'] : '', ['class' => 'form-control', 'id' => 'effect_from_id']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('department', __('Departments'), ['class' => 'form-label']) }}
                                    {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control select', 'id' => 'department_id']) }}
                                </div>
                            </div>

                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('adhoc', __('IS Adhoc'), ['class' => 'form-label']) }}
                                    {{ Form::select('adhoc', ['' => 'Select', '0' => 'No', '1' => 'Yes'], isset($_GET['adhoc']) ? $_GET['adhoc'] : '', ['class' => 'form-control', 'id' => 'is_adhoc_id']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                    {{ Form::select('status', ['all ' => 'All Select', '1' => 'Active', '0' => 'In-Active'], isset($_GET['status']) ? $_GET['status'] : $stat, ['class' => 'form-control']) }}
                                </div>
                            </div>

                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('employee_scale_submit').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('employee_scale.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                {{-- <a href="#" class="btn mx-1 btn-sm btn-outline-success"
                                    onclick="printReport(); return false;" 
                                    data-bs-title="{{ __('Print Report') }}">
                                    <span class="btn-inner--icon">Print</span>
                                </a>
                                <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export"
                                    value="excel"  data-bs-title="Spreadsheet"><span class="btn-inner--icon">Export</span></button> --}}
                                <!-- Actions Dropdown -->
                                <div class="dropdown d-inline-block mx-1">
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
                                            <button class="dropdown-item" type="submit" name="export" value="pdf">
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
            <div class="table-responsive">
        <table class="datatable">
            <thead>
                <tr class="table_heads">
                    <th>{{ __('Sr.') }}</th>
                    <th>{{ __('ScaleNo') }}</th>
                    <th>{{ __('Department') }}</th>
                    @php
                        $net_gross = 0;
                    @endphp
                    @foreach ($heads as $account)
                        <th>{{ !empty($account->head) ? @$account->head : '-' }}</th>
                    @endforeach
                    <th>{{ __('Net Gross') }}</th>
                    {{-- <th>{{ __('Emp. Sec. 8% of Gross') }}</th> --}}
                    {{-- <th>{{ __('Eobi Employee Cont.') }}</th> --}}
                    {{-- <th>{{ __('Inc. Tax') }}</th> --}}
                    {{-- <th>{{ __('Net Payable') }}</th> --}}
                    <th>{{ __('Effect From') }}</th>
                    <th>{{ __('IsAdhoc') }}</th>
                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('edit employee scale') || Gate::check('delete employee scale') || Gate::check('show employee scale'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                </tr>
            </thead>
            <tbody class="font-style" id="employee-scale-table-body">
                @forelse ($employee_scales as $scale)
                    @include('scale.row', ['scale' => $scale, 'heads' => $heads])
                @empty
                    <tr id="employee-scale-empty-row">
                        <td colspan="{{ 7 + $heads->count() }}" class="text-center text-muted py-4">
                            {{ __('No Employee Scale found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- @if ($employee_scales->hasPages())
        <div class="pagination">
            <ul>
                @if ($employee_scales->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $employee_scales->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($employee_scales->currentPage() > 1)
                    <li><a href="{{ $employee_scales->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $employee_scales->currentPage();
                    $lastPage = $employee_scales->lastPage();
                    $startPage = max(1, $currentPage - 4);
                    $endPage = min($lastPage, $currentPage + 5);
                    if ($endPage - $startPage < 9) {
                        if ($currentPage < $lastPage - 9) {
                            $endPage = $startPage + 9;
                        } else {
                            $startPage = max(1, $lastPage - 9);
                        }
                    }
                @endphp
                @for ($page = $startPage; $page <= $endPage; $page++)
                    <li class="{{ $page == $employee_scales->currentPage() ? 'active' : '' }}">
                        <a href="{{ $employee_scales->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($employee_scales->hasMorePages())
                    <li><a href="{{ $employee_scales->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($employee_scales->currentPage() < $employee_scales->lastPage())
                    <li><a
                            href="{{ $employee_scales->appends(request()->query())->url($employee_scales->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif --}}
@endsection

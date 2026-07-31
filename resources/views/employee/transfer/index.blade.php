@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Transfer') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Transfer') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create transfer')
            <a href="#" data-size="lg" data-url="{{ route('employee-transfer.create') }}" data-ajax-popup="true"
                data-bs-title="{{ __('Create Employee Transfer') }}" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>

            </a>
        @endcan
    </div>
@endsection

@push('script-page')
    <script>
        function branchemployees(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.employees') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {
                    console.log(result);
                    if (result.status == 'success') {
                        $('#employee_id').empty();
                        $('#employee_id').append($('<option>', {
                            value: '',
                            text: 'Select Employee'
                        }));

                        for (var j = 0; j < result.employee.length; j++) {
                            var cls = result.employee[j];
                            $('#employee_id').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }
                    if (result.status == 'error') {}
                }
            });

        }

        function employeedep(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('employee.dep') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {
                    if (result.status == 'success') {
                        // $('#employee_id').empty();
                        // $('#employee_id').append($('<option>', {
                        //     value: '',
                        //     text: 'Select Employee'
                        // }));
                        $('#exist_desig').val(result.employee.designation.name);
                        $('#exist_dept').val(result.employee.department.name);
                        $('#dec_id').val(result.employee.department_id).trigger('change.select2');
                        getDesignation(
                            result.employee.department_id,
                            result.employee.designation_id
                        );
                        $('#scale').val(result.scale.scale.scale_no);
                        $('#gross').val(result.scale.net);
                    }
                    if (result.status == 'error') {}
                }
            });

        }

        function submitWithPrintFlag(type = "pdf") {
            const form = $('#transfer_submit');
            if (type === "pdf")
                $('#is_print').val(1);
            if (type === "excel")
                $('#is_excel').val(1);
            form.attr('target', '_blank').submit().removeAttr('target');
            $('#is_print').val(0);
            $('#is_excel').val(0);
        }

        function initializeEmployeeTransferTable() {
            var table = document.querySelector('#employee-transfer-content .datatable');
            if (table && window.simpleDatatables) {
                new simpleDatatables.DataTable(table, {
                    perPage: 50,
                    paging: false,
                    perPageSelect: false
                });
            }
        }

        window.refreshEmployeeTransferContent = function() {
            return $.ajax({
                url: window.location.href,
                type: 'GET',
                cache: false,
                dataType: 'html',
                success: function(html) {
                    var documentHtml = new DOMParser().parseFromString(html, 'text/html');
                    var nextContent = documentHtml.getElementById('employee-transfer-content');
                    var currentContent = document.getElementById('employee-transfer-content');

                    if (!nextContent || !currentContent) {
                        show_toastr('error', '{{ __('Unable to refresh employee transfers.') }}');
                        return;
                    }

                    currentContent.replaceWith(nextContent);
                    initializeEmployeeTransferTable();
                    common_bind();
                    commonLoader();
                },
                error: function() {
                    show_toastr('error', '{{ __('Unable to refresh employee transfers.') }}');
                }
            });
        };

        $(function() {
            ajaxModalForm({
                formSelector: '.employee-transfer-approval-form',
                submitText: '{{ __('Approving...') }}',
                closeOnSuccess: false,
                onSuccess: function() {
                    window.refreshEmployeeTransferContent();
                }
            });

            ajaxDeleteForm({
                selector: '.employee-transfer-ajax-delete',
                defaultConfirm: '{{ __('Are you sure you want to delete this transfer?') }}',
                onSuccess: function() {
                    window.refreshEmployeeTransferContent();
                }
            });
        });
    </script>
@endpush
@section('content')
    <div id="employee-transfer-content">
    <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['transferreport'], 'method' => 'GET', 'id' => 'transfer_submit']) }}
                            <div class="row d-flex">

                                @if (\Auth::user()->type == 'company')
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                            {{ Form::select('branches', $branches, request('branches'), ['class' => 'form-control select']) }}
                                        </div>
                                    </div>
                                @endif
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('type', __('Transfer Type'), ['class' => 'form-label']) }}
                                        {{ Form::select('type', ['' => 'Select Type', 'transfer_in' => 'Transfer In', 'transfer_out' => 'Transfer Out'], isset($_GET['type']) ? $_GET['type'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('status', __('Transfer Status'), ['class' => 'form-label']) }}
                                        {{ Form::select('status', ['' => 'Select Status', '0' => 'Pending', '1' => 'Approve'], isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('datefrom', __('Date From'), ['class' => 'form-label']) }}
                                        <input type="date" class="form-control" name="datefrom"
                                            value="{{ $datefrom ?? request('datefrom') }}">
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('dateto', __('Date To'), ['class' => 'form-label']) }}
                                        <input type="date" class="form-control" name="dateto"
                                            value="{{ $dateto ?? request('dateto') }}">
                                    </div>
                                </div>
                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('transfer_submit').submit(); return false;"
                                        data-bs-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('transferreport') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                        data-bs-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon">Clear</span>
                                    </a>

                                    {{-- <a href="#" class="btn mx-1 btn-sm btn-outline-success"
                                        data-bs-title="{{ __('Print') }}">
                                        <span class="btn-inner--icon">Print</span>
                                    </a>
                                    <input type="text" class="d-none" id="is_excel" name="is_excel" value="0">
                                    <a type="button" class="btn mx-1 btn-sm btn-outline-primary me-2" data-bs-title="excel"
                                        onclick="submitWithPrintFlag('excel')">
                                        <span class="btn-inner--icon">Export</span>
                                    </a> --}}

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
    <table class="datatable" data-pagination="false">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                <th>{{ __('Emp Id') }}</th>
                <th>{{ __('Employee Name') }}</th>
                <th>{{ __('Branch From') }}</th>
                <th>{{ __('Branch To') }}</th>
                <th>{{ __('Department From') }}</th>
                <th>{{ __('Department To') }}</th>
                <th>{{ __('Transfer Date') }}</th>
                <th>{{ __('Description') }}</th>
                @if (Gate::check('edit transfer') || Gate::check('delete transfer'))
                    <th width="200px" style="text-align:center;">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($transfers as $transfer)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ !empty($transfer->employee) ? $transfer->employee->employee_id : '' }}</td>
                    <td>{{ !empty($transfer->employee) ? $transfer->employee->name : '' }}</td>
                    <td>{{ !empty($transfer->branch_from) ? $transfer->branch_from->name : '' }}</td>
                    <td>{{ !empty($transfer->branch_to) ? $transfer->branch_to->name : '' }}</td>
                    <td>{{ !empty($transfer->department_from) ? $transfer->department_from->name : '' }}</td>
                    <td>{{ !empty($transfer->department_to) ? $transfer->department_to->name : '' }}</td>
                    <td>{{ \Auth::user()->dateFormat($transfer->transfer_date) }}</td>
                    <td>{{ !empty($transfer->transfer_reason) ? $transfer->transfer_reason : '' }}</td>
                    @if (Gate::check('edit transfer') || Gate::check('delete transfer'))
                        <td style="min-width: 250px; white-space: nowrap;">
                            <div class="action-btn ms-2" style="align-items: baseline;">
                                {{-- button icon for print --}}
                                <a href="{{ route('employee-transfer.print', $transfer->id) }}" target="_blank"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-success" data-bs-title="{{ __('Print') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-printer"></i></span></a>
                                @can('edit transfer')
                                    @if ((int) $transfer->status !== 1)
                                        <a href="#" data-size="lg"
                                            data-url="{{ route('employee-transfer.edit', $transfer->id) }}" data-ajax-popup="true"
                                            data-bs-title="{{ __('Edit Employee Transfer') }}"
                                            class="btn mx-1 btn-sm btn-outline-primary">
                                            <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                        </a>
                                    @endif
                                @endcan
                                @can('delete transfer')
                                    @if ((int) $transfer->status !== 1 || \Auth::user()->type === 'company')
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['employee-transfer.destroy', $transfer->id],
                                            'id' => 'delete-form-' . $transfer->id,
                                            'class' => 'd-inline',
                                        ]) !!}
                                        <a type="button"
                                            class="mx-1 btn btn-sm btn-outline-danger employee-transfer-ajax-delete"
                                            data-form-id="delete-form-{{ $transfer->id }}"
                                            data-confirm="{{ __('Are you sure you want to delete this transfer?') }}"
                                            data-bs-title="{{ __('Delete') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                        </a>
                                        {!! Form::close() !!}
                                    @endif
                                @endcan
                                @can('edit transfer')
                                    @if ((int) $transfer->status !== 1)
                                        {{ Form::open([
                                            'route' => ['employee-transfer.approve', $transfer->id],
                                            'method' => 'POST',
                                            'class' => 'employee-transfer-approval-form d-inline',
                                        ]) }}
                                        <a href="#" class="btn mx-1 btn-sm btn-outline-warning mx-3" onclick="$(this).closest('form').submit(); return false;">
                                            {{ __('Approve') }}
                                        </a>
                                        {{ Form::close() }}
                                    @endif
                                @endcan
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    {{-- @if ($transfers->hasPages())
        <div class="pagination">
            <ul>
                @if ($transfers->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $transfers->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($transfers->currentPage() > 1)
                    <li><a href="{{ $transfers->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $transfers->currentPage();
                    $lastPage = $transfers->lastPage();
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
                    <li class="{{ $page == $transfers->currentPage() ? 'active' : '' }}">
                        <a href="{{ $transfers->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($transfers->hasMorePages())
                    <li><a href="{{ $transfers->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($transfers->currentPage() < $transfers->lastPage())
                    <li><a
                            href="{{ $transfers->appends(request()->query())->url($transfers->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif --}}
@endsection

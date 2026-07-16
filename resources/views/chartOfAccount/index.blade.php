@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Chart of Accounts') }}
@endsection
@section('breadcrumb')
    <style>
        td a {
            text-decoration: none !important;
        }
    </style>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Chart of Account') }}</li>
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // $(document).on('change', '#type', function() {
        //     var type = $(this).val();
        //     $.ajax({
        //         url: '{{ route('charofAccount.subType') }}',
        //         type: 'POST',
        //         data: {
        //             "type": type,
        //             "_token": "{{ csrf_token() }}",
        //         },
        //         success: function(data) {
        //             $('#sub_type').empty();
        //             $.each(data, function(key, value) {
        //                 $('#sub_type').append('<option value="' + key + '">' + value +
        //                     '</option>');
        //             });
        //         }
        //     });
        // });
        $(document).on('change', '#sub_type', function() {
            $('.acc_check').removeClass('d-none');
            var type = $(this).val();
            $.ajax({
                url: '{{ route('charofAccount.subType') }}',
                type: 'POST',
                data: {
                    "type": type,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#parent').empty();
                    $.each(data, function(key, value) {
                        $('#parent').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        });
        $(document).on('click', '#account', function() {
            const element = $('#account').is(':checked');
            $('.acc_type').addClass('d-none');
            if (element == true) {
                $('.acc_type').removeClass('d-none');
            } else {
                $('.acc_type').addClass('d-none');
            }
        });
        // on change category update
        $(document).on('change', 'select[name="category"]', function() {
            var category = $(this).val();
            var accountId = $(this).data('id'); // Get account ID from data attribute
            $.ajax({
                url: '{{ route('chart-of-account.updateCategory') }}',
                type: 'POST',
                data: {
                    "account_id": accountId,
                    "category": category,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    if (data.success) {

                        Swal.fire({
                            toast: true,
                            position: 'top-end', // 🔥 top right
                            // icon: 'success',
                            title: 'Category updated successfully',
                            showConfirmButton: false,
                            timer: 3000,
                        });

                    } else {

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            // icon: 'error',
                            title: 'Failed to update category',
                            showConfirmButton: false,

                            timer: 3000
                        });

                    }
                },
                error: function() {
                    alert('An error occurred while updating category');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            callback();

            function callback() {
                var start_date = $(".startDate").val();
                var end_date = $(".endDate").val();
                var branch = $(".branch").val();

                $('.start_date').val(start_date);
                $('.end_date').val(end_date);
                $('.branch').val(branch);

            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#show_balance_btn').on('click', function(e) {
                e.preventDefault(); // prevent default anchor behavior

                // Check current icon state
                const icon = $('#balance_icon');
                const isVisible = icon.hasClass('ti-eye');

                // Toggle icon classes
                icon.toggleClass('ti-eye ti-eye-off');

                // Redirect with updated parameter
                const showBalance = isVisible ? 0 : 1;
                window.location.href = "{{ route('chart-of-account.index') }}?show_balance=" + showBalance;
            });
        });
    </script>
@endpush



@section('action-btn')
    <div class="float-end">
        @can('create chart of account')
            <a href="#" data-url="{{ route('chart-of-account.create') }}" data-bs-title="{{ __('Create') }}" data-size="lg"
                data-ajax-popup="true" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">
                    Create
                </span>
            </a>
        @endcan
    </div>
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card" id="show_filter">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['chart-of-account.index'], 'method' => 'GET', 'id' => 'report_bill_summary']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                                            {{ Form::select('branch', $branches, $filter['branch'], ['class' => 'form-control branch']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate form-control']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                            onclick="document.getElementById('report_bill_summary').submit(); return false;"
                                            data-bs-title="{{ __('Apply') }}" data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>

                                        <a href="{{ route('chart-of-account.index') }}"
                                            class="btn mx-1 btn-sm btn-outline-danger " data-bs-title="{{ __('Reset') }}"
                                            data-bs-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon">Clear</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @foreach ($chartAccounts as $type => $accounts)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6>{{ $type }}</h6>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="datatable">
                                <thead>
                                    <tr class="table_heads">
                                        <th width="10%"> {{ __('Code') }}</th>
                                        <th width="30%"> {{ __('Name') }}</th>
                                        <th width="20%"> {{ __('Type') }}</th>
                                        <th width="20%"> {{ __('Category') }}</th>
                                        <th width="20%"> {{ __('Parent Account Name') }}</th>
                                        <th width="20%"> {{ __('Balance') }}</th>
                                        <th width="10%"> {{ __('Status') }}</th>
                                        <th width="10%"> {{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accounts as $account)
                                        @php
                                            $balance = 0;
                                            $totalDebit = 0;
                                            $totalCredit = 0;
                                            $totalBalance = 0;
                                            // App\Models\Utility::getAccountBalance($account->id,$filter['startDateRange'],$filter['endDateRange'],$filter['branch']);
                                            $categories = [
                                                '' => 'Select Category',
                                                'hr' => 'Hr',
                                                'student' => 'Student',
                                                'bank' => 'Bank',
                                                'inventory' => 'Inventory',
                                            ];
                                        @endphp

                                        <tr>
                                            <td>{{ $account->code }}</td>
                                            <td><a
                                                    href="{{ route('report.ledger', $account->id) }}?account={{ $account->id }}">{{ $account->name }}</a>
                                            </td>
                                            <td>{{ !empty($account->subType) ? $account->subType->name : '-' }}</td>
                                            <td>
                                                {{ Form::select('category', $categories, !empty($account->category) ? $account->category : null, ['class' => 'form-control select', 'required' => 'required', 'data-id' => $account->id]) }}

                                            </td>
                                            <td>{{ !empty($account->parentAccount) ? $account->parentAccount->name : '-' }}
                                            </td>

                                            <td>
                                                @if (!empty($totalBalance))
                                                    @php
                                                        $totalBalance = ltrim($totalBalance, '-');
                                                    @endphp
                                                    {{ \Auth::user()->priceFormat($totalBalance) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($account->is_enabled == 1)
                                                    <span
                                                        class="badge bg-outline-primary p-2 px-3 rounded">{{ __('Enabled') }}</span>
                                                @else
                                                    <span
                                                        class="badge bg-outline-danger p-2 px-3 rounded">{{ __('Disabled') }}</span>
                                                @endif
                                            </td>
                                            <td class="">
                                                <div class="action-btn ms-2">
                                                    <a href="{{ route('report.ledger', $account->id) }}?account={{ $account->id }}"
                                                        class="mx-1 btn btn-sm align-items-center btn-outline-warning "
                                                        data-bs-title="{{ __('Transaction Summary') }}"
                                                        data-bs-title="{{ __('Detail') }}"><span class="btn-inner--icon">
                                                            <i class="fas fa-wave-square"></i>
                                                        </span>

                                                    </a>
                                                    @can('edit chart of account')
                                                        <a href="#"
                                                            class="mx-1 btn btn-sm align-items-center btn-outline-primary"
                                                            data-url="{{ route('chart-of-account.edit', $account->id) }}"
                                                            data-size="lg"
                                                            data-ajax-popup="true" title="{{ __('Edit Account') }}"
                                                            data-bs-title="{{ __('Edit') }}"><span class="btn-inner--icon">
                                                                <i class="ti ti-pencil"></i>
                                                            </span>
                                                        </a>
                                                    @endcan
                                                    {{-- @can('delete chart of account')
                                        {!! Form::open([
                                        'method' => 'DELETE',
                                        'route' => ['chart-of-account.destroy', $account->id],
                                        'id' => 'delete-form-' . $account->id,
                                        ]) !!}
                                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                             data-bs-title="{{ __('Delete') }}"
                                            data-bs-title="{{ __('Delete') }}"
                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                            data-confirm-yes="document.getElementById('delete-form-{{ $account->id }}').submit();">
                                            <span class="btn-inner--icon">
                                                <i class="ti ti-trash"></i>
                                            </span>
                                        </a>
                                        {!! Form::close() !!}
                                    </div>
                                    @endcan --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

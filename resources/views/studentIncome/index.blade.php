@extends('layouts.admin')
@section('page-title')
    {{ __('Receive Income of Students') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Student Incomes')}}</li>
@endsection


@section('action-btn')
    <div class="float-end">
        @can('create journal entry')
            <a href="#" data-url="{{ route('student-incomes.create') }}" data-ajax-popup="true" data-title="{{__('Receive Student Income')}}" data-size="xl" class="btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Receive Student Income') }}">
                <span class="btn-inner--icon">{{__('Receive Income')}}</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        @if($pendingCount > 0)
            <div class="col-md-12 mb-3">
                <div class="card bg-light-primary p-3 d-flex flex-row justify-content-between align-items-center" style="border-left: 5px solid #0d0d5e;">
                    <div>
                        <strong class="text-primary">{{ __('Pending Day End Consolidation:') }}</strong>
                        <span class="ms-2 text-dark">{{ $pendingCount }} {{ __('income record(s) totaling') }} <strong>{{ \Auth::user()->priceFormat($pendingSum) }}</strong> {{ __('are currently pending voucher posting.') }}</span>
                    </div>
                    {!! Form::open(['method' => 'POST', 'route' => 'student-incomes.day-end', 'id' => 'day-end-form', 'class' => 'm-0 ajax-modal-form']) !!}
                        <input type="hidden" name="date" value="{{ request()->date ?? date('Y-m-d') }}">
                        <input type="hidden" name="branch_id" value="{{ request()->branch_id }}">
                        <input type="hidden" name="class_id" value="{{ request()->class_id }}">
                        <button type="submit" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Process Day End Voucher') }}">{{ __('Process Day End Voucher') }}</button>
                    {!! Form::close() !!}
                </div>
            </div>
        @endif

        <div class="col-sm-12 mb-4">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['student-incomes.index'], 'method' => 'GET', 'id' => 'student-income_submit']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'), ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                                        {{ Form::select('branch_id', $branches, isset($_GET['branch_id']) ? $_GET['branch_id'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}
                                        {{ Form::select('class_id', $classes, isset($_GET['class_id']) ? $_GET['class_id'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto d-flex gap-1">
                                    <a href="#" class="btn btn-sm btn-outline-primary"
                                        onclick="document.getElementById('student-income_submit').submit(); return false;" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Search') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('student-incomes.index') }}" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Clear Search Filters') }}">
                                        <span class="btn-inner--icon">Clear</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table align-items-center table-bordered">
                            <thead class="table_heads">
                            <tr>
                                <th>#</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Student')}}</th>
                                <th> {{__('Fee/Income Type')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Bank')}}</th>
                                <th> {{__('Account (COA)')}}</th>
                                <th> {{__('Received By')}}</th>
                                <th class="wrap-td"> {{__('Description')}}</th>
                                <th> {{__('Linked Voucher')}}</th>
                                <th> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $groupedIncomes = $incomes->groupBy('owned_by');
                                $grandTotal = 0;
                            @endphp
                            @forelse ($groupedIncomes as $branchId => $branchIncomes)
                                @php
                                    $branchName = optional($branchIncomes->first()->branch)->name ?? __('Default / Main Branch');
                                    $branchTotal = 0;
                                @endphp
                                <tr style="background-color: #f0f4f8;">
                                    <td colspan="11" class="text-dark font-bold py-2">
                                        <i class="ti ti-building me-1"></i><strong>{{ $branchName }}</strong>
                                    </td>
                                </tr>
                                @foreach ($branchIncomes as $index => $income)
                                    @php
                                        $branchTotal += $income->amount;
                                        $grandTotal += $income->amount;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ Auth::user()->dateFormat($income->date) }}</td>
                                        <td>{{ optional($income->student)->stdname ?? '-' }} ({{ optional($income->student)->roll_no ?? '-' }})</td>
                                        <td>{{ $income->income_type }}</td>
                                        <td>{{ \Auth::user()->priceFormat($income->amount) }}</td>
                                        <td>{{ optional($income->bank)->holder_name ?? '-' }}</td>
                                        <td>{{ optional($income->coa)->name ?? '-' }}</td>
                                        <td>{{ optional($income->receivedBy)->name ?? '-' }}</td>
                                        <td class="wrap-td">{{ !empty($income->description) ? $income->description : '-' }}</td>
                                        <td>
                                            @if($income->journalEntry)
                                                <a href="{{ route('journal-entry.show', $income->journalEntry->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('View Linked Voucher') }}">
                                                    {{ Auth::user()->journalNumberFormat($income->journalEntry->journal_id) }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$income->journal_entry_id)
                                                @can('delete journal entry')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['student-incomes.destroy', $income->id], 'id' => 'delete-form-'.$income->id, 'class' => 'd-inline']) !!}
                                                        <a href="#" class="mx-1 btn btn-sm btn-outline-danger bs-pass-para" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$income->id}}').submit();">
                                                            <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endcan
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                <tr style="background-color: #fafafa; font-weight: bold;">
                                    <td colspan="4" class="text-end text-muted">{{ $branchName }} {{ __('Total:') }}</td>
                                    <td colspan="7" class="text-dark"><strong>{{ \Auth::user()->priceFormat($branchTotal) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">{{ __('No Incomes found') }}</td>
                                </tr>
                            @endforelse
                            @if($incomes->count() > 0)
                                <tr style="background-color: #e8ecf4; font-weight: bold; border-top: 2px double #0d0d5e;">
                                    <td colspan="4" class="text-end">{{ __('Grand Total:') }}</td>
                                    <td colspan="7" class="text-primary font-bold"><strong>{{ \Auth::user()->priceFormat($grandTotal) }}</strong></td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            // Initialize Bootstrap 5 tooltips using vanilla JS API
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            if (typeof ajaxModalForm === 'function') {
                ajaxModalForm({
                    onSuccess: function(response) {
                        if (typeof show_toastr === 'function') {
                            show_toastr('success', response.message);
                        }
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                });
            }
        });
    </script>
@endpush

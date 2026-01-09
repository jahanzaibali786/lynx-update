@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Fee Head') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script>
        // Function to update input and search box element widths based on the parent's width
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('All Fee Head') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}
        <a href="#" data-size="md" data-url="{{ route('fee_head.create') }}" data-ajax-popup="true"
             data-bs-title="{{ __('Create') }}" class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>
        {{-- @endcan --}}
    </div>
@endsection
@section('content')
    <div class="table-responsive">
        <table class="">
            <thead class="table_heads">
                <tr>
                    <th>#</th>
                    <th>{{ __('Head Name') }}</th>
                    <th>{{ __('Account (Income)') }}</th>
                    <th>{{ __('Account (Receivable)') }}</th>
                    <th>{{ __('Account (Discount)') }}</th>
                    <th width="200px">{{ __('Action') }}</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($heads as $head)
                    <tr>
                        <td>{{ ($heads->currentPage() - 1) * $heads->perPage() + $loop->iteration }}</td>
                        <td>
                            {{ !empty($head->fee_head) ? $head->fee_head : '-' }}
                        </td>
                        <td>
                            {{ !empty($head->account_head) ? $head->account_head->name : '-' }}
                        </td>
                        <td>
                            {{ !empty($head->receivable_head->sub_type) ? $head->receivable_head->name : '-' }}
                        </td>
                        <td>
                            {{ !empty($head->discount_head->sub_type) ? $head->discount_head->name : '-' }}
                        </td>
                        {{-- @if (Gate::check('edit session') || Gate::check('delete session')) --}}
                        <td>
                            <div class="action-btn ms-2">
                                {{-- @can('edit session') --}}
                                <a href="#!"data-url="{{ route('fee_head.edit', $head->id) }}" data-ajax-popup="true"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-primary" 
                                    data-bs-title="{{ __('Edit') }}"><span
                                        class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>
                                {{-- @endcan --}}

                                {{-- <div class="action-btn ms-2">
                                    @can('delete section') 
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['fee_head.destroy', $head->id],'id'=>'delete-form-'.$head->id]) !!}
                                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$head->id}}').submit();"><span class="btn-inner--icon"><i class="ti ti-trash text-white"></i></span></a>
                                    {!! Form::close() !!}
                                </div> --}}
                                {{-- @endcan --}}

                        </td>
                        {{-- @endif --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($heads->hasPages())
        <div class="pagination">
            <ul>
                @if ($heads->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $heads->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($heads->currentPage() > 1)
                    <li><a href="{{ $heads->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $heads->currentPage();
                    $lastPage = $heads->lastPage();
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
                    <li class="{{ $page == $heads->currentPage() ? 'active' : '' }}">
                        <a href="{{ $heads->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($heads->hasMorePages())
                    <li><a href="{{ $heads->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($heads->currentPage() < $heads->lastPage())
                    <li><a
                            href="{{ $heads->appends(request()->query())->url($heads->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
@endsection

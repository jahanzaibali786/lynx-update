@extends('layouts.admin')
@section('page-title')
    {{ __('All Register Options') }}
@endsection
@push('script-page')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('All Register Options') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}
        <a href="#" data-size="md" data-url="{{ route('registerOption.create') }}" data-ajax-popup="true"
             data-bs-title="{{ __('Create') }}" class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>
        {{-- @endcan --}}
    </div>
@endsection
@section('content')
    <div class="col-12 table-responsive">
        <table class="">
            <thead class="table_heads">
            <tr >
                <th>#</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Price') }}</th>
                <th style=" text-align: center;">{{ __('Action') }}</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($registeroptions as $registeroption)
                    <tr style="  border-radius: 10px !important;">
                        <td>{{ ($registeroptions->currentPage() - 1) * $registeroptions->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $registeroption->name }}</td>
                        <td>{{ $registeroption->discount }}</td>
                        <td style="    text-align: center;">
                            <div class="action-btn ms-2">
                                <a href="#!"data-url="{{ route('registerOption.edit', $registeroption->id) }}"
                                    data-ajax-popup="true" class="mx-1 btn mx-1 btn-sm btn-outline-primary"
                                     data-bs-title="{{ __('Edit') }}"
                                    data-original-title="{{ __('Edit') }}"><span class="btn-inner--icon"><i
                                            class="ti ti-pencil"></i></span></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($registeroptions->hasPages())
        <div class="pagination">
            <ul>
                @if ($registeroptions->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $registeroptions->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($registeroptions->currentPage() > 1)
                    <li><a href="{{ $registeroptions->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $registeroptions->currentPage();
                    $lastPage = $registeroptions->lastPage();
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
                    <li class="{{ $page == $registeroptions->currentPage() ? 'active' : '' }}">
                        <a href="{{ $registeroptions->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($registeroptions->hasMorePages())
                    <li><a href="{{ $registeroptions->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($registeroptions->currentPage() < $registeroptions->lastPage())
                    <li><a
                            href="{{ $registeroptions->appends(request()->query())->url($registeroptions->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif


@endsection

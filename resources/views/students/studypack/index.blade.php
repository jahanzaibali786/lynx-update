@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Study Pack') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Study Pack') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}
        <a href="{{ route('studypack.create') }}"  data-bs-title="{{ __('Create') }}"
            class="btn mx-1 btn-sm btn-outline-primary">
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
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Class') }}</th>
                    <th>{{ __('Cost') }}</th>
                    <th>{{ __('Session') }}</th>
                    <th width="200px" style="text-align: center;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($studypacks as $studypack)
                    <tr>
                        <td>{{ ($studypacks->currentPage() - 1) * $studypacks->perPage() + $loop->iteration }}</td>
                        <td>{{ $studypack->title }}</td>
                        <td>{{ @$studypack->class }}</td>
                        <td>{{ $studypack->study_pack_cost }}</td>
                        <td>{{ @$studypack->session->year }}</td>
                        <td>
                            <div class="action-btn ms-5">
                                <a href="{{ route('studypack.show', $studypack->id) }}"
                                    class="mx-1 btn btn-sm align-items-center btn-outline-info" 
                                    title="Show " data-bs-title="{{ __('Detail') }}">
                                    <span class="btn-inner--icon"><i class="fas fa-eye"></i></span>
                                </a>

                                <a href="{{ route('studypack.edit', $studypack->id) }}"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-primary" 
                                    data-bs-title="{{ __('Edit') }}" data-bs-title="{{ __('Edit') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-pencil "></i></span></a>

                                {{-- {!! Form::open(['method' => 'DELETE', 'route' => ['studypack.destroy', $studypack->id],'id'=>'delete-form-'.$studypack->id])!!}
                            <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger bs-pass-para"  data-bs-title="{{__('Delete')}}"
                            data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                            data-confirm-yes="document.getElementById('delete-form-{{$studypack->id}}').submit();">
                            <span class="btn-inner--icon"><i class="ti ti-trash "></i></a>
                        {!! Form::close()!!} --}}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($studypacks->hasPages())
        <div class="pagination">
            <ul>
                @if ($studypacks->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $studypacks->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;
                            Previous</a></li>
                @endif
                @if ($studypacks->currentPage() > 1)
                    <li><a href="{{ $studypacks->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $studypacks->currentPage();
                    $lastPage = $studypacks->lastPage();
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
                    <li class="{{ $page == $studypacks->currentPage() ? 'active' : '' }}">
                        <a href="{{ $studypacks->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($studypacks->hasMorePages())
                    <li><a href="{{ $studypacks->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($studypacks->currentPage() < $studypacks->lastPage())
                    <li><a href="{{ $studypacks->appends(request()->query())->url($studypacks->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif

@endsection

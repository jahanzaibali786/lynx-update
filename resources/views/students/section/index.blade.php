@extends('layouts.admin')
@section('page-title')
    {{__('Manage Section')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('All Section')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}
            <a href="#" data-size="md" data-url="{{ route('section.create') }}" data-ajax-popup="true"   data-bs-title="{{__('Create')}}"  class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        {{-- @endcan --}}
    </div>
@endsection
@section('content')

    <div class="table-responsive">
    <table class="">
            <thead class ="table_heads">
            <tr>
                <th>#</th>
                <th>{{__('Name')}}</th>
                <th width="200px">{{__('Action')}}</th>

            </tr>
            </thead>
            <tbody>
            @foreach ($sections as $section)
                <tr>
                    <td>{{ ($sections->currentPage() - 1) * $sections->perPage() + $loop->iteration }}</td>
                    <td>
                        {{ (!empty($section->name)) ? $section->name : '-' }}
                    </td>

                    {{-- @if(Gate::check('edit session') || Gate::check('delete session')) --}}
                        <td>
                                {{-- @can('edit session') --}}
                                <div class="action-btn ms-2">
                                    <a href="#!"data-url="{{route('section.edit',$section->id)}}"  data-ajax-popup="true" class="mx-1 btn mx-1 btn-sm btn-outline-primary"  data-bs-title="{{__('Edit')}}"
                                    data-bs-title="{{__('Edit')}}"><span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>

                                {{-- @endcan  
                                     @can('delete section') --}}
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['section.destroy', $section->id],'id'=>'delete-form-'.$section->id]) !!}
                                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$section->id}}').submit();"><span class="btn-inner--icon"><i class="ti ti-trash"></i></span></a>
                                    {!! Form::close() !!}
                                    {{-- @endcan --}}
                                </div>
         
                        </td>
                    {{-- @endif --}}
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if ($sections->hasPages())
        <div class="pagination">
            <ul>
                @if ($sections->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $sections->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($sections->currentPage() > 1)
                    <li><a href="{{ $sections->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $sections->currentPage();
                    $lastPage = $sections->lastPage();
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
                    <li class="{{ $page == $sections->currentPage() ? 'active' : '' }}">
                        <a href="{{ $sections->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($sections->hasMorePages())
                    <li><a href="{{ $sections->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($sections->currentPage() < $sections->lastPage())
                    <li><a
                            href="{{ $sections->appends(request()->query())->url($sections->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
@endsection

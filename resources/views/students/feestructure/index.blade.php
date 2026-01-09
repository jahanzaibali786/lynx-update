@extends('layouts.admin')
@section('page-title')
    {{__('Manage Fee Structure Class Wise')}}
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
            <a href="#" data-size="md" data-url="{{ route('section.create') }}" data-ajax-popup="true"   data-bs-title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                Create
            </a>
        {{-- @endcan --}}
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
            <div class="card-body table-border-style">
                        <div class="table-responsive">
                        <table class="table datatable">
                                <thead>
                                <tr>
                                    <th>{{__('Name')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
    
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($sections as $section)
                                    <tr>
                                    
                                        <td>
                                            {{ (!empty($section->name)) ? $section->name : '-' }}
                                        </td>
            
                                        {{-- @if(Gate::check('edit session') || Gate::check('delete session')) --}}
                                            <td>
                                                    {{-- @can('edit session') --}}
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#!"data-url="{{route('section.edit',$section->id)}}"  data-ajax-popup="true" class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}"
                                                        data-bs-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                                    </div>
    
                                                    {{-- @endcan
                                                    @can('delete section') --}}
                                                    <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['section.destroy', $section->id],'id'=>'delete-form-'.$section->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$section->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    {{-- @endcan --}}
                                                {{-- @else
    
                                                    <i class="ti ti-lock"></i>
                                                @endif --}}
                                            </td>
                                        {{-- @endif --}}
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

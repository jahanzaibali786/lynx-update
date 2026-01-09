@extends('layouts.admin')
@section('page-title')
    {{__('Manage Concession Policy')}}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script>
        // Function to update input and search box element widths based on the parent's width

    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('All Concession Policy')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}
            <a href="#" data-size="lg" data-url="{{ route('concession_policy.create') }}" data-ajax-popup="true"   data-bs-title="{{__('Create')}}"  class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        {{-- @endcan --}}
    </div>
@endsection
@section('content')

    <div class="table-responsive" style="margin-top: 25px !important">
    <table class="datatable">
            <thead class="table_concessions table_heads">
            <tr>
                <th>{{__('#')}}</th>
                <th>{{__('No')}}</th>
                <th>{{__('Title')}}</th>
                <th>{{__('Description')}}</th>
                {{-- <th>{{__('session_id')}}</th> --}}
                <th width="200px">{{__('Action')}}</th>

            </tr>
            </thead>
            <tbody>
            @foreach ($concessions as $concession)
                <tr>
                
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ (!empty($concession->order_no)) ? $concession->order_no : '-' }}
                    </td>
                    <td>
                        {{ (!empty($concession->title)) ? $concession->title : '-' }}
                    </td>
                    <td>
                        {{ (!empty($concession->description)) ? $concession->description : '-' }}
                    </td>

                    {{-- @if(Gate::check('edit session') || Gate::check('delete session')) --}}
                        <td>
                                <div class="action-btn ms-2">
                                    {{-- @can('edit session') --}}
                                    <a href="#!" data-size="lg" data-url="{{route('concession_policy.edit',$concession->id)}}"  data-ajax-popup="true" class="mx-1 btn mx-1 btn-sm btn-outline-primary"  data-bs-title="{{__('Edit')}}"
                                    data-bs-title="{{__('Edit')}}"><span class="btn-inner--icon"><i class="ti ti-pencil "></i></span></a>
                                </div>

                                {{-- @endcan
                                @can('delete section') --}}
                                {{-- <div class="action-btn bg-danger ms-2">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['fee_head.destroy', $head->id],'id'=>'delete-form-'.$head->id]) !!}
                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$head->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
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
@endsection

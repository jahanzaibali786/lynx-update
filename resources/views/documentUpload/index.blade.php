@extends('layouts.admin')

@section('page-title')
    {{__('Manage Document')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Document')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create document')
            <a href="#" data-url="{{ route('document-upload.create') }}" data-ajax-popup="true" data-bs-toggle="{{__('Create New Document')}}"  data-bs-title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                Create
            </a>
        @endcan
    </div>
@endsection


@section('content')

                    <table class="">
                            <thead>
                            <tr class="table_heads">
                                <th>{{__('Name')}}</th>
                                <th>{{__('Document')}}</th>
                                <th>{{__('Role')}}</th>
                                <th>{{__('Description')}}</th>
                                @if(Gate::check('edit document') || Gate::check('delete document'))
                                    <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($documents as $document)
                                @php
                                    $documentPath=\App\Models\Utility::get_file('uploads/documentUpload');
                                    $roles = \Spatie\Permission\Models\Role::find($document->role);
                                @endphp
                                <tr>
                                    <td>{{ $document->name }}</td>
                                    <td>
                                        @if (!empty($document->document))
                                            <div class="action-btn bg-primary ms-2">
                                                <a class="mx-3 btn btn-sm align-items-center"
                                                   href="{{ $documentPath . '/' . $document->document }}" download>
                                                    <i class="ti ti-download text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-secondary ms-2">
                                                <a class="mx-3 btn btn-sm align-items-center" href="{{ $documentPath . '/' . $document->document }}" target="_blank"  >
                                                    <i class="ti ti-crosshair text-white"  data-bs-title="{{ __('Preview') }}"></i>
                                                </a>
                                            </div>
                                        @else
                                            <p>-</p>
                                        @endif
                                    </td>
                                    <td>{{ !empty($roles)?$roles->name:'All' }}</td>
                                    <td>{{ $document->description }}</td>
                                    @if(Gate::check('edit document') || Gate::check('delete document'))
                                        <td>
                                            @can('edit document')
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" data-url="{{ route('document-upload.edit',$document->id)}}" data-size="lg" data-ajax-popup="true" data-bs-toggle="{{__('Edit Document')}}" class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                            </div>
                                                @endcan
                                            @can('delete document')
                                            <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['document-upload.destroy', $document->id],'id'=>'delete-form-'.$document->id]) !!}

                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$document->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                                                {!! Form::close() !!}
                                            </div>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
@endsection

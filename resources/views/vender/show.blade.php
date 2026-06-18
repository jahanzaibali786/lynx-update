@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Manage Vendor-Detail')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('vender.index')}}">{{__('Vendor')}}</a></li>
    <li class="breadcrumb-item">{{$vendor['name']}}</li>

@endsection

@section('action-btn')
    <div class="float-end">
        {{-- @can('create bill')
            <a href="{{ route('bill.create',$vendor->id) }}" class="btn btn-sm btn-primary">
                {{__('Create Bill')}}
            </a>
        @endcan --}}

        @can('edit vender')
            <a href="#" class="btn mx-1 btn-sm btn-outline-primary" data-size="lg" data-url="{{ route('vender.edit',$vendor['id']) }}" data-ajax-popup="true" data-bs-title="{{__('Edit')}}"  data-bs-title="{{__('Edit')}}">
                <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
            </a>
        @endcan
        @can('delete vender')
            {!! Form::open(['method' => 'DELETE', 'route' => ['vender.destroy', $vendor['id']],'class'=>'delete-form-btn','id'=>'delete-form-'.$vendor['id']]) !!}
            <a href="#" class="btn mx-1 btn-sm btn-outline-danger bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{ $vendor['id']}}').submit();">
                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
            </a>
            {!! Form::close() !!}
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Basic Info Card -->
        <div class="col-md-6 col-lg-6 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title mb-3"><strong>{{__('Basic Information')}}</strong></h3>

                    @if(!empty($vendor->company_name))
                        <div class="mb-3">
                            <p class="text-muted mb-1"><small>{{__('Company Name')}}</small></p>
                            <h6 class="mb-0 text-break">{{$vendor->company_name}}</h6>
                        </div>
                    @endif

                    <div class="mb-3">
                        <p class="text-muted mb-1"><small>{{__('Full Name')}}</small></p>
                        <h6 class="mb-0 text-break">
                            @if(!empty($vendor->name_prefix) || !empty($vendor->first_name) || !empty($vendor->last_name))
                                {{ trim(($vendor->name_prefix ? $vendor->name_prefix . ' ' : '') . ($vendor->first_name ? $vendor->first_name . ' ' : '') . ($vendor->middle_initial ? $vendor->middle_initial . ' ' : '') . ($vendor->last_name ?? '')) }}
                            @else
                                {{$vendor->name}}
                            @endif
                        </h6>
                    </div>

                    @if(!empty($vendor->job_title))
                        <div class="mb-3">
                            <p class="text-muted mb-1"><small>{{__('Job Title')}}</small></p>
                            <h6 class="mb-0 text-break">{{$vendor->job_title}}</h6>
                        </div>
                    @endif

                    @if(!empty($vendor->tax_number))
                        <div class="mb-3">
                            <p class="text-muted mb-1"><small>{{__('Tax Number')}}</small></p>
                            <h6 class="mb-0 text-break">{{$vendor->tax_number}}</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact Info Card -->
        <div class="col-md-6 col-lg-6 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title mb-3"><strong>{{__('Contact Information')}}</strong></h3>

                    @if(!empty($vendor->main_phone))
                        <div class="mb-3">
                            <p class="text-muted mb-1"><small>{{__('Main Phone')}} @if($vendor->main_phone_type) <span class="badge bg-secondary">{{$vendor->main_phone_type}}</span> @endif</small></p>
                            <h6 class="mb-0 text-break"><i class="ti ti-phone me-2"></i>{{$vendor->main_phone}}</h6>
                        </div>
                    @elseif(!empty($vendor->contact))
                        <div class="mb-3">
                            <p class="text-muted mb-1"><small>{{__('Contact')}}</small></p>
                            <h6 class="mb-0 text-break"><i class="ti ti-phone me-2"></i>{{$vendor->contact}}</h6>
                        </div>
                    @endif

                    @if(!empty($vendor->work_phone))
                        <div class="mb-3">
                            <p class="text-muted mb-1"><small>{{__('Work Phone')}} @if($vendor->work_phone_type) <span class="badge bg-secondary">{{$vendor->work_phone_type}}</span> @endif</small></p>
                            <h6 class="mb-0 text-break"><i class="ti ti-phone me-2"></i>{{$vendor->work_phone}}</h6>
                        </div>
                    @endif

                    <div class="mb-3">
                        <p class="text-muted mb-1"><small>{{__('Email')}} @if($vendor->main_email_type) <span class="badge bg-secondary">{{$vendor->main_email_type}}</span> @endif</small></p>
                        <h6 class="mb-0 text-break"><i class="ti ti-mail me-2"></i>{{$vendor->email}}</h6>
                    </div>

                    @if(!empty($vendor->cc_email))
                        <div class="mb-3">
                            <p class="text-muted mb-1"><small>{{__('CC Email')}} @if($vendor->cc_email_type) <span class="badge bg-secondary">{{$vendor->cc_email_type}}</span> @endif</small></p>
                            <h6 class="mb-0 text-break"><i class="ti ti-mail me-2"></i>{{$vendor->cc_email}}</h6>
                        </div>
                    @endif

                    @if(!empty($vendor->website))
                        <div class="mb-3">
                            <p class="text-muted mb-1"><small>{{__('Website')}} @if($vendor->website_type) <span class="badge bg-secondary">{{$vendor->website_type}}</span> @endif</small></p>
                            <h6 class="mb-0 text-break"><i class="ti ti-world me-2"></i><a href="{{$vendor->website}}" target="_blank" class="text-break">{{$vendor->website}}</a></h6>
                        </div>
                    @endif

                    @if(!empty($vendor->other1))
                        <div class="mb-3">
                            <p class="text-muted mb-1"><small>{{__('Other')}} @if($vendor->other1_type) <span class="badge bg-secondary">{{$vendor->other1_type}}</span> @endif</small></p>
                            <h6 class="mb-0 text-break"><i class="ti ti-info-circle me-2"></i>{{$vendor->other1}}</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6 col-lg-6 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title mb-3"><strong>{{__('Billing Address')}}</strong></h3>
                    @if(!empty($vendor->billing_address))
                        <div class="mb-2">
                            <p class="card-text mb-0 text-break" style="white-space: pre-line;">{{$vendor->billing_address}}</p>
                        </div>
                    @else
                        @if(!empty($vendor->billing_name))
                            <p class="card-text mb-1 text-break">{{$vendor->billing_name}}</p>
                        @endif
                        @if(!empty($vendor->billing_city) || !empty($vendor->billing_state) || !empty($vendor->billing_zip))
                            <p class="card-text mb-1 text-break">{{$vendor->billing_city}}@if($vendor->billing_city && ($vendor->billing_state || $vendor->billing_zip)),@endif {{ $vendor->billing_state }} {{$vendor->billing_zip}}</p>
                        @endif
                        @if(!empty($vendor->billing_country))
                            <p class="card-text mb-1 text-break">{{$vendor->billing_country}}</p>
                        @endif
                        @if(!empty($vendor->billing_phone))
                            <p class="card-text mb-1 text-break"><i class="ti ti-phone me-2"></i>{{$vendor->billing_phone}}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title mb-3"><strong>{{__('Shipping Address')}}</strong></h3>
                    @if(!empty($vendor->shipping_address))
                        <div class="mb-2">
                            <p class="card-text mb-0 text-break" style="white-space: pre-line;">{{$vendor->shipping_address}}</p>
                        </div>
                    @else
                        @if(!empty($vendor->shipping_name))
                            <p class="card-text mb-1 text-break">{{$vendor->shipping_name}}</p>
                        @endif
                        @if(!empty($vendor->shipping_city) || !empty($vendor->shipping_state) || !empty($vendor->shipping_zip))
                            <p class="card-text mb-1 text-break">{{$vendor->shipping_city}}@if($vendor->shipping_city && ($vendor->shipping_state || $vendor->shipping_zip)),@endif {{ $vendor->shipping_state }} {{$vendor->shipping_zip}}</p>
                        @endif
                        @if(!empty($vendor->shipping_country))
                            <p class="card-text mb-1 text-break">{{$vendor->shipping_country}}</p>
                        @endif
                        @if(!empty($vendor->shipping_phone))
                            <p class="card-text mb-1 text-break"><i class="ti ti-phone me-2"></i>{{$vendor->shipping_phone}}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-12">
            <div class="card pb-0">
                <div class="card-body">
                    <h3 class="card-title">{{__('Company Info')}}</h3>
                    <div class="row">
                        @php
                            $totalBillSum=$vendor->vendorTotalBillSum($vendor['id']);
                            $totalBill=$vendor->vendorTotalBill($vendor['id']);
                            $averageSale=($totalBillSum!=0)?$totalBillSum/$totalBill:0;
                        @endphp
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0"><strong>{{ __('Vendor Id') }}</strong></p>
                                <h6 class="report-text mb-3">{{\Auth::user()->venderNumberFormat($vendor->vender_id)}}</h6>
                                <p class="card-text mb-0"><strong>{{__('Total Sum of Bills')}}</strong></p>
                                <h6 class="report-text mb-0">{{\Auth::user()->priceFormat($totalBillSum)}}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0"><strong>{{__('Date of Creation')}}</strong></p>
                                <h6 class="report-text mb-3">{{\Auth::user()->dateFormat($vendor->created_at)}}</h6>
                                <p class="card-text mb-0"><strong>{{__('Quantity of Bills')}}</strong></p>
                                <h6 class="report-text mb-0">{{$totalBill}}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0"><strong>{{__('Balance')}}</strong></p>
                                <h6 class="report-text mb-3">{{\Auth::user()->priceFormat($vendor->balance)}}</h6>
                                <p class="card-text mb-0"><strong>{{__('Average Sales')}}</strong></p>
                                <h6 class="report-text mb-0">{{\Auth::user()->priceFormat($averageSale)}}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0"><strong>{{__('Overdue')}}</strong></p>
                                <h6 class="report-text mb-3">{{\Auth::user()->priceFormat($vendor->vendorOverdue($vendor->id))}}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5 class=" d-inline-block mb-5">{{__('Bills')}}</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{{__('Bill')}}</th>
                                <th>{{__('Bill Date')}}</th>
                                <th>{{__('Due Date')}}</th>
                                <th>{{__('Due Amount')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($vendor->vendorBill($vendor->id) as $bill)
                                <tr class="font-style">
                                    <td class="Id">
                                        <a href="{{ route('bill.show',\Crypt::encrypt($bill->id)) }}" class="btn btn-outline-primary">{{ AUth::user()->billNumberFormat($bill->bill_id) }}
                                        </a>
                                    </td>
                                    <td>{{ Auth::user()->dateFormat($bill->bill_date) }}</td>
                                    <td>
                                        @if(($bill->due_date < date('Y-m-d')))
                                            <p class="text-danger"> {{ \Auth::user()->dateFormat($bill->due_date) }}</p>
                                        @else
                                            {{ \Auth::user()->dateFormat($bill->due_date) }}
                                        @endif
                                    </td>
                                    <td>{{\Auth::user()->priceFormat($bill->getDue())  }}</td>
                                    <td>
                                        @if($bill->status == 0)
                                            <span class="badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 1)
                                            <span class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 2)
                                            <span class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 3)
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 4)
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                        @endif
                                    </td>
                                    @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                        <td class="Action">
                                            <span>
                                            @can('duplicate bill')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"  data-bs-title="{{__('Duplicate Bill')}}" data-bs-title="{{__('Duplicate')}}" data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('duplicate-form-{{$bill->id}}').submit();">
                                                            <i class="ti ti-copy text-white text-white"></i>
                                                            {!! Form::open(['method' => 'get', 'route' => ['bill.duplicate', $bill->id],'id'=>'duplicate-form-'.$bill->id]) !!}{!! Form::close() !!}
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('show bill')

                                                    <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('bill.show',\Crypt::encrypt($bill->id)) }}" class="mx-3 btn btn-sm  align-items-center"  data-bs-title="{{__('Show')}}" data-bs-title="{{__('Detail')}}">
                                                                <i class="ti ti-eye text-white text-white"></i>
                                                            </a>
                                                        </div>
                                                @endcan
                                                @can('edit bill')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route('bill.edit',\Crypt::encrypt($bill->id)) }}" class="mx-3 btn btn-sm  align-items-center"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete bill')
                                                    <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['bill.destroy', $bill->id],'id'=>'delete-form-'.$bill->id]) !!}

                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$bill->id}}').submit();">
                                                            <i class="ti ti-trash text-white text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
@endsection

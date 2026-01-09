@extends('layouts.admin')
@section('page-title')
    {{__('Manage Revenues')}}
@endsection
@push('script-page')
     <script>
        function branchcustomer(id) {
            var customer = $('#customerselect').val();
            $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url: "{{ route('branch.revenue_data') }}",
                    type: "POST",
                    data: {id: id},
                    dataType: 'json',
                    success: function (result)
                    {
                        console.log(result);
                        if(result.status == 'success'){
                            $('#customerselect').empty();
                            $('#customerselect').append($('<option>', {
                                value: '',
                                text: 'select Customer'
                            }));
                            $('#accountselect').empty().append($('<option>', {
                                value: '',
                                text: 'select Account'
                            }));
                            $('#categoryselect').empty().append($('<option>', {
                                value: '',
                                text: 'select Category'
                            }));
                            // console.log(result);
                            for (var i = 0; i < result.account.length; i++) {
                                var account = result.account[i];
                                $('#accountselect').append($('<option>', {
                                    value: account.id,
                                    text: account.holder_name
                                }));
                            }
                            for (var i = 0; i < result.customer.length; i++) {
                                var customer = result.customer[i];
                                $('#customerselect').append($('<option>', {
                                    value: customer.id,
                                    text: customer.name
                                }));
                            }
                            for (var i = 0; i < result.category.length; i++) {
                                var category = result.category[i];
                                $('#categoryselect').append($('<option>', {
                                    value: category.id,
                                    text: category.name
                                }));
                            }
                        } 
                        if(result.status == 'error'){
                        }
                        
                    }
                });
            // Add more code as needed
        }
    
        document.getElementById('branchcustomer').addEventListener('change', function() {
            var id = this.value;
            branchcustomer(id);
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Revenue')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1"  data-bs-title="{{__('Filter')}}">--}}
        {{--            Filters--}}
        {{--        </a>--}}

        @can('create revenue')
            <a href="#" data-url="{{ route('revenue.create') }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="{{__('Create New Revenue')}}" class="btn btn-sm btn-primary"  data-bs-title="{{__('Create')}}">
                Create
            </a>
        @endcan

    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('revenue.index'),'method' => 'GET','id'=>'revenue_form')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row justify-content-end">

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        {{Form::label('date',__('Date'),['class'=>'form-label'])}}
                                        {{ Form::text('date', isset($_GET['date'])?$_GET['date']:null, array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1','readonly')) }}

                                    </div>
                                    @if(\Auth::user()->type == 'company')                            
                                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 month">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchcustomer(this.value)']) }}
                                        </div>      
                                    </div>
                                    @endif
                                    @if(\Auth::user()->type == 'company') 
                                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 month">
                                    @else
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                    @endif
                                        <div class="btn-box">
                                            {{Form::label('account',__('Account'),['class'=>'form-label'])}}
                                            {{ Form::select('account',$account,isset($_GET['account'])?$_GET['account']:'', array('class' => 'form-control select','id' => 'accountselect')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                        <div class="btn-box">
                                            {{ Form::label('customer', __('Customer'),['class'=>'form-label'])}}
                                            {{ Form::select('customer',$customer,isset($_GET['customer'])?$_GET['customer']:'', array('class' => 'form-control select','id' => 'customerselect')) }}
                                        </div>
                                    </div>

                                    @if(\Auth::user()->type == 'company') 
                                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 month">
                                    @else
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                    @endif
                                        <div class="btn-box">
                                            {{ Form::label('category', __('Category'),['class'=>'form-label'])}}
                                            {{ Form::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select','id' => 'categoryselect')) }}
                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">

                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('revenue_form').submit(); return false;"  data-bs-title="{{__('Apply')}}" data-bs-title="{{__('apply')}}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>

                                        <a href="{{route('revenue.index')}}" class="btn btn-sm btn-danger "   data-bs-title="{{ __('Reset') }}" data-bs-title="{{__('Reset')}}">
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

                        <table class="">
                            <thead>
                            <tr class="table_heads">
                                <th> {{__('Date')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Account')}}</th>
                                <th> {{__('Customer')}}</th>
                                <th> {{__('Category')}}</th>
                                <th> {{__('Reference')}}</th>
                                <th> {{__('Description')}}</th>
                                <th>{{__('Payment Receipt')}}</th>

                                @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $revenuepath=\App\Models\Utility::get_file('uploads/revenue');
                            @endphp
                            @foreach ($revenues as $revenue)

                                <tr class="font-style">
                                    <td>{{  Auth::user()->dateFormat($revenue->date)}}</td>
                                    <td>{{  Auth::user()->priceFormat($revenue->amount)}}</td>
                                    <td>{{ !empty($revenue->bankAccount)?$revenue->bankAccount->bank_name.' '.$revenue->bankAccount->holder_name:''}}</td>
                                    <td>{{  (!empty($revenue->customer)?$revenue->customer->name:'-')}}</td>
                                    <td>{{  !empty($revenue->category)?$revenue->category->name:'-'}}</td>
                                    <td>{{  !empty($revenue->reference)?$revenue->reference:'-'}}</td>
                                    <td>{{  !empty($revenue->description)?$revenue->description:'-'}}</td>

                                    <td>
{{--                                        @if(!empty($revenue->add_receipt))--}}
{{--                                            <a href="{{asset(Storage::url('uploads/revenue')).'/'.$revenue->add_receipt}}" download="" class="action-btn bg-primary ms-2 mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Download')}}" target="_blank"><span class="btn-inner--icon"><i class="ti ti-download text-white" ></i></span></a>--}}

{{--                                            <div class="action-btn bg-secondary">--}}
{{--                                                <a class="mx-3 btn btn-sm align-items-center" href="{{asset(Storage::url('uploads/revenue')).'/'.$revenue->add_receipt}}" target="_blank"  >--}}
{{--                                                    <i class="ti ti-crosshair text-white"  data-bs-title="{{ __('Preview') }}"></i>--}}
{{--                                                </a>--}}
{{--                                            </div>--}}
{{--                                        @else--}}
{{--                                            ---}}
{{--                                        @endif--}}

                                        @if(!empty($revenue->add_receipt))
                                            <a  class="action-btn bg-primary ms-2 btn btn-sm align-items-center" href="{{ $revenuepath . '/' . $revenue->add_receipt }}" download="">
                                                <i class="ti ti-download text-white"></i>
                                            </a>
                                            <a href="{{ $revenuepath . '/' . $revenue->add_receipt }}"  class="action-btn bg-secondary ms-2 mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Download')}}" target="_blank"><span class="btn-inner--icon"><i class="ti ti-crosshair text-white" ></i></span></a>
                                        @else
                                            -
                                        @endif

                                    </td>
                                    @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
                                        <td class="Action">
                                            <span>
                                            @can('edit revenue')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('revenue.edit',$revenue->id) }}" data-ajax-popup="true" data-size="lg"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete revenue')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['revenue.destroy', $revenue->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$revenue->id]) !!}

                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$revenue->id}}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
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
                        @if($revenues != "")
<div class="pagination">
    <ul>
        @if ($revenues->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $revenues->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $revenues->lastPage(); $page++)
            <li class="{{ $page == $revenues->currentPage() ? 'active' : '' }}">
                <a href="{{ $revenues->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($revenues->hasMorePages())
            <li><a href="{{ $revenues->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif
@endsection

@extends('layouts.admin')
@section('page-title')
{{__('Manage Expenses')}}
@endsection
@push('script-page')
<script>
$('.copy_link').click(function(e) {
    e.preventDefault();
    var copyText = $(this).attr('href');

    document.addEventListener('copy', function(e) {
        e.clipboardData.setData('text/plain', copyText);
        e.preventDefault();
    }, true);

    document.execCommand('copy');
    show_toastr('success', 'Url copied to clipboard', 'success');
});
</script>
<script>
function branchcategory(id) {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "{{ route('branch.category') }}",
        type: "POST",
        data: {
            id: id
        },
        dataType: 'json',
        success: function(result) {
            console.log(result);
            if (result.status == 'success') {
                $('#categoryselect').empty().append($('<option>', {
                    value: '',
                    text: 'select Category'
                }));
                for (var i = 0; i < result.category.length; i++) {
                    var category = result.category[i];
                    $('#categoryselect').append($('<option>', {
                        value: category.id,
                        text: category.name
                    }));
                }
            }
            if (result.status == 'error') {}

        }
    });
}

document.getElementById('branchcategory').addEventListener('change', function() {
    var id = this.value;
    branchcategory(id);
});
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Expense')}}</li>
@endsection

@section('action-btn')
<div class="float-end">
    @can('create bill')
    <a href="{{ route('expense.create',0) }}" class="btn btn-sm btn-primary" 
        data-bs-title="{{__('Create')}}">
        Create
    </a>
    @endcan
</div>
@endsection


@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body filter_change">
                    {{ Form::open(array('route' => array('expense.index'),'method' => 'GET','id'=>'frm_submit')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                            <div class="btn-box">
                                {{Form::label('bill_date',__('Payment Date'),['class'=>'form-label'])}}
                                {{ Form::text('bill_date', isset($_GET['bill_date'])?$_GET['bill_date']:null, array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1','readonly')) }}
                            </div>
                        </div>
                        @if(\Auth::user()->type == 'company')
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchcategory(this.value)']) }}
                            </div>
                        </div>
                        @endif
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('category', __('Category'),['class'=>'form-label'])}}
                                {{ Form::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select','id'=>'categoryselect')) }}
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('frm_submit').submit(); return false;"
                                         data-bs-title="{{__('Apply')}}"
                                        data-bs-title="{{__('apply')}}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{route('expense.index')}}" class="btn btn-sm btn-danger "
                                         data-bs-title="{{ __('Reset') }}"
                                        data-bs-title="{{__('Reset')}}">
                                        <span class="btn-inner--icon"><i
                                                class="ti ti-trash-off text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<table class="">
    <thead>
        <tr class="table_heads">
            <th> {{__('Expense')}}</th>
            <th> {{__('Category')}}</th>
            <th> {{__('Date')}}</th>
            <th>{{__('Status')}}</th>
            @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
            <th width="10%"> {{__('Action')}}</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($expenses as $expense)

        <tr>
            <td class="Id">
                <a href="{{ route('expense.show',\Crypt::encrypt($expense->id)) }}"
                    class="btn btn-outline-primary">{{ AUth::user()->expenseNumberFormat($expense->bill_id) }}</a>
            </td>
            <td>{{ !empty($expense->category)?$expense->category->name:'-'}}</td>
            <td>{{ Auth::user()->dateFormat($expense->bill_date) }}</td>
            <td>
                <span
                    class="status_badge badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$expense->status]) }}</span>
            </td>
            @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
            <td class="Action">
                <span>

                    @can('show bill')
                    <div class="action-btn bg-info ms-2">
                        <a href="{{ route('expense.show',\Crypt::encrypt($expense->id)) }}"
                            class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Show')}}"
                            data-bs-title="{{__('Detail')}}">
                            <i class="ti ti-eye text-white"></i>
                        </a>
                    </div>
                    @endcan
                    @can('edit bill')
                    <div class="action-btn bg-primary ms-2">
                        <a href="{{ route('expense.edit',\Crypt::encrypt($expense->id)) }}"
                            class="mx-3 btn btn-sm align-items-center"  title="Edit"
                            data-bs-title="{{__('Edit')}}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    </div>
                    @endcan
                    @can('delete bill')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['expense.destroy',
                        $expense->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$expense->id]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                            data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}"
                            data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                            data-confirm-yes="document.getElementById('delete-form-{{$expense->id}}').submit();">
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
@if($expenses != "")
<div class="pagination">
    <ul>
        @if ($expenses->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $expenses->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $expenses->lastPage(); $page++)
            <li class="{{ $page == $expenses->currentPage() ? 'active' : '' }}">
                <a href="{{ $expenses->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($expenses->hasMorePages())
            <li><a href="{{ $expenses->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif
@endsection
@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Health & Insurance Plan') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Health & Insurance Plan') }}</li>
@endsection
@push('script-page')
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script src="{{ asset('public/acron/searchselect.js')}}"></script>
<script>
function branchemployees(id) {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "{{ route('branch.employees') }}",
        type: "POST",
        data: {
            id: id
        },
        dataType: 'json',
        success: function(result) {
            console.log(result);
            if (result.status == 'success') {
                $('#employee_id').empty();
                $('#employee_id').append($('<option>', {
                    value: '',
                    text: 'Select Employee'
                }));

                for (var j = 0; j < result.employee.length; j++) {
                    var cls = result.employee[j];
                    $('#employee_id').append($('<option>', {
                        value: cls.id,
                        text: cls.name
                    }));
                }
            }
            if (result.status == 'error') {}
        }
    });
}
</script>
@endpush
@section('action-btn')
@can('create loan')
<div class="col text-end">
    <a href="#" data-url="{{ route('health-insurance-plan.create') }}" data-size="lg" data-ajax-popup="true"
        
        data-bs-tittle="{{__('Create Health & Insurance Setup')}}" class="apply-btn btn mx-1 btn-sm btn-primary">
        Create
    </a>
</div>
@endcan

@endsection
@section('content')
    <div class="card-body full-card">
        <div class="table-responsive">
            @if (!$healthInsurances->isEmpty())
                <table class="">
                    <thead class="">
                        <tr class="table_heads">
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('DOJ') }}</th>
                            <th>{{ __('Plan Name') }}</th>
                            <th>{{ __('Plan Amount') }}</th>
                            <th>{{ __('Plan Type') }}</th>
                            <th>{{ __('Plan Start') }}</th>
                            <th>{{ __('Plan End') }}</th>
                            <th>{{ __('Plan Status') }}</th>
                            @if (\Auth::user()->type != 'Employee')
                                <th>{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($healthInsurances as $insurance)
                            <tr>
                                <td>{{ @$insurance->employee->name }}</td>
                                <td>{{ @$insurance->employee->company_doj }}</td>
                                <td>{{ @$insurance->plan_name	 }}</td>
                                <td>{{ $insurance->plan_amount }}</td>
                                <td>{{ $insurance->plan_type }}</td>
                                <td>{{ $insurance->plan_start }}</td>
                                <td>{{ $insurance->plan_end }}</td>
                                <td>{{ $insurance->status == 0 ? 'Inactive' : 'Active' }}</td>
                                @if (\Auth::user()->type != 'Employee')
                                    <td class="row">
                                        <div class="action-btn  ms-3">
                                            @can('edit loan')
                                                <a href="#" data-url="{{ URL::to('health-insurance-plan/' . $insurance->id . '/edit') }}"
                                                    data-size="lg" data-ajax-popup="true"
                                                    data-bs-toggle="{{ __('Edit Employee Leave') }}"
                                                    class="mx-1 btn btn-sm  btn-outline-primary align-items-center"
                                                    data-bs-title="{{ __('Edit') }}" 
                                                    data-bs-title="{{ __('Edit') }}"><span class="btn-inner--icon">
                                                        <i class="ti ti-pencil"></i></span></a>
                                            @endcan

                                            @can('delete loan')
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['health-insurance-plan.destroy', $insurance->id],
                                                    'id' => 'delete-form-' . $insurance->id,
                                                ]) !!}
                                                <a href="#" class="mx-1 btn btn-sm  btn-outline-danger align-items-center bs-pass-para"
                                                    data-bs-title="{{ __('Delete') }}"
                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="document.getElementById('delete-form-{{ $insurance->id }}').submit();">
                                                    <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                </a>
                                                {!! Form::close() !!}
                                            @endcan

                                            
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="mt-2 text-center">
                    No Insurance Setup Yet!
                </div>
            @endif
        </div>
    </div>
    </div>
    </div>

@endsection

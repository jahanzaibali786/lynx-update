<tr data-advance-tax-id="{{ $collection->id }}">
    <td class="advance-tax-row-number">{{ $index ?? '' }}</td>
    <td>{{ @$collection->employee->name ?: '-' }}</td>
    <td>{{ \Carbon\Carbon::parse($collection->tax_month)->format('M Y') }}</td>
    <td>{{ \Carbon\Carbon::parse($collection->collection_date)->format('d-M-Y') }}</td>
    <td>{{ number_format($collection->amount, 2) }}</td>
    <td>{{ $collection->payment_method ? ucfirst($collection->payment_method) : '-' }}</td>
    <td>{{ $collection->reference ?: '-' }}</td>
    <td>
        @if($collection->status == 1)
            <span class="badge bg-success">{{ __('Approved') }}</span>
        @elseif($collection->status == 2)
            <span class="badge bg-danger">{{ __('Rejected') }}</span>
        @else
            @if(\Auth::user()->type == 'company')
                <a href="#" data-url="{{ route('advance-tax-collection.status', $collection->id) }}"
                    data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                    data-bs-title="{{ __('Approve / Reject') }}"
                    class="btn btn-sm btn-outline-warning w-100">
                    {{ __('Pending') }}
                </a>
            @else
                <span class="badge bg-warning">{{ __('Pending') }}</span>
            @endif
        @endif
    </td>
    <td>{{ !empty($collection->approval_date) ? \Carbon\Carbon::parse($collection->approval_date)->format('d-M-Y') : '-' }}</td>
    <td><small>{{ !empty($collection->approvedBy->name) ? $collection->approvedBy->name : '-' }}</small></td>
    <td>
        <div class="action-btn d-flex align-items-center gap-1">
            @if($collection->status == 0 && \Auth::user()->type == 'company')
                <a href="#" data-url="{{ route('advance-tax-collection.status', $collection->id) }}"
                    data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                    data-bs-title="{{ __('Approve / Reject') }}"
                    class="btn btn-sm btn-outline-success">
                    <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                </a>
            @endif
            <a href="#" data-url="{{ route('advance-tax-collection.show', $collection->id) }}"
                data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                data-bs-title="{{ __('View') }}" class="btn btn-sm btn-outline-info">
                <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
            </a>
            @if($collection->status != 1 || \Auth::user()->type == 'company')
                <a href="#" data-url="{{ route('advance-tax-collection.edit', $collection->id) }}"
                    data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                    data-bs-title="{{ __('Edit') }}" class="btn btn-sm btn-outline-primary">
                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                </a>
            @endif
            @if($collection->status != 1)
                {!! Form::open([
                    'method' => 'DELETE',
                    'route' => ['advance-tax-collection.destroy', $collection->id],
                    'id' => 'delete-form-' . $collection->id,
                    'class' => 'd-inline',
                ]) !!}
                <a type="button" class="btn btn-sm btn-outline-danger bs-pass-para"
                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                    data-confirm-yes="document.getElementById('delete-form-{{ $collection->id }}').submit();"
                    data-bs-toggle="tooltip" data-bs-title="{{ __('Delete') }}">
                    <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                </a>
                {!! Form::close() !!}
            @endif
        </div>
    </td>
</tr>

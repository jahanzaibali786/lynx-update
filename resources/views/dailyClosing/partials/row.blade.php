<tr data-closing-row="{{ $closing->id }}">
    <td class="daily-closing-row-index">{{ $index ?? '' }}</td>
    <td>{{ $closing->from_date->format('d-M-Y') }}</td>
    <td>{{ $closing->to_date->format('d-M-Y') }}</td>
    <td>{{ $closing->deposit_date ? $closing->deposit_date->format('d-M-Y') : '-' }}</td>
    <td>{{ \Auth::user()->priceFormat($closing->total_income_received) }}</td>
    <td>{{ \Auth::user()->priceFormat($closing->total_income_deposited) }}</td>
    <td>
        @if($closing->difference > 0)
            <span class="text-danger font-weight-bold">({{ \Auth::user()->priceFormat(abs($closing->difference)) }})</span>
        @elseif($closing->difference < 0)
            <span class="text-success font-weight-bold">{{ \Auth::user()->priceFormat(abs($closing->difference)) }}</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>{{ $closing->issued_by ?? '-' }}</td>
    <td>{{ $closing->received_by ?? '-' }}</td>
    <td>
        <span class="badge badge-status p-2 px-3 rounded-pill bg-{{ $closing->status === 'approved' ? 'success' : 'warning' }}" id="status-badge-{{ $closing->id }}">
            {{ ucfirst($closing->status) }}
        </span>
    </td>
    <td class="text-end font-style">
        @can('show daily cash closing')
            <a href="{{ route('daily-closing.show', $closing->id) }}" target="_blank"
               class="btn btn-sm btn-outline-info align-items-center"
               data-bs-toggle="tooltip" data-bs-title="{{ __('View / Print') }}">
                <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
            </a>
        @endcan

        @can('approve daily cash closing')
            @php
                $isApproved = $closing->status === 'approved';
                $approvalTitle = $isApproved ? __('Mark as Pending') : __('Approve');
            @endphp
            <a href="#" class="btn btn-sm {{ $isApproved ? 'btn-outline-warning' : 'btn-outline-success' }} align-items-center toggle-approval"
               data-id="{{ $closing->id }}" data-url="{{ route('daily-closing.approve', $closing->id) }}"
               data-bs-toggle="tooltip" data-bs-title="{{ $approvalTitle }}">
                <span class="btn-inner--icon"><i class="ti {{ $isApproved ? 'ti-rotate-clockwise' : 'ti-circle-check' }}"></i></span>
            </a>
        @endcan

        @if($closing->status !== 'approved')
            @can('edit daily cash closing')
                <a href="#" class="btn btn-sm btn-outline-primary align-items-center"
                   data-url="{{ route('daily-closing.edit', $closing->id) }}" data-ajax-popup="true"
                   data-title="{{ __('Edit Daily Closing') }}" data-size="xl"
                   data-bs-toggle="tooltip" data-bs-title="{{ __('Edit') }}">
                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                </a>
            @endcan

            @can('delete daily cash closing')
                <a href="#" class="btn btn-sm btn-outline-danger align-items-center daily-closing-delete"
                   data-id="{{ $closing->id }}" data-url="{{ route('daily-closing.destroy', $closing->id) }}"
                   data-bs-toggle="tooltip" data-bs-title="{{ __('Delete') }}">
                    <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                </a>
            @endcan
        @endif
    </td>
</tr>

<tr id="leave-row-{{ $leave->id }}">
    <td>{{ $loopIteration ?? $loop->iteration ?? '' }}</td>
    @if (\Auth::user()->type != 'Employee')
        <td>{{ !empty(\Auth::user()->getEmployee($leave->employee_id)) ? \Auth::user()->getEmployee(@$leave->employee_id)->name : '-' }}</td>
    @endif
    <td>{{ !empty($leave->leaveType) ? $leave->leaveType->title : '' }}</td>
    <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
    <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
    <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
    <td>{{ $leave->total_leave_days }}</td>
    @php
        $leavesreasons = [
            'sick_leave' => __('Sick Leave'),
            'domestic_problem' => __('Domestic Problem'),
            'maternity' => __('Maternity'),
        ];
    @endphp
    <td>{{ $leavesreasons[$leave->leave_reason] ?? $leave->leave_reason }}</td>
    <td>{{ optional($leave->addedBy)->name ?? '-' }}</td>
    <td>
        @if ($leave->status == 'Pending')
            <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ $leave->status }}</div>
        @elseif($leave->status == 'Approved')
            <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $leave->status }}</div>
        @else
            <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $leave->status }}</div>
        @endif
    </td>
    @can('edit leave')
        <td>
            <div class="action-btn d-flex align-items-center gap-1">
                @if (\Auth::user()->type == 'Employee')
                    @if ($leave->status == 'Pending')
                        <a href="#" data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                            data-size="lg" data-ajax-popup="true" data-bs-title="{{ __('Edit Leave') }}"
                            class="btn mx-1 btn-sm btn-outline-primary align-items-center">
                            <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>
                    @endif
                @else
                    <a href="#" data-url="{{ URL::to('leave/' . $leave->id . '/action') }}" data-size="lg"
                        data-ajax-popup="true" class="btn mx-1 btn-sm btn-outline-warning align-items-center"
                        data-bs-title="{{ __('Leave Action') }}">
                        <span class="btn-inner--icon"><i class="ti ti-caret-right"></i></span> </a>
                    <a href="#" data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}" data-size="lg"
                        data-ajax-popup="true" class="btn mx-1 btn-sm btn-outline-primary align-items-center"
                        data-bs-toggle="{{ __('Edit Leave') }}" data-bs-title="{{ __('Edit') }}">
                        <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>
                @endif
                @can('delete leave')
                    {!! Form::open([
                        'method' => 'DELETE',
                        'route' => ['leave.destroy', $leave->id],
                        'id' => 'delete-form-' . $leave->id,
                    ]) !!}
                    <a href="#" class="btn mx-1 btn-sm btn-outline-danger align-items-center leave-ajax-delete"
                        data-bs-toggle="{{ __('Delete') }}" data-bs-title="{{ __('Delete') }}"
                        data-form-id="delete-form-{{ $leave->id }}"
                        data-confirm="{{ __('Are You Sure?') . ' ' . __('This action can not be undone. Do you want to continue?') }}">
                        <span class="btn-inner--icon"><i class="ti ti-trash"></i></span></a>
                    {!! Form::close() !!}
                @endcan
            </div>
        </td>
    @endcan
</tr>

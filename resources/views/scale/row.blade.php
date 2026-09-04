<tr id="employee-scale-row-{{ $scale->id }}" data-row-id="{{ $scale->id }}">
    <td>{{ $scale->id }}</td>
    <td>{{ !empty($scale->scale_no) ? $scale->scale_no : '' }}</td>
    <td>{{ !empty($scale->department) ? $scale->department->name : '' }}</td>
    @php
        $net_gross = 0;
    @endphp
    @foreach ($heads as $account)
        @php
            $headValue = @$scale->employeeScaleHeads->firstWhere('head', $account->id);
            if ($account->head == 'Initial Basic') {
                $headValue = @$scale->employeeScaleHeads->firstWhere('head', $account->id);
                $basic = $headValue ? $headValue->head_value : 0;
            }
            $net_gross += $headValue ? $headValue->head_value : 0;
        @endphp
        <td>{{ $headValue ? $headValue->head_value : '-' }}</td>
    @endforeach
    <td>{{ $net_gross }}</td>
    <td>{{ date('d-M-Y', strtotime($scale->effect_from)) }}</td>
    <td>{{ $scale->adhoc == 1 ? 'Yes' : 'No' }}</td>
    <td>{{ $scale->status == '1' ? 'Active' : 'In-Active' }}</td>
    @if (Gate::check('edit employee scale') || Gate::check('delete employee scale') || Gate::check('show employee scale'))
        <td>
            <div class="action-btn ms-2">
                @can('edit employee scale')
                    <a href="#" data-url="{{ route('employee_scale.edit', $scale->id) }}" data-size="lg"
                        data-ajax-popup="true" data-bs-toggle="Edit Employee Scale"
                        data-bs-title="{{ __('Edit Employee Scale') }}"
                        class="mx-1 btn mx-1 btn-sm btn-outline-primary">
                        <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                    </a>
                @endcan
                @can('delete employee scale')
                    {!! Form::open(['method' => 'DELETE', 'route' => ['employee_scale.destroy', $scale->id], 'id' => 'delete-form-' . $scale->id]) !!}
                    <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger ajax-delete employee-scale-delete"
                        data-form-id="delete-form-{{ $scale->id }}"
                        data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                        data-bs-title="{{ __('Delete') }}">
                        <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                    </a>
                    {!! Form::close() !!}
                @endcan
            </div>
        </td>
    @endif
</tr>

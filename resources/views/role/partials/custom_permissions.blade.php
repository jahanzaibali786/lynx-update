@if(!empty($customPermissions))
    <div class="col-md-12">
        <div class="form-group">
            <h6 class="my-3">{{ __('Assign Custom Permission to Roles') }}</h6>
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input align-middle custom_align_middle" id="custom_checkall">
                        </th>
                        <th>{{ __('Module') }}</th>
                        <th>{{ __('Permission') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customPermissions as $permissionId => $permissionName)
                        @php
                            $permissionParts = explode(' ', $permissionName, 2);
                            $customModuleName = $permissionParts[1] ?? $permissionName;
                        @endphp
                        <tr>
                            <td>
                                {{ Form::checkbox('permissions[]', $permissionId, isset($role) ? $role->hasPermissionTo($permissionName) : false, ['class' => 'form-check-input custom_checkall', 'id' => 'permission_custom_'.$permissionId]) }}
                            </td>
                            <td>{{ ucwords($customModuleName) }}</td>
                            <td>
                                {{ Form::label('permission_custom_'.$permissionId, $permissionName, ['class' => 'custom-control-label']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="alert alert-light mb-0">
        {{ __('No custom permissions found.') }}
    </div>
@endif

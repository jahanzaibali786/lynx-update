{{ Form::open(['route' => 'user.permissions.update', 'method' => 'POST']) }}
<div class="modal-body">
    @if($users->isEmpty())
        <div class="alert alert-warning mb-0">
            {{ __('No users found.') }}
        </div>
    @else
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('user_id', __('User'), ['class' => 'form-label']) }}
                    {{ Form::select('user_id', $users->mapWithKeys(fn($user) => [$user->id => $user->name.' - '.$user->email.' ('.ucfirst($user->type).')'])->toArray(), null, ['class' => 'form-control select', 'id' => 'direct_permission_user_id', 'required' => 'required']) }}
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3" style="max-height: 520px; overflow-y: auto;">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th width="5%">
                            <input type="checkbox" class="form-check-input" id="direct_permission_checkall">
                        </th>
                        <th>{{ __('Module') }}</th>
                        <th>{{ __('Permission') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissions as $permission)
                        @php
                            $permissionParts = explode(' ', $permission->name, 2);
                            $moduleName = $permissionParts[1] ?? $permission->name;
                        @endphp
                        <tr>
                            <td>
                                {{ Form::checkbox('permissions[]', $permission->id, false, ['class' => 'form-check-input direct-permission-check', 'id' => 'direct_permission_'.$permission->id]) }}
                            </td>
                            <td>{{ ucwords($moduleName) }}</td>
                            <td>
                                {{ Form::label('direct_permission_'.$permission->id, $permission->name, ['class' => 'custom-control-label']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    @if(!$users->isEmpty())
        <input type="submit" value="{{ __('Save') }}" class="btn btn-primary">
    @endif
</div>
{{ Form::close() }}

<script>
    $(document).ready(function () {
        const userPermissions = @json($userPermissions);

        function syncDirectPermissionChecks() {
            const userId = $('#direct_permission_user_id').val();
            const assignedPermissions = userPermissions[userId] || [];

            $('.direct-permission-check').each(function () {
                $(this).prop('checked', assignedPermissions.includes(parseInt($(this).val())));
            });

            $('#direct_permission_checkall').prop(
                'checked',
                $('.direct-permission-check').length > 0 && $('.direct-permission-check:not(:checked)').length === 0
            );
        }

        $('#direct_permission_user_id').on('change', syncDirectPermissionChecks);

        $('#direct_permission_checkall').on('change', function () {
            $('.direct-permission-check').prop('checked', this.checked);
        });

        $(document).on('change', '.direct-permission-check', function () {
            $('#direct_permission_checkall').prop(
                'checked',
                $('.direct-permission-check').length > 0 && $('.direct-permission-check:not(:checked)').length === 0
            );
        });

        syncDirectPermissionChecks();
    });
</script>

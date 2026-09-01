@php
    $bulkColumnDefinitions = [
        ['key' => 'sr', 'label' => 'Sr', 'group' => 'current'],
        ['key' => 'emp_no', 'label' => 'Emp No', 'group' => 'current'],
        ['key' => 'emp_name', 'label' => 'Emp Name', 'group' => 'current'],
        ['key' => 'doj', 'label' => 'D.O.J', 'group' => 'current'],
        ['key' => 'end_date', 'label' => 'End Date', 'group' => 'current'],
        ['key' => 'service_period', 'label' => 'Service Period', 'group' => 'current'],
        ['key' => 'designation', 'label' => 'Designation', 'group' => 'current'],
        ['key' => 'current_payscale', 'label' => 'Payscale No', 'group' => 'current'],
        ['key' => 'current_gross', 'label' => 'Gross Salary (Current)', 'group' => 'current'],
        ['key' => 'current_security', 'label' => 'Emp Security (Current)', 'group' => 'current'],
        ['key' => 'current_tax', 'label' => 'Tax Deduction', 'group' => 'current'],
        ['key' => 'current_eobi', 'label' => 'Eobi Emp (Current)', 'group' => 'current'],
        ['key' => 'current_net', 'label' => 'Net Sal', 'group' => 'current'],
        ['key' => 'child_concession', 'label' => 'Child Concession', 'group' => 'current'],
        ['key' => 'current_employer', 'label' => 'Employer Cont Eobi/PESSI (Current)', 'group' => 'current'],
        ['key' => 'current_ctc', 'label' => 'Cost to Company (Current)', 'group' => 'current'],
        ['key' => 'department', 'label' => 'Department', 'group' => 'revision'],
        ['key' => 'new_payscale', 'label' => 'New Payscale No', 'group' => 'revision'],
        ['key' => 'increment_pct', 'label' => '% Incr.', 'group' => 'revision'],
        ['key' => 'salary_value', 'label' => 'Salary Value', 'group' => 'revision'],
        ['key' => 'scale_search', 'label' => 'Scale Search', 'group' => 'revision'],
        ['key' => 'effect_from', 'label' => 'Effect From', 'group' => 'revision'],
        ['key' => 'new_gross', 'label' => 'Gross Salary (New)', 'group' => 'revision'],
        ['key' => 'new_security', 'label' => 'Emp Security (New)', 'group' => 'revision'],
        ['key' => 'new_eobi', 'label' => 'Eobi Emp (New)', 'group' => 'revision'],
        ['key' => 'new_tax', 'label' => 'Tax Ded', 'group' => 'revision'],
        ['key' => 'new_net', 'label' => 'Net Salary', 'group' => 'revision'],
        ['key' => 'new_employer', 'label' => 'Employer Cont Eobi/PESSI (New)', 'group' => 'revision'],
        ['key' => 'child_count', 'label' => 'No of Child', 'group' => 'revision'],
        ['key' => 'new_child', 'label' => 'Child Concession Rs.', 'group' => 'revision'],
        ['key' => 'new_ctc', 'label' => 'Cost to Company (New)', 'group' => 'revision'],
        ['key' => 'gross_diff', 'label' => 'Gross Salary Diff', 'group' => 'revision'],
    ];
@endphp

{{ Form::open(['route' => ['employee-salary-proporal.bulk.store'], 'method' => 'post', 'id' => 'bulk_salary_proposal_form']) }}
<div class="modal-body bulk-salary-modal-body position-relative">
    @if(strtolower((string) Auth::user()->type) === 'company')
        <div class="bulk-settings-toolbar d-flex justify-content-end align-items-center mb-2" id="bulk-column-settings-anchor">
            <button type="button"
                    class="btn btn-sm bulk-column-settings-btn"
                    id="bulk-column-toggle-btn"
                    title="{{ __('Column Settings') }}"
                    aria-label="{{ __('Column Settings') }}"
                    aria-expanded="false">
                <span class="bulk-settings-gear" aria-hidden="true">&#9881;</span>
                <span class="bulk-settings-label">{{ __('Columns') }}</span>
            </button>

            <div class="bulk-column-toggle-menu" id="bulk-column-toggle-menu" aria-hidden="true">
                <div class="bulk-column-menu-head">
                    <div>
                        <div class="bulk-column-menu-title">{{ __('Column Settings') }}</div>
                        <div class="bulk-column-menu-subtitle">{{ __('Choose columns visible to branches') }}</div>
                    </div>
                    <div class="bulk-column-menu-actions">
                        <small id="bulk-column-save-status" class="text-muted"></small>
                        <button type="button"
                                id="bulk-column-save-btn"
                                class="btn btn-primary btn-sm bulk-column-save-btn"
                                title="{{ __('Save Column Settings') }}"
                                aria-label="{{ __('Save Column Settings') }}">
                            <i class="ti ti-device-floppy me-1"></i>
                            <span>{{ __('Save Settings') }}</span>
                        </button>
                    </div>
                </div>
                <div class="bulk-column-menu-list">
                    @foreach($bulkColumnDefinitions as $column)
                        <label class="bulk-column-toggle-item">
                            <input type="checkbox"
                                   class="bulk-column-setting-checkbox"
                                   data-column="{{ $column['key'] }}"
                                   {{ ($columnVisibility[$column['key']] ?? true) ? 'checked' : '' }}>
                            <span>{{ $column['label'] }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="bulk-branch-access-section">
                    <div class="bulk-branch-access-title">{{ __('Disable Inputs for Branch') }}</div>
                    <div class="bulk-branch-access-subtitle">{{ __('HO can lock these revision controls while keeping them available at company level.') }}</div>
                    <div class="bulk-branch-access-list">
                        @foreach([
                            'new_payscale' => 'New Payscale Dropdown',
                            'increment_pct' => '% Increment Input',
                            'salary_value' => 'Salary Value Input',
                        ] as $restrictionKey => $restrictionLabel)
                            <label class="bulk-column-toggle-item bulk-branch-access-item">
                                <input type="checkbox"
                                       class="bulk-branch-restriction-checkbox"
                                       data-restriction="{{ $restrictionKey }}"
                                       {{ ($branchInputRestrictions[$restrictionKey] ?? false) ? 'checked' : '' }}>
                                <span>{{ $restrictionLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 align-items-end mb-3 bulk-filter-row">
        <div class="col-md-2">
            {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}
            {{ Form::select('session_id', $sessions, $activeSessionId ?? null, ['class' => 'form-control select', 'id' => 'session_id']) }}
        </div>
        <div class="col-md-2">
            {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
            @if ($branchLocked)
                {{ Form::select('branch_id_display', $branches, $defaultBranchId, ['class' => 'form-control select', 'id' => 'branch_id_display', 'disabled' => 'disabled']) }}
                {{ Form::hidden('branch_id', $defaultBranchId, ['id' => 'branch_id']) }}
            @else
                {{ Form::select('branch_id', $branches, $defaultBranchId, ['class' => 'form-control select', 'id' => 'branch_id']) }}
            @endif
        </div>
        <div class="col-md-2">
            {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
            {{ Form::select('department_id', $departments, null, ['class' => 'form-control select', 'id' => 'department_id']) }}
        </div>
        <div class="col-md-2">
            {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
            <select class="select form-control" id="designation_id" name="designation_id" data-placeholder="{{ __('Select Designation ...') }}">
                <option value="">{{__('Select any Designation')}}</option>
            </select>
        </div>

        <div class="col-md-2">
            <button type="button" class="btn btn-primary w-100" id="bulk-search-btn">Search</button>
        </div>
    </div>

    <div id="bulk-result-summary" class="alert alert-info d-none"></div>
    <div id="bulk-employee-results" class="d-grid gap-3"></div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Save Draft Proposals') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}


<style>
    #bulk-employee-results {
        --bulk-border: #d9e2ec;
        --bulk-border-strong: #bcccdc;
        --bulk-text: #243b53;
        --bulk-muted: #627d98;
        --bulk-current: #eef6ff;
        --bulk-current-strong: #d9ebff;
        --bulk-revision: #f2fbf6;
        --bulk-revision-strong: #d9f3e3;
        --bulk-row-alt: #fbfdff;
        --bulk-hover: #f4f8fc;
    }

    #bulk-employee-results .bulk-salary-table-wrap {
        width: 100%;
        overflow-x: auto;
        padding: 4px 4px 14px;
        border: 1px solid var(--bulk-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
        scrollbar-width: thin;
    }

    #bulk-employee-results .bulk-salary-table {
        min-width: 3500px;
        width: max-content;
        margin: 0;
        font-size: 12px;
        color: var(--bulk-text);
        white-space: nowrap;
        border-collapse: separate;
        border-spacing: 0;
    }

    #bulk-employee-results .bulk-salary-table th,
    #bulk-employee-results .bulk-salary-table td {
        padding: 10px 12px;
        vertical-align: middle;
        border-right: 1px solid var(--bulk-border);
        border-bottom: 1px solid var(--bulk-border);
        background-clip: padding-box;
    }

    #bulk-employee-results .bulk-salary-table th:first-child,
    #bulk-employee-results .bulk-salary-table td:first-child {
        border-left: 1px solid var(--bulk-border);
    }

    #bulk-employee-results .bulk-session-row th {
        position: sticky;
        top: 0;
        z-index: 8;
        padding: 11px 14px;
        font-weight: 800;
        font-size: 13px;
        letter-spacing: .02em;
        color: #17324d;
        border-top: 1px solid var(--bulk-border-strong);
        border-bottom: 2px solid var(--bulk-border-strong);
    }

    #bulk-employee-results #bulk-current-group-head {
        background: var(--bulk-current-strong);
    }

    #bulk-employee-results #bulk-revision-group-head {
        background: var(--bulk-revision-strong);
    }

    #bulk-employee-results .bulk-column-row th {
        position: sticky;
        top: 41px;
        z-index: 7;
        min-height: 46px;
        background: #f8fafc;
        color: #334e68;
        font-weight: 700;
        text-align: center;
        line-height: 1.3;
        border-bottom: 2px solid var(--bulk-border-strong);
    }

    /* Give current/revision halves a subtle visual identity. */
    #bulk-employee-results .bulk-column-row th:nth-child(-n+18) {
        background: var(--bulk-current);
    }

    #bulk-employee-results .bulk-column-row th:nth-child(n+19) {
        background: var(--bulk-revision);
    }

    #bulk-employee-results .bulk-employee-row:nth-child(even) td {
        background: var(--bulk-row-alt);
    }

    #bulk-employee-results .bulk-employee-row:hover td {
        background: var(--bulk-hover);
    }

    #bulk-employee-results .bulk-employee-row.table-warning td {
        background: #fff8e6;
    }

    #bulk-employee-results .bulk-employee-row.table-warning:hover td {
        background: #fff3cd;
    }

    #bulk-employee-results .bulk-salary-table td[class*="bulk-current"],
    #bulk-employee-results .bulk-salary-table td[class*="bulk-new"],
    #bulk-employee-results .bulk-salary-table td[class*="gross"],
    #bulk-employee-results .bulk-salary-table td[class*="net"],
    #bulk-employee-results .bulk-salary-table td[class*="tax"],
    #bulk-employee-results .bulk-salary-table td[class*="security"] {
        font-variant-numeric: tabular-nums;
    }

    #bulk-employee-results .bulk-spacer-head,
    #bulk-employee-results .bulk-spacer-cell {
        min-width: 16px !important;
        width: 16px !important;
        padding: 0 !important;
        background: #edf2f7 !important;
        border-left: 2px solid var(--bulk-border-strong) !important;
        border-right: 2px solid var(--bulk-border-strong) !important;
    }

    #bulk-employee-results .bulk-sr-head,
    #bulk-employee-results .bulk-sr-cell {
        min-width: 68px;
        text-align: center;
    }

    #bulk-employee-results .bulk-salary-table select,
    #bulk-employee-results .bulk-salary-table input[type="text"],
    #bulk-employee-results .bulk-salary-table input[type="number"],
    #bulk-employee-results .bulk-salary-table input[type="date"] {
        min-height: 34px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
        padding: 5px 8px;
        box-shadow: none;
    }

    #bulk-employee-results .bulk-salary-table select:focus,
    #bulk-employee-results .bulk-salary-table input:focus {
        border-color: #7aa7d9;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, .10);
        outline: 0;
    }

    .bulk-salary-modal-body {
        overflow: visible !important;
        padding-top: 1rem;
    }

    .bulk-settings-toolbar {
        position: relative;
        z-index: 30;
        min-height: 36px;
        overflow: visible !important;
    }

    .bulk-branch-access-section {
        margin: 8px 10px 10px;
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
    }
    .bulk-branch-access-title {
        font-size: 12px;
        font-weight: 800;
        color: #334155;
        margin-bottom: 2px;
    }
    .bulk-branch-access-subtitle {
        font-size: 11px;
        color: #64748b;
        line-height: 1.35;
        margin-bottom: 7px;
    }
    .bulk-branch-access-list {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
    }
    .bulk-branch-access-item {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 6px 8px;
        margin: 0;
        background: #f8fafc;
    }
    .bulk-branch-locked-control {
        background-color: #f1f5f9 !important;
        cursor: not-allowed !important;
        opacity: .78;
    }

    .bulk-column-settings-btn {
        min-width: 94px;
        height: 34px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid #94a3b8 !important;
        border-radius: 6px;
        background: #334155 !important;
        color: #fff !important;
        box-shadow: 0 1px 2px rgba(15,23,42,.12);
        font-weight: 600;
        line-height: 1;
        cursor: pointer;
    }

    .bulk-column-settings-btn:hover,
    .bulk-column-settings-btn:focus,
    .bulk-column-settings-btn[aria-expanded="true"] {
        color: #fff !important;
        border-color: #1e293b !important;
        background: #1e293b !important;
    }

    .bulk-settings-gear {
        font-size: 17px;
        line-height: 1;
    }

    .bulk-settings-label {
        font-size: 12px;
        line-height: 1;
    }

    .bulk-column-toggle-menu {
        position: fixed !important;
        z-index: 2147483000 !important;
        left: 50% !important;
        top: 50% !important;
        right: auto !important;
        bottom: auto !important;
        transform: translate(-50%, -50%) !important;
        width: min(440px, calc(100vw - 32px));
        max-height: min(680px, calc(100vh - 48px));
        overflow: hidden;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
        display: none;
    }

    .bulk-column-toggle-menu.show {
        display: flex !important;
        flex-direction: column;
    }

    .bulk-column-menu-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 14px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
        flex: 0 0 auto;
    }

    .bulk-column-menu-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }

    .bulk-column-menu-subtitle {
        margin-top: 2px;
        font-size: 11px;
        line-height: 1.3;
        color: #64748b;
    }

    #bulk-column-save-status {
        min-width: 42px;
        font-size: 11px;
        text-align: right;
        white-space: nowrap;
    }

    .bulk-column-menu-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
    }

    .bulk-column-save-btn {
        min-width: 118px;
        height: 34px;
        padding: 0 12px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        opacity: 1 !important;
    }

    .bulk-column-save-btn:disabled {
        opacity: .7 !important;
        cursor: wait;
    }

    .bulk-column-menu-list {
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 6px;
    }

    .bulk-column-toggle-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 8px;
        margin: 0;
        cursor: pointer;
        font-size: 12px;
        line-height: 1.25;
        border-radius: 6px;
        color: #334155;
    }

    .bulk-column-toggle-item input {
        flex: 0 0 auto;
        margin: 0;
    }

    .bulk-column-toggle-item span {
        min-width: 0;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .bulk-column-toggle-item:hover {
        background: #f1f5f9;
    }

    @media (max-width: 767.98px) {
        .bulk-column-settings-btn {
            min-width: 88px;
        }
    }

    #bulk-employee-results .bulk-increment-input,
    #bulk-employee-results .bulk-salary-value-input {
        min-width: 115px;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    #bulk-employee-results .bulk-scale-search-btn {
        min-width: 82px;
        min-height: 34px;
        background: #2563eb !important;
        border-color: #1d4ed8 !important;
        color: #fff !important;
        font-weight: 700;
        box-shadow: 0 1px 2px rgba(37, 99, 235, .18);
    }

    #bulk-employee-results .bulk-scale-search-btn:hover,
    #bulk-employee-results .bulk-scale-search-btn:focus {
        background: #1d4ed8 !important;
        border-color: #1e40af !important;
        color: #fff !important;
    }

    #bulk-employee-results .bulk-add-scale-wrap {
        min-width: 155px;
        white-space: normal;
        line-height: 1.3;
    }

    #bulk-employee-results .bulk-draft-message,
    #bulk-employee-results .bulk-scale-message {
        margin-top: 5px;
        line-height: 1.25;
        white-space: normal;
    }

    @media (max-width: 991.98px) {
        #bulk-employee-results .bulk-salary-table th,
        #bulk-employee-results .bulk-salary-table td {
            padding: 8px 10px;
        }
    }

/* Percentage column only: compact 3-4 digit field without affecting other columns */
#salary-proposal-bulk-table th[data-column="increment_percent"],
#salary-proposal-bulk-table td[data-column="increment_percent"] {
    width: 58px !important;
    min-width: 58px !important;
    max-width: 58px !important;
    white-space: nowrap;
}
#salary-proposal-bulk-table .increment-percent,
#salary-proposal-bulk-table input[name*="increment_percent"],
#salary-proposal-bulk-table input[data-role="increment-percent"],
#salary-proposal-bulk-table .sp-increment-percent {
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
    padding-left: 4px !important;
    padding-right: 4px !important;
    text-align: center;
}


</style>

@php
    $resolvedBranchInputRestrictions = $branchInputRestrictions ?? [
        'new_payscale' => false,
        'increment_pct' => false,
        'salary_value' => false,
    ];
@endphp

<script>
    $(document).ready(function() {
        var payScaleOptions = [];
        var searchUrl = '{{ route('employee-salary-proporal.bulk.search') }}';
        var previewUrl = '{{ route('employee-salary-proporal.bulk.preview') }}';
        var autoScaleCreateUrl = '{{ route('employee_scale.bulk_auto_create') }}';
        var canCreateScale = @json(Auth::user()->type === 'branch' || Auth::user()->can('create employee scale'));
        var columnVisibilityUpdateUrl = '{{ route('employee-salary-proporal.bulk.column-visibility') }}';
        var isCompanyUser = @json(Auth::user()->type === 'company');
        var isBranchUser = @json(Auth::user()->type === 'branch');
        var bulkColumns = @json($bulkColumnDefinitions);
        var bulkColumnVisibility = @json($columnVisibility ?? []);
        var bulkBranchInputRestrictions = @json($resolvedBranchInputRestrictions);

        $.each(bulkColumns, function(_, column) {
            if (typeof bulkColumnVisibility[column.key] === 'undefined') {
                bulkColumnVisibility[column.key] = true;
            } else {
                bulkColumnVisibility[column.key] = !!bulkColumnVisibility[column.key];
            }
        });

        $.each(['new_payscale', 'increment_pct', 'salary_value'], function(_, key) {
            bulkBranchInputRestrictions[key] = bulkBranchInputRestrictions[key] === true || bulkBranchInputRestrictions[key] === 1 || bulkBranchInputRestrictions[key] === '1';
        });

        var savedBulkColumnVisibility = $.extend({}, bulkColumnVisibility);
        var savedBulkBranchInputRestrictions = $.extend({}, bulkBranchInputRestrictions);
        var bulkColumnSettingsDirty = false;

        function setBulkColumnSettingsDirty(isDirty) {
            bulkColumnSettingsDirty = !!isDirty;
            var $saveButton = $('#bulk-column-save-btn');
            $saveButton.toggleClass('is-dirty', bulkColumnSettingsDirty);

            if (bulkColumnSettingsDirty) {
                $('#bulk-column-save-status')
                    .removeClass('text-danger text-success')
                    .addClass('text-muted')
                    .text('Unsaved changes');
            } else {
                $('#bulk-column-save-status').text('');
            }
        }

        function refreshBulkColumnSettingsDirtyState() {
            var dirty = false;
            $.each(bulkColumns, function(_, column) {
                var key = column.key;
                if ((bulkColumnVisibility[key] !== false) !== (savedBulkColumnVisibility[key] !== false)) {
                    dirty = true;
                    return false;
                }
            });

            if (!dirty) {
                $.each(['new_payscale', 'increment_pct', 'salary_value'], function(_, key) {
                    if (!!bulkBranchInputRestrictions[key] !== !!savedBulkBranchInputRestrictions[key]) {
                        dirty = true;
                        return false;
                    }
                });
            }

            setBulkColumnSettingsDirty(dirty);
        }

        function applyBranchInputRestrictions(scope) {
            if (!isBranchUser) {
                return;
            }

            var $scope = scope ? $(scope) : $(document);
            var selectors = {
                new_payscale: '.bulk-payscale-select',
                increment_pct: '.bulk-increment-input',
                salary_value: '.bulk-salary-value-input'
            };

            $.each(selectors, function(key, selector) {
                if (!bulkBranchInputRestrictions[key]) {
                    return;
                }

                $scope.find(selector)
                    .prop('disabled', true)
                    .addClass('bulk-branch-locked-control')
                    .attr('title', 'Disabled by HO settings');
            });
        }

        if ($('#branch_id').length && $('#branch_id').val() === '' && $('#branch_id_display').length) {
            $('#branch_id').val($('#branch_id_display').val());
        }

        function escapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function money(value) {
            var number = parseFloat(value || 0);
            if (isNaN(number)) {
                number = 0;
            }
            return number.toFixed(2);
        }

        function todayDate() {
            var now = new Date();
            var local = new Date(now.getTime() - (now.getTimezoneOffset() * 60000));
            return local.toISOString().slice(0, 10);
        }

        function scalesForDepartment(departmentId) {
            return $.grep(payScaleOptions, function(scale) {
                return !departmentId || String(scale.department_id) === String(departmentId);
            });
        }

        function findScaleById(scaleId) {
            var found = null;
            $.each(payScaleOptions, function(_, scale) {
                if (String(scale.id) === String(scaleId || '')) {
                    found = scale;
                    return false;
                }
            });
            return found;
        }

        function scaleSalaryValue(scale) {
            if (!scale) return 0;
            var explicit = parseFloat(scale.salary_value);
            if (!isNaN(explicit) && explicit > 0) return explicit;
            return parseFloat(scale.initial_basic || 0)
                + parseFloat(scale.house_rent || 0)
                + parseFloat(scale.medical || 0);
        }

        function findScaleBySalaryValue(departmentId, amount) {
            amount = parseFloat(amount);
            if (isNaN(amount)) {
                return null;
            }

            var found = null;
            $.each(scalesForDepartment(departmentId), function(_, scale) {
                if (Math.abs(scaleSalaryValue(scale) - amount) <= 0.01) {
                    found = scale;
                    return false;
                }
            });
            return found;
        }

        function scaleOptionsHtml(selectedId, departmentId) {
            var html = '<option value="">Select Scale</option>';
            $.each(scalesForDepartment(departmentId), function(index, scale) {
                var selected = String(scale.id) === String(selectedId) ? 'selected' : '';
                html += '<option value="' + scale.id + '" ' + selected + '>' + escapeHtml(scale.label) + '</option>';
            });
            return html;
        }

        function loadDesignations(departmentId, selectedDesignationId) {
            $('#designation_id').html('<option value="">{{__('Select any Designation')}}</option>');
            if (!departmentId) {
                return;
            }

            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    department_id: departmentId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $.each(data, function(key, value) {
                        var selected = String(key) === String(selectedDesignationId || '') ? 'selected' : '';
                        $('#designation_id').append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
                    });
                }
            });
        }

        function buildHiddenInputs(index, row) {
            return ''
                + '<div class="d-none bulk-hidden-inputs">'
                + '<input type="hidden" name="proposals[' + index + '][employee_id]" value="' + escapeHtml(row.employee_id) + '">'
                + '<input type="hidden" name="proposals[' + index + '][emp_no]" value="' + escapeHtml(row.employee_no) + '">'
                + '<input type="hidden" class="proposal-payscale" name="proposals[' + index + '][payscale]" value="' + escapeHtml(row.selected_scale_id || '') + '">'
                + '<input type="hidden" class="proposal-income-tax" name="proposals[' + index + '][income_tax]" value="' + money(row.new_tax) + '">'
                + '<input type="hidden" class="proposal-other-deduction" name="proposals[' + index + '][other_deduction]" value="' + money(row.new_emp_security) + '">'
                + '<input type="hidden" class="proposal-eobi" name="proposals[' + index + '][EOBI]" value="' + money(row.new_eobi_emp) + '">'
                + '<input type="hidden" class="proposal-gross" name="proposals[' + index + '][gross]" value="' + money(row.new_gross) + '">'
                + '<input type="hidden" class="proposal-net-salary" name="proposals[' + index + '][net_salary]" value="' + money(row.new_net_salary) + '">'
                + '<input type="hidden" class="proposal-pay-method" name="proposals[' + index + '][pay_method]" value="">'
                + '<input type="hidden" class="proposal-bank-name" name="proposals[' + index + '][bank_name]" value="">'
                + '<input type="hidden" class="proposal-bank-account" name="proposals[' + index + '][bank_account]" value="">'
                + '</div>';
        }

        function spacerCell() {
            return '<td class="bulk-spacer-cell"></td>';
        }

        function columnClass(key, extraClass) {
            var hiddenClass = bulkColumnVisibility[key] === false ? ' d-none' : '';
            return 'bulk-col bulk-col-' + key + hiddenClass + (extraClass ? ' ' + extraClass : '');
        }

        function buildColumnToggle() {
            // Column visibility control now lives with the filters (company/HO only).
            // This result toolbar only keeps the master Effect From control.
            return ''
                + '<div class="d-flex justify-content-start align-items-end gap-2 flex-wrap mb-2">'
                + '<div style="min-width:190px;">'
                + '<label for="bulk-master-effect-from" class="form-label mb-1">Effect From</label>'
                + '<input type="date" class="form-control form-control-sm" id="bulk-master-effect-from" value="' + todayDate() + '">'
                + '</div>'
                + '</div>';
        }

        function applyColumnVisibility() {
            $.each(bulkColumns, function(_, column) {
                var isVisible = bulkColumnVisibility[column.key] !== false;
                $('.bulk-col-' + column.key)
                    .toggleClass('d-none', !isVisible)
                    .toggle(isVisible);
            });

            var currentVisible = 0;
            var revisionVisible = 0;
            $.each(bulkColumns, function(_, column) {
                if (bulkColumnVisibility[column.key] !== false) {
                    if (column.group === 'current') currentVisible++;
                    if (column.group === 'revision') revisionVisible++;
                }
            });

            // Two spacer columns belong to current section; one spacer belongs to revision section.
            $('#bulk-current-group-head').attr('colspan', currentVisible + 2).toggle(currentVisible > 0);
            $('#bulk-revision-group-head').attr('colspan', revisionVisible + 1).toggle(revisionVisible > 0);
        }

        function buildEmployeeRow(row, index) {
            return ''
                + '<tr class="bulk-employee-row' + (row.same_scale_draft_exists ? ' table-warning' : '') + '" id="bulk-row-' + row.employee_id + '" data-employee-id="' + row.employee_id + '" data-department-id="' + escapeHtml(row.department_id || '') + '" data-index="' + index + '" data-increment-base-basic="' + money(scaleSalaryValue(findScaleById(row.selected_scale_id)) || row.selected_scale_initial_basic || 0) + '" data-increment-base-scale="' + escapeHtml(row.selected_scale_id || '') + '">'
                + '<td class="' + columnClass('sr', 'text-center bulk-sr-cell') + '"><div class="d-flex align-items-center justify-content-center gap-1"><input type="checkbox" class="bulk-row-check" checked><span>' + (index + 1) + '</span></div></td>'
                + '<td class="' + columnClass('emp_no') + '">' + escapeHtml(row.employee_no) + '</td>'
                + '<td class="' + columnClass('emp_name') + '">' + escapeHtml(row.employee_name) + '</td>'
                + '<td class="' + columnClass('doj') + '">' + escapeHtml(row.doj) + '</td>'
                + '<td class="' + columnClass('end_date') + '">' + escapeHtml(row.end_date) + '</td>'
                + '<td class="' + columnClass('service_period') + '">' + escapeHtml(row.service_duration) + '</td>'
                + '<td class="' + columnClass('designation') + '">' + escapeHtml(row.designation_name) + '</td>'
                + '<td class="' + columnClass('current_payscale') + '">' + escapeHtml(row.current_scale_label) + '</td>'
                + '<td class="' + columnClass('current_gross', 'text-end') + '">' + money(row.current_gross) + '</td>'
                + '<td class="' + columnClass('current_security', 'text-end') + '">' + money(row.current_emp_security) + '</td>'
                + '<td class="' + columnClass('current_tax', 'text-end') + '">' + money(row.current_tax) + '</td>'
                + '<td class="' + columnClass('current_eobi', 'text-end') + '">' + money(row.current_eobi_emp) + '</td>'
                + '<td class="' + columnClass('current_net', 'text-end') + '">' + money(row.current_net_salary) + '</td>'
                + spacerCell()
                + '<td class="' + columnClass('child_concession', 'text-end') + '">' + money(row.child_amount) + '</td>'
                + '<td class="' + columnClass('current_employer', 'text-end') + '">' + money(row.eobi_employer) + ' / ' + money(row.pessi_employer) + '</td>'
                + '<td class="' + columnClass('current_ctc', 'text-end') + '">' + money(row.current_ctc) + '</td>'
                + spacerCell()
                + '<td class="' + columnClass('department') + '">' + escapeHtml(row.department_name) + '</td>'
                + '<td class="' + columnClass('new_payscale') + '">'
                + '<select class="form-control form-control-sm bulk-payscale-select" data-index="' + index + '" data-employee-id="' + row.employee_id + '">'
                + scaleOptionsHtml(row.selected_scale_id, row.department_id)
                + '</select>'
                + '<div class="small text-danger fw-semibold mt-1 bulk-draft-message ' + (row.same_scale_draft_exists ? '' : 'd-none') + '">' + escapeHtml(row.same_scale_draft_message || '') + '</div>'
                + buildHiddenInputs(index, row)
                + '</td>'
                + '<td class="' + columnClass('increment_pct') + '"><input type="text" inputmode="decimal" autocomplete="off" class="form-control form-control-sm bulk-increment-input" placeholder="%" value="" aria-label="Percentage increment"></td>'
                + '<td class="' + columnClass('salary_value') + '"><input type="number" step="0.01" min="0" class="form-control form-control-sm bulk-salary-value-input" value="' + money(scaleSalaryValue(findScaleById(row.selected_scale_id)) || row.selected_scale_initial_basic || 0) + '"></td>'
                + '<td class="' + columnClass('scale_search', 'bulk-scale-result-cell') + '">'
                + '<div class="bulk-add-scale-wrap"></div>'
                + '</td>'
                + '<td class="' + columnClass('effect_from') + '"><input type="date" class="form-control form-control-sm bulk-effect-from" style="min-width:145px;" name="proposals[' + index + '][effect_from]" value="' + escapeHtml($('#bulk-master-effect-from').val() || todayDate()) + '"></td>'
                + '<td class="' + columnClass('new_gross', 'text-end bulk-new-gross') + '">' + money(row.new_gross) + '</td>'
                + '<td class="' + columnClass('new_security', 'text-end bulk-new-security') + '">' + money(row.new_emp_security) + '</td>'
                + '<td class="' + columnClass('new_eobi', 'text-end bulk-new-eobi') + '">' + money(row.new_eobi_emp) + '</td>'
                + '<td class="' + columnClass('new_tax', 'text-end bulk-new-tax') + '">' + money(row.new_tax) + '</td>'
                + '<td class="' + columnClass('new_net', 'text-end bulk-new-net') + '">' + money(row.new_net_salary) + '</td>'
                + spacerCell()
                + '<td class="' + columnClass('new_employer', 'text-end bulk-new-employer') + '">' + money(row.eobi_employer) + ' / ' + money(row.pessi_employer) + '</td>'
                + '<td class="' + columnClass('child_count', 'text-center bulk-new-child-count') + '">' + escapeHtml(row.child_count) + '</td>'
                + '<td class="' + columnClass('new_child', 'text-end bulk-new-child') + '">' + money(row.new_child_amount) + '</td>'
                + '<td class="' + columnClass('new_ctc', 'text-end bulk-new-ctc') + '">' + money(row.new_ctc) + '</td>'
                + '<td class="' + columnClass('gross_diff', 'text-end bulk-gross-diff') + '">' + money(row.gross_salary_diff) + '</td>'
                + '</tr>';
        }

        function syncHiddenFields($row, employee) {
            $row.find('.proposal-payscale').val(employee.selected_scale_id || '');
            $row.find('.proposal-income-tax').val(money(employee.new_tax));
            $row.find('.proposal-other-deduction').val(money(employee.new_emp_security));
            $row.find('.proposal-eobi').val(money(employee.new_eobi_emp));
            $row.find('.proposal-gross').val(money(employee.new_gross));
            $row.find('.proposal-net-salary').val(money(employee.new_net_salary));
        }

        function setRowSelectionState($row, enabled) {
            $row.toggleClass('table-secondary', !enabled);
            $row.find('input, select, textarea, button').not('.bulk-row-check').prop('disabled', !enabled);

            // Row selection must never re-enable controls locked by HO for branch users.
            if (enabled) {
                applyBranchInputRestrictions($row);
            }
        }

        function syncCheckAllState() {
            var $checks = $('.bulk-row-check');
            var total = $checks.length;
            var checked = $checks.filter(':checked').length;
            $('#bulk-check-all').prop('checked', total > 0 && checked === total);
            $('#bulk-check-all').prop('indeterminate', checked > 0 && checked < total);
        }

        function renderEmployees(rows) {
            if (!rows.length) {
                $('#bulk-employee-results').html('<div class="alert alert-warning mb-0">No active employees matched the selected filters.</div>');
                return;
            }

            var selectedSession = $('#session_id option:selected').text() || '';
            var revisionTitle = selectedSession && selectedSession.toLowerCase().indexOf('select') === -1
                ? 'Salaries Revision Session ' + selectedSession
                : 'Salaries Revision';

            var html = ''
                + buildColumnToggle()
                + '<div class="table-responsive bulk-salary-table-wrap">'
                + '<table id="salary-proposal-bulk-table" class="table table-bordered table-sm align-middle mb-0 bulk-salary-table bulk-proposal-table">'
                + '<thead class="table_heads">'
                + '<tr class="bulk-session-row">'
                + '<th id="bulk-current-group-head" colspan="18" class="text-center bulk-current-group">Current Salary Detail</th>'
                + '<th id="bulk-revision-group-head" colspan="14" class="text-center bulk-revision-group">' + escapeHtml(revisionTitle) + '</th>'
                + '</tr>'
                + '<tr class="bulk-column-row">'
                + '<th class="' + columnClass('sr', 'text-center bulk-sr-head') + '"><div class="d-flex align-items-center justify-content-center gap-1"><input type="checkbox" id="bulk-check-all" title="Select all"><span>Sr</span></div></th>'
                + '<th class="' + columnClass('emp_no') + '">Emp No</th>'
                + '<th class="' + columnClass('emp_name') + '">Emp Name</th>'
                + '<th class="' + columnClass('doj') + '">D.O.J</th>'
                + '<th class="' + columnClass('end_date') + '">End Date</th>'
                + '<th class="' + columnClass('service_period') + '">Service Period</th>'
                + '<th class="' + columnClass('designation') + '">Designation</th>'
                + '<th class="' + columnClass('current_payscale') + '">Payscale No</th>'
                + '<th class="' + columnClass('current_gross') + '">Gross Salary</th>'
                + '<th class="' + columnClass('current_security') + '">Emp Security</th>'
                + '<th class="' + columnClass('current_tax') + '">Tax Deduction</th>'
                + '<th class="' + columnClass('current_eobi') + '">Eobi Emp</th>'
                + '<th class="' + columnClass('current_net') + '">Net Sal</th>'
                + '<th class="bulk-spacer-head"></th>'
                + '<th class="' + columnClass('child_concession') + '">Child Concession</th>'
                + '<th class="' + columnClass('current_employer') + '">Employer Cont Eobi/PESSI</th>'
                + '<th class="' + columnClass('current_ctc') + '">Cost to Company</th>'
                + '<th class="bulk-spacer-head"></th>'
                + '<th class="' + columnClass('department') + '">Department</th>'
                + '<th class="' + columnClass('new_payscale') + '">New Payscale No</th>'
                + '<th class="' + columnClass('increment_pct') + '">% Incr.</th>'
                + '<th class="' + columnClass('salary_value') + '">Salary Value</th>'
                + '<th class="' + columnClass('scale_search') + '">Search</th>'
                + '<th class="' + columnClass('effect_from') + '">Effect From</th>'
                + '<th class="' + columnClass('new_gross') + '">Gross Salary</th>'
                + '<th class="' + columnClass('new_security') + '">Emp Security</th>'
                + '<th class="' + columnClass('new_eobi') + '">Eobi Emp</th>'
                + '<th class="' + columnClass('new_tax') + '">Tax Ded</th>'
                + '<th class="' + columnClass('new_net') + '">Net Salary</th>'
                + '<th class="bulk-spacer-head"></th>'
                + '<th class="' + columnClass('new_employer') + '">Employer Cont Eobi/PESSI</th>'
                + '<th class="' + columnClass('child_count') + '">No of Child</th>'
                + '<th class="' + columnClass('new_child') + '">Child Concession Rs.</th>'
                + '<th class="' + columnClass('new_ctc') + '">Cost to Company</th>'
                + '<th class="' + columnClass('gross_diff') + '">Gross Salary Diff</th>'
                + '</tr>'
                + '</thead>'
                + '<tbody>';

            $.each(rows, function(index, row) {
                html += buildEmployeeRow(row, index);
            });

            html += '</tbody></table></div>';
            $('#bulk-employee-results').html(html);
            $('.bulk-employee-row').each(function() {
                var $row = $(this);
                var hasExistingActive = $row.find('.bulk-draft-message').not('.d-none').length > 0;
                if (hasExistingActive) {
                    $row.addClass('table-warning');
                }
                setRowSelectionState($row, $row.find('.bulk-row-check').is(':checked'));
            });
            syncCheckAllState();
            applyColumnVisibility();
            applyBranchInputRestrictions($('#bulk-employee-results'));
        }

        function applyPreview(rowId, employee) {
            var $row = $('#bulk-row-' + rowId);
            if (!$row.length) {
                return;
            }

            $row.find('.bulk-new-gross').text(money(employee.new_gross));
            $row.find('.bulk-new-security').text(money(employee.new_emp_security));
            $row.find('.bulk-new-eobi').text(money(employee.new_eobi_emp));
            $row.find('.bulk-new-tax').text(money(employee.new_tax));
            $row.find('.bulk-new-net').text(money(employee.new_net_salary));
            $row.find('.bulk-new-employer').text(money(employee.eobi_employer) + ' / ' + money(employee.pessi_employer));
            $row.find('.bulk-new-child-count').text(employee.child_count || 0);
            $row.find('.bulk-new-child').text(money(employee.new_child_amount));
            $row.find('.bulk-new-ctc').text(money(employee.new_ctc));
            $row.find('.bulk-gross-diff').text(money(employee.gross_salary_diff));
            $row.find('.bulk-payscale-select').val(employee.selected_scale_id || '');

            var selectedScale = findScaleById(employee.selected_scale_id);
            if (selectedScale) {
                $row.find('.bulk-salary-value-input').attr('data-selected-basic', money(scaleSalaryValue(selectedScale)));
            }

            var $check = $row.find('.bulk-row-check');
            var $message = $row.find('.bulk-draft-message');
            // Existing Draft/Pending proposals are allowed in Bulk mode.
            // They are highlighted as a warning and will be UPDATED by the backend.
            $check.prop('disabled', false);
            if (employee.same_scale_draft_exists) {
                $message.text((employee.same_scale_draft_message || 'An active proposal already exists for this payscale.') + ' It will be updated when submitted.')
                    .removeClass('d-none');
                $row.addClass('table-warning');
            } else {
                $message.addClass('d-none').text('');
                $row.removeClass('table-warning');
            }
            setRowSelectionState($row, $check.is(':checked'));

            syncHiddenFields($row, employee);
            syncCheckAllState();
        }

        function loadPreview(rowId) {
            var $row = $('#bulk-row-' + rowId);
            if (!$row.length) {
                return;
            }

            $.ajax({
                url: previewUrl,
                type: 'GET',
                dataType: 'json',
                data: {
                    employee_id: $row.data('employee-id'),
                    payscale_id: $row.find('.bulk-payscale-select').val(),
                    department_id: $row.data('department-id') || $('#department_id').val(),
                    session_id: $('#session_id').val()
                },
                success: function(response) {
                    if (response.success && response.employee) {
                        applyPreview(rowId, response.employee);
                    }
                }
            });
        }

        function ensureScaleInOptions(scale) {
            if (!scale || !scale.id) {
                return;
            }

            var exists = false;
            $.each(payScaleOptions, function(i, item) {
                if (String(item.id) === String(scale.id)) {
                    payScaleOptions[i] = $.extend({}, item, scale);
                    exists = true;
                    return false;
                }
            });

            if (!exists) {
                payScaleOptions.push(scale);
            }
        }

        function ensureScaleOptionInRow($row, scale) {
            if (!scale || !scale.id) {
                return;
            }

            ensureScaleInOptions(scale);
            var $select = $row.find('.bulk-payscale-select');
            if (!$select.find('option[value="' + scale.id + '"]').length) {
                $select.append('<option value="' + escapeHtml(scale.id) + '">' + escapeHtml(scale.label || scale.scale_no || ('Scale #' + scale.id)) + '</option>');
            }
        }

        function clearScaleSearchMessage($row) {
            $row.find('.bulk-add-scale-wrap').empty();
        }

        function showNoScaleMatch($row, amount) {
            var html = '<div class="small text-warning fw-semibold" style="font-size: 12px;">No matching scale found for Salary Value ' + money(amount) + '.</div>';

            if (canCreateScale) {
                html += '<button type="button" class="btn btn-sm btn-success mt-1 bulk-add-scale-btn">Create Scale</button>';
            }

            $row.find('.bulk-add-scale-wrap').html(html);
        }

        function selectMatchedScale($row, scale, preserveIncrementBase) {
            if (!scale) {
                return;
            }

            ensureScaleOptionInRow($row, scale);
            $row.find('.bulk-payscale-select').val(scale.id);

            if (!preserveIncrementBase) {
                $row.data('increment-base-basic', scaleSalaryValue(scale));
                $row.data('increment-base-scale', scale.id);
            }

            clearScaleSearchMessage($row);
            loadPreview($row.data('employee-id'));
        }

        function searchScaleForRow($row, preserveIncrementBase) {
            var amount = parseFloat($row.find('.bulk-salary-value-input').val());
            if (isNaN(amount) || amount < 0) {
                showNoScaleMatch($row, 0);
                return;
            }

            var scale = findScaleBySalaryValue($row.data('department-id'), amount);
            if (scale) {
                selectMatchedScale($row, scale, !!preserveIncrementBase);
            } else {
                showNoScaleMatch($row, amount);
            }
        }

        function calculateIncrementAndSearch($row) {
            var percent = parseFloat($row.find('.bulk-increment-input').val());
            var base = parseFloat($row.data('increment-base-basic') || 0);

            if (isNaN(percent) || isNaN(base) || base <= 0) {
                return;
            }

            var amount = base + ((base * percent) / 100);
            $row.find('.bulk-salary-value-input').val(money(amount));
            searchScaleForRow($row, true);
        }

        var incrementTimers = {};
        $(document).on('input', '.bulk-increment-input', function() {
            // Direct decimal entry only (e.g. 2.5). No number-stepper controls.
            var raw = String($(this).val() || '').replace(/[^0-9.]/g, '');
            var firstDot = raw.indexOf('.');
            if (firstDot !== -1) {
                raw = raw.substring(0, firstDot + 1) + raw.substring(firstDot + 1).replace(/\./g, '');
            }
            $(this).val(raw);

            var $row = $(this).closest('.bulk-employee-row');
            var employeeId = $row.data('employee-id');
            clearTimeout(incrementTimers[employeeId]);
            incrementTimers[employeeId] = setTimeout(function() {
                calculateIncrementAndSearch($row);
            }, 350);
        });

        var salaryValueTimers = {};
        $(document).on('input', '.bulk-salary-value-input', function() {
            var $row = $(this).closest('.bulk-employee-row');
            var employeeId = $row.data('employee-id');

            $row.find('.bulk-increment-input').val('');
            clearScaleSearchMessage($row);

            clearTimeout(salaryValueTimers[employeeId]);
            salaryValueTimers[employeeId] = setTimeout(function() {
                var value = parseFloat($row.find('.bulk-salary-value-input').val() || 0);
                if (!isNaN(value) && value > 0) {
                    searchScaleForRow($row, false);
                }
            }, 400);
        });

        function bulkProposalNotify(type, message) {
            type = type === 'success' ? 'success' : 'error';
            message = message || (type === 'success' ? 'Completed successfully.' : 'Something went wrong.');

            // Prefer the application's own notification helper when it exists.
            if (typeof window.show_toastr === 'function') {
                window.show_toastr(type, message, type);
                return;
            }

            // Use toastr only when the library is actually loaded.
            if (window.toastr && typeof window.toastr[type] === 'function') {
                window.toastr[type](message);
                return;
            }

            // SweetAlert is a safe secondary fallback when available.
            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    icon: type,
                    title: type === 'success' ? 'Success' : 'Error',
                    text: message,
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Last-resort fallback. Do not throw and break the remaining JS.
            if (type === 'error') {
                console.error(message);
            } else {
                console.log(message);
            }
        }

        $(document).on('click', '.bulk-add-scale-btn', function() {
            var $button = $(this);
            var $row = $button.closest('.bulk-employee-row');
            var departmentId = $row.data('department-id');
            var salaryValue = parseFloat($row.find('.bulk-salary-value-input').val() || 0);
            var effectFrom = $row.find('.bulk-effect-from').val() || $('#bulk-master-effect-from').val() || todayDate();

            if (!departmentId || isNaN(salaryValue) || salaryValue <= 0) {
                bulkProposalNotify('error', 'Department and a valid Salary Value are required to create a scale.');
                return;
            }

            var originalText = $button.text();
            $button.prop('disabled', true).text('Creating...');

            $.ajax({
                url: autoScaleCreateUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val(),
                    department_id: departmentId,
                    salary_value: salaryValue,
                    effect_from: effectFrom
                },
                success: function(response) {
                    if (!response.success || !response.scale) {
                        bulkProposalNotify('error', response.message || 'Unable to create employee scale.');
                        return;
                    }

                    var scale = response.scale;
                    ensureScaleInOptions(scale);
                    ensureScaleOptionInRow($row, scale);

                    // Select the newly-created scale, then explicitly execute the same
                    // row refresh used for a manual payscale change.  Calling the shared
                    // handler directly is intentional: some custom-select implementations do
                    // not forward a programmatic jQuery change event reliably.
                    var $scaleSelect = $row.find('.bulk-payscale-select');
                    $scaleSelect.val(String(scale.id));

                    clearScaleSearchMessage($row);
                    bulkProposalNotify('success', response.message || 'Employee Scale created and selected.');

                    // Wait until the appended option/value is committed to the DOM.
                    setTimeout(function() {
                        $scaleSelect.val(String(scale.id));
                        handleBulkPayscaleChange($scaleSelect);

                        // Also emit the DOM/jQuery event for any other listeners in the app.
                        $scaleSelect.trigger('change.bulkAutoCreated');
                    }, 0);
                },
                error: function(xhr) {
                    var message = 'Unable to create employee scale.';
                    if (xhr.responseJSON) {
                        message = xhr.responseJSON.message
                            || (xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors)[0][0] : message);
                    }
                    bulkProposalNotify('error', message);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });

        $(document).on('change', '#bulk-master-effect-from', function() {
            var value = $(this).val();
            $('.bulk-effect-from').val(value);
        });

        function handleBulkPayscaleChange(selectOrJquery) {
            var $select = selectOrJquery && selectOrJquery.jquery
                ? selectOrJquery
                : $(selectOrJquery);
            var $row = $select.closest('.bulk-employee-row');

            if (!$row.length) {
                return;
            }

            var rowId = $row.data('employee-id');
            var selectedScaleId = $select.val();
            var scale = findScaleById(selectedScaleId);

            clearScaleSearchMessage($row);
            $row.find('.bulk-increment-input').val('');
            $row.find('.proposal-payscale').val(selectedScaleId || '');

            if (scale) {
                var initialBasic = scaleSalaryValue(scale);
                $row.data('increment-base-basic', initialBasic);
                $row.data('increment-base-scale', scale.id);
                $row.find('.bulk-salary-value-input').val(money(initialBasic));
            } else {
                $row.data('increment-base-basic', 0);
                $row.data('increment-base-scale', '');
                $row.find('.bulk-salary-value-input').val(money(0));
            }

            // This request is the authoritative refresh for all salary values in the row.
            loadPreview(rowId);
        }

        $(document).on('change.bulkPayscale', '.bulk-payscale-select', function() {
            handleBulkPayscaleChange($(this));
        });

        $(document).on('change', '.bulk-row-check', function() {
            var $row = $(this).closest('.bulk-employee-row');
            setRowSelectionState($row, $(this).is(':checked'));
            syncCheckAllState();
        });

        $(document).on('change', '#bulk-check-all', function() {
            var checked = $(this).is(':checked');
            $('.bulk-row-check').each(function() {
                $(this).prop('checked', checked);
                setRowSelectionState($(this).closest('.bulk-employee-row'), checked);
            });
            syncCheckAllState();
        });

        // Keep exactly one settings panel for the active AJAX modal.
        // Older body-appended panels from previously closed modals are removed immediately.
        var $bulkColumnMenu = $('#bulk_salary_proposal_form #bulk-column-toggle-menu').last();
        $('body > #bulk-column-toggle-menu').not($bulkColumnMenu).remove();

        $(document).off('.bulkColumnSettings');

        $(document).on('click.bulkColumnSettings', '#bulk-column-toggle-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!$bulkColumnMenu.length) {
                $bulkColumnMenu = $('#bulk_salary_proposal_form #bulk-column-toggle-menu').last();
            }
            if (!$bulkColumnMenu.length) {
                return;
            }

            // Move the one current panel to body so no modal/container overflow can clip it.
            if (!$bulkColumnMenu.parent().is('body')) {
                $('body > #bulk-column-toggle-menu').remove();
                $bulkColumnMenu.appendTo(document.body);
            }

            var willOpen = !$bulkColumnMenu.hasClass('show');
            $bulkColumnMenu.toggleClass('show', willOpen)
                .attr('aria-hidden', willOpen ? 'false' : 'true');
            $(this).attr('aria-expanded', willOpen ? 'true' : 'false');
        });

        $(document).on('click.bulkColumnSettings', '#bulk-column-toggle-menu', function(e) {
            e.stopPropagation();
        });

        $(document).on('click.bulkColumnSettings', function() {
            if ($bulkColumnMenu && $bulkColumnMenu.length) {
                $bulkColumnMenu.removeClass('show').attr('aria-hidden', 'true');
            }
            $('#bulk-column-toggle-btn').attr('aria-expanded', 'false');
        });

        $(document).on('change.bulkColumnSettings', '.bulk-column-setting-checkbox', function(e) {
            // IMPORTANT: checkbox changes are preview-only. No AJAX is allowed here.
            // The renamed class also prevents stale handlers from older AJAX-modal loads
            // (which listened on .bulk-column-toggle-check) from firing.
            e.stopImmediatePropagation();

            var $checkbox = $(this);
            var key = String($checkbox.data('column') || '');
            bulkColumnVisibility[key] = $checkbox.is(':checked');

            applyColumnVisibility();
            refreshBulkColumnSettingsDirtyState();
        });

        $(document).on('change.bulkColumnSettings', '.bulk-branch-restriction-checkbox', function(e) {
            e.stopImmediatePropagation();
            var key = String($(this).data('restriction') || '');
            if (!key) {
                return;
            }
            bulkBranchInputRestrictions[key] = $(this).is(':checked');
            refreshBulkColumnSettingsDirtyState();
        });

        $(document).on('click.bulkColumnSettings', '#bulk-column-save-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!isCompanyUser) {
                return;
            }

            var $button = $(this);
            var originalHtml = $button.html();
            $button.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...');

            $('#bulk-column-save-status')
                .removeClass('text-danger text-success')
                .addClass('text-muted')
                .text('Saving...');

            var columnsToSave = {};
            $('.bulk-column-setting-checkbox').each(function() {
                var key = String($(this).data('column') || '');
                if (key) {
                    columnsToSave[key] = $(this).is(':checked') ? 1 : 0;
                }
            });

            // Fallback for any configured column not currently rendered in the menu.
            $.each(bulkColumns, function(_, column) {
                if (typeof columnsToSave[column.key] === 'undefined') {
                    columnsToSave[column.key] = bulkColumnVisibility[column.key] !== false ? 1 : 0;
                }
            });

            var branchDisabledToSave = {};
            $('.bulk-branch-restriction-checkbox').each(function() {
                var key = String($(this).data('restriction') || '');
                if (key) {
                    branchDisabledToSave[key] = $(this).is(':checked') ? 1 : 0;
                }
            });

            $.ajax({
                url: columnVisibilityUpdateUrl,
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                },
                data: {
                    _token: '{{ csrf_token() }}',
                    columns: columnsToSave,
                    branch_disabled: branchDisabledToSave
                },
                success: function(response) {
                    if (!response || response.success !== true) {
                        $('#bulk-column-save-status')
                            .removeClass('text-muted text-success')
                            .addClass('text-danger')
                            .text((response && response.message) ? response.message : 'Could not save.');
                        return;
                    }

                    if (response.columns) {
                        $.each(response.columns, function(key, value) {
                            bulkColumnVisibility[key] = !!value;
                        });
                    }
                    if (response.branch_disabled) {
                        $.each(response.branch_disabled, function(key, value) {
                            bulkBranchInputRestrictions[key] = !!value;
                        });
                    }

                    savedBulkColumnVisibility = $.extend({}, bulkColumnVisibility);
                    savedBulkBranchInputRestrictions = $.extend({}, bulkBranchInputRestrictions);
                    setBulkColumnSettingsDirty(false);
                    applyColumnVisibility();

                    $('#bulk-column-save-status')
                        .removeClass('text-muted text-danger')
                        .addClass('text-success')
                        .text('Saved');
                },
                error: function(xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Could not save column settings.';

                    $('#bulk-column-save-status')
                        .removeClass('text-muted text-success')
                        .addClass('text-danger')
                        .text(message);
                },
                complete: function() {
                    $button.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // When this AJAX modal closes, remove the body-appended panel so reopening cannot create duplicates.
        $(document).on('hidden.bs.modal.bulkColumnSettings', '.modal', function() {
            if ($bulkColumnMenu && $bulkColumnMenu.length) {
                $bulkColumnMenu.remove();
            }
            $('#bulk-column-toggle-btn').attr('aria-expanded', 'false');
        });

        $('#department_id').on('change', function() {
            loadDesignations($(this).val(), '');
        });

        if ($('#department_id').val()) {
            loadDesignations($('#department_id').val(), $('#designation_id').val());
        }

        $('#bulk-search-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Searching...');
            $.ajax({
                url: searchUrl,
                type: 'GET',
                dataType: 'json',
                data: $('#bulk_salary_proposal_form').serialize(),
                success: function(response) {
                    if (response.success) {
                        payScaleOptions = response.pay_scales || [];
                        $('#bulk-result-summary').removeClass('d-none').text((response.employees || []).length + ' employee(s) found.');
                        renderEmployees(response.employees || []);
                    } else {
                        $('#bulk-employee-results').html('<div class="alert alert-danger mb-0">Unable to load employees.</div>');
                    }
                },
                error: function(xhr) {
                    var message = 'Unable to load employees.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    $('#bulk-employee-results').html('<div class="alert alert-danger mb-0">' + escapeHtml(message) + '</div>');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        ajaxModalForm({
            formSelector: '#bulk_salary_proposal_form',
            submitText: 'Creating...',
            closeOnSuccess: false,
            showToast: true,
            onSuccess: function(response) {
                closeActiveBootstrapModal();

                // Same post-save behavior as Single Create: refresh the proposal index
                // so newly-created/updated bulk proposals are visible immediately.
                var redirectUrl = (response && response.redirect_url)
                    ? response.redirect_url
                    : '{{ route('employee-salary-proporal.index') }}';

                window.location.href = redirectUrl;
            }
        });
    });
</script>


<style>
/* Professional bulk salary table overrides */
.bulk-proposal-table-wrap {
    background: #fff !important;
    border: 1px solid #dfe3e8 !important;
    border-radius: 6px !important;
    box-shadow: none !important;
}
.bulk-proposal-table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    background: #fff !important;
}
.bulk-proposal-table thead th {
    background: #f7f8fa !important;
    color: #2f3742 !important;
    border-right: 1px solid #e3e6ea !important;
    border-bottom: 1px solid #cfd5dc !important;
    font-weight: 600 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
    padding: 8px 9px !important;
}
.bulk-proposal-table thead th.current-group,
.bulk-proposal-table thead th.revision-group,
.bulk-proposal-table .current-group,
.bulk-proposal-table .revision-group {
    background: #f2f4f6 !important;
    color: #2f3742 !important;
}
.bulk-proposal-table tbody td {
    background: #fff !important;
    border-right: 1px solid #eceff2 !important;
    border-bottom: 1px solid #eceff2 !important;
    padding: 8px 9px !important;
    vertical-align: middle !important;
}
.bulk-proposal-table tbody tr:nth-child(even) td {
    background: #fbfbfc !important;
}
.bulk-proposal-table tbody tr:hover td {
    background: #f7f9fb !important;
}
.bulk-proposal-table .salary-separator,
.bulk-proposal-table .section-separator {
    border-left: 2px solid #cfd5dc !important;
}
.bulk-proposal-table input.form-control,
.bulk-proposal-table select.form-control,
.bulk-proposal-table .custom-select {
    min-height: 32px !important;
    height: 32px !important;
    border-color: #cfd5dc !important;
    border-radius: 4px !important;
    box-shadow: none !important;
    font-size: 12px !important;
}
.bulk-proposal-table input.form-control:focus,
.bulk-proposal-table select.form-control:focus {
    border-color: #8b95a1 !important;
    box-shadow: 0 0 0 1px rgba(80,90,100,.08) !important;
}
.bulk-proposal-table .btn-search-scale {
    background: #334155 !important;
    border-color: #334155 !important;
    color: #fff !important;
    box-shadow: none !important;
}
.bulk-proposal-table .btn-search-scale:hover {
    background: #1f2937 !important;
    border-color: #1f2937 !important;
}
/* Compact percentage column */
.bulk-proposal-table th.percentage-col,
.bulk-proposal-table td.percentage-col {
    width: 46px !important;
    min-width: 46px !important;
    max-width: 46px !important;
    padding-left: 5px !important;
    padding-right: 5px !important;
    text-align: center !important;
}
.bulk-proposal-table .percentage-col input,
.bulk-proposal-table input.percentage-input,
.bulk-proposal-table input[name*="increment"],
.bulk-proposal-table input[name*="percentage"] {
    width: 30px !important;
    min-width: 30px !important;
    max-width: 30px !important;
    padding: 4px 2px !important;
    text-align: center !important;
}
/* Existing-active rows: understated, not colorful */
.bulk-proposal-table tr.existing-active td,
.bulk-proposal-table tr.has-existing-proposal td {
    background: #fafafa !important;
}
.bulk-proposal-table .existing-warning,
.bulk-proposal-table .draft-warning,
.bulk-proposal-table .pending-warning {
    color: #6b7280 !important;
    background: transparent !important;
    border: 0 !important;
    font-size: 11px !important;
}
</style>


<style>
/* Exact widths for the four marked Salary Revision columns only. */
#salary-proposal-bulk-table {
    width: max-content !important;
    min-width: 100% !important;
    table-layout: auto !important;
}

#salary-proposal-bulk-table th.bulk-col-new_payscale,
#salary-proposal-bulk-table td.bulk-col-new_payscale {
    width: 165px !important;
    min-width: 165px !important;
    max-width: 165px !important;
}
#salary-proposal-bulk-table td.bulk-col-new_payscale .bulk-payscale-select,
#salary-proposal-bulk-table td.bulk-col-new_payscale select {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    box-sizing: border-box !important;
}

#salary-proposal-bulk-table th.bulk-col-increment_pct,
#salary-proposal-bulk-table td.bulk-col-increment_pct {
    width: 66px !important;
    min-width: 66px !important;
    max-width: 66px !important;
    padding-left: 5px !important;
    padding-right: 5px !important;
}
#salary-proposal-bulk-table td.bulk-col-increment_pct .bulk-increment-input {
    width: 48px !important;
    min-width: 48px !important;
    max-width: 48px !important;
    margin: 0 auto !important;
    padding-left: 4px !important;
    padding-right: 4px !important;
    box-sizing: border-box !important;
}

#salary-proposal-bulk-table th.bulk-col-salary_value,
#salary-proposal-bulk-table td.bulk-col-salary_value {
    width: 138px !important;
    min-width: 138px !important;
    max-width: 138px !important;
    padding-left: 6px !important;
    padding-right: 6px !important;
}
#salary-proposal-bulk-table td.bulk-col-salary_value .bulk-salary-value-input {
    width: 124px !important;
    min-width: 124px !important;
    max-width: 124px !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
}

#salary-proposal-bulk-table th.bulk-col-scale_search,
#salary-proposal-bulk-table td.bulk-col-scale_search {
    width: 92px !important;
    min-width: 92px !important;
    max-width: 92px !important;
    padding-left: 6px !important;
    padding-right: 6px !important;
}
#salary-proposal-bulk-table td.bulk-col-scale_search .bulk-scale-search-btn {
    width: 70px !important;
    min-width: 70px !important;
    max-width: 70px !important;
    padding-left: 7px !important;
    padding-right: 7px !important;
    white-space: nowrap !important;
    box-sizing: border-box !important;
}
</style>

<style>
/* Salary revision: automatic search result column (no Search button). */
#salary-proposal-bulk-table th.bulk-col-scale_search,
#salary-proposal-bulk-table td.bulk-col-scale_search {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    padding-left: 7px !important;
    padding-right: 7px !important;
}
#salary-proposal-bulk-table td.bulk-scale-result-cell,
#salary-proposal-bulk-table td.bulk-col-scale_search .bulk-add-scale-wrap {
    white-space: normal !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
    line-height: 1.35 !important;
    font-size: 11px !important;
}
#salary-proposal-bulk-table td.bulk-col-scale_search .bulk-add-scale-btn {
    white-space: normal !important;
    width: 100% !important;
    padding: 4px 6px !important;
    line-height: 1.2 !important;
}

/* Professional header fallback; existing .table_heads theme can still override globally. */
#salary-proposal-bulk-table thead.table_heads th {
    background: #e8f0f7 !important;
    color: #1f3b53 !important;
    border-color: #c5d3df !important;
    font-weight: 700 !important;
}
#salary-proposal-bulk-table thead.table_heads .bulk-session-row th {
    background: #d9e7f1 !important;
    color: #17354b !important;
    letter-spacing: .02em !important;
}
#salary-proposal-bulk-table thead.table_heads .bulk-column-row th {
    background: #eef4f8 !important;
}
</style>


<style>
/* Final professional bulk-table refinements. */
#salary-proposal-bulk-table td.bulk-col-scale_search,
#salary-proposal-bulk-table td.bulk-scale-result-cell {
    width: 170px !important;
    min-width: 170px !important;
    max-width: 170px !important;
    white-space: normal !important;
    overflow: hidden !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
    vertical-align: top !important;
}

#salary-proposal-bulk-table td.bulk-col-scale_search .bulk-add-scale-wrap,
#salary-proposal-bulk-table td.bulk-scale-result-cell .bulk-add-scale-wrap {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
}

#salary-proposal-bulk-table td.bulk-col-scale_search .bulk-add-scale-btn,
#salary-proposal-bulk-table td.bulk-scale-result-cell .bulk-add-scale-btn {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
    padding: 4px 6px !important;
    margin: 5px 0 0 !important;
    line-height: 1.15 !important;
    font-size: 11px !important;
}

/* Distinct, restrained section headers. */
#salary-proposal-bulk-table thead.table_heads .bulk-current-group {
    background: #dbe7f1 !important;
    color: #18364d !important;
    border-color: #b9cbd9 !important;
}

#salary-proposal-bulk-table thead.table_heads .bulk-revision-group {
    background: #dcebe4 !important;
    color: #214b3a !important;
    border-color: #b9d1c4 !important;
}

/* Current-detail column headers: cool blue-gray. */
#salary-proposal-bulk-table thead.table_heads .bulk-column-row th:nth-child(-n+18) {
    background: #edf3f7 !important;
    color: #29465b !important;
    border-color: #cbd8e2 !important;
}

/* Revision column headers: muted sage-gray. */
#salary-proposal-bulk-table thead.table_heads .bulk-column-row th:nth-child(n+19) {
    background: #edf5f0 !important;
    color: #315343 !important;
    border-color: #c9d9d0 !important;
}
</style>


<style>
/* Final header + percentage refinements. */
#salary-proposal-bulk-table thead.table_heads .bulk-current-group,
#salary-proposal-bulk-table thead.table_heads #bulk-current-group-head {
    background: #315b78 !important;
    color: #ffffff !important;
    border-color: #274b64 !important;
}

#salary-proposal-bulk-table thead.table_heads .bulk-revision-group,
#salary-proposal-bulk-table thead.table_heads #bulk-revision-group-head {
    background: #426b59 !important;
    color: #ffffff !important;
    border-color: #36594a !important;
}

/* Keep the detailed header rows related to their main section without making them loud. */
#salary-proposal-bulk-table thead.table_heads .bulk-column-row th:nth-child(-n+18) {
    background: #e8f0f5 !important;
    color: #27465c !important;
    border-color: #c3d3df !important;
}
#salary-proposal-bulk-table thead.table_heads .bulk-column-row th:nth-child(n+19) {
    background: #eaf2ed !important;
    color: #335546 !important;
    border-color: #c7d7ce !important;
}

/* Slightly wider percentage field, but still compact. */
#salary-proposal-bulk-table th.bulk-col-increment_pct,
#salary-proposal-bulk-table td.bulk-col-increment_pct {
    width: 78px !important;
    min-width: 78px !important;
    max-width: 78px !important;
    padding-left: 6px !important;
    padding-right: 6px !important;
}
#salary-proposal-bulk-table td.bulk-col-increment_pct .bulk-increment-input {
    width: 60px !important;
    min-width: 60px !important;
    max-width: 60px !important;
    margin: 0 auto !important;
    padding: 4px 6px !important;
    text-align: right !important;
    box-sizing: border-box !important;
}
</style>

{{ Form::open(['url' => 'employee-salary-proporal', 'method' => 'post', 'id' => 'sp-single-create-form']) }}

<style>
    /* Single salary proposal modal: use full viewport height and prevent custom-select clipping */
    .modal.show:has(#sp-single-create-form) .modal-dialog {
        height: calc(100vh - 1rem);
        max-height: calc(100vh - 1rem);
        margin-top: .5rem;
        margin-bottom: .5rem;
    }

    .modal.show:has(#sp-single-create-form) .modal-content {
        height: 100%;
        max-height: 100%;
        overflow: visible;
    }

    .modal.show:has(#sp-single-create-form) .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        max-height: none !important;
        overflow: visible !important;
        position: relative;
    }

    .modal.show:has(#sp-single-create-form) {
        overflow-x: hidden !important;
        overflow-y: auto !important;
        padding-right: 0 !important;
    }

    .modal.show:has(#sp-single-create-form) .modal-dialog {
        min-height: calc(100vh - 1rem);
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    .modal.show:has(#sp-single-create-form) .modal-content {
        min-height: calc(100vh - 1rem);
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    /* Keep dropdowns above modal content and other sections */
    .modal.show:has(#sp-single-create-form) .custom-select-wrapper,
    .modal.show:has(#sp-single-create-form) .select2-container {
        position: relative;
        z-index: 1065;
    }

    .modal.show:has(#sp-single-create-form) .custom-select-dropdown,
    .modal.show:has(#sp-single-create-form) .custom-select-options,
    .modal.show:has(#sp-single-create-form) .select2-dropdown {
        z-index: 1085 !important;
        max-height: 320px;
        overflow-y: auto;
    }

    .modal.show:has(#sp-single-create-form) .custom-select-wrapper {
        overflow: visible !important;
    }

    .modal.show:has(#sp-single-create-form) .custom-select-wrapper > *,
    .modal.show:has(#sp-single-create-form) .custom-select-menu,
    .modal.show:has(#sp-single-create-form) .custom-select-list {
        z-index: 1095 !important;
    }

    @media (max-width: 767.98px) {
        .modal.show:has(#sp-single-create-form) .modal-dialog {
            height: calc(100vh - .5rem);
            max-height: calc(100vh - .5rem);
            margin: .25rem;
            max-width: calc(100% - .5rem);
        }
    }
</style>
<div class="modal-body salary-proposal-compact">
    {{ Form::hidden('session_id', $activeSessionId ?? null, ['id' => 'session_id']) }}
    {{ Form::hidden('department_id', null, ['id' => 'department_id']) }}
    {{ Form::hidden('bank_account', null, ['id' => 'bank_account']) }}
    {{ Form::hidden('gross_display', 0, ['id' => 'gross']) }}
    {{ Form::hidden('net', 0, ['id' => 'net']) }}
    {{ Form::hidden('itax', 0, ['id' => 'itax']) }}
    {{ Form::hidden('base_scale_gross', 0, ['id' => 'base_scale_gross']) }}
    {{ Form::hidden('cost_to_company', 0, ['id' => 'cost_to_company']) }}

    <style>
        .salary-proposal-compact{--sp-border:#e5e7eb;--sp-muted:#64748b;--sp-text:#1e293b;--sp-primary:#eef2ff;--sp-info:#f0f9ff;--sp-success:#ecfdf5;--sp-warning:#fff7ed;background:#f8fafc;padding:.85rem}
        .salary-proposal-compact .sp-shell{display:flex;flex-direction:column;gap:.7rem}
        .salary-proposal-compact .sp-panel{background:#fff;border:1px solid var(--sp-border);border-radius:12px;overflow:visible;box-shadow:0 2px 8px rgba(15,23,42,.04);position:relative}
        /* Selection dropdowns must always render above the already-loaded employee-detail panels. */
        .salary-proposal-compact .sp-shell > .sp-panel:first-child{z-index:500;overflow:visible!important}
        .salary-proposal-compact #employee-proposal-details{position:relative;z-index:20;overflow:visible!important}
        /* The first details panel contains Payscale and Pay Method. Keep this entire
           stacking context above the tabs panel so its custom dropdown menus cannot
           render underneath the Additions/Deductions section. */
        .salary-proposal-compact #employee-proposal-details > .sp-panel:first-child{
            position:relative;
            z-index:2200!important;
            overflow:visible!important;
        }
        .salary-proposal-compact #employee-proposal-details > .sp-panel:nth-child(2){
            position:relative;
            z-index:10!important;
            overflow:visible!important;
        }
        .salary-proposal-compact .sp-panel.sp-select-open{z-index:4000!important}
        .salary-proposal-compact .sp-tabs{display:flex;gap:.35rem;padding:.55rem .6rem 0;background:#fff;border-bottom:1px solid var(--sp-border);flex-wrap:wrap}
        .salary-proposal-compact .sp-tab-btn{border:0;background:transparent;color:var(--sp-muted);font-size:.9rem;font-weight:800;padding:.5rem .75rem;border-radius:8px 8px 0 0;cursor:pointer}
        .salary-proposal-compact .sp-tab-btn.active[data-target="additions"]{background:#ecfdf5;color:#047857}
        .salary-proposal-compact .sp-tab-btn.active[data-target="deductions"]{background:#fff7ed;color:#b45309}
        .salary-proposal-compact .sp-tab-pane{display:none;padding:.8rem}
        .salary-proposal-compact .sp-tab-pane.active{display:block}
        .salary-proposal-compact .custom-select-wrapper{overflow:visible!important}
        .salary-proposal-compact .custom-select-wrapper.open,
        .salary-proposal-compact .custom-select-wrapper.active,
        .salary-proposal-compact .custom-select-wrapper:focus-within{position:relative!important;z-index:5000!important}
        .salary-proposal-compact .custom-select-options,
        .salary-proposal-compact .custom-select-dropdown,
        .salary-proposal-compact .custom-select-menu,
        .salary-proposal-compact .custom-select-list{position:absolute!important;z-index:5100!important}
        .salary-proposal-compact .sp-head{padding:.65rem .8rem;border-bottom:1px solid var(--sp-border);display:flex;justify-content:space-between;align-items:center;gap:.5rem}
        .salary-proposal-compact .sp-head.primary{background:var(--sp-primary)} .salary-proposal-compact .sp-head.info{background:var(--sp-info)} .salary-proposal-compact .sp-head.success{background:var(--sp-success)} .salary-proposal-compact .sp-head.warning{background:var(--sp-warning)}
        .salary-proposal-compact .sp-title{margin:0;font-size:1rem;font-weight:800;color:var(--sp-text)} .salary-proposal-compact .sp-sub{font-size:.76rem;color:var(--sp-muted);margin-top:.08rem}
        .salary-proposal-compact .sp-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr))}
        .salary-proposal-compact .sp-item{padding:.7rem .8rem;min-height:66px;border-right:1px solid var(--sp-border);border-bottom:1px solid var(--sp-border)}
        .salary-proposal-compact .sp-item:nth-child(4n){border-right:0}.salary-proposal-compact .sp-label{display:block;font-size:.72rem;font-weight:800;color:var(--sp-muted);text-transform:uppercase;letter-spacing:.035em;margin-bottom:.12rem}.salary-proposal-compact .sp-value{font-size:.9rem;font-weight:700;color:var(--sp-text);word-break:break-word}
        .salary-proposal-compact .sp-details-loading{display:none;position:absolute;inset:0;z-index:6500;background:rgba(248,250,252,.88);backdrop-filter:blur(1.5px);align-items:flex-start;justify-content:center;padding-top:72px;border-radius:12px}
        .salary-proposal-compact .sp-details-loading.active{display:flex}
        .salary-proposal-compact .sp-loading-card{display:flex;align-items:center;gap:.75rem;background:#fff;border:1px solid #dbe3ec;border-radius:10px;padding:.75rem 1rem;box-shadow:0 8px 24px rgba(15,23,42,.10);color:#334155}
        .salary-proposal-compact .sp-loading-spinner{width:22px;height:22px;border:3px solid #e2e8f0;border-top-color:#334155;border-radius:50%;animation:spSalarySpin .75s linear infinite;flex:0 0 22px}
        .salary-proposal-compact .sp-loading-title{font-size:.86rem;font-weight:800;line-height:1.15}.salary-proposal-compact .sp-loading-sub{font-size:.72rem;color:#64748b;margin-top:.1rem}
        @keyframes spSalarySpin{to{transform:rotate(360deg)}}
        .salary-proposal-compact .sp-form{padding:.75rem .8rem}.salary-proposal-compact .form-group{margin-bottom:.7rem}.salary-proposal-compact .form-label{font-size:.8rem;font-weight:700;color:#475569}
        .salary-proposal-compact .sp-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;padding:.7rem}.salary-proposal-compact .sp-total{border:1px solid var(--sp-border);border-radius:9px;padding:.6rem .7rem;background:#fff}.salary-proposal-compact .sp-total strong{display:block;font-size:1rem;color:var(--sp-text)}
        @media(max-width:900px){.salary-proposal-compact .sp-grid,.salary-proposal-compact .sp-summary{grid-template-columns:repeat(2,1fr)}.salary-proposal-compact .sp-item:nth-child(4n){border-right:1px solid var(--sp-border)}.salary-proposal-compact .sp-item:nth-child(2n){border-right:0}}
        @media(max-width:575px){.salary-proposal-compact .sp-grid,.salary-proposal-compact .sp-summary{grid-template-columns:1fr}.salary-proposal-compact .sp-item{border-right:0!important}}
    </style>

    <div class="sp-shell">
        <div class="sp-panel">
            <div class="sp-head primary"><div><h6 class="sp-title">Employee Selection</h6><div class="sp-sub">Select branch and active employee</div></div><span class="badge bg-warning text-dark">Pending Approval</span></div>
            <div class="sp-form"><div class="row">
                <div class="form-group col-md-6">
                    {{ Form::label('spc_branch', __('Branch'), ['class'=>'form-label']) }}<span class="text-danger"> *</span>
                    @if(\Auth::user()->type === 'branch')
                        {{ Form::select('branches_display',$branches,$defaultBranchId ?? null,['class'=>'form-control','id'=>'spc_branch_display','disabled'=>'disabled']) }}
                        {{ Form::hidden('branches',$defaultBranchId ?? null,['id'=>'spc_branch']) }}
                    @else
                        {{ Form::select('branches',$branches,null,['class'=>'form-control select custom-select','id'=>'spc_branch','required'=>'required']) }}
                    @endif
                </div>
                <div class="form-group col-md-6" id="spc_employee_wrap">
                    {{ Form::label('spc_employee', __('Employee'), ['class'=>'form-label']) }}<span class="text-danger"> *</span>
                    {{ Form::select('employee_id',$employees,null,['class'=>'form-control select custom-select','id'=>'spc_employee','required'=>'required']) }}
                </div>
            </div></div>
        </div>

        <div id="employee-proposal-details" class="d-none sp-shell">
            <div id="sp_employee_loading" class="sp-details-loading" aria-live="polite" aria-busy="true">
                <div class="sp-loading-card">
                    <span class="sp-loading-spinner" aria-hidden="true"></span>
                    <div>
                        <div class="sp-loading-title">Loading employee salary details</div>
                        <div class="sp-loading-sub">Please wait while salary, payscale and deductions are prepared.</div>
                    </div>
                </div>
            </div>
            <div class="sp-panel">
                <div class="sp-head info"><div><h6 class="sp-title">Employee Basic Details</h6><div class="sp-sub">Employee identity and current organizational information</div></div></div>
                <div class="sp-grid">
                    <div class="sp-item"><span class="sp-label">Employee No.</span><div class="sp-value" id="employee_no_display">-</div></div>
                    <div class="sp-item"><span class="sp-label">Employee</span><div class="sp-value" id="employee_name_display">-</div></div>
                    <div class="sp-item"><span class="sp-label">Department</span><div class="sp-value" id="department_display">-</div></div>
                    <div class="sp-item"><span class="sp-label">Designation</span><div class="sp-value" id="designation_display">-</div></div>
                    <div class="sp-item">
                        <span class="sp-label">Bank A/C</span>
                        <div class="sp-value" id="bank_account_display_wrap">
                            <span id="bank_account_display">-</span>
                        </div>
                    </div>
                    <div class="sp-item"><span class="sp-label">Current Payscale</span><div class="sp-value" id="current_scale_display">-</div></div>
                    <div class="sp-item"><span class="sp-label">Current Gross</span><div class="sp-value" id="current_gross_display">0.00</div></div>
                    <div class="sp-item"><span class="sp-label">Current Net</span><div class="sp-value" id="current_net_display">0.00</div></div>
                </div>
                <div class="sp-form"><div class="row">
                    <div class="form-group col-md-3">
                        {{ Form::label('payscale',__('New Payscale'),['class'=>'form-label']) }}<span class="text-danger"> *</span>
                        {{ Form::select('payscale',$payscale,null,['class'=>'form-control select custom-select','id'=>'payscale','required'=>'required']) }}
                        <div id="same_scale_draft_notice" class="small text-danger fw-semibold mt-1 d-none"></div>
                    </div>
                    <div class="form-group col-md-3">{{ Form::label('effect_from',__('Effect From'),['class'=>'form-label']) }}<span class="text-danger"> *</span>{{ Form::date('effect_from',date('Y-m-d'),['class'=>'form-control','id'=>'effect_from','required'=>'required']) }}</div>
                    <div class="form-group col-md-3">{{ Form::label('working_days',__('Working Days'),['class'=>'form-label']) }}{{ Form::number('working_days',30,['class'=>'form-control','id'=>'working_days','min'=>1,'max'=>31]) }}</div>
                    <div class="form-group col-md-3">{{ Form::label('pay_method',__('Pay Method'),['class'=>'form-label']) }}{{ Form::select('pay_method',[''=>'Select One','Bank Deposit HBL'=>'Bank Deposit HBL','Bank Deposit AF'=>'Bank Deposit AF','Demand Draft'=>'Demand Draft','Cheque'=>'Cheque','Bank'=>'Bank Deposite','Cash'=>'Cash'],null,['class'=>'form-control select custom-select','id'=>'pay_method']) }}</div>
                </div></div>
            </div>

            <div class="sp-panel">
                <div class="sp-tabs">
                    <button type="button" class="sp-tab-btn active" data-target="additions">Additions</button>
                    <button type="button" class="sp-tab-btn" data-target="deductions">Deductions & Contributions</button>
                </div>

                <div class="sp-tab-pane active" data-pane="additions">
                    <div class="row">
                        <div class="form-group col-md-3">{{ Form::label('drns',__('Drns'),['class'=>'form-label']) }}{{ Form::number('drns',0,['class'=>'form-control addition-field','id'=>'drns','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('conv',__('Other'),['class'=>'form-label']) }}{{ Form::number('conv',0,['class'=>'form-control addition-field','id'=>'conv','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('misc',__('Misc'),['class'=>'form-label']) }}{{ Form::number('misc',0,['class'=>'form-control addition-field','id'=>'misc','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('other_add',__('Other Allowance'),['class'=>'form-label']) }}{{ Form::number('other_add',0,['class'=>'form-control addition-field','id'=>'other_add','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('chaild_concession',__('Child Concession'),['class'=>'form-label']) }}{{ Form::number('chaild_concession',0,['class'=>'form-control ctc-field','id'=>'chaild_concession','step'=>'0.01']) }}</div>
                    </div>
                </div>

                <div class="sp-tab-pane" data-pane="deductions">
                    <div class="row">
                        <div class="form-group col-md-3">{{ Form::label('emp_sec_percentage',__('Emp Security %'),['class'=>'form-label']) }}{{ Form::number('emp_sec_percentage',0,['class'=>'form-control','id'=>'emp_sec_percentage','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('emp_sec',__('Emp Security'),['class'=>'form-label']) }}{{ Form::number('emp_sec',0,['class'=>'form-control deduction-field','id'=>'emp_sec','readonly'=>'readonly','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('eobi_percentage',__('EOBI %'),['class'=>'form-label']) }}{{ Form::number('eobi_percentage',0,['class'=>'form-control','id'=>'eobi_percentage','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('eobi',__('EOBI'),['class'=>'form-label']) }}{{ Form::number('eobi',0,['class'=>'form-control deduction-field','id'=>'eobi','readonly'=>'readonly','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('eobi_employer_percentage',__('EOBI Employer %'),['class'=>'form-label']) }}{{ Form::number('eobi_employer_percentage',0,['class'=>'form-control','id'=>'eobi_employer_percentage','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('eobi_employer',__('EOBI Employer'),['class'=>'form-label']) }}{{ Form::number('eobi_employer',0,['class'=>'form-control ctc-field','id'=>'eobi_employer','readonly'=>'readonly','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('pessi_percentage',__('PESSI %'),['class'=>'form-label']) }}{{ Form::number('pessi_percentage',0,['class'=>'form-control','id'=>'pessi_percentage','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('pessi',__('PESSI'),['class'=>'form-label']) }}{{ Form::number('pessi',0,['class'=>'form-control deduction-field','id'=>'pessi','readonly'=>'readonly','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('pessi_employer_percentage',__('PESSI Employer %'),['class'=>'form-label']) }}{{ Form::number('pessi_employer_percentage',0,['class'=>'form-control','id'=>'pessi_employer_percentage','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('pessi_employer',__('PESSI Employer'),['class'=>'form-label']) }}{{ Form::number('pessi_employer',0,['class'=>'form-control ctc-field','id'=>'pessi_employer','readonly'=>'readonly','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3"><label class="form-label">Income Tax</label><div class="form-control bg-light" id="itax_display" style="cursor:default">0.00</div><small class="text-muted">Calculated automatically</small></div>
                        <div class="form-group col-md-3">{{ Form::label('other_deduction',__('Other Deduction'),['class'=>'form-label']) }}{{ Form::number('other_deduction',0,['class'=>'form-control deduction-field','id'=>'other_deduction','step'=>'0.01']) }}</div>
                        <div class="form-group col-md-3">{{ Form::label('advance',__('Advance'),['class'=>'form-label']) }}{{ Form::number('advance',0,['class'=>'form-control deduction-field','id'=>'advance','step'=>'0.01']) }}</div>
                    </div>
                </div>

                <div class="sp-summary">
                    <div class="sp-total"><span class="sp-label">Payscale Gross</span><strong id="base_scale_gross_display">0.00</strong></div>
                    <div class="sp-total"><span class="sp-label">Gross Salary</span><strong id="gross_display_text">0.00</strong></div>
                    <div class="sp-total"><span class="sp-label">Net Salary</span><strong id="net_display_text">0.00</strong></div>
                    <div class="sp-total"><span class="sp-label">Cost to Company</span><strong id="ctc_display_text">0.00</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer"><input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal"><input type="submit" value="{{ __('Create') }}" class="btn btn-outline-primary"></div>
{{ Form::close() }}

<script>
(function ($) {
    'use strict';

    var $form = $('#sp-single-create-form');
    if (!$form.length) return;

    var previewUrl = '{{ route('employee-salary-proporal.single.preview') }}';
    var companyEobi = 0, companyPessi = 0, initialBasic = 0, baseScaleGross = 0;

    function $f(selector) { return $form.find(selector); }
    function num(v) { v = parseFloat(v || 0); return isNaN(v) ? 0 : v; }
    function money(v) { return num(v).toFixed(2); }

    function destroyCustomSelect($select) {
        if (!$select || !$select.length) return;
        if ($select[0].customSelectInstance) {
            try { $select[0].customSelectInstance.destroy(); } catch (e) {}
            delete $select[0].customSelectInstance;
        }
        $select.next('.custom-select-wrapper').remove();
        $select.parent().children('.custom-select-wrapper').remove();
        $select.css({display: '', opacity: '', visibility: '', position: ''});
    }

    function refreshCustomSelect($select) {
        if (!$select || !$select.length) return;
        destroyCustomSelect($select);
        $select.addClass('select custom-select');

        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
            $select[0].customSelectInstance = window.CustomSelect.create($select[0]);

            // CustomSelect renders its own control; keep the native select available
            // for form submission/change events but do not display a second dropdown.
            if ($select.siblings('.custom-select-wrapper').length || $select.parent().children('.custom-select-wrapper').length) {
                $select.css({
                    position: 'absolute',
                    width: '1px',
                    height: '1px',
                    padding: 0,
                    margin: '-1px',
                    overflow: 'hidden',
                    clip: 'rect(0,0,0,0)',
                    whiteSpace: 'nowrap',
                    border: 0,
                    opacity: 0
                });
            }
        }
    }

    // Raise only the panel whose custom select is currently being used. This is
    // especially important for Payscale / Pay Method because the tabs panel comes
    // later in the DOM and would otherwise paint above their option menus.
    $form
        .off('.singleSalaryProposalSelectLayer')
        .on('mousedown.singleSalaryProposalSelectLayer click.singleSalaryProposalSelectLayer focusin.singleSalaryProposalSelectLayer', '.custom-select-wrapper, select.custom-select', function () {
            $f('.sp-panel').removeClass('sp-select-open');
            $(this).closest('.sp-panel').addClass('sp-select-open');
        })
        .on('focusout.singleSalaryProposalSelectLayer', '.custom-select-wrapper, select.custom-select', function () {
            var $panel = $(this).closest('.sp-panel');
            setTimeout(function () {
                if (!$panel.find('.custom-select-wrapper:focus-within').length) {
                    $panel.removeClass('sp-select-open');
                }
            }, 150);
        });

    // All selects are scoped to this create form. Dynamic Employee select is destroyed
    // before AJAX option replacement and recreated only after the new options are present.
    $f('select.custom-select').each(function () {
        refreshCustomSelect($(this));
    });

    function resetEmployeeDetails() {
        $f('#employee-proposal-details').addClass('d-none');
        $f('#department_id, #bank_account').val('');
        $f('#employee_no_display, #employee_name_display, #department_display, #designation_display, #current_scale_display').text('-');
        $f('#bank_account_display_wrap').html('<span id="bank_account_display">-</span>');
        $f('#current_gross_display, #current_net_display').text('0.00');
        var $scale = $f('#payscale');
        $scale.html('<option value="">Select Scale</option>');
        refreshCustomSelect($scale);
    }

    function populateEmployees(employees, placeholder, disabled) {
        var $employee = $f('#spc_employee');
        employees = Array.isArray(employees) ? employees : [];

        // IMPORTANT: destroy the current custom-select BEFORE touching native options.
        // Some CustomSelect implementations restore their original option snapshot on destroy().
        destroyCustomSelect($employee);

        $employee.empty().append($('<option>', {
            value: '',
            text: placeholder || 'Select Employee'
        }));

        $.each(employees, function (_, employee) {
            if (!employee || !employee.id) return;

            var employeeNumber = employee.employee_id || employee.employee_no || '';
            var employeeName = employee.name || employee.employee_name || employee.full_name || ('Employee #' + employee.id);
            var label = employeeNumber ? (employeeNumber + ' - ' + employeeName) : employeeName;

            $employee.append($('<option>', {
                value: String(employee.id),
                text: label
            }));
        });

        $employee.prop('disabled', !!disabled);

        // Recreate CustomSelect only after AJAX/native options are complete.
        refreshCustomSelect($employee);

        console.log('[Single Salary Proposal] employees populated + custom select rebuilt', {
            count: employees.length,
            branch: $f('#spc_branch').val(),
            optionCount: $employee.find('option').length,
            disabled: !!disabled,
            customSelect: !!$employee[0].customSelectInstance
        });
    }

    function loadBranchEmployees(branchId) {
        resetEmployeeDetails();
        populateEmployees([], branchId ? 'Loading employees...' : 'Select Employee', !!branchId);

        if (!branchId) return;

        $.ajax({
            url: previewUrl,
            type: 'GET',
            dataType: 'json',
            data: { branch_id: branchId },
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (response) {
            console.log('[Single Salary Proposal] branch employee response', response);

            if (!response || response.success !== true) {
                populateEmployees([], 'Select Employee', false);
                var message = (response && response.message) || 'Unable to load branch employees.';
                if (typeof show_toastr === 'function') show_toastr('error', message, 'error');
                return;
            }

            populateEmployees(response.employees || [], 'Select Employee', false);
        }).fail(function (xhr) {
            populateEmployees([], 'Select Employee', false);
            var json = xhr.responseJSON || {};
            var message = json.message || 'Unable to load branch employees.';
            console.error('[Single Salary Proposal] employee load failed', xhr.status, xhr.responseText);
            if (typeof show_toastr === 'function') show_toastr('error', message, 'error');
            else alert(message);
        });
    }

    function renderBankAccountField(latestDetail, payment) {
        latestDetail = latestDetail || {};
        payment = payment || {};

        var account = payment.bank_account || '';
        var hasExistingPayscale = !!(
            latestDetail.has_existing_payscale || latestDetail.id || latestDetail.pay_scale_id
        );

        $f('#bank_account').val(account);

        if (hasExistingPayscale) {
            $f('#bank_account_display_wrap').html(
                $('<input>', {
                    type: 'text',
                    class: 'form-control form-control-sm bg-light',
                    id: 'spc_bank_account_existing',
                    value: account || '-',
                    disabled: true,
                    readonly: true
                })
            );
        } else {
            $f('#bank_account_display_wrap').html(
                $('<input>', {
                    type: 'text',
                    class: 'form-control form-control-sm',
                    id: 'spc_bank_account_entry',
                    name: 'bank_account_entry',
                    placeholder: 'Enter Bank A/C',
                    value: account,
                    required: true
                })
            );
        }
    }

    function populateScales(list, selected) {
        var $scale = $f('#payscale');
        destroyCustomSelect($scale);
        $scale.empty().append('<option value="">Select Scale</option>');

        $.each(list || [], function (_, item) {
            $scale.append($('<option>', {
                value: item.id,
                text: item.label,
                selected: String(item.id) === String(selected || '')
            }));
        });
        refreshCustomSelect($scale);
    }

    function setSelectByName(name, value) {
        var $select = $form.find('[name="' + name + '"]');
        if (!$select.length) return;
        destroyCustomSelect($select);
        if (value && $select.find('option[value="' + value + '"]').length) $select.val(value);
        refreshCustomSelect($select);
    }

    function recalcStatutory() {
        $f('#emp_sec').val(money(initialBasic * num($f('#emp_sec_percentage').val()) / 100));
        $f('#eobi').val(money(companyEobi * num($f('#eobi_percentage').val()) / 100));
        $f('#eobi_employer').val(money(companyEobi * num($f('#eobi_employer_percentage').val()) / 100));
        $f('#pessi').val(money(companyPessi * num($f('#pessi_percentage').val()) / 100));
        $f('#pessi_employer').val(money(companyPessi * num($f('#pessi_employer_percentage').val()) / 100));
    }

    function recalcTotals() {
        var additions = num($f('#drns').val()) + num($f('#conv').val()) + num($f('#misc').val()) + num($f('#other_add').val());
        var gross = baseScaleGross + additions;
        var deductions = num($f('#emp_sec').val()) + num($f('#eobi').val()) + num($f('#pessi').val()) + num($f('#itax').val()) + num($f('#other_deduction').val()) + num($f('#advance').val());
        var net = gross - deductions;
        var ctc = gross + num($f('#eobi_employer').val()) + num($f('#pessi_employer').val()) + num($f('#chaild_concession').val());

        $f('#gross').val(money(gross));
        $f('#net').val(money(net));
        $f('#cost_to_company').val(money(ctc));
        $f('#gross_display_text').text(money(gross));
        $f('#net_display_text').text(money(net));
        $f('#ctc_display_text').text(money(ctc));
    }

    function showEmployeePreviewLoading() {
        $f('#employee-proposal-details').removeClass('d-none');
        $f('#sp_employee_loading').addClass('active');
        $f('#spc_employee').prop('disabled', true);
    }

    function hideEmployeePreviewLoading() {
        $f('#sp_employee_loading').removeClass('active');
        $f('#spc_employee').prop('disabled', false);
    }

    function loadEmployeePreview(scaleId, showLoader) {
        var employeeId = $f('#spc_employee').val();
        if (!employeeId) {
            resetEmployeeDetails();
            return;
        }

        showLoader = showLoader === true;
        if (showLoader) {
            showEmployeePreviewLoading();
        }

        $.ajax({
            url: previewUrl,
            type: 'GET',
            dataType: 'json',
            data: {
                employee_id: employeeId,
                payscale_id: scaleId || '',
                session_id: $f('#session_id').val()
            },
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (response) {
            console.log('[Single Salary Proposal] employee preview response', response);

            if (!response || response.success !== true) {
                var message = (response && response.message) || 'Unable to load employee salary details.';
                if (typeof show_toastr === 'function') show_toastr('error', message, 'error');
                return;
            }

            var e = response.employee || {};
            var d = response.latest_detail || {};
            var p = response.percentages || {};
            var pay = response.payment || {};

            $f('#employee-proposal-details').removeClass('d-none');
            $f('#employee_no_display').text(e.employee_no || e.employee_id || '-');
            $f('#employee_name_display').text(e.employee_name || $f('#spc_employee option:selected').text() || '-');
            $f('#department_display').text(e.department_name || '-');
            $f('#designation_display').text(e.designation_name || '-');
            $f('#department_id').val(e.department_id || '');
            renderBankAccountField(d, pay);
            $f('#current_scale_display').text(e.current_scale_label || '-');
            $f('#current_gross_display').text(money(e.current_gross));
            $f('#current_net_display').text(money(e.current_net_salary || 0));

            setSelectByName('pay_method', pay.pay_method || '');
            populateScales(response.pay_scales || [], scaleId || e.selected_scale_id);

            $f('#working_days').val(d.working_days || 30);
            $f('#drns').val(num(d.drns));
            $f('#conv').val(num(d.conv));
            $f('#misc').val(num(d.misc));
            $f('#other_add').val(num(d.other_add));
            $f('#chaild_concession').val(num(d.chaild_concession || e.new_child_amount));
            $f('#other_deduction').val(num(d.other_deduction));
            $f('#advance').val(num(d.advance));

            $f('#emp_sec_percentage').val(num(p.security));
            $f('#eobi_percentage').val(num(p.eobi));
            $f('#eobi_employer_percentage').val(num(p.eobi_employer));
            $f('#pessi_percentage').val(num(p.pessi));
            $f('#pessi_employer_percentage').val(num(p.pessi_employer));

            companyEobi = num((response.company_values || {}).eobi);
            companyPessi = num((response.company_values || {}).pessi);
            initialBasic = num(response.scale_initial_basic);
            baseScaleGross = num(e.new_gross);

            $f('#base_scale_gross').val(money(baseScaleGross));
            $f('#base_scale_gross_display').text(money(baseScaleGross));
            $f('#itax').val(money(e.new_tax));
            $f('#itax_display').text(money(e.new_tax));

            var $draftNotice = $f('#same_scale_draft_notice');
            var $submitButton = $form.find('input[type="submit"], button[type="submit"]').last();
            if (e.same_scale_draft_exists) {
                $draftNotice.text(e.same_scale_draft_message || 'Already in Draft for the same payscale.').removeClass('d-none');
                $submitButton.prop('disabled', true).attr('data-same-scale-draft', '1');
            } else {
                $draftNotice.addClass('d-none').text('');
                if ($submitButton.attr('data-same-scale-draft') === '1') {
                    $submitButton.prop('disabled', false).removeAttr('data-same-scale-draft');
                }
            }

            recalcStatutory();
            recalcTotals();
        }).fail(function (xhr) {
            var json = xhr.responseJSON || {};
            var message = json.message || 'Unable to load employee salary details.';
            console.error('[Single Salary Proposal] preview failed', xhr.status, xhr.responseText);
            if (typeof show_toastr === 'function') show_toastr('error', message, 'error');
            else alert(message);
        }).fail(function (xhr) {
            console.error('[Single Salary Proposal] employee preview failed', xhr);
            var message = xhr.responseJSON?.message || 'Unable to load employee salary details.';
            if (typeof show_toastr === 'function') {
                show_toastr('error', message, 'error');
            } else {
                alert(message);
            }
        }).always(function () {
            if (showLoader) {
                hideEmployeePreviewLoading();
            }
        });
    }

    // All events are scoped to this specific modal form. Nothing here can target index filters.
    $form.off('.singleSalaryProposal');

    $form.on('change.singleSalaryProposal', '#spc_branch', function () {
        console.log('[Single Salary Proposal] branch changed', this.value);
        loadBranchEmployees(this.value);
    });

    $form.on('change.singleSalaryProposal', '#spc_employee', function (e) {
        console.log('[Single Salary Proposal] employee changed', this.value);
        loadEmployeePreview('', true);
    });

    $form.on('change.singleSalaryProposal', '#payscale', function () {
        loadEmployeePreview(this.value, false);
    });

    $form.on('input.singleSalaryProposal', '.addition-field, .deduction-field, .ctc-field', recalcTotals);
    $form.on('input.singleSalaryProposal', '#emp_sec_percentage, #eobi_percentage, #eobi_employer_percentage, #pessi_percentage, #pessi_employer_percentage', function () {
        recalcStatutory();
        recalcTotals();
    });

    $form.on('input.singleSalaryProposal', '#spc_bank_account_entry', function () {
        $f('#bank_account').val(this.value);
    });

    $form.on('click.singleSalaryProposal', '.sp-tab-btn', function () {
        var target = $(this).data('target');
        var $panel = $(this).closest('.sp-panel');
        $panel.find('.sp-tab-btn').removeClass('active');
        $(this).addClass('active');
        $panel.find('.sp-tab-pane').removeClass('active');
        $panel.find('.sp-tab-pane[data-pane="' + target + '"]').addClass('active');
    });

    $form.on('submit.singleSalaryProposal', function (e) {
        e.preventDefault();

        var $submit = $form.find('input[type="submit"], button[type="submit"]').last();
        var originalText = $submit.is('input') ? $submit.val() : $submit.html();
        var $bankEntry = $f('#spc_bank_account_entry');

        if ($submit.attr('data-same-scale-draft') === '1') {
            var duplicateMessage = $f('#same_scale_draft_notice').text() || 'Already in Draft for the same payscale.';
            if (typeof show_toastr === 'function') show_toastr('error', duplicateMessage, 'error');
            else alert(duplicateMessage);
            return false;
        }

        if ($bankEntry.length && !String($bankEntry.val() || '').trim()) {
            if (typeof show_toastr === 'function') show_toastr('error', 'Please enter Bank A/C.', 'error');
            else alert('Please enter Bank A/C.');
            $bankEntry.focus();
            return false;
        }

        $submit.prop('disabled', true);
        if ($submit.is('input')) $submit.val('{{ __('Creating...') }}');
        else $submit.html('{{ __('Creating...') }}');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).done(function (response) {
            var message = (response && response.message) || 'Salary Proposal created successfully.';
            if (typeof show_toastr === 'function') show_toastr('success', message, 'success');
            window.location.href = (response && response.redirect_url) ? response.redirect_url : '{{ route('employee-salary-proporal.index') }}';
        }).fail(function (xhr) {
            var json = xhr.responseJSON || {};
            var message = json.message || 'Unable to create salary proposal.';
            if (json.errors) {
                var keys = Object.keys(json.errors);
                if (keys.length && json.errors[keys[0]] && json.errors[keys[0]].length) {
                    message = json.errors[keys[0]][0];
                }
            }
            console.error('[Single Salary Proposal] create failed', xhr.status, xhr.responseText);
            if (typeof show_toastr === 'function') show_toastr('error', message, 'error');
            else if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: message });
            else alert(message);
        }).always(function () {
            $submit.prop('disabled', false);
            if ($submit.is('input')) $submit.val(originalText);
            else $submit.html(originalText);
        });
    });

    @if(\Auth::user()->type === 'branch')
        loadBranchEmployees('{{ $defaultBranchId ?? '' }}');
    @endif

})(jQuery);
</script>

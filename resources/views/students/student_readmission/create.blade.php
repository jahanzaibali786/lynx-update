@extends('layouts.admin')

@section('page-title')
    {{ __('Create Re-Admission') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('readmissionstudent.index') }}">{{ __('Re-Admission') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('action-btn')
    <div class="float-end mb-3">
        <a href="{{ route('readmissionstudent.index') }}" class="btn btn-sm btn-light border">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Back') }}
        </a>
    </div>
@endsection

@push('css-page')
<style>
    .readmission-hero{background:linear-gradient(135deg,#102a43 0%,#1f338b 52%,#7b2c35 100%);color:#fff;border-radius:18px;overflow:hidden;position:relative}
    .readmission-hero:before{content:'';position:absolute;inset:0;background:radial-gradient(circle at top right,rgba(255,255,255,.18),transparent 35%);pointer-events:none}
    .readmission-hero .inner{position:relative;z-index:1}
    .detail-card{border:0;border-radius:16px;box-shadow:0 10px 28px rgba(16,42,67,.08);overflow:hidden}
    .detail-card .card-header{background:#fff;border-bottom:1px solid rgba(16,42,67,.08)}
    .source-info{background:#f8fafc;border:1px solid #e7edf4;border-radius:12px;padding:.65rem .75rem;height:100%}
    .source-info .label{font-size:.72rem;text-transform:uppercase;color:#64748b;font-weight:700;margin-bottom:.2rem}
    .source-info .value{font-size:.88rem;font-weight:700;color:#1f2937;min-height:20px}
    .target-placement{border:1px solid #dbeafe;background:#f8fbff;border-radius:14px;padding:1rem}
    .gap-card{border-left:4px solid #f97316;width:100%}
    .fee-structure-card .card-body{padding:1rem!important}
    .fee-structure-card .table-responsive{border-radius:10px}
    .table-responsive{width:100%;overflow-x:auto;margin:0!important}
    .table_heads th{background:#f5f7fa!important;color:#39475a!important;white-space:nowrap}
    #check_policy_status_btn,#check_policy_status_btn:hover,#check_policy_status_btn:focus{background:#fd7e14!important;border-color:#fd7e14!important;color:#fff!important}
    .save-current-structure-btn,.save-current-structure-btn:hover{background:#0f766e!important;border-color:#0f766e!important;color:#fff!important}
    .gap-month-row.missing{background:#fffaf5}
    .gap-month-check{width:18px;height:18px}
    .policy-actions{display:flex;gap:.5rem;flex-wrap:wrap}
    .compact-detail-table th,.compact-detail-table td{white-space:nowrap;vertical-align:middle}
    .section-head th{background:#d1d1d1!important;color:#000!important}

    .top-workspace .detail-card{margin-bottom:0}
    .top-workspace .sticky-side{position:sticky;top:1rem}
    .detected-scenario-card{border:1px solid #dbe7f3;border-radius:14px;background:linear-gradient(135deg,#f8fbff,#eef6ff);padding:.85rem 1rem;margin-bottom:.75rem}
    .detected-scenario-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b;font-weight:800}
    .detected-scenario-value{font-size:1rem;font-weight:800;color:#1d4ed8;margin-top:.15rem}
    .compact-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.55rem}
    .compact-detail-item{border:1px solid #e8edf3;border-radius:10px;background:#fbfcfe;padding:.55rem .65rem;min-width:0}
    .compact-detail-item .label{font-size:.66rem;text-transform:uppercase;letter-spacing:.03em;color:#7b8794;font-weight:700;margin-bottom:.12rem}
    .compact-detail-item .value{font-size:.8rem;font-weight:700;color:#27364a;line-height:1.25;word-break:break-word}
    .compact-section-title{font-size:.78rem;font-weight:800;color:#334155;margin:.85rem 0 .45rem;padding-bottom:.35rem;border-bottom:1px solid #edf1f5}
    .gap-card .card-body{padding:.85rem 1rem}
    .gap-summary-bar{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.65rem}
    .gap-summary-badge{display:inline-flex;align-items:center;gap:.3rem;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;padding:.3rem .6rem;font-size:.72rem;color:#475569}
    .gap-chip-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(145px,1fr));gap:.45rem;max-height:155px;overflow-y:auto;padding:.15rem .15rem .15rem 0}
    .gap-month-option{display:flex;align-items:center;gap:.45rem;border:1px solid #fed7aa;background:#fffaf5;border-radius:10px;padding:.5rem .6rem;margin:0;cursor:pointer;min-width:0}
    .gap-month-option.disabled{cursor:default;opacity:.8}
    .gap-month-option .month-label{font-size:.77rem;font-weight:700;color:#9a3412;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .gap-covered-details{margin-top:.65rem;border-top:1px solid #edf1f5;padding-top:.55rem}
    .gap-covered-details summary{cursor:pointer;font-size:.75rem;font-weight:700;color:#64748b;user-select:none}
    .covered-chip-list{display:flex;flex-wrap:wrap;gap:.35rem;max-height:105px;overflow-y:auto;margin-top:.55rem}
    .covered-month-chip{display:inline-flex;align-items:center;gap:.3rem;border-radius:999px;padding:.3rem .55rem;font-size:.69rem;font-weight:700;background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
    .covered-month-chip.advance{background:#ecfeff;color:#155e75;border-color:#a5f3fc}
    .covered-month-chip.admission{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
    .preview-side-card .card-body{padding:.85rem}
    .preview-side-card .policy-actions{gap:.35rem}
    .preview-side-card .policy-actions .btn{font-size:.7rem;padding:.32rem .5rem}

    /* Compact fee-structure tables: keep both half-width cards usable without clipping. */
    .fee-structure-row>[class*="col-"]{min-width:0}
    .fee-structure-card,.fee-structure-card .card-body{min-width:0}
    .fee-structure-card .card-body{overflow:hidden}
    .fee-table-scroll{display:block;width:100%;max-width:100%;min-width:0;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;scrollbar-gutter:stable;padding:0 10px 8px 0;margin:0!important}
    .fee-table-scroll::-webkit-scrollbar{height:10px}
    .compact-fee-table{width:max-content!important;min-width:100%;max-width:none;margin-bottom:0!important;table-layout:auto}
    .compact-fee-table th,.compact-fee-table td{padding:.42rem .45rem!important;vertical-align:middle;font-size:.76rem;line-height:1.2}
    .compact-fee-table th{font-size:.67rem!important}
    .compact-fee-table .fee-col-check{width:46px;min-width:46px;text-align:center}
    .compact-fee-table .fee-col-index{width:34px;min-width:34px;text-align:center}
    .compact-fee-table .fee-col-head{width:135px;min-width:115px;max-width:165px;white-space:normal!important;overflow-wrap:anywhere}
    .compact-fee-table .fee-col-money{width:86px;min-width:78px;text-align:right;white-space:nowrap}
    .compact-fee-table .fee-col-discount{width:72px;min-width:68px;text-align:right;white-space:nowrap}
    .compact-fee-table .fee-col-source{width:125px;min-width:105px;max-width:150px;white-space:normal!important}
    .compact-fee-table .fee-col-source .badge{white-space:normal;text-align:left;line-height:1.15;max-width:145px}
    @media(max-width:1199.98px){.top-workspace .sticky-side{position:static}.gap-chip-grid{max-height:190px}}
    @media(max-width:575.98px){.compact-detail-grid{grid-template-columns:1fr}.gap-chip-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}


    /* Prevent accidental double actions while an AJAX/navigation action is in progress. */
    .readmission-control-busy{opacity:.65!important;cursor:wait!important}
    a.readmission-control-busy{pointer-events:none!important}

</style>
@endpush

@section('content')
@php
    $isBranchUser = Auth::user()->type === 'branch';
    $currentBranchId = $isBranchUser ? Auth::user()->ownedId() : null;
    $currentBranchName = $isBranchUser ? ($branches->get($currentBranchId) ?? Auth::user()->name) : null;
@endphp

<div class="readmission-hero shadow-sm mb-4">
    <div class="inner px-3 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="small text-white-50">{{ __('Student Management') }}</div>
            <div class="h5 text-white mb-0">{{ __('Re-Admission / Re-enrollment') }}</div>
        </div>
        <div class="text-end">
            <div class="small text-white-50">{{ __('Active Session') }}</div>
            <strong>{{ $activeSessionId ? ($session[$activeSessionId] ?? '-') : '-' }}</strong>
        </div>
    </div>
</div>

{{ Form::open(['url' => 'readmissionstudent', 'id' => 'readmission_form']) }}
<input type="hidden" name="flow_type" id="flow_type">
<input type="hidden" name="branch_id" id="branch_from_hidden" value="{{ $currentBranchId }}">
<input type="hidden" name="class_id" id="class_id" value="">
<input type="hidden" name="section_id" id="section_id" value="">
<input type="hidden" name="session_id" id="session_id" value="">
<div id="selected_heads_container"></div>

<div class="row g-3 align-items-start top-workspace mb-3">
    <div class="col-xl-7">
<div class="card detail-card mb-3">
    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">{{ __('Re-Admission Inputs') }}</h5>
            <small class="text-muted">{{ __('Select the branch and student first. Current placement is loaded automatically.') }}</small>
        </div>
        @if($isBranchUser)<span class="badge bg-primary">{{ __('Branch User') }}</span>@endif
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('Branch') }} <span class="text-danger">*</span>
                @if($isBranchUser)
                    <span class="small text-muted mt-1" style="position:relative; right:-100px;">{{ __('You may choose another branch for the application.') }}</span>
                @endif
                </label>
                {{ Form::select(
                    'branch_id_display',
                    $branches,
                    $currentBranchId,
                    [
                        'class'=>'form-control select',
                        'id'=>'branch_from',
                        'required'=>'required'
                    ]
                ) }}
                
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Student') }} <span class="text-danger">*</span></label>
                <select name="student_id" id="branch_students" class="form-control select" required>
                    <option value="">{{ __('Select Student') }}</option>
                </select>
            </div>

            <div class="col-12"><div class="border-top pt-3"></div></div>
            <div class="col-md-3"><label class="form-label">{{ __('Class') }}</label><input type="text" id="source_class_display" class="form-control bg-light" value="-" disabled></div>
            <div class="col-md-3"><label class="form-label">{{ __('Section') }}</label><input type="text" id="source_section_display" class="form-control bg-light" value="-" disabled></div>
            <div class="col-md-3"><label class="form-label">{{ __('Session') }}</label><input type="text" id="source_session_display" class="form-control bg-light" value="-" disabled></div>
            <div class="col-md-3"><label class="form-label">{{ __('D.O.A') }}</label><input type="text" id="source_doa_display" class="form-control bg-light" value="-" disabled></div>

            <div class="col-md-6">
                <label class="form-label">{{ __('Scenario') }} <span class="text-danger">*</span></label>
                <select id="flow_type_display" class="form-control">
                    <option value="auto">{{ __('Auto Detect') }}</option>
                    <option value="reactivation">{{ __('Reactivation') }}</option>
                    <option value="readmission">{{ __('Readmission') }}</option>
                    <option value="re_enrollment">{{ __('Re-enrollment') }}</option>
                </select>
                <div id="scenario_lock_note" class="small text-muted mt-1 d-none">
                    {{ __('Branch scenario is locked because Auto Detect identified Reactivation.') }}
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Re-Admission Date') }} <span class="text-danger">*</span></label>
                <input type="date" name="readmission_date" value="{{ date('Y-m-d') }}" class="form-control" required>
            </div>

            <div class="col-12">
                <div class="target-placement">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div><h6 class="mb-0">{{ __('Target Placement') }}</h6><small class="text-muted">{{ __('Prefilled from the selected student and editable before submission.') }}</small></div>
                        <span class="badge bg-info">{{ __('Editable') }}</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('New Branch') }} *</label>
                            {{ Form::select(
                                'new_branch_id',
                                $branches,
                                $currentBranchId,
                                [
                                    'class'=>'form-control select',
                                    'id'=>'new_branch_id',
                                    'required'=>'required'
                                ]
                            ) }}
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('New Session') }} *</label>
                            {{ Form::select('new_session_id', $session, $activeSessionId, ['class'=>'form-control select','id'=>'new_session_id','required'=>'required']) }}
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('New Class') }} *</label>
                            <select name="new_class_id" id="new_class_id" class="form-control select" required><option value="">{{ __('Select Class') }}</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('New Section') }} *</label>
                            <select name="new_section_id" id="new_section_id" class="form-control select" required><option value="">{{ __('Select Section') }}</option></select>
                        </div>

                        <div class="col-12 d-none" id="tuition_increment_wrap">
                            <div class="border rounded-3 p-3 bg-white">
                                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                    <div>
                                        <div class="fw-bold">{{ __('Tuition Fee Revision on Session Change') }}</div>
                                        <div class="small text-muted">
                                            {{ __('The percentage is applied on the selected Tuition base: student Tuition by default, or target class Tuition when Use Class Tuition Fee is checked.') }}
                                            <strong>{{ __('Choose the month from which the increment should become applicable.') }}</strong>
                                        </div>
                                    </div>

                                    <label class="form-check mb-0">
                                        <input type="hidden" name="tuition_increment_enabled" value="0">
                                        <input type="checkbox"
                                               name="tuition_increment_enabled"
                                               id="tuition_increment_enabled"
                                               value="1"
                                               class="form-check-input"
                                               checked>
                                        <span class="form-check-label fw-semibold">{{ __('Apply Tuition Revision') }}</span>
                                    </label>
                                </div>

                                <div class="row g-2 mt-1 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">{{ __('Increment %') }} <div class="small text-muted">{{ __('Whole number only. Minimum 0%.') }}</div></label>
                                        <input type="number"
                                               name="tuition_increment_percentage"
                                               id="tuition_increment_percentage"
                                               value="0"
                                               min="0"
                                               step="1"
                                               inputmode="numeric"
                                               class="form-control"
                                               required>
                                        
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label mb-1">{{ __('Effect From') }}</label>
                                        <input type="month"
                                               name="tuition_increment_effective_from"
                                               id="tuition_increment_effective_from"
                                               value="{{ old('tuition_increment_effective_from', date('Y-m')) }}"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <div id="tuition_increment_preview" class="alert alert-light border mb-0 py-2">
                                            {{ __('Select a student and change the session to preview the revised Tuition Fee.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4"><label class="form-label">{{ __('Fee Month') }} *</label><input type="month" name="month_date" id="month_date" value="{{ date('Y-m') }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">{{ __('Issue Date') }} *</label><input type="date" name="issue_date" value="{{ date('Y-m-d') }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">{{ __('Due Date') }} *</label><input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="form-control" required></div>
            <div class="col-12"><label class="form-label">{{ __('Reason') }} *</label><textarea name="reason" class="form-control" rows="2" required></textarea></div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <button type="button"
                id="load_preview_btn"
                class="btn btn-outline-primary">
            {{ __('Load Preview') }}
        </button>
    </div>
</div>




    </div>
    <div class="col-xl-5">

<div class="sticky-side">
    <div id="preview_status" class="alert d-none mb-2"></div>

    <div id="preview_meta" class="detected-scenario-card d-none">
        <div class="detected-scenario-label">{{ __('Detected Scenario') }}</div>
        <div class="detected-scenario-value" id="detected_scenario_value">-</div>
        <div class="small text-muted mt-1" id="detected_scenario_note"></div>
    </div>

    <div id="combined_details_card" class="card detail-card preview-side-card d-none mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <strong>{{ __('Student / Withdrawal / Policy') }}</strong>
            <div class="policy-actions">
                <a href="javascript:void(0)" id="apply_new_policy_btn" data-ajax-popup="true" data-size="xl" data-url="{{ route('concession.create') }}" class="btn btn-sm btn-primary">{{ __('Apply Policy') }}</a>
                <button type="button" id="check_policy_status_btn" class="btn btn-sm">{{ __('Check Policy') }}</button>
                <button type="button" id="end_policy_btn" class="btn btn-sm btn-danger d-none">{{ __('End Policy') }}</button>
            </div>
        </div>
        <div class="card-body">
            <div id="latest_policy_wrap" class="alert alert-light border py-2 px-2 d-none mb-2"><div id="latest_policy_content"></div></div>

            <div class="compact-section-title">{{ __('Student') }}</div>
            <div class="compact-detail-grid">
                <div class="compact-detail-item"><div class="label">{{ __('Roll No') }}</div><div class="value" id="student_roll_no">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Student') }}</div><div class="value" id="student_name">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Father') }}</div><div class="value" id="student_father_name">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Status') }}</div><div class="value" id="student_status">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Class / Section') }}</div><div class="value"><span id="student_class">-</span> / <span id="student_section">-</span></div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Session') }}</div><div class="value" id="student_session">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Branch') }}</div><div class="value" id="student_branch">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('D.O.A') }}</div><div class="value" id="student_doa">-</div></div>
            </div>

            <div class="compact-section-title">{{ __('Withdrawal') }}</div>
            <div class="compact-detail-grid">
                <div class="compact-detail-item"><div class="label">{{ __('Status') }}</div><div class="value" id="withdrawal_status">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Withdraw Date') }}</div><div class="value" id="withdrawal_date">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Expected Rejoin') }}</div><div class="value" id="withdrawal_expected">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Branch') }}</div><div class="value" id="withdrawal_branch">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Class / Session') }}</div><div class="value"><span id="withdrawal_class">-</span> / <span id="withdrawal_session">-</span></div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Reason') }}</div><div class="value" id="withdrawal_reason">-</div></div>
            </div>
            <div id="withdrawal_remarks" class="d-none"></div>

            <div class="compact-section-title">{{ __('Concession Policy') }}</div>
            <div class="compact-detail-grid">
                <div class="compact-detail-item"><div class="label">{{ __('Policy') }}</div><div class="value" id="policy_title">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Status') }}</div><div class="value" id="policy_status">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Apply Date') }}</div><div class="value" id="policy_apply_date">-</div></div>
                <div class="compact-detail-item"><div class="label">{{ __('Start / End') }}</div><div class="value"><span id="policy_start_date">-</span> / <span id="policy_end_date">-</span></div></div>
            </div>
            <div id="policy_remarks" class="d-none"></div>
            <div id="policy_heads_table_wrap" class="table-responsive mt-2 d-none" style="max-height:130px;overflow-y:auto"><table class="table table-bordered table-sm mb-0"><thead class="table_heads"><tr><th>#</th><th>{{ __('Fee Head') }}</th><th>{{ __('Percentage') }}</th></tr></thead><tbody id="policy_heads_body"></tbody></table></div>
        </div>
    </div>
</div>

    </div>
</div>

{{-- Full-width missing / coverage months section --}}
<div class="row mb-3">
    <div class="col-12">
<div id="gap_billing_wrap" class="card detail-card gap-card mt-3 mb-3 d-none">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong>{{ __('Generate missing fee months on approval') }}</strong>
            <div class="small text-muted">
                @if($isBranchUser)
                    {{ __('Branch can review missing months; company decides which months will be charged.') }}
                @else
                    {{ __('Select only the missing months you want to charge.') }}
                @endif
            </div>
        </div>
        <label class="form-check mb-0">
            <input type="checkbox" id="check_all_gap_months" class="form-check-input" {{ $isBranchUser ? 'disabled' : '' }}>
            <span class="form-check-label small fw-semibold">{{ __('Select all missing') }}</span>
        </label>
    </div>
    <div class="card-body">
        <div class="gap-summary-bar">
            <span id="gap_range_preview" class="gap-summary-badge"></span>
            <span class="gap-summary-badge"><strong id="gap_missing_count">0</strong> {{ __('missing') }}</span>
            <span class="gap-summary-badge"><strong id="gap_covered_count">0</strong> {{ __('covered') }}</span>
        </div>

        <div id="gap_missing_empty" class="small text-success d-none">{{ __('No missing months to charge.') }}</div>
        <div id="gap_missing_months_chips" class="gap-chip-grid"></div>

        <details id="gap_covered_details" class="gap-covered-details d-none">
            <summary>{{ __('View already covered months') }} (<span id="gap_covered_summary_count">0</span>)</summary>
            <div id="gap_covered_months_list" class="covered-chip-list"></div>
        </details>
    </div>
</div>
    </div>
</div>


<div class="row g-3 mb-3 fee-structure-row">
    <div class="col-xl-6">
        <div id="existing_fee_table_wrap" class="card detail-card fee-structure-card d-none h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div><strong>{{ __('Current / Existing Fee Structure') }}</strong><div class="small text-muted">{{ __('Separate selection for the student current fee structure.') }}</div></div>
                <button type="button" id="save_fee_structure_btn" class="btn btn-sm save-current-structure-btn"><i class="ti ti-device-floppy me-1"></i>{{ __('Save Current Structure') }}</button>
            </div>
            <div class="card-body">
                <div class="fee-table-scroll">
                    <table class="table table-bordered align-middle compact-fee-table">
                        <thead class="table_heads"><tr><th class="fee-col-check"><input type="checkbox" id="check_all_existing_heads"></th><th class="fee-col-index">#</th><th class="fee-col-head">{{ __('Fee Head') }}</th><th class="fee-col-money">{{ __('Amount') }}</th><th class="fee-col-discount">{{ __('Discount/Policy %') }}</th><th class="fee-col-money">{{ __('Payable') }}</th></tr></thead>
                        <tbody id="existing_fee_table_body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div id="preview_table_wrap" class="card detail-card fee-structure-card d-none h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong>{{ __('Applicable / New Fee Structure') }}</strong>
                    <div class="small text-muted">{{ __('Selected heads will be used for the approval-time admission/re-admission challan.') }}</div>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <label class="form-check mb-0 d-none" id="readmission_structure_source_wrap">
                        <input type="hidden" name="readmission_structure_source" id="readmission_structure_source" value="{{ old('readmission_structure_source', 'student') }}">
                        <input type="checkbox" id="use_class_tuition_structure" class="form-check-input" {{ old('readmission_structure_source', 'student') === 'class' ? 'checked' : '' }}>
                        <span class="form-check-label fw-semibold">{{ __('Use Class Tuition Fee') }}</span>
                        <div class="small text-muted">{{ __('Unchecked = Student Structure') }}</div>
                    </label>
                    <label class="form-check mb-0"><input type="checkbox" id="check_all_target_heads" class="form-check-input"><span class="form-check-label">{{ __('Check all') }}</span></label>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-2 text-end"><a href="#" id="generate_class_fee_structure" class="btn btn-sm btn-success d-none">{{ __('Generate fee structure for target class/session') }}</a></div>
                <div class="fee-table-scroll">
                    <table class="table table-bordered align-middle compact-fee-table">
                        <thead class="table_heads">
                            <tr>
                                <th class="fee-col-check">{{ __('Select') }}</th>
                                <th class="fee-col-index">#</th>
                                <th class="fee-col-head">{{ __('Fee Head') }}</th>
                                <th class="fee-col-money">{{ __('Existing Student') }}</th>
                                <th class="fee-col-money">{{ __('Class Reference') }}</th>
                                <th class="fee-col-money">{{ __('Will Charge') }}</th>
                                <th class="fee-col-source">{{ __('Source') }}</th>
                                <th class="fee-col-discount">{{ __('Discount/Policy %') }}</th>
                                <th class="fee-col-money">{{ __('Payable') }}</th>
                            </tr>
                        </thead>
                        <tbody id="preview_table_body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card detail-card mb-4">
    <div class="card-header py-3"><strong>{{ __('Previous Unpaid Challans') }}</strong></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered table-sm mb-0"><thead class="table_heads"><tr><th>#</th><th>{{ __('Challan No') }}</th><th>{{ __('Type') }}</th><th>{{ __('Fee Month') }}</th><th>{{ __('Other Months') }}</th><th>{{ __('Issue Date') }}</th><th>{{ __('Due Date') }}</th><th>{{ __('Payable') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody id="unpaid_challans_body"><tr><td colspan="9" class="text-center text-muted">{{ __('Select a student to load unpaid challans.') }}</td></tr></tbody></table></div></div>
</div>


{{-- Final submission action intentionally placed at the END of the page. --}}
<div class="card detail-card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="fw-bold">
                {{ __('Application Submission') }}
            </div>
            <div class="small text-muted">
                {{ __('Review all application details, fee heads, gap months and previous unpaid challans before sending for approval.') }}
            </div>
        </div>

        <input type="submit"
               id="submit_btn"
               value="{{ __('Send For Approval') }}"
               class="btn btn-primary px-4">
    </div>
</div>

{{ Form::close() }}

<div class="modal fade" id="endPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form id="end_policy_form"><div class="modal-header"><h5 class="modal-title">{{ __('End Existing Policy') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">{{ __('End Date') }}</label><input type="date" name="end_date" value="{{ date('Y-m-d') }}" class="form-control mb-3" required><label class="form-label">{{ __('Remarks') }}</label><textarea name="remarks" class="form-control" rows="3" required></textarea></div><div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button type="submit" class="btn btn-danger">{{ __('End Policy') }}</button></div></form></div></div>
</div>
@endsection

@push('script-page')
<script>
(function(){
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Single-bind namespace + request guards
    |--------------------------------------------------------------------------
    |
    | Every handler in this page is namespaced. If the Blade/script is
    | initialized again, the old handlers are removed before new ones are
    | attached.
    |
    | Preview changes are debounced and only ONE preview request may remain
    | active. A newer preview aborts the older one.
    |
    */
    const NS='.readmissionCreate';

    const csrfToken='{{ csrf_token() }}';
    const isBranch={{ $isBranchUser ? 'true' : 'false' }};
    const previewUrl=@json(route('readmissionstudent.preview'));
    const branchStudentsUrl=@json(route('get.branch-students'));
    const branchClassUrl=@json(route('branch.class'));
    const sectionsUrl=@json(url('/get-sections'));
    const saveFeeStructureUrl=@json(route('student.fee.update-selection'));
    const policyStatusUrl=@json(route('readmissionstudent.policy-status'));
    const policyEndUrl=@json(route('readmissionstudent.policy-end'));
    const concessionCreateBaseUrl=@json(route('concession.create'));

    let students={};
    let latestPreview=null;

    /*
     * Branch-only state:
     * true ONLY when Auto Detect returned Reactivation and we locked
     * the Scenario dropdown because of that automatic result.
     */
    let branchAutoReactivationLocked=false;

    let previewTimer=null;
    let previewXhr=null;
    let previewRequestKey=null;

    let branchStudentsXhr=null;
    let branchStudentsKey=null;

    let targetClassesXhr=null;
    let targetClassesKey=null;
    let targetClassesCallbacks=[];

    let targetSectionsXhr=null;
    let targetSectionsKey=null;
    let targetSectionsCallbacks=[];

    let policyStatusXhr=null;
    let feeStructureXhr=null;
    let endPolicyXhr=null;
    let tuitionIncrementEffectiveTouched=false;

    const actionLocks={
        feeStructure:false,
        policyStatus:false,
        endPolicy:false,
        formSubmit:false
    };

    function valueOrDash(v){
        return (v===null||v===undefined||v==='')?'-':v;
    }

    function monthName(n){
        return [
            'January','February','March','April',
            'May','June','July','August',
            'September','October','November','December'
        ][Number(n)-1]||'';
    }

    function formatDisplayDate(v){
        if(!v)return '-';
        const m=String(v).match(/^(\d{4})-(\d{2})-(\d{2})/);
        return m?`${m[3]}-${monthName(m[2])}-${m[1]}`:v;
    }

    function formatDisplayMonth(v){
        if(!v)return '-';
        const m=String(v).match(/^(\d{4})-(\d{2})/);
        return m?`${monthName(m[2])} ${m[1]}`:v;
    }

    function formatDisplayMonthList(v){
        return v
            ? String(v).split(',').map(function(x){
                return formatDisplayMonth(x.trim());
            }).join(', ')
            : '-';
    }

    function escapeHtml(v){
        return String(v??'')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#039;');
    }

    function toast(type,msg){
        if(typeof show_toastr==='function'){
            show_toastr(
                type==='error'?'Error':'Success',
                msg,
                type==='error'?'error':'success'
            );
        }else{
            alert(msg);
        }
    }

    function tryLock(name){
        if(actionLocks[name]){
            return false;
        }

        /*
         * IMPORTANT:
         * Set the lock BEFORE disabling the UI or starting AJAX.
         * This guarantees a second click/submit cannot start another request.
         */
        actionLocks[name]=true;
        return true;
    }

    function unlock(name){
        actionLocks[name]=false;
    }

    function setButtonBusy($button,busy,busyText){
        if(!$button||!$button.length)return;

        if(busy){
            if($button.is('input')){
                if($button.data('readmission-original-value')===undefined){
                    $button.data('readmission-original-value',$button.val());
                }
                if(busyText)$button.val(busyText);
            }else{
                if($button.data('readmission-original-html')===undefined){
                    $button.data('readmission-original-html',$button.html());
                }
                if(busyText)$button.html(busyText);
            }

            $button
                .prop('disabled',true)
                .addClass('readmission-control-busy')
                .attr('aria-busy','true');
        }else{
            if($button.is('input')){
                const oldValue=$button.data('readmission-original-value');
                if(oldValue!==undefined)$button.val(oldValue);
                $button.removeData('readmission-original-value');
            }else{
                const oldHtml=$button.data('readmission-original-html');
                if(oldHtml!==undefined)$button.html(oldHtml);
                $button.removeData('readmission-original-html');
            }

            $button
                .prop('disabled',false)
                .removeClass('readmission-control-busy')
                .removeAttr('aria-busy');
        }
    }

    function lockAnchor($anchor,autoUnlockMs){
        if(!$anchor||!$anchor.length)return false;

        if($anchor.data('readmission-busy')){
            return false;
        }

        /*
         * Lock first, then change the appearance.
         */
        $anchor.data('readmission-busy',true)
            .addClass('readmission-control-busy')
            .attr('aria-disabled','true');

        if(autoUnlockMs){
            window.setTimeout(function(){
                unlockAnchor($anchor);
            },autoUnlockMs);
        }

        return true;
    }

    function unlockAnchor($anchor){
        if(!$anchor||!$anchor.length)return;

        $anchor.removeData('readmission-busy')
            .removeClass('readmission-control-busy')
            .removeAttr('aria-disabled');
    }

    function rebuildSelect($el,items,placeholder,selected){
        if($el[0]&&$el[0].customSelectInstance){
            $el[0].customSelectInstance.destroy();
            delete $el[0].customSelectInstance;
        }

        if($el.next('.custom-select-wrapper').length){
            $el.next('.custom-select-wrapper').remove();
        }

        $el.removeClass('custom-select')
            .empty()
            .append($('<option>',{
                value:'',
                text:placeholder
            }));

        (items||[]).forEach(function(item){
            $el.append($('<option>',{
                value:String(item.id),
                text:item.name
            }));
        });

        if(
            selected!==undefined &&
            selected!==null &&
            selected!==''
        ){
            $el.val(String(selected));
        }

        $el.addClass('custom-select').show();

        if(
            window.CustomSelect &&
            typeof window.CustomSelect.create==='function'
        ){
            window.CustomSelect.create($el[0]);
        }
    }

    function setSelect($el,value){
        if(!$el.length)return;

        if($el[0]&&$el[0].customSelectInstance){
            $el[0].customSelectInstance.destroy();
            delete $el[0].customSelectInstance;
        }

        if($el.next('.custom-select-wrapper').length){
            $el.next('.custom-select-wrapper').remove();
        }

        $el.removeClass('custom-select')
            .val(String(value||''))
            .addClass('custom-select')
            .show();

        if(
            window.CustomSelect &&
            typeof window.CustomSelect.create==='function'
        ){
            window.CustomSelect.create($el[0]);
        }
    }

    function abortXhr(xhr){
        if(xhr&&xhr.readyState!==4){
            try{
                xhr.abort();
            }catch(e){}
        }
    }

    function setScenarioControl(value,disabled,showLockNote){
        const $scenario=$('#flow_type_display');

        /*
         * Destroy any custom-select instance/wrapper first.
         * This prevents a stale "disabled" visual state from surviving
         * after the native select has been enabled.
         */
        if(
            $scenario[0] &&
            $scenario[0].customSelectInstance
        ){
            $scenario[0].customSelectInstance.destroy();
            delete $scenario[0].customSelectInstance;
        }

        if($scenario.next('.custom-select-wrapper').length){
            $scenario.next('.custom-select-wrapper').remove();
        }

        /*
         * Company / HO is NEVER locked.
         */
        if(!isBranch){
            disabled=false;
            showLockNote=false;
        }

        if(value!==undefined&&value!==null&&value!==''){
            $scenario.val(String(value));
        }

        if(disabled){
            $scenario
                .prop('disabled',true)
                .attr('disabled','disabled');
        }else{
            /*
             * Explicitly remove BOTH property + attribute.
             * Some custom scripts check one while others check the other.
             */
            $scenario
                .prop('disabled',false)
                .removeAttr('disabled')
                .removeClass('disabled');
        }

        /*
         * Clean any stale disabled classes from nearby generated UI.
         */
        $scenario
            .parent()
            .find('.custom-select-wrapper,.custom-select-trigger,.select2-selection')
            .toggleClass('disabled',!!disabled)
            .attr('aria-disabled',disabled?'true':'false');

        $('#scenario_lock_note').toggleClass(
            'd-none',
            !showLockNote
        );
    }

    function resetScenarioToAuto(){
        branchAutoReactivationLocked=false;

        setScenarioControl(
            'auto',
            false,
            false
        );

        $('#flow_type').val('');
    }

    function releaseBranchAutoReactivationLock(){
        if(!isBranch||!branchAutoReactivationLocked){
            return;
        }

        resetScenarioToAuto();
    }

    function applyScenarioResult(resp,requestedScenario){
        requestedScenario=String(requestedScenario||'auto');

        const detectedFlow=String(resp.flow_type||'');

        /*
         * COMPANY / HO
         * ----------------
         * Never disable Scenario.
         *
         * When using Auto Detect we show the detected result as the selected
         * option, but the company can immediately change it.
         */
        if(!isBranch){
            branchAutoReactivationLocked=false;

            if(
                requestedScenario==='auto' &&
                ['reactivation','readmission','re_enrollment'].includes(detectedFlow)
            ){
                setScenarioControl(
                    detectedFlow,
                    false,
                    false
                );
            }else{
                setScenarioControl(
                    $('#flow_type_display').val()||requestedScenario||'auto',
                    false,
                    false
                );
            }

            return;
        }

        /*
         * BRANCH - AUTO DETECTED REACTIVATION
         * ------------------------------------
         * This is the ONLY case where Scenario is disabled.
         */
        if(
            requestedScenario==='auto' &&
            detectedFlow==='reactivation'
        ){
            branchAutoReactivationLocked=true;

            setScenarioControl(
                'reactivation',
                true,
                true
            );

            $('#flow_type').val('reactivation');

            return;
        }

        /*
         * BRANCH - AUTO DETECTED READMISSION / RE-ENROLLMENT
         * --------------------------------------------------
         * Show the detected result but KEEP the dropdown enabled so Branch
         * can submit for company approval or choose another allowed scenario.
         */
        branchAutoReactivationLocked=false;

        if(
            requestedScenario==='auto' &&
            ['readmission','re_enrollment'].includes(detectedFlow)
        ){
            setScenarioControl(
                detectedFlow,
                false,
                false
            );

            $('#flow_type').val(detectedFlow);

            return;
        }

        /*
         * Any manual/custom Branch scenario stays enabled.
         */
        setScenarioControl(
            $('#flow_type_display').val()||requestedScenario||'auto',
            false,
            false
        );
    }

    function clearStudent(){
        students={};

        /*
         * Every new student starts with an enabled Auto Detect scenario.
         * It may be locked later only if Branch auto-detects Reactivation.
         */
        resetScenarioToAuto();

        rebuildSelect(
            $('#branch_students'),
            [],
            'Select Student'
        );

        $('#class_id,#section_id,#session_id').val('');

        $('#source_class_display,#source_section_display,#source_session_display,#source_doa_display')
            .val('-');

        resetPreview();
    }

    function loadBranchStudents(branchId){
        clearStudent();

        branchId=String(branchId||'');

        if(!branchId)return;

        $('#branch_from_hidden').val(branchId);

        /*
         * Same branch request already running = do nothing.
         * Different branch = abort the stale request first.
         */
        if(
            branchStudentsXhr &&
            branchStudentsXhr.readyState!==4 &&
            branchStudentsKey===branchId
        ){
            return;
        }

        abortXhr(branchStudentsXhr);

        branchStudentsKey=branchId;

        branchStudentsXhr=$.ajax({
            url:branchStudentsUrl,
            type:'GET',
            data:{
                branch_id:branchId,
                readmission:1
            }
        })
        .done(function(res){
            const rows=res.student_records||[];

            students={};

            rows.forEach(function(r){
                students[String(r.reg_id)]=r;
            });

            rebuildSelect(
                $('#branch_students'),
                rows.map(function(r){
                    return {
                        id:r.reg_id,
                        name:
                            (r.roll_no?r.roll_no+' - ':'')+
                            r.stdname+
                            (r.fathername?' S/D/O '+r.fathername:'')
                    };
                }),
                'Select Student'
            );
        })
        .fail(function(xhr,status){
            if(status==='abort')return;
            toast('error','Unable to load branch students.');
        })
        .always(function(){
            branchStudentsXhr=null;
            branchStudentsKey=null;
        });
    }

    function loadTargetClasses(branchId,selected,done){
        branchId=String(branchId||'');

        if(typeof done==='function'){
            targetClassesCallbacks.push(done);
        }

        if(!branchId){
            rebuildSelect(
                $('#new_class_id'),
                [],
                'Select Class'
            );

            const callbacks=targetClassesCallbacks.splice(0);
            callbacks.forEach(function(cb){cb();});
            return;
        }

        if(
            targetClassesXhr &&
            targetClassesXhr.readyState!==4 &&
            targetClassesKey===branchId
        ){
            /*
             * Do not send a second identical request.
             * Its callback is already queued above.
             */
            return;
        }

        abortXhr(targetClassesXhr);

        targetClassesCallbacks=typeof done==='function'
            ? [done]
            : [];

        targetClassesKey=branchId;

        targetClassesXhr=$.ajax({
            url:branchClassUrl,
            type:'POST',
            data:{
                branch_id:branchId,
                _token:csrfToken
            }
        })
        .done(function(rows){
            rebuildSelect(
                $('#new_class_id'),
                (rows||[]).map(function(r){
                    return {
                        id:r.id,
                        name:r.name
                    };
                }),
                'Select Class',
                selected
            );

            const callbacks=targetClassesCallbacks.splice(0);
            callbacks.forEach(function(cb){cb();});
        })
        .fail(function(xhr,status){
            if(status==='abort')return;
            toast('error','Unable to load target classes.');
            targetClassesCallbacks=[];
        })
        .always(function(){
            targetClassesXhr=null;
            targetClassesKey=null;
        });
    }

    function loadTargetSections(classId,selected,done){
        classId=String(classId||'');

        if(typeof done==='function'){
            targetSectionsCallbacks.push(done);
        }

        if(!classId){
            rebuildSelect(
                $('#new_section_id'),
                [],
                'Select Section'
            );

            const callbacks=targetSectionsCallbacks.splice(0);
            callbacks.forEach(function(cb){cb();});
            return;
        }

        if(
            targetSectionsXhr &&
            targetSectionsXhr.readyState!==4 &&
            targetSectionsKey===classId
        ){
            return;
        }

        abortXhr(targetSectionsXhr);

        targetSectionsCallbacks=typeof done==='function'
            ? [done]
            : [];

        targetSectionsKey=classId;

        targetSectionsXhr=$.ajax({
            url:sectionsUrl+'/'+classId,
            type:'GET'
        })
        .done(function(rows){
            const list=(rows.sections||rows||[]).map(function(r){
                return {
                    id:r.id,
                    name:r.name
                };
            });

            rebuildSelect(
                $('#new_section_id'),
                list,
                'Select Section',
                selected
            );

            const callbacks=targetSectionsCallbacks.splice(0);
            callbacks.forEach(function(cb){cb();});
        })
        .fail(function(xhr,status){
            if(status==='abort')return;
            toast('error','Unable to load target sections.');
            targetSectionsCallbacks=[];
        })
        .always(function(){
            targetSectionsXhr=null;
            targetSectionsKey=null;
        });
    }

    function selectedStudent(){
        return students[
            String($('#branch_students').val()||'')
        ]||null;
    }

    function applyStudent(r){
        if(!r)return;

        $('#branch_from_hidden').val(r.branch_id||'');
        $('#class_id').val(r.class_id||'');
        $('#section_id').val(r.section_id||'');
        $('#session_id').val(r.session_id||'');

        $('#source_class_display').val(
            valueOrDash(r.class)
        );

        $('#source_section_display').val(
            valueOrDash(r.section)
        );

        $('#source_session_display').val(
            valueOrDash(r.session)
        );

        $('#source_doa_display').val(
            formatDisplayDate(r.date_of_admission)
        );

        setSelect(
            $('#new_branch_id'),
            r.branch_id
        );

        setSelect(
            $('#new_session_id'),
            r.session_id||''
        );

        updatePolicyUrl();
        updateTuitionIncrementVisibility();

        /*
         * Load dependencies in sequence instead of using overlapping
         * setTimeout() calls:
         * Branch -> Class -> Section -> ONE preview.
         */
        loadTargetClasses(
            r.branch_id,
            r.class_id,
            function(){
                loadTargetSections(
                    r.class_id,
                    r.section_id,
                    function(){
                        schedulePreview(80);
                    }
                );
            }
        );
    }

    function syncFlow(){
        const v=$('#flow_type_display').val();
        $('#flow_type').val(v==='auto'?'':v);
    }

    function normalizeIncrementInput(){
        const $input=$('#tuition_increment_percentage');
        let value=String($input.val()??'0').replace(/[^\d]/g,'');

        if(value===''){
            value='0';
        }

        value=String(Math.max(0,parseInt(value,10)||0));

        $input.val(value);

        return Number(value);
    }

    function sessionChanged(){
        const source=String($('#session_id').val()||'');
        const target=String($('#new_session_id').val()||'');

        return !!source && !!target && source!==target;
    }

    function updateTuitionIncrementVisibility(){
        const changed=sessionChanged();

        $('#tuition_increment_wrap').toggleClass(
            'd-none',
            !changed
        );

        if(!changed){
            $('#tuition_increment_percentage').val('0');
            $('#tuition_increment_preview').html(
                '<span class="text-muted">Tuition revision is only applied when the session changes.</span>'
            );
        }

        return changed;
    }

    function renderTuitionRevision(resp){
        const revision=resp.fee_revision||{};
        const changed=!!revision.session_changed;

        $('#tuition_increment_wrap').toggleClass('d-none',!changed);

        if(!changed){
            return;
        }

        const oldBase=Number(revision.prev_base_amount||0);
        const newBase=Number(revision.new_base_amount||0);
        const pct=Number(revision.percentage||0);
        const baseLabel=revision.base_source_label||'Existing Student Fee';

        const effectiveMonth=formatDisplayMonth(
            revision.effective_from ||
            $('#tuition_increment_effective_from').val() ||
            $('#month_date').val()
        );

        $('#tuition_increment_preview').html(
            `<strong>${escapeHtml(revision.fee_head||'Tuition Fee')}</strong>
             <span class="ms-2 text-muted">${escapeHtml(baseLabel)}:</span>
             <strong>${oldBase.toFixed(2)}</strong>
             <span class="mx-1">→</span>
             <span class="text-muted">${pct}%:</span>
             <strong>${newBase.toFixed(2)}</strong>
             <div class="small text-primary mt-1">
                Effective from <strong>${escapeHtml(effectiveMonth)}</strong> onward.
                Earlier gap months keep the previous Tuition amount.
             </div>`
        );
    }

    function formData(){
        syncFlow();

        return {
            branch_id:$('#branch_from_hidden').val(),
            class_id:$('#class_id').val(),
            section_id:$('#section_id').val(),
            session_id:$('#session_id').val(),
            student_id:$('#branch_students').val(),
            flow_type:$('#flow_type').val(),
            month_date:$('#month_date').val(),
            new_branch_id:$('#new_branch_id').val(),
            new_class_id:$('#new_class_id').val(),
            new_section_id:$('#new_section_id').val(),
            new_session_id:$('#new_session_id').val(),
            readmission_structure_source:$('#readmission_structure_source').val()||'student',
            tuition_increment_enabled:$('#tuition_increment_enabled').is(':checked')?1:0,
            tuition_increment_percentage:normalizeIncrementInput(),
            tuition_increment_effective_from:$('#tuition_increment_effective_from').val()||$('#month_date').val(),
            _token:csrfToken
        };
    }

    function previewDataReady(d){
        return !!(
            d.student_id &&
            d.branch_id &&
            d.class_id &&
            d.session_id &&
            d.month_date &&
            d.new_branch_id &&
            d.new_class_id &&
            d.new_session_id &&
            d.new_section_id
        );
    }

    function previewKey(d){
        return JSON.stringify({
            branch_id:d.branch_id,
            class_id:d.class_id,
            section_id:d.section_id,
            session_id:d.session_id,
            student_id:d.student_id,
            flow_type:d.flow_type,
            month_date:d.month_date,
            new_branch_id:d.new_branch_id,
            new_class_id:d.new_class_id,
            new_section_id:d.new_section_id,
            new_session_id:d.new_session_id,
            readmission_structure_source:d.readmission_structure_source,
            tuition_increment_enabled:d.tuition_increment_enabled,
            tuition_increment_percentage:d.tuition_increment_percentage,
            tuition_increment_effective_from:d.tuition_increment_effective_from
        });
    }

    function resetPreview(){
        latestPreview=null;

        if(previewTimer){
            clearTimeout(previewTimer);
            previewTimer=null;
        }

        $('#preview_status,#preview_meta,#existing_fee_table_wrap,#preview_table_wrap,#combined_details_card,#gap_billing_wrap')
            .addClass('d-none');

        $('#existing_fee_table_body,#preview_table_body,#gap_missing_months_chips,#gap_covered_months_list')
            .empty();

        $('#gap_missing_count,#gap_covered_count,#gap_covered_summary_count')
            .text('0');

        $('#gap_missing_empty,#gap_covered_details')
            .addClass('d-none');

        $('#selected_heads_container').empty();
    }

    function showError(msg){
        $('#preview_status')
            .removeClass('d-none alert-success alert-info')
            .addClass('alert-danger')
            .text(msg);
    }

    function showInfo(msg){
        $('#preview_status')
            .removeClass('d-none alert-danger alert-success')
            .addClass('alert-info')
            .text(msg);
    }

    function currentSelectedHeads(){
        return $('.existing-fee-head:checked')
            .map(function(){
                return Number(this.value);
            })
            .get();
    }

    function targetSelectedHeads(){
        return $('.target-fee-head:checked')
            .map(function(){
                return Number(this.value);
            })
            .get();
    }

    function syncSelectedHeads(){
        const flow=latestPreview
            ? latestPreview.flow_type
            : ($('#flow_type').val()||'');

        const ids=flow==='reactivation'
            ? currentSelectedHeads()
            : targetSelectedHeads();

        $('#selected_heads_container').empty();

        [...new Set(ids)].forEach(function(id){
            $('#selected_heads_container').append(
                `<input type="hidden" name="selected_heads[]" value="${id}">`
            );
        });
    }

    function renderExisting(rows){
        const body=$('#existing_fee_table_body').empty();

        (rows||[]).forEach(function(r,i){
            body.append(
                `<tr>
                    <td class="fee-col-check">
                        <input type="checkbox"
                               class="form-check-input existing-fee-head"
                               value="${Number(r.head_id)}"
                               ${Number(r.checked||0)===1?'checked':''}>
                    </td>
                    <td class="fee-col-index">${i+1}</td>
                    <td class="fee-col-head">${escapeHtml(r.fee_head||'-')}</td>
                    <td class="fee-col-money">${Number(r.class_amount||0).toFixed(2)}</td>
                    <td class="fee-col-discount">${Number(r.discount||0).toFixed(2)}%</td>
                    <td class="fee-col-money">${Number(r.payable_amount||0).toFixed(2)}</td>
                </tr>`
            );
        });

        $('#existing_fee_table_wrap')
            .toggleClass('d-none',!(rows||[]).length);

        syncExistingCheckAll();
        syncSelectedHeads();
    }

    function renderTarget(rows,preservedIds){
        const body=$('#preview_table_body').empty();

        (rows||[]).forEach(function(r,i){
            const name=String(r.fee_head||'').toUpperCase();
            const backendLocked=Number(r.branch_disabled||0)===1;
            const autoCharge=Number(r.auto_charge||0)===1;

            /*
             * Readmission:
             * - Admission/Security are disabled + unchecked at Branch.
             * - Tuition is automatically charged on the Readmission fee month,
             *   so it is shown checked + locked for every user.
             */
            /*
             * ONLY Branch may have locked fee heads.
             *
             * Company/HO:
             * - no disabled fee head;
             * - Tuition, Admission, Security, Re-Admission, etc. are all editable.
             */
            const locked=isBranch && (backendLocked || autoCharge);
            const backendChecked=Number(r.checked||0)===1;

            const checked=isBranch
                ? (
                    autoCharge
                        ? true
                        : (
                            backendLocked
                                ? false
                                : (
                                    Array.isArray(preservedIds)
                                        ? preservedIds.includes(Number(r.head_id))
                                        : backendChecked
                                )
                        )
                )
                : (
                    Array.isArray(preservedIds)
                        ? preservedIds.includes(Number(r.head_id))
                        : backendChecked
                );

            const existingAmount=
                r.existing_amount===null ||
                r.existing_amount===undefined
                    ? '-'
                    : Number(r.existing_amount||0).toFixed(2);

            const classReference=
                r.class_reference_amount===null ||
                r.class_reference_amount===undefined
                    ? '-'
                    : Number(r.class_reference_amount||0).toFixed(2);

            const chargeAmount=Number(
                r.charge_amount!==undefined
                    ? r.charge_amount
                    : r.class_amount||0
            ).toFixed(2);

            const sourceLabel=escapeHtml(
                r.charge_source_label ||
                (
                    r.source==='student_fee_structure'
                        ? 'Existing Student Fee'
                        : 'Class Fee Structure'
                )
            );

            const disabledTitle=locked
                ? ` title="${escapeHtml(
                    autoCharge
                        ? (r.auto_charge_reason||'Automatically charged on the Readmission Fee Month')
                        : (r.disabled_reason||'Not chargeable for Readmission')
                )}"`
                : '';

            const chargeNote=isBranch
                ? (
                    autoCharge
                        ? '<div class="small text-primary">Automatically charged on Readmission Fee Month</div>'
                        : (
                            backendLocked
                                ? '<div class="small text-danger">Not charged on Re-Admission</div>'
                                : ''
                        )
                )
                : '';

            body.append(
                `<tr class="${checked?'table-success':''}">
                    <td class="fee-col-check">
                        <input type="checkbox"
                               class="form-check-input target-fee-head"
                               value="${Number(r.head_id)}"
                               ${checked?'checked':''}
                               ${locked?'disabled':''}
                               ${disabledTitle}>
                    </td>
                    <td class="fee-col-index">${i+1}</td>
                    <td class="fee-col-head">
                        <strong>${escapeHtml(r.fee_head||'-')}</strong>
                        ${chargeNote}
                    </td>
                    <td class="fee-col-money">${existingAmount}</td>
                    <td class="fee-col-money">${classReference}</td>
                    <td class="fee-col-money"><strong>${chargeAmount}</strong></td>
                    <td class="fee-col-source"><span class="badge bg-light text-dark border">${sourceLabel}</span></td>
                    <td class="fee-col-discount">${Number(r.discount||0).toFixed(2)}%</td>
                    <td class="fee-col-money"><strong>${Number(r.payable_amount||0).toFixed(2)}</strong></td>
                </tr>`
            );
        });

        $('#preview_table_wrap')
            .toggleClass('d-none',!(rows||[]).length);

        syncTargetCheckAll();
        syncSelectedHeads();
    }

    function syncExistingCheckAll(){
        const all=$('.existing-fee-head:not(:disabled)');

        $('#check_all_existing_heads').prop(
            'checked',
            all.length>0 &&
            all.filter(':checked').length===all.length
        );
    }

    function syncTargetCheckAll(){
        const all=$('.target-fee-head:not(:disabled)');

        $('#check_all_target_heads').prop(
            'checked',
            all.length>0 &&
            all.filter(':checked').length===all.length
        );
    }

    function renderGap(resp){
        if(resp.flow_type==='reactivation'){
            $('#gap_billing_wrap').addClass('d-none');
            return;
        }

        const statuses=resp.gap_month_statuses||[];

        const missing=statuses.filter(function(r){
            return r.status==='missing';
        });

        const covered=statuses.filter(function(r){
            return r.status!=='missing';
        });

        const missingWrap=
            $('#gap_missing_months_chips').empty();

        const coveredWrap=
            $('#gap_covered_months_list').empty();

        missing.forEach(function(r){
            const disabled=isBranch?'disabled':'';
            const disabledClass=isBranch?'disabled':'';

            missingWrap.append(
                `<label class="gap-month-option ${disabledClass}"
                        title="${escapeHtml(r.label||'Missing')}">
                    <input type="checkbox"
                           class="form-check-input gap-month-check"
                           name="gap_months[]"
                           value="${r.month}"
                           ${disabled}>
                    <span class="month-label">
                        ${formatDisplayMonth(r.month)}
                    </span>
                </label>`
            );
        });

        covered.forEach(function(r){
            let cls='';

            if(r.status==='advance_covered'){
                cls='advance';
            }else if(r.status==='admission_covered'){
                cls='admission';
            }

            const challan=r.challan_no
                ? ` · #${escapeHtml(r.challan_no)}`
                : '';

            coveredWrap.append(
                `<span class="covered-month-chip ${cls}"
                       title="${escapeHtml(r.label||'Covered')}${challan}">
                    ${formatDisplayMonth(r.month)}
                </span>`
            );
        });

        const range=resp.gap_range||{};

        $('#gap_range_preview').html(
            range.from&&range.to
                ? `<strong>${formatDisplayMonth(range.from)}</strong> – <strong>${formatDisplayMonth(range.to)}</strong>`
                : ''
        );

        $('#gap_missing_count').text(missing.length);

        $('#gap_covered_count,#gap_covered_summary_count')
            .text(covered.length);

        $('#gap_missing_empty')
            .toggleClass('d-none',missing.length>0);

        $('#gap_covered_details')
            .toggleClass('d-none',covered.length===0);

        $('#gap_billing_wrap').removeClass('d-none');

        $('#check_all_gap_months').prop('checked',false);
    }

    function renderDetails(resp){
        const s=resp.student_details||{};
        const w=resp.withdrawal_details||{};
        const p=resp.policy_details||{};

        $('#student_roll_no').text(valueOrDash(s.roll_no));
        $('#student_name').text(valueOrDash(s.student_name));
        $('#student_father_name').text(valueOrDash(s.father_name));
        $('#student_branch').text(valueOrDash(s.branch));
        $('#student_class').text(valueOrDash(s.class));
        $('#student_section').text(valueOrDash(s.section));
        $('#student_session').text(valueOrDash(s.session));
        $('#student_doa').text(formatDisplayDate(s.date_of_admission));
        $('#student_status').text(valueOrDash(s.student_status));

        $('#withdrawal_status').text(valueOrDash(w.status));
        $('#withdrawal_date').text(formatDisplayDate(w.withdraw_date));
        $('#withdrawal_expected').text(formatDisplayDate(w.expected_readmission_date));
        $('#withdrawal_branch').text(valueOrDash(w.branch));
        $('#withdrawal_class').text(valueOrDash(w.class));
        $('#withdrawal_session').text(valueOrDash(w.session));
        $('#withdrawal_reason').text(valueOrDash(w.reason));
        $('#withdrawal_remarks').text(valueOrDash(w.remark));

        $('#policy_title').text(valueOrDash(p.policy_title));
        $('#policy_status').text(valueOrDash(p.status));
        $('#policy_apply_date').text(formatDisplayDate(p.apply_date));
        $('#policy_start_date').text(formatDisplayDate(p.start_date));
        $('#policy_end_date').text(formatDisplayDate(p.end_date));
        $('#policy_remarks').text(valueOrDash(p.remarks));

        const hb=$('#policy_heads_body').empty();

        (p.heads||[]).forEach(function(h,i){
            hb.append(
                `<tr>
                    <td>${i+1}</td>
                    <td>${escapeHtml(h.fee_head||'-')}</td>
                    <td>${Number(h.percentage||0).toFixed(2)}%</td>
                </tr>`
            );
        });

        $('#policy_heads_table_wrap')
            .toggleClass('d-none',!(p.heads||[]).length);

        if(p.has_policy){
            $('#end_policy_btn').removeClass('d-none');
        }else{
            $('#end_policy_btn').addClass('d-none');
        }

        const lp=resp.latest_policy||{};

        if(lp.exists){
            $('#latest_policy_content').html(
                `<strong>${escapeHtml(lp.policy_title||'-')}</strong>
                 <span class="badge bg-secondary">${escapeHtml(lp.status||'-')}</span>
                 <div class="small text-muted">
                    ${formatDisplayDate(lp.start_date)} to ${formatDisplayDate(lp.end_date)}
                 </div>`
            );

            $('#latest_policy_wrap').removeClass('d-none');
        }else{
            $('#latest_policy_wrap').addClass('d-none');
        }

        $('#combined_details_card').removeClass('d-none');
    }

    function renderUnpaid(rows){
        const body=$('#unpaid_challans_body').empty();

        if(!(rows||[]).length){
            body.html(
                '<tr><td colspan="9" class="text-center text-success">No previous unpaid challans found.</td></tr>'
            );
            return;
        }

        (rows||[]).forEach(function(r,i){
            body.append(
                `<tr>
                    <td>${i+1}</td>
                    <td>${escapeHtml(r.challan_no||'-')}</td>
                    <td>${escapeHtml(r.challan_type||'-')}</td>
                    <td>${formatDisplayMonth(r.fee_month)}</td>
                    <td>${formatDisplayMonthList(r.other_months)}</td>
                    <td>${formatDisplayDate(r.issue_date)}</td>
                    <td>${formatDisplayDate(r.due_date)}</td>
                    <td>${Number(r.payable_amount||0).toFixed(2)}</td>
                    <td>
                        <span class="badge bg-warning text-dark">
                            ${escapeHtml(r.status||'-')}
                        </span>
                    </td>
                </tr>`
            );
        });
    }

    function schedulePreview(delay){
        if(previewTimer){
            clearTimeout(previewTimer);
        }

        previewTimer=setTimeout(function(){
            previewTimer=null;
            loadPreview();
        },Number(delay||180));
    }

    function loadPreview(){
        /*
         * Capture the visible Scenario BEFORE formData() calls syncFlow().
         * This tells us whether Reactivation came from Auto Detect
         * or from a user's manual/custom choice.
         */
        const requestedScenario=branchAutoReactivationLocked
            ? 'auto'
            : String($('#flow_type_display').val()||'auto');

        const previousFlow=latestPreview?String(latestPreview.flow_type||''):'';
        const preservedTargetIds=$('#preview_table_body .target-fee-head').length
            ? targetSelectedHeads()
            : null;

        const data=formData();

        if(!previewDataReady(data)){
            return false;
        }

        const key=previewKey(data);

        /*
         * If the exact same preview is already in flight, do NOT start
         * another request.
         */
        if(
            previewXhr &&
            previewXhr.readyState!==4 &&
            previewRequestKey===key
        ){
            return false;
        }

        /*
         * If a different preview is still running, it is stale.
         * Abort it BEFORE starting the new request.
         */
        abortXhr(previewXhr);

        previewRequestKey=key;

        const $button=$('#load_preview_btn');

        setButtonBusy(
            $button,
            true,
            '<span class="spinner-border spinner-border-sm me-1"></span>Loading...'
        );

        showInfo('Loading preview...');

        previewXhr=$.ajax({
            url:previewUrl,
            type:'POST',
            data:data
        });

        const thisXhr=previewXhr;

        thisXhr.done(function(resp){
            /*
             * Only the current request may render.
             */
            if(thisXhr!==previewXhr){
                return;
            }

            latestPreview=resp;

            $('#flow_type').val(resp.flow_type||'');

            /*
             * Branch locks ONLY Auto-detected Reactivation.
             * Company always remains editable.
             */
            applyScenarioResult(
                resp,
                requestedScenario
            );

            $('#preview_status')
                .removeClass('alert-info alert-danger')
                .addClass('alert-success')
                .text(resp.message||'Preview loaded.')
                .removeClass('d-none');

            $('#detected_scenario_value')
                .text(resp.flow_label||'-');

            $('#detected_scenario_note')
                .text(
                    resp.approval_required
                        ? 'Company approval required'
                        : 'Immediate reactivation'
                );

            $('#preview_meta').removeClass('d-none');

            renderExisting(
                resp.existing_fee_structure||[]
            );

            if(resp.flow_type==='readmission'){
                $('#readmission_structure_source_wrap').removeClass('d-none');
            }else{
                $('#readmission_structure_source_wrap').addClass('d-none');
                $('#use_class_tuition_structure').prop('checked',false);
                $('#readmission_structure_source').val('student');
            }

            if(resp.flow_type==='reactivation'){
                $('#preview_table_wrap').addClass('d-none');
                $('#submit_btn').val('Reactivate Now');
            }else{
                renderTarget(
                    resp.target_fee_structure||[],
                    previousFlow===String(resp.flow_type||'')
                        ? preservedTargetIds
                        : null
                );

                $('#submit_btn').val('Send For Approval');
            }

            renderGap(resp);
            renderDetails(resp);
            renderTuitionRevision(resp);
            renderUnpaid(resp.unpaid_challans||[]);

            if(resp.generate_class_fee_url){
                $('#generate_class_fee_structure')
                    .attr('href',resp.generate_class_fee_url)
                    .removeClass('d-none');
            }else{
                $('#generate_class_fee_structure')
                    .addClass('d-none');
            }

            syncSelectedHeads();
        });

        thisXhr.fail(function(xhr,status){
            if(status==='abort'){
                return;
            }

            if(thisXhr!==previewXhr){
                return;
            }

            showError(
                xhr.responseJSON&&xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Unable to load preview.'
            );
        });

        thisXhr.always(function(){
            if(thisXhr===previewXhr){
                previewXhr=null;
                previewRequestKey=null;
                setButtonBusy($button,false);
            }
        });

        return true;
    }

    function updatePolicyUrl(){
        const r=selectedStudent();

        if(!r)return;

        const q=new URLSearchParams({
            from_readmission:'1',
            student_id:String(r.reg_id),
            branch_id:String(r.branch_id||''),
            class_id:String(r.class_id||''),
            readmission_roll_no:String(r.roll_no||''),
            period_from:String(
                $('[name="readmission_date"]').val()||''
            )
        });

        $('#apply_new_policy_btn').attr(
            'data-url',
            concessionCreateBaseUrl+'?'+q.toString()
        );
    }

    function refreshPolicy(){
        const id=$('#branch_students').val();

        if(!id){
            toast('error','Select a student first.');
            return;
        }

        if(!tryLock('policyStatus')){
            return;
        }

        const $button=$('#check_policy_status_btn');

        setButtonBusy(
            $button,
            true,
            '<span class="spinner-border spinner-border-sm me-1"></span>Checking...'
        );

        abortXhr(policyStatusXhr);

        policyStatusXhr=$.ajax({
            url:policyStatusUrl,
            type:'GET',
            data:{
                student_id:id
            }
        })
        .done(function(resp){
            if(
                resp.active_policy &&
                resp.active_policy.has_policy
            ){
                $('#end_policy_btn').removeClass('d-none');
            }else{
                $('#end_policy_btn').addClass('d-none');
            }

            /*
             * One policy check -> one preview refresh.
             */
            schedulePreview(50);
        })
        .fail(function(xhr,status){
            if(status==='abort')return;

            toast(
                'error',
                xhr.responseJSON&&xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Unable to check policy status.'
            );
        })
        .always(function(){
            policyStatusXhr=null;
            unlock('policyStatus');
            setButtonBusy($button,false);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Remove any previous handlers from this page namespace
    |--------------------------------------------------------------------------
    */
    $(document).off(NS);

    $(document).on('change'+NS,'#use_class_tuition_structure',function(){
        const useClass=$(this).is(':checked');
        $('#readmission_structure_source').val(useClass ? 'class' : 'student');
        updateTuitionIncrementVisibility();
        schedulePreview(50);
    });

    $('#readmission_form').off(NS);
    $('#end_policy_form').off(NS);

    /*
    |--------------------------------------------------------------------------
    | Single change handlers
    |--------------------------------------------------------------------------
    */
    $(document).on(
        'change'+NS,
        '#branch_from',
        function(){
            const branchId=this.value;

            $('#branch_from_hidden').val(branchId);

            /*
             * Source and target both remain editable. When source branch
             * changes, use the same branch as the new target default.
             */
            setSelect($('#new_branch_id'),branchId);

            loadBranchStudents(branchId);
            loadTargetClasses(branchId);
        }
    );

    $(document).on(
        'change'+NS,
        '#branch_students',
        function(){
            /*
             * Student selection must NEVER inherit a stale disabled state
             * from the previously selected student.
             */
            resetScenarioToAuto();
            setScenarioControl('auto',false,false);

            tuitionIncrementEffectiveTouched=false;
            $('#tuition_increment_effective_from').val($('#month_date').val()||'');

            resetPreview();
            applyStudent(selectedStudent());
        }
    );

    $(document).on(
        'change'+NS,
        '#flow_type_display',
        function(){
            /*
             * A manual choice is always editable.
             * Auto may be locked later only if preview detects Reactivation.
             */
            branchAutoReactivationLocked=false;
            $('#scenario_lock_note').addClass('d-none');

            syncFlow();
            schedulePreview(180);
        }
    );

    $(document).on(
        'change'+NS,
        '#new_branch_id',
        function(){
            const branchId=this.value;

            resetPreview();

            loadTargetClasses(
                branchId,
                null,
                function(){
                    rebuildSelect(
                        $('#new_section_id'),
                        [],
                        'Select Section'
                    );
                }
            );
        }
    );

    $(document).on(
        'change'+NS,
        '#new_class_id',
        function(){
            const classId=this.value;

            loadTargetSections(
                classId,
                null,
                function(){
                    /*
                     * Section must be selected before preview is valid.
                     * Do not fire an incomplete request here.
                     */
                    if($('#new_section_id').val()){
                        schedulePreview(120);
                    }
                }
            );
        }
    );

    $(document).on(
        'change'+NS,
        '#new_section_id',
        function(){
            schedulePreview(180);
        }
    );

    $(document).on(
        'change'+NS,
        '#month_date',
        function(){
            if(!tuitionIncrementEffectiveTouched){
                $('#tuition_increment_effective_from').val(this.value||'');
            }
            schedulePreview(180);
        }
    );

    $(document).on(
        'change'+NS,
        '#tuition_increment_effective_from',
        function(){
            tuitionIncrementEffectiveTouched=true;
            schedulePreview(180);
        }
    );

    $(document).on(
        'change'+NS,
        '#new_session_id',
        function(){
            updateTuitionIncrementVisibility();
            schedulePreview(180);
        }
    );

    $(document).on(
        'input'+NS,
        '#tuition_increment_percentage',
        function(){
            normalizeIncrementInput();
            schedulePreview(320);
        }
    );

    $(document).on(
        'change'+NS,
        '#tuition_increment_enabled',
        function(){
            $('#tuition_increment_percentage').prop(
                'disabled',
                !this.checked
            );

            schedulePreview(180);
        }
    );

    $(document).on(
        'change'+NS,
        '[name="readmission_date"]',
        function(){
            /*
             * Re-Admission Date can change the detected scenario.
             * Re-open Auto Detect before recalculating a previously
             * auto-locked Branch Reactivation.
             */
            releaseBranchAutoReactivationLock();
            schedulePreview(180);
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Manual Preview
    |--------------------------------------------------------------------------
    */
    $(document).on(
        'click'+NS,
        '#load_preview_btn',
        function(e){
            e.preventDefault();

            if(
                $(this).prop('disabled') ||
                $(this).attr('aria-busy')==='true'
            ){
                return false;
            }

            if(previewTimer){
                clearTimeout(previewTimer);
                previewTimer=null;
            }

            loadPreview();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Fee head selection handlers
    |--------------------------------------------------------------------------
    */
    $(document).on(
        'change'+NS,
        '.existing-fee-head',
        function(){
            syncExistingCheckAll();
            syncSelectedHeads();
        }
    );

    $(document).on(
        'change'+NS,
        '.target-fee-head',
        function(){
            syncTargetCheckAll();
            syncSelectedHeads();
        }
    );

    $(document).on(
        'change'+NS,
        '#check_all_existing_heads',
        function(){
            $('.existing-fee-head:not(:disabled)')
                .prop('checked',this.checked);

            syncSelectedHeads();
        }
    );

    $(document).on(
        'change'+NS,
        '#check_all_target_heads',
        function(){
            $('.target-fee-head:not(:disabled)')
                .prop('checked',this.checked);

            syncSelectedHeads();
        }
    );

    $(document).on(
        'change'+NS,
        '#check_all_gap_months',
        function(){
            $('.gap-month-check:not(:disabled)')
                .prop('checked',this.checked);
        }
    );

    $(document).on(
        'change'+NS,
        '.gap-month-check',
        function(){
            const all=$('.gap-month-check:not(:disabled)');

            $('#check_all_gap_months').prop(
                'checked',
                all.length>0 &&
                all.filter(':checked').length===all.length
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Save Current Fee Structure
    |--------------------------------------------------------------------------
    */
    $(document).on(
        'click'+NS,
        '#save_fee_structure_btn',
        function(e){
            e.preventDefault();

            /*
             * FIRST acquire lock.
             * Only after that disable the button/start AJAX.
             */
            if(!tryLock('feeStructure')){
                return false;
            }

            const r=selectedStudent();

            if(!r){
                unlock('feeStructure');
                toast('error','Select a student first.');
                return false;
            }

            const applicationSessionId=
                $('#new_session_id').val() ||
                r.session_id ||
                '';

            if(!applicationSessionId){
                unlock('feeStructure');
                toast(
                    'error',
                    'Please select the application session first.'
                );
                return false;
            }

            const $button=$(this);

            setButtonBusy(
                $button,
                true,
                '<span class="spinner-border spinner-border-sm me-1"></span>Saving...'
            );

            abortXhr(feeStructureXhr);

            feeStructureXhr=$.ajax({
                url:saveFeeStructureUrl,
                type:'POST',
                data:{
                    _token:csrfToken,
                    student_id:r.reg_id,

                    /*
                     * Session is used by backend only when it must attach
                     * a missing class structure to this student.
                     */
                    session_id:applicationSessionId,

                    selected_heads:currentSelectedHeads()
                }
            })
            .done(function(resp){
                toast(
                    resp.success?'success':'error',
                    resp.message||'Fee structure updated.'
                );

                if(resp.success){
                    /*
                     * Exactly one refresh after save.
                     */
                    schedulePreview(50);
                }
            })
            .fail(function(xhr,status){
                if(status==='abort')return;

                toast(
                    'error',
                    xhr.responseJSON&&xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Unable to save current structure.'
                );
            })
            .always(function(){
                feeStructureXhr=null;
                unlock('feeStructure');
                setButtonBusy($button,false);
            });
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Policy actions
    |--------------------------------------------------------------------------
    */
    $(document).on(
        'click'+NS,
        '#check_policy_status_btn',
        function(e){
            e.preventDefault();
            refreshPolicy();
        }
    );

    $(document).on(
        'click'+NS,
        '#end_policy_btn',
        function(e){
            e.preventDefault();

            const $button=$(this);

            if(
                $button.prop('disabled') ||
                $button.attr('aria-busy')==='true'
            ){
                return false;
            }

            /*
             * This button opens a modal only; short UI guard prevents
             * repeated modal opens.
             */
            setButtonBusy($button,true);

            const modalEl=document.getElementById('endPolicyModal');

            if(modalEl){
                new bootstrap.Modal(modalEl).show();
            }

            window.setTimeout(function(){
                setButtonBusy($button,false);
            },500);
        }
    );

    $('#end_policy_form')
        .off('submit'+NS)
        .on(
            'submit'+NS,
            function(e){
                e.preventDefault();
                e.stopImmediatePropagation();

                if(!tryLock('endPolicy')){
                    return false;
                }

                const id=$('#branch_students').val();

                if(!id){
                    unlock('endPolicy');
                    toast('error','Select a student first.');
                    return false;
                }

                const $form=$(this);
                const $button=$form.find('[type="submit"]');

                setButtonBusy(
                    $button,
                    true,
                    '<span class="spinner-border spinner-border-sm me-1"></span>Saving...'
                );

                const data=$form.serializeArray();

                data.push(
                    {
                        name:'student_id',
                        value:id
                    },
                    {
                        name:'_token',
                        value:csrfToken
                    }
                );

                abortXhr(endPolicyXhr);

                endPolicyXhr=$.ajax({
                    url:policyEndUrl,
                    type:'POST',
                    data:$.param(data)
                })
                .done(function(resp){
                    toast(
                        resp.success?'success':'error',
                        resp.message||'Policy updated.'
                    );

                    const modalEl=
                        document.getElementById('endPolicyModal');

                    if(modalEl){
                        const instance=
                            bootstrap.Modal.getInstance(modalEl);

                        if(instance){
                            instance.hide();
                        }
                    }

                    refreshPolicy();
                })
                .fail(function(xhr,status){
                    if(status==='abort')return;

                    toast(
                        'error',
                        xhr.responseJSON&&xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Unable to end policy.'
                    );
                })
                .always(function(){
                    endPolicyXhr=null;
                    unlock('endPolicy');
                    setButtonBusy($button,false);
                });

                return false;
            }
        );

    /*
    |--------------------------------------------------------------------------
    | Guard AJAX popup anchor
    |--------------------------------------------------------------------------
    |
    | Direct binding runs before the usual document-level AJAX-popup
    | delegated handler. The first click is allowed through; subsequent
    | fast clicks are stopped.
    |
    */
    $('#apply_new_policy_btn')
        .off('click'+NS)
        .on(
            'click'+NS,
            function(e){
                const $anchor=$(this);

                if($anchor.data('readmission-busy')){
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }

                lockAnchor($anchor,2500);
            }
        );

    /*
    |--------------------------------------------------------------------------
    | Guard generated-fee-structure anchor
    |--------------------------------------------------------------------------
    */
    $('#generate_class_fee_structure')
        .off('click'+NS)
        .on(
            'click'+NS,
            function(e){
                const $anchor=$(this);
                const href=String($anchor.attr('href')||'');

                if(
                    !href ||
                    href==='#' ||
                    $anchor.hasClass('d-none')
                ){
                    e.preventDefault();
                    return false;
                }

                if($anchor.data('readmission-busy')){
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }

                /*
                 * Navigation starts only after this synchronous lock.
                 */
                lockAnchor($anchor,5000);
            }
        );

    /*
    |--------------------------------------------------------------------------
    | Policy popup completion event
    |--------------------------------------------------------------------------
    |
    | Remove previous global handler if this script was initialized before.
    |
    */
    if(window.__readmissionPolicyUpdatedHandler){
        window.removeEventListener(
            'readmission:policy-updated',
            window.__readmissionPolicyUpdatedHandler
        );
    }

    window.__readmissionPolicyUpdatedHandler=function(){
        unlockAnchor($('#apply_new_policy_btn'));
        refreshPolicy();
    };

    window.addEventListener(
        'readmission:policy-updated',
        window.__readmissionPolicyUpdatedHandler
    );

    /*
    |--------------------------------------------------------------------------
    | Main form submit - hard double-submit guard
    |--------------------------------------------------------------------------
    */
    $('#readmission_form')
        .off('submit'+NS)
        .on(
            'submit'+NS,
            function(e){
                /*
                 * Lock synchronously BEFORE browser navigation/request starts.
                 */
                if(!tryLock('formSubmit')){
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }

                syncFlow();
                syncSelectedHeads();

                if(isBranch){
                    $('.gap-month-check').prop('checked',false);
                }

                if(
                    !$('#branch_from_hidden').val() &&
                    $('#branch_from').length
                ){
                    $('#branch_from_hidden').val(
                        $('#branch_from').val()
                    );
                }

                const $submit=$('#submit_btn');

                setButtonBusy(
                    $submit,
                    true,
                    'Submitting...'
                );

                /*
                 * Also disable other request-producing controls after the
                 * form lock has been acquired.
                 */
                $('#load_preview_btn,#save_fee_structure_btn,#check_policy_status_btn,#end_policy_btn')
                    .prop('disabled',true);

                $('#apply_new_policy_btn,#generate_class_fee_structure')
                    .addClass('readmission-control-busy')
                    .attr('aria-disabled','true');

                /*
                 * Allow the FIRST normal form submission.
                 */
                return true;
            }
        );

    /*
    |--------------------------------------------------------------------------
    | Initial load
    |--------------------------------------------------------------------------
    */
    if(isBranch){
        setSelect(
            $('#branch_from'),
            $('#branch_from_hidden').val()
        );

        setSelect(
            $('#new_branch_id'),
            $('#branch_from_hidden').val()
        );

        loadBranchStudents(
            $('#branch_from_hidden').val()
        );
    }else if($('#branch_from').val()){
        loadBranchStudents(
            $('#branch_from').val()
        );
    }

})();
</script>
@endpush
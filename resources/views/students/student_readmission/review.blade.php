@extends('layouts.admin')

@section('page-title')
    {{ __('Readmission Review') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('readmissionstudent.index') }}">{{ __('Re-Admission') }}</a></li>
    <li class="breadcrumb-item">{{ __('Review') }}</li>
@endsection
@section('action-btn')
    <div class="float-end mb-3"><a href="{{ route('readmissionstudent.index') }}" class="btn btn-sm btn-light border"><i class="ti ti-arrow-left me-1"></i>{{ __('Back to Applications') }}</a></div>
@endsection

@push('css-page')
<style>
    .review-page{--border:#e6edf4;--soft:#f8fafc}
    .review-hero{border:0;border-radius:18px;color:#fff;background:linear-gradient(135deg,#102a43 0%,#721818 52%,#2563a8 100%);box-shadow:0 14px 34px rgba(15,39,66,.15)}
    .review-hero .card-body{padding:1.25rem 1.4rem}.application-code{display:inline-flex;padding:.3rem .65rem;border-radius:999px;background:rgba(255,255,255,.14);font-size:.75rem;font-weight:700}.hero-title{font-size:1.35rem;font-weight:800;margin:.6rem 0 .15rem;color:#fff}.hero-meta{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.8rem}.hero-meta span{padding:.35rem .65rem;border-radius:9px;background:rgba(255,255,255,.1);font-size:.78rem}.status-pill{padding:.4rem .75rem;border-radius:999px;font-size:.75rem;font-weight:800}.status-pending{background:#fff7ed;color:#c2410c}.status-approved{background:#dcfce7;color:#166534}.status-rejected{background:#fee2e2;color:#991b1b}
    .section-card{border:1px solid var(--border);border-radius:16px;box-shadow:0 8px 22px rgba(15,39,66,.05);overflow:hidden;background:#fff}.section-card .card-header{background:#fff;border-bottom:1px solid var(--border);padding:.85rem 1rem}.section-card .card-body{padding:1rem}.section-title{font-size:.95rem;font-weight:800;color:#1f2937}.section-subtitle{font-size:.76rem;color:#8290a3}.info-box{height:100%;border:1px solid #edf1f5;background:#fbfcfe;border-radius:11px;padding:.7rem}.info-label{font-size:.69rem;color:#7b8794;text-transform:uppercase;font-weight:700;margin-bottom:.2rem}.info-value{font-size:.87rem;font-weight:700;color:#1f2937}.placement-side{padding:1rem;background:#fbfcfe}.placement-side.target{background:#f5f9ff}.placement-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem}.placement-item{border:1px solid #e8edf3;border-radius:10px;padding:.6rem;background:#fff}.placement-arrow{display:flex;align-items:center;justify-content:center;height:100%;min-height:80px;color:#2563eb;font-size:1.3rem}
    .table-responsive{overflow-x:auto;width:100%;margin:0!important}.review-table{min-width:720px;margin-bottom:0}.review-table thead th{background:#f7f9fc!important;color:#526174!important;font-size:.72rem;text-transform:uppercase;font-weight:800;white-space:nowrap}.review-table td{vertical-align:middle;font-size:.82rem}.amount{text-align:right;white-space:nowrap}.selected-row{background:#f0fdf4!important}.coverage-missing{background:#ffedd5;color:#c2410c}.coverage-covered{background:#dcfce7;color:#166534}.coverage-pill{display:inline-flex;padding:.27rem .55rem;border-radius:999px;font-size:.7rem;font-weight:800}.edit-banner{border-left:4px solid #2563eb;background:#eff6ff;color:#1e40af;border-radius:10px;padding:.65rem .8rem;font-size:.82rem}.save-current-btn,.save-current-btn:hover{background:#0f766e!important;border-color:#0f766e!important;color:#fff!important}.save-review-btn,.save-review-btn:hover{background:#2563eb!important;border-color:#2563eb!important;color:#fff!important}.approve-btn,.approve-btn:hover{background:#16a34a!important;border-color:#16a34a!important;color:#fff!important}.reject-btn,.reject-btn:hover{background:#dc2626!important;border-color:#dc2626!important;color:#fff!important}.gap-edit-row{background:#fffaf5}.current-check,.target-check,.gap-check{width:18px;height:18px}.decision-card{border:1px solid #dbe5ef;border-radius:16px;background:#f8fafc}
    /* Dedicated fee-table layout prevents the last column being clipped inside the 50% cards. */
    .review-fee-row>[class*="col-"]{min-width:0}
    .review-fee-card,.review-fee-card .card-body{min-width:0}
    .review-fee-card .card-body{overflow:hidden}
    .review-fee-scroll{display:block;width:100%;max-width:100%;min-width:0;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;scrollbar-gutter:stable;padding:0 10px 8px 0;margin:0!important}
    .review-fee-scroll::-webkit-scrollbar{height:10px}
    .review-fee-table{width:max-content!important;min-width:100%!important;max-width:none!important;table-layout:auto;margin-bottom:0!important}
    .review-fee-table th,.review-fee-table td{padding:.4rem .42rem!important;vertical-align:middle;font-size:.75rem}
    .review-fee-table thead th{font-size:.66rem!important;white-space:nowrap}
    .review-fee-table .fee-col-check{width:46px;min-width:46px;text-align:center}
    .review-fee-table .fee-col-index{width:34px;min-width:34px;text-align:center}
    .review-fee-table .fee-col-head{width:132px;min-width:112px;max-width:165px;white-space:normal!important;overflow-wrap:anywhere}
    .review-fee-table .fee-col-money{width:82px;min-width:76px;text-align:right;white-space:nowrap}
    .review-fee-table .fee-col-discount{width:70px;min-width:66px;text-align:right;white-space:nowrap}
    .review-fee-table .fee-col-source{width:120px;min-width:100px;max-width:145px;white-space:normal!important}
    .review-fee-table .fee-col-source .badge{white-space:normal;text-align:left;line-height:1.15;max-width:140px}
    @media(max-width:991px){.placement-grid{grid-template-columns:1fr}.placement-arrow{min-height:48px;transform:rotate(90deg)}}

    .review-gap-summary{display:flex;gap:.45rem;flex-wrap:wrap;margin-bottom:.7rem}
    .review-gap-summary span{display:inline-flex;border:1px solid #e2e8f0;background:#f8fafc;border-radius:999px;padding:.3rem .6rem;font-size:.72rem;color:#475569}
    .review-gap-chip-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.45rem;max-height:165px;overflow-y:auto}
    .review-gap-option{display:flex;align-items:center;gap:.45rem;border:1px solid #fed7aa;background:#fffaf5;border-radius:10px;padding:.5rem .6rem;margin:0}
    .review-gap-option.readonly{opacity:.86}
    .review-gap-option .month{font-size:.77rem;font-weight:800;color:#9a3412}
    .review-covered-wrap{display:flex;flex-wrap:wrap;gap:.35rem;max-height:100px;overflow-y:auto;margin-top:.5rem}
    .review-covered-chip{display:inline-flex;border-radius:999px;border:1px solid #bbf7d0;background:#ecfdf5;color:#166534;padding:.28rem .52rem;font-size:.68rem;font-weight:700}
    .review-covered-chip.advance{background:#ecfeff;color:#155e75;border-color:#a5f3fc}
    .review-covered-chip.admission{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
    .generated-challan-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:.7rem}
    .generated-challan-item{border:1px solid #dbe5ef;border-radius:12px;padding:.8rem;background:#fbfdff}
    .generated-challan-no{font-size:1rem;font-weight:800}
    .generated-challan-meta{font-size:.73rem;color:#64748b;margin-top:.25rem;display:flex;gap:.35rem;flex-wrap:wrap}
    .implementation-badge{display:inline-flex;padding:.3rem .6rem;border-radius:999px;font-size:.7rem;font-weight:800}
    .implementation-pending{background:#fff7ed;color:#c2410c}
    .implementation-applied{background:#dcfce7;color:#166534}

</style>
@endpush

@section('content')
@php
    $flow=$snapshot['flow']??[];$student=$snapshot['student']??[];$withdrawal=$snapshot['withdrawal']??[];$source=$snapshot['source_placement']??[];$target=$snapshot['target_placement']??[];$policy=$snapshot['active_policy']??[];$latestPolicy=$snapshot['latest_policy_application']??[];$requestSnapshot=$snapshot['request']??[];
    $unpaidChallans=$snapshot['unpaid_challans']??[];$existingFeeStructure=$snapshot['existing_fee_structure']??[];$targetFeeStructure=$snapshot['target_fee_structure']??[];$feeRevision=$snapshot['fee_revision']??[];$gapRange=$snapshot['gap_range']??[];$gapStatuses=$snapshot['gap_month_statuses']??[];$gapMonths=$snapshot['gap_months']??[];$selectedGapMonths=$snapshot['selected_gap_months']??[];
    $selectedHeadIds=collect($snapshot['selected_head_ids']??[])->map(function($id){return(int)$id;})->all();
    $displayDate=function($v){if(empty($v))return '-';try{return \Carbon\Carbon::parse($v)->format('d-F-Y');}catch(\Throwable $e){return $v;}};
    $displayMonth=function($v){if(empty($v))return '-';try{return \Carbon\Carbon::parse($v)->format('F Y');}catch(\Throwable $e){return $v;}};
    $displayMonthList=function($v)use($displayMonth){if(empty($v))return '-';return collect(explode(',',(string)$v))->map(function($m)use($displayMonth){return $displayMonth(trim($m));})->implode(', ');};
    $flowType=$flow['type']??$record->flow_type;$flowLabel=$flowType==='re_enrollment'?'Re-enrollment':($flowType==='reactivation'?'Reactivation':'Readmission');$status=strtolower(trim((string)$record->status));
    $statusClass=$status==='approved'?'status-approved':(in_array($status,['rejected','rollbacked','canceled','cancelled'],true)?'status-rejected':'status-pending');
    $existingSelectedIds=collect($snapshot['existing_selected_head_ids']??[])->map(function($id){return(int)$id;})->all();
    if(empty($existingSelectedIds)){$existingSelectedIds=collect($existingFeeStructure)->filter(function($r){return(int)($r['checked']??0)===1;})->pluck('head_id')->map(function($id){return(int)$id;})->all();}

    $isBranchReview=Auth::user()->type==='branch';
    $generatedChallans=isset($generatedChallans)?$generatedChallans:collect();
    $approvalSnapshot=!empty($record->approval_snapshot)?json_decode($record->approval_snapshot,true):[];
    $implementation=$approvalSnapshot['implementation']??[];
    $readmissionStructureSource=$snapshot['readmission_structure_source']??$requestSnapshot['readmission_structure_source']??'student';
@endphp

<div class="review-page">
    <div class="card review-hero mb-3"><div class="card-body d-flex justify-content-between align-items-start gap-3 flex-wrap"><div><span class="application-code">#RA-{{ str_pad($record->id,5,'0',STR_PAD_LEFT) }}</span><div class="hero-title">{{ $student['roll_no']??$record->student_id }} - {{ $student['student_name']??'-' }}</div><div class="text-white-50 small">{{ __('Company review uses the saved branch snapshot. Approved applications become preview-only.') }}</div><div class="hero-meta"><span>{{ $flowLabel }}</span><span>{{ $displayDate($requestSnapshot['readmission_date']??$record->readmission_date) }}</span><span>{{ $target['branch']??optional($record->branch)->name??'-' }}</span></div></div><span class="status-pill {{ $statusClass }}">{{ ucwords($status) }}</span></div></div>
    @if($editable)<div class="edit-banner mb-3"><i class="ti ti-edit me-1"></i><strong>{{ __('Company Edit Mode') }}</strong> — {{ __('Save changes first or use Approve & Generate to save the current edits and approve in one action.') }}</div>@endif

    @if($editable)
        <form method="POST" action="{{ route('readmissionstudent.review-update',$record->id) }}" id="companyReviewForm">
            @csrf
            <input type="hidden" name="current_structure_present" value="1">

            {{-- Permanent action flag.
                 Do not rely on submit-button name/value because buttons are disabled
                 during submit and disabled controls are excluded from POST data. --}}
            <input type="hidden"
                   name="approve_after_save"
                   id="review_approve_after_save"
                   value="0">
    @endif

    <div class="row g-3 mb-3">
        <div class="col-xl-7"><div class="card section-card h-100"><div class="card-header"><div class="section-title">{{ __('Application Details') }}</div><div class="section-subtitle">{{ __('Company can edit these values while status is For Approval.') }}</div></div><div class="card-body"><div class="row g-2">
            <div class="col-md-4"><div class="info-box"><div class="info-label">{{ __('Scenario') }}</div>@if($editable)<select name="flow_type" id="review_flow_type" class="form-control"><option value="readmission" {{ $flowType==='readmission'?'selected':'' }}>{{ __('Readmission') }}</option><option value="re_enrollment" {{ $flowType==='re_enrollment'?'selected':'' }}>{{ __('Re-enrollment') }}</option></select>@else<div class="info-value">{{ $flowLabel }}</div>@endif</div></div>
            <div class="col-md-4"><div class="info-box"><div class="info-label">{{ __('Re-Admission Date') }}</div>@if($editable)<input type="date" name="readmission_date" class="form-control" value="{{ $requestSnapshot['readmission_date']??$record->readmission_date }}" required>@else<div class="info-value">{{ $displayDate($requestSnapshot['readmission_date']??null) }}</div>@endif</div></div>
            <div class="col-md-4"><div class="info-box"><div class="info-label">{{ __('Fee Month') }}</div>@if($editable)<input type="month" name="month_date" id="review_month_date" class="form-control" value="{{ !empty($requestSnapshot['billing_month'])?\Carbon\Carbon::parse($requestSnapshot['billing_month'])->format('Y-m'):'' }}" required>@else<div class="info-value">{{ $displayMonth($requestSnapshot['billing_month']??null) }}</div>@endif</div></div>
            <div class="col-md-4"><div class="info-box"><div class="info-label">{{ __('Issue Date') }}</div>@if($editable)<input type="date" name="issue_date" class="form-control" value="{{ $requestSnapshot['issue_date']??'' }}" required>@else<div class="info-value">{{ $displayDate($requestSnapshot['issue_date']??null) }}</div>@endif</div></div>
            <div class="col-md-4"><div class="info-box"><div class="info-label">{{ __('Due Date') }}</div>@if($editable)<input type="date" name="due_date" class="form-control" value="{{ $requestSnapshot['due_date']??'' }}" required>@else<div class="info-value">{{ $displayDate($requestSnapshot['due_date']??null) }}</div>@endif</div></div>
            <div class="col-md-4"><div class="info-box"><div class="info-label">{{ __('Withdrawal Gap') }}</div><div class="info-value">{{ $flow['withdrawal_gap_days']??'-' }} {{ __('day(s)') }}</div></div></div>
            <div class="col-12"><div class="info-label mt-1">{{ __('Reason') }}</div>@if($editable)<textarea name="reason" class="form-control" rows="3" required>{{ $requestSnapshot['reason']??$record->remarks }}</textarea>@else<div class="info-box"><div class="info-value">{{ $requestSnapshot['reason']??$record->remarks??'-' }}</div></div>@endif</div>
        </div></div></div></div>
        <div class="col-xl-5"><div class="card section-card h-100"><div class="card-header"><div class="section-title">{{ __('Student / Withdrawal') }}</div></div><div class="card-body"><div class="row g-2"><div class="col-6"><div class="info-box"><div class="info-label">{{ __('Class') }}</div><div class="info-value">{{ $student['class']??'-' }}</div></div></div><div class="col-6"><div class="info-box"><div class="info-label">{{ __('Section') }}</div><div class="info-value">{{ $student['section']??'-' }}</div></div></div><div class="col-6"><div class="info-box"><div class="info-label">{{ __('Session') }}</div><div class="info-value">{{ $student['session']??'-' }}</div></div></div><div class="col-6"><div class="info-box"><div class="info-label">{{ __('D.O.A') }}</div><div class="info-value">{{ $displayDate($student['date_of_admission']??null) }}</div></div></div><div class="col-6"><div class="info-box"><div class="info-label">{{ __('Withdraw Date') }}</div><div class="info-value">{{ $displayDate($withdrawal['withdraw_date']??null) }}</div></div></div><div class="col-6"><div class="info-box"><div class="info-label">{{ __('Expected Rejoin') }}</div><div class="info-value">{{ $displayDate($withdrawal['expected_readmission_date']??null) }}</div></div></div></div></div></div></div>
    </div>

    <div class="card section-card mb-3"><div class="card-header"><div class="section-title">{{ __('Current → Target Placement') }}</div></div><div class="card-body p-0"><div class="row g-0"><div class="col-lg-5 placement-side"><div class="section-subtitle mb-2">{{ __('Current / Source') }}</div><div class="placement-grid"><div class="placement-item"><div class="info-label">Branch</div><div class="info-value">{{ $source['branch']??'-' }}</div></div><div class="placement-item"><div class="info-label">Class</div><div class="info-value">{{ $source['class']??'-' }}</div></div><div class="placement-item"><div class="info-label">Section</div><div class="info-value">{{ $source['section']??'-' }}</div></div><div class="placement-item"><div class="info-label">Session</div><div class="info-value">{{ $source['session']??'-' }}</div></div></div></div><div class="col-lg-2"><div class="placement-arrow"><i class="ti ti-arrow-right"></i></div></div><div class="col-lg-5 placement-side target"><div class="section-subtitle mb-2">{{ __('Requested / Target') }}</div><div class="placement-grid">
        @if($editable)
            <div class="placement-item"><div class="info-label">Branch</div>{{ Form::select('new_branch_id',$reviewBranches,$target['branch_id']??$record->branch_id,['class'=>'form-control','id'=>'review_new_branch','required'=>'required']) }}</div>
            <div class="placement-item"><div class="info-label">Session</div>{{ Form::select('new_session_id',$reviewSessions,$target['session_id']??$record->session_id,['class'=>'form-control','id'=>'review_new_session','required'=>'required']) }}</div>
            <div class="placement-item"><div class="info-label">Class</div>{{ Form::select('new_class_id',$reviewClasses,$target['class_id']??$record->class_id,['class'=>'form-control','id'=>'review_new_class','required'=>'required']) }}</div>
            <div class="placement-item"><div class="info-label">Section</div>{{ Form::select('new_section_id',$reviewSections,$target['section_id']??$record->target_section_id,['class'=>'form-control','id'=>'review_new_section','required'=>'required']) }}</div>
        @else
            <div class="placement-item"><div class="info-label">Branch</div><div class="info-value">{{ $target['branch']??'-' }}</div></div><div class="placement-item"><div class="info-label">Session</div><div class="info-value">{{ $target['session']??'-' }}</div></div><div class="placement-item"><div class="info-label">Class</div><div class="info-value">{{ $target['class']??'-' }}</div></div><div class="placement-item"><div class="info-label">Section</div><div class="info-value">{{ $target['section']??'-' }}</div></div>
        @endif
    </div></div></div></div></div>

    <div id="review_tuition_revision_wrap"
         class="card section-card mb-3 {{ !empty($feeRevision['session_changed']) ? '' : 'd-none' }}">
        <div class="card-header">
            <div class="section-title">{{ __('Tuition Fee Revision on Session Change') }}</div>
            <div class="section-subtitle">
                {{ __('The revision uses the selected Tuition base: student Tuition by default, or target class Tuition when Use Class Tuition Fee is checked.') }}
                <strong>{{ __('Choose the month from which the increment should become applicable.') }}</strong>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    @if($editable)
                        <label class="form-check mb-2">
                            <input type="hidden" name="tuition_increment_enabled" value="0">
                            <input type="checkbox"
                                   class="form-check-input"
                                   name="tuition_increment_enabled"
                                   id="review_tuition_increment_enabled"
                                   value="1"
                                   {{ !array_key_exists('enabled',$feeRevision) || !empty($feeRevision['enabled']) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">{{ __('Apply Tuition Revision') }}</span>
                        </label>

                        <label class="form-label">{{ __('Increment %') }}</label>
                        <input type="number"
                               name="tuition_increment_percentage"
                               id="review_tuition_increment_percentage"
                               class="form-control"
                               min="0"
                               step="1"
                               inputmode="numeric"
                               value="{{ (int)($feeRevision['percentage']??0) }}"
                               required>
                        <div class="small text-muted mt-1">{{ __('Whole number only. Minimum 0%.') }}</div>
                    @else
                        <div class="info-label">{{ __('Increment') }}</div>
                        <div class="info-value">{{ (int)($feeRevision['percentage']??0) }}%</div>
                    @endif
                </div>

                <div class="col-md-3">
                    @if($editable)
                        <label class="form-label">{{ __('Increment Fee Applicable From') }}</label>
                        <input type="month"
                               name="tuition_increment_effective_from"
                               id="review_tuition_increment_effective_from"
                               class="form-control"
                               value="{{ !empty($feeRevision['effective_from']) ? \Carbon\Carbon::parse($feeRevision['effective_from'])->format('Y-m') : (!empty($requestSnapshot['billing_month']) ? \Carbon\Carbon::parse($requestSnapshot['billing_month'])->format('Y-m') : '') }}">
                        <div class="small text-muted mt-1">{{ __('Defaults to Billing Month. Company can change it before approval.') }}</div>
                    @else
                        <div class="info-label">{{ __('Increment Fee Applicable From') }}</div>
                        <div class="info-value">{{ $displayMonth($feeRevision['effective_from'] ?? $requestSnapshot['billing_month'] ?? null) }}</div>
                    @endif
                </div>

                <div class="col-md-6">
                    <div id="review_tuition_increment_preview" class="info-box">
                        <div class="info-label">{{ $feeRevision['fee_head']??__('Tuition Fee') }}</div>
                        <div class="info-value">
                            {{ __('Existing') }}:
                            {{ number_format((float)($feeRevision['prev_base_amount']??0),2) }}
                            <span class="mx-2">→</span>
                            {{ __('Revised') }}:
                            {{ number_format((float)($feeRevision['new_base_amount']??0),2) }}
                        </div>
                        <div class="small text-primary mt-1">
                            {{ __('Effective from') }}
                            <strong>{{ $displayMonth($feeRevision['effective_from'] ?? $requestSnapshot['billing_month'] ?? null) }}</strong>
                            {{ __('onward. Months before this use the previous Tuition amount.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 review-fee-row">
        <div class="col-12 col-lg-6">
            <div class="card section-card review-fee-card h-100 mb-0"><div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"><div><div class="section-title">{{ __('Current Fee Structure') }}</div><div class="section-subtitle">{{ __('This selection updates the student real StudentFeeStructure.checked_status.') }}</div></div>@if($editable)<button type="button" id="save_current_structure" class="btn btn-sm save-current-btn"><i class="ti ti-device-floppy me-1"></i>{{ __('Save Current Structure') }}</button>@endif</div><div class="card-body p-0"><div class="review-fee-scroll"><table class="table review-table review-fee-table"><thead><tr>@if($editable)<th class="fee-col-check"><input type="checkbox" id="review_check_all_existing"></th>@endif<th class="fee-col-index">#</th><th class="fee-col-head">{{ __('Fee Head') }}</th><th class="fee-col-money">{{ __('Amount') }}</th><th class="fee-col-discount">{{ __('Discount %') }}</th><th class="fee-col-money">{{ __('Payable') }}</th></tr></thead><tbody id="review_existing_body">@if(count($existingFeeStructure)>0)@foreach($existingFeeStructure as $i=>$row)<tr>@if($editable)<td class="fee-col-check"><input type="checkbox" name="current_selected_heads[]" class="form-check-input current-check" value="{{ (int)($row['head_id']??0) }}" {{ in_array((int)($row['head_id']??0),$existingSelectedIds,true)?'checked':'' }}></td>@endif<td class="fee-col-index">{{ $i+1 }}</td><td class="fee-col-head"><strong>{{ $row['fee_head']??'-' }}</strong></td><td class="fee-col-money">{{ number_format((float)($row['class_amount']??0),2) }}</td><td class="fee-col-discount">{{ number_format((float)($row['discount']??0),2) }}</td><td class="fee-col-money">{{ number_format((float)($row['payable_amount']??0),2) }}</td></tr>@endforeach @else<tr><td colspan="{{ $editable?6:5 }}" class="text-center text-muted py-4">{{ __('No current structure snapshot.') }}</td></tr>@endif</tbody></table></div></div></div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card section-card review-fee-card h-100 mb-0">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="section-title">{{ __('Applicable / Charge Selection') }}</div>
                        <div class="section-subtitle">{{ __('Will Charge shows the exact base amount saved for approval-time challan generation.') }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        @if($editable)
                            <label class="form-check mb-0 {{ $flowType==='readmission'?'':'d-none' }}" id="review_structure_source_wrap">
                                <input type="hidden" name="readmission_structure_source" id="review_readmission_structure_source" value="{{ $readmissionStructureSource }}">
                                <input type="checkbox" id="review_use_class_tuition_structure" class="form-check-input" {{ $readmissionStructureSource==='class'?'checked':'' }}>
                                <span class="form-check-label fw-semibold">{{ __('Use Class Tuition Fee') }}</span>
                                <div class="small text-muted">{{ __('Unchecked = Student Structure') }}</div>
                            </label>
                        @elseif($flowType==='readmission')
                            <span class="badge bg-light text-dark border">
                                {{ $readmissionStructureSource==='class' ? __('Tuition Source: Class Structure') : __('Tuition Source: Student Structure') }}
                            </span>
                        @endif
                        @if($editable)
                            <label class="form-check mb-0">
                                <input type="checkbox" id="review_check_all_target" class="form-check-input">
                                <span class="form-check-label">{{ __('Check all') }}</span>
                            </label>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="review-fee-scroll">
                        <table class="table review-table review-fee-table">
                            <thead>
                                <tr>
                                    <th class="fee-col-check">{{ __('Selected') }}</th>
                                    <th class="fee-col-index">#</th>
                                    <th class="fee-col-head">{{ __('Fee Head') }}</th>
                                    <th class="fee-col-money">{{ __('Existing') }}</th>
                                    <th class="fee-col-money">{{ __('Class Ref.') }}</th>
                                    <th class="fee-col-money">{{ __('Will Charge') }}</th>
                                    <th class="fee-col-source">{{ __('Source') }}</th>
                                    <th class="fee-col-discount">{{ __('Discount %') }}</th>
                                    <th class="fee-col-money">{{ __('Payable') }}</th>
                                </tr>
                            </thead>
                            <tbody id="review_target_body">
                                @if(count($targetFeeStructure)>0)
                                    @foreach($targetFeeStructure as $i=>$row)
                                        @php
                                            $autoCharge = $flowType==='readmission' && !empty($row['auto_charge']);
                                            $sel = in_array((int)($row['head_id']??0),$selectedHeadIds,true);

                                            if($isBranchReview && $autoCharge){
                                                $sel=true;
                                            }

                                            $disabledReadmission = $flowType==='readmission' && !empty($row['branch_disabled']);
                                            $lockedSelection = $isBranchReview && ($disabledReadmission || $autoCharge);
                                        @endphp
                                        <tr class="{{ $sel?'selected-row':'' }}">
                                            <td class="fee-col-check">
                                                @if($editable)
                                                    <input type="checkbox"
                                                           name="selected_heads[]"
                                                           class="form-check-input target-check"
                                                           value="{{ (int)($row['head_id']??0) }}"
                                                           {{ $sel?'checked':'' }}
                                                           {{ $lockedSelection?'disabled':'' }}>
                                                @else
                                                    <span class="badge {{ $sel?'bg-success':'bg-light text-dark border' }}">{{ $sel?__('Yes'):__('No') }}</span>
                                                @endif
                                            </td>
                                            <td class="fee-col-index">{{ $i+1 }}</td>
                                            <td class="fee-col-head">
                                                <strong>{{ $row['fee_head']??'-' }}</strong>
                                                @if($isBranchReview && $autoCharge)
                                                    <div class="small text-primary">{{ __('Automatically charged on Readmission Fee Month') }}</div>
                                                @elseif($isBranchReview && $disabledReadmission)
                                                    <div class="small text-danger">{{ __('Not chargeable on Re-Admission') }}</div>
                                                @endif
                                            </td>
                                            <td class="fee-col-money">{{ isset($row['existing_amount'])&&$row['existing_amount']!==null?number_format((float)$row['existing_amount'],2):'-' }}</td>
                                            <td class="fee-col-money">{{ isset($row['class_reference_amount'])&&$row['class_reference_amount']!==null?number_format((float)$row['class_reference_amount'],2):'-' }}</td>
                                            <td class="fee-col-money"><strong>{{ number_format((float)($row['charge_amount']??$row['class_amount']??0),2) }}</strong></td>
                                            <td class="fee-col-source"><span class="badge bg-light text-dark border">{{ $row['charge_source_label']??($row['source']==='student_fee_structure'?__('Existing Student Fee'):__('Class Fee Structure')) }}</span></td>
                                            <td class="fee-col-discount">{{ number_format((float)($row['discount']??0),2) }}</td>
                                            <td class="fee-col-money">{{ number_format((float)($row['payable_amount']??0),2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="9" class="text-center text-muted py-4">{{ __('No target structure snapshot.') }}</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="section-title">{{ __('Generate missing fee months on approval') }}</div>
                <div class="section-subtitle">{{ __('Only missing months are shown as selections. Covered months are summarized below.') }}</div>
            </div>
            @if($editable)
                <label class="form-check mb-0">
                    <input type="checkbox" id="review_check_all_gap" class="form-check-input">
                    <span class="form-check-label small fw-semibold">{{ __('Select all missing') }}</span>
                </label>
            @endif
        </div>
        <div class="card-body">
            @php
                $reviewMissingMonths = collect($gapStatuses)->filter(function($row){
                    return ($row['status'] ?? '') === 'missing';
                })->values()->all();
                $reviewCoveredMonths = collect($gapStatuses)->filter(function($row){
                    return ($row['status'] ?? '') !== 'missing';
                })->values()->all();
            @endphp

            <div class="review-gap-summary">
                <span><strong>{{ __('Range') }}:</strong>&nbsp;{{ $displayMonth($gapRange['from']??null) }} – {{ $displayMonth($gapRange['to']??null) }}</span>
                <span><strong>{{ count($reviewMissingMonths) }}</strong>&nbsp;{{ __('missing') }}</span>
                <span><strong>{{ count($reviewCoveredMonths) }}</strong>&nbsp;{{ __('covered') }}</span>
            </div>

            @if(count($reviewMissingMonths)>0)
                <div class="review-gap-chip-grid">
                    @foreach($reviewMissingMonths as $row)
                        @php $selectedGap = in_array($row['month'],$selectedGapMonths,true); @endphp
                        <label class="review-gap-option {{ $editable?'':'readonly' }}">
                            @if($editable)
                                <input type="checkbox" class="form-check-input gap-check" name="gap_months[]" value="{{ $row['month'] }}" {{ $selectedGap?'checked':'' }}>
                            @else
                                <input type="checkbox" class="form-check-input" {{ $selectedGap?'checked':'' }} disabled>
                            @endif
                            <span class="month">{{ $displayMonth($row['month']??null) }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="small text-success">{{ __('No missing months are available to charge.') }}</div>
            @endif

            @if(count($reviewCoveredMonths)>0)
                <details class="mt-3">
                    <summary class="small fw-semibold text-muted" style="cursor:pointer">{{ __('View already covered months') }} ({{ count($reviewCoveredMonths) }})</summary>
                    <div class="review-covered-wrap">
                        @foreach($reviewCoveredMonths as $row)
                            @php
                                $coveredClass = ($row['status']??'')==='advance_covered'?'advance':((($row['status']??'')==='admission_covered')?'admission':'');
                            @endphp
                            <span class="review-covered-chip {{ $coveredClass }}" title="{{ $row['label']??'Covered' }}{{ !empty($row['challan_no'])?' · #'.$row['challan_no']:'' }}">
                                {{ $displayMonth($row['month']??null) }}
                            </span>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    </div>
    @if($status==='approved' && $generatedChallans->isNotEmpty())
        <div class="card section-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="section-title">{{ __('Generated Challans') }}</div>
                    <div class="section-subtitle">
                        {{ __('These challans were generated on HO approval. Click a challan number to open it.') }}
                    </div>
                </div>

                @if(!empty($implementation['applied_at']))
                    <span class="implementation-badge implementation-applied">
                        {{ __('Implemented on first receipt') }}
                    </span>
                @else
                    <span class="implementation-badge implementation-pending">
                        {{ __('Pending first payment') }}
                    </span>
                @endif
            </div>

            <div class="card-body">
                <div class="generated-challan-grid">
                    @foreach($generatedChallans as $generatedChallan)
                        @php
                            $generatedDue=(float)($generatedChallan->total_amount??0)
                                -(float)($generatedChallan->concession_amount??0)
                                -(float)($generatedChallan->paid_amount??0);
                        @endphp

                        <div class="generated-challan-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="small text-muted">{{ $generatedChallan->challan_type??__('Challan') }}</div>
                                    <a href="{{ route('challan.legacy_show',$generatedChallan->id) }}" target="_blank"
                                       class="generated-challan-no text-primary"
                                       title="{{ __('Open Challan') }}">
                                        #{{ $generatedChallan->challanNo }}
                                        <i class="ti ti-external-link ms-1"></i>
                                    </a>
                                </div>

                                <span class="badge {{ strtolower((string)$generatedChallan->status)==='paid'?'bg-success':'bg-light text-dark border' }}">
                                    {{ $generatedChallan->status??'-' }}
                                </span>
                            </div>

                            <div class="generated-challan-meta">
                                <span>{{ __('Fee Month') }}: <strong>{{ $displayMonth($generatedChallan->fee_month) }}</strong></span>
                                <span>•</span>
                                <span>{{ __('Amount') }}: <strong>{{ number_format((float)($generatedChallan->total_amount??0),2) }}</strong></span>
                                <span>•</span>
                                <span>{{ __('Paid') }}: <strong>{{ number_format((float)($generatedChallan->paid_amount??0),2) }}</strong></span>
                                <span>•</span>
                                <span>{{ __('Due') }}: <strong>{{ number_format(max(0,$generatedDue),2) }}</strong></span>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(!empty($implementation['applied_at']))
                    <div class="small text-success mt-3">
                        <i class="ti ti-circle-check me-1"></i>
                        {{ __('Student implementation was triggered by receipt') }}
                        #{{ $implementation['receipt_id']??'-' }}
                        {{ __('on') }}
                        {{ $displayDate($implementation['receipt_date']??$implementation['applied_at']) }}.
                    </div>
                @else
                    <div class="small text-warning mt-3">
                        <i class="ti ti-clock me-1"></i>
                        {{ __('Student placement, enrollment, fee structure and history will be applied on the first positive receipt of the main challan.') }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    <div class="card section-card mb-3"><div class="card-header"><div class="section-title">{{ __('Previous Unpaid Challans') }}</div></div><div class="card-body p-0"><div class="table-responsive"><table class="table review-table"><thead><tr><th>#</th><th>{{ __('Challan No') }}</th><th>{{ __('Type') }}</th><th>{{ __('Fee Month') }}</th><th>{{ __('Other Months') }}</th><th>{{ __('Issue') }}</th><th>{{ __('Due') }}</th><th class="text-end">{{ __('Payable') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>@if(count($unpaidChallans)>0)@foreach($unpaidChallans as $i=>$c)<tr><td>{{ $i+1 }}</td><td>#{{ $c['challan_no']??'-' }}</td><td>{{ $c['challan_type']??'-' }}</td><td>{{ $displayMonth($c['fee_month']??null) }}</td><td>{{ $displayMonthList($c['other_months']??null) }}</td><td>{{ $displayDate($c['issue_date']??null) }}</td><td>{{ $displayDate($c['due_date']??null) }}</td><td class="amount">{{ number_format((float)($c['payable_amount']??0),2) }}</td><td><span class="badge bg-warning text-dark">{{ $c['status']??'-' }}</span></td></tr>@endforeach @else<tr><td colspan="9" class="text-center text-success py-4">{{ __('No previous unpaid challans.') }}</td></tr>@endif</tbody></table></div></div></div>

    <div class="row g-3 mb-3"><div class="col-lg-6"><div class="card section-card h-100"><div class="card-header"><div class="section-title">{{ __('Active Concession Policy') }}</div></div><div class="card-body">@if(!empty($policy['has_policy']))<strong>{{ $policy['policy_title']??'-' }}</strong><div class="small text-muted mt-1">{{ $policy['status']??'-' }} · {{ $displayDate($policy['start_date']??null) }} - {{ $displayDate($policy['end_date']??null) }}</div>@else<span class="text-muted">{{ __('No active policy at snapshot time.') }}</span>@endif</div></div></div><div class="col-lg-6"><div class="card section-card h-100"><div class="card-header"><div class="section-title">{{ __('Latest Policy Application') }}</div></div><div class="card-body">@if(!empty($latestPolicy['exists']))<strong>{{ $latestPolicy['policy_title']??'-' }}</strong><div class="small text-muted mt-1">{{ $latestPolicy['status']??'-' }} · {{ $displayDate($latestPolicy['start_date']??null) }} - {{ $displayDate($latestPolicy['end_date']??null) }}</div>@else<span class="text-muted">{{ __('No policy application.') }}</span>@endif</div></div></div></div>

    @if($editable)
        <div class="card decision-card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <strong>{{ __('Company Decision') }}</strong>
                    <div class="small text-muted">
                        {{ __('Save keeps the application pending. Approve & Generate saves these exact edits and immediately runs approval.') }}
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="button"
                            class="btn reject-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#rejectModal">
                        {{ __('Reject') }}
                    </button>

                    <button type="button"
                            class="btn save-review-btn"
                            id="save_review_changes">
                        {{ __('Save Review Changes') }}
                    </button>

                    <button type="button"
                            class="btn approve-btn"
                            id="approve_after_save">
                        {{ __('Approve & Generate') }}
                    </button>
                </div>
            </div>
        </div>
        </form>
    @endif
</div>

@if($editable)
<div class="modal fade" id="rejectModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST" action="{{ route('readmissionstudent.reject',$record->id) }}">@csrf<div class="modal-header"><h5 class="modal-title">{{ __('Reject Application') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">{{ __('Rejection Reason') }} *</label><textarea name="rejection_reason" class="form-control" rows="4" required></textarea></div><div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button type="submit" class="btn reject-btn">{{ __('Confirm Rejection') }}</button></div></form></div></div></div>
@endif
@endsection

@push('script-page')
<script>
(function(){
    'use strict';

    const editable={{ $editable?'true':'false' }};
    if(!editable)return;

    const NS='.readmissionReview';
    const csrf=@json(csrf_token());
    const previewUrl=@json(route('readmissionstudent.preview'));
    const branchClassUrl=@json(route('branch.class'));
    const sectionsUrl=@json(url('/get-sections'));
    const currentSaveUrl=@json(route('readmissionstudent.current-fee-structure',$record->id));

    const studentId=@json($student['reg_id']??null);
    const sourceBranch=@json($source['branch_id']??null);
    const sourceClass=@json($source['class_id']??null);
    const sourceSession=@json($source['session_id']??null);
    const sourceSection=@json($source['section_id']??null);

    let previewTimer=null;
    let previewXhr=null;
    let saveCurrentBusy=false;
    let formBusy=false;
    let reviewIncrementEffectiveTouched=false;

    function esc(v){
        return String(v??'')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#039;');
    }

    function formatMonthLabel(v){
        if(!v)return '-';

        const match=String(v).match(/^(\d{4})-(\d{2})/);

        if(!match){
            return String(v);
        }

        const names=[
            'January','February','March','April',
            'May','June','July','August',
            'September','October','November','December'
        ];

        return `${names[Number(match[2])-1]||match[2]} ${match[1]}`;
    }

    function rebuild($el,rows,placeholder,selected){
        $el.empty().append(`<option value="">${placeholder}</option>`);

        (rows||[]).forEach(function(row){
            $el.append(
                `<option value="${row.id}">${esc(row.name)}</option>`
            );
        });

        if(selected){
            $el.val(String(selected));
        }
    }

    function syncAll(sel,master){
        const x=$(sel+':not(:disabled)');
        $(master).prop(
            'checked',
            x.length>0 && x.filter(':checked').length===x.length
        );
    }

    function normalizeIncrement(){
        const $input=$('#review_tuition_increment_percentage');
        let value=String($input.val()??'0').replace(/[^\d]/g,'');

        if(value==='')value='0';

        value=String(Math.max(0,parseInt(value,10)||0));
        $input.val(value);

        return Number(value);
    }

    function sessionChanged(){
        return String(sourceSession||'')!==
            String($('#review_new_session').val()||'');
    }

    function syncRevisionVisibility(){
        const changed=sessionChanged();

        $('#review_tuition_revision_wrap').toggleClass(
            'd-none',
            !changed
        );

        if(!changed){
            $('#review_tuition_increment_percentage').val('0');
        }
    }

    function renderRevision(revision){
        revision=revision||{};

        $('#review_tuition_revision_wrap').toggleClass(
            'd-none',
            !revision.session_changed
        );

        if(!revision.session_changed)return;

        const baseLabel=revision.base_source_label||'Existing Student Fee';

        const effectiveMonth=formatMonthLabel(
            revision.effective_from ||
            $('#review_tuition_increment_effective_from').val() ||
            $('#review_month_date').val()
        );

        $('#review_tuition_increment_preview').html(
            `<div class="info-label">${esc(revision.fee_head||'Tuition Fee')}</div>
             <div class="info-value">
                ${esc(baseLabel)}: ${Number(revision.prev_base_amount||0).toFixed(2)}
                <span class="mx-2">→</span>
                Revised: ${Number(revision.new_base_amount||0).toFixed(2)}
             </div>
             <div class="small text-primary mt-1">
                Effective from <strong>${esc(effectiveMonth)}</strong> onward.
                Months before this use the previous Tuition amount.
             </div>`
        );
    }

    function loadClasses(branch,selected){
        $.post(
            branchClassUrl,
            {_token:csrf,branch_id:branch},
            function(rows){
                rebuild(
                    $('#review_new_class'),
                    rows||[],
                    'Select Class',
                    selected
                );
            }
        );
    }

    function loadSections(cls,selected){
        $.get(
            sectionsUrl+'/'+cls,
            function(rows){
                rebuild(
                    $('#review_new_section'),
                    rows.sections||rows||[],
                    'Select Section',
                    selected
                );
            }
        );
    }

    function renderTarget(rows,flowType,preservedIds){
        const body=$('#review_target_body').empty();

        (rows||[]).forEach(function(x,i){
            /*
             * This editable Review JS runs only for Company/HO.
             * Company has NO disabled/forced target head.
             */
            const disabled=false;
            const backendChecked=Number(x.checked||0)===1;

            const checked=Array.isArray(preservedIds)
                ? preservedIds.includes(Number(x.head_id))
                : backendChecked;

            const existing=
                x.existing_amount===null ||
                x.existing_amount===undefined
                    ? '-'
                    : Number(x.existing_amount||0).toFixed(2);

            const classRef=
                x.class_reference_amount===null ||
                x.class_reference_amount===undefined
                    ? '-'
                    : Number(x.class_reference_amount||0).toFixed(2);

            const charge=Number(
                x.charge_amount!==undefined
                    ? x.charge_amount
                    : x.class_amount||0
            ).toFixed(2);

            body.append(
                `<tr class="${checked?'selected-row':''}">
                    <td>
                        <input type="checkbox"
                               name="selected_heads[]"
                               class="form-check-input target-check"
                               value="${Number(x.head_id)}"
                               ${checked?'checked':''}
                               ${disabled?'disabled':''}>
                    </td>
                    <td>${i+1}</td>
                    <td>
                        <strong>${esc(x.fee_head||'-')}</strong>
                    </td>
                    <td class="amount">${existing}</td>
                    <td class="amount">${classRef}</td>
                    <td class="amount"><strong>${charge}</strong></td>
                    <td><span class="badge bg-light text-dark border">${esc(x.charge_source_label||'Class Fee Structure')}</span></td>
                    <td class="amount">${Number(x.discount||0).toFixed(2)}</td>
                    <td class="amount">${Number(x.payable_amount||0).toFixed(2)}</td>
                </tr>`
            );
        });

        syncAll('.target-check','#review_check_all_target');
    }

    function scheduleRefresh(delay){
        if(previewTimer){
            clearTimeout(previewTimer);
        }

        previewTimer=setTimeout(function(){
            previewTimer=null;
            refreshTarget();
        },Number(delay||180));
    }

    function refreshTarget(){
        const previousFlow=String($('#review_flow_type').val()||'');
        const preservedTargetIds=$('#review_target_body .target-check').length
            ? $('.target-check:checked').map(function(){return Number(this.value);}).get()
            : null;

        const data={
            _token:csrf,
            branch_id:sourceBranch,
            class_id:sourceClass,
            session_id:sourceSession,
            student_id:studentId,
            flow_type:$('#review_flow_type').val(),
            month_date:$('#review_month_date').val(),
            new_branch_id:$('#review_new_branch').val(),
            new_class_id:$('#review_new_class').val(),
            new_session_id:$('#review_new_session').val(),
            new_section_id:$('#review_new_section').val(),
            readmission_structure_source:$('#review_readmission_structure_source').val()||'student',
            tuition_increment_enabled:$('#review_tuition_increment_enabled').is(':checked')?1:0,
            tuition_increment_percentage:normalizeIncrement(),
            tuition_increment_effective_from:$('#review_tuition_increment_effective_from').val()||$('#review_month_date').val()
        };

        if(
            !data.new_branch_id ||
            !data.new_class_id ||
            !data.new_session_id ||
            !data.new_section_id
        ){
            return;
        }

        if(previewXhr&&previewXhr.readyState!==4){
            previewXhr.abort();
        }

        previewXhr=$.post(previewUrl,data)
            .done(function(resp){
                const returnedFlow=String(resp.flow_type||data.flow_type||'');

                renderTarget(
                    resp.target_fee_structure||[],
                    returnedFlow,
                    previousFlow===returnedFlow
                        ? preservedTargetIds
                        : null
                );

                renderRevision(resp.fee_revision||{});
            })
            .always(function(){
                previewXhr=null;
            });
    }

    $(document).off(NS);

    $(document).on('change'+NS,'#review_use_class_tuition_structure',function(){
        $('#review_readmission_structure_source').val(
            $(this).is(':checked') ? 'class' : 'student'
        );

        syncRevisionVisibility();
        scheduleRefresh(50);
    });

    $(document).on('change'+NS,'#review_check_all_existing',function(){
        $('.current-check:not(:disabled)').prop('checked',this.checked);
    });

    $(document).on('change'+NS,'.current-check',function(){
        syncAll('.current-check','#review_check_all_existing');
    });

    $(document).on('change'+NS,'#review_check_all_target',function(){
        $('.target-check:not(:disabled)')
            .prop('checked',this.checked)
            .trigger('change');
    });

    $(document).on('change'+NS,'.target-check',function(){
        $(this).closest('tr').toggleClass(
            'selected-row',
            this.checked
        );

        syncAll('.target-check','#review_check_all_target');
    });

    $(document).on('change'+NS,'#review_check_all_gap',function(){
        $('.gap-check:not(:disabled)').prop('checked',this.checked);
    });

    $(document).on('change'+NS,'.gap-check',function(){
        syncAll('.gap-check','#review_check_all_gap');
    });

    $(document).on('click'+NS,'#save_current_structure',function(e){
        e.preventDefault();

        if(saveCurrentBusy)return false;
        saveCurrentBusy=true;

        const btn=$(this);
        const original=btn.html();

        const ids=$('.current-check:checked')
            .map(function(){
                return Number(this.value);
            })
            .get();

        btn.prop('disabled',true).html('Saving...');

        $.post(
            currentSaveUrl,
            {
                _token:csrf,
                selected_heads:ids
            },
            function(resp){
                if(typeof show_toastr==='function'){
                    show_toastr(
                        resp.success?'Success':'Error',
                        resp.message,
                        resp.success?'success':'error'
                    );
                }else{
                    alert(resp.message);
                }
            }
        )
        .fail(function(xhr){
            alert(
                xhr.responseJSON&&xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Unable to save current structure.'
            );
        })
        .always(function(){
            saveCurrentBusy=false;
            btn.prop('disabled',false).html(original);
        });
    });

    $(document).on('change'+NS,'#review_new_branch',function(){
        loadClasses(this.value);
        rebuild($('#review_new_section'),[],'Select Section');
    });

    $(document).on('change'+NS,'#review_new_class',function(){
        loadSections(this.value);
        scheduleRefresh(220);
    });

    $(document).on(
        'change'+NS,
        '#review_new_session,#review_flow_type,#review_new_section',
        function(){
            const isReadmission=$('#review_flow_type').val()==='readmission';
            $('#review_structure_source_wrap').toggleClass('d-none',!isReadmission);
            if(!isReadmission){
                $('#review_use_class_tuition_structure').prop('checked',false);
                $('#review_readmission_structure_source').val('student');
            }
            syncRevisionVisibility();
            scheduleRefresh(180);
        }
    );

    $(document).on(
        'change'+NS,
        '#review_month_date',
        function(){
            if(!reviewIncrementEffectiveTouched){
                $('#review_tuition_increment_effective_from').val(this.value||'');
            }
            scheduleRefresh(180);
        }
    );

    $(document).on(
        'change'+NS,
        '#review_tuition_increment_effective_from',
        function(){
            reviewIncrementEffectiveTouched=true;
            scheduleRefresh(180);
        }
    );

    $(document).on(
        'input'+NS,
        '#review_tuition_increment_percentage',
        function(){
            normalizeIncrement();
            scheduleRefresh(320);
        }
    );

    $(document).on(
        'change'+NS,
        '#review_tuition_increment_enabled',
        function(){
            $('#review_tuition_increment_percentage')
                .prop('disabled',!this.checked);

            scheduleRefresh(180);
        }
    );

    function submitCompanyReview(approveAfterSave){
        const form=document.getElementById('companyReviewForm');

        if(!form||formBusy){
            return false;
        }

        /*
         * Set the requested action in a hidden input BEFORE any button is
         * disabled. This value is always included in the POST request.
         *
         * 0 = Save Review Changes
         * 1 = Approve & Generate
         */
        $('#review_approve_after_save').val(
            approveAfterSave ? '1' : '0'
        );

        if(approveAfterSave){
            const confirmed=window.confirm(
                'Approve this application? Your current review edits will be saved and then challans/enrollment will be applied.'
            );

            if(!confirmed){
                $('#review_approve_after_save').val('0');
                return false;
            }
        }

        /*
         * requestSubmit() preserves browser required-field validation and
         * runs the single guarded submit handler below.
         */
        form.requestSubmit();

        return true;
    }

    $(document)
        .off('click'+NS,'#save_review_changes')
        .on('click'+NS,'#save_review_changes',function(e){
            e.preventDefault();
            submitCompanyReview(false);
        });

    $(document)
        .off('click'+NS,'#approve_after_save')
        .on('click'+NS,'#approve_after_save',function(e){
            e.preventDefault();
            submitCompanyReview(true);
        });

    $('#companyReviewForm')
        .off('submit'+NS)
        .on('submit'+NS,function(e){
            if(formBusy){
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            /*
             * The action is already safely stored in the hidden field.
             * We can now disable all action buttons without losing whether
             * this request is SAVE or APPROVE.
             */
            formBusy=true;

            $('#save_review_changes,#approve_after_save')
                .prop('disabled',true);

            $(this)
                .find('button[type="submit"]')
                .prop('disabled',true);

            return true;
        });

    syncAll('.current-check','#review_check_all_existing');
    syncAll('.target-check','#review_check_all_target');
    syncAll('.gap-check','#review_check_all_gap');
    syncRevisionVisibility();
})();
</script>
@endpush
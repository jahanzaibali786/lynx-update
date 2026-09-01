@php
    $old = is_array($previousSnapshot ?? null) ? $previousSnapshot : [];
    $new = is_array($newSnapshot ?? null) ? $newSnapshot : [];
    $oldDetail = is_array($old['salary_detail'] ?? null) ? $old['salary_detail'] : [];
    $newDetail = is_array($new['salary_detail'] ?? null) ? $new['salary_detail'] : [];

    $money = fn($value) => number_format((float)($value ?? 0), 2);
    $displayDate = function ($value) {
        if (empty($value)) return '-';
        try { return \Carbon\Carbon::parse($value)->format('d-M-Y'); }
        catch (\Throwable $e) { return (string)$value; }
    };

    $statusMap = [0 => 'Draft', 1 => 'Pending Approval', 2 => 'Approved', 3 => 'Rejected'];
    $statusLabel = $statusMap[(int)($salaryProposal->status ?? 0)] ?? 'Unknown';
    $statusClass = (int)($salaryProposal->status ?? 0) === 2 ? 'sp-badge-success' : ((int)($salaryProposal->status ?? 0) === 3 ? 'sp-badge-danger' : 'sp-badge-warning');

    $employeeName = $new['employee_name'] ?? $old['employee_name'] ?? optional($employee)->name ?? '-';
    $employeeNo = $new['employee_no'] ?? $old['employee_no'] ?? $salaryProposal->emp_no ?? '-';
    $department = $new['department_name'] ?? $old['department_name'] ?? optional(optional($employee)->department)->name ?? '-';
    $designation = $new['designation_name'] ?? $old['designation_name'] ?? optional(optional($employee)->designation)->name ?? '-';
    $branch = optional(optional($employee)->userbranch)->name ?? '-';
@endphp

<div class="modal-body salary-proposal-shared-show">
    <style>
        .salary-proposal-shared-show{--sp-border:#e5e7eb;--sp-muted:#64748b;--sp-text:#1e293b;background:#f8fafc;padding:.85rem}
        .salary-proposal-shared-show .sp-shell{display:flex;flex-direction:column;gap:.75rem}
        .salary-proposal-shared-show .sp-panel{background:#fff;border:1px solid var(--sp-border);border-radius:12px;box-shadow:0 2px 8px rgba(15,23,42,.04);overflow:hidden}
        .salary-proposal-shared-show .sp-head{padding:.65rem .8rem;border-bottom:1px solid var(--sp-border);display:flex;align-items:center;justify-content:space-between;gap:.6rem;background:#eef2ff}
        .salary-proposal-shared-show .sp-head.info{background:#f0f9ff}.salary-proposal-shared-show .sp-head.success{background:#ecfdf5}.salary-proposal-shared-show .sp-head.warning{background:#fff7ed}
        .salary-proposal-shared-show .sp-title{font-size:1rem;font-weight:800;color:var(--sp-text);margin:0}.salary-proposal-shared-show .sp-sub{font-size:.76rem;color:var(--sp-muted);margin-top:.08rem}
        .salary-proposal-shared-show .sp-badge{padding:.28rem .55rem;border-radius:999px;font-size:.76rem;font-weight:800;white-space:nowrap}.salary-proposal-shared-show .sp-badge-warning{background:#ffedd5;color:#9a3412}.salary-proposal-shared-show .sp-badge-success{background:#d1fae5;color:#065f46}.salary-proposal-shared-show .sp-badge-danger{background:#fee2e2;color:#991b1b}
        .salary-proposal-shared-show .sp-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr))}.salary-proposal-shared-show .sp-item{padding:.7rem .8rem;border-right:1px solid var(--sp-border);border-bottom:1px solid var(--sp-border);min-height:66px}.salary-proposal-shared-show .sp-item:nth-child(4n){border-right:0}
        .salary-proposal-shared-show .sp-label{display:block;font-size:.7rem;font-weight:800;color:var(--sp-muted);text-transform:uppercase;letter-spacing:.035em;margin-bottom:.12rem}.salary-proposal-shared-show .sp-value{font-size:.9rem;font-weight:700;color:var(--sp-text);word-break:break-word}
        .salary-proposal-shared-show .sp-section-body{padding:.8rem}.salary-proposal-shared-show .table{margin-bottom:0;font-size:.88rem}.salary-proposal-shared-show .table th{font-size:.75rem;text-transform:uppercase;color:#475569;background:#f8fafc;white-space:nowrap}.salary-proposal-shared-show .table td,.salary-proposal-shared-show .table th{padding:.48rem .55rem;vertical-align:middle}.salary-proposal-shared-show .sp-money{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
        .salary-proposal-shared-show .sp-total-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem}.salary-proposal-shared-show .sp-total{border:1px solid var(--sp-border);border-radius:9px;padding:.6rem;background:#fff}.salary-proposal-shared-show .sp-total strong{display:block;font-size:1rem;margin-top:.15rem}
        .salary-proposal-shared-show .sp-tabs{display:flex;gap:.35rem;padding:.55rem .6rem 0;background:#fff;border-bottom:1px solid var(--sp-border);flex-wrap:wrap}
        .salary-proposal-shared-show .sp-tab-btn{border:0;background:transparent;color:var(--sp-muted);font-size:.86rem;font-weight:800;padding:.5rem .7rem;border-radius:8px 8px 0 0;cursor:pointer}
        .salary-proposal-shared-show .sp-tab-btn.active[data-target="structure"]{background:#f0f9ff;color:#0369a1}
        .salary-proposal-shared-show .sp-tab-btn.active[data-target="additions"]{background:#ecfdf5;color:#047857}
        .salary-proposal-shared-show .sp-tab-btn.active[data-target="deductions"]{background:#fff7ed;color:#b45309}
        .salary-proposal-shared-show .sp-tab-btn.active[data-target="accounting"]{background:#f8fafc;color:#334155;border:1px solid #cbd5e1;border-bottom:0}
        .salary-proposal-shared-show .sp-tab-pane{display:none;background:#fff}
        .salary-proposal-shared-show .sp-tab-pane.active{display:block}

        .salary-proposal-shared-show .sp-action-panel{background:#fff;border:1px solid var(--sp-border);border-radius:10px;padding:.75rem .85rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap}
        .salary-proposal-shared-show .sp-action-copy{min-width:220px}.salary-proposal-shared-show .sp-action-title{font-size:.9rem;font-weight:800;color:var(--sp-text);margin:0}.salary-proposal-shared-show .sp-action-sub{font-size:.76rem;color:var(--sp-muted);margin-top:.12rem}
        .salary-proposal-shared-show .sp-action-buttons{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap}.salary-proposal-shared-show .sp-action-buttons form{margin:0}.salary-proposal-shared-show .sp-action-buttons .btn{min-width:92px}
        .salary-proposal-shared-show .sp-accounting-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem;padding:.85rem}.salary-proposal-shared-show .sp-accounting-field label{display:block;font-size:.74rem;font-weight:800;color:#475569;margin-bottom:.3rem}.salary-proposal-shared-show .sp-accounting-field select{width:100%;min-height:36px}.salary-proposal-shared-show .sp-accounting-note{padding:.55rem .75rem;margin:.8rem .85rem 0;border:1px solid #dbe3ec;background:#f8fafc;border-radius:8px;font-size:.78rem;color:#475569}
        @media(max-width:900px){.salary-proposal-shared-show .sp-grid,.salary-proposal-shared-show .sp-total-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.salary-proposal-shared-show .sp-item:nth-child(4n){border-right:1px solid var(--sp-border)}.salary-proposal-shared-show .sp-item:nth-child(2n){border-right:0}}
        @media(max-width:575px){.salary-proposal-shared-show .sp-grid,.salary-proposal-shared-show .sp-total-grid,.salary-proposal-shared-show .sp-accounting-grid{grid-template-columns:1fr}.salary-proposal-shared-show .sp-item{border-right:0!important}}
    </style>

    <div class="sp-shell">
        <div class="sp-panel">
            <div class="sp-head">
                <div>
                    <h6 class="sp-title">Employee Basic Details</h6>
                    <div class="sp-sub">Employee and proposal identification</div>
                </div>
                <span class="sp-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <div class="sp-grid">
                <div class="sp-item"><span class="sp-label">Employee</span><div class="sp-value">{{ $employeeName }}</div></div>
                <div class="sp-item"><span class="sp-label">Employee No.</span><div class="sp-value">{{ $employeeNo }}</div></div>
                <div class="sp-item"><span class="sp-label">Branch</span><div class="sp-value">{{ $branch }}</div></div>
                <div class="sp-item"><span class="sp-label">Department</span><div class="sp-value">{{ $department }}</div></div>
                <div class="sp-item"><span class="sp-label">Designation</span><div class="sp-value">{{ $designation }}</div></div>
                <div class="sp-item"><span class="sp-label">Effect From</span><div class="sp-value">{{ $displayDate($new['effect_from'] ?? $newDetail['effect_from'] ?? null) }}</div></div>
                <div class="sp-item"><span class="sp-label">Pay Method</span><div class="sp-value">{{ $newDetail['paymode'] ?? '-' }}</div></div>
                <div class="sp-item"><span class="sp-label">Bank A/C</span><div class="sp-value">{{ $newDetail['account_number'] ?? $salaryProposal->bank_account ?? '-' }}</div></div>
            </div>
        </div>

        <div class="sp-panel sp-tabs-panel">
            <div class="sp-tabs">
                <button type="button" class="sp-tab-btn active" data-target="structure">Salary Structure</button>
                <button type="button" class="sp-tab-btn" data-target="additions">Additions</button>
                <button type="button" class="sp-tab-btn" data-target="deductions">Deductions & Contributions</button>
                @if(Auth::user()->type === 'company')
                    <button type="button" class="sp-tab-btn" data-target="accounting">Accounting Setup</button>
                @endif
            </div>

            <div class="sp-tab-pane active" data-pane="structure">
                <div class="sp-head info">
                    <div>
                        <h6 class="sp-title">Salary Structure</h6>
                        <div class="sp-sub">Current and proposed salary values saved with the application</div>
                    </div>
                </div>
                <div class="sp-section-body table-responsive">
                    <table class="table table-bordered">
                        <thead><tr><th>Item</th><th class="sp-money">Current</th><th class="sp-money">Proposed</th></tr></thead>
                        <tbody>
                            <tr><td>Payscale</td><td class="sp-money">{{ $old['scale_label'] ?? '-' }}</td><td class="sp-money fw-bold">{{ $new['scale_label'] ?? '-' }}</td></tr>
                            <tr><td>Gross Salary</td><td class="sp-money">{{ $money($old['gross'] ?? 0) }}</td><td class="sp-money fw-bold">{{ $money($new['gross'] ?? 0) }}</td></tr>
                            <tr><td>Employee Security</td><td class="sp-money">{{ $money($old['emp_security'] ?? 0) }}</td><td class="sp-money">{{ $money($new['emp_security'] ?? 0) }}</td></tr>
                            <tr><td>Income Tax</td><td class="sp-money">{{ $money($old['tax'] ?? 0) }}</td><td class="sp-money">{{ $money($new['tax'] ?? 0) }}</td></tr>
                            <tr><td>EOBI Employee</td><td class="sp-money">{{ $money($old['eobi_employee'] ?? 0) }}</td><td class="sp-money">{{ $money($new['eobi_employee'] ?? 0) }}</td></tr>
                            <tr><td>PESSI Employee</td><td class="sp-money">{{ $money($old['pessi_employee'] ?? 0) }}</td><td class="sp-money">{{ $money($new['pessi_employee'] ?? 0) }}</td></tr>
                            <tr><td>Net Salary</td><td class="sp-money">{{ $money($old['net_salary'] ?? 0) }}</td><td class="sp-money fw-bold">{{ $money($new['net_salary'] ?? 0) }}</td></tr>
                            <tr><td>Child Concession</td><td class="sp-money">{{ $money($old['child_concession'] ?? 0) }}</td><td class="sp-money">{{ $money($new['child_concession'] ?? 0) }}</td></tr>
                            <tr><td>EOBI Employer</td><td class="sp-money">{{ $money($old['eobi_employer'] ?? 0) }}</td><td class="sp-money">{{ $money($new['eobi_employer'] ?? 0) }}</td></tr>
                            <tr><td>PESSI Employer</td><td class="sp-money">{{ $money($old['pessi_employer'] ?? 0) }}</td><td class="sp-money">{{ $money($new['pessi_employer'] ?? 0) }}</td></tr>
                            <tr><td>Cost to Company</td><td class="sp-money">{{ $money($old['cost_to_company'] ?? 0) }}</td><td class="sp-money fw-bold">{{ $money($new['cost_to_company'] ?? 0) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="sp-tab-pane" data-pane="additions">
                <div class="sp-head success">
                    <div>
                        <h6 class="sp-title">Additions</h6>
                        <div class="sp-sub">Additional salary values entered by branch</div>
                    </div>
                </div>
                <div class="sp-grid">
                    <div class="sp-item"><span class="sp-label">DRNS</span><div class="sp-value">{{ $money($newDetail['drns'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">Other</span><div class="sp-value">{{ $money($newDetail['conv'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">Misc</span><div class="sp-value">{{ $money($newDetail['misc'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">Other Allowance</span><div class="sp-value">{{ $money($newDetail['other_add'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">Child Concession</span><div class="sp-value">{{ $money($newDetail['chaild_concession'] ?? $new['child_concession'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">Working Days</span><div class="sp-value">{{ $newDetail['working_days'] ?? 30 }}</div></div>
                </div>
            </div>

            <div class="sp-tab-pane" data-pane="deductions">
                <div class="sp-head warning">
                    <div>
                        <h6 class="sp-title">Deductions & Contributions</h6>
                        <div class="sp-sub">Exact values and percentages entered in the application</div>
                    </div>
                </div>
                <div class="sp-grid">
                    <div class="sp-item"><span class="sp-label">Emp Security %</span><div class="sp-value">{{ $money(($new['employee_percentages']['security'] ?? 0)) }}%</div></div>
                    <div class="sp-item"><span class="sp-label">Emp Security</span><div class="sp-value">{{ $money($newDetail['emp_sec'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">EOBI %</span><div class="sp-value">{{ $money(($new['employee_percentages']['eobi'] ?? 0)) }}%</div></div>
                    <div class="sp-item"><span class="sp-label">EOBI</span><div class="sp-value">{{ $money($newDetail['eobi'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">EOBI Employer %</span><div class="sp-value">{{ $money(($new['employee_percentages']['eobi_employer'] ?? 0)) }}%</div></div>
                    <div class="sp-item"><span class="sp-label">EOBI Employer</span><div class="sp-value">{{ $money($newDetail['eobi_employer'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">PESSI %</span><div class="sp-value">{{ $money(($new['employee_percentages']['pessi'] ?? 0)) }}%</div></div>
                    <div class="sp-item"><span class="sp-label">PESSI</span><div class="sp-value">{{ $money($newDetail['pessi'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">PESSI Employer %</span><div class="sp-value">{{ $money(($new['employee_percentages']['pessi_employer'] ?? 0)) }}%</div></div>
                    <div class="sp-item"><span class="sp-label">PESSI Employer</span><div class="sp-value">{{ $money($newDetail['pessi_employer'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">Income Tax</span><div class="sp-value">{{ $money($newDetail['itax'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">Other Deduction</span><div class="sp-value">{{ $money($newDetail['other_deduction'] ?? 0) }}</div></div>
                    <div class="sp-item"><span class="sp-label">Advance</span><div class="sp-value">{{ $money($newDetail['advance'] ?? 0) }}</div></div>
                </div>
            </div>

            @if(Auth::user()->type === 'company')
                <div class="sp-tab-pane" data-pane="accounting">
                    <div class="sp-head">
                        <div>
                            <h6 class="sp-title">Accounting Setup</h6>
                            <div class="sp-sub">These mappings will be applied when this salary proposal is approved.</div>
                        </div>
                    </div>

                    <div class="sp-accounting-note">
                        Existing salary-detail mappings are selected first. If an employee has no mapping yet, the configured default account is selected automatically.
                    </div>

                    <div class="sp-accounting-grid">
                        <div class="sp-accounting-field">
                            <label for="sp_account_id">Bank Account <span class="text-danger">*</span></label>
                            {{ Form::select('account_id', $accounts ?? [], $selectedAccounting['account_id'] ?? '', [
                                'id' => 'sp_account_id',
                                'class' => 'form-select form-select-sm',
                                'required' => 'required',
                                'form' => 'single-proposal-approval-form',
                                'disabled' => (int)($salaryProposal->status ?? 0) !== 1,
                            ]) }}
                        </div>

                        <div class="sp-accounting-field">
                            <label for="sp_security_receive_account">Security Payable Account <span class="text-danger">*</span></label>
                            {{ Form::select('security_receive_account', $payableaccounts ?? [], $selectedAccounting['security_receive_account'] ?? '', [
                                'id' => 'sp_security_receive_account','class' => 'form-select form-select-sm','required' => 'required','form' => 'single-proposal-approval-form','disabled' => (int)($salaryProposal->status ?? 0) !== 1,
                            ]) }}
                        </div>

                        <div class="sp-accounting-field">
                            <label for="sp_eobi_payable_account">EOBI Payable Account <span class="text-danger">*</span></label>
                            {{ Form::select('eobi_payable_account', $payableaccounts ?? [], $selectedAccounting['eobi_payable_account'] ?? '', [
                                'id' => 'sp_eobi_payable_account','class' => 'form-select form-select-sm','required' => 'required','form' => 'single-proposal-approval-form','disabled' => (int)($salaryProposal->status ?? 0) !== 1,
                            ]) }}
                        </div>

                        <div class="sp-accounting-field">
                            <label for="sp_pessi_payable_account">PESSI Payable Account <span class="text-danger">*</span></label>
                            {{ Form::select('pessi_payable_account', $payableaccounts ?? [], $selectedAccounting['pessi_payable_account'] ?? '', [
                                'id' => 'sp_pessi_payable_account','class' => 'form-select form-select-sm','required' => 'required','form' => 'single-proposal-approval-form','disabled' => (int)($salaryProposal->status ?? 0) !== 1,
                            ]) }}
                        </div>

                        <div class="sp-accounting-field">
                            <label for="sp_tax_payable_account">I.Tax Payable Account <span class="text-danger">*</span></label>
                            {{ Form::select('tax_payable_account', $payableaccounts ?? [], $selectedAccounting['tax_payable_account'] ?? '', [
                                'id' => 'sp_tax_payable_account','class' => 'form-select form-select-sm','required' => 'required','form' => 'single-proposal-approval-form','disabled' => (int)($salaryProposal->status ?? 0) !== 1,
                            ]) }}
                        </div>

                        <div class="sp-accounting-field">
                            <label for="sp_other_dedu_payable_account">Deduction Payable Account <span class="text-danger">*</span></label>
                            {{ Form::select('other_dedu_payable_account', $payableaccounts ?? [], $selectedAccounting['other_dedu_payable_account'] ?? '', [
                                'id' => 'sp_other_dedu_payable_account','class' => 'form-select form-select-sm','required' => 'required','form' => 'single-proposal-approval-form','disabled' => (int)($salaryProposal->status ?? 0) !== 1,
                            ]) }}
                        </div>

                        <div class="sp-accounting-field">
                            <label for="sp_advance_payable_account">Advance Payable Account <span class="text-danger">*</span></label>
                            {{ Form::select('advance_payable_account', $payableaccounts ?? [], $selectedAccounting['advance_payable_account'] ?? '', [
                                'id' => 'sp_advance_payable_account','class' => 'form-select form-select-sm','required' => 'required','form' => 'single-proposal-approval-form','disabled' => (int)($salaryProposal->status ?? 0) !== 1,
                            ]) }}
                        </div>

                        <div class="sp-accounting-field">
                            <label for="sp_net_payable_account">Net Payable Account <span class="text-danger">*</span></label>
                            {{ Form::select('net_payable_account', $payableaccounts ?? [], $selectedAccounting['net_payable_account'] ?? '', [
                                'id' => 'sp_net_payable_account','class' => 'form-select form-select-sm','required' => 'required','form' => 'single-proposal-approval-form','disabled' => (int)($salaryProposal->status ?? 0) !== 1,
                            ]) }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="sp-total-grid">
            <div class="sp-total"><span class="sp-label">Gross</span><strong>{{ $money($new['gross'] ?? 0) }}</strong></div>
            <div class="sp-total"><span class="sp-label">Net Salary</span><strong>{{ $money($new['net_salary'] ?? 0) }}</strong></div>
            <div class="sp-total"><span class="sp-label">Employer EOBI / PESSI</span><strong>{{ $money($new['eobi_employer'] ?? 0) }} / {{ $money($new['pessi_employer'] ?? 0) }}</strong></div>
            <div class="sp-total"><span class="sp-label">Cost to Company</span><strong>{{ $money($new['cost_to_company'] ?? 0) }}</strong></div>
        </div>


        @if(Auth::user()->type === 'company')
            <div class="sp-action-panel">
                <div class="sp-action-copy">
                    <div class="sp-action-title">Proposal Action</div>
                    <div class="sp-action-sub">Review this salary proposal and approve or reject it.</div>
                </div>

                <div class="sp-action-buttons">
                    @if((int)($salaryProposal->status ?? 0) === 1)
                        <form action="{{ route('employee-salary-proporal.rollback', $salaryProposal->id) }}" method="POST" class="sp-proposal-action-form" data-action="reject">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                        </form>

                        <form id="single-proposal-approval-form" action="{{ route('employee-salary-proporal.approve', $salaryProposal->id) }}" method="POST" class="sp-proposal-action-form" data-action="approve">
                            @csrf
                        </form>
                        <button type="submit" form="single-proposal-approval-form" class="btn btn-sm btn-primary">Approve</button>
                    @elseif((int)($salaryProposal->status ?? 0) === 2)
                        <span class="sp-badge sp-badge-success">Already Approved</span>
                    @elseif((int)($salaryProposal->status ?? 0) === 3)
                        <span class="sp-badge sp-badge-danger">Rejected</span>
                    @else
                        <span class="sp-badge sp-badge-warning">Draft - Awaiting Submission</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
(function($){
    'use strict';
    const root = '.salary-proposal-shared-show';
    $(document)
        .off('click.salaryProposalShowTabs', root + ' .sp-tab-btn')
        .on('click.salaryProposalShowTabs', root + ' .sp-tab-btn', function(){
            const $root = $(this).closest(root);
            const target = $(this).data('target');
            $root.find('.sp-tab-btn').removeClass('active');
            $(this).addClass('active');
            $root.find('.sp-tab-pane').removeClass('active');
            $root.find('.sp-tab-pane[data-pane="' + target + '"]').addClass('active');
        });


    $(document)
        .off('submit.salaryProposalSharedAction', root + ' .sp-proposal-action-form')
        .on('submit.salaryProposalSharedAction', root + ' .sp-proposal-action-form', function(e){
            const action = $(this).data('action');
            const message = action === 'approve'
                ? 'Approve this salary proposal and apply it to the employee salary details?'
                : 'Reject this salary proposal and return it to the branch?';

            if (!window.confirm(message)) {
                e.preventDefault();
            }
        });
})(jQuery);
</script>

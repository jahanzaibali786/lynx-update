{{ Form::open(['url' => 'employee-salary-detail', 'method' => 'post']) }}
<div class="modal-body">
    @php
        $hasPayscale = !empty($lastPayscaleDetail->pay_scale_id);
        $normalizedPaymode = strtolower(trim($lastPayscaleDetail->paymode ?? ''));
        $paymodes = [
            '' => 'Select One',
            'Bank Deposit HBL' => 'Bank Deposit HBL',
            'Bank Deposit AF' => 'Bank Deposit AF',
            'Demand Draft' => 'Demand Draft',
            'Cheque' => 'Cheque',
            'Bank' => 'Bank Deposite',
            'Cash' => 'Cash',
        ];
        $selectedPaymode = collect($paymodes)->keys()->first(fn($key) => strtolower($key) === $normalizedPaymode);
        // Joining Date for min effect_from
        $joiningDate = \Carbon\Carbon::parse($employee->company_doj)->format('Y-m-d');
    @endphp

    <div class="row">
        <div class="form-group col-md-4">
            {{ Form::label('paymode', __('Paymode'), ['class' => 'form-label']) }}
            {{ Form::select('paymode', $paymodes, $hasPayscale ? $selectedPaymode : '', ['class' => 'form-control select custom-select', 'required']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('account_number', __('Bank A/C'), ['class' => 'form-label']) }}
            {{ Form::hidden('employee_id', $employee->id) }}
            {{ Form::text('account_number', $hasPayscale ? $lastPayscaleDetail->account_number : '', ['class' => 'form-control', 'id' => 'account_number']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('accounts', __('Bank'), ['class' => 'form-label']) }}
            {{ Form::select('accounts', $accounts, $hasPayscale ? $lastPayscaleDetail->account_id : '', ['class' => 'form-control select custom-select', 'required']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
            {{ Form::select(
                'department_id',
                $departments,
                $hasPayscale ? $lastPayscaleDetail->department_id : $employee->department_id,
                [
                    'class' => 'form-control select custom-select',
                    'required',
                ],
            ) }}
            {{ Form::hidden('departmentid', $hasPayscale ? $lastPayscaleDetail->department_id : $employee->department_id, [
                'class' => 'form-control',
                'id' => 'departmentid',
            ]) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('pay_scale', __('Pay Scale'), ['class' => 'form-label']) }}
            {{ Form::select('pay_scale', $payscales, $hasPayscale ? $lastPayscaleDetail->pay_scale_id : '', [
                'id' => 'pay_scale',
                'class' => 'form-control select custom-select',
                'required' => 'required',
            ]) }}
        </div>
        <div class="form-group col-md-2">
            {{ Form::label('effect_from', __('Effect From'), ['class' => 'form-label']) }}
            {{ Form::date('effect_from', $hasPayscale ? $lastPayscaleDetail->effect_from : date('Y-m-d'), [
                'class' => 'form-control',
                'required',
                'id' => 'effect_from',
                'min' => $joiningDate,
                ]) }}
        </div>
        <div class="form-group col-md-2">
            {{ Form::label('company_doj_display', __('Date of Joining'), ['class' => 'form-label']) }}
            {{ Form::text('company_doj_display', !empty($employee->company_doj) ? \Carbon\Carbon::parse($employee->company_doj)->format('d-M-Y') : '', [
                'class' => 'form-control',
                'disabled' => 'disabled',
            ]) }}
        </div>
        

        {{-- Scale Heads & Gross --}}
        <div class="scale_heads_row row">
            @if ($hasPayscale)
                @php
                    $gross = 0;
                    $scaleData = \App\Models\EmployeeScale::with(
                        'employeeScaleHeads.SalaryHeads',
                        'employeepayScaledetailHeads',
                    )->find($lastPayscaleDetail->pay_scale_id);
                @endphp

                @foreach ($scaleData->employeeScaleHeads as $head)
                    @php
                        if ($head->SalaryHeads->head === 'Initial Basic') {
                            $basicValue = $head->head_value;
                        }
                        $gross += $head->head_value;
                    @endphp

                    <div class="form-group col-md-2 headsInput">
                        <label class="form-label">{{ $head->SalaryHeads->head }}</label>
                        <input type="number" name="scale_heads[{{ $head->id }}]" value="{{ $head->head_value }}"
                            class="form-control" readonly>
                    </div>
                @endforeach

                @php
                    // Add allowances
                    $gross +=
                        $lastPayscaleDetail->drns +
                        $lastPayscaleDetail->conv +
                        $lastPayscaleDetail->misc +
                        $lastPayscaleDetail->other_add;
                @endphp

                <div class="form-group col-md-4">
                    <label class="form-label">Gross</label>
                    <input type="text" name="gross" value="{{ $gross }}" class="form-control" readonly>
                    <input type="hidden" name="old_gross" value="{{ $gross }}">
                </div>
            @endif

            {{-- Working Days & Per Day Salary --}}
            <div class="form-group col-md-2">
                {{ Form::label('working_days', __('Working Days'), ['class' => 'form-label']) }}
                {{ Form::number('working_days', $hasPayscale ? $lastPayscaleDetail->working_days : '30', [
                    'class' => 'form-control working-days-input',
                    'id' => 'working_days',
                ]) }}
            </div>

            @php
                $perDay = $hasPayscale ? round($gross / 30, 2) : '';
            @endphp
            <div class="form-group col-md-2">
                {{ Form::label('per_day_salary', __('Per Day Salary'), ['class' => 'form-label']) }}
                {{ Form::text('per_day_salary', $perDay, ['class' => 'form-control', 'readonly', 'id' => 'per_day_salary']) }}
            </div>
        </div>

        {{-- Additions & Deductions & Net --}}
        <div class="form-group col-md-2">
            {{ Form::label('drns', __('Drns'), ['class' => 'form-label']) }}
            {{ Form::number('drns', $hasPayscale ? $lastPayscaleDetail->drns : '', ['class' => 'form-control addition-field']) }}
        </div>
        <div class="form-group col-md-2">
            {{ Form::label('conv', __('Other'), ['class' => 'form-label']) }}
            {{ Form::number('conv', $hasPayscale ? $lastPayscaleDetail->conv : '', ['class' => 'form-control addition-field']) }}
        </div>
        <div class="form-group col-md-2">
            {{ Form::label('misc', __('Misc'), ['class' => 'form-label']) }}
            {{ Form::number('misc', $hasPayscale ? $lastPayscaleDetail->misc : '', ['class' => 'form-control addition-field']) }}
        </div>
        <div class="form-group col-md-2">
            {{ Form::label('other_add', __('Other Allowance'), ['class' => 'form-label']) }}
            {{ Form::number('other_add', $hasPayscale ? $lastPayscaleDetail->other_add : '', ['class' => 'form-control addition-field']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('chaild_concession', __('Child Cons.'), ['class' => 'form-label']) }}
            {{ Form::number('chaild_concession', $hasPayscale ? $lastPayscaleDetail->chaild_concession : '', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('eobi_percentage', __('EOBI %'), ['class' => 'form-label']) !!}
            {{ Form::number('eobi_percentage', !empty($employee) ? @$employee->eobi : '', ['class' => 'form-control', 'id' => 'eobi_percentage']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('eobi', __('EOBI'), ['class' => 'form-label']) !!}
            {{ Form::number('eobi', !empty($lastPayscaleDetail) ? $eobiValue : $eobiValue, ['class' => 'form-control deduction-field', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('eobi_employer_percentage', __('EOBI Employer %'), ['class' => 'form-label']) !!}
            {{ Form::number('eobi_employer_percentage', !empty($employee) ? @$employee->eobi_employer : '', ['class' => 'form-control', 'id' => 'eobi_employer_percentage']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('eobi_employer', __('EOBI Employer'), ['class' => 'form-label']) !!}
            {{ Form::number('eobi_employer', !empty($lastPayscaleDetail) ? $eobiEmployerValue : $eobiEmployerValue, ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('eobi_payable_account', __('EOBI Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::select('eobi_payable_account', $payableaccounts, !empty($lastPayscaleDetail) ? $lastPayscaleDetail->eobi_payable_account : '', ['class' => 'form-control select custom-select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('pessi_percentage', __('PESSI %'), ['class' => 'form-label']) !!}
            {{ Form::number('pessi_percentage', !empty($employee) ? @$employee->pessi : '', ['class' => 'form-control', 'id' => 'pessi_percentage']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('pessi', __('PESSI'), ['class' => 'form-label']) !!}
            {{ Form::number('pessi', !empty($lastPayscaleDetail) ? $pessiValue : $pessiValue, ['class' => 'form-control deduction-field', 'required' => 'required', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('pessi_employer_percentage', __('PESSI Employer %'), ['class' => 'form-label']) !!}
            {{ Form::number('pessi_employer_percentage', !empty($employee) ? @$employee->pessi_employer : '', ['class' => 'form-control', 'id' => 'pessi_employer_percentage']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('pessi_employer', __('PESSI Employer'), ['class' => 'form-label']) !!}
            {{ Form::number('pessi_employer', !empty($lastPayscaleDetail) ? $pessiEmployerValue : $pessiEmployerValue, ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('pessi_payable_account', __('PESSI Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::select('pessi_payable_account', $payableaccounts, !empty($lastPayscaleDetail) ? $lastPayscaleDetail->pessi_payable_account : '', ['class' => 'form-control select custom-select', 'required' => 'required']) }}
        </div>
        {{-- //calculate tax button --}}
        <div class="form-group col-md-2">
            <button type="button" class="btn btn-primary calculate-tax-btn"
                style="position: relative; top:15px;">Calculate Tax</button>
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('itax', __('I.Tax'), ['class' => 'form-label']) !!}
            {{ Form::number('itax', !empty($lastPayscaleDetail) ? $lastPayscaleDetail->itax : '', ['class' => 'form-control deduction-field', 'id' => 'itax']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('totaltax', __('Total Tax'), ['class' => 'form-label']) !!}
            {{ Form::number('totaltax', !empty($lastPayscaleDetail) ? $lastPayscaleDetail->totaltax : '', ['class' => 'form-control', 'id' => 'totaltax', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('prevtax', __('Prev. Tax'), ['class' => 'form-label']) !!}
            {{ Form::number('prevtax', !empty($lastPayscaleDetail) ? $lastPayscaleDetail->prevtax : '', ['class' => 'form-control', 'id' => 'prevtax', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('salary_tax_received', __('Salary Tax'), ['class' => 'form-label']) !!}
            {{ Form::number('salary_tax_received', '', ['class' => 'form-control', 'id' => 'salary_tax_received', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('advance_tax_collection', __('Advance Tax'), ['class' => 'form-label']) !!}
            {{ Form::number('advance_tax_collection', '', ['class' => 'form-control', 'id' => 'advance_tax_collection', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('tax_payable_account', __('I.Tax Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::select('tax_payable_account', $payableaccounts, !empty($lastPayscaleDetail) ? $lastPayscaleDetail->tax_payable_account : '', ['class' => 'form-control select custom-select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('emp_sec_percentage', __('Emp Sec %'), ['class' => 'form-label']) !!}
            {{ Form::number('emp_sec_percentage', !empty($employee) ? @$employee->security : '', ['class' => 'form-control', 'id' => 'security_percentage']) }}
        </div>
        <div class="form-group col-md-2">
            {!! Form::label('emp_sec', __('Emp Sec.'), ['class' => 'form-label']) !!}
            {{ Form::number('emp_sec', !empty($lastPayscaleDetail) ? $lastPayscaleDetail->emp_sec : '0', ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly', 'id' => 'emp_sec']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('security_receive_account', __('Security Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::select('security_receive_account', $payableaccounts, !empty($lastPayscaleDetail) ? $lastPayscaleDetail->security_receive_account : '', ['class' => 'form-control select custom-select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('other_deduction', __('Other Deduction'), ['class' => 'form-label']) !!}
            {{ Form::number('other_deduction', !empty($lastPayscaleDetail) ? $lastPayscaleDetail->other_deduction : '', ['class' => 'form-control deduction-field']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('other_dedu_payable_account', __('Deduction Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::select('other_dedu_payable_account', $payableaccounts, !empty($lastPayscaleDetail) ? $lastPayscaleDetail->other_dedu_payable_account : '', ['required' => 'required', 'class' => 'form-control select custom-select']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('advance', __('Advance'), ['class' => 'form-label']) !!}
            {{ Form::number('advance', !empty($lastPayscaleDetail) ? $lastPayscaleDetail->advance : '', ['class' => 'form-control deduction-field']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('advance_payable_account', __('Advance Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::select('advance_payable_account', $payableaccounts, !empty($lastPayscaleDetail) ? $lastPayscaleDetail->advance_payable_account : '', ['required' => 'required', 'class' => 'form-control select custom-select']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('net', __('Net'), ['class' => 'form-label']) !!}
            {{ Form::number('net', !empty($lastPayscaleDetail) ? $lastPayscaleDetail->net : '0', ['class' => 'form-control', 'id' => 'net']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('net_payable_account', __('Net Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::select('net_payable_account', $payableaccounts, !empty($lastPayscaleDetail) ? $lastPayscaleDetail->net_payable_account : '', ['required' => 'required', 'class' => 'form-control select custom-select', 'readonly' => 'readonly']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    @if (Auth::user()->type == 'company')
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Save') }}" class="btn  btn-outline-primary">
    @endif
</div>
{{-- //sawl cdn  --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Global variables
    var companySetEobi = {{ $companySetEobi ?? 0 }};
    var companySetPessi = {{ $companySetPessi ?? 0 }};
    var companySetEobiEmployer = {{ $companySetEobiEmployer ?? 0 }};
    var companySetPessiEmployer = {{ $companySetPessiEmployer ?? 0 }};
    var basic_head_value = {{ $basic_head_value ?? 0 }};


    $(document).ready(function() {
        initializeSalarySelectSearch();
        toggleBankAccountRequirement();

        // Initialize event handlers once
        initializeEventHandlers();

        $(document).off('shown.bs.modal.salaryFormModal').on('shown.bs.modal.salaryFormModal', '.modal', function() {
            if (!$(this).find('input[name="employee_id"]').length) {
                return;
            }

            initializeSalarySelectSearch(this);
            toggleBankAccountRequirement(this);

            if ($(this).data('salaryScaleHeadsLoaded')) {
                return;
            }

            $(this).data('salaryScaleHeadsLoaded', true);
            payscalheads({{ $lastPayscaleDetail->pay_scale_id ?? '' }});
        });

        $(document).off('hidden.bs.modal.salaryFormModal').on('hidden.bs.modal.salaryFormModal', '.modal', function() {
            $(this).off('.salaryForm');
        });
    });

    function initializeSalarySelectSearch(scope) {
        const activeModal = getCurrentModal();
        const container = scope ? $(scope) : (activeModal.is(document) ? $('select[name="paymode"]').last().closest('form') : activeModal);

        if (!container.length) {
            return;
        }

        container.find('select').each(function() {
            const select = $(this);

            if ($.fn.select2 && select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }

            if (this.customSelectInstance) {
                try {
                    this.customSelectInstance.destroy();
                } catch (e) {}
                delete this.customSelectInstance;
            }

            if (select.next('.custom-select-wrapper').length) {
                select.next('.custom-select-wrapper').remove();
            }

            select.removeClass('js-searchBox custom-search').addClass('select custom-select').show();

            if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                this.customSelectInstance = window.CustomSelect.create(this);
            }
        });
    }

    function toggleBankAccountRequirement(scope) {
        const container = scope ? $(scope) : $(document);
        const paymode = (container.find('select[name="paymode"]').val() || '').trim();
        const isBankPaymode = ['Bank Deposit HBL', 'Bank Deposit AF', 'Bank'].includes(paymode);
        const accountInput = container.find('input[name="account_number"]');

        accountInput.prop('required', isBankPaymode);
        accountInput.closest('.form-group').find('.bank-ac-required-mark').remove();

        if (isBankPaymode) {
            accountInput.closest('.form-group').find('label').append('<span class="text-danger bank-ac-required-mark"> *</span>');
            accountInput.prop('readonly', false).removeClass('bg-light');
        } else {
            accountInput.prop('readonly', false).removeClass('is-invalid');
        }
    }

    // Main initialization function
    function initializeEventHandlers() {

        // Remove existing handlers to prevent duplicates
        $(document).off('.salaryFormGlobal');


        // Pay scale change handler
        $(document).on('change.salaryFormGlobal', '#pay_scale', function() {
            const newScaleId = $(this).val();
            if (newScaleId && newScaleId !== '') {
                payscalheads(newScaleId);
            } else {
                clearPayscaleData();
            }
        });

        // Department change handler
        $(document).on('change.salaryFormGlobal', 'select[name="department_id"]', function() {
            const departmentId = $(this).val();
            departmentfunc(departmentId);
            getDeptWiseScales();
        });

        // Working days change
        $(document).on('input.salaryFormGlobal', '.working-days-input', function() {
            calculatePerDaySalary();
        });

        // Addition fields change
        $(document).on('input.salaryFormGlobal', '.addition-field', function() {
            updateGross();
        });

        // Deduction fields change
        $(document).on('input.salaryFormGlobal', '.deduction-field', function() {
            updateNet();
        });

        // Calculate tax button
        $(document).on('click.salaryFormGlobal', '.calculate-tax-btn', function(e) {
            e.preventDefault();
            calculateTax();
        });

        // Security percentage change
        $(document).on('input.salaryFormGlobal', '#security_percentage', function() {
            updateSec();
        });

        // EOBI percentage changes
        $(document).on('input.salaryFormGlobal', '#eobi_percentage', function() {
            updateEobi();
        });

        $(document).on('input.salaryFormGlobal', '#eobi_employer_percentage', function() {
            updateEobiEmployer();
        });

        // PESSI percentage changes
        $(document).on('input.salaryFormGlobal', '#pessi_percentage', function() {
            updatePessi();
        });

        $(document).on('input.salaryFormGlobal', '#pessi_employer_percentage', function() {
            updatePessiEmployer();
        });

        $(document).on('click.salaryFormGlobal', 'form input[type="submit"]', function(e) {
            const form = $(this).closest('form');
            if (form.find('input[name="employee_id"]').length && !validateRequiredSalaryDropdowns(form)) {
                e.preventDefault();
            }
        });

        $(document).on('submit.salaryFormGlobal', 'form', function(e) {
            if ($(this).find('input[name="employee_id"]').length && !validateRequiredSalaryDropdowns($(this))) {
                e.preventDefault();
                return false;
            }
        });

        $(document).on('change.salaryFormGlobal', 'form select[required], form select[required="required"]', function() {
            if ($(this).val()) {
                $(this).next('.custom-select-wrapper').find('.custom-select-display').removeClass('is-invalid border border-danger');
            }
        });

        $(document).on('change.salaryFormGlobal', 'select[name="paymode"]', function() {
            toggleBankAccountRequirement($(this).closest('form'));
        });

        document.removeEventListener('invalid', handleSalaryDropdownInvalid, true);
        document.addEventListener('invalid', handleSalaryDropdownInvalid, true);
        //tax 
        // calculateTax();
        // Initial calculations
        updateNet();
    }

    // Modal-aware utility functions
    function getCurrentModal() {
        const modal = $('.modal.show').last();
        if (modal.length === 0) {
            // Fallback to document if no modal is active
            return $(document);
        }
        return modal;
    }

    function findInModal(selector) {
        const modal = getCurrentModal();
        const element = modal.find(selector);
        if (element.length === 0) {
            // Fallback to document search if not found in modal
            return $(selector);
        }
        return element;
    }

    function salaryDropdownLabel(select) {
        const label = select.closest('.form-group, .btn-box').find('label').first().text().replace('*', '').trim();
        return label || select.attr('name') || 'required dropdown';
    }

    function showSalaryDropdownError(select) {
        const label = salaryDropdownLabel(select);
        const wrapper = select.next('.custom-select-wrapper');
        const message = 'Please select ' + label + '.';
        wrapper.find('.custom-select-display').addClass('is-invalid border border-danger');

        if (typeof show_toastr === 'function') {
            show_toastr('error', message, 'error');
        } else {
            alert(message);
        }

        if (wrapper.length && wrapper[0].scrollIntoView) {
            wrapper[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function validateRequiredSalaryDropdowns(scope) {
        const container = scope && $(scope).length ? $(scope) : getCurrentModal();
        let invalidSelect = null;
        toggleBankAccountRequirement(container);

        container.find('select[required], select[required="required"]').each(function() {
            const select = $(this);
            if (!select.val()) {
                invalidSelect = select;
                return false;
            }
        });

        if (invalidSelect) {
            showSalaryDropdownError(invalidSelect);
            return false;
        }

        const paymode = (container.find('select[name="paymode"]').val() || '').trim();
        const bankAccount = container.find('input[name="account_number"]');
        if (['Bank Deposit HBL', 'Bank Deposit AF', 'Bank'].includes(paymode) && !bankAccount.val()) {
            if (typeof show_toastr === 'function') {
                show_toastr('error', 'Please enter Bank A/C for bank paymode.', 'error');
            }
            bankAccount.addClass('is-invalid').focus();
            return false;
        }

        return true;
    }

    function handleSalaryDropdownInvalid(e) {
        const select = $(e.target);
        if (!select.is('select[required], select[required="required"]')) {
            return;
        }
        if (!select.closest('form').find('input[name="employee_id"]').length) {
            return;
        }

        e.preventDefault();
        showSalaryDropdownError(select);
    }

    // Clear pay scale data
    function clearPayscaleData() {
        const modal = getCurrentModal();
        const scaleHeadsRow = findInModal('.scale_heads_row');
        const netInput = findInModal('#net');
        netInput.val(0);
        const HeadsInputs = findInModal('.headsInput');
        HeadsInputs.remove();
        findInModal('input[name="emp_sec"]').val('');
        // findInModal('#effect_from').val('');
        //working days and per day sal clearPayscaleData
    }

    // Per day salary calculation
    function calculatePerDaySalary() {
        const grossEl = findInModal('input[name="gross"]');
        const daysEl = findInModal('#working_days');
        const perEl = findInModal('#per_day_salary');

        let gross = parseFloat(grossEl.val()) || 0;
        let days = Math.min(parseFloat(daysEl.val()) || 0, 31);

        daysEl.val(days);
        const perDayValue = (gross > 0 && days > 0) ? (gross / days).toFixed(2) : '';
        perEl.val(perDayValue);

    }

    // Tax calculation
    function calculateTax() {
        const empScaleId = findInModal('select[name="pay_scale"]').val();

        if (!empScaleId || empScaleId === '') {
            alert('Please select a pay scale first.');
            return;
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('calculate.tax') }}",
            type: "POST",
            data: {
                empScaleId: empScaleId,
                employee_id: {{ $employee->id }}
            },
            dataType: 'json',
            beforeSend: function() {},
            success: function(data) {
            console.log('Tax calculation response:', data);
                findInModal('#itax').val(data.permonthtax || 0);
                findInModal('#totaltax').val(data.totaltax || 0);
                findInModal('#prevtax').val(data.prevTax || 0);
                findInModal('#salary_tax_received').val(data.salaryTaxReceived || 0);
                findInModal('#advance_tax_collection').val(data.advanceTaxCollection || 0);
                updateNet();
            },
            error: function(xhr, status, error) {
                //fire sawl
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.error || 'An unexpected error occurred.',
                    confirmButtonText: 'OK',
                });

            }
        });
    }

    // Net calculation
    function updateNet() {
        const gross = parseFloat(findInModal('input[name="gross"]').val()) || 0;
        const security = parseFloat(findInModal('input[name="emp_sec"]').val()) || 0;

        const deductions = findInModal('.deduction-field').toArray().reduce((total, field) => {
            return total + (parseFloat($(field).val()) || 0);
        }, 0);

        const netValue = Math.round(gross - security - deductions);
        console.log('Gross:', gross, 'Security:', security, 'Deductions:', deductions, 'Net:', netValue);
        const netElement = findInModal('input[name="net"]');
        netElement.val(netValue);

    }

    // Gross calculation
    function updateGross() {
        const grossElement = findInModal('input[name="gross"]');
        const oldGrossElement = findInModal('input[name="old_gross"]');
        const oldGrossValue = parseFloat(oldGrossElement.val()) || 0;

        const additions = findInModal('.addition-field').toArray().reduce((total, field) => {
            return total + (parseFloat($(field).val()) || 0);
        }, 0);

        const newGross = oldGrossValue + additions;
        grossElement.val(newGross);


        calculatePerDaySalary();
        updateNet();
    }

    // Security calculation
    function updateSec() {
        if (basic_head_value == 0) {
            alert('Please select payscale first.');
            findInModal('#security_percentage').val(0);
            return;
        }
        const securityPercentage = parseFloat(findInModal('#security_percentage').val()) || 0;
        const securityValue = Math.round(basic_head_value * (securityPercentage / 100));
        findInModal('#emp_sec').val(securityValue);

        updateNet();
    }

    // EOBI calculations
    function updateEobi() {
        if (companySetEobi == 0) {
            alert('Please set EOBI value in company setup first.');
            findInModal('#eobi_percentage').val(0);
            return;
        }
        const eobiPercentage = parseFloat(findInModal('#eobi_percentage').val()) || 0;
        const eobiValue = (companySetEobi * (eobiPercentage / 100)).toFixed(2);
        findInModal('input[name="eobi"]').val(eobiValue);

        updateNet();
    }

    function updateEobiEmployer() {
        if (companySetEobiEmployer == 0) {
            alert('Please set EOBI Employer value in company setup first.');
            findInModal('#eobi_employer_percentage').val(0);
            return;
        }
        const eobiEmployerPercentage = parseFloat(findInModal('#eobi_employer_percentage').val()) || 0;
        const eobiEmployerValue = (companySetEobiEmployer * (eobiEmployerPercentage / 100)).toFixed(2);
        findInModal('input[name="eobi_employer"]').val(eobiEmployerValue);

    }

    // PESSI calculations
    function updatePessi() {
        if (companySetPessi == 0) {
            alert('Please set PESSI value in company setup first.');
            findInModal('#pessi_percentage').val(0);
            return;
        }
        const pessiPercentage = parseFloat(findInModal('#pessi_percentage').val()) || 0;
        const pessiValue = (companySetPessi * (pessiPercentage / 100)).toFixed(2);
        findInModal('input[name="pessi"]').val(pessiValue);

        updateNet();
    }

    function updatePessiEmployer() {
        if (companySetPessiEmployer == 0) {
            alert('Please set PESSI Employer value in company setup first.');
            findInModal('#pessi_employer_percentage').val(0);
            return;
        }
        const pessiEmployerPercentage = parseFloat(findInModal('#pessi_employer_percentage').val()) || 0;
        const pessiEmployerValue = (companySetPessiEmployer * (pessiEmployerPercentage / 100)).toFixed(2);
        findInModal('input[name="pessi_employer"]').val(pessiEmployerValue);

    }

    function departmentfunc(departmentId) {
        let hidDeptId = document.querySelector('#departmentid');
        if (!hidDeptId) {
            hidDeptId = findInModal('input[name="departmentid"]');
        }

        if (hidDeptId) {
            hidDeptId.value = departmentId;
        }
        const payScaleSelect = findInModal('#pay_scale');
        if (payScaleSelect) {
            payScaleSelect.value = '';
        }
        //working days 
        clearPayscaleData();
        calculatePerDaySalary();
        // get scales on basis of department change
        // getDeptWiseScales();
    }
    // Enhanced pay scale heads function
    function payscalheads(payScaleId) {

        const modal = getCurrentModal();
        const scaleHeadsRow = findInModal('.scale_heads_row');
        const effectfrom = findInModal('#effect_from');
        const securityElement = findInModal('input[name="emp_sec"]');
        const departmentId = document.querySelector('#departmentid').value;
        // Clear existing content
        scaleHeadsRow.find('.form-group:not(:has(#working_days)):not(:has(#per_day_salary))').remove();
        securityElement.val('');
        const netInput = findInModal('#net');
        // Validate required data
        if (!payScaleId || payScaleId === '') {
            return;
        }

        if (!departmentId || departmentId == '' || departmentId == null) {
            //fire sawl
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select a department first.',
                confirmButtonText: 'OK',
            });
            return;
        }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('get-pay-scale-heads') }}",
            type: "POST",
            data: {
                id: payScaleId,
                department_id: departmentId
            },
            dataType: 'json',
            success: function(data) {
                clearPayscaleData();
                // Remove loading indicator
                scaleHeadsRow.find('.loading-indicator').remove();

                if (data.success) {
                    let gross = 0;
                    let initialBasicsValue = 0;

                    // Set effect from date
                    // if (data.payscale && data.payscale.effect_from) {
                    //     effectfrom.val(data.payscale.effect_from);
                    // }

                    // Process each salary head
                    if (data.data && Array.isArray(data.data)) {

                        data.data.forEach((head, index) => {

                            const headName = head.salary_heads?.head?.toLowerCase() || '';
                            const headValue = parseFloat(head.head_value) || 0;
                            gross += headValue;

                            if (headName == 'initial basic') {
                                initialBasicsValue = headValue;
                                basic_head_value = headValue; // Update global variable
                            }

                            const headElement = $(`
                                <div class="form-group col-md-2 headsInput">
                                    <label class="form-label">${head.salary_heads?.head || 'Unknown Head'}</label>
                                    <input type="number" name="scale_heads[${head.salary_heads?.id || head.id}]" 
                                           value="${headValue}" class="form-control" readonly>
                                </div>
                            `);
                            scaleHeadsRow.append(headElement);
                        });
                    }


                    // Add gross display
                    const grossElement = $(`
                        <div class="form-group col-md-2 headsInput">
                            <label class="form-label">Gross</label>
                            <input type="number" name="gross" value="${gross}" class="form-control" readonly>
                            <input type="hidden" name="old_gross" value="${gross}">
                        </div>
                    `);
                    scaleHeadsRow.append(grossElement);

                    // Calculate and set security
                    const securityPercentage = parseFloat(findInModal('#security_percentage').val()) || 0;
                    const securityValue = Math.round(initialBasicsValue * (securityPercentage / 100));
                    securityElement.val(securityValue);

                    // Calculate net (gross - security - other deductions)
                    const currentDeductions = findInModal('.deduction-field').toArray().reduce((total,
                        field) => {
                        return total + (parseFloat($(field).val()) || 0);
                    }, 0);

                    const netValue = (gross - parseFloat(securityValue) - currentDeductions).toFixed(2);

                    netInput.val(netValue);
                    // Recalculate dependent values
                    calculatePerDaySalary();
                    updateGross();
                    updateNet();

                } else {
                    //fire sawl
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch pay scale heads: ' + (data.message ||
                            'Unknown error'),
                        confirmButtonText: 'OK',
                    });
                }
            },
            error: function(xhr, status, error) {

                // Remove loading indicator
                scaleHeadsRow.find('.loading-indicator').remove();

                let errorMessage = 'Error fetching pay scale data';
                if (xhr.status === 404) {
                    errorMessage = 'Pay scale data not found';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error occurred';
                } else if (error) {
                    errorMessage = 'Error: ' + error;
                }

                //fire sawl
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'OK',
                });
            },
            complete: function() {}
        });
    }

    // dept wise scales
    function getDeptWiseScales() {
        const dept = findInModal('select[name="department_id"]').val();

        if (!dept || dept === '') {
            return;
        }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('get-dept-wise-scales') }}",
            type: "GET",
            data: {
                dept: dept
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    const payScaleSelect = findInModal('#pay_scale');
                    payScaleSelect.empty();
                    payScaleSelect.append($('<option></option>').attr('value', '').text('Select Scale'));
                    data.data.forEach(scale => {
                        payScaleSelect.append($('<option></option>').attr('value', scale.id).text(
                            scale.title));
                    });
                    initializeSalarySelectSearch(getCurrentModal());
                }
            },
            error: function(xhr, status, error) {
                //fire sawl
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error,
                    confirmButtonText: 'OK',
                });
            }
        });
    }


    // Debug function - call this in browser console to check current state
    function debugSalaryForm() {

        // Test if pay scale change works
        const payScaleElement = $('#pay_scale');
        if (payScaleElement.length > 0) {
            payScaleElement.trigger('change');
        }
    }

    // Auto-debug on errors (optional - remove if not needed)
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        return false;
    };

    // Test function to verify AJAX endpoint
    function testAjaxEndpoint() {
        const testPayScaleId = findInModal('#pay_scale option:nth-child(2)').val();
        const testDepartmentId = findInModal('input[name="departmentid"]').val();

        if (!testPayScaleId || !testDepartmentId) {
            return;
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('get-pay-scale-heads') }}",
            type: "POST",
            data: {
                id: testPayScaleId,
                department_id: testDepartmentId
            },
            success: function(data) {},
            error: function(xhr, status, error) {}
        });
    }
</script>
{{ Form::close() }}

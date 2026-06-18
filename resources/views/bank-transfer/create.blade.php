{{ Form::open(['url' => 'bank-transfer']) }}

<div class="modal-body">
    <div class="row">

        <!-- FROM ACCOUNT -->
        <div class="form-group col-md-6">
            {{ Form::label('from_account', __('From Account'), ['class' => 'form-label']) }}
            <select name="from_account" id="from_account" class="form-control select custom-select" required>
                <option value="">Select Account</option>
                @foreach ($bankAccounts as $account)
                    <option value="{{ $account->id }}" data-balance="{{ $account->opening_balance }}">
                        {{ $account->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- TO ACCOUNT -->
        <div class="form-group col-md-6">
            {{ Form::label('to_account', __('To Account'), ['class' => 'form-label']) }}
            <select name="to_account" id="to_account" class="form-control select custom-select" required>
                <option value="">Select Account</option>
                @foreach ($bankAccounts as $account)
                    <option value="{{ $account->id }}" data-balance="{{ $account->opening_balance }}">
                        {{ $account->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- AVAILABLE BALANCE -->
        <div class="form-group col-md-6">
            <label class="form-label">Available Balance *</label>
            <input type="text" id="available_balance" class="form-control" readonly>
            <input type="hidden" name="prev_balance" id="available_balance_hidden">
        </div>

        <!-- AMOUNT -->
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', '', [
                'class' => 'form-control',
                'required',
                'step' => '0.01',
                'id' => 'amount'
            ]) }}
        </div>

        <!-- REFERENCE -->
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
            {{ Form::text('reference', '', ['class' => 'form-control', 'readonly']) }}
        </div>

        <!-- DATE -->
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
            {{ Form::date('date', null, ['class' => 'form-control', 'required']) }}
        </div>

        <!-- DESCRIPTION -->
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', '', [
                'class' => 'form-control',
                'rows' => 3,
                'required'
            ]) }}
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}

<script>
(function () {

    let fromAccount = document.getElementById('from_account');
    let toAccount   = document.getElementById('to_account');
    let amountInput = document.getElementById('amount');
    let balanceField = document.getElementById('available_balance');
    let balanceHidden = document.getElementById('available_balance_hidden');

    if (!fromAccount || !toAccount) return;

    // ─────────────────────────────
    // HELPERS
    // ─────────────────────────────
    function getInst(el) {
        return el?.customSelectInstance || null;
    }

    function getDisplay(el) {
        return getInst(el)?.display || null;
    }

    function forceOpen(el) {
        const inst = getInst(el);
        if (!inst) return;

        const display = getDisplay(el);

        if (display) {
            if (!display.hasAttribute('tabindex')) {
                display.setAttribute('tabindex', '0');
            }
            display.focus();
        }

        if (typeof inst.open === 'function') {
            inst.open();
        }
    }

    function forceClose(el) {
        const inst = getInst(el);
        if (inst && typeof inst.close === 'function') {
            inst.close();
        }
    }

    // ─────────────────────────────
    // UPDATE BALANCE
    // ─────────────────────────────
    function updateBalance() {
        let opt = fromAccount.options[fromAccount.selectedIndex];
        let bal = opt ? opt.getAttribute('data-balance') || 0 : 0;

        balanceField.value = bal;
        balanceHidden.value = bal;
        amountInput.max = bal;
        amountInput.value = '';

        if (toAccount.value === fromAccount.value && fromAccount.value) {
            alert('From and To account cannot be same.');
            toAccount.value = '';
        }

        if (fromAccount.value) {
            let url = "{{ route('bank.transfer.reference', ':id') }}".replace(':id', fromAccount.value);
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    let ref = document.querySelector('[name="reference"]');
                    if (ref) ref.value = data.reference;
                })
                .catch(() => {});
        }
    }

    // ─────────────────────────────
    // 🔥 MAIN FIX: WHEN FROM IS SELECTED → OPEN TO
    // ─────────────────────────────
    fromAccount.addEventListener('change', function () {

        updateBalance();

        // close from dropdown
        forceClose(fromAccount);

        // open to dropdown
        setTimeout(() => {
            forceOpen(toAccount);
        }, 100);
    });

    // ─────────────────────────────
    // VALIDATIONS
    // ─────────────────────────────
    toAccount.addEventListener('change', function () {
        if (this.value === fromAccount.value && this.value) {
            alert('From and To account cannot be same.');
            this.value = '';
        }
    });

    amountInput.addEventListener('input', function () {
        let max = parseFloat(this.max) || 0;
        let val = parseFloat(this.value) || 0;

        if (val > max) {
            alert('Amount cannot exceed available balance.');
            this.value = max;
        }
    });

    // ─────────────────────────────
    // MODAL OPEN → OPEN FROM FIRST
    // ─────────────────────────────
    let modal = fromAccount.closest('.modal');

    if (modal) {
        modal.addEventListener('shown.bs.modal', function () {

            updateBalance();

            setTimeout(() => {
                forceOpen(fromAccount);
            }, 100);
        });
    }

})();
</script>
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

        <!-- AVAILABLE BALANCE DISPLAY -->
        <div class="form-group col-md-6">
            <label class="form-label">Available Balance *</label>
            <input type="text" id="available_balance" class="form-control" readonly>
            {{-- //hidden --}}
            <input type="hidden" name="prev_balance" id="available_balance_hidden" value="">
        </div>

        <!-- AMOUNT -->
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', '', [
                'class' => 'form-control',
                'required' => 'required',
                'step' => '0.01',
                'id' => 'amount',
            ]) }}
        </div>
        <!-- REFERENCE -->
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
            {{ Form::text('reference', '', ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly']) }}
        </div>

        <!-- DATE -->
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
            {{ Form::date('date', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>


        <!-- DESCRIPTION -->
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', '', ['class' => 'form-control', 'rows' => 3,'required' => 'required']) }}
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}

<script>
    //document.addEventListener("DOMContentLoaded", function() {

    let fromAccount = document.getElementById('from_account');
    let toAccount = document.getElementById('to_account');
    let amountInput = document.getElementById('amount');
    let balanceField = document.getElementById('available_balance');
    let balanceFieldHidden = document.getElementById('available_balance_hidden');

    // When From Account changes
    fromAccount.addEventListener('change', function() {

        let selectedOption = this.options[this.selectedIndex];
        let balance = selectedOption.getAttribute('data-balance') || 0;

        balanceField.value = balance;
        balanceFieldHidden.value = balance;

        // Restrict max amount
        amountInput.setAttribute('max', balance);
        amountInput.value = '';

        // Prevent same account selection
        if (toAccount.value === this.value) {
            alert('From and To account cannot be same.');
            toAccount.value = '';
        }
        let url = "{{ route('bank.transfer.reference', ':id') }}";
        url = url.replace(':id', this.value);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                document.querySelector('input[name="reference"]').value = data.reference;
            });
    });

    // Prevent same account
    toAccount.addEventListener('change', function() {
        if (this.value === fromAccount.value) {
            alert('From and To account cannot be same.');
            this.value = '';
        }
    });

    // Prevent exceeding balance
    amountInput.addEventListener('input', function() {

        let max = parseFloat(this.getAttribute('max')) || 0;
        let value = parseFloat(this.value) || 0;

        if (value > max) {
            alert('Amount cannot exceed available balance.');
            this.value = max;
        }
    });

    // });
</script>

{{ Form::open(['url' => 'fee_head']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Head name'), 'required' => 'required']) }}
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('account', __('Chart of Accounts (Income)'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{ Form::select('account_id', $ChartofAccounts, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('receivables_account', __('Chart of Accounts (Receivables)'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{ Form::select('receivableaccount_id', $ChartofAccounts, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        {{-- //discount --}}
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('discount_account', __('Chart of Accounts (Discount)'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{ Form::select('discountaccount_id', $ChartofAccounts, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-12">
            <div class="form-group">
                {{ Form::label('status', __('Concession View'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::select('status', ['1' => 'Show', '0' => 'Hide'], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-outline-primary">
</div>

{{ Form::close() }}
<script>
    JsSearchBox();

    function updateWidths() {
        // Get the input element and its parent
        var refineText = document.querySelector('.refineText');
        var parentElement = refineText.parentNode;

        // Get the width of the parent element
        var parentWidth = parentElement.offsetWidth;

        // Set the input width
        refineText.style.width = parentWidth + 'px';

        // Set the search box element width
        var searchBoxElement = document.querySelector('.searchBoxElement');
        if (searchBoxElement) {
            searchBoxElement.style.width = parentWidth + 'px';
        }
        refineText.style.borderColor = '#100773';

    }

    // Listen for input events to update the input and search box element widths dynamically
    // document.querySelector('.refineText').addEventListener('input', updateWidths);

    // Call the function initially to set the initial widths based on the parent's width
    setTimeout(function() {
        updateWidths();
    }, 1000); // Delay of 1 second (1000 milliseconds)
</script>

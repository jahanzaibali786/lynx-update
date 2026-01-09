
{{-- <form id="adjustmentForm{{ $lastChallan->challanNo }}" action="" method="POST"> --}}
    <form action="{{ route('submit_adjustment') }}" method="POST" id="challanForm">
    @csrf
        <input type="hidden" name="challan_id" value="{{ $lastChallan->id }}">
        <input type="hidden" name="total_available" id="total_available" value="{{ $totalAvailable }}">
        <input type="hidden" name="used" id="used" value="0">
    <div style="display: grid; grid-template-columns: 30% 30% 30%; gap: 20px;">
        <h5><strong>Challan No:</strong> <span id="modalChallanNo">{{ $lastChallan->challanNo }}</span></h5>
        <h5><strong>Due Date:</strong> <span id="modalDueDate">{{ date('d-M-Y', strtotime($lastChallan->due_date)) }}</span></h5>
        <h5><strong>Billing Month:</strong> <span id="modalFeeMonth">{{ date('M-Y', strtotime($lastChallan->fee_month)) }}</span></h5>
        <h5><strong>Payable Fee:</strong> <span id="modalPayable">{{ $lastChallan->total_amount - ($lastChallan->concession_amount) }}</span></h5>
        <h5><strong>Total Fee:</strong> <span id="modalTotal"></span>{{ $lastChallan->total_amount - ($lastChallan->paid_amount + $lastChallan->concession_amount) }}</h5>
        <h5><strong>Max Adjustment:</strong> <span id="modalTotal"></span>{{ $totalAvailable }}</h5>
    </div>
    <hr>
    <h4><strong> Unpaid Fee Heads </strong></h4><br>
    @foreach ($lastChallan->unpaidHeads as $index => $head)
        <div class="row mb-3">
            <div class="col-md-4">
                {{ $head->feeHead->fee_head ?? 'Head' }}
                <input name="oldhead_id[]" type="hidden" value="{{ $head->head_id }}">
            </div>
            <div class="col-md-4">
                <input name="oldtamount[]" class="form-control oldtamount" type="text"
                    value="{{ $head->price - $head->concession - $head->paid }}" disabled>
            </div>
            <div class="col-md-4">
                <input name="oldramount[]" class="form-control oldramount challan-adj" type="number" value="" min="0"
                    max="{{ $head->price - $head->concession - $head->paid }}" step="any">
            </div>
        </div>
    @endforeach

        <button type="submit" id="submitBtn" class="btn btn-primary float-end mt-3 disabled">Submit</button>
    </form>

    <script>
    (function() {
        function updateMaxValues() {
            const totalAvailable = parseFloat(document.getElementById('total_available').value) || 0;
            const inputs = document.querySelectorAll('.challan-adj');

            let used = 0;

            inputs.forEach((input) => {
                const val = parseFloat(input.value) || 0;
                const remaining = totalAvailable - used;

                input.setAttribute('max', remaining);

                if (val > remaining) {
                    input.value = remaining;
                }

                used += parseFloat(input.value) || 0;
            });
            document.getElementById('used').value = used;
            if(used == 0){
                document.getElementById('submitBtn').classList.add('disabled');
            }else{
                document.getElementById('submitBtn').classList.remove('disabled');
            }
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('challan-adj')) {
                updateMaxValues();
            }
        });

        // Call once when the form loads
        updateMaxValues();
    })();
</script>

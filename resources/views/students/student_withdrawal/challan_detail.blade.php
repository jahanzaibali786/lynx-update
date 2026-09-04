{{-- <form id="adjustmentForm{{ $lastChallan->challanNo }}" action="" method="POST"> --}}
<form action="{{ route('submit_adjustment') }}" method="POST" id="challanForm">
    @csrf
    <input type="hidden" name="challan_id" value="{{ $lastChallan->id }}">
    <input type="hidden" name="total_available" id="total_available" value="{{ $totalAvailable }}">
    <input type="hidden" name="used" id="used" value="0">
    <div style="display: grid; grid-template-columns: 30% 30% 30%; gap: 20px;">
        <h5><strong>Challan No:</strong> <span id="modalChallanNo">{{ $lastChallan->challanNo }}</span></h5>
        <h5><strong>Due Date:</strong> <span
                id="modalDueDate">{{ date('d-M-Y', strtotime($lastChallan->due_date)) }}</span></h5>
        <h5><strong>Billing Month:</strong> <span
                id="modalFeeMonth">{{ date('M-Y', strtotime($lastChallan->fee_month)) }}</span></h5>
        <h5><strong>Payable Fee:</strong> <span
                id="modalPayable">{{ $lastChallan->total_amount - $lastChallan->concession_amount }}</span></h5>
        <h5><strong>Total Fee:</strong> <span
                id="modalTotal"></span>{{ $lastChallan->total_amount - ($lastChallan->paid_amount + $lastChallan->concession_amount) }}
        </h5>
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
                <input name="oldramount[]" class="form-control oldramount challan-adj" type="number" value=""
                    min="0" max="{{ $head->price - $head->concession - $head->paid }}"
                    data-head-max="{{ $head->price - $head->concession - $head->paid }}" {{-- ✅ ADD THIS --}}
                    step="any">
            </div>
        </div>
    @endforeach

    <button type="submit" id="submitBtn" class="btn btn-primary float-end mt-3 disabled">Submit</button>
</form>
<script>
    (function() {

        let isUpdating = false;

        function updateMaxValues(changedInput = null) {

            if (isUpdating) return;
            isUpdating = true;

            try {

                const totalAvailable = Number(document.getElementById('total_available')?.value) || 0;
                const inputs = document.querySelectorAll('.challan-adj');

                console.log("---- RUNNING UPDATE ----");
                console.log("Total Available:", totalAvailable);

                // STEP 1: calculate total used
                let totalUsed = 0;

                inputs.forEach(input => {
                    let val = Number(input.value);
                    if (!Number.isFinite(val)) val = 0;
                    totalUsed += val;
                });

                console.log("Total Used:", totalUsed);

                // STEP 2: enforce constraints per input
                inputs.forEach((input, index) => {

                    const headMax = Number(input.dataset.headMax) || 0;

                    let currentVal = Number(input.value);
                    if (!Number.isFinite(currentVal)) currentVal = 0;

                    // remove current input value from global used
                    const usedOtherInputs = totalUsed - currentVal;

                    const remainingGlobal = totalAvailable - usedOtherInputs;

                    const finalMax = Math.min(headMax, remainingGlobal);

                    console.log(`Input ${index}:`, {
                        headMax,
                        currentVal,
                        remainingGlobal,
                        finalMax
                    });

                    input.max = finalMax;

                    if (currentVal > finalMax) {
                        input.value = finalMax;
                    }
                });

                // STEP 3: recalc final used after correction
                let correctedUsed = 0;
                inputs.forEach(input => {
                    let val = Number(input.value);
                    if (Number.isFinite(val)) correctedUsed += val;
                });

                document.getElementById('used').value = correctedUsed;

                // STEP 4: toggle submit
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.classList.toggle('disabled', correctedUsed <= 0);
                }

            } finally {
                isUpdating = false;
            }
        }

        // FIX: wait for DOM sync (important for modals + inputs)
        document.addEventListener('input', function(e) {

            if (e.target.classList.contains('challan-adj')) {

                requestAnimationFrame(() => {
                    updateMaxValues(e.target);
                });

            }

        });

        // initial run
        window.addEventListener('load', () => {
            setTimeout(updateMaxValues, 100);
        });

    })();
</script>


{{-- <form id="adjustmentForm{{ $lastChallan->challanNo }}" action="" method="POST"> --}}
    {{-- <form action="{{ route('submit_adjustment') }}" method="POST" id="challanForm">
    @csrf --}}
        {{-- <input type="hidden" name="challan_id" value="{{ $lastChallan->id }}"> --}}
        {{-- <input type="hidden" name="total_available" id="total_available" value="{{ $totalAvailable }}"> --}}
    <div style="display: grid; grid-template-columns: 30% 30% 30%; gap: 20px;">
        <h5><strong>Challan No:</strong> <span id="modalChallanNo">{{ $challan->challanNo }}</span></h5>
        <h5><strong>Due Date:</strong> <span id="modalDueDate">{{ date('d-M-Y', strtotime($challan->due_date)) }}</span></h5>
        <h5><strong>Billing Month:</strong> <span id="modalFeeMonth">{{ date('M-Y', strtotime($challan->fee_month)) }}</span></h5>
        <h5><strong>Paid Date:</strong> <span id="modalFeeMonth">{{ date('d-M-Y', strtotime($adjustment->date)) }}</span></h5>
        <h5><strong>Adjustment Paid:</strong> <span id="modalPayable">{{ $adjustment->amount }}</span></h5>
    </div>
    <hr>
    <h4><strong> Adjusted Fee Heads </strong></h4><br>
    @foreach ($heads as $index => $head)
        <div class="row mb-3">
            <div class="col-md-4">
                {{ $head['head_name'] ?? 'Head' }}
                <input name="oldhead_id[]" type="hidden" value="{{ $head['head_id'] }}">
            </div>
            <div class="col-md-4">
                <input name="oldramount[]" class="form-control oldramount challan-adj" type="number" value="{{ $head['amount'] }}" min="0"
                    max="{{ $head['amount'] }}" step="any" disabled>
            </div>
            <div class="col-md-4">
                <input name="oldtamount[]" class="form-control oldtamount" type="text"
                    value="{{ $head['description'] }}" disabled>
            </div>
        </div>
    @endforeach

        {{-- <button type="submit" class="btn btn-primary float-end mt-3">Submit</button>
    </form> --}}

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

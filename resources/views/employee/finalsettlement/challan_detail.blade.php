@php
    $totalAvailable = ($actual_sec ?? 0) + ($notpaidsalaries ?? 0) - $already_adjusted;
@endphp

<div>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <p><strong>Challan #:</strong>{{ $challan->challanNo }}</p>
        <p><strong>Date:</strong> {{ $challan->date }}</p>
        <p><strong>Status:</strong> {{ ucfirst($challan->status) }}</p>
        <p><strong>Total Amount:</strong> Rs.{{ number_format($challan->total_amount) }}</p>
        <p><strong>Available Amount:</strong> Rs.{{ number_format($totalAvailable) }}</p>
    </div>
    <hr>
    <h6>Challan Heads</h6>

    <form action="{{ route('EmployeeSettlement.adjustchallan') }}" method="POST" id="challanForm">
        @csrf
        <input type="hidden" name="challan_id" value="{{ $challan->id }}">
        <input type="hidden" id="total_available" value="{{ $totalAvailable }}">
        <input type="hidden" name="employee_id" value="{{ $employee_id }}">
        <table class="">
            <thead class="table_heads">
                <tr>
                    <th>Head</th>
                    <th>Remaining Amount</th>
                    <th>Adj. Amnt</th>
                    <th>Referance <span style="color: red;">*</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($challan->heads as $index => $head)
                    {{-- @dump($head) --}}
                    @php
                        $headName = $head->feehead->fee_head;
                        $actualAmount = $head->price - $head->paid  - $head->concession;
                        @endphp
                        {{-- @dump($actualAmount,$head->price - $head->paid  - $head->concession) --}}
                    <tr>
                        <td>{{ $headName }}</td>
                        <td>Rs.{{ number_format($actualAmount) }}</td>
                        <td style="display: flex; align-items: center;">
                            Rs.&nbsp;&nbsp;
                            <input class="form-control challan-adj" data-index="{{ $index }}"
                                style="width: 100px;" type="number" name="heads[{{ $index }}][adjust_amount]"
                                value="{{ $actualAmount }}" min="0" max="{{ $totalAvailable }}"  @if($totalAvailable == 0) disabled @endif >
                            <input type="hidden" name="heads[{{ $index }}][amount]"
                                value="{{ $actualAmount }}">
                            <input type="hidden" name="heads[{{ $index }}][head_name]"
                                value="{{ $headName }}">
                            <input type="hidden" name="heads[{{ $index }}][head_id]"
                                value="{{ $head->id }}">
                        </td>
                        <td>
                            <input class="form-control" type="text" name="heads[{{ $index }}][referance]"
                                required>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="2">
                        <label for="adjustment_account">Adjustment Account <span style="color: red;">*</span></label>
                        <select name="adjustment_account" id="adjustment_account" class="form-control" required>
                            @foreach ($chartOfAccounts as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td colspan="2">
                        <label for="ref">Adjustment Reference <span style="color: red;">*</span></label>
                        <input class="form-control" type="text" name="ref" id="ref" required
                            placeholder="Reference">
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary float-end mt-3">Submit</button>
    </form>
</div>

{{-- JavaScript --}}
<script>
    (function() {
        function updateMaxValues() {
            const totalAvailable = parseFloat(document.getElementById('total_available').value) || 0;
            const inputs = document.querySelectorAll('.challan-adj');
            console.log(totalAvailable);
            
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

{{ Form::model($dailyClosing, ['route' => ['daily-closing.update', $dailyClosing->id], 'method' => 'PUT', 'id' => 'daily-closing-edit-form']) }}
<div class="modal-body">
    <div class="row">
        <!-- Error alert placeholder -->
        <div class="col-md-12 d-none" id="modal-error-alert-wrapper">
            <div class="alert alert-danger" id="modal-error-alert"></div>
        </div>

        <!-- Left Column: Date Range Selection & Income Received Transfers -->
        <div class="col-md-7 border-end">
            <h5 class="mb-3 text-primary"><i class="ti ti-calendar"></i> {{ __('Income Received (Transfers)') }}</h5>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                        {{ Form::date('from_date', $dailyClosing->from_date->format('Y-m-d'), ['class' => 'form-control date-input', 'required' => 'required', 'id' => 'e_from_date']) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                        {{ Form::date('to_date', $dailyClosing->to_date->format('Y-m-d'), ['class' => 'form-control date-input', 'required' => 'required', 'id' => 'e_to_date']) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('deposit_date', __('Deposit Date'), ['class' => 'form-label']) }}
                        {{ Form::date('deposit_date', $dailyClosing->deposit_date ? $dailyClosing->deposit_date->format('Y-m-d') : date('Y-m-d'), ['class' => 'form-control', 'required' => 'required', 'id' => 'e_deposit_date']) }}
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-bordered table-striped" id="transfers-table">
                    <thead>
                        <tr style="background-color: #f1f1f1;">
                            <th style="width: 5%;">#</th>
                            <th style="width: 30%;">{{ __('Branch') }}</th>
                            <th style="width: 20%;">{{ __('Period') }}</th>
                            <th style="width: 20%;">{{ __('Slip No') }}</th>
                            <th style="width: 10%;">{{ __('A/C') }}</th>
                            <th style="width: 15%; text-align: right;">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody id="transfers-list">
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ __('Loading transfers...') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-3 p-2 bg-light rounded">
                <strong>{{ __('Total Income Received') }}:</strong>
                <span class="h5 m-0 text-primary font-weight-bold" id="total-received-display">{{ number_format($dailyClosing->total_income_received, 2) }}</span>
                <input type="hidden" name="total_income_received" id="total_income_received_val" value="{{ $dailyClosing->total_income_received }}">
            </div>
        </div>

        <!-- Right Column: Income Deposited Note Counts -->
        <div class="col-md-5">
            <h5 class="mb-3 text-primary"><i class="ti ti-wallet"></i> {{ __('Income Deposited (Cash Denominations)') }}</h5>
            
            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">{{ __('Value') }}</th>
                            <th style="width: 30%;">{{ __('Count') }}</th>
                            <th style="width: 40%; text-align: right;">{{ __('Total Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $denominations = [5000, 1000, 500, 100, 50, 20, 10, 5, 2, 1];
                        @endphp
                        @foreach ($denominations as $denom)
                            @php
                                $field = 'note_' . $denom;
                                $countVal = $dailyClosing->$field ?? 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $denom }}</strong></td>
                                <td>
                                    <input type="number" name="note_{{ $denom }}" id="note_{{ $denom }}" class="form-control form-control-sm note-count-input" min="0" value="{{ $countVal }}" style="width: 100px;">
                                </td>
                                <td class="text-end">
                                    <span id="denom_total_{{ $denom }}">{{ number_format($countVal * $denom, 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="table-light font-weight-bold">
                            <td colspan="2"><strong>{{ __('Total Deposited') }}</strong></td>
                            <td class="text-end text-primary h5 m-0 font-weight-bold" id="total-deposited-display">{{ number_format($dailyClosing->total_income_deposited, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Discrepancy & Signatures Section -->
    <div class="row mt-4 pt-3 border-top bg-light p-3 rounded">
        <div class="col-md-4 d-flex align-items-center">
            <div class="w-100">
                <h6 class="m-0 text-dark">{{ __('Difference in Income Deposited') }}:</h6>
                @php
                    $diff = $dailyClosing->difference;
                @endphp
                <span class="h4 font-weight-bold m-0 @if($diff > 0) text-danger @elseif($diff < 0) text-success @else text-muted @endif" id="difference-display">
                    @if($diff > 0)
                        ({{ number_format(abs($diff), 2) }})
                    @elseif($diff < 0)
                        {{ number_format(abs($diff), 2) }}
                    @else
                        0.00
                    @endif
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('issued_by_id', __('Issued By'), ['class' => 'form-label']) }}
                {{ Form::select('issued_by_id', $hoEmployees, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Issued By')]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('received_by_id', __('Received By'), ['class' => 'form-label']) }}
                {{ Form::select('received_by_id', $hoEmployees, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Received By')]) }}
            </div>
        </div>
    </div>

    <!-- Note Section -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('note', __('Note'), ['class' => 'form-label']) }}
                {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter closing remarks or note...')]) }}
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light btn-sm" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary btn-sm" id="submit-btn">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        var totalReceived = parseFloat($('#total_income_received_val').val()) || 0;
        var totalDeposited = 0;

        function fetchTransfers() {
            var fromDate = $('#e_from_date').val();
            var toDate = $('#e_to_date').val();

            if (!fromDate || !toDate) return;

            $('#submit-btn').prop('disabled', true);
            $('#transfers-list').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</td></tr>');
            $('#modal-error-alert-wrapper').addClass('d-none');

            $.ajax({
                url: '{{ route("daily-closing.transfers") }}',
                type: 'GET',
                data: {
                    from_date: fromDate,
                    to_date: toDate,
                    exclude_id: '{{ $dailyClosing->id }}'
                },
                success: function(response) {
                    if (response.overlap) {
                        $('#modal-error-alert').text(response.message);
                        $('#modal-error-alert-wrapper').removeClass('d-none');
                        $('#transfers-list').html('<tr><td colspan="6" class="text-center text-danger font-weight-bold">' + response.message + '</td></tr>');
                        totalReceived = 0;
                        updateCalculations();
                        return;
                    }

                    var html = '';
                    if (response.transfers.length === 0) {
                        html = '<tr><td colspan="6" class="text-center text-muted py-4">{{ __("No bank transfer entries found for selected range") }}</td></tr>';
                        totalReceived = 0;
                    } else {
                        $.each(response.transfers, function(i, row) {
                            html += '<tr>' +
                                '<td>' + row.sr_no + '</td>' +
                                '<td>' + row.branch_name + '</td>' +
                                '<td>' + row.period + '</td>' +
                                '<td>' + row.slip_no + '</td>' +
                                '<td><span class="badge bg-secondary">' + row.ac + '</span></td>' +
                                '<td style="text-align: right;">' + row.amount.toFixed(2) + '</td>' +
                                '</tr>';
                        });
                        totalReceived = response.total_received;
                        $('#submit-btn').prop('disabled', false);
                    }
                    $('#transfers-list').html(html);
                    updateCalculations();
                },
                error: function(xhr) {
                    $('#transfers-list').html('<tr><td colspan="6" class="text-center text-danger py-4">{{ __("Failed to fetch transfers") }}</td></tr>');
                    totalReceived = 0;
                    updateCalculations();
                }
            });
        }

        function updateCalculations() {
            // Note Summation
            totalDeposited = 0;
            $('.note-count-input').each(function() {
                var input = $(this);
                var val = parseInt(input.attr('name').split('_')[1]);
                var count = parseInt(input.val()) || 0;
                var subtotal = val * count;
                totalDeposited += subtotal;
                $('#denom_total_' + val).text(subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            });

            // Update displays
            $('#total-received-display').text(totalReceived.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#total_income_received_val').val(totalReceived);
            $('#total-deposited-display').text(totalDeposited.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            // Difference
            var diff = totalReceived - totalDeposited;
            var diffDisplay = $('#difference-display');
            
            if (diff > 0) {
                diffDisplay.text('(' + diff.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
                diffDisplay.removeClass('text-success text-muted').addClass('text-danger');
            } else if (diff < 0) {
                diffDisplay.text(Math.abs(diff).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                diffDisplay.removeClass('text-danger text-muted').addClass('text-success');
            } else {
                diffDisplay.text('0.00');
                diffDisplay.removeClass('text-danger text-success').addClass('text-muted');
            }
        }

        function validateEmployees() {
            var issuedBy = $('select[name="issued_by_id"]').val();
            var receivedBy = $('select[name="received_by_id"]').val();
            
            if (issuedBy && receivedBy && issuedBy === receivedBy) {
                $('#modal-error-alert').text('Issued By and Received By cannot be the same employee.');
                $('#modal-error-alert-wrapper').removeClass('d-none');
                $('#submit-btn').prop('disabled', true);
                return false;
            } else {
                if ($('#modal-error-alert').text() === 'Issued By and Received By cannot be the same employee.') {
                    $('#modal-error-alert-wrapper').addClass('d-none');
                    if (totalReceived > 0) {
                        $('#submit-btn').prop('disabled', false);
                    }
                }
            }
            return true;
        }

        $(document).on('change', 'select[name="issued_by_id"], select[name="received_by_id"]', function() {
            validateEmployees();
        });

        // Initialize select2 if available
        if ($.fn.select2) {
            $('.select2').select2({
                dropdownParent: $('#commonModal'),
                width: '100%'
            });
        }

        // Listeners
        $('.date-input').on('change', function() {
            fetchTransfers();
        });

        $(document).on('input change', '.note-count-input', function() {
            updateCalculations();
        });

        // Initialize on load
        fetchTransfers();

        // AJAX Form Submit
        $('#daily-closing-edit-form').on('submit', function(e) {
            e.preventDefault();
            if (!validateEmployees()) {
                return false;
            }
            var form = $(this);
            var submitBtn = $('#submit-btn');
            
            submitBtn.prop('disabled', true);
            $('#modal-error-alert-wrapper').addClass('d-none');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#commonModal').modal('hide');
                        window.upsertDailyClosingRow?.(response.row, '{{ $dailyClosing->id }}', 'replace');
                        show_toastr('success', response.message, 'success');
                    } else {
                        $('#modal-error-alert').text(response.error || 'Validation error');
                        $('#modal-error-alert-wrapper').removeClass('d-none');
                        submitBtn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    $('#modal-error-alert').text(xhr.responseJSON?.error || 'Server error occurred. Please try again.');
                    $('#modal-error-alert-wrapper').removeClass('d-none');
                    submitBtn.prop('disabled', false);
                }
            });
        });
    });
</script>

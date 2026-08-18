@extends('layouts.admin')
@section('page-title')
    {{ __('Remove Late Fee') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Remove Late Fee') }}</li>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Remove Late Fee') }}</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="challan_no">{{ __('Challan Number') }}</label>
                            <input type="text" class="form-control" id="challan_no" name="challan_no" placeholder="{{ __('Enter Challan Number') }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="searchBtn" class="btn btn-primary w-100">{{ __('Search') }}</button>
                    </div>
                </div>

                <div id="challanDetails" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table_heads">
                                <tr>
                                    <th>{{ __('Challan No') }}</th>
                                    <th>{{ __('Student Name') }}</th>
                                    <th>{{ __('Roll No') }}</th>
                                    <th>{{ __('Class') }}</th>
                                    <th>{{ __('Billing Month') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Paid Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="challanInfo">
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <h4 class="mt-4">{{ __('Challan Heads') }}</h4>
                    <br>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table_heads">
                                <tr>
                                    <th>{{ __('Head Name') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Concession') }}</th>
                                    <th>{{ __('Paid Amount') }}</th>
                                    <th>{{ __('Outstanding Amount') }}</th>
                                    <th>{{ __('Remove Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody id="challanHeads">
                            </tbody>
                        </table>
                    </div>

                    <h4 class="mt-4">{{ __('Receipts') }}</h4>
                    <br>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table_heads">
                                <tr>
                                    <th>{{ __('Receipt Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Heads Paid') }}</th>
                                </tr>
                            </thead>
                            <tbody id="receipts">
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <button type="button" id="removeBtn" class="btn btn-danger w-100">{{ __('Remove Late Fee') }}</button>
                        </div>
                    </div>
                </div>

                <div id="errorMessage" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script-page')
<script>
$(document).ready(function() {
    $('#searchBtn').click(function() {
        const challanNo = $('#challan_no').val().trim();
        
        if (!challanNo) {
            $('#errorMessage').text('{{ __('Please enter a challan number.') }}').show();
            $('#challanDetails').hide();
            return;
        }

        $('#errorMessage').hide();
        $('#searchBtn').prop('disabled', true).text('{{ __('Searching...') }}');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('remove-late-fee.search') }}",
            type: "POST",
            data: {
                challan_no: challanNo
            },
            dataType: 'json',
            success: function(result) {
                $('#searchBtn').prop('disabled', false).text('{{ __('Search') }}');
                
                if (result.status === 'success') {
                    displayChallanDetails(result.data);
                } else {
                    $('#errorMessage').text(result.message).show();
                    $('#challanDetails').hide();
                }
            },
            error: function(xhr) {
                $('#searchBtn').prop('disabled', false).text('{{ __('Search') }}');
                const response = xhr.responseJSON;
                $('#errorMessage').text(response ? response.message : '{{ __('An error occurred. Please try again.') }}').show();
                $('#challanDetails').hide();
            }
        });
    });

    $('#removeBtn').off('click').on('click', function() {
        const challanNo = $('#challan_no').val().trim();
        const removeAmount = parseFloat($('#remove_amount').val());

        if (!removeAmount || removeAmount <= 0) {
            alert('{{ __('Please enter a valid remove amount.') }}');
            return;
        }

        const outstandingAmount = parseFloat($('#outstanding_amount').val());
        if (removeAmount > outstandingAmount) {
            alert('{{ __('Remove amount cannot exceed outstanding amount.') }}');
            return;
        }

        if (!confirm('{{ __('Are you sure you want to remove this Late Fee?') }}')) {
            return;
        }

        $('#removeBtn').prop('disabled', true).text('{{ __('Processing...') }}');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('remove-late-fee.remove') }}",
            type: "POST",
            data: {
                challan_no: challanNo,
                remove_amount: removeAmount
            },
            dataType: 'json',
            success: function(result) {
                if (result.status === 'success') {
                    alert(result.message);
                    window.location.href = "{{ route('remove-late-fee.index') }}";
                } else {
                    $('#removeBtn').prop('disabled', false).text('{{ __('Remove Late Fee') }}');
                    alert(result.message || '{{ __('An error occurred.') }}');
                }
            },
            error: function(xhr) {
                $('#removeBtn').prop('disabled', false).text('{{ __('Remove Late Fee') }}');
                const response = xhr.responseJSON;
                if (response) {
                    let errorMsg = response.message || '{{ __('An error occurred. Please try again.') }}';
                    if (response.trace) {
                        errorMsg += '\n\nTrace: ' + response.trace;
                    }
                    alert(errorMsg);
                } else {
                    alert('{{ __('An error occurred. Please try again.') }}');
                }
            }
        });
    });

    // Real-time validation for remove amount input
    $(document).on('input', '#remove_amount', function() {
        const removeAmount = parseFloat($(this).val());
        const outstandingAmount = parseFloat($('#outstanding_amount').val());
        const maxVal = $(this).attr('max');

        if (removeAmount > outstandingAmount) {
            // Automatically set to maximum outstanding amount
            $(this).val(outstandingAmount);
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    function displayChallanDetails(data) {
        const challan = data.challan;
        const heads = data.heads;
        const lateFeeHead = data.late_fee_head;
        const outstandingAmount = data.outstanding_amount;

        // Determine billing month
        let billingMonth = '-';
        if (challan.other_months && challan.other_months !== null && challan.other_months !== '') {
            billingMonth = formatBillingMonth(challan.other_months);
        } else if (challan.fee_month) {
            billingMonth = formatBillingMonth(challan.fee_month);
        }

        // Display challan info
        let challanHtml = `
            <tr>
                <td>${challan.challanNo}</td>
                <td>${challan.student ? challan.student.stdname : '-'}</td>
                <td>${challan.student ? challan.student.roll_no : '-'}</td>
                <td>${challan.class ? challan.class.name : '-'}</td>
                <td>${billingMonth}</td>
                <td>${challan.due_date || '-'}</td>
                <td>${formatCurrency(challan.total_amount)}</td>
                <td>${formatCurrency(challan.paid_amount)}</td>
                <td><span class="badge bg-${getStatusBadgeClass(challan.status)}">${challan.status}</span></td>
            </tr>
        `;
        $('#challanInfo').html(challanHtml);

        // Display challan heads
        let headsHtml = '';
        heads.forEach(function(head) {
            const isLateFee = lateFeeHead && head.id === lateFeeHead.id;
            const outstanding = head.price - head.concession - head.paid;
            const headName = head.fee_head ? head.fee_head.fee_head : '-';
            
            headsHtml += `
                <tr>
                    <td>${headName}</td>
                    <td>${formatCurrency(head.price)}</td>
                    <td>${formatCurrency(head.concession)}</td>
                    <td>${formatCurrency(head.paid)}</td>
                    <td>${formatCurrency(outstanding)}</td>
                    <td>
                        ${isLateFee ? 
                            `<input type="number" class="form-control" id="remove_amount" name="remove_amount" 
                                   min="0.01" max="${outstandingAmount}" step="0.01" value="${outstandingAmount}" 
                                   placeholder="{{ __('Enter amount') }}">` : 
                            '<input type="text" class="form-control" disabled value="-">'
                        }
                        <input type="hidden" id="outstanding_amount" value="${outstandingAmount}">
                    </td>
                </tr>
            `;
        });
        $('#challanHeads').html(headsHtml);

        // Display receipts
        const receipts = data.receipts || [];
        let receiptsHtml = '';
        if (receipts.length === 0) {
            receiptsHtml = '<tr><td colspan="3" class="text-center">{{ __('No receipts found') }}</td></tr>';
        } else {
            receipts.forEach(function(receipt) {
                // Get journal items with type 'Challan Payment'
                const journalItems = receipt.journal_items || [];
                const challanPaymentItems = journalItems.filter(function(item) {
                    return item.types === 'Challan Payment';
                });

                // Get fee head names from journal item's heads relation
                let headsPaid = [];
                challanPaymentItems.forEach(function(item) {
                    if (item.heads && item.heads.fee_head) {
                        headsPaid.push(item.heads.fee_head);
                    }
                });

                const headsPaidText = headsPaid.length > 0 ? headsPaid.join(', ') : '-';

                receiptsHtml += `
                    <tr>
                        <td>${receipt.recipt_date || '-'}</td>
                        <td>${formatCurrency(receipt.recipt_amount)}</td>
                        <td>${headsPaidText}</td>
                    </tr>
                `;
            });
        }
        $('#receipts').html(receiptsHtml);

        $('#challanDetails').show();
    }

    function formatCurrency(amount) {
        return parseFloat(amount || 0).toLocaleString('en-PK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getStatusBadgeClass(status) {
        switch(status.toLowerCase()) {
            case 'paid':
                return 'success';
            case 'partial paid':
                return 'warning';
            case 'issued':
                return 'info';
            default:
                return 'secondary';
        }
    }

    function formatBillingMonth(monthString) {
        if (!monthString) return '-';
        
        // Handle comma-separated months
        const months = monthString.split(',');
        const formattedMonths = months.map(function(month) {
            const date = new Date(month.trim());
            if (isNaN(date.getTime())) return '-';
            
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const monthName = monthNames[date.getMonth()];
            const year = date.getFullYear();
            
            return monthName + '-' + year;
        });
        
        return formattedMonths.join(', ');
    }
});
</script>
@endpush

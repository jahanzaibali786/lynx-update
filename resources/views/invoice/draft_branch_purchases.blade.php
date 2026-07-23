<div class="modal-body">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Purchase No') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    @php
                        $formattedNo = Auth::user()->purchaseNumberFormat($purchase->branch_purchase_no);
                    @endphp
                    <tr class="select-purchase" data-id="{{ $purchase->id }}" data-code="{{ $formattedNo }}" style="cursor:pointer">
                        <td><button type="button" class="btn btn-sm btn-outline-primary text-light select-purchase-btn">{{ $formattedNo }}</button></td>
                        <td>{{ $purchase->branch->name ?? '-' }}</td>
                        <td>{{ $purchase->purchase_date }}</td>
                        <td>{{ number_format($purchase->getTotal(), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">{{ __('No draft branch purchases found for this store.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <script>
        var _purchaseBusy = false;
        $(document).on('click', '.select-purchase, .select-purchase-btn', function(e) {
            if (_purchaseBusy) return;
            _purchaseBusy = true;

            var $row = $(this).closest('tr.select-purchase');
            var code = $row.data('code');
            var id = $row.data('id');

            $('input[name="ref_number"]').val(code);
            $('#commonModalOver').modal('hide');

            if (!id) {
                _purchaseBusy = false;
                return;
            }

            $.ajax({
                url: '{{ url("invoice/branch-purchase-items") }}/' + id,
                success: function(items) {
                    if (items && items.length) {
                        items.forEach(function(item) {
                            // We need to trigger the same product fetch logic used in the main invoice form,
                            // OR we can manually populate the array and re-render. Since we have product_id,
                            // the easiest way is to add a row and trigger the select.
                            
                            var rid = ++rowCounter;
                            
                            var selectOptions = PRODUCT_OPTS;
                            var rowHtml = '<tr data-row-id="' + rid + '" class="inline-edit-row" data-purchase-item="true">' +
                                '<td class="col-item">' +
                                '<div style="margin-bottom: 2px;"><span class="badge bg-info" style="font-size: 10px;">Branch Purchase</span></div>' +
                                '<select class="form-control form-control-sm custom-select row-item" data-rid="' + rid + '">' +
                                selectOptions +
                                '</select>' +
                                '<input type="hidden" class="row-stock-new" data-rid="' + rid + '" value="0">' +
                                '<input type="hidden" class="row-stock-used" data-rid="' + rid + '" value="0">' +
                                '<input type="hidden" class="row-stock-damaged" data-rid="' + rid + '" value="0">' +
                                '</td>' +
                                '<td class="col-quantity">' +
                                '<input type="number" class="form-control form-control-sm row-quantity" data-rid="' + rid + '" placeholder="Qty" min="1" step="1" value="' + item.quantity + '">' +
                                '<span class="unit-label" id="unit-' + rid + '"></span>' +
                                '</td>' +
                                '<td class="col-price">' +
                                '<input type="number" class="form-control form-control-sm row-price" data-rid="' + rid + '" placeholder="0.00" min="0" step="0.01" value="' + item.price + '">' +
                                '</td>' +
                                '<td class="col-type">' +
                                '<select class="form-control form-control-sm custom-select row-type" data-rid="' + rid + '">' +
                                '<option value="new" selected>New</option>' +
                                '<option value="use">Used</option>' +
                                '<option value="damage">Damage</option>' +
                                '</select>' +
                                '</td>' +
                                '<td class="col-amount text-end">' +
                                '<span class="amount-display" id="amount-' + rid + '">0.00</span>' +
                                '</td>' +
                                '<td class="col-actions" style="white-space:nowrap;">' +
                                '<button type="button" class="btn btn-sm btn-primary confirm-row-btn" data-rid="' + rid + '" title="Confirm"><i class="ti ti-check"></i></button> ' +
                                '<button type="button" class="btn btn-sm btn-outline-danger discard-row-btn" data-rid="' + rid + '" title="Discard"><i class="ti ti-x"></i></button>' +
                                '</td>' +
                                '</tr>';

                            $('#items-tbody').append(rowHtml);
                            $('#empty-row').hide();

                            var $newRowItem = $('.row-item[data-rid="' + rid + '"]');
                            $newRowItem.val(item.product_id);
                            
                            // Trigger change to load taxes, unit, and prices then we can confirm it 
                            // wait, we already have quantity and price from branch purchase. We can trigger change to get taxes and then override price.
                            $newRowItem.trigger('change');
                            setTimeout(function() {
                                $('.row-quantity[data-rid="' + rid + '"]').val(item.quantity);
                                $('.row-price[data-rid="' + rid + '"]').val(item.price);
                                calculateRowAmount(rid);
                                $('.confirm-row-btn[data-rid="' + rid + '"]').trigger('click');
                            }, 500);
                        });
                    }
                },
                complete: function() {
                    _purchaseBusy = false;
                }
            });
        });
    </script>
</div>

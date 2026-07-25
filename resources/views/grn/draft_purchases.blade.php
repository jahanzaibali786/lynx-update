<div class="modal-body">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Purchase No') }}</th>
                    <th>{{ __('Vendor') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr class="select-purchase" data-id="{{ $purchase->id }}" data-code="{{ \Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}" style="cursor:pointer">
                        <td><a type="button" class="btn btn-sm btn-outline-primary text-light select-purchase-btn">{{ \Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}</a></td>
                        <td>{{ $purchase->vender->name ?? '-' }}</td>
                        <td>{{ $purchase->purchase_date }}</td>
                        <td>{{ number_format($purchase->getTotal(), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">{{ __('No draft purchases found.') }}</td>
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

            $('input[name="purchase_order_id"]').val(id);
            $('input[name="purchase_order"]').val(code);
            $('#commonModalOver').modal('hide');

            $('#grn-items-table tbody tr[data-purchase-item="true"]').remove();

            if (!id) {
                _purchaseBusy = false;
                return;
            }

            $.ajax({
                url: '{{ url("grn/purchase-items") }}/' + id,
                success: function(items) {
                    if (items && items.length) {
                        items.forEach(function(item) {
                            addRow({
                                purchase_id: item.purchase_id,
                                purchase_product_id: item.purchase_product_id,
                                purchase_order_no: item.purchase_order_no,
                                product_id: item.product_id,
                                ordered_quantity: item.ordered_quantity,
                                received_quantity: item.received_quantity,
                                quantity: item.quantity,
                                available_quantity: item.available_quantity,
                                price: item.price,
                                description: item.description,
                                source: item.purchase_order_no
                            }, true, false);
                            $('#grn-items-table tbody tr:last').attr('data-purchase-item', 'true');
                        });
                        recalcTotal();
                    }
                },
                complete: function() {
                    _purchaseBusy = false;
                }
            });
        });
    </script>
</div>

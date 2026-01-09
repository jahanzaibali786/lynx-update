{{ Form::model($recipt, ['route' => ['student_receipt.update', $recipt->id], 'method' => 'PUT']) }}
<script>
$(function () {
    function refreshReceiptTotal() {
        let sum = 0;
        $('.receipt-credit').each(function () {
            const $input = $(this);
            const max = parseInt($input.data('max')) || 0;
            const paid = parseInt($input.data('paid')) || 0;
            let val = parseInt($input.val()) || 0;

            if (val < 0) val = 0;
            if (val > max) val = max;
            $input.val(val);
            
            sum += val;
            const id = $input.attr('name').match(/\d+/)[0]; // extract id from name like items[12][credit]
            const remaining = max - val;
            $(`.remaining-balance[data-id="${id}"]`).text(remaining.toFixed(2));
        });

        $('#recipt_amount').val(sum);
    }

    $(document).on('input', '.receipt-credit', refreshReceiptTotal);
    // refreshReceiptTotal();
});
</script>

<div class="p-4">
    <div class="row mb-3">
        <div class="col-md-3">
            {{ Form::label('recipt_date', __('Receipt Date'), ['class' => 'form-label']) }}
            {{ Form::date('recipt_date', null, ['class' => 'form-control', 'required', 'readonly' => 'readonly']) }}
        </div>
        <div class="col-md-3">
            {{ Form::label('challan_id', __('Challan No.'), ['class' => 'form-label']) }}
            {{ Form::text('challan_id', null, ['class' => 'form-control', 'readonly']) }}
        </div>
        <div class="col-md-3">
            {{ Form::label('recipt_amount', __('Receipt Amount'), ['class' => 'form-label']) }}
            {{ Form::number('recipt_amount', $recipt->recipt_amount, ['class' => 'form-control', 'id' => 'recipt_amount', 'required', 'readonly' => 'readonly']) }}
            @error('recipt_amount')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            {{ Form::label('challan_amount', __('Challan Amt'), ['class' => 'form-label']) }}
            {{ Form::text('challan_amount', null, ['class' => 'form-control', 'readonly']) }}
        </div>
    </div>

    <table class="table mb-0">
        <thead class="table_heads">
            <tr>
                <th>{{ __('Fee Head') }}</th>
                <th>{{ __('Challan Amt') }}</th>
                <th>{{ __('Already Paid') }}</th>
                <th>{{ __('This Receipt') }}</th>
                <th>{{ __('Max Credit.') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($voucher as $vo)
                @php
                    // find the matching challan head
                    $challanHead = \App\Models\ChallanHead::where('challan_id', $recipt->challan_id)
                        ->where('head_id', $vo->head)
                        ->first();
                @endphp
                @if ($challanHead)
                    <tr>
                        <td>{{ $challanHead->feeHead->fee_head }}</td>
                        <td>{{ number_format($challanHead->price, 2) }}</td>
                        <td>{{ number_format($challanHead->paid, 2) }}</td>
                        {{ Form::hidden("items[{$vo->id}][journal_item_id]", $vo->id) }}
                        {{ Form::hidden("items[{$vo->id}][challan_head_id]", $challanHead->id) }}
                        {{ Form::hidden("items[{$vo->id}][voucher_old]", $vo->credit) }}
                        <td>
                           {{ Form::number("items[{$vo->id}][credit]", old("items.{$vo->id}.credit", $vo->credit), [
                                    'class' => 'form-control receipt-credit',
                                    'min' => 0,
                                    'max' => ($challanHead->price - $challanHead->paid) +$vo->credit,
                                    'step' => 1,
                                    'data-max' => ($challanHead->price - $challanHead->paid) +$vo->credit,
                                    'data-paid' => $vo->credit,
                                ]) }}

                        </td>
                        <td class="remaining-balance" data-id="{{ $vo->id }}">
                            {{ number_format(($challanHead->price - $challanHead->paid) , 2) }}
                        </td>

                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="mt-3 text-end">
        {{ Form::submit(__('Update Receipt'), ['class' => 'btn btn-primary']) }}
    </div>
</div>

{{ Form::close() }}

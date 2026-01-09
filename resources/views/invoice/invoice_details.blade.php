@if(@$ware_products)
    <tr>

        <td width="25%" class="form-group pt-0">
            <select name="item" class="form-control select2 item" data-url="{{route('invoice.product')}}"  required>
                @foreach ($ware_products as $item)
                    <option value="{{ $item['id'] }}" {{ $item['quantity'] == 0 ? 'disabled' : '' }}>
                        {{ $item['name'] }}
                    </option>
                @endforeach
            </select>

        </td>
        <td>
            <div class="form-group price-input input-group search-form">
                {{ Form::text('quantity','1', array('class' => 'form-control quantity','required'=>'required','placeholder'=>__('Qty'),'required'=>'required')) }}
                <span class="unit input-group-text bg-transparent"></span>
            </div>
        </td>

        <td>
            <div class="form-group price-input input-group search-form">
                {{ Form::text('price',0, array('class' => 'form-control price','required'=>'required','placeholder'=>__('Price'),'required'=>'required')) }}
                <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
            </div>
        </td>
        <td>
            <div class="form-group price-input input-group search-form">
                {{ Form::text('discount',0, array('class' => 'form-control discount','required'=>'required','placeholder'=>__('Discount'))) }}
                <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
            </div>
        </td>



        <td>
            <div class="form-group">
                <div class="input-group colorpickerinput">
                    <div class="taxes"></div>
                    {{ Form::hidden('tax', '', array('class' => 'form-control tax text-dark')) }}
                    {{ Form::hidden('itemTaxPrice','', array('class' => 'form-control itemTaxPrice')) }}
                    {{ Form::hidden('itemTaxRate','', array('class' => 'form-control itemTaxRate')) }}
                </div>
            </div>
        </td>

        <td class="text-end amount"></td>
        {{-- <td>
            <a href="#" class="ti ti-trash text-white repeater-action-btn bg-danger ms-2 bs-pass-para" data-repeater-delete></a>
        </td> --}}
    </tr>
        {{-- <tr>
            <td colspan="2">
                <div class="form-group">
                    {{ Form::textarea('description', isset($product->description) ? $product->description : null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
                </div>
            </td>
            <td colspan="5"></td>
        </tr> --}}
@endif

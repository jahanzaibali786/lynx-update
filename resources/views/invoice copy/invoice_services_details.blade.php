@if(@$serv)
  <tr>
        <td width="25%" class="form-group pt-0">
    
            {{ Form::select('item', $servprod,'', array('class' => 'form-control select2 item','required'=>'required')) }}

        </td>
        <td>
            <div class="form-group price-input input-group search-form">
                {{ Form::text('quantity',$assign_room->count('chair_id'), array('class' => 'form-control quantity','required'=>'required','placeholder'=>__('Qty'),'required'=>'required')) }}
                <span class="unit input-group-text bg-transparent"></span>
            </div>
        </td>


        <td>
            <div class="form-group price-input input-group search-form">
                {{ Form::text('price',$contract_data->service_price/$assign_room->count('chair_id'), array('class' => 'form-control price','required'=>'required','placeholder'=>__('Price'),'required'=>'required')) }}
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
                    <div class="taxes">{!! @$taxes2 !!}</div>
                    {{ Form::hidden('tax', implode(',', $data2['tax']), array('class' => 'form-control tax text-dark')) }}
                    {{ Form::hidden('itemTaxPrice',$data2['itemTaxPrice'], array('class' => 'form-control itemTaxPrice')) }}
                    {{ Form::hidden('itemTaxRate',$data2['totalItemTaxRate'], array('class' => 'form-control itemTaxRate')) }}
                </div>
            </div>
        </td>

        <td class="text-end amount">{{$data2['total']}}</td>
        {{-- <td>
            <a href="#" class="ti ti-trash text-white repeater-action-btn bg-danger ms-2 bs-pass-para" data-repeater-delete></a>
        </td> --}}
    </tr>
    <tr>
        <td colspan="2">
            <div class="form-group">
                {{ Form::textarea('description', isset($serv->description) ? $serv->description : null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
            </div>
        </td>
        <td colspan="5"></td>
    </tr>
@endif
@if(@$serv2)
  <tr>
        <td width="25%" class="form-group pt-0">
    
            {{ Form::select('item', $servprod,'', array('class' => 'form-control select2 item','required'=>'required')) }}

        </td>
        <td>
            <div class="form-group price-input input-group search-form">
                {{ Form::text('quantity',1, array('class' => 'form-control quantity','required'=>'required','placeholder'=>__('Qty'),'required'=>'required','readonly'=>'readonly')) }}
                <span class="unit input-group-text bg-transparent"></span>
            </div>
        </td>


        <td>
            <div class="form-group price-input input-group search-form">
                {{ Form::text('price',$contract_data->security_deposit_price, array('class' => 'form-control price','required'=>'required','placeholder'=>__('Price'),'required'=>'required')) }}
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
                    <div class="taxes">{!! @$taxes2 !!}</div>
                    {{ Form::hidden('tax', implode(',', $data2['tax']), array('class' => 'form-control tax text-dark')) }}
                    {{ Form::hidden('itemTaxPrice',$data2['itemTaxPrice'], array('class' => 'form-control itemTaxPrice')) }}
                    {{ Form::hidden('itemTaxRate',$data2['totalItemTaxRate'], array('class' => 'form-control itemTaxRate')) }}
                </div>
            </div>
        </td>

        <td class="text-end amount">{{$data2['total']}}</td>
        {{-- <td>
            <a href="#" class="ti ti-trash text-white repeater-action-btn bg-danger ms-2 bs-pass-para" data-repeater-delete></a>
        </td> --}}
    </tr>
    <tr>
        <td colspan="2">
            <div class="form-group">
                {{ Form::textarea('description', isset($serv2->description) ? $serv2->description : null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
            </div>
        </td>
        <td colspan="5"></td>
    </tr>
@endif
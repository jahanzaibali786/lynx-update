@php
$options = ['DD', 'OL', 'CHQ', 'CD'];
@endphp
{{-- @foreach ($reciptss as $recipt) --}}
    <tr style="  border-radius: 10px !important;">
        <td>
            <input type="text" value="{{ @$recipts->id }}" disabled style="width:50px; font-size: 11px;">
        </td>
        <td>
            <input type="text" value="{{ date('d/m/Y', strtotime(@$recipts->recipt_date)) }}" disabled class="font_less"  style="width:63px; font-size: 11px;">
        </td>
        <td>
            <input type="text" value="{{ @$recipts->challan->challanNo }}" disabled style="width:60px; ">
        </td>
        <td>
            <input type="text" value="{{ @$recipts->recipt_amount }}" disabled style="width:60px; font-size: 13px;">
        </td>
        <td>
            <input type="text" value="{{ @$recipts->challan_amount }}" disabled style="width:65px; font-size: 13px;">
        </td>
        <td>
            <input type="text" value="{{ @$recipts->late_amount ?? 0 }}" disabled style="width:50px; font-size: 13px;">
        </td>
        <td>
            <input type="text" value="{{ @$recipts->challan_amount + @$recipts->late_amount + @$recipts->arrears }}"
                disabled style="width:60px; font-size: 12px;">
        </td>
        <td>
            <input type="text"
                value="{{ @$recipts->challan_amount + @$recipts->late_amount + @$recipts->arrears - (@$recipts->remaining_fee ?? (@$recipts->challan->paid_amount ?? 0)) }}"
                disabled style="width:65px; font-size: 12px;">
        </td>
        
        <td>
            <input type="text"
                value="{{ @$recipts->remaining_fee }}"
                disabled style="width:65px; font-size: 12px;">
        </td>

        {{-- <td>
            <input type="text" value="RV" disabled style="width:50px; font-size: 13px;">
        </td> --}}
        <td>
            {{ Form::select('default_bank', $accounts, @$recipts->bank_id, ['style' => 'width:100px; font-size: 12px;', 'disabled' => 'disabled']) }}
        </td>
        <td>
            <select class="input" disabled>
                @foreach ($options as $option)
                <option value="{{$option}}" {{ $option == @$recipts->receive_type ? 'selected' : '' }} > {{$option}}
                </option>
                @endforeach>
            </select>

        </td>
        <td>
            <input type="text" value="{{ @$recipts->referance }}" disabled style="width:80px; font-size: 11px;">
        </td>
        <td>
            <input type="text" value="{{@$recipts->received->name}}" style="width:100px; font-size: 11px;" disabled>
        </td>
        

    </tr>

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
                value="{{ @$recipts->challan_amount + @$recipts->late_amount + @$recipts->arrears - @$recipts->recipt_amount }}"
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
{{-- @endforeach --}}
<tr id="focus_row" style="  border-radius: 10px !important;">
    <td>
        <input type="text" value="" disabled style="width:50px; font-size: 11px;">
    </td>
    <td>
        {{-- {{ Form::date('date', date('Y-m-d'),['class' => 'form-control']) }} --}}
        <input type="date" value="{{ date('Y-m-d') }}" id="recipt_date" class="font_less"
            style="width:70px; font-size: 11px;">
    </td>
    <td>
        <input type="text" id="challan_id" value="" style="width:60px; ">
    </td>
    <td>
        <input type="text" value="" id="remp_amt" disabled style="width:60px; font-size: 13px;">
    </td>
    <td>
        <input type="text" id="challan_amt" value="" disabled style="width:65px; font-size: 13px;">
    </td>
    <td>
        <input type="text" id="late_amt" value="0" disabled
            style="width:50px; font-size: 13px;">
    </td>
    <td>
        <input type="text" id="total_fee" value="" disabled
            style="width:60px; font-size: 12px;">
    </td>
    <td>
        <input type="text" id="rem_fee" value="" disabled
            style="width:65px; font-size: 12px;">
    </td>
    {{-- <td>
        <input type="text" value="RV" disabled style="width:50px; font-size: 13px;">
    </td> --}}
    <td>
        {{ Form::select('default_bank', $accounts, null, [ 'style' => 'width:100px; font-size: 12px;','disabled'=>'disabled']) }}
    </td>
    <td>
        <select class="input" disabled name="receive_type">
            <option value="DD">DD</option>
            <option value="OL">OL</option>
            <option value="CHQ">CHQ</option>
            <option value="CD">CD</option>
        </select>
    </td>
    <td>
        <input type="text" value="" disabled id="ref" style="width:80px; font-size: 11px;">
    </td>
    <td>
        <input type="text" value="{{Auth::user()->name}}" style="width:100px; font-size: 11px;" disabled>
    </td>
</tr>

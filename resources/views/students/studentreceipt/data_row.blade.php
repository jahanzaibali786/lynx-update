@php
    $options = ['DD', 'OL', 'CHQ', 'CD'];

    $totalFee = @$recipts->challan_amount + @$recipts->late_amount + @$recipts->arrears;

    $remainingFee = $totalFee - @$recipts->recipt_amount;
@endphp

{{-- @foreach ($reciptss as $recipt) --}}

<tr style="border-radius:10px !important;">

    <td>
        <span style="font-size:11px;">
            {{ @$recipts->id }}
        </span>
    </td>

    <td>
        <span class="font_less" style="font-size:11px;">
            {{ date('d/m/Y', strtotime(@$recipts->recipt_date)) }}
        </span>
    </td>

    <td>
        <span style="font-size:12px;">
            {{ @$recipts->challan->challanNo }}
        </span>
    </td>

    <td>
        <span style="font-size:13px;">
            {{ @$recipts->recipt_amount }}
        </span>
    </td>

    <td>
        <span style="font-size:13px;">
            {{ @$recipts->challan_amount }}
        </span>
    </td>

    <td>
        <span style="font-size:13px;">
            {{ @$recipts->late_amount }}
        </span>
    </td>

    <td>
        <span style="font-size:13px;">
            {{ @$recipts->arrears }}
        </span>
    </td>

    <td>
        <span style="font-size:12px;">
            {{ $totalFee }}
        </span>
    </td>

    <td>
        <span style="font-size:12px;">
            {{ $remainingFee }}
        </span>
    </td>

    <td>
        <span style="font-size:12px;">
            {{ $accounts[@$recipts->bank_id] ?? '-' }}
        </span>
    </td>

    <td>
        <span style="font-size:12px;">
            {{ @$recipts->receive_type }}
        </span>
    </td>

    <td>
        <span style="font-size:11px;">
            {{ @$recipts->referance }}
        </span>
    </td>

    <td>
        <span style="font-size:11px;">
            {{ @$recipts->received->name }}
        </span>
    </td>

</tr>

{{-- @endforeach --}}

{{-- <tr id="focus_row" style="  border-radius: 10px !important;">
    <td>
        <input type="text" value="" disabled style="width:50px; font-size: 11px;">
    </td>
    <td>
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

        <input type="text" id="arrears" value="" disabled
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
</tr> --}}
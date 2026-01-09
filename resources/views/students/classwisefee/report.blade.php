<style>
    table,tr,th,td{
        border: 1px solid black;
        border-collapse: collapse;
    }

</style>

        @php
            $i=1;
        @endphp
        @foreach ($class_wise_fee as $branchId => $students)
        <div class="" style="width: 100%; margin-top: 20px;">
            <span style="font-size:1rem; font-weight:600; padding:10px; width:100%;">
                {{ @$branches[@$branchId] ?? 'All Branches' }} </span>
                <table  style="width:100%; font-size:0.9rem;">
                    <thead>
                        <tr style="background-color:grey; font-size:0.9rem;">
                            <th style="width:5%;">{{__('Sr No')}}</th>
                            <th style="width:5%">{{__('Class')}}</th>
                            <th style="width:5%;">{{__('Account Fee Head.')}}</th>
                            <th style="width:10%;">{{__('Amount')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $index => $reports)
        <tr style="font-size:0.7rem;">
            <td  style="width:5%;">{{ $i }}</td>
            <td  style="width:5%">{{ @$reports->class->name}}</td>
            <td class="font-style"  style="width:5%;">{{ @$reports->feehead->fee_head }}</td>
            <td class="font-style" style="width:10%;">{{ @$reports->amount }}</td>
            
        </tr>
        @php
        $i++;
    @endphp
    @endforeach
</tbody>
</table>
@endforeach 



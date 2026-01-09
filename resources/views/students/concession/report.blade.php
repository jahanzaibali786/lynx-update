<style>
    table,tr,th,td{
        border: 1px solid black;
        border-collapse: collapse;
    }

</style>

        @php
            $i=1;
        @endphp
        @foreach ($concessions as $branchId => $students)
        <div class="" style="width: 100%; margin-top: 20px;">
            {{-- @dd($branches_name, $branchId) --}}
            <span style="font-size:1rem; font-weight:600; padding:10px; width:100%;">
                {{ @$branches_name[@$branchId] ?? 'All Branches' }} </span>
                <table  style="width:100%; font-size:0.9rem;">
                    <thead>
                        <tr style="background-color:grey; font-size:0.9rem;">
                            <th style="width:5%;">{{__('Sr No')}}</th>
                            <th style="width:5%">{{__('B Sr No.')}}</th>
                            <th style="width:5%;">{{__('Roll No.')}}</th>
                            <th style="width:10%;">{{__('Student Name')}}</th>
                            <th style="width:10%;">{{__('Father Name')}}</th>
                            <th style="width:5%;">{{__('Class Name')}}</th>
                            <th style="width:40%;">{{__('Concession')}}</th>
                            <th style="width:5%;">{{__('Applied')}}</th>
                            <th style="width:5%;">{{__('Period From') }}</th>
                            <th style="width:5%;">{{__('Period To') }}</th>
                            <th style="width:5%;">{{__('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $index => $reports)
        {{-- @dd($student) --}}
        <tr style="font-size:0.7rem;">
            <td  style="width:5%;">{{ $i }}</td>
            <td  style="width:5%">{{ $index + 1 }}</td>
            <td class="font-style"  style="width:5%;">{{ @$reports->student_id }}</td>
            <td class="font-style" style="width:10%;">{{ @$reports->student->stdname }}</td>
            <td class="font-style" style="width:10%;">{{ @$reports->student->fathername }}</td>
            <td class="font-style"  style="width:5%;">{{ @$reports->class->name }}</td>
            <td class="font-style" style="width:40%;">{{ @$reports->concession->title }}</td>
            <td class="font-style"  style="width:5%;">{{ @$reports->apply_date }}</td>
            <td class="font-style"  style="width:5%;">{{ @$reports->start_date }}</td>
            <td class="font-style"  style="width:5%;">{{ @$reports->end_date }}</td>
            <td class="font-style"  style="width:5%;">{{ @$reports->status }}</td>
            
        </tr>
        @php
        $i++;
    @endphp
    @endforeach
</tbody>
</table>
@endforeach

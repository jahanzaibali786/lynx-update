{{ Form::model($Concession, array('route' => array('concession.update', $Concession->id), 'method' => 'PUT', 'id' => 'concessionFormedit')) }}
<div class="modal-body">
    <div class="row">
        @for ($i=0; $i<3; $i++)
        <div class="col-4">
            <div class="row">
                <div class="col-7">
                    <div class="form-group">
                        {{ Form::label('title', __('Title'),['class'=>'form-label']) }}
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        {{ Form::label('concession', __('Concession %'),['class'=>'form-label']) }}
                    </div>
                </div>
            </div>
        </div>
        @endfor
        @foreach ($heads as $account)
            @if($account->status == 0)
                {{ Form::hidden('title_id[]', $account->id, array('class' => 'form-control','placeholder'=>__('Enter Title'))) }}
                {{ Form::hidden('head_name[]', $account->fee_head, array('class' => 'form-control','placeholder'=>__('Enter Title'),'readonly'=>'readonly')) }}
                {{ Form::hidden('concession[]', 0, array('class' => 'form-control', 'step' => 'any')) }}
            @else

                <div class="col-4">
                    <div class="row">
                    <div class="col-7">
                        <div class="form-group">
                            {{ Form::hidden('title_id[]', $account->id, array('class' => 'form-control','placeholder'=>__('Enter Title'))) }}
                            {{ Form::text('head_name[]', $account->fee_head, array('class' => 'form-control','placeholder'=>__('Enter Title'),'readonly'=>'readonly')) }}
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            {{ Form::number('concession[]', 0, array('class' => 'form-control', 'step' => 'any','min' => '0')) }}
                        </div>
                    </div>
                    </div>
                </div>
            @endif
        @endforeach
        <div class="col-4" style="float: right;">
            {{ Form::button('Search', ['id' => 'search', 'class' => 'btn btn-sm btn-primary']) }}
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-4">
            <div class="form-group" id="re_edit">
                {{ Form::label('concession_id', __('Concession Policy'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('concession_id', $concession_policy, null, ['class' => 'form-control select js-searchBox' ,'id' => 'conc']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('period_from', __('Period From'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('period_from', $Concession->start_date, array('class' => 'form-control ','placeholder'=>__('Enter Period From'),'required'=>'required')) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('period_to', __('Period To'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('period_to', $Concession->end_date, array('class' => 'form-control ','placeholder'=>__('Enter Period To'))) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select', 'required' => 'required','readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('class_id', __('Class'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_id', $classes, null, ['class' => 'form-control select', 'id' => 'class_id', 'required' => 'required','readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('student_id', __('Students'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('student_id', $student, null, ['class' => 'form-control select','id' => 'class_students','readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-12">
            <div id="student-details" class="mt-3"></div>
        </div>
        <div class="col-4 av d-none">
            <div class="form-group">
                {{ Form::label('cancle_date', __('Prev Cancle Date'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{ Form::date('cancle_date', null, ['class' => 'form-control ', 'placeholder' => __('Enter Date')]) }}
            </div>
        </div>
        <div class="col-8 av d-none">
            <div class="form-group">
                {{ Form::label('cancle_remarks', __('Prev Cancle Remarks'), ['class' => 'form-label']) }}
                {{ Form::textarea('cancle_remarks', null, ['class' => 'form-control', 'placeholder' => __('Enter Cancle Remarks'), 'rows' => 2]) }}
            </div>
        </div>
        {{-- <div class="col-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('date', $Concession->apply_date, array('class' => 'form-control ','placeholder'=>__('Enter Date'),'required'=>'required')) }}
            </div>
        </div> --}}
        <div class="col">
            <div class="form-group">
                {{ Form::label('bill_remarks', __('Bill Remarks'),['class'=>'form-label']) }}
                {{ Form::textarea('bill_remarks', $Concession->remarks, array('class' => 'form-control','placeholder'=>__('Enter Bill Remarks'),'rows' => 3)) }}
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>

{{Form::close()}}

<script>
    JsSearchBox();

    // document.getElementById('search').addEventListener('click', function() {
    //     // Get the form element
    //     var form = document.getElementById('concessionFormedit');

    //     // Create a FormData object from the form
    //     var formData = new FormData(form);

    //     // Send the form data using fetch
    //     fetch('{{ url('concession_list') }}', {
    //         method: 'POST',
    //         body: formData,
    //         headers: {
    //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //         }
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //     // Handle the server response here

    //     // Update the dropdown
    //     const dropdown = document.getElementById('conc');
    //     if(data == ''){
    //         show_toastr('danger', 'No Concession Policy Found', 'danger');
    //     }else{

    //     }
    //     dropdown.innerHTML = ''; // Clear existing options and add a default option
    //     data.forEach(policy => {
    //         const option = document.createElement('option');
    //         option.value = policy.id;
    //         option.textContent = policy.title;
    //         dropdown.appendChild(option);
    //     });
    // })
    // });
</script>

<script>
    document.getElementById('search').addEventListener('click', function() {
        // Get the form element
        var form = document.getElementById('concessionFormedit');

        // Create a FormData object from the form
        var formData = new FormData(form);
            formData.set('_method', 'post');
       
        // Send the form data using fetch
        fetch('{{ url('concession_list') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
        // Handle the server response here
        document.getElementById('re_edit').innerHTML = `
                <label for="concession_id" class="form-label">
                    {{ __('Concession Policy') }} <span style="color: red"> *</span>
                </label>
                <select name="concession_id" id="conc" class="form-control">
                </select>
            `;

        // Update the dropdown
        const dropdown = document.getElementById('conc');
        if(data == ''){
            show_toastr('danger', 'No Concession Policy Found', 'danger');
        }else{

        }
        console.log(data);
        dropdown.innerHTML = ''; // Clear existing options and add a default option
        data.forEach(policy => {
            const option = document.createElement('option');
            option.value = policy.id;
            option.textContent = policy.title;
            dropdown.appendChild(option);
        });
    })
    });    
    fetchStudentDetails({{ $Concession->student_id }});
</script>
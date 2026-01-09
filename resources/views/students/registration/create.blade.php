@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Registration') }}
@endsection
@push('script-page')
    <script></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('registration.index') }}">{{ __('All Registrations') }}</a></li>
    <li class="breadcrumb-item">{{ __('New Registration') }}</li>
@endsection
@section('content')
    <div class="card mt-6 p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {!! Form::open(['route' => 'registration.store', 'method' => 'POST']) !!}
        {!! csrf_field() !!}
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('regdate', __('Registration Date'), ['class' => 'form-label']) }}<span
                        style="color: red">*</span>
                    {{ Form::date('regdate', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="col-md-6">
                    {{ Form::label('stdname', __('Student Name'), ['class' => 'form-label']) }}<span style="color: red">
                        *</span>
                    {{ Form::text('stdname', null, ['class' => 'form-control', 'placeholder' => __('Student name'), 'style' => 'text-transform: uppercase;', 'required' => 'required']) }}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('dob', __('D.O.B'), ['class' => 'form-label']) }}<span style="color: red"> *
                    </span><small id="age" style="position: absolute; margin-left: 5px;"> </small>
                    {{ Form::date('dob', null, ['class' => 'form-control', 'id' => 'dob', 'required' => 'required', 'max' => date('Y-m-d')]) }}
                </div>
                <div class="col-md-6">
                    {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span style="color: red"> *</span>
                    {!! Form::select('gender', ['' => 'Select Gender', 'male' => 'Male', 'female' => 'Female'], null, [
                        'class' => 'form-control',
                        'required' => 'required',
                    ]) !!}
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('fathername', __('Father Name'), ['class' => 'form-label']) }}<span style="color: red">
                        *</span>
                    {{ Form::text('fathername', null, ['class' => 'form-control', 'id' => 'fathername', 'placeholder' => __('Father name'), 'required' => 'required']) }}
                </div>
                <div class="col-md-6">
                    {!! Form::label('fathercnic', __('Father CNIC'), ['class' => 'form-label']) !!}
                    <span
                        style="color: red"> *</span>
                    {!! Form::text('fathercnic', null, ['class' => 'form-control', 'id' => 'fathercnic']) !!}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('fathercell', __('Cell'), ['class' => 'form-label']) }}<span
                        style="color: red"> *</span>
                    {{ Form::text('fathercell', null, ['class' => 'form-control', 'id' => 'fathercell', 'placeholder' => '03xxxxxxxxx,03xxxxxxxxx']) }}
                </div>
                <div class="col-md-6">
                    {{ Form::label('fatherphone', __('Phone'), ['class' => 'form-label']) }}
                    {{ Form::text('fatherphone', null, ['class' => 'form-control', 'id' => 'fatherphone', 'placeholder' => '03xxxxxxxxx']) }}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('fatherprofession', __('Father Profession'), ['class' => 'form-label']) }}<span
                        style="color: red"> *</span>
                    {{ Form::text('fatherprofession', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-md-6">
                    {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                    {{ Form::text('city', null, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('mothername', __('Mother Name'), ['class' => 'form-label']) }}
                    {{ Form::text('mothername', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-md-6">
                    {!! Form::label('mothercnic', __('Mother CNIC'), ['class' => 'form-label']) !!} <span
                        style="color: red"> *</span>
                    {!! Form::text('mothercnic', null, ['class' => 'form-control', 'id' => 'mothercnic']) !!}
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('motherprofession', __('Mother Profession'), ['class' => 'form-label']) }}
                    {{ Form::text('motherprofession', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-md-6">
                    {{ Form::label('prevschool', __('Previous School'), ['class' => 'form-label']) }}
                    {{ Form::text('prevschool', null, ['class' => 'form-control', 'placeholder' => __('Previous School')]) }}
                </div>

            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('prevclass', __('Previous Class'), ['class' => 'form-label']) }}
                    {{ Form::text('prevclass', null, ['class' => 'form-control', 'placeholder' => __('Previous Class')]) }}
                </div>
                <div class="col-md-6">
                    {!! Form::label('register_option', __('Register Option'), ['class' => 'form-label']) !!}<span style="color: red"> *</span>
                    {!! Form::select('register_option', $registerOption, null, [
                        'class' => 'form-control',
                        'required' => 'required',
                    ]) !!}
                </div>
                

                {{-- Hidden wrapper for additional fields --}}


                {{-- <div class="col-md-6">
                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Email')]) }}
            </div> --}}
            </div>
        </div>
        @php
            $userType = Auth::user()->type;
            $userId = Auth::user()->id;

            if ($userType == 'branch') {
                $id = $userId;
            } elseif ($userType == 'company') {
                $id = $userId;
            } else {
                $id = Auth::user()->owned_by;
            }
        @endphp

        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('branchname', __('Branch'), ['class' => 'form-label']) }}<span style="color: red">
                        *</span>
                    <select name="branch" id="branch" class="form-control select" required>
                        <option value="" selected disabled>Select Branch</option>
                        @foreach ($branch as $key => $values)
                            <option value="{{ $key }}" {{ $key == $id ? 'selected' : '' }}>{{ $values }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}<span style="color: red">*</span>
                    {!! Form::select('class_id', ['' => 'Select Class'], null, [
                        'class' => 'form-control',
                        'id' => 'class_id',
                        'required' => 'required',
                    ]) !!}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}<span style="color: red">
                        *</span>
                    <select name="session_id" class="form-control" required>
                        {{-- <option value="" selected disabled>Select Session</option> --}}
                        @foreach ($session as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    {{ Form::label('religion', __('Religion'), ['class' => 'form-label']) }}<span style="color: red">
                        *</span>
                    {{ Form::text('religion', null, ['class' => 'form-control', 'placeholder' => __('Religion'), 'required' => 'required']) }}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">

                <div class="col-md-6">
                    {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                    {{ Form::textarea('address', null, ['class' => 'form-control', 'rows' => 1, 'placeholder' => __('Address'), 'style' => 'text-transform: uppercase;']) }}
                </div>

                <div class="col-md-6">
                    {!! Form::label('remarks', __('Remarks'), ['class' => 'form-label']) !!}
                    {!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 1]) !!}
                </div>
            </div>
        </div>
        <br>
        <div class="form-group">
                    <div class="form-check">
                        {!! Form::checkbox('legal_custody', 1, false, [
                            'class' => 'form-check-input',
                            'id' => 'legal_custody_checkbox',
                        ]) !!}
                        {!! Form::label('legal_custody_checkbox', __('Legal Custody'), [
                            'class' => 'form-check-label',
                        ]) !!}
                    </div>
                </div>
        <div id="legal_custody_fields" style="display: none;">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-4">
                        {{ Form::label('custody_name', __('Custody Holder Name'), ['class' => 'form-label']) }}
                      <span
                        style="color: red"> *</span>  {{ Form::text('custody_name', null, [
                            'class' => 'form-control custody-dependent',
                            'placeholder' => __('Name'),
                        ]) }}
                    </div>
                    <div class="col-md-4">
                        {{ Form::label('custody_cnic', __('Custody Holder CNIC'), ['class' => 'form-label']) }}
                     <span
                        style="color: red"> *</span>   {{ Form::text('custody_cnic', null, [
                            'class' => 'form-control custody-dependent',
                            'placeholder' => __('XXXXX-XXXXXXX-X'),
                        ]) }}
                    </div>
                    <div class="col-md-4">
                        {{ Form::label('custody_relation', __('Relation'), ['class' => 'form-label']) }}
                    <span
                        style="color: red"> *</span>    {{ Form::text('custody_relation', null, [
                            'class' => 'form-control custody-dependent',
                            'placeholder' => __('e.g. Uncle, Aunt…'),
                        ]) }}
                    </div>
                </div>
            </div>
        </div>
        <table id="siblingtable" style="display: none;">
            <thead>
                <tr class="table_heads">
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Branch</th>
                    <th>Class</th>
                    <th>Fee</th>
                    <th>Disount %</th>
                    <th>Pay Fee</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <div class="form-group">
            <div class="row mt-2" style="float: right; margin-right: 3px;">
                <input type="submit" value="{{ __('Create') }}" class="btn btn-outline-primary">
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('legal_custody_checkbox');
            const wrapper = document.getElementById('legal_custody_fields');
            const inputs = wrapper.querySelectorAll('.custody-dependent');

            function toggleCustodyFields() {
                if (checkbox.checked) {
                    wrapper.style.display = 'block';
                    inputs.forEach(i => i.setAttribute('required', 'required'));
                } else {
                    wrapper.style.display = 'none';
                    inputs.forEach(i => i.removeAttribute('required'));
                }
            }

            // init on load (in case of old input)
            toggleCustodyFields();

            // listen for changes
            checkbox.addEventListener('change', toggleCustodyFields);
        });
    </script>
    <script>
        document.getElementById('dob').addEventListener('change', function() {
            const dob = this.value;
            document.getElementById('age').innerText = calculateAge(dob);
        });

        function calculateAge(dob) {
            const today = new Date();
            const birthDate = new Date(dob);

            let years = today.getFullYear() - birthDate.getFullYear();
            let months = today.getMonth() - birthDate.getMonth();
            let days = today.getDate() - birthDate.getDate();

            // Adjust the years and months if necessary
            if (months < 0 || (months === 0 && days < 0)) {
                years--;
                months += 12;
            }

            // Adjust the days if necessary
            if (days < 0) {
                months--;
                const prevMonth = (today.getMonth() + 11) % 12;
                const daysInPrevMonth = new Date(today.getFullYear(), prevMonth + 1, 0).getDate();
                days += daysInPrevMonth;
            }

            return `( ${years} years, ${months} months, and ${days} days )`;
        }

        function formatCNIC(input) {
            var value = input.value.replace(/\D/g, '');
            if (value.length > 13) {
                value = value.substring(0, 13);
            }
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5);
            }
            if (value.length > 13) {
                value = value.substring(0, 13) + '-' + value.substring(13);
            }
            input.value = value;
        }

        document.getElementById('fathercell').addEventListener('keyup', function() {
            // Remove all non-digit characters
            var value = this.value.replace(/\D/g, '');
            // Limit the length to 23 digits
            if (value.length > 44) {
                value = value.substring(0, 44);
            }
            // Insert commas after the 11th and 23rd digits if applicable
            if (value.length > 11) {
                value = value.substring(0, 11) + ',' + value.substring(11);
            }
            if (value.length > 23) {
                value = value.substring(0, 23) + ',' + value.substring(23);
            }
            if (value.length > 35) {
                value = value.substring(0, 35) + ',' + value.substring(35);
            }
            // Update the input value with the formatted result
            this.value = value;
        });

        document.getElementById('fatherphone').addEventListener('keyup', function() {
            // Remove all non-digit characters
            var value = this.value.replace(/\D/g, '');
            // Limit the length to 23 digits
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            this.value = value;
        });



        document.getElementById('fathercnic').addEventListener('keyup', function() {
            formatCNIC(this);
            var fatherCnic = $(this).val();
            console.log(fatherCnic);
            if (fatherCnic.length >= 15) {
                $.ajax({
                    url: '{{ route('SiblingonFathercnic') }}',
                    type: 'GET',
                    data: {
                        fatherCnic: fatherCnic
                    },
                    success: function(response) {
                        if (response.siblings.length > 0) {
                            console.log(response);

                            populateSiblingTable(response.siblings, response.head);
                            $('#siblingtable').show();
                        } else {
                            $('#siblingtable').hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            }
        });


        function populateSiblingTable(siblings, head) {
            var tableBody = $('#siblingtable tbody');
            tableBody.empty();
            var dis = 0;
            siblings.forEach(function(sibling) {
                $.ajax({
                    url: '{{ route('get.concession') }}',
                    type: 'GET',
                    data: {
                        studentId: sibling.id
                    },
                    success: function(response) {
                        const fee_data = sibling.fee_structure.find(item => item.head_id === head.id);
                        if (response == 0) {
                            dis = fee_data.amount;
                        } else {
                            dis = Math.round(fee_data.amount - (response / 100) * fee_data.amount);
                            console.log(fee_data.amount / response);

                        }

                        var row = $('<tr>');
                        row.append($('<td>').text(sibling.roll_no));
                        row.append($('<td>').text(sibling.stdname));
                        row.append($('<td>').text(sibling.branches.name));
                        row.append($('<td>').text(sibling.class.name));
                        row.append($('<td>').text(fee_data.amount));
                        row.append($('<td>').text(response));
                        row.append($('<td>').text(dis));
                        tableBody.append(row);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });


            });
        }

        function fetchSubTotal(studentId) {

        }


        document.getElementById('mothercnic').addEventListener('keyup', function() {
            formatCNIC(this);
        });

        $(document).on('change', '#branch', function() {
            var branch = $(this).val();
            $.ajax({
                url: '{{ route('branch.class') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#class_id').empty();
                    $('#class_id').append(
                        '<option value="" selected disabled>{{ __('Select Class') }}</option>');

                    for (let index = 0; index < data.length; index++) {
                        $('#class_id').append('<option value="' + data[index]['id'] + '">' + data[index]
                            ['name'] + '</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + error); // Log AJAX errors for debugging
                }
            });
        });

        $(document).ready(function() {
            setTimeout(function() {
                var branch = $('#branch').val();
                $.ajax({
                    url: '{{ route('branch.class') }}',
                    type: 'POST',
                    data: {
                        "branch_id": branch,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {

                        $('#class_id').empty();
                        $('#class_id').append(
                            '<option value="" selected disabled>{{ __('Select Class') }}</option>'
                        );

                        for (let index = 0; index < data.length; index++) {
                            $('#class_id').append('<option value="' + data[index]['id'] + '">' +
                                data[index]['name'] + '</option>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + error); // Log AJAX errors for debugging
                    }
                });
            }, 2000); // Delay of 1000 milliseconds (1 second)
        });
    </script>


@endsection

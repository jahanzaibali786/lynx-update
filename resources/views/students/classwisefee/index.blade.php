@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Fee Structure') }}
@endsection
@push('script-page')
    <script>
        function branchcustomer(id) {
            var customer = $('#customerselect').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {
                    // console.log(result);
                    if (result.status == 'success') {
                        // $('#sessionselect').empty();
                        // $('#sessionselect').append($('<option>', {
                        //     value: '',
                        //     text: 'Select Session'
                        // }));
                        // for (var i = 0; i < result.session.length; i++) {
                        //     var session = result.session[i];
                        //     $('#sessionselect').append($('<option>', {
                        //         value: session.id, text: session.title
                        //     }));
                        // }
                        $('#class_select').empty();
                        $('#class_select').append($('<option>', {
                            value: '',
                            text: 'Select Class'
                        }));

                        for (var j = 0; j < result.class.length; j++) {
                            var cls = result.class[j];
                            $('#class_select').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }
                    if (result.status == 'error') {}

                }
            });
            // Add more code as needed
        }

        function printReport() {
            var form = document.getElementById('class_wise_fee_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('class_wise_fee.report') }}?" + queryString,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], {
                        type: 'application/pdf'
                    });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"> {{ __('Fee Structure') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">

    </div>
@endsection
@section('content')
    {{-- @if (\Auth::user()->type == 'company') --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['class_wise_fee.index'], 'method' => 'GET', 'id' => 'class_wise_fee_submit']) }}
                        <div class="row d-flex justify-content-end ">

                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                                    {{ Form::select('session', $session, isset($_GET['session']) ? $_GET['session'] : '', ['class' => 'form-control select', 'id' => 'sessionselect', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>

                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, isset($_GET['class']) ? $_GET['class'] : '', ['class' => 'form-control select', 'id' => 'class_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('type', __('Structure Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('type', $structureTypes, isset($_GET['type']) ? $_GET['type'] : 'regular', ['class' => 'form-control select']) }}
                                </div>
                            </div>

                            <div class="col-auto float-end ms-2 mt-4">
                                <button type="submit" class="btn mx-1 btn-sm btn-outline-primary"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </button>
                                {{-- <a href="#" class="btn mx-1 btn-sm btn-outline-primary submit"
                                        onclick="document.getElementById('class_wise_fee_submit').submit(); return false;"
                                         data-bs-title="{{ __('Apply') }}">
                                    </a> --}}
                                <a href="{{ route('class_wise_fee.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <a href="#" onclick="printReport(); return false;"
                                    class="btn mx-1 btn-sm btn-outline-success" data-bs-title="Print">
                                    <span class="btn-inner--icon">Print
                                    </span>
                                </a>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @endif --}}
    {{ Form::open(['route' => ['class_wise_fee.store'], 'method' => 'POST', 'id' => 'class_fee_submit']) }}
    <div class="card">
        <div class="row p-3">
            <table class="datatable" style="width:100%; font-size:0.9rem;">
                <thead>
                    <tr class="table_heads" style="background-color:grey; font-size:0.6rem;">
                        <th style="width:5%;">{{ __('No.') }}</th>
                        <th style="width:10%;">{{ __('Type') }}</th>
                        <th style="width:65%;">{{ __('Account Fee Head') }}</th>
                        <th style="width:20%;">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($classfee))
                        @foreach ($heads as $account)
                            <tr style="font-size:0.8rem;">
                                <td style="text-align: center;">{{ $loop->iteration }}</td>
                                <td>{{ isset($_GET['type']) && $_GET['type'] === 'teacher_child' ? 'Teacher Child' : 'Regular' }}</td>
                                <td>{{ !empty($account->fee_head) ? @$account->fee_head : '-' }}</td>
                                <td>
                                    <input type="number" name="account_value[]" id="" style="max-width: 200px"
                                        placeholder="0"
                                        value="{{ isset($classfee[$account->id]) ? $classfee[$account->id] : 0 }}"
                                        class="form-control">
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        @if (!empty($classfee))
            @foreach ($heads as $account)
                <input type="hidden" name="account_id[]" value="{{ $account->id }}">
            @endforeach
        @endif
        <input type="hidden" name="branch" id=""
            value="{{ isset($_GET['branches']) ? $_GET['branches'] : '' }}">
        <input type="hidden" name="session" id="" value="{{ isset($_GET['session']) ? $_GET['session'] : '' }}">
        <input type="hidden" name="class" id="" value="{{ isset($_GET['class']) ? $_GET['class'] : '' }}">
        <input type="hidden" name="type" id="" value="{{ isset($_GET['type']) ? $_GET['type'] : 'regular' }}">

        @if (!empty($classfee))
            <div class="modal-footer p-3">
                <input type="submit" value="{{ __('Save') }}" class="btn  btn-outline-primary">
            </div>
        @endif
    </div>
    {{ Form::close() }}
@endsection

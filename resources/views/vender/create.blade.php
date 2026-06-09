
{{Form::open(array('url'=>'vender','method'=>'post'))}}
<div class="modal-body">

    <h6 class="sub-title"><strong>{{__('Basic Info')}}</strong></h6>
    <div class="row">
        <!-- Company Name -->
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{Form::label('company_name',__('COMPANY NAME'),array('class'=>'form-label')) }}
                {{Form::text('company_name',null,array('class'=>'form-control','placeholder'=>''))}}
            </div>
        </div>

        <!-- Full Name Section -->
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{Form::label('full_name',__('FULL NAME'),array('class'=>'form-label')) }}<span class="text-danger pl-1"> *</span>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12">
            <div class="form-group">
                {{Form::select('name_prefix',['Mr'=>'Mr.','Ms'=>'Ms.','Mrs'=>'Mrs.','Dr'=>'Dr.'],null,array('class'=>'form-control','placeholder'=>'Mr/Ms.'))}}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <div class="form-group">
                {{Form::text('first_name',null,array('class'=>'form-control','placeholder'=>'First','required'=>'required'))}}
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12">
            <div class="form-group">
                {{Form::text('middle_initial',null,array('class'=>'form-control','placeholder'=>'M.I.'))}}
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <div class="form-group">
                {{Form::text('last_name',null,array('class'=>'form-control','placeholder'=>'Last','required'=>'required'))}}
            </div>
        </div>

        <!-- Job Title -->
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{Form::label('job_title',__('JOB TITLE'),array('class'=>'form-label')) }}
                {{Form::text('job_title',null,array('class'=>'form-control','placeholder'=>''))}}
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Phone Numbers (2 fields) - All in one row -->
        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('main_phone_label',__('Main Phone'),array('class'=>'form-label')) }}<span class="text-danger"> *</span>
                {{Form::select('main_phone_type',[''=>'Main Phone','Work'=>'Work','Home'=>'Home','Mobile'=>'Mobile','Other'=>'Other'],null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('main_phone',__(''),array('class'=>'form-label','style'=>'visibility:hidden;')) }}
                {{Form::text('main_phone',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('work_phone_label',__('Work Phone'),array('class'=>'form-label')) }}
                {{Form::select('work_phone_type',[''=>'Work Phone','Work'=>'Work','Home'=>'Home','Mobile'=>'Mobile','Other'=>'Other'],null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('work_phone',__(''),array('class'=>'form-label','style'=>'visibility:hidden;')) }}
                {{Form::text('work_phone',null,array('class'=>'form-control'))}}
            </div>
        </div>

        <!-- Email Fields - All in one row -->
        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('main_email_label',__('Main Email'),array('class'=>'form-label')) }}<span class="text-danger"> *</span>
                {{Form::select('main_email_type',[''=>'Main Email','Work'=>'Work','Personal'=>'Personal','Other'=>'Other'],null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('main_email',__(''),array('class'=>'form-label','style'=>'visibility:hidden;')) }}
                {{Form::email('email',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('cc_email_label',__('CC Email'),array('class'=>'form-label')) }}
                {{Form::select('cc_email_type',[''=>'CC Email','Work'=>'Work','Personal'=>'Personal','Other'=>'Other'],null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('cc_email',__(''),array('class'=>'form-label','style'=>'visibility:hidden;')) }}
                {{Form::email('cc_email',null,array('class'=>'form-control'))}}
            </div>
        </div>

        <!-- Website and Other - All in one row -->
        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('website_label',__('Website'),array('class'=>'form-label')) }}
                {{Form::select('website_type',[''=>'Website','Work'=>'Work','Personal'=>'Personal','Other'=>'Other'],null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('website',__(''),array('class'=>'form-label','style'=>'visibility:hidden;')) }}
                {{Form::text('website',null,array('class'=>'form-control'))}}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('other1_label',__('Other 1'),array('class'=>'form-label')) }}
                {{Form::select('other1_type',[''=>'Other 1','Phone'=>'Phone','Email'=>'Email','Website'=>'Website','Other'=>'Other'],null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {{Form::label('other1',__(''),array('class'=>'form-label','style'=>'visibility:hidden;')) }}
                {{Form::text('other1',null,array('class'=>'form-control'))}}
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('account',__('Account'),array('class'=>'form-label')) }}<span class="text-danger"> *</span>
                <select name="account_id" class="form-control selectbox" required="required">
                    <option value="" selected disabled>Select Account</option>
                    @foreach ($accounts as $chartAccount)
                        <option value="{{ $chartAccount['id'] }}" class="subAccount" >{{$chartAccount['code'] .' - '. $chartAccount['name'] }}</option>
                        @foreach ($subAccounts as $subAccount)
                            @if ($chartAccount['id'] == $subAccount['account'])
                                <option value="{{ $subAccount['id'] }}" class="ms-5" > &nbsp; &nbsp;&nbsp; {{ $subAccount['code'] .' - '. $subAccount['name'] }}</option>
                            @endif
                        @endforeach
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('tax_number',__('Tax Number'),['class'=>'form-label'])}}
                {{Form::text('tax_number',null,array('class'=>'form-control'))}}
            </div>
        </div>
        @if(!$customFields->isEmpty())
            <div class="col-md-12">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customFields.formBuilder')
                </div>
            </div>
        @endif
    </div>
    <h6 class="sub-title"><strong>{{__('ADDRESS DETAILS')}}</strong></h6>
    <div class="row">
        <!-- Billed From Column -->
        <div class="col-lg-5 col-md-5 col-sm-12">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold mb-0">{{__('BILLED FROM')}}</h6>
                <button type="button" class="btn btn-sm btn-link p-0" onclick="editBillingAddress()">
                    <i class="ti ti-pencil"></i>
                </button>
            </div>
            <div class="form-group mt-2">
                {{Form::textarea('billing_address',null,array('class'=>'form-control','rows'=>4,'id'=>'billing_address','placeholder'=>''))}}
            </div>
        </div>

        <!-- Copy Button Column -->
        <div class="col-lg-2 col-md-2 col-sm-12 d-flex align-items-center justify-content-center">
            <button type="button" class="btn btn-outline-primary" onclick="copyAddress()" style="white-space: nowrap;">
                {{__('Copy >>')}}
            </button>
        </div>

        <!-- Shipped From Column -->
        <div class="col-lg-5 col-md-5 col-sm-12">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold mb-0">{{__('SHIPPED FROM')}}</h6>
                <button type="button" class="btn btn-sm btn-link p-0" onclick="editShippingAddress()">
                    <i class="ti ti-pencil"></i>
                </button>
            </div>
            <div class="form-group mt-2">
                {{Form::textarea('shipping_address',null,array('class'=>'form-control','rows'=>4,'id'=>'shipping_address','placeholder'=>''))}}
            </div>
        </div>
    </div>

    <!-- Hidden fields for detailed address info -->
    <input type="hidden" name="billing_name" id="billing_name">
    <input type="hidden" name="billing_phone" id="billing_phone">
    <input type="hidden" name="billing_city" id="billing_city">
    <input type="hidden" name="billing_state" id="billing_state">
    <input type="hidden" name="billing_country" id="billing_country">
    <input type="hidden" name="billing_zip" id="billing_zip">

    <input type="hidden" name="shipping_name" id="shipping_name">
    <input type="hidden" name="shipping_phone" id="shipping_phone">
    <input type="hidden" name="shipping_city" id="shipping_city">
    <input type="hidden" name="shipping_state" id="shipping_state">
    <input type="hidden" name="shipping_country" id="shipping_country">
    <input type="hidden" name="shipping_zip" id="shipping_zip">

</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-outline-primary">
</div>
{{Form::close()}}

<script>
    // Copy billing address to shipping address
    function copyAddress() {
        document.getElementById('shipping_address').value = document.getElementById('billing_address').value;
        document.getElementById('shipping_name').value = document.getElementById('billing_name').value;
        document.getElementById('shipping_phone').value = document.getElementById('billing_phone').value;
        document.getElementById('shipping_city').value = document.getElementById('billing_city').value;
        document.getElementById('shipping_state').value = document.getElementById('billing_state').value;
        document.getElementById('shipping_country').value = document.getElementById('billing_country').value;
        document.getElementById('shipping_zip').value = document.getElementById('billing_zip').value;
    }

    // Edit billing address (placeholder - you can add a modal or inline form)
    function editBillingAddress() {
        // This could open a modal with detailed fields
        alert('Edit billing address functionality - you can implement a modal here');
    }

    // Edit shipping address (placeholder - you can add a modal or inline form)
    function editShippingAddress() {
        // This could open a modal with detailed fields
        alert('Edit shipping address functionality - you can implement a modal here');
    }


</script>


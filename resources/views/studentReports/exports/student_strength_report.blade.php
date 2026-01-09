@extends('layouts.admin')
@section('page-title')
{{__('Manage Admission & Withdrawal Structure')}}
@endsection
@push('script-page')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
<script>
function generatePDF() {
    console.log('generating');
    const element = document.getElementById('report-content');
    const opt = {
        filename: 'student_strength_report.pdf',
        html2canvas: {
            scale: 1
        },
        jsPDF: {
            unit: 'pt',
            format: [700, 1000],
            orientation: 'landscape'
        }
    };
    html2pdf().from(element).set(opt).save();
}

function printPDF() {
    console.log('printing');
    const element = document.getElementById('report-content');
    const opt = {
        filename: 'student_strength_report.pdf',
        html2canvas: {
            scale: 1
        },
        jsPDF: {
            unit: 'pt',
            format: [700, 1000],
            orientation: 'landscape'
        }
    };
    html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
        window.open(pdf);
    });
}

</script>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Admission & Withdrawal Report')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body filter_change">
                    {{ Form::open(['route' => ['studentstrength.index'], 'method' => 'GET', 'id' => 'studentstrength_submit']) }}
                    <div class="row align-items-center justify-content-end ">
                        <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                            <div class="row d-flex justify-content-end ">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'),['class'=>'form-label'])}}
                                        {{ Form::date('date', isset($_GET['date'])?$_GET['date']:'', array('class' => 'form-control ')) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                onclick="document.getElementById('studentstrength_submit').submit(); return false;"
                                 data-bs-title="{{ __('Apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('studentstrength.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                 data-bs-title="{{ __('Reset') }}">
                                <span class="btn-inner--icon">Clear</span>
                            </a>
                            <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"  data-bs-title="{{ __('Print') }}"><span
                                    class="btn-inner--icon">Print</span>
                            </a>
                        </div>
                    </div>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <div class="my-3">
    <div class="row">
        <div class="col-12 d-flex justify-content-end gap-3">
            <button class="btn btn-outline-primary" onclick="generatePDF()">Download PDF</button>
            <button class="btn btn-outline-success" onclick="printPDF()">Print PDF</button>
        </div>
    </div>
</div> -->
<div class="content" id="report-content">
    <div class="card">
    <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School  </b></p>
    <p style="text-align:center; font-weight:600; font-size:1rem;">Lynx Network</p>
        <p style="text-align:center; font-weight:900; font-size:1rem;">Student Strength Report</p>
    </div>
    <table class="datatable">
        <thead class="table_heads">
        <tr>
            <th>id</th>
            <th>Branch</th>
            <th>Class</th>
            <th>Section</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>

            @foreach ($all_data as $data)
           <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{ (!empty($data->branch))? $data->branch->name : '-' }}</td>
            <td>{{ (!empty($data->class))? $data->class->name : '-' }}</td>
            <td>{{ (!empty($data->section))? $data->section->name : '-' }}</td>
            <td>{{ (!empty($data->student_count))? $data->student_count : '-' }}</td>
            </tr>
            @endforeach
            
        </tbody>

    </table>
</div>
@endsection
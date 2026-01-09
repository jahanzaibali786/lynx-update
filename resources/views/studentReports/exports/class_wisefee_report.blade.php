@extends('layouts.admin')

@section('page-title')
{{ __('Manage Classwise Fee Structure') }}
@endsection

@push('script-page')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script>
function generatePDF() {
    const element = document.getElementById('report-content');
    const opt = {
        filename: 'classfee_report.pdf',
        html2canvas: {
            scale: 1
        },
        jsPDF: {
            unit: 'pt',
            format: 'a4',
            orientation: 'landscape'
        }
    };
    html2pdf().from(element).set(opt).save();
}

// function printPDF() {
//     const element = document.getElementById('report-content');
//     const opt = {
//         filename: 'classfee_report.pdf',
//         html2canvas: {
//             scale: 1
//         },
//         jsPDF: {
//             unit: 'pt',
//             format: 'a4',
//             orientation: 'landscape'
//         }
//     };
//     html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
//         window.open(pdf);
//     });
// }

function printPDF() {
        var form = document.getElementById('classwisefee');
        var formData = new FormData(form);
        var queryString = new URLSearchParams(formData).toString();

        $.ajax({
            url: "{{ route('classwisefee_structure_report.report') }}?" + queryString,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                const base64Pdf = response.base64Pdf;
                const byteCharacters = atob(base64Pdf);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);
                window.open(blobUrl, '_blank');
            },
            error: function (xhr) {
                console.log(xhr.responseText);
            }
        });
}
</script>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Classwise Fee Structure') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body filter_change">
                    {{ Form::open(['route' => ['classwisefeereport.index'], 'method' => 'GET', 'id' => 'classwisefee']) }}
                    <div class="row">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                                {{ Form::select('session', $session, request()->get('session'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary" onclick="document.getElementById('classwisefee').submit(); return false;"  data-bs-title="{{ __('Apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            {{-- <button class="btn mx-1 btn-sm btn-outline-primary" type="button" onclick="generatePDF()">Download PDF</button> --}}
                            <button class="btn mx-1 btn-sm btn-outline-success" type="button" onclick="printPDF()">Print PDF</button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
{{-- <div class="content" id="report-content">
    <div class="card p-4">
        <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School  </b></p>
        <p style="text-align: center; font-weight: 600; font-size: 1rem;">PWD BRANCH ISLAMABAD</p>
        <p style="text-align: center; font-weight: 900; font-size: 1rem;">Classwise Fee Structure</p>
        <table class="datatable">
            <thead>
                <tr class="table_heads">
                    <th>Class</th>
                    @foreach ($heads as $head)
                    <th>{{ $head->fee_head ?? '-' }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                
                @foreach ($classes as $class)
                <tr>
                    <td>{{ $class->name ?? '-' }}</td>
                    @php $total = 0; @endphp
                    @foreach ($heads as $head)
                    <td>
                        @foreach ($class->classhead as $classhead)
                        @if ($classhead->head_id == $head->id)
                        {{ $classhead->amount }}
                        @php $total += $classhead->amount; @endphp
                        @break
                        @endif
                        @endforeach
                    </td>
                    @endforeach
                    <td>{{ $total }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> --}}

<div class="content" id="report-content">
    <div class="card p-4">
        <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School </b></p>
        <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> </b></p>
        <p style="text-align: center; font-weight: 600; font-size: 1rem;">
            {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}
        </p>        
        {{-- @dd($branches) --}}
        <p style="text-align: center; font-weight: 900; font-size: 1rem;">Classwise Fee Structure</p>

        <!-- Add this div as a wrapper for the table to enable scrolling -->
        <div class="table-responsive" >
            <table class="table">
                <thead>
                    <tr class="table_heads">
                        <th>Class</th>
                        @foreach ($heads as $head)
                        <th>{{ $head->fee_head ?? '-' }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classes as $class)
                    <tr>
                        <td>{{ $class->name ?? '-' }}</td>
                        @php $total = 0; @endphp
                        @foreach ($heads as $head)
                        <td>
                            @foreach ($class->classhead as $classhead)
                            @if ($classhead->head_id == $head->id)
                            {{ $classhead->amount }}
                            @php $total += $classhead->amount; @endphp
                            @break
                            @endif
                            @endforeach
                        </td>
                        @endforeach
                        <td>{{ $total }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
    }

    th,
    td {
        border: 1px solid #aaa;
        padding: 4px;
        text-align: center;
    }

    th {
        background-color: #f2f2f2;
    }

    .branch-title {
        font-weight: bold;
        text-align: left;
        background-color: #ddd;
    }

    .total-row {
        font-weight: bold;
        background-color: #f9f9f9;
    }
    .branch-header-row {
        text-align: left;
        font-size: 0.8rem;
    }
    .branchname{
        font-size: 1rem !important;
    }
</style>

<table style="margin-top: -80px !important;">
    <thead class="table_heads">
        <tr>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Sr No') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Br No') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Reg No') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Roll No') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Student Name') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Father Name') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Father Occupation') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Mother Name') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Mother Occupation') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Phone') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Class') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Section') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Session') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('D.O.B') }}</th>
            <th style="font-size: 12px !important; font-family: Arial, Helvetica, sans-serif !important;">{{ __('Admission Date') }}</th>
        </tr>
    </thead>
    <tbody>
        @php $serialNumber = 1; @endphp
        @foreach ($groupedEnrollments as $branchName => $classesByBranch)
            {{-- Branch Header Row --}}
            <tr>
                <td colspan="10" class="branch-header-row" style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important;">
                    <strong>{{ $branchName }}</strong>
                </td>
            </tr>
                @php $br = 1; @endphp
            @foreach ($classesByBranch as $className => $enrollments)
                @foreach ($enrollments as $enroll)
                    <tr>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important;">{{ $serialNumber++ }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important;">{{ $br++ }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important;">{{ $enroll->reg_no }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important;">{{ $enroll->enrollId }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important; text-align: left; width: 100px;">{{ $enroll->stdname }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important; text-align: left; width: 100px;">{{ $enroll->fathername }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important; text-align: left; width: 100px;">{{ $enroll->fatherprofession }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important; text-align: left; width: 100px;">{{ $enroll->mothername }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important; text-align: left; width: 100px;">{{ $enroll->motherprofession }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important; text-align: left;">{{ $enroll->fatherphone }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important;">{{ @$enroll->class->name }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important;">{{ $enroll->section->name ?? 'N/A' }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important;">{{ $enroll->session->year ?? 'N/A' }}</td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important; width: 60px;">
                            {{ $enroll->dob == '0000-00-00' || !$enroll->dob ? '' : date('d-M-Y', strtotime($enroll->dob)) }}
                        </td>
                        <td style="font-size: 12px!important; font-family: Arial, Helvetica, sans-serif !important;">{{ $enroll->adm_date ? date('d-M-Y', strtotime($enroll->adm_date)) : '' }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
<div style="display: table; width: 100%; margin-top: 50px;">
    <div style="display: table-row;">
        <div style="display: table-cell; width: 300px; border-bottom: 4px solid #000; text-align: left; font-weight: bolder;">&nbsp;</div>
        <div style="display: table-cell;">&nbsp;</div> <!-- Spacer cell -->
        <div style="display: table-cell; width: 300px; border-bottom: 4px solid #000; text-align: right; font-weight: bolder;">&nbsp;</div>
    </div>
</div>
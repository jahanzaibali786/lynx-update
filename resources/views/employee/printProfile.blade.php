<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee CV - {{ $employee->name ?? 'Employee Name' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.4;
            color: #333;
            background: #fff;
            font-size: 12px;
        }
        
        .cv-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        
        .profile-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .profile-left {
            display: table-cell;
            width: 200px;
            vertical-align: top;
            padding-right: 20px;
            text-align: center;
        }
        
        .profile-image {
            margin-bottom: 15px;
        }
        
        .profile-image img {
            width: 180px;
            height: 180px;
            border: 2px solid #2c3e50;
            border-radius: 20px;
            object-fit: cover;
        }
        
        .profile-name-section {
            text-align: center;
        }
        
        .name {
            font-size: 36px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .designation {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .profile-right {
            display: table-cell;
            vertical-align: top;
            padding-left: 20px;
        }
        
        .contact-info {
            font-size: 11px;
            line-height: 1.6;
        }

        .contact-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .contact-item {
            display: table-cell;
            width: 70%;
            padding-right: 10px;
            text-align: left;
        }

        .contact-item:last-child {
            padding-right: 0;
        }

        .status-row {
            margin-top: 10px;
            text-align: left;
        }
        
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 4px 10px 4px 0;
            vertical-align: top;
            color: #2c3e50;
        }
        
        .info-value {
            display: table-cell;
            padding: 4px 0;
            vertical-align: top;
        }
        
        .two-column {
            display: table;
            width: 100%;
        }
        
        .column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-right: 2%;
        }
        
        .experience-item, .education-item, .facility-item {
            margin-bottom: 12px;
            padding: 10px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
        }
        
        .item-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .item-subtitle {
            color: #7f8c8d;
            font-style: italic;
            margin-bottom: 3px;
        }
        
        .item-date {
            font-size: 10px;
            color: #95a5a6;
            margin-bottom: 5px;
        }
        
        .item-description {
            font-size: 11px;
            line-height: 1.4;
        }
        
        .children-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .children-table th,
        .children-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        
        .children-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-resigned {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-probation {
            background-color: #fff3cd;
            color: #856404;
        }
        
        @media print {
            body { font-size: 11px; }
            .cv-container { padding: 15px; }
            .section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="cv-container">
        <!-- Header Section -->
        <div class="header">
            <div class="profile-section">
                <div class="profile-left">
                    <div class="profile-image">
                        @if(!empty($employee->profile_img))
                            <img src="{{ Storage::url('emp_profile_images/' . $employee->profile_img) }}" alt="">
                        @else
                            <img src="{{ Storage::url('emp_profile_images/avatar.png') }}" alt="">
                        @endif
                    </div>
                    <div class="profile-name-section">
                        
                        
                    </div>
                </div>
                <div class="profile-right">
                    <div class="contact-info">
                        <div class="contact-row">
                            <div class="contact-item">
                                <div class="name">{{ $employee->name ?? 'Employee Name' }}</div>
                                <div class="designation">{{ $employee->designation->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    <div class="contact-info">
                        <div class="contact-row">
                            <div class="contact-item">
                                <strong>Employee ID:</strong> {{ $employeesId ?? 'N/A' }}
                            </div>
                            <div class="contact-item">
                                <strong>Email:</strong> {{ $employee->email ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="contact-row">
                            <div class="contact-item">
                                <strong>Phone:</strong> {{ $employee->phone ?? 'N/A' }}
                            </div>
                            <div class="contact-item">
                                <strong>CNIC:</strong> {{ $employee->cnic ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="status-row">
                            @if(!empty($employee->is_resigned))
                                <span class="status-badge status-resigned">Resigned</span>
                            @elseif(!empty($employee->probation_period) && $employee->probation_end > now())
                                <span class="status-badge status-probation">On Probation</span>
                            @else
                                <span class="status-badge status-active">Active</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="section">
            <div class="section-title">Personal Information</div>
            <div class="two-column">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Father/Husband Name:</div>
                            <div class="info-value">{{ $employee->f_name ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date of Birth:</div>
                            <div class="info-value">{{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M Y') : 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Gender:</div>
                            <div class="info-value">{{ $employee->gender ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Religion:</div>
                            <div class="info-value">{{ $employee->religion ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Blood Group:</div>
                            <div class="info-value">{{ $employee->blood_group ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Present Address:</div>
                            <div class="info-value">{{ $employee->present_address ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Permanent Address:</div>
                            <div class="info-value">{{ $employee->address ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">EOBI:</div>
                            <div class="info-value">{{ $employee->eobi_id ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">SSC#:</div>
                            <div class="info-value">{{ $employee->ssc_id ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Information Section -->
        <div class="section">
            <div class="section-title">Employment Details</div>
            <div class="two-column">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Branch:</div>
                            <div class="info-value">{{ $employee->branch->name ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Department:</div>
                            <div class="info-value">{{ $employee->department->name ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Joining Date:</div>
                            <div class="info-value">{{ $employee->company_doj ? \Carbon\Carbon::parse($employee->company_doj)->format('d M Y') : 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Pay Scale:</div>
                            <div class="info-value">{{ $payscalesauto->id ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Probation Period:</div>
                            <div class="info-value">{{ $employee->probation_period ?? 'N/A' }} months</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Probation End:</div>
                            <div class="info-value">{{ $employee->probation_end ? \Carbon\Carbon::parse($employee->probation_end)->format('d M Y') : 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Service Tenure:</div>
                            <div class="info-value">
                                @php
                                    $service_tenure = '';
                                    if($employee->company_doj) {
                                        $company_doj = \Carbon\Carbon::parse($employee->company_doj);
                                        $current_date = \Carbon\Carbon::now();
                                        $years = $current_date->diffInYears($company_doj);
                                        $months = $current_date->diffInMonths($company_doj) % 12;
                                        $service_tenure = $years . ' years, ' . $months . ' months';
                                    }
                                @endphp
                                {{ $service_tenure ?: 'N/A' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Employee Security:</div>
                            <div class="info-value">{{ $employee->security ?? 'N/A' }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Experience Section -->
        @if(!empty($emp_exp) && count($emp_exp) > 0)
        <div class="section">
            <div class="section-title">Work Experience</div>
            @foreach($emp_exp as $exp)
            <div class="experience-item">
                <div class="item-title">{{ $exp->designation ?? 'N/A' }}</div>
                <div class="item-subtitle">{{ $exp->organization ?? 'N/A' }}</div>
                <div class="item-date">{{ $exp->from ? \Carbon\Carbon::parse($exp->from)->format('M Y') : 'N/A' }} - {{ $exp->to ? \Carbon\Carbon::parse($exp->to)->format('M Y') : 'N/A' }}</div>
                @if(!empty($exp->reason))
                <div class="item-description"><strong>Reason for leaving:</strong> {{ $exp->reason }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Education Section -->
        @if(!empty($emp_edu) && count($emp_edu) > 0)
        <div class="section">
            <div class="section-title">Education</div>
            @foreach($emp_edu as $edu)
            <div class="education-item">
                <div class="item-title">{{ $edu->title ?? 'N/A' }} ({{ $edu->degree ?? 'N/A' }})</div>
                <div class="item-subtitle">{{ $edu->institute ?? 'N/A' }}</div>
                <div class="item-date">
                    {{ $edu->adm_date ? \Carbon\Carbon::parse($edu->adm_date)->format('Y') : 'N/A' }} - 
                    {{ $edu->pass_date ? \Carbon\Carbon::parse($edu->pass_date)->format('Y') : 'N/A' }}
                    @if(!empty($edu->grade))
                    | Grade: {{ $edu->grade }}
                    @endif
                </div>
                @if(!empty($edu->subject))
                <div class="item-description"><strong>Subject:</strong> {{ $edu->subject }}</div>
                @endif
                @if(!empty($edu->reason))
                <div class="item-description">{{ $edu->reason }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Facilities Section -->
        @if(!empty($emp_fac) && count($emp_fac) > 0)
        <div class="section">
            <div class="section-title">Facilities & Benefits</div>
            @foreach($emp_fac as $fac)
            <div class="facility-item">
                <div class="item-title">{{ $fac->title ?? 'N/A' }}</div>
                <div class="item-subtitle">{{ $fac->type ?? 'N/A' }}</div>
                <div class="item-date">
                    {{ $fac->given_date ? \Carbon\Carbon::parse($fac->given_date)->format('d M Y') : 'N/A' }} - 
                    {{ $fac->upto_date ? \Carbon\Carbon::parse($fac->upto_date)->format('d M Y') : 'Ongoing' }}
                </div>
                @if(!empty($fac->detail))
                <div class="item-description">{{ $fac->detail }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Employee Children Section -->
        @if(!empty($emp_child) && count($emp_child) > 0)
        <div class="section">
            <div class="section-title">Employee Children (Students)</div>
            <table class="children-table">
                <thead>
                    <tr>
                        <th>Child Name</th>
                        <th>Class</th>
                        <th>Branch</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($emp_child as $child)
                    <tr>
                        <td>{{ $child->student->stdname ?? 'N/A' }}</td>
                        <td>{{ $child->student->class->name ?? 'N/A' }}</td>
                        <td>{{ $child->student->branches->name ?? 'N/A' }}</td>
                        <td>{{ $child->amount ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Additional Information -->
        <div class="section">
            <div class="section-title">Additional Information</div>
            <div class="two-column">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">PESSI:</div>
                            <div class="info-value">{{ $employee->pessi ?? 'N/A' }}%</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">PESSI Employer:</div>
                            <div class="info-value">{{ $employee->pessi_employer ?? 'N/A' }}%</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">EOBI:</div>
                            <div class="info-value">{{ $employee->eobi ?? 'N/A' }}%</div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">EOBI Employer:</div>
                            <div class="info-value">{{ $employee->eobi_employer ?? 'N/A' }}%</div>
                        </div>
                        @if(!empty($employee->job_description))
                        <div class="info-row">
                            <div class="info-label">Job Description:</div>
                            <div class="info-value">{{ $employee->job_description }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

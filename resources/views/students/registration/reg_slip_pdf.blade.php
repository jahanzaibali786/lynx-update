<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Receipt</title>
    <style>
        @page {
            margin: 18px 22px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .receipt {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 18px 22px 20px;
        }

        .header {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 12px;
        }

        .header-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .header-spacer {
            width: 56px;
        }

        .brand {
            text-align: center;
            width: calc(100% - 112px);
        }

        .brand h1 {
            margin: 0;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 28px;
            font-weight: 600;
            line-height: 1.1;
        }

        .brand .header-text {
            display: block;
            width: auto;
            max-width: 220px;
            max-height: 40px;
            margin: 0 auto;
            object-fit: contain;
        }

        .brand .branch {
            margin-top: 4px;
            font-size: 13px;
        }

        .logo {
            width: 56px;
            text-align: right;
        }

        .logo img {
            display: inline-block;
            width: 48px;
            max-width: 48px;
            height: 48px;
            max-height: 48px;
            object-fit: contain;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin: 6px 0 12px;
            font-size: 12px;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            margin: 10px 0 18px;
        }

        .body-text {
            line-height: 1.9;
            font-size: 12px;
            text-align: justify;
        }

        .line {
            display: inline-block;
            min-width: 120px;
            border-bottom: 1px solid #111827;
            text-align: center;
            padding: 0 4px 1px;
            vertical-align: baseline;
        }

        .line.wide {
            min-width: 210px;
        }

        .signatures {
            width: 100%;
            margin-top: 70px;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 10px;
        }

        .sig .name {
            border-bottom: 1px solid #111827;
            min-height: 18px;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sig .role {
            font-size: 12px;
        }
    </style>
</head>
<body>
        @php
        $branchName = optional($reg_recipt->branches)->name;
        $headmasterName = optional(optional($reg_recipt->branch_name)->headmaster_name)->name;
        $sessionYear = optional($reg_recipt->session)->year;

        $headerTextPath = null;
        foreach ([
            public_path('assets/images/lynxheadertext.png'),
            public_path('assets/images/lynxheadertext.jpg'),
            public_path('assets/images/lynxheadertext.webp'),
            public_path('assets/images/lynxheadertext_old.png'),
            public_path('assets/images/thelynxschool.png'),
        ] as $candidate) {
            if (file_exists($candidate)) {
                $headerTextPath = $candidate;
                break;
            }
        }

        $logoPath = null;
        foreach ([
            public_path('assets/images/lynx2.jpg'),
            public_path('assets/images/lynx2-header.png'),
            public_path('assets/images/lynxLogo.png'),
            public_path('uploads/logo/logo-dark.png'),
        ] as $candidate) {
            if (file_exists($candidate)) {
                $logoPath = $candidate;
                break;
            }
        }
    @endphp

    <div class="receipt">
        <div class="header">
            <div class="header-cell header-spacer"></div>
            <div class="header-cell brand">
                @if($headerTextPath)
                    <img class="header-text" src="{{ $headerTextPath }}" alt="The Lynx School">
                @else
                    <div style="font-size: 22px; font-weight: 700; text-align: center;">The Lynx School</div>
                @endif
                <div class="branch">{{ $branchName }}</div>
            </div>
            <div class="header-cell logo">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="logo">
                @endif
            </div>
        </div>

        <div class="meta">
            <div>Registration No: {{ $reg_recipt->id }}</div>
            <div>Date: {{ now()->format("d M Y") }}</div>
        </div>

        <div class="title">Registration Receipt</div>

        <div class="body-text">
            Received Rs <span class="line">{{ number_format((float)($reg_recipt->registrationfee ?? 0), 0) }}</span>
            with thanks from Mr. <span class="line wide">{{ $reg_recipt->fathername }}</span>
            for the registration of {{ $reg_recipt->gender == 'male' ? 'his' : 'her' }} ward
            <span class="line wide">{{ $reg_recipt->stdname }}</span>
            of class <span class="line">{{ optional($reg_recipt->class)->name }}</span>
            session <span class="line">{{ $sessionYear }}</span>.
        </div>

        <table class="signatures">
            <tr>
                <td class="sig">
                    <div class="name">{{ $headmasterName }}</div>
                    <div class="role">Headmistress</div>
                </td>
                <td class="sig">
                    <div class="name">&nbsp;</div>
                    <div class="role">Accountant</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Payslip')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            width: 100%;
        }
        
        /* Header */
        .payslip-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .payslip-header .company-name {
            font-size: 16px;
            font-weight: bold;
        }
        
        .payslip-header .title {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .payslip-header .period {
            font-size: 12px;
        }
        
        /* Employee Info */
        .employee-info {
            margin-bottom: 15px;
        }
        
        .info-row {
            margin-bottom: 5px;
        }
        
        .info-label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }
        
        .info-value {
            display: inline-block;
        }
        
        /* Tables */
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .salary-table th,
        .salary-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            font-size: 10px;
        }
        
        .salary-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }
        
        /* Sections */
        .calculation-section {
            border: 1px solid #000;
            padding: 10px;
            margin: 10px 0;
            page-break-inside: avoid;
        }
        
        .calculation-section h3 {
            margin-top: 0;
            font-size: 12px;
        }
        
        /* Signature */
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .signature-line {
            display: inline-block;
            width: 200px;
            border-top: 1px solid #000;
            margin: 0 20px;
            padding-top: 5px;
            text-align: center;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
            .page-break {
                page-break-after: always;
            }
        }
        
        @yield('styles')
    </style>
</head>
<body>
    @yield('content')
</body>
</html>

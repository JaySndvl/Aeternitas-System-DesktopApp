<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PDF Document')</title>
    <style>
        /* Base PDF Styles */
        body {
            font-family: 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        
        /* Header Section */
        .pdf-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .pdf-header .company-name {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #2d3748;
        }
        
        .pdf-header .document-title {
            font-size: 16px;
            margin: 0 0 10px 0;
            color: #4a5568;
        }
        
        .pdf-header .document-info {
            font-size: 11px;
            color: #666;
            margin: 3px 0;
        }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        th {
            background-color: #4472C4;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #2d3748;
        }
        
        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 9px;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Text Alignment */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        
        /* Currency formatting */
        .currency {
            font-family: 'DejaVu Sans', monospace;
        }
        
        /* Status Colors */
        .status-success { color: #38a169; font-weight: bold; }
        .status-warning { color: #d69e2e; font-weight: bold; }
        .status-danger { color: #e53e3e; font-weight: bold; }
        .status-info { color: #3182ce; font-weight: bold; }
        
        /* Total/Summary Rows */
        .total-row {
            font-weight: bold;
            background-color: #e6e6e6 !important;
        }
        
        /* Footer */
        .pdf-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        /* Summary Section */
        .summary-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .summary-section h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            color: #333;
        }
        
        /* Page Break Control */
        .page-break {
            page-break-after: always;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
        
        @media print {
            body { margin: 0; padding: 10px; }
        }
        
        @yield('styles')
    </style>
</head>
<body>
    @yield('content')
</body>
</html>

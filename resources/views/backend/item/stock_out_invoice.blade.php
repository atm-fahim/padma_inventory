<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gate Pass - Stock Out</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
                background-color: white;
            }
            .invoice-container {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .invoice-copy {
                page-break-after: none !important;
                page-break-inside: avoid;
                margin-bottom: 8px;
                padding-bottom: 5px;
                border-bottom: 2px dashed #000 !important;
            }
            .invoice-copy:last-child {
                border-bottom: none !important;
                margin-bottom: 0;
            }
            .item-table th,
            .item-table td {
                border: 1px solid #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .item-table th {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .signature-section {
                margin-top: 15px !important;
            }
            .signature-box {
                border-top: 1px solid #000 !important;
            }
            .header {
                padding-bottom: 3px !important;
                margin-bottom: 5px !important;
                border-bottom: 2px solid #000 !important;
            }
            .header h1 {
                font-size: 14px !important;
            }
            .header h2 {
                font-size: 11px !important;
            }
            .invoice-body {
                margin-top: 5px !important;
            }
            .section-title {
                margin-bottom: 4px !important;
            }
            .field-group {
                margin-bottom: 2px !important;
            }
            .item-table {
                margin-top: 5px !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            margin: 0;
        }
        
        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 5mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .invoice-copy {
            margin-bottom: 10px;
            border-bottom: 2px dashed #ccc;
            padding-bottom: 8px;
        }
        
        .invoice-copy:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .copy-title {
            text-align: right;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            padding: 3px 8px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 2px 0;
            font-size: 12px;
        }
        
        .invoice-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 8px;
        }
        
        .left-section, .right-section {
            padding: 5px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        
        .field-group {
            margin-bottom: 4px;
            display: flex;
            align-items: flex-start;
        }
        
        .field-label {
            font-weight: bold;
            font-size: 9px;
            min-width: 90px;
            display: inline-block;
        }
        
        .field-value {
            font-size: 9px;
            flex: 1;
        }
        
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8px;
        }
        
        .item-table th,
        .item-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: left;
        }
        
        .item-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .item-table td {
            text-align: center;
        }
        
        .item-table td.product-details {
            white-space: nowrap;
            font-size: 7px;
            text-align: left;
            padding-left: 5px;
            padding-right: 5px;
            word-break: keep-all;
        }
        
        @media print {
            .item-table td.product-details {
                white-space: nowrap !important;
                font-size: 6px !important;
            }
        }
        
        .signature-section {
            margin-top: 25px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        
        .signature-box {
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn-print {
            background-color: #007bff;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-print:hover {
            background-color: #0056b3;
        }
        
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
        }

        .gate-pass-session-banner {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            letter-spacing: 0.12em;
            padding: 10px 12px;
            margin-bottom: 12px;
            border: 2px solid #000;
            background-color: #fff8e1;
            color: #000;
        }

        @media print {
            .gate-pass-session-banner {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background-color: #fff8e1 !important;
                margin-bottom: 8px !important;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        @if(!empty($invoiceData['gate_pass_top_banner']))
            <div class="gate-pass-session-banner">{{ $invoiceData['gate_pass_top_banner'] }}</div>
        @endif

        <div class="print-button no-print">
            <button onclick="window.print()" class="btn-print">Print</button>
            <a href="{{ route('stock-out') }}" class="btn-back">Back to Stock Out</a>
        </div>
        
        @php
            $copyTypes = ['Original', 'Duplicate', 'Triplicate'];
        @endphp
        
        @foreach($copyTypes as $copyType)
        <div class="invoice-copy">
            <div class="copy-title">{{ $copyType }}</div>
            
            <div class="header">
                <h1>GATE PASS</h1>
                <h2>Padma Bank PLC.</h2>
            </div>
            
            <div class="invoice-body">
                <div class="left-section">
                    <div class="section-title">Item and Transaction Details</div>
                    
                    <div class="field-group">
                        <span class="field-label">Invoice No:</span>
                        <span class="field-value">{{ $invoiceData['stock_out_no'] }}</span>
                    </div>
                    
                    <div class="field-group">
                        <span class="field-label">From Employee ID:</span>
                        <span class="field-value">{{ data_get($invoiceData['from_employee'], 'employee_id') ?: data_get($invoiceData['from_employee'], 'emp_id') ?: 'N/A' }}</span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">From Employee Name:</span>
                        <span class="field-value">{{ data_get($invoiceData['from_employee'], 'emp_name') ?: data_get($invoiceData['from_employee'], 'name') ?: 'N/A' }}</span>
                    </div>
                    
                    <div class="field-group">
                        <span class="field-label">From:</span>
                        <span class="field-value">{{ data_get($invoiceData['from_branch'], 'branch_name') ?: 'N/A' }}</span>
                    </div>
                    
                    <div class="field-group">
                        <span class="field-label">Date of Issue:</span>
                        <span class="field-value">{{ $invoiceData['created_at'] ? date('Y-m-d H:i:s', strtotime($invoiceData['created_at'])) : 'N/A' }}</span>
                    </div>

                    <div class="field-group">
                        <span class="field-label">Note:</span>
                        <span class="field-value">{{ !empty($invoiceData['global_note']) ? $invoiceData['global_note'] : 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="right-section">
                    <div class="section-title">Assignment and Product Specifications</div>
                    
                    <!-- <div class="field-group">
                        <span class="field-label">Assign To:</span>
                        <span class="field-value">
                            @php
                                $assignTos = array_filter(array_map(function($item) {
                                    return $item['to_employee']->emp_name ?? '';
                                }, $invoiceData['items']));
                                echo implode(', ', $assignTos) ?: 'N/A';
                            @endphp
                        </span>
                    </div> -->
                    
                    <div class="field-group">
                        <span class="field-label">To Employee ID:</span>
                        <span class="field-value">
                            @php
                                $toEmpIds = [];
                                foreach ($invoiceData['items'] as $item) {
                                    $te = $item['to_employee'] ?? null;
                                    $id = $te ? (data_get($te, 'employee_id') ?: data_get($te, 'emp_id') ?: '') : '';
                                    if ($id !== '') {
                                        $toEmpIds[$id] = true;
                                    }
                                }
                                echo count($toEmpIds) ? implode(', ', array_keys($toEmpIds)) : 'N/A';
                            @endphp
                        </span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">To Employee Name:</span>
                        <span class="field-value">
                            @php
                                $toEmpNames = [];
                                foreach ($invoiceData['items'] as $item) {
                                    $te = $item['to_employee'] ?? null;
                                    $nm = $te ? (data_get($te, 'emp_name') ?: data_get($te, 'name') ?: '') : '';
                                    if ($nm !== '') {
                                        $toEmpNames[$nm] = true;
                                    }
                                }
                                echo count($toEmpNames) ? implode(', ', array_keys($toEmpNames)) : 'N/A';
                            @endphp
                        </span>
                    </div>
                    
                    <div class="field-group">
                        <span class="field-label">To:</span>
                        <span class="field-value">{{ data_get($invoiceData['to_branch'], 'branch_name') ?: 'N/A' }}</span>
                    </div>
                    
                    <div class="field-group">
                        <span class="field-label">Mobile:</span>
                        <span class="field-value">
                            @php
                                $mobiles = array_filter(array_map(function($item) {
                                    return $item['mobile'] ?? '';
                                }, $invoiceData['items']));
                                echo implode(', ', $mobiles) ?: 'N/A';
                            @endphp
                        </span>
                    </div>
                </div>
            </div>
            
            <table class="item-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">SL</th>
                        <th style="width: 35%;">Product</th>
                        <th style="width: 10%;">QTY</th>
                        <th style="width: 10%;">Product Details</th>
                        <th style="width: 30%;">Serial</th>
                        <th style="width: 30%;">Condition</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedItems = [];
                        foreach ($invoiceData['items'] as $idx => $item) {
                            $setUniqNo = trim((string)($item['set_uniq_no'] ?? ''));
                            $groupKey = $setUniqNo !== '' ? 'set:' . $setUniqNo : 'single:' . $idx;

                            if (!isset($groupedItems[$groupKey])) {
                                $groupedItems[$groupKey] = [
                                    'base_item' => $item,
                                    'quantity' => 0,
                                    'processor_values' => [],
                                    'storage_values' => [],
                                    'ram_values' => [],
                                    'serial_values' => [],
                                    'condition_values' => [],
                                ];
                            }

                            $groupedItems[$groupKey]['quantity'] += (int)($item['quantity'] ?? 0);

                            if (!empty($item['processor'])) {
                                $groupedItems[$groupKey]['processor_values'][] = trim((string)$item['processor']);
                            }
                            if (!empty($item['storage'])) {
                                $groupedItems[$groupKey]['storage_values'][] = trim((string)$item['storage']);
                            }
                            if (!empty($item['ram'])) {
                                $groupedItems[$groupKey]['ram_values'][] = trim((string)$item['ram']);
                            }
                            if (!empty($item['serial_no'])) {
                                $groupedItems[$groupKey]['serial_values'][] = trim((string)$item['serial_no']);
                            }
                            if (!empty($item['condition'])) {
                                $groupedItems[$groupKey]['condition_values'][] = trim((string)$item['condition']);
                            }
                        }
                    @endphp

                    @foreach(array_values($groupedItems) as $index => $groupedItem)
                        @php
                            $baseItem = $groupedItem['base_item'];
                            $processorValues = array_values(array_unique(array_filter($groupedItem['processor_values'])));
                            $storageValues = array_values(array_unique(array_filter($groupedItem['storage_values'])));
                            $ramValues = array_values(array_unique(array_filter($groupedItem['ram_values'])));
                            $serialValues = array_values(array_unique(array_filter($groupedItem['serial_values'])));
                            $conditionValues = array_values(array_unique(array_filter($groupedItem['condition_values'])));

                            $details = [];
                            if (!empty($processorValues)) {
                                $details[] = 'Processor: ' . implode(' / ', $processorValues);
                            }
                            if (!empty($storageValues)) {
                                $details[] = 'Storage: ' . implode(' / ', $storageValues);
                            }
                            if (!empty($ramValues)) {
                                $details[] = 'RAM: ' . implode(' / ', $ramValues);
                            }

                            $productDetails = !empty($details) ? implode(', ', $details) : 'N/A';
                            $serialDisplay = !empty($serialValues) ? implode(', ', $serialValues) : '';
                            $conditionDisplay = !empty($conditionValues) ? implode(', ', $conditionValues) : 'Good';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $baseItem['item']->item_title ?? $baseItem['item']->item ?? 'N/A' }}</td>
                            <td>{{ $groupedItem['quantity'] > 0 ? $groupedItem['quantity'] : 'N/A' }}</td>
                            <td class="product-details">{{ $productDetails }}</td>
                            <td>{{ $serialDisplay }}</td>
                            <td>{{ $conditionDisplay }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div style="margin-bottom: 15px;">
                        <div style="border-bottom: 1px solid #000; width: 150px; margin: 0 auto;"></div>
                        <div style="margin-top: 3px; font-size: 8px;">Receiver's Signature</div>
                    </div>
                </div>
                <div class="signature-box">
                    <div style="margin-bottom: 15px;">
                        <div style="border-bottom: 1px solid #000; width: 150px; margin: 0 auto;"></div>
                        <div style="margin-top: 3px; font-size: 8px;">Signature</div>
                    </div>
                </div>
                <div class="signature-box">
                    <div style="margin-bottom: 15px;">
                        <div style="border-bottom: 1px solid #000; width: 150px; margin: 0 auto;"></div>
                        <div style="margin-top: 3px; font-size: 8px;">Authorized Signature - ICT Division</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <script>
        (function () {
            var stockOutNo = @json($invoiceData['stock_out_no'] ?? '');
            if (!stockOutNo) {
                return;
            }
            var logUrl = @json(route('stock-out-gate-pass-print-log'));
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (!tokenMeta) {
                return;
            }
            window.addEventListener('afterprint', function () {
                fetch(logUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': tokenMeta.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ stock_out_no: stockOutNo })
                }).catch(function () {});
            });
        })();
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In Invoice</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                border: none;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #28a745;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
        }
        
        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .info-section {
            padding: 15px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .field-group {
            margin-bottom: 12px;
        }
        
        .field-label {
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
            min-width: 120px;
        }
        
        .field-value {
            font-size: 12px;
            display: inline-block;
        }
        
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .item-table th,
        .item-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        
        .item-table th {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        
        .total-row {
            font-size: 14px;
            font-weight: bold;
            padding: 10px 0;
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn-print {
            background-color: #28a745;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-print:hover {
            background-color: #218838;
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
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="print-button no-print">
            <button onclick="window.print()" class="btn-print">Print</button>
            <a href="{{ route('stock-in-list') }}" class="btn-back">Back to Stock In List</a>
        </div>
        
        <div class="header">
            <h1>STOCK IN INVOICE</h1>
            <h2>Padma Bank PLC.</h2>
        </div>
        
        <div class="invoice-info">
            <div class="info-section">
                <div class="section-title">Stock In Information</div>
                
                <div class="field-group">
                    <span class="field-label">Stock In No:</span>
                    <span class="field-value">{{ $invoiceData['stock_in_no'] }}</span>
                </div>
                
                <div class="field-group">
                    <span class="field-label">Receive Date:</span>
                    <span class="field-value">{{ $invoiceData['receive_date'] }}</span>
                </div>
                
                <div class="field-group">
                    <span class="field-label">Branch:</span>
                    <span class="field-value">{{ $invoiceData['to_branch']->branch_name ?? 'N/A' }}</span>
                </div>
            </div>
            
            <div class="info-section">
                <div class="section-title">Organization</div>
                
                <div class="field-group">
                    <span class="field-label">Organization ID:</span>
                    <span class="field-value">{{ $invoiceData['org_id'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        
        <table class="item-table">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoiceData['items'] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['item']->item_title ?? 'N/A' }}</td>
                        <td>{{ $item['category']->category_title ?? 'N/A' }}</td>
                        <td>{{ $item['brand']->brand_name ?? 'N/A' }}</td>
                        <td>{{ $item['supplier']->supplier_name ?? 'N/A' }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td class="text-right">৳{{ number_format($item['price'], 2) }}</td>
                        <td class="text-right">৳{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="total-section">
            @php
                $subTotal = 0;
                $totalVat = 0;
                $totalTax = 0;
                foreach($invoiceData['items'] as $item) {
                    $itemTotal = $item['price'] * $item['quantity'];
                    $subTotal += $itemTotal;
                    $totalVat += ($item['vat'] ?? 0) * $item['quantity'];
                    $totalTax += ($item['tax'] ?? 0) * $item['quantity'];
                }
                $grandTotal = $subTotal + $totalVat + $totalTax;
            @endphp
            
            <div class="total-row">
                <span style="margin-right: 20px;">Subtotal:</span>
                <span>৳{{ number_format($subTotal, 2) }}</span>
            </div>
            @if($totalVat > 0)
            <div class="total-row">
                <span style="margin-right: 20px;">VAT:</span>
                <span>৳{{ number_format($totalVat, 2) }}</span>
            </div>
            @endif
            @if($totalTax > 0)
            <div class="total-row">
                <span style="margin-right: 20px;">Tax:</span>
                <span>৳{{ number_format($totalTax, 2) }}</span>
            </div>
            @endif
            <div class="total-row" style="font-size: 18px; border-top: 2px solid #28a745; padding-top: 10px;">
                <span style="margin-right: 20px;">Grand Total:</span>
                <span>৳{{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>
    </div>
    
    <script>
        // Auto print on load (optional - uncomment if needed)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Detail Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 3px 4px; }
        th { background: #0f4c5c; color: #fff; }
        .text-center { text-align: center; }
        .header { text-align: center; margin-bottom: 10px; }
        .org-name { margin: 0 0 2px 0; font-size: 16px; font-weight: bold; }
        .report-title { margin: 0 0 4px 0; font-size: 13px; font-weight: bold; }
        .meta { margin: 0; color: #555; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="org-name">{{ $organizationName ?? 'Organization' }}</h1>
        <h2 class="report-title">Inventory Detail Report</h2>
        <p class="meta">
            Branch: <strong>{{ $branchLabel ?? 'All Branch' }}</strong>
            &nbsp;|&nbsp; Generated: {{ $generatedAt ?? date('Y-m-d H:i') }}
            &nbsp;|&nbsp; Total: {{ count($report) }}
        </p>
    </div>
    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>Item</th>
                <th>Category</th>
                <th>Branch</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Serial No</th>
                <th>Set No</th>
                <th>RAM</th>
                <th>STORAGE</th>
                <th>PROCESSOR</th>
                <th>Warranty</th>
                <th>Supplier</th>
                <th>From Branch</th>
                <th>To Branch</th>
                <th>Received Branch</th>
                <th>Brand</th>
                <th>Condition</th>
                <th>Warehouse</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->item_name ?? '' }}</td>
                <td>{{ $row->category_name ?? '' }}</td>
                <td>{{ $row->store_branch_name ?? '' }}</td>
                <td class="text-center">{{ $row->quantity ?? 0 }}</td>
                <td>{{ $row->status_label ?? '' }}</td>
                <td>{{ $row->uniq_serial_no ?? '' }}</td>
                <td>{{ $row->set_uniq_no ?? '' }}</td>
                <td>{{ $row->ram ?? '' }}</td>
                <td>{{ $row->storage ?? '' }}</td>
                <td>{{ $row->processor ?? '' }}</td>
                <td>{{ $row->warranty ?? '' }}</td>
                <td>{{ $row->supplier_name ?? '' }}</td>
                <td>{{ $row->from_branch_name ?? '' }}</td>
                <td>{{ $row->to_branch_name ?? '' }}</td>
                <td>{{ $row->received_branch_name ?? '' }}</td>
                <td>{{ $row->brand_name ?? '' }}</td>
                <td>{{ $row->condition ?? '' }}</td>
                <td>{{ $row->warehouse ?? '' }}</td>
                <td>{{ $row->created_date_formatted ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="20" class="text-center">No records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Inventory Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 3px 4px; }
        th { background: #e0e0e0; }
        .text-center { text-align: center; }
        h2 { margin: 0 0 10px 0; font-size: 14px; }
    </style>
</head>
<body>
    <h2>Employee Inventory Report - {{ date('Y-m-d H:i') }}</h2>
    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>Item</th>
                <th>Store Emp</th>
                <th>From Emp</th>
                <th>To Emp</th>
                <th>From Branch</th>
                <th>To Branch</th>
                <th>Received Branch</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Serial</th>
                <th>Set No</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Warehouse</th>
                <th>Condition</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->item_name ?? '' }}</td>
                    <td>{{ $row->store_employee_id ?? '' }}</td>
                    <td>{{ $row->from_employee_id ?? '' }}</td>
                    <td>{{ $row->to_employee_id ?? '' }}</td>
                    <td>{{ $row->from_branch_name ?? '' }}</td>
                    <td>{{ $row->to_branch_name ?? '' }}</td>
                    <td>{{ $row->received_branch_name ?? '' }}</td>
                    <td class="text-center">{{ (int)($row->quantity ?? 0) }}</td>
                    <td>{{ $row->status_label ?? '' }}</td>
                    <td>{{ $row->uniq_serial_no ?? '' }}</td>
                    <td>{{ $row->set_uniq_no ?? '' }}</td>
                    <td>{{ $row->stock_in_no ?? '' }}</td>
                    <td>{{ $row->stock_out_no ?? '' }}</td>
                    <td>{{ $row->warehouse ?? '' }}</td>
                    <td>{{ $row->condition ?? '' }}</td>
                    <td>{{ $row->created_date_formatted ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

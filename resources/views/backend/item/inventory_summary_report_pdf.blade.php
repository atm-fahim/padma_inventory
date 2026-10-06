<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Item Branch Division Stock Summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 4px 0; font-size: 14px; }
        .meta { margin-bottom: 10px; color: #555; }
        .kpi { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .kpi td { border: 1px solid #ccc; padding: 6px 8px; width: 25%; background: #f7fafc; }
        .kpi .label { font-size: 9px; color: #666; text-transform: uppercase; }
        .kpi .value { font-size: 13px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #333; padding: 4px; }
        table.data th { background: #0f4c5c; color: #fff; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .section { margin-top: 14px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $summary = $summary ?? [
            'total_qty' => collect($report)->sum('total_qty'),
            'item_count' => collect($report)->pluck('item_id')->unique()->count(),
            'branch_count' => collect($report)->pluck('branch_key')->unique()->count(),
            'division_count' => collect($report)->pluck('division_id')->unique()->count(),
        ];
    @endphp

    <h2>Item / Branch / Division Stock Summary</h2>
    <div class="meta">Generated: {{ date('Y-m-d H:i') }}</div>

    <table class="kpi">
        <tr>
            <td>
                <div class="label">Total Quantity</div>
                <div class="value">{{ number_format($summary['total_qty'] ?? 0) }}</div>
            </td>
            <td>
                <div class="label">Unique Items</div>
                <div class="value">{{ number_format($summary['item_count'] ?? 0) }}</div>
            </td>
            <td>
                <div class="label">Branches</div>
                <div class="value">{{ number_format($summary['branch_count'] ?? 0) }}</div>
            </td>
            <td>
                <div class="label">Divisions</div>
                <div class="value">{{ number_format($summary['division_count'] ?? 0) }}</div>
            </td>
        </tr>
    </table>

    <div class="section">Detail Report</div>
    <table class="data">
        <thead>
            <tr>
                <th>SL</th>
                <th>Division</th>
                <th>Branch</th>
                <th>Item</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Current Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->division_name ?? 'Unassigned' }}</td>
                    <td>{{ $row->branch_name ?? $row->location_name ?? '' }}</td>
                    <td>{{ $row->item_name ?? '' }}</td>
                    <td class="text-right">{{ (int)($row->stock_in_qty ?? 0) }}</td>
                    <td class="text-right">{{ (int)($row->stock_out_qty ?? 0) }}</td>
                    <td class="text-right">{{ (int)($row->total_qty ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($report) > 0)
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Grand Total</th>
                <th class="text-right">{{ collect($report)->sum('stock_in_qty') }}</th>
                <th class="text-right">{{ collect($report)->sum('stock_out_qty') }}</th>
                <th class="text-right">{{ $summary['total_qty'] ?? collect($report)->sum('total_qty') }}</th>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>

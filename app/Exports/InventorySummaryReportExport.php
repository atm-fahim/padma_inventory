<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventorySummaryReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'SL',
            'Division',
            'Branch',
            'Item',
            'Stock In',
            'Stock Out',
            'Current Qty',
        ];
    }

    public function map($row): array
    {
        static $sl = 0;
        $sl++;

        return [
            $sl,
            $row->division_name ?? 'Unassigned',
            $row->branch_name ?? $row->location_name ?? '',
            $row->item_name ?? '',
            (int) ($row->stock_in_qty ?? 0),
            (int) ($row->stock_out_qty ?? 0),
            (int) ($row->total_qty ?? 0),
        ];
    }
}

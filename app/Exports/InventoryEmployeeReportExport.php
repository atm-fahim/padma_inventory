<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryEmployeeReportExport implements FromCollection, WithHeadings, WithMapping
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
            'Item',
            'Store Employee ID',
            'From Employee ID',
            'To Employee ID',
            'From Branch',
            'To Branch',
            'Received Branch',
            'Quantity',
            'Status',
            'Serial',
            'Set No',
            'Stock In',
            'Stock Out',
            'Warehouse',
            'Condition',
            'Date',
        ];
    }

    public function map($row): array
    {
        static $sl = 0;
        $sl++;

        return [
            $sl,
            $row->item_name ?? '',
            $row->store_employee_id ?? '',
            $row->from_employee_id ?? '',
            $row->to_employee_id ?? '',
            $row->from_branch_name ?? '',
            $row->to_branch_name ?? '',
            $row->received_branch_name ?? '',
            (int) ($row->quantity ?? 0),
            $row->status_label ?? '',
            $row->uniq_serial_no ?? '',
            $row->set_uniq_no ?? '',
            $row->stock_in_no ?? '',
            $row->stock_out_no ?? '',
            $row->warehouse ?? '',
            $row->condition ?? '',
            $row->created_date_formatted ?? '',
        ];
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InventoryDetailReportExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    protected $collection;
    protected array $meta;

    public function __construct($collection, array $meta = [])
    {
        $this->collection = $collection;
        $this->meta = $meta;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $orgName = $this->meta['organization_name'] ?? 'Organization';
                $branchLabel = $this->meta['branch_label'] ?? 'All Branch';
                $generated = $this->meta['generated_at'] ?? date('Y-m-d H:i');
                $total = $this->collection->count();

                $lastCol = 'AD'; // 30 heading columns
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->mergeCells("A3:{$lastCol}3");

                $sheet->setCellValue('A1', $orgName);
                $sheet->setCellValue('A2', 'Inventory Detail Report');
                $sheet->setCellValue('A3', 'Branch: ' . $branchLabel . '  |  Generated: ' . $generated . '  |  Total: ' . $total);

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A3')->getFont()->setSize(10);
                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A4:' . $lastCol . '4')->getFont()->setBold(true);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'SL',
            'Item Name',
            'Category',
            'Branch',
            'Quantity',
            'Status',
            'UNIQ_SERIAL_NO',
            'FROM_DEVICE_SERIAL_NO',
            'TO_DEVICE_SERIAL_NO',
            'RAM',
            'STORAGE',
            'PROCESSOR',
            'SET_UNIQ_NO',
            'SERVICE_WARRANTY',
            'WARRANTY',
            'Supplier',
            'Store Employee ID',
            'From Employee ID',
            'To Employee ID',
            'From Branch',
            'To Branch',
            'Brand',
            'Condition',
            'RECEIVE_REMARKS',
            'Received Branch',
            'Warehouse',
            'Remarks',
            'Stock In No',
            'Stock Out No',
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
            $row->category_name ?? '',
            $row->store_branch_name ?? '',
            $row->quantity ?? 0,
            $row->status_label ?? '',
            $row->uniq_serial_no ?? '',
            $row->from_device_serial_no ?? '',
            $row->to_device_serial_no ?? '',
            $row->ram ?? '',
            $row->storage ?? '',
            $row->processor ?? '',
            $row->set_uniq_no ?? '',
            $row->service_warranty ?? '',
            $row->warranty ?? '',
            $row->supplier_name ?? '',
            $row->store_employee_id ?? '',
            $row->from_employee_id ?? '',
            $row->to_employee_id ?? '',
            $row->from_branch_name ?? '',
            $row->to_branch_name ?? '',
            $row->brand_name ?? '',
            $row->condition ?? '',
            $row->receive_remarks ?? '',
            $row->received_branch_name ?? '',
            $row->warehouse ?? '',
            $row->remarks ?? '',
            $row->stock_in_no ?? '',
            $row->stock_out_no ?? '',
            $row->created_date_formatted ?? '',
        ];
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RecentlyRevisedTaxesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $taxableHeads;

    public function __construct(array $data, array $taxableHeads)
    {
        $this->data = collect($data);
        $this->taxableHeads = $taxableHeads;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $headers = [
            __('Employee ID'),
            __('Employee Name'),
            __('Branch Name'),
            __('Pay Scale'),
            __('Scale No'),
        ];

        foreach ($this->taxableHeads as $headName) {
            $headers[] = __($headName);
        }

        $headers[] = __('Other Income');
        $headers[] = __('Gross Salary');
        $headers[] = __('Old Monthly Tax');
        $headers[] = __('New Monthly Tax');
        $headers[] = __('Tax Change');
        $headers[] = __('Old Net Salary');
        $headers[] = __('New Net Salary');
        $headers[] = __('Net Salary Change');

        return $headers;
    }

    public function map($row): array
    {
        $rowArr = (array) $row;
        $rowHeads = (array) ($rowArr['heads'] ?? []);

        $mapped = [
            $rowArr['employee_id'] ?? '',
            $rowArr['name'] ?? '',
            $rowArr['branch_name'] ?? '',
            $rowArr['payScale'] ?? '',
            $rowArr['scale_no'] ?? '',
        ];

        foreach ($this->taxableHeads as $headName) {
            $mapped[] = $rowHeads[$headName] ?? 0;
        }

        $mapped[] = $rowArr['other_income'] ?? 0;
        $mapped[] = $rowArr['gross'] ?? 0;
        $mapped[] = $rowArr['oldTax'] ?? 0;
        $mapped[] = $rowArr['newTax'] ?? 0;
        $mapped[] = $rowArr['taxChange'] ?? 0;
        $mapped[] = $rowArr['oldNet'] ?? 0;
        $mapped[] = $rowArr['newNet'] ?? 0;
        $mapped[] = $rowArr['netChange'] ?? 0;

        return $mapped;
    }
}

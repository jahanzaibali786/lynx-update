<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class VoucherPrintExport implements FromView, WithColumnWidths, WithEvents
{
    private array $data;
    private Collection $accounts;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->accounts = collect($data['accounts'] ?? []);
    }

    public function view(): View
    {
        return view('vouchers.print.export', [
            'data' => $this->data,
            'accounts' => $this->accounts,
            'groupedAccounts' => collect($this->data['grouped_accounts'] ?? []),
            'payeeRows' => [
                ['Name/Account Title:', $this->data['payee']['name'] ?? ''],
                ['Account no:', $this->data['payee']['account_no'] ?? ''],
                ['Contact:', $this->data['payee']['contact'] ?? ''],
                ['Email:', $this->data['payee']['email'] ?? ''],
                ['NTN/CNIC:', $this->data['payee']['ntn_cnic'] ?? ''],
            ],
            'paymentRows' => [
                ['Payment Mode:', $this->data['payment']['mode'] ?? ''],
                ['Payment Ref:', $this->data['payment']['reference'] ?? ''],
                ['Payment Date:', $this->data['payment']['payment_date'] ?? ''],
                ['Bank Name:', $this->data['payment']['bank_name'] ?? ''],
                ['Invoice No:', $this->data['payment']['invoice_no'] ?? ''],
            ],
            'receiverRows' => [
                ['Name:', $this->data['receiver']['name'] ?? ''],
                ['CNIC no:', $this->data['receiver']['cnic'] ?? ''],
                ['Contact:', $this->data['receiver']['contact'] ?? ''],
                ['Email:', $this->data['receiver']['email'] ?? ''],
                ['Signature:', ''],
            ],
            'titleLogo' => $this->firstExistingPath([
                public_path('assets/image/lynxheadertext.png'),
                public_path('assets/images/lynxheadertext.png'),
                public_path('assets/images/lynxheadertext.jpg'),
                public_path('assets/images/lynxheadertext.webp'),
            ]),
            'headerLogo' => $this->data['header_logo'] ?? '',
            'watermarkLogo' => $this->data['watermark_logo'] ?? '',
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 13,
            'B' => 12,
            'C' => 14,
            'D' => 13,
            'E' => 13,
            'F' => 15,
            'G' => 7.5,
            'H' => 7.5,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getPageMargins()->setTop(0.25);
                $sheet->getPageMargins()->setBottom(0.25);
                $sheet->getPageMargins()->setLeft(0.25);
                $sheet->getPageMargins()->setRight(0.25);

                $sheet->getStyle('A1:H120')->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(false)
                    ->setShrinkToFit(true);
            },
        ];
    }

    private function firstExistingPath(array $paths): string
    {
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return str_replace('\\', '/', $path);
            }
        }

        return '';
    }
}

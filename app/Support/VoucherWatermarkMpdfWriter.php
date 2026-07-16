<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

class VoucherWatermarkMpdfWriter extends Mpdf
{
    private ?string $watermarkPath;

    public function __construct(Spreadsheet $spreadsheet, ?string $watermarkPath = null)
    {
        parent::__construct($spreadsheet);

        $this->watermarkPath = $watermarkPath && file_exists($watermarkPath) ? $watermarkPath : null;
        $this->tempDir = storage_path('app/mpdf-tmp');

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0775, true);
        }
    }

    public function save($filename, int $flags = 0): void
    {
        $fileHandle = parent::prepareForSave($filename);

        $setup = $this->spreadsheet->getSheet($this->getSheetIndex() ?? 0)->getPageSetup();
        $orientation = $this->getOrientation() ?? $setup->getOrientation();
        $orientation = ($orientation === PageSetup::ORIENTATION_LANDSCAPE) ? 'L' : 'P';
        $printPaperSize = $this->getPaperSize() ?? $setup->getPaperSize();
        $paperSize = self::$paperSizes[$printPaperSize] ?? PageSetup::getPaperSizeDefault();

        $pdf = new \Mpdf\Mpdf(['tempDir' => $this->tempDir]);
        $ortmp = $orientation;
        $pdf->_setPageSize($paperSize, $ortmp);
        $pdf->DefOrientation = $orientation;

        if ($this->watermarkPath) {
            $pdf->SetWatermarkImage($this->watermarkPath, 0.4, [85, 66], [62, 106]);
            $pdf->showWatermarkImage = true;
            $pdf->watermarkImgBehind = true;
        }

        $pdf->AddPageByArray([
            'orientation' => $orientation,
            'margin-left' => $this->inchesToMm($this->spreadsheet->getActiveSheet()->getPageMargins()->getLeft()),
            'margin-right' => $this->inchesToMm($this->spreadsheet->getActiveSheet()->getPageMargins()->getRight()),
            'margin-top' => $this->inchesToMm($this->spreadsheet->getActiveSheet()->getPageMargins()->getTop()),
            'margin-bottom' => $this->inchesToMm($this->spreadsheet->getActiveSheet()->getPageMargins()->getBottom()),
        ]);

        $pdf->SetTitle($this->spreadsheet->getProperties()->getTitle());
        $pdf->SetAuthor($this->spreadsheet->getProperties()->getCreator());
        $pdf->SetSubject($this->spreadsheet->getProperties()->getSubject());
        $pdf->SetKeywords($this->spreadsheet->getProperties()->getKeywords());
        $pdf->SetCreator($this->spreadsheet->getProperties()->getCreator());

        $html = $this->generateHTMLAll();
        $bodyLocation = strpos($html, Html::BODY_LINE);

        if ($bodyLocation !== false) {
            $bodyLocation += strlen(Html::BODY_LINE);
            $pdf->WriteHTML(substr($html, 0, $bodyLocation));
            $html = substr($html, $bodyLocation);
        }

        foreach (array_chunk(explode(PHP_EOL, $html), 1000) as $lines) {
            $pdf->WriteHTML(implode(PHP_EOL, $lines));
        }

        fwrite($fileHandle, $pdf->Output('', 'S'));

        parent::restoreStateAfterSave();
    }

    private function inchesToMm($inches): float
    {
        return $inches * 25.4;
    }
}

<?php

declare(strict_types=1);

namespace Infra;

use Application\Flow\GetReport;
use Application\Flow\ExportReportPdf;

class Report
{
    public function __construct(
        private readonly GetReport $getReport,
        private readonly ExportReportPdf $exportPdf
    ) {}

    public function show(?string $format = null): void
    {
        $reportData = $this->getReport->execute();

        if ($format === 'pdf') {
            $pdf = $this->exportPdf->execute($reportData);
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="relatorio.pdf"');
            echo $pdf;
            exit;
        }

        // Renderiza HTML
        include __DIR__ . '/../Presentation/report.html';
    }
}

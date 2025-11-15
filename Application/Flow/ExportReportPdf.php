<?php

declare(strict_types=1);
namespace Application\Flow;

use Mpdf\Mpdf;

class ExportReportPdf
{
    public function execute(array $reportData): string
    {
        $html = $this->buildHtml($reportData);
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        return $mpdf->Output('', 'S');
    }

    private function buildHtml(array $data): string
    {
        $byTypeHtml = '';
        foreach ($data['byType'] as $type => $info) {
            $byTypeHtml .= sprintf(
                '<tr><td>%s</td><td>%d</td><td>R$ %.2f</td></tr>',
                ucfirst($type),
                $info['count'],
                $info['revenue']
            );
        }

        return sprintf(
            '<html><body>
            <h1>Relatório de Estacionamento</h1>
            <p><strong>Total de Veículos:</strong> %d</p>
            <p><strong>Faturamento Total:</strong> R$ %.2f</p>
            <table border="1" cellpadding="10">
                <thead>
                    <tr><th>Tipo</th><th>Quantidade</th><th>Faturamento</th></tr>
                </thead>
                <tbody>%s</tbody>
            </table>
            </body></html>',
            $data['totalVehicles'],
            $data['totalRevenue'],
            $byTypeHtml
        );
    }
}
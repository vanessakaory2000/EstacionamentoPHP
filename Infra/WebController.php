<?php

declare(strict_types=1);

namespace Infra;

use Application\Flow\RegEntry;
use Application\Flow\RegExit;
use Application\Flow\GetReport;
use Application\Flow\ExportReportPdf;
use Application\Flow\ListOpenSessions;
use Application\DTO\RegisterEntryDTO;
use Application\DTO\RegisterExitDTO;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

class WebController
{
    private const TIMEZONE = 'America/Sao_Paulo';

    private DateTimeZone $timezone;

    public function __construct(
        private readonly RegEntry $regEntry,
        private readonly RegExit $regExit,
        private readonly GetReport $getReport,
        private readonly ExportReportPdf $exportPdf,
        private readonly ListOpenSessions $listOpenSessions
    ) {
        $this->timezone = new DateTimeZone(self::TIMEZONE);
    }

    public function handleEntry(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $dto = new RegisterEntryDTO(
                plate: $data['plate'] ?? '',
                vehicleType: $data['vehicleType'] ?? '',
                entryTime: $this->nowString()
            );

            $result = $this->regEntry->execute($dto);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function handleExit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $plate = null;
            if (isset($data['plate'])) {
                $normalizedPlate = strtoupper(trim($data['plate']));
                $plate = $normalizedPlate === '' ? null : $normalizedPlate;
            }

            $dto = new RegisterExitDTO(
                parkingSessionId: isset($data['parkingSessionId']) ? (int) $data['parkingSessionId'] : null,
                plate: $plate,
                exitTime: $this->nowString()
            );

            if ($dto->parkingSessionId === null && $dto->plate === null) {
                throw new InvalidArgumentException('Informe a placa ou o identificador da sessão.');
            }

            $result = $this->regExit->execute($dto);

            header('Content-Type: application/json');
            $sessionDto = $result['session'];

            echo json_encode([
                'success' => true,
                'plate' => $sessionDto->plate,
                'vehicleType' => ucfirst($sessionDto->vehicleType),
                'hours' => $result['hours'],
                'amount' => $sessionDto->amount ?? 0.0,
            ]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function handleReport(): void
    {
        $format = $_GET['format'] ?? 'json';

        try {
            $reportData = $this->getReport->execute();

            if ($format === 'pdf') {
                $pdf = $this->exportPdf->execute($reportData);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="relatorio.pdf"');
                echo $pdf;
            } else {
                header('Content-Type: application/json');
                $byType = [];
                foreach ($reportData['byType'] as $type => $info) {
                    $byType[] = [
                        'vehicleType' => ucfirst($type),
                        'totalSessions' => $info['count'],
                        'totalAmount' => $info['revenue'],
                    ];
                }

                echo json_encode($byType);
            }
        } catch (InvalidArgumentException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function handleOpenSessions(): void
    {
        try {
            $sessions = $this->listOpenSessions->execute();

            $data = [];
            foreach ($sessions as $sessionDto) {
                $data[] = [
                    'id' => $sessionDto->id,
                    'plate' => $sessionDto->plate,
                    'vehicleType' => ucfirst($sessionDto->vehicleType),
                    'entryTime' => $sessionDto->entryTime,
                ];
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (InvalidArgumentException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function handlePage(): void
    {
        include __DIR__ . '/../Presentation/index.html';
    }

    public function handleAppScript(): void
    {
        $scriptPath = __DIR__ . '/../Presentation/index.js';
        if (!is_file($scriptPath)) {
            http_response_code(404);
            return;
        }

        header('Content-Type: application/javascript');
        readfile($scriptPath);
    }

    private function nowString(): string
    {
        return (new DateTimeImmutable('now', $this->timezone))->format('Y-m-d H:i:s');
    }
}

<?php

declare(strict_types=1);
namespace Application\Flow;

use Domain\Interfaces\ParkingSessionRepositoryInterface;

class GetReport
{
    public function __construct(
        private readonly ParkingSessionRepositoryInterface $repository
    ) {
    }

    /** @return array{totalVehicles: int, totalRevenue: float, byType: array} */
    public function execute(): array
    {
        $sessions = $this->repository->findAll();

        $report = [
            'totalVehicles' => 0,
            'totalRevenue' => 0.0,
            'byType' => [
                'carro' => ['count' => 0, 'revenue' => 0.0],
                'moto' => ['count' => 0, 'revenue' => 0.0],
                'caminhao' => ['count' => 0, 'revenue' => 0.0],
            ]
        ];

        foreach ($sessions as $session) {
            if ($session->getExitTime() === null) {
                continue;
            }

            $type = $session->getVehicleType();
            $amount = $session->getAmount() ?? 0.0;

            $report['totalVehicles']++;
            $report['totalRevenue'] += $amount;
            $report['byType'][$type]['count']++;
            $report['byType'][$type]['revenue'] += $amount;
        }

        return $report;
    }
}
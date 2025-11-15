<?php

declare(strict_types=1);

namespace Application\Flow;

use Application\DTO\ParkingSessionDTO;
use Domain\Interfaces\ParkingSessionRepositoryInterface;
use InvalidArgumentException;

class ListOpenSessions
{
    public function __construct(
        private readonly ParkingSessionRepositoryInterface $repository
    ) {}

    /** @return ParkingSessionDTO[] */
    public function execute(): array
    {
        $sessions = $this->repository->findOpenSessions();
        $result = [];

        foreach ($sessions as $session) {
            $id = $session->getId();
            if ($id === null) {
                throw new InvalidArgumentException('Sessão sem identificador.');
            }

            $result[] = new ParkingSessionDTO(
                id: $id,
                plate: $session->getPlate(),
                vehicleType: $session->getVehicleType(),
                entryTime: $session->getEntryTime()->format('Y-m-d H:i:s'),
                exitTime: $session->getExitTime()?->format('Y-m-d H:i:s'),
                amount: $session->getAmount()
            );
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Application\Flow;

use Application\DTO\RegisterEntryDTO;
use Application\DTO\ParkingSessionDTO;
use Domain\Interfaces\ParkingSessionRepositoryInterface;
use Domain\Model\ParkingSession;
use DateTimeImmutable;
use InvalidArgumentException;

class RegEntry
{
    public function __construct(
        private readonly ParkingSessionRepositoryInterface $repository
    ) {}

    /** @return ParkingSessionDTO */
    public function execute(RegisterEntryDTO $dto): ParkingSessionDTO
    {
        if ($this->repository->findOpenByPlate($dto->plate)) {
            throw new InvalidArgumentException('Veículo já está registrado no pátio.');
        }

        $session = new ParkingSession(
            plate: $dto->plate,
            vehicleType: $dto->vehicleType,
            entryTime: new DateTimeImmutable($dto->entryTime)
        );

        $this->repository->save($session);

        $id = $session->getId();
        if ($id === null) {
            throw new InvalidArgumentException('Falha ao gerar identificador da sessão.');
        }

        return new ParkingSessionDTO(
            id: $id,
            plate: $session->getPlate(),
            vehicleType: $session->getVehicleType(),
            entryTime: $session->getEntryTime()->format('Y-m-d H:i:s'),
            exitTime: null,
            amount: null
        );
    }
}

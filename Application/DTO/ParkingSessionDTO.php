<?php

declare(strict_types=1);
namespace Application\DTO;

class ParkingSessionDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $plate,
        public readonly string $vehicleType,
        public readonly string $entryTime,
        public readonly ?string $exitTime,
        public readonly ?float $amount
    ) {
    }
}
<?php

declare(strict_types=1);
namespace Application\DTO;

class RegisterEntryDTO
{
    public function __construct(
        public readonly string $plate,
        public readonly string $vehicleType,
        public readonly string $entryTime
    ) {
    }
}
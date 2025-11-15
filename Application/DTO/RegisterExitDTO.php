<?php

declare(strict_types=1);

namespace Application\DTO;

class RegisterExitDTO
{
    public function __construct(
        public readonly ?int $parkingSessionId,
        public readonly ?string $plate,
        public readonly string $exitTime
    ) {}
}

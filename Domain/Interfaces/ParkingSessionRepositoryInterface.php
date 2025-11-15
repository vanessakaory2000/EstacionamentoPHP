<?php

declare(strict_types=1);

namespace Domain\Interfaces;

use Domain\Model\ParkingSession;

interface ParkingSessionRepositoryInterface
{
    /** @param ParkingSession $session */
    public function save(ParkingSession $session): void;

    /** @return ParkingSession|null */
    public function findById(int $id): ?ParkingSession;

    /** @return ParkingSession|null */
    public function findOpenByPlate(string $plate): ?ParkingSession;

    /** @return ParkingSession[] */
    public function findAll(): array;

    /** @return ParkingSession[] */
    public function findOpenSessions(): array;
}

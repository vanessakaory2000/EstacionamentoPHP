<?php

declare(strict_types=1);

namespace Application\Flow;

use Application\DTO\RegisterExitDTO;
use Application\DTO\ParkingSessionDTO;
use Domain\Interfaces\ParkingSessionRepositoryInterface;
use Domain\Interfaces\PricingStrategyInterface;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

class RegExit
{
    /** @param PricingStrategyInterface[] $pricingStrategies */
    public function __construct(
        private readonly ParkingSessionRepositoryInterface $repository,
        private readonly array $pricingStrategies
    ) {
        $this->timezone = new DateTimeZone(self::TIMEZONE);
    }

    private const TIMEZONE = 'America/Sao_Paulo';

    private DateTimeZone $timezone;

    /** @return array{session: ParkingSessionDTO, hours: int} */
    public function execute(RegisterExitDTO $dto): array
    {
        $session = null;

        if ($dto->parkingSessionId !== null) {
            $session = $this->repository->findById($dto->parkingSessionId);
        }

        if ($session === null && $dto->plate !== null) {
            $session = $this->repository->findOpenByPlate($dto->plate);
        }

        if ($session === null) {
            throw new InvalidArgumentException('Sessão não encontrada.');
        }

        $exitTime = new DateTimeImmutable($dto->exitTime, $this->timezone);
        $session->setExitTime($exitTime);

        $hours = $this->calculateHours($session->getEntryTime(), $exitTime);
        $strategy = $this->findPricingStrategy($session->getVehicleType());
        $amount = $strategy->calculate($hours);

        $session->setAmount($amount);
        $this->repository->save($session);

        $id = $session->getId();
        if ($id === null) {
            throw new InvalidArgumentException('Sessão sem identificador.');
        }

        return [
            'session' => new ParkingSessionDTO(
                id: $id,
                plate: $session->getPlate(),
                vehicleType: $session->getVehicleType(),
                entryTime: $session->getEntryTime()->format('Y-m-d H:i:s'),
                exitTime: $session->getExitTime()?->format('Y-m-d H:i:s'),
                amount: $amount
            ),
            'hours' => $hours,
        ];
    }

    private function calculateHours(DateTimeImmutable $entry, DateTimeImmutable $exit): int
    {
        $seconds = max(0, $exit->getTimestamp() - $entry->getTimestamp());

        // Arredonda sempre para a próxima hora cheia e garante cobrança mínima de 1h
        return (int) max(1, ceil($seconds / 3600));
    }

    private function findPricingStrategy(string $vehicleType): PricingStrategyInterface
    {
        foreach ($this->pricingStrategies as $strategy) {
            if ($strategy->getVehicleType() === $vehicleType) {
                return $strategy;
            }
        }

        throw new InvalidArgumentException("Estratégia de preço não encontrada para: {$vehicleType}");
    }
}

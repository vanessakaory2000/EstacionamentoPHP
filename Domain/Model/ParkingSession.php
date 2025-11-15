<?php

declare(strict_types=1);

namespace Domain\Model;

use DateTimeImmutable;
use InvalidArgumentException;

class ParkingSession
{
    private const VALID_VEHICLE_TYPES = ['carro', 'moto', 'caminhao'];

    private ?int $id;
    private string $plate;
    private string $vehicleType;
    private DateTimeImmutable $entryTime;
    private ?DateTimeImmutable $exitTime;
    private ?float $amount;

    public function __construct(
        string $plate,
        string $vehicleType,
        DateTimeImmutable $entryTime,
        ?DateTimeImmutable $exitTime = null,
        ?float $amount = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->plate = strtoupper(trim($plate));
        $this->entryTime = $entryTime;
        $this->exitTime = $exitTime;
        $this->amount = $amount;

        $this->validatePlate($plate);
        $this->vehicleType = $this->validateVehicleType($vehicleType);
        $this->validateDates($entryTime, $exitTime);
        $this->validateAmount($amount);
    }

    // Validar placa do veículo
    private function validatePlate(string $plate): void
    {
        // É vazia?
        if (trim($plate) === '') {
            throw new InvalidArgumentException('Placa não pode estar vazia.');
        }
    }

    // O tipo de veículo é válido?
    private function validateVehicleType(string $vehicleType): string
    {
        $lower = strtolower(trim($vehicleType));

        // Se não, estoura erro
        if (!in_array($lower, self::VALID_VEHICLE_TYPES, true)) {
            throw new InvalidArgumentException('Tipo de veículo inválido.');
        }

        return $lower;
    }

    // Validar datas
    private function validateDates(DateTimeImmutable $entryTime, ?DateTimeImmutable $exitTime): void
    {
        // Impossível sair antes de entrar
        if ($exitTime !== null && $exitTime < $entryTime) {
            throw new InvalidArgumentException('Horário de saída não pode ser anterior à entrada.');
        }
    }

    // Validar valor da tarifa
    private function validateAmount(?float $amount): void
    {
        // Não faz sentido tarifa negativa
        if ($amount !== null && $amount < 0) {
            throw new InvalidArgumentException('Valor da tarifa não pode ser negativo.');
        }
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlate(): string
    {
        return $this->plate;
    }

    public function getVehicleType(): string
    {
        return $this->vehicleType;
    }

    public function getEntryTime(): DateTimeImmutable
    {
        return $this->entryTime;
    }

    public function getExitTime(): ?DateTimeImmutable
    {
        return $this->exitTime;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    // Setters
    public function setId(int $id): void
    {
        if ($this->id !== null && $this->id !== $id) {
            throw new InvalidArgumentException('ID da sessão já definido.');
        }

        $this->id = $id;
    }

    public function setExitTime(DateTimeImmutable $exitTime): void
    {
        $this->validateDates($this->entryTime, $exitTime);
        $this->exitTime = $exitTime;
    }

    public function setAmount(float $amount): void
    {
        $this->validateAmount($amount);
        $this->amount = $amount;
    }
}

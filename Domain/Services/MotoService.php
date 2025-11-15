<?php

declare(strict_types=1);
namespace Domain\Services;

use Domain\Interfaces\PricingStrategyInterface;

class MotoService implements PricingStrategyInterface
{
    private const HOUR_VALUE = 3.0;
    private const VEHICLE_TYPE = 'moto';

    /** @param int $hours Horas arredondadas para cima
     * @return float Valor em reais */
    public function calculate(int $hours): float
    {
        return self::HOUR_VALUE * $hours;
    }

    /** @return string 'carro', 'moto' ou 'caminhao' */
    public function getVehicleType(): string
    {
        return self::VEHICLE_TYPE;
    }
}
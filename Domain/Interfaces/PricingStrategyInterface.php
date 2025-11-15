<?php

declare(strict_types=1);
namespace Domain\Interfaces;

interface PricingStrategyInterface
{
    /** @param int $hours Horas arredondadas para cima
     * @return float Valor em reais */
    public function calculate(int $hours): float;

    /** @return string 'carro', 'moto' ou 'caminhao' */
    public function getVehicleType(): string;
}
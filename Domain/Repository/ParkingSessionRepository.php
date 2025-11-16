<?php

declare(strict_types=1);

namespace Domain\Repository;

use Domain\Interfaces\ParkingSessionRepositoryInterface;
use Domain\Model\ParkingSession;
use Infra\Connection;
use PDO;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

class ParkingSessionRepository implements ParkingSessionRepositoryInterface
{
    private PDO $pdo;
    private DateTimeZone $timezone;

    private const TIMEZONE = 'America/Sao_Paulo';

    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->connect();
        $this->timezone = new DateTimeZone(self::TIMEZONE);
    }

    public function save(ParkingSession $session): void
    {
        if ($session->getId() === null) {
            $this->insert($session);
        } else {
            $this->update($session);
        }
    }

    private function insert(ParkingSession $session): void
    {
        $sql = <<<SQL
            INSERT INTO parking_sessions (plate, vehicle_type, entry_time, exit_time, amount)
            VALUES (:plate, :vehicle_type, :entry_time, :exit_time, :amount)
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':plate' => $session->getPlate(),
            ':vehicle_type' => $session->getVehicleType(),
            ':entry_time' => $session->getEntryTime()->setTimezone($this->timezone)->format('Y-m-d H:i:s'),
            ':exit_time' => $session->getExitTime()?->setTimezone($this->timezone)->format('Y-m-d H:i:s'),
            ':amount' => $session->getAmount(),
        ]);

        $session->setId((int) $this->pdo->lastInsertId());
    }

    private function update(ParkingSession $session): void
    {
        $sql = <<<SQL
            UPDATE parking_sessions
            SET exit_time = :exit_time, amount = :amount
            WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $id = $session->getId();
        if ($id === null) {
            throw new InvalidArgumentException('Sessão sem identificador para atualização.');
        }

        $stmt->execute([
            ':exit_time' => $session->getExitTime()?->setTimezone($this->timezone)->format('Y-m-d H:i:s'),
            ':amount' => $session->getAmount(),
            ':id' => $id,
        ]);
    }

    public function findById(int $id): ?ParkingSession
    {
        $sql = 'SELECT * FROM parking_sessions WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function findOpenByPlate(string $plate): ?ParkingSession
    {
        $normalizedPlate = strtoupper(trim($plate));
        if ($normalizedPlate === '') {
            return null;
        }

        $sql = 'SELECT * FROM parking_sessions WHERE plate = :plate AND exit_time IS NULL LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':plate' => $normalizedPlate]);

        $row = $stmt->fetch();
        return $row ? $this->mapRowToEntity($row) : null;
    }

    /** @return ParkingSession[] */
    public function findAll(): array
    {
        $sql = 'SELECT * FROM parking_sessions ORDER BY entry_time DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $sessions = [];
        foreach ($stmt->fetchAll() as $row) {
            $sessions[] = $this->mapRowToEntity($row);
        }

        return $sessions;
    }

    /** @return ParkingSession[] */
    public function findOpenSessions(): array
    {
        $sql = 'SELECT * FROM parking_sessions WHERE exit_time IS NULL ORDER BY entry_time ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $sessions = [];
        foreach ($stmt->fetchAll() as $row) {
            $sessions[] = $this->mapRowToEntity($row);
        }

        return $sessions;
    }

    private function mapRowToEntity(array $row): ParkingSession
    {
        $entryTime = new DateTimeImmutable($row['entry_time'], $this->timezone);
        $exitTime = $row['exit_time'] ? new DateTimeImmutable($row['exit_time'], $this->timezone) : null;
        $amount = $row['amount'] !== null ? (float) $row['amount'] : null;

        return new ParkingSession(
            plate: $row['plate'],
            vehicleType: $row['vehicle_type'],
            entryTime: $entryTime,
            exitTime: $exitTime,
            amount: $amount,
            id: (int) $row['id']
        );
    }
}

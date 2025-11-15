<?php

declare(strict_types=1);

namespace Infra;

use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    private static ?PDO $instance = null;
    private readonly string $databasePath;

    public function __construct(string $databasePath = __DIR__ . '/../storage/parking.db')
    {
        $this->databasePath = $databasePath;
    }

    public function connect(): PDO
    {
        if (self::$instance === null) {
            $directory = dirname($this->databasePath);
            if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
                throw new RuntimeException('Não foi possível criar o diretório do banco de dados.');
            }

            $needsInitialization = !file_exists($this->databasePath);

            try {
                self::$instance = new PDO('sqlite:' . $this->databasePath);
            } catch (PDOException $e) {
                throw new RuntimeException('Falha ao conectar ao SQLite: ' . $e->getMessage(), 0, $e);
            }

            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            if ($needsInitialization) {
                $this->initialize(self::$instance);
            }
        }

        return self::$instance;
    }

    private function initialize(PDO $pdo): void
    {
        $sql = <<<SQL
            CREATE TABLE parking_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                plate TEXT NOT NULL,
                vehicle_type TEXT NOT NULL,
                entry_time TEXT NOT NULL,
                exit_time TEXT,
                amount REAL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );
        SQL;

        $pdo->exec($sql);
    }
}

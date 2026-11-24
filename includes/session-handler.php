<?php

class FixflowSessionHandler implements SessionHandlerInterface
{
    private $pdo;
    private $ready = false;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function open(string $path, string $name): bool
    {
        try {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(128) NOT NULL,
                data MEDIUMTEXT NOT NULL,
                last_activity INT UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                KEY idx_sessions_activity (last_activity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            $this->ready = true;
        } catch (PDOException $e) {
            $this->ready = false;
        }
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        if (!$this->ready) {
            return '';
        }
        try {
            $stmt = $this->pdo->prepare('SELECT data FROM sessions WHERE id = ? AND last_activity > ?');
            $stmt->execute([$id, time() - 7200]);
            $row = $stmt->fetch();
            return $row ? (string) $row['data'] : '';
        } catch (PDOException $e) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        if (!$this->ready) {
            return true;
        }
        try {
            $stmt = $this->pdo->prepare('INSERT INTO sessions (id, data, last_activity) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE data = VALUES(data), last_activity = VALUES(last_activity)');
            $stmt->execute([$id, $data, time()]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        if (!$this->ready) {
            return true;
        }
        try {
            $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = ?');
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int
    {
        if (!$this->ready) {
            return 0;
        }
        try {
            $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE last_activity < ?');
            $stmt->execute([time() - (int) $max_lifetime]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return 0;
        }
    }
}

<?php

namespace Az\Session\Driver;

use SessionHandlerInterface;
use \PDO;

final class Db implements SessionHandlerInterface
{
    const CREATE_TABLE_SESSIONS = [
        'mysql' => "CREATE TABLE `sessions` (
            `id` varbinary(192) NOT NULL,
            `last_activity` int(11) NOT NULL DEFAULT '0',
            `data` longtext COLLATE utf8mb4_unicode_ci,
            PRIMARY KEY (`id`))",

        'sqlite' => "CREATE TABLE sessions (
            id text NOT NULL PRIMARY KEY,
            last_activity integer NOT NULL DEFAULT '0',
            data text)"
    ];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $sql = "SELECT data FROM sessions WHERE id = ? LIMIT 1";
        $sth = $this->pdo->prepare($sql);
        $sth->setFetchMode(PDO::FETCH_NUM);
        $sth->execute([$id]);
        $data = $sth->fetchColumn();

        return ($data) ? $data : '';
    }

    public function write(string $id, string $data): bool
    {
        $sql = "REPLACE INTO sessions VALUES (?, ?, ?)";
        $sth = $this->pdo->prepare($sql);
        $sth->execute([$id, time(), $data]);
        return true;
    }

    public function destroy(string $id): bool
    {
        $sql = "DELETE FROM sessions WHERE id = ?";
        $sth = $this->pdo->prepare($sql);
        $sth->execute([$id]);
        return true;
    }

    public function gc(int $maxlifetime): int|false
    {
        $past = time() - $maxlifetime;

        $sql = "DELETE FROM sessions WHERE last_activity < ?";
        $sth = $this->pdo->prepare($sql);
        $sth->execute([$past]);

        return $sth->rowCount();
    }

    public function close(): bool
    {
        return true;
    }

    public function delete(string $id, int $maxlifetime)
    {
        $past = time() - $maxlifetime;

        $sql = "DELETE FROM sessions WHERE last_activity < ? AND id = ?";
        $sth = $this->pdo->prepare($sql);
        $sth->execute([$past, $id]);

        return $sth->rowCount();
    }
}

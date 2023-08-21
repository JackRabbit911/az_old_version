<?php

namespace Sys\Profiler\Model;

use PDO;
use stdClass;
use Sys\Model\BaseModel;

final class Mysql extends BaseModel implements ProfilerModelInterface
{
    const TABLE = "CREATE TABLE `_profilers` (
        `uri` varchar(2048) NOT NULL,
        `size` float unsigned NOT NULL,
        `time` float unsigned NOT NULL,
        `queries` int(3) unsigned NOT NULL,
        `duration` float unsigned NOT NULL,
        `counter` int(10) unsigned NOT NULL,
        `profiles` json NOT NULL,
        UNIQUE `uri` (`uri`),
        INDEX (`size`, `time`)
      ) ENGINE='InnoDB' COLLATE 'latin1_bin'";

    protected string $table = '_profilers';

    public function setProfiling(): void
    {
        $this->qb->query("SET profiling = 1");
    }

    public function set($data): void
    {
        $this->qb->table($this->table)
            ->onDuplicateKeyUpdate($data)
            ->insert($data);
    }

    public function get($uri)
    {
        $sth = $this->qb->pdo()->prepare("SELECT * FROM $this->table WHERE `uri` = ?");
        $sth->execute([$uri]);
        return $sth->fetchObject();
    }

    public function showProfiles(): array
    {
         return $this->qb->pdo()->query("SHOW PROFILES")->fetchAll();
    }

    public function truncate()
    {
        $this->qb->query("TRUNCATE TABLE `_profilers`");
    }
}

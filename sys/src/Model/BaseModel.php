<?php

namespace Sys\Model;

use Pecee\Pixie\QueryBuilder\QueryBuilderHandler;
use Sys\Entity\Entity;
use PDO;

abstract class BaseModel implements Saveble
{
    protected QueryBuilderHandler $qb;
    protected string $table;
    protected string $entityClass;

    protected array $cache = [];

    public function __construct(QueryBuilderHandler $qb)
    {
        $this->qb = $qb;
    }

    public function getQueryBuilder(): QueryBuilderHandler
    {
        return $this->qb;
    }

    public function find($value, $column = 'id', $cache = true): ?Entity
    {
        if ($cache && ($entity = $this->cache($value, $column))) {
            return $entity;
        }

        $sql = "SELECT * FROM users WHERE $column = ? LIMIT 1";
        $pdo = $this->qb->pdo();
        $sth = $pdo->prepare($sql);
        $sth->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        $sth->execute([$value]);
        $entity = $sth->fetch();

        if (!$entity) {
            return null;
        }

        return ($cache) ? $this->cache($value, $column, $entity) : $entity;
    }

    public function save(Entity|array $data): void
    {
        $this->protectCall();

        if ($data instanceof Entity) {
            $data = $data->toArray();
        }
        
        $data = array_intersect_key($data, array_flip($this->columns($this->table)));

        $this->qb->table($this->table)
            ->onDuplicateKeyUpdate($data)
            ->insert($data);
    }

    public function cache($value, $column, $entity = null)
    {
        $key = md5((string) $value . $column);

        if (!$entity) {
            return $this->cache[$this->table][$key] ?? null;
        }

        $this->cache[$this->table][$key] = $entity;
        return $entity;
    }

    protected function protectCall()
    {
        if (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['class'] !== ModelCommit::class) {
            throw new \RuntimeException('method "save()" cannot be called from outside the class ' . ModelCommit::class);
        }
    }

    public function columns($table = null): array
    {
        if (!$table) {
            $table = $this->table;
        }
        
        if (isset($this->cache['schema'][$table])) {
            return $this->cache['schema'][$table];
        }

        $sql = "SELECT `COLUMN_NAME`
        FROM `INFORMATION_SCHEMA`.`COLUMNS` 
        WHERE `TABLE_SCHEMA` = DATABASE()  
        AND `TABLE_NAME` = '$table'";

        $sth = $this->qb->pdo()->query($sql);
        $columns = $sth->fetchAll(\PDO::FETCH_COLUMN);

        $this->cache['schema'][$table] = $columns;
        return $columns;
    }

    public function nexAI($table = null): int
    {
        if (!$table) {
            $table = $this->table;
        }

        $sql = "SELECT AUTO_INCREMENT
        FROM information_schema.tables
        WHERE table_name = '$table'
        AND table_schema = DATABASE()";

        $sth = $this->qb->pdo()->query($sql);
        return $sth->fetchColumn();
    }
}

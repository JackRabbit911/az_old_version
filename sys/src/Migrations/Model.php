<?php

namespace Sys\Migrations;

use Sys\Model\BaseModel;
use Pecee\Pixie\QueryBuilder\QueryBuilderHandler;
use PDO;

final class Model extends BaseModel
{
    private File $file;
    private PDO $pdo;

    public function __construct(QueryBuilderHandler $qb, File $file)
    {
        parent::__construct($qb);
        $this->pdo = $qb->pdo();
        $this->file = $file;
    }

    public function get($path = '')
    {
        $dbName = $this->qb->getConnection()->getAdapterConfig()['database'];

        $is_table = $this->qb->table('information_schema.tables')
            ->where('table_schema', '=', $dbName)
            ->where('table_name', '=', 'migrations')
            ->first();

        if (!$is_table) {
            return [];
        }

        return $this->qb->table('migrations')
            ->select('name')
            ->where('path', '=', $path)
            ->setFetchMode(PDO::FETCH_COLUMN)
            ->get();
    }
    
    public function up($up, $path = '')
    {
        $dir = $this->file->getDir();

        // if (!empty($path)) {
        //     $dir .= $path . '/';
        // }

        foreach ($up as $filename) {
            $file = $dir . $filename;
            if (is_file($file)) {
                $class = $this->file->getClassName($filename);
                require_once $file;

                $sql = (new $class)->up();

                if (!empty($sql)) {
                    $sth = $this->pdo->prepare($sql);
                    $sth->execute();
                    $sth->closeCursor();
                    $data[] = [
                        'name' => $filename,
                        'path' => $path,
                    ];

                    $res[] = $filename;
                }               
            }
        }

        if (!empty($data)) {
            $this->qb->table('migrations')->insert($data);
            return $res;
        }

        return false;
    }

    public function down($filename, $path = '')
    {
        $dir = $this->file->getDir();

        // if (!empty($path)) {
        //     $dir .= $path . '/';
        // }

        $file = $dir . $filename;

        $this->qb->table('migrations')
                ->where('name', '=', $filename)
                ->where('path', '=', $path)
                ->delete();

        if (is_file($file)) {
            $class = $this->file->getClassName($filename);
            require_once $file;
            $sql = (new $class)->down();

            if (!empty($sql)) {
                $this->qb->query($sql);
            }

            return $filename;
        }

        return false;
    }
}

<?php

namespace Sys\Install\Model;

use Symfony\Component\Yaml\Yaml;

final class ModelWelcome
{
    private $yaml;

    public function __construct(Yaml $yaml)
    {
        $this->yaml = $yaml;
    }

    public function createDB($dbname)
    {
        $sql = "CREATE DATABASE IF NOT EXISTS `$dbname` COLLATE 'utf8mb4_general_ci';
        CREATE USER IF NOT EXISTS '$dbname'@'%' IDENTIFIED BY '12345';
        GRANT ALL PRIVILEGES ON $dbname.* TO '$dbname'@'%';
        FLUSH PRIVILEGES;";

        $config = $this->yaml->parseFile(realpath(SYSPATH) . '/docker-compose.yml');

        $pass = $config['services']['mysql']['environment']['MYSQL_ROOT_PASSWORD'];
        $dsn = 'mysql:host=mysql';
        $pdo = new PDO($dsn, 'root', $pass);
        return $pdo->exec($sql);
    }
}

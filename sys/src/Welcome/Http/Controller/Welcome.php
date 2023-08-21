<?php

namespace Sys\Welcome\Http\Controller;

use Symfony\Component\Yaml\Yaml;
use Sys\Controller\WebController;
use PDO;

final class Welcome extends WebController
{
    public function __invoke()
    {
        return $this->render('@welcome/welcome', ['title' => 'Welcome']);
    }

    public function db(Yaml $yaml)
    {
        $dbname = 'burime';
        $sql = "CREATE DATABASE IF NOT EXISTS `$dbname` COLLATE 'utf8mb4_general_ci';
        CREATE USER IF NOT EXISTS '$dbname'@'%' IDENTIFIED BY '12345';
        GRANT ALL PRIVILEGES ON $dbname.* TO '$dbname'@'%';
        FLUSH PRIVILEGES;";

        $config = $yaml->parseFile(realpath(SYSPATH) . '/docker-compose.yml');

        $pass = $config['services']['mysql']['environment']['MYSQL_ROOT_PASSWORD'];
        $dsn = 'mysql:host=mysql';
        $pdo = new PDO($dsn, 'root', $pass);
        $res = $pdo->exec($sql);
        return $this->render('@welcome/welcome', ['title' => 'Create database', 'res' => $res]);
    }

    protected function _before()
    {
        $this->tpl->getEngine()
            ->getLoader()
            ->addPath(realpath(SYSPATH) . '/vendor/az/sys/src/Welcome/views', 'welcome');
    }
}

<?php

use Sys\Helper\Facade\Dir;

if (is_dir('vendor/az/sys/src/install')) {
    if (!is_file('composer.json')) {
        rename('vendor/az/sys/src/install/composer.json', 'composer.json');
        exec('composer update --no-progress');
        echo PHP_EOL, '==================================', PHP_EOL, PHP_EOL;
    }
}

$autoload = getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once $autoload;

if (isset($argv[1]) && $argv[1] === '--dev') {
    $dev = true;
} else {
    $dev = false;
}

if (!$dev) {
    if (is_dir('.git')) {
        Dir::removeAll('.git');
    }

    if (is_file('.gitignore')) {
        unlink('.gitignore');
    }
}

if (is_dir('vendor/az/sys/src/htdocs') && !is_dir('htdocs')) {
    rename('vendor/az/sys/src/htdocs', 'htdocs');
}

if (is_dir('vendor/az/sys/src/install')) {
    if ($dev && is_file('vendor/az/sys/src/install/.gitignore')) {
        rename('vendor/az/sys/src/install/.gitignore', '.gitignore');
    }
    
    if (!is_file('docker-compose.yml')) {
        rename('vendor/az/sys/src/install/docker-compose.yml', 'docker-compose.yml');
    }

    if (!is_dir('httpd')) {
        rename('vendor/az/sys/src/install/httpd', 'httpd');
    }    
}

Dir::removeAll('vendor/az/sys/src/install');

if (!is_file('htdocs/www/.env')) {
    $project_name = basename(getcwd());

    foreach (file('httpd/default.conf') as $str) {
        if (strpos($str, 'DocumentRoot') !== false) {
            $str = str_replace('DocumentRoot', '', $str);
            $ide_search = trim($str);
            break;
        }
    }

    $ide_replace = 'vscode://file' . realpath('htdocs/www');

    $str = <<<ENV
    project_name: $project_name
    host: localhost
    env: !php/const DEVELOPMENT
    connect:
        mysql:
            dsn: 'mysql:dbname=test;host=mysql'
            host: mysql
            database: $project_name
            username: $project_name
            password: '12345'
        sqlite:
            driver: sqlite
            database: app/storage/data/data.sdb
        memcache:
            server: localhost
            port: 11211
        ftp:
            host: ftp.hostname
            username: username
            password: password
        git: 'https://github.com/Owner/projectName'
    mail:
        is_smtp: true
        smtp: fakesmtp
        smtp_port: 1025
        smtp_auth: false
        mailboxes:
            -
                address: robot@site.zone
                password: ''
                name: $project_name
    ide:
        search: '$ide_search'
        replace: '$ide_replace'
    ENV;

    file_put_contents('htdocs/www/.env', $str);
}

echo 'Installation completed!', PHP_EOL, 
    'run the command "docker compose up -d" or "docker-compose up -d"',
    PHP_EOL;

unlink(__FILE__);

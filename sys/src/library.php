<?php

use Sys\I18n\I18n;
use Az\Session\Session;
use Az\Validation\Csrf;
use Az\Route\RouteCollectionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Yaml\Yaml;

function dd(...$values)
{
    ob_start();
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        echo 'file: ', $trace[0]['file'], ' line: ', $trace[0]['line'], '<br>';
        var_dump(...$values);
    $output = ob_get_clean();

    echo (getMode() === 'web') ? $output : strip_tags($output, ['<br>', '<pre>']);
    exit;
}

function env()
{
    static $obj;

    if (is_object($obj)) {
        return $obj;
    }

    $data = Yaml::parseFile(APPPATH . '.env', Yaml::PARSE_CONSTANT);

    // dd($data);

    $obj = new class($data)
    {
        private $data;

        public function __construct($data)
        {
            $this->data = $data;
        }

        public function __get($name)
        {
            return (is_scalar($this->data[$name])) ? $this->data[$name] : new self($this->data[$name]);
        }

        public function array()
        {
            $data = $this->data;

            foreach (func_get_args() as $key) {
                $data = $data[$key];
            }

            return $data;
        }
    };

    return $obj;
}

function dot(&$arr, $path, $default = null, $separator = '.') {
    $keys = explode($separator, $path);

    foreach ($keys as $key) {

        if (!is_array($arr) || !array_key_exists($key, $arr)) {
            // throw new ErrorException("Undefined array key '$key'", 0, E_WARNING);
            $arr = $default;
        } else {
            $arr = &$arr[$key];
        }

        
    }

    return $arr;
}

function getMode()
{
    static $mode;

    if (isset($mode)) {
        return $mode;
    }

    $arrMode = require_once CONFIGPATH . 'mode.php';

    foreach ($arrMode as $key => $paths) {
        foreach ($paths as $path) {
            if (strpos($_SERVER['REQUEST_URI'], $path) === 0) {
                $mode = $key;
                return $mode;
            }
        }
    }

    $mode = 'web';
    return $mode;
}

function __(string $string, ?array $values = null): string
{
    global $container;
    $i18n = $container->get(I18n::class);
    return $i18n->gettext($string, $values);
}

function path($routeName, $params = [])
{
    global $container;
    $routeCollection = $container->get(RouteCollectionInterface::class);
    $route = $routeCollection->getRoute($routeName);

    if (!array_key_exists('lang', $params) && $container->has(I18n::class)) {
        $i18n = $container->get(I18n::class);
        $params['lang'] = rtrim($i18n->langSegment(), '/');
    }

    return $route->path($params);
}

function url($routeName = null, $params = [])
{
    global $container;
    $request = $container->get(ServerRequestInterface::class);
    $scheme = getScheme($request);
    $host = $request->getServerParams()['SERVER_NAME'];

    $path = ($routeName) ? path($routeName, $params) : $request->getServerParams()['REQUEST_URI'];

    return $scheme . '://' . $host . $path;
}

function json(?string $string)
{
    if (empty($string)) {
        return [];
    }

    return json_decode($string, true) ?? [];
}

function createCsrf()
{
    global $container;
    
    $token = (new Csrf())->getToken();
    $session = $container->get(Session::class);
    $session->flash('_csrf', $token);
    return $token;
}

function getScheme($request)
{
    $serverParams = $request->getServerParams();

    if (isset($serverParams['HTTPS'])) {
        $scheme = $serverParams['HTTPS'];
    } else {
        $scheme = '';
    }

    if (($scheme) && ($scheme != 'off')) {
        return'https';
    }
    else {
        return 'http';
    }
}

function render($file, $data)
{
    extract($data, EXTR_SKIP);               
    ob_start();
    include $file;
    return ob_get_clean();
}

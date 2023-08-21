<?php

namespace Sys\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TemplateFactory
{
    public function create($config = null): Template
    {
        $viewPath = $config['view_path'] ?? APPPATH . 'app/views';
        $options = $config['options'] ?? [];

        $loader = new FilesystemLoader($viewPath);
        $twig = new Environment($loader, $options);
        
        $twig->addFunction(new TwigFunction('path', function ($routeName, $params = []) {
            return path($routeName, $params);
        }));

        $twig->addFunction(new TwigFunction('url', function ($routeName, $params = []) {
            return url($routeName, $params);
        }));

        $twig->addFunction(new TwigFunction('json', function ($string) {
            return json($string);
        }));

        $twig->addFunction(new TwigFunction('csrf', function () {
            return '<input name="_csrf" type="hidden" value="' . createCsrf() . '">';
        }));

        $twig->addFunction(new TwigFunction('method', function ($method) {
            return '<input name="_method" type="hidden" value="' . $method . '">';
        }));

        $twig->addFunction(new TwigFunction('base64', function ($path) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }));

        return new Template($twig, 'twig');
    }   
}

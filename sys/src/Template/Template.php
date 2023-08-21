<?php

namespace Sys\Template;

use RuntimeException;
use Twig\TwigFunction;

class Template
{
    private $engine;
    private $ext;
    private $path;

    public function __construct($engine, string $ext = null)
    {
        $this->engine = $engine;
        $this->ext = $ext;
    }

    public function addGlobal($name, $value)
    {
        $this->engine->addGlobal($name, $value);
    }

    public function addFunction(string $name, callable $callback): void
    {
        if ($this->ext === 'twig') {
            $this->engine->addFunction(new TwigFunction($name, $callback));
        }
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function path($path)
    {
        $this->path = trim($path, '/') . '/';
    }

    public function render(string $view, array $params = [])
    {
        $ext = pathinfo('app/views/bs/home', PATHINFO_EXTENSION);

        if (empty($ext)) {
            $ext = $this->ext;
        }
        
        $view = ltrim($this->path . ltrim($view, '/'));
        return $this->engine->render($view . '.' .$ext, $params);
    }
}

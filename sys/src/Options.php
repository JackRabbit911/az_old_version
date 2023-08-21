<?php

namespace Sys;

trait Options
{
    private $path = CONFIGPATH;

    public function options(?string $configFile = null): void
    {
        if (!$configFile) {
             $configFile = strtolower(basename(str_replace('\\', '/', __CLASS__)) . '.php');
        }

        if (pathinfo($configFile, PATHINFO_EXTENSION) === '') {
            $configFile .= '.php';
        }

        if (!is_file($configFile)) {
            $configFile = $this->path . $configFile;
        }

        if (is_file($configFile)) {
            $this->setOptions(include $configFile);
        }
    }

    public function setOptions(array $options): void
    {
        foreach ($options as $key => $value) {     
            if (is_array($value)) {
                $this->$key = array_replace_recursive($this->$key, $value);
            } else {
                if (isset(static::$$key)) {
                    static::$$key = $value;
                } else {
                    $this->$key = $value;
                }
            }        
        }
    }
}

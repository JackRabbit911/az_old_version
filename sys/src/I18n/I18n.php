<?php

namespace Sys\I18n;

use Sys\I18n\Model\I18nModelInterface;
use Sys\Options;
// use Sys\I18n\Model\File;

final class I18n
{
    use Options;

    const NONE = 0;
    const LANG_TO_EMPTY = 1;
    const EMPTY_TO_LANG = 2;

    public readonly string $langSegment;
    public readonly array $linksList;
    private string $lang;
    private array $langs = ['en' => 'English'];
    private I18nModelInterface $model;
    private int $redirect = self::NONE;

    public function __construct(I18nModelInterface $model)
    {
        $this->options();
        $this->lang = $this->baseLang();
        $this->model = $model;
    }

    public function __call($name, $arguments)
    {
        return $$name;
    }

    public function setVar($name, $value)
    {
        $this->$name = $value;
    }

    public function lang(?string $lang = null): string
    {
        if ($lang) {
            $this->lang = $lang;
        }

        return $this->lang;
    }

    public function list($all = false): array
    {
        $langs = $this->langs;

        if (!$all) {
            unset($langs[$this->lang]);
        }

        return $langs;
    }

    public function baseLang()
    {
        return array_key_first($this->langs);
    }

    public function regex(): string
    {        
        $regex = implode('|', array_keys($this->langs));

        if ($this->redirect > self::NONE) {
            $regex .= '|';
        }

        return $regex;
    }

    public function language($lang = null): string
    {
        if (!$lang) {
            $lang = $this->lang;
        }

        return $this->langs[$lang];
    }

    public function langSegment(?string $lang = null): string
    {
        if (!$lang) {
            $lang = $this->lang;
        }

        if ($this->redirect === self::LANG_TO_EMPTY 
            && $this->baseLang() === $lang) {
                return '';
        }
        
        return $lang;
    }

    public function getRedirect()
    {
        return $this->redirect;
    }

    public function addPath(string $path)
    {
        $this->model->addPath($path);
    }

    public function gettext(string $string, array $values = null): string
    {
        $string = $this->model->get($this->lang, $string);
        return ($values) ? strtr($string, $values) : $string;
    }
}

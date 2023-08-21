<?php

namespace Sys\I18n\Model;

interface I18nModelInterface
{
    public function get(string $lang, string $str, array $values = []): string;

    public function addPath(string $path): void;
}

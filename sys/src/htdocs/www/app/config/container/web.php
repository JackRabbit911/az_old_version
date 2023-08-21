<?php

use Sys\I18n\Model\File;
use Sys\I18n\Model\I18nModelInterface;
use Sys\Template\Template;
use Sys\Template\TemplateFactory;

return [
    I18nModelInterface::class => fn() => new File,
    Template::class => fn() => (new TemplateFactory())->create(require_once CONFIGPATH . 'template.php'),
];

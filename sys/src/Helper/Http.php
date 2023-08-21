<?php

namespace Sys\Helper;

use Psr\Http\Message\ServerRequestInterface;

class Http
{
    private $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function isAjax()
    {
        $ajax = $this->request->getServerParams()['HTTP_X_REQUESTED_WITH'] ?? false;
        return ($ajax == 'XMLHttpRequest');
    }

    public function getResponseType(array $mimeTypes = null, string $typeIsAjax = 'json')
    {
        static $mimeTypes;

        if (!$mimeTypes) {
            $mimeTypes = $this->getSortedMimeTypesByRequest();
        }

        if ($this->isAjax()) {
            return $typeIsAjax;
        }

        foreach ($mimeTypes as $mimeType) {
            if ($mimeType === 'text/html' || $mimeType === '*/*') {
                return 'html';
            }

            if ($mimeType === 'text/plain') {
                return 'text';
            }

            if ($mimeType === 'application/json') {
                return 'json';
            }

            if ($mimeType === 'application/xml' || $mimeType === 'text/xml') {
                return 'xml';
            }
        }

        return 'html';
    }

    private function getSortedMimeTypesByRequest(): array
    {
        if (!$acceptParameters = $this->request->getHeaderLine('accept')) {
            return [];
        }

        $mimeTypes = [];

        foreach (explode(',', $acceptParameters) as $acceptParameter) {
            $parts = explode(';', $acceptParameter);

            if (!isset($parts[0]) || isset($mimeTypes[$parts[0]]) || !($mimeType = strtolower(trim($parts[0])))) {
                continue;
            }

            if (!isset($parts[1])) {
                $mimeTypes[$mimeType] = 1.0;
                continue;
            }

            if (preg_match('/^\s*q=\s*(0(?:\.\d{1,3})?|1(?:\.0{1,3})?)\s*$/i', $parts[1], $matches)) {
                $mimeTypes[$mimeType] = (float) ($matches[1] ?? 1.0);
            }
        }

        uasort($mimeTypes, static fn(float $a, float $b) => ($a === $b) ? 0 : ($a > $b ? -1 : 1));
        return array_keys($mimeTypes);
    }
}

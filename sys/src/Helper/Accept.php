<?php

namespace Az\Helper;

use Psr\Http\Message\ServerRequestInterface;

class Accept
{
    const DEFAULT_QUALITY = 1;

    private $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function type($part = null)
    {
        return $this->accept('Accept', $part);
    }

    public function isPreferredTypeJson()
    {
        $types = $this->type();

        $html = $types['text/html'] ?? (float) 0;
        $json = $types['application/json'] ?? (float) 0;

        return ($json > $html);
    }

    public function encoding($part = null)
    {
        return $this->accept('Accept-Encoding', $part);
    }

    public function charset($part = null)
    {
        return $this->accept('Accept-Charset', $part);
    }

    public function quality(string $header)
    {
        $parsed = array();

        $parts = explode(',', $header);

        // Resource light iteration
        $parts_keys = array_keys($parts);
        foreach ($parts_keys as $key)
        {
            $value = trim(str_replace(array("\r", "\n"), '', $parts[$key]));

            $pattern = '~\b(\;\s*+)?q\s*+=\s*+([.0-9]+)~';

            // If there is no quality directive, return default
            if ( ! preg_match($pattern, $value, $quality))
            {
                $parsed[$value] = (float) self::DEFAULT_QUALITY;
            }
            else
            {
                $quality = $quality[2];

                if ($quality[0] === '.')
                {
                        $quality = '0'.$quality;
                }

                // Remove the quality value from the string and apply quality
                $parsed[trim(preg_replace($pattern, '', $value, 1), '; ')] = (float) $quality;
            }
        }

        return $parsed;
    }

    private function accept(string $headerKey, ?string $part = null): float|array
    {
        $headerValue = $this->request->getHeaderLine($headerKey);
        $arrayAccept = $this->quality($headerValue);
        return ($part) ? $arrayAccept[$part] ?? (float) 0 : $arrayAccept;
    }
}

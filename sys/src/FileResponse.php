<?php

namespace Sys;

use Sys\Helper\Facade\File;
use HttpSoft\Response\ResponseExtensionTrait;
use HttpSoft\Response\ResponseStatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

class FileResponse implements ResponseInterface, ResponseStatusCodeInterface
{
    use ResponseExtensionTrait;

    /**
     * @param string $file
     * @param int $code
     * @param array $headers
     * @param string $protocol
     * @param string $reasonPhrase
     */
    public function __construct(
        string $file,
        int $lifetime = 0,
        int $code = self::STATUS_OK,
        array $headers = [],
        string $protocol = '1.1',
        string $reasonPhrase = ''
    ) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $contentType = finfo_file($finfo, $file);
        $content = file_get_contents($file);

        if ($contentType == 'text/plain') {
            $contentType = File::mime($file) ?? 'text/plain';
        }
        
        header_remove();

        $headers = [
            'Content-Type' => $contentType,
            'Content-length' => filesize($file),
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline',
            'Content-Transfer-Encoding' => 'binary',
        ];

        if ($lifetime > 0) {
            $headers['Cache-Control'] = 'private, max-age='.$lifetime;
        }
        
        $this->init($code, $reasonPhrase, $headers, $this->createBody($content), $protocol);
    }
}

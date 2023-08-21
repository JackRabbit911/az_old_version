<?php

namespace Sys\Exception;

use Psr\Http\Message\ResponseInterface;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\XmlResponse;
use HttpSoft\Response\TextResponse;

final class ExceptionResponseFactory implements HttpExceptionInterface
{
    private $view;

    public function __construct(string $view = SYSPATH . 'vendor/az/sys/src/Exception/views/http.php')
    {
        $this->view = $view;
    }

    public function createResponse(string $responseType, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if ($reasonPhrase === '') {
            $reasonPhrase = HttpExceptionInterface::ERROR_PHRASES[$code] ?? 'Page not found';
        }

        switch($responseType) {
            case 'xml':
                $xml = '<?xml version="1.0" encoding="utf-8"?>';
                $xml .= '<error><code>' .$code .'</code><message>' . $reasonPhrase . '</message></error>';
                return new XmlResponse($xml, $code);
                break;
            case 'text':
                $text = $code . ' | ' . $reasonPhrase;
                return new TextResponse($text, $code);
                break;
            case 'json':
                $array = ['error' => ['code' => $code, 'message' => $reasonPhrase]];
                return new JsonResponse($array, $code);
                break;
            default:
                $html = $this->render($this->view, ['code' => $code, 'msg' => $reasonPhrase]);
                return new HtmlResponse($html, $code);
        }
    }

    private function render($file, $data)
    {
        extract($data, EXTR_SKIP);               
        ob_start();
        include $file;
        return ob_get_clean();
    }
}

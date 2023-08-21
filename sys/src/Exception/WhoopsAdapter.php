<?php

namespace Sys\Exception;

use HttpSoft\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

final class WhoopsAdapter implements SetErrorHandlerInterface
{
    private string $logfile;

    public function __construct(ServerRequestInterface $request, ?string $logfile = null)
    {
        $this->logfile = (empty($logfile)) ? realpath(APPPATH) . '/app/storage/error.log' : $logfile;

        $accept_header = $request->getHeaderLine('accept');
        $whoops = new \Whoops\Run;

        if (\Whoops\Util\Misc::isAjaxRequest()) {
            $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler);
            $responseType = 'json';
        } elseif (\Whoops\Util\Misc::isCommandLine()) {
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler);
            $responseType = 'text';
        } elseif (strpos($accept_header, 'text/html') === 0) {
            $handler = new \Whoops\Handler\PrettyPageHandler;
            $this->setEditor($handler);
            $whoops->pushHandler($handler);
            $responseType = 'html';
        } else {
            $this->pushMimeHandler($whoops, $accept_header);
        }

        if (ini_get('display_errors') == 0) {
            $this->pushHttpHandler($whoops, $responseType);
        }

        $this->pushLogHandler($whoops);

        $whoops->register();
    }

    private function pushMimeHandler($whoops, $accept_header)
    {
        $mimeNegotiator = new MimeNegotiator($accept_header);
        $responseType = $mimeNegotiator->getResponseType();

        switch ($responseType) {
            case 'json':
                $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler);
                break;
            case 'text':
                $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler);
                $whoops->allowQuit(false);
                break;
            case 'xml':
                $whoops->pushHandler(new \Whoops\Handler\XmlResponseHandler);
                break;
            default:
                if (ini_get('display_errors') != 0) {
                    $handler = new \Whoops\Handler\PrettyPageHandler;
                    $this->setEditor($handler);
                    $whoops->pushHandler($handler);
                }
        }
    }

    private function pushHttpHandler($whoops, $responseType)
    {
        $whoops->pushHandler(function($exception, $inspector, $run) use ($responseType) {
            $factory = new ExceptionResponseFactory();
            $emitter = new SapiEmitter();
            $run->sendHttpCode(503);             
            $response = $factory->createResponse($responseType, 503);
            $emitter->emit($response);

            return \Whoops\Handler\Handler::QUIT;
        });
    }

    private function pushLogHandler($whoops)
    {
        $logger = new Logger('e');
        $logger->setTimezone(new \DateTimeZone('Europe/Moscow')); //TODO
        $logger->pushHandler(new StreamHandler($this->logfile, Level::Debug));
        // $logger->pushHandler(new FirePHPHandler());
        $whoops->pushHandler(function ($exception, $inspector, $run) use ($logger) {
            $file = str_replace(DOCROOT, '', $exception->getFile());
            $logger->error($inspector->getExceptionMessage().' '.$file, [$exception->getLine()]);
        });
    }

    private function setEditor($handler)
    {
        $handler->setEditor(function ($file, $line) {
            $ide = env()->ide;
            $file = str_replace($ide->search, $ide->replace, $file);           
            return $file . ':' . $line;
        });

        // if (is_file('vendor/dev.php')) {
        //     $handler->setEditor(function ($file, $line) {
        //         // $ide = require DOCROOT . 'vendor/dev.php';
        //         $ide = env()->ide;
        //         $file = str_replace('/var/www/', $ide, $file);                
                
        //         return $file . ':' . $line;
        //     });
        // }
    }
}

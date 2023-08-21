<?php

namespace Sys\Controller;

use HttpSoft\Response\TextResponse;

abstract class CliController extends BaseController
{
    protected function _after(&$response)
    {
        if (is_string($response)) {
            $response = new TextResponse($response, 200, ['Accept' => 'text/plain']);
        }
    }
}

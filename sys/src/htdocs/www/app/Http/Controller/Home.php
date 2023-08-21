<?php

namespace App\Http\Controller;

use Sys\Controller\WebController;

final class Home extends WebController 
{
    public function __invoke()
    {
        return $this->redirect('/~welcome');
    }
}

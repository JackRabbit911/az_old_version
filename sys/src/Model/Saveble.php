<?php

namespace Sys\Model;
use Sys\Entity\Entity;

interface Saveble
{
    public function save(Entity|array $data): void;
}
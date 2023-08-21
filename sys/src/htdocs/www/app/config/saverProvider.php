<?php

use App\Entity\Burime;
use App\Entity\Genre;
use App\Model\ModelBurime;
use App\Model\ModelGenres;

return [
    Burime::class => ModelBurime::class,
    Genre::class => ModelGenres::class,
];

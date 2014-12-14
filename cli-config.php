<?php

if (PHP_SAPI != 'cli') {
    die('this is a console script.');
}

// replace with file to your own project bootstrap
require_once __DIR__ . '/bootstrap.php';

return Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entity_manager);

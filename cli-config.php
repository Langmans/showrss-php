<?php

if (PHP_SAPI != 'cli') {
    die('This is a console script.');
}

// Replace with file to your own project bootstrap
require_once __DIR__ . '/bootstrap.php';

return Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entity_manager);

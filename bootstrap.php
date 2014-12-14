<?php

if (PHP_SAPI != 'cli') {
    die('this is a console script.');
}

use Doctrine\ORM\Tools\Setup;

require_once __DIR__ . '/vendor/autoload.php';
// Create a simple "default" Doctrine ORM configuration for XML Mapping
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/src"), $isDevMode, null, null, false);
// or if you prefer yaml or annotations
//$config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $isDevMode);
//$config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/config/yaml"), $isDevMode);
// database configuration parameters
$conn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/showrss.db',
);
// obtaining the entity manager
$entity_manager = \Doctrine\ORM\EntityManager::create($conn, $config);

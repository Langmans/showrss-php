<?php

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\Tools\Setup;

require_once __DIR__ . '/vendor/autoload.php';

$isDevMode = true;

$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/src"), $isDevMode, 'proxies', null, false);

// If we don't have any means of normal caching, set it to filesystem!
if (!$isDevMode && $config->getQueryCacheImpl() instanceof ArrayCache) {
    $cache = new FilesystemCache(__DIR__ . '/resources/doctrine');
    $config->setMetadataCacheImpl($cache);
    $config->setQueryCacheImpl($cache);
    $config->setResultCacheImpl($cache);
    $config->setAutoGenerateProxyClasses(true);
}

$connection = [
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/resources/database/sqlite.db',
];

// Obtaining the entity manager
$entity_manager = \Doctrine\ORM\EntityManager::create($connection, $config);

<?php

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\Tools\Setup;

require_once __DIR__ . '/vendor/autoload.php';

$isDevMode = true;

$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/src"), $isDevMode, 'proxies', null, false);
// If we dont have any means of normal caching, set it to filesystem!
if (!$isDevMode && $config->getQueryCacheImpl() instanceof ArrayCache) {
    $cache = new FilesystemCache(__DIR__ . '/cache/doctrine');
    $config->setMetadataCacheImpl($cache);
    $config->setQueryCacheImpl($cache);
    $config->setResultCacheImpl($cache);
    $config->setAutoGenerateProxyClasses(true);
}

$connection = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/showrss.db',
);
// obtaining the entity manager
$entity_manager = \Doctrine\ORM\EntityManager::create($connection, $config);

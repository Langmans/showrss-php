<?php

use FastFeed\Factory;
use FastFeed\Item;

require __DIR__ . '/bootstrap.php';

$showrss_config = read_config('showrss', array(
    'user_id' => 0,
    'ttl' => 30 * 60,
    'url' => 'http://showrss.info/rss.php?namespaces=true'
));

if (!$showrss_config['user_id'] || !is_numeric($showrss_config['user_id'])) {
    die('please set your user_id in the config: http://showrss.info/?cs=feeds');
}

$url = $showrss_config['url'];
$url .= strpos($url, '?') ? '&' : '?';
$url .= 'user_id=' . $showrss_config['user_id'];

$feed = Factory::create();
$feed->addFeed('default', $url);
/** @var Item $item */
foreach ($feed->fetch() as $item) {
    $name = $item->getName();
    if (preg_match('@^\s*(?<show>.*)\s+(?<season>\d+)x(?<episode>\d+)(\s+(?<name>.*?))?\s*(?<repack>PROPER|REPACK)?\s*$@',
        $name, $matches)) {
        foreach ($matches as $k => $v) {
            if (is_numeric($k)) {
                unset($matches[$k]);
            }
        }
        $matches += array('name' => '');
        $matches['season'] = (int)$matches['season'];
        $matches['episode'] = (int)$matches['episode'];
        $matches['repack'] = isset($matches['repack']) ? $matches['repack'] : false;

        var_dump($item);
//        var_dump($matches);
    } else {
        echo "$name: no match\n";
    }
}
//var_dump($feed);


defined('JSON_PRETTY_PRINT') || define('JSON_PRETTY_PRINT', 0);

function read_config($name, array $default = array())
{
    $config_file = __DIR__ . '/config/' . $name . '.json';
    if (file_exists($config_file)) {
        $config = json_decode(file_get_contents($config_file), true);
        if (json_last_error()) {
            echo json_last_error_msg(), PHP_EOL;
        }
        if (!is_array($config)) {
            $config = array();
        }
    } else {
        $config = array();
    }
    if (!$config && $default) {
        $config = $default;
        write_config($name, $default);
    }
    return $config;
}

function write_config($name, $config)
{
    $config_file = __DIR__ . '/config/' . $name . '.json';
    if (!is_file($config_file)) {
        $dir = dirname($config_file);
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }
    file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));
}
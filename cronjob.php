<?php

if (PHP_SAPI != 'cli') {
    die('this is a console script.');
}

use FastFeed\Factory;
use FastFeed\Item;

require __DIR__ . '/bootstrap.php';
$em = &$entity_manager;

$showrss_config = read_config('showrss', array(
    'user_id' => 0,
    'ttl' => 30 * 60,
    'url' => 'http://showrss.info/rss.php?namespaces=true&raw=true'
));

if (!$showrss_config['user_id'] || !is_numeric($showrss_config['user_id'])) {
    die('please set your user_id in the config: config/showrss.json (http://showrss.info/?cs=feeds)');
}

$url = $showrss_config['url'];
$url .= strpos($url, '?') ? '&' : '?';
$url .= 'user_id=' . $showrss_config['user_id'];

$feed = Factory::create();
$feed->addFeed('default', $url);

$show_regex = '@
^
\s*
(?<show>
    .*?
)
#(
#        \s*
#        \(
#        (?<year>
#            \d{4}
#        )
#        \)
#    )?
\s+
0*
(?<season>
    \d+
)
x
0*
(?<episode>
    \d+
)
(
    \s+
    (?<name>
        .*?
    )
)?
(
    \s
    (?<quality1>
        (720|1080)p|x264
    )
|
    \s
    (?<repack1>
        REPACK|PROPER
    )
)?
(
    \s
    (?<repack2>
        REPACK|PROPER
    )
|
    \s
    (?<quality2>
        (720|1080)p|x264
    )
)?
\s*
$
@xi';
$show_repo = $em->getRepository('Show');
$episode_repo = $em->getRepository('Episode');

/** @var Item $item */
foreach ($feed->fetch() as $item) {
    $name = $item->getName();
    if (preg_match($show_regex,
        $name, $matches)) {

        echo $name,PHP_EOL;

        foreach ($matches as $k => $v) {
            if (is_numeric($k)) {
                unset($matches[$k]);
            } elseif ((int)$v == $v && $v) {
                $maches[$k] = (int)$v;
            } else {
                $matches[$k] = trim($v);
            }
        }
        // defaults.. dont trigger notice
        $matches += array(
            'show' => '',
            'season' => '',
            'episode' => '',
            'name' => '',
            'quality' => '',
            'repack' => '',
        );
        $matches['season'] = (int)$matches['season'];
        $matches['episode'] = (int)$matches['episode'];
        if (!empty($matches['repack1'])) {
            $matches['repack'] = $matches['repack1'];
        } elseif (!empty($matches['repack2'])) {
            $matches['repack'] = $matches['repack2'];
        }
        unset($matches['repack1'], $matches['repack2']);

        if (!empty($matches['quality1'])) {
            $matches['quality'] = $matches['quality1'];
        } elseif (!empty($matches['quality2'])) {
            $matches['quality'] = $matches['quality2'];
        }
        unset($matches['quality1'], $matches['quality2']);
        echo json_encode($matches),PHP_EOL;

        /**
         * @var Show $show
         */
        // to prevent reinserting
        if (isset($show_objects[$matches['show']])) {
            $show = $show_objects[$matches['show']];
        } else {
            if (!($show = $show_repo->findOneBy(array(
                'name' => $matches['show']
            )))
            ) {
                $show = Show::create(array(
                    'name' => $matches['show']
                ));
            }
            $show_objects[$matches['show']] = $show;
        }

        /**
         * @var Episode $episode
         */
        $episode_string = implode(',',
            array(
                spl_object_hash($show),
                $matches['season'],
                $matches['episode']
            ));
        if (isset($show_objects[$episode_string])) {
            $episode = $show_objects[$episode_string];
        } else {
            if (!$show->getId() ||
                !($episode = $episode_repo->findOneBy(array(
                    'show_id' => $show->getId(),
                    'season_number' => $matches['season'],
                    'episode_number' => $matches['episode']
                )))
            ) {
                $episode = Episode::create(array(
                    'show' => $show,
                    'season_number' => $matches['season'],
                    'episode_number' => $matches['episode']
                ));
            }
            $show_objects[$episode_string] = $episode;
        }
        $episode->setName($matches['name']);

        $show->addEpisode($episode);

        $em->persist($show);

    } else {
        echo "$name: no match\n";
    }
}
$em->flush();

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
    $ret = $config + $default;
    if ($ret != $config) {
        write_config($name, $ret);
    }
    return $ret;
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

/**
 * @param $url
 * @param array $options
 * @return array|mixed|SimpleXMLElement|string
 */
function url_request($url, array $options = array())
{
    $options += array(
        'force_content_type' > false,
        'auto_decode' => false,
        'verbose' => false,
//        'method' => 'GET',
        'raw' => false,
        'json_decode_assoc' => true
    );
    asort($options);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($options['verbose']) {
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
    }
    if (!empty($options['method'])) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method']);
    }
    curl_setopt($ch, CURLOPT_HEADER, 1);

    if ($options['verbose'] == 'capture') {
        ob_start();
    }
    $response = curl_exec($ch);
    if ($options['verbose'] == 'capture') {
        $verbose = ob_get_clean();
    }
    $info = curl_getinfo($ch);
    $header_size = $info['header_size'];
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    if ($options['raw'] === true) {
        $return = $response;
    } elseif ($options['raw'] == 'array') {
        $return = compact('info', 'header', 'body', 'return', 'verbose');
    } elseif ($options['auto_decode'] === true) {
        switch ($options['force_content_type'] ?: $info['content_type']) {
            case 'text/xml':
            case 'application/xml':
                $return = new SimpleXMLElement($body);
                break;
            case 'application/json':
                $return = json_decode($body, $options['json_decode_array']);
                break;
            default:
                $return = $body;
                break;
        }
    } elseif (is_callable($options['auto_decode'])) {
        $return = call_user_func($options['auto_decode'], $body);
    } else {
        $return = $body;
    }

    return $return;
}

/**
 * @param $url
 * @param array $options
 * @return array|mixed|SimpleXMLElement|string
 */
function url_cache($url, array $options = array())
{
    $options += array(
        'ttl' => 3600
    );

    asort($options);
    $file = __DIR__ . '/cache/' . md5($url . serialize($options)) . '.url_cache.txt';
    if (!is_file($file) || filemtime($file) + $options['ttl'] < time()) {
        $data = url_request($url, $options);
        file_put_contents($file, serialize($data));
        return $data;
    }
    return unserialize(file_get_contents($file));
}
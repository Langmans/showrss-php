<?php

if (PHP_SAPI != 'cli') {
    die('this is a console script.');
}

use Desarrolla2\Cache\Adapter\File as FileCacheAdapter;
use Desarrolla2\Cache\Cache;
use FastFeed\Aggregator\RSSContentAggregator;
use FastFeed\Cache\FastFeed;
use FastFeed\Item;
use FastFeed\Logger\Logger;
use FastFeed\Parser\RSSParser;
use rubenvincenten\ShowRss\Config;
use rubenvincenten\ShowRss\Episode;
use rubenvincenten\ShowRss\Show;
use rubenvincenten\ShowRss\ShowRSSAggregator;

require __DIR__ . '/bootstrap.php';
$em = &$entity_manager;

$config = new Config();
$config->setConfigDir(__DIR__ . '/config/showrss.json');

$config->read([
    'user_id' => 0,
    'ttl' => 30 * 60,
    'url' => 'https://showrss.info/user/{user_id}.rss?magnets=true&namespaces=true'
]);

if (!$config->item('user_id') || !is_numeric($config->item('user_id'))) {
    die('Please set your user_id in the config: config/showrss.json (https://showrss.info/feeds)');
}

$sUrl = $config->item('url');
$sUrl = str_replace('{user_id}', $config->item('user_id'), $sUrl);

$channel = 'showrss-php-' . $config->item('user_id');

$feed = new FastFeed(new \Guzzle\Http\Client(), new Logger('fastfeed_log.txt'));
$cache_adapter = new FileCacheAdapter(__DIR__ . '/cache/fastfeed');
$cache_adapter->setOption('ttl', $config->item('ttl'));
$cache = new Cache($cache_adapter);
$feed->setCache($cache);
$parser = new RSSParser();
$parser->pushAggregator(new ShowRSSAggregator());
$parser->pushAggregator(new RSSContentAggregator());
$feed->pushParser($parser);
$feed->addFeed($channel, $sUrl);

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
    (?<quality1>
        (720|1080)p|x264
    )
|
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
$show_repo = $em->getRepository('rubenvincenten\ShowRss\Show');
$episode_repo = $em->getRepository('rubenvincenten\ShowRss\Episode');

/** @var Item $item */
foreach ($feed->fetch($channel) as $item) {
    $name = $item->getName();
    if (preg_match($show_regex,
        $name, $matches)) {

        echo $name, PHP_EOL;

        foreach ($matches as $k => $v) {
            if (is_numeric($k)) {
                unset($matches[$k]);
            } elseif ((int)$v == $v && $v) {
                $matches[$k] = (int)$v;
            } else {
                $matches[$k] = trim($v);
            }
        }

        // Defaults.. Don't trigger notice
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
        echo json_encode($matches), PHP_EOL;

        /**
         * @var Show $show
         */
        $show_name = $item->getExtra('showname') ?: $matches['show'];
        // to prevent reinserting
        if (isset($show_objects[$show_name])) {
            $show = $show_objects[$show_name];
        } else {
            $fields = array(
                'name' => $show_name
            );
            if (!($show = $show_repo->findOneBy($fields))) {
                $show = Show::create($fields);
            }
            $show_objects[$show_name] = $show;
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
            // If show isnt saved, or if episode cant be found
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

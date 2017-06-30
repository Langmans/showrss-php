<?php

/**
 * This file is part of the FastFeed package.
 *
 * Copyright (c) Daniel González
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Daniel González <daniel@desarrolla2.com>
 * @author Ruben Vincenten <ruben@dragonmediagroup.nl>
 */

namespace rubenvincenten\ShowRss;

use FastFeed\Aggregator;
use FastFeed\Item;

class ShowRSSAggregator extends Aggregator\AbstractAggregator implements Aggregator\AggregatorInterface
{
    protected $keys = array('showid', 'showname', 'episode', 'info_hash', 'rawtitle');

    /**
     * Execute the Aggregator
     *
     * @param \DOMElement $node
     * @param Item $item
     */

    public function process(\DOMElement $node, Item $item)
    {
        foreach ($this->keys as $key) {
            $item->setExtra($key, $this->getValue($node, $key));
        }
    }

    /**
     * @param \DOMElement $node
     * @param string $tagName
     *
     * @return bool|string
     */
    public function getValue(\DOMElement $node, $tagName)
    {
        return $this->getNodeValueByTagNameNS($node, 'http://showrss.info/', $tagName);
    }
}
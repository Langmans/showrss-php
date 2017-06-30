<?php

namespace rubenvincenten\ShowRss;

use Doctrine\Common\Inflector\Inflector;

/**
 * Class BaseEntity
 */
abstract class BaseEntity
{
    /**
     * @param array $fields
     * @return static
     */
    static function create(array $fields)
    {
        $object = new static;
        foreach ($fields as $key => $value) {
            if (
                is_string($key) & !is_numeric($key) &&
                ($callable = array($object, 'set' . Inflector::camelize($key))) &&
                is_callable($callable)
            ) {
                call_user_func($callable, $value);
            }
        }
        return $object;
    }
}
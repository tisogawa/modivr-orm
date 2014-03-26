<?php

namespace ModivrOrm;

/**
 * Class Util
 * @package ModivrOrm
 */
final class Util
{
    /**
     * @param array $params
     * @return string
     */
    public static function createAndClauseStringFromArray(array $params)
    {
        $tmp = array();
        foreach ($params as $key => $value) {
            $tmp[] = "$key = :$key";
        }
        return implode(' AND ', $tmp);
    }

    /**
     * @param array $options
     * @return string
     */
    public static function createOptionsStringFromArray(array $options)
    {
        $tmp = array();
        if (isset($options['order_by'])) {
            $tmp[] = sprintf('ORDER BY %s', $options['order_by']);
        }
        if (isset($options['limit'])) {
            $tmp[] = sprintf('LIMIT %s', $options['limit']);
        }
        return implode(' ', $tmp);
    }

    /**
     * @param $camel_cased_word
     * @return string
     */
    public static function underscore($camel_cased_word)
    {
        $tmp = $camel_cased_word;
        $tmp = str_replace('::', '/', $tmp);
        $tmp = preg_replace(
            array(
                '/([A-Z]+)([A-Z][a-z])/',
                '/([a-z\d])([A-Z])/'),
            array(
                '\\1_\\2',
                '\\1_\\2'),
            $tmp
        );
        return strtolower($tmp);
    }
}

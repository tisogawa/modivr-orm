<?php

namespace SimpleOrm;

/**
 * Interface ConfigurationInterface
 * @package SimpleOrm
 */
interface ConfigurationInterface
{
    /**
     * @return string
     */
    public function getDsn();

    /**
     * @return string
     */
    public function getUsername();

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @return array
     */
    public function getOptions();
}

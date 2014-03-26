<?php

namespace ModivrOrm;

/**
 * Interface ConfigurationInterface
 * @package ModivrOrm
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

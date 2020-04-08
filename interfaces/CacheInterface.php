<?php

namespace Interfaces;

interface CacheInterface {
    /**
     * @param string|array $names
     * @return string|array
     */
    public function get($names);

    /**
     * @param string|array $names
     * @param null|string $value
     * @return mixed
     */
    public function set($names, $value = null);

    /**
     * @param string|array $names
     * @return boolean
     */
    public function clear($names = []);
}
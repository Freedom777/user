<?php

namespace Models;

use \Memcache as Cache;

class Memcache implements \Interfaces\CacheInterface {
    protected $cache = null;
    const CONNECT_TIMEOUT = 30;

    /**
     * Memcache constructor.
     *
     * @param string $host
     * @param int $port
     * @param int $timeout
     *
     * @return boolean
     */
    public function __construct($host = 'localhost', $port = 11211, $timeout = self::CONNECT_TIMEOUT)
    {
        $this->cache = new Cache();

        return $this->cache->connect($host, $port, $timeout);
    }

    /**
     * @param string|array $names
     * @return mixed
     */
    public function get($names)
    {
        return $this->cache->get($names);
    }

    /**
     * @param string|array $names
     * @param null|string $value
     * @return boolean
     */
    public function set($names, $value = null)
    {
        $result = true;

        if (is_array($names)) {
            foreach ($names as $name => $value) {
                if (!$this->cache->set($name, $value)) {
                    $result = false;
                }
            }
        } else {
            $result = $this->cache->set($names, $value);
        }

        return $result;
    }

    /**
     * @param string|array $names
     * @return bool
     */
    public function clear($names = [])
    {
        $result = true;

        if (empty($names)) {
            // Clear all
            $result = $this->cache->flush();
        } elseif (is_array($names)) {
            foreach ($names as $name) {
                if (!$this->cache->delete($name)) {
                    $result = false;
                }
            }
        } else {
            $result = $this->cache->delete($names);
        }

        return $result;
    }
}
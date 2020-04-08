<?php

namespace Models;

class App {
    protected static $db;
    protected static $cache;

    public function __construct(\Interfaces\DBInterface $db, \Interfaces\CacheInterface $cache = null)
    {
        self::$db = $db;
        self::$cache = $cache;
    }

    public static function getDb()
    {
        return self::$db->getInstance();
    }

    public static function getCache()
    {
        return self::$cache;
    }
}

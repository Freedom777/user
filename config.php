<?php

return [
    'timezone' => DateTimeZone::listIdentifiers(DateTimeZone::UTC)[0],

    'dbHost' => 'localhost',
    'dbName' => 'app',
    'dbUser' => 'root',
    'dbPass' => '',

    'memcacheHost' => 'localhost',
    'memcachePort' => 11211,
    'memcacheTimeout' => 30,
];
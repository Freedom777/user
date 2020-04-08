<?php

$config = require_once 'config.php';

date_default_timezone_set($config['timezone']);

require_once 'vendor/autoload.php';

$dsn = 'mysql:host='.$config['dbHost'].';dbname='.$config['dbName'];

$app = new Models\App(
    \Models\Mysql::getInstance(['dsn' => $dsn, 'username' => $config['dbUser'], 'password' => $config['dbPass']]),
    new Models\Memcache($config['memcacheHost'], $config['memcachePort'], $config['memcacheTimeout'])
);

$app::getCache()->clear();

$user = new \Models\User();
$csv = new \Models\Csv($user);
$csv->loadToDb(
    './data/user.csv'
);

$user = new \Models\UserCache();
$user->delete(['id' => 1]);
$user->add(['name' => 'Freedom', 'email' => 'olegfreedom777@gmail.com']);
$user->edit(['name' => 'Oleg'], ['email' => 'olegfreedom777@gmail.com']);
echo '<pre>';
print_r($user->get(['name' => 'Oleg']));

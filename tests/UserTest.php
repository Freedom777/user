<?php

namespace Tests;

use Models\App;
use Models\User;
use Models\Memcache;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    const APP_DIR = __DIR__ . '../';

    protected $app = null;

    protected function setUp()
    {
        $config = require_once 'config.php';

        date_default_timezone_set($config['timezone']);
        require_once 'vendor/autoload.php';

        $dsn = 'mysql:host='.$config['dbHost'].';dbname='.$config['dbName'];
        $app = new App(
            \Models\Mysql::getInstance(['dsn' => $dsn, 'username' => $config['dbUser'], 'password' => $config['dbPass']]),
            new Memcache($config['memcacheHost'], $config['memcachePort'], $config['memcacheTimeout'])
        );

        $app::getCache()->clear();
        $app::getDb()->executeSql('TRUNCATE TABLE `' . User::TABLE_NAME . '`');

        $this->app = $app;
    }

    public function testAddUpdateDelete()
    {
        $user = new \Models\UserCache();
        $this->assertTrue($user->add(['name' => 'Freedom', 'email' => 'olegfreedom777@gmail.com']));
        $this->assertTrue($user->edit(['name' => 'Oleg'], ['email' => 'olegfreedom777@gmail.com']));
        $this->assertTrue($user->delete(['email' => 'olegfreedom777@gmail.com']));
    }
}

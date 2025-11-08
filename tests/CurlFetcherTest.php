<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Utils\CurlFetcher;

class CurlFetcherTest extends TestCase
{
    private static int $serverPid;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $cmd = 'php -S localhost:8000 ' . __DIR__ . '/test_server.php > /dev/null & echo $!';
        $output = [];
        exec($cmd, $output);
        self::$serverPid = (int) $output[0];
        sleep(1); // give the server time to start
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$serverPid) {
            exec('kill ' . self::$serverPid);
        }
    }

    public function testFetchURL()
    {
        $url = "http://localhost:8000";
        $fetch = new CurlFetcher(__DIR__ . '/tmp');
        $this->assertEquals( '{ "hello" : "world"}', $fetch->get($url));
        $this->assertTrue($fetch->isCached($url));
        $this->assertEquals( '{ "hello" : "world"}', $fetch->getCache($url));
    }
}

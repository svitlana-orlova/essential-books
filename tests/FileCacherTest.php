<?php

declare(strict_types=1);
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Utils\FileCacher;

class FileCacherTest extends TestCase
{
    public function testCacheExists()
    {
        $url = "http://example.com/data.json";
        $data = "hello world";
        $cache = new FileCacher(__DIR__ . '/tmp');
        $this->assertTrue($cache->putCache($url, $data));
        $this->assertTrue($cache->isCached($url));
        $this->assertEquals($data, $cache->getCache($url));
    }

    public function testCacherUrltoName()
    {
        $cache = new FileCacher();
        $this->assertEquals(md5(""), $cache->getCacheName(""));
    }

    public function testCacherCustomDir()
    {
        $tmpDir = __DIR__ . "/test_cache_dir";

        if (file_exists($tmpDir)) {
            rmdir($tmpDir);
        }

        $this->assertFileDoesNotExist($tmpDir);

        $cache = new FileCacher($tmpDir);
        $this->assertFileExists($tmpDir);

        unset($cache);
        $this->assertFileDoesNotExist($tmpDir);
    }

    public function testCacherDefaultDir()
    {
        $cache = new FileCacher();
        $this->assertIsObject($cache);

        $cacheFolder = $cache->getFolder();
        $this->assertfileExists($cacheFolder);

        unset($cache);
        $this->assertFileDoesNotExist($cacheFolder);
    }

}
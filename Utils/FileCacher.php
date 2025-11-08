<?php

declare(strict_types=1);
namespace Utils;

class FileCacher implements Cacher
{
    private string $cacheFolder;
    private bool $autoClean = true;

    public function __construct($tmpDir = "")
    {
        if (empty($tmpDir)) {
            $tmpDir = sys_get_temp_dir() . "/fetcher_cache";
        }

        if (!$tmpDir) {
            $tmpDir = __DIR__ . "/fetcher_cache";
        }

        if (!file_exists($tmpDir) && mkdir($tmpDir)) {
            $this->cacheFolder = $tmpDir;
        } else if (file_exists($tmpDir)) {
            $this->cacheFolder = $tmpDir;
        } else {
            echo "Unable to create tmpDir: $tmpDir";
        }
    }

    public function __destruct()
    {
       if ($this->autoClean &&
           file_exists($this->cacheFolder) &&
           is_dir($this->cacheFolder))  {

           foreach (glob($this->cacheFolder . '/*') as $file) {
               if (is_file($file)) {
                   unlink($file);
               }
           }
           rmdir($this->cacheFolder);
       }
    }

    public function setAutoClean(bool $autoClean) : bool
    {
        return ($this->autoClean = $autoClean);
    }

    public function getCacheName(string $name) : string
    {
        return md5($name);
    }

    public function getFolder() : string
    {
        return $this->cacheFolder;
    }

    public function getFileName(string $url) : string
    {
        return $this->cacheFolder . '/' . $this->getCacheName($url);
    }

    public function isCached(string $url): bool
    {
        return file_exists($this->getFileName($url));
    }

    public function putCache(string $url, string $data): bool
    {
        return file_put_contents($this->getFileName($url), $data) >= 0;
    }

    public function getCache(string $url): string | false
    {
        return file_get_contents($this->getFileName($url));
    }
}

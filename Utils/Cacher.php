<?php

declare(strict_types=1);
namespace Utils;

interface Cacher
{
    public function isCached(string $url) : bool;
    public function putCache(string $url, string $data) : bool;
    public function getCache(string $url) : string | false;

}
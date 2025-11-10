<?php

namespace Utils;

class SimpleFetcher extends FileCacher implements Fetcher
{

    public function get(string $url): string|false
    {
        if ($this->isCached($url)) {
            return $this->getCache($url);
        }

        $content = file_get_contents($url);

        if ($content) {
            $this->putCache($url, $content);
            return $content;
        }

        return false;
    }
}
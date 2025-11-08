<?php

namespace Utils;

class CurlFetcher extends FileCacher implements Fetcher
{

    public function get(string $url): string | false
    {
        if ($this->isCached($url)) {
            return $this->getCache($url);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($content) {
            $this->putCache($url, $content);
            return $content;
        } else {
            echo $error;
        }

        return false;
    }
}
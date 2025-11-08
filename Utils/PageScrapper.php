<?php

declare(strict_types=1);
namespace Utils;

use DOMDocument;
use DOMXPath;

class PageScrapper
{
    private Fetcher $fetcher;

    public function __construct(Fetcher $fetcher)
    {
       $this->fetcher = $fetcher;
    }

    public function getContents(string $url) : string | false
    {
        $content = $this->fetcher->get($url);
        if (!$content) return false;

        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
        $dom->loadHTML($content);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//div[contains(@class, "content")]');

        if (!$nodes || !$nodes[0]) return false;

        return $dom->saveHTML($nodes[0]);
    }
}

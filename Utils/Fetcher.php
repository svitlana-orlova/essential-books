<?php

declare(strict_types=1);
namespace Utils;

interface Fetcher
{
    public function get(string $url) : string | false;
}

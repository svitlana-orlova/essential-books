<?php

declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

use Utils\CurlFetcher;
use Utils\PageScrapper;

$outDir = __DIR__;
$pages = [
    'essential/go'  => 'https://www.programming-books.io/s/app-go.js',
    'essential/cpp' => 'https://www.programming-books.io/s/app-cpp.js',
    'essential/javascript' => 'https://www.programming-books.io/s/app-javascript.js',
    'essential/css' => 'https://www.programming-books.io/s/app-css.js',
    'essential/html' => 'https://www.programming-books.io/s/app-html.js',
    'essential/htmlcanvas' => 'https://www.programming-books.io/s/app-htmlcanvas.js',
    'essential/java' => 'https://www.programming-books.io/s/app-java.js',
    'essential/kotlin' => 'https://www.programming-books.io/s/app-kotlin.js',
    'essential/csharp' => 'https://www.programming-books.io/s/app-csharp.js',
    'essential/python' => 'https://www.programming-books.io/s/app-python.js',
    'essential/postgresql' => 'https://www.programming-books.io/s/app-postgresql.js',
    'essential/mysql' => 'https://www.programming-books.io/s/app-mysql.js',
    'essential/android' => 'https://www.programming-books.io/s/app-android.js',
    'essential/bash' => 'https://www.programming-books.io/s/app-bash.js',
    'essential/powershell' => 'https://www.programming-books.io/s/app-powershell.js',
    'essential/batch' => 'https://www.programming-books.io/s/app-batch.js',
    'essential/git' => 'https://www.programming-books.io/s/app-git.js',
    'essential/php' => 'https://www.programming-books.io/s/app-php.js',
    'essential/ruby' => 'https://www.programming-books.io/s/app-ruby.js',
    'essential/netframework' => 'https://www.programming-books.io/s/app-netframework.js',
    'essential/nodejs' => 'https://www.programming-books.io/s/app-nodejs.js',
    'essential/dart' => 'https://www.programming-books.io/s/app-dart.js',
    'essential/typescript' => 'https://www.programming-books.io/s/app-typescript.js',
    'essential/swift' => 'https://www.programming-books.io/s/app-swift.js',
    'essential/algorithms' => 'https://www.programming-books.io/s/app-algorithms.js',
    'essential/c' => 'https://www.programming-books.io/s/app-c.js',
    'essential/objectivec' => 'https://www.programming-books.io/s/app-objectivec.js',
    'essential/react' => 'https://www.programming-books.io/s/app-react.js',
    'essential/reactnative' => 'https://www.programming-books.io/s/app-reactnative.js',
    'essential/rubyonrails' => 'https://www.programming-books.io/s/app-rubyonrails.js',
    'essential/sql' => 'https://www.programming-books.io/s/app-sql.js',
];

$fetcher = new CurlFetcher("/tmp/essential-books/");
$scrap = new PageScrapper($fetcher);

$fetcher->setAutoClean(false);

/* Collect json files */
foreach ($pages as $path => $url) {
    echo "Fetching: $url\n";
    $dirName = $outDir . '/' . $path;
    $fileName = $dirName . '/toc.json';
    $content = '';

    /*
     * Fix the JSON and parse all the available pages
     */
    $content = preg_replace('/[[:^print:]]/', '', $fetcher->get($url));
    $content = preg_replace('/^gTocItems =/', '', $content);
    $content = preg_replace('/;$/', '', $content);

    echo "Writing into $fileName\n";

    if (!is_dir($dirName)) {
        mkdir($dirName, 0755, true);
    }

    if (!file_put_contents($fileName, $content)) {
        echo "Failed\n";
        exit(1);
    }

    $json = json_decode($content, true);

    if (!$json) {
        echo "Failed to parse json";
        continue;
    }

    foreach ($json as $title) {
        if ($title[2] == -1) continue;

        $titleFile = $dirName . '/' . $title[0];
        $url = 'https://www.programming-books.io/' . $path . '/' . $title[0];

        echo "Scrapping $url";
        $page = $scrap->getContents($url);

        if ($page) {
            echo " Success\n";
        } else {
            echo " Failed\n";
            continue;
        }

        echo "Writing into $titleFile\n";
        if (!file_put_contents($titleFile, $page)) {
            echo "Failed\n";
            continue;
        }
    }

}


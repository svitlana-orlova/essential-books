<?php

declare(strict_types=1);

function extractFile(string $path) : string | false
{
    return file_get_contents(__DIR__ . "/essential/$path");
}

function getJson(string $folder) : array
{
    return json_decode(extractFile("$folder/toc.json"), true);
}

function findRecords(string $folder, int $sub = -1) : array
{
    $json = getJson($folder);
    $res = [];
    foreach ($json as $title) {
        if ($sub == -1 && $title[1] == -1) {
            $res[] = $title;
        } else if ($title[1] == $sub) {
            $res[] = $title;
        }
    }
    return $res;
}

function findIndex(string $folder, string $name) : int
{
    $json = getJson($folder);
    foreach ($json as $index => $title) {
        if ($title[0] == $name) {
            return ($title[1] == -1 ? $index : $title[1]);
        }
    }
    return -1;
}

function findTitle(string $folder, string $name) : int
{
    $json = findRecords($folder, -1);
    $sub = findIndex($folder, $name) + 1;
    foreach ($json as $index => $title) {
        if ($title[0] == $name || $title[2] == $sub) {
            return $index;
        }
    }
    return -1;
}

function indexToc(string $folder, $name = '') : string
{
    $json = findRecords($folder, -1);
    $out  = '<div id="book-toc"><div class="article toc svelte-1ib47n1">';
    $sub = ($name ? findTitle($folder, $name) : -1);

    foreach ($json as $index => $title) {
        $out .= '<div class="toc-item"><div class="chapters-toc-item">';
        $out .= '<span class="no svelte-1ib47n1">'. $index .'</span>';
        if ($sub != -1 && $sub == $index) {
            $out .= '<b>' . $title[3] . '</b>';
        } else {
            $out .= '<a href="/essential/' . $folder . '/' . $title[0] . '">' . $title[3] . '</a>';
        }
        $out .= '</div></div>';
    }
    $out .= '</div></div>';
    return $out;
}

function pageToc(string $folder, $name) : string
{
    $index = findIndex($folder, $name);
    $records = findRecords($folder, $index);
    $title = findTitle($folder, $name);

    $out = '<div id="page-toc"><div class="article chapter-toc svelte-1t851gm">';
    $out .= '<div class="mtoc-0 svelte-1t851gm"><b>';
    $out .= findRecords($folder, -1)[$title][3];
    $out .= ':</b></div>';

    foreach ($records as $title) {
        $out .= '<div class="mtoc-1 svelte-1t851gm">';
        $out .= '<span class="no svelte-1ib47n1">*</span>';
        if ($name == $title[0]) {
            $out .= '<b>' . $title[3] . '</b>';
        } else {
            $out .= '<a href="/essential/'. $folder . '/'. $title[0] .'">' . $title[3] . '</a>';
        }
        $out .= '</div>';
    }
    $out .= '</div></div>';
    return $out;
}

function main() : void
{
    $request = $_SERVER['REQUEST_URI'];

    if (preg_match('#^/essential/(\w+)/?(.*)$#', $request, $matches)) {
        $folder = $matches[1];
        $name = $matches[2];

        echo extractFile('/assets/header.html');

        if (!$name || $name == 'index.html') {
            echo indexToc($folder);
        } else {
            $page = extractFile($folder . '/' . $name);
            $page = preg_replace('#<div id="page-toc"></div>#', pageToc($folder, $name), $page);
            echo preg_replace('#<div id="book-toc"></div>#', indexToc($folder, $name), $page);
        }

        echo extractFile('/assets/footer.html');

    } else if (str_ends_with($request, '.css')) {
        header('Content-Type: text/css');
        echo extractFile($request);
    } else {
        echo extractFile('/assets/index.html');
    }
}

main();

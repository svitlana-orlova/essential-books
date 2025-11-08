<?php

declare(strict_types=1);
$request = $_SERVER['REQUEST_URI'];

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
            continue;
        }

        if ($title[1] == $sub) {
           $res[] = $title;
        }
    }

    return $res;
}

function findIndex(string $folder, string $name) : int
{
    $json = getJson($folder);
    foreach ($json as $index => $title) {
        if ($title[0] == $name) return $index;
    }
    return -1;
}

function indexToc(string $folder) : string
{
    $json = findRecords($folder, -1);
    $out  = '<div id="book-toc"><div class="toc svelte-1ib47n1">';

    foreach ($json as $index => $title) {
        $out .= '<div class="toc-item"><div class="chapters-toc-item">';
        $out .= '<span class="no svelte-1ib47n1">'. $index .'</span>';
        $out .= '<a href="/essential/'. $folder . '/'. $title[0] .'">' . $title[3] . '</a>';
        $out .= '</div></div>';
    }

    $out .= '</div></div>';

    return $out;
}

function pageToc(string $folder, $name) : string
{
    $index = findIndex($folder, $name);
    $records = findRecords($folder, $index);
    $out = '<div id="page-toc"><div class="chapter-toc svelte-1t851gm">';

    foreach ($records as $index => $title) {
        $out .= '<div class="mtoc-1 svelte-1t851gm">';
        $out .= '<a href="/essential/'. $folder . '/'. $title[0] .'">' . $title[3] . '</a>';
        $out .= '</div>';
    }

    $out .= '</div></div>';

    return $out;
}

if (preg_match('#^/essential/(\w+)/(.*)$#', $request, $matches)) {
    $folder = $matches[1];
    $name = $matches[2];

    echo extractFile('/assets/header.html');

    if ($name == 'index.html') {
        echo indexToc($folder);
    } else {
        $page = extractFile($folder . '/' . $name);
        $ptoc = pageToc($folder, $name);
        $itoc = indexToc($folder);

        $page = preg_replace('#<div id="page-toc"></div>#', $ptoc, $page);
        $page = preg_replace('#<div id="book-toc"></div>#', $itoc, $page);
        echo $page;
    }

    echo extractFile('/assets/footer.html');

} else if (str_ends_with($request, '.css')) {
    header('Content-Type: text/css');
    echo extractFile($request);
}

?>

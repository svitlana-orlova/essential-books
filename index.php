<?php

declare(strict_types=1);

function extractFile(string $path) : string | false
{
    $dirPath = __DIR__ . '/essential/';
    $zipFile = __DIR__ . '/essential.zip';

    if (is_dir($dirPath)) {
        return @file_get_contents( $dirPath . $path);
    } else if (file_exists($zipFile)) {
        $zipPath = preg_replace('#//#', '/', "essential/$path");
        return @file_get_contents('zip://'. $zipFile . '#'. $zipPath);
    }

    echo "No essential(.zip) file found\n";
    return false;
}

function getJson(string $folder) : array
{
    return json_decode(extractFile("$folder/toc.json"), true) ?? [];
}

function findRecords(string $folder, int $sub = -1) : array
{
    return array_filter(getJson($folder), function($title) use ($sub) {
        if (($sub == -1 && $title[1] == -1) || $title[1] == $sub) {
            return true;
        }
        return false;
    });
}

function findIndex(string $folder, string $name) : int
{
    foreach (getJson($folder) as $index => $title) {
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
    $sub = ($name ? findTitle($folder, $name) : -1);

ob_start(); ?>
    <div id="book-toc"><div class="article toc svelte-1ib47n1">
    <?php $count = 0; foreach ($json as $index => $title): ?>
       <div class="toc-item"><div class="chapters-toc-item">
       <span class="no svelte-1ib47n1"><?= $count++ ?></span>
        <?php if ($sub != -1 && $sub == $index): ?>
            <b><?= $title[3] ?></b>
        <?php else: ?>
            <a href="/essential/<?= $folder . '/' . $title[0] ?>"><?= $title[3] ?></a>
        <?php endif; ?>
        </div></div>
    <?php endforeach; ?>
    </div></div>
<?php

    return ob_get_clean();
}

function pageToc(string $folder, $name) : string
{
    $index = findIndex($folder, $name);
    $records = findRecords($folder, $index);
    $title = findTitle($folder, $name);

ob_start(); ?>
    <div id="page-toc"><div class="article chapter-toc svelte-1t851gm">
    <div class="mtoc-0 svelte-1t851gm"><b><?= findRecords($folder, -1)[$title][3]; ?>:</b></div>
    <?php foreach ($records as $title): ?>
        <div class="mtoc-1 svelte-1t851gm">
        <span class="no svelte-1ib47n1">*</span>
        <?php if ($name == $title[0]): ?>
            <b> <?= $title[3] ?></b>
        <?php else: ?>
            <a href="/essential/<?= $folder . '/'. $title[0] ?>"><?= $title[3] ?></a>
        <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div></div>
<?php

    return ob_get_clean();
}

function show404() : string
{
    header("HTTP/1.1 404 Not Found");
    return  "404 Not Found";
}

function showPage(mixed $html) : void
{
    if ($html) {
        echo extractFile('/assets/header.html');
        echo $html;
        echo extractFile('/assets/footer.html');
    } else {
        echo show404();
    }
}

function main() : void
{
    $request = $_SERVER['REQUEST_URI'];

    if (preg_match('#^/essential/(\w+)/?(.*)$#', $request, $matches)) {
        list (, $folder, $name) = $matches;

        if (!$name || $name == 'index.html') {
            showPage(indexToc($folder));
        } else {
            $page = extractFile($folder . '/' . $name) ?: '';
            $page = preg_replace('#<div id="page-toc"></div>#', pageToc($folder, $name), $page);
            $page = preg_replace('#<div id="book-toc"></div>#', indexToc($folder, $name), $page);
            showPage($page);
        }
    } else if (str_ends_with($request, '.css')) {
        header('Content-Type: text/css');
        echo extractFile($request) ?: show404();
    } else {
        echo extractFile('/assets/index.html') ?: show404();
    }
}

main();
